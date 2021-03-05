<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//Ter acesso ao model de Eventos para acessar o banco
use App\Models\Event;

//Ter acesso ao model de Usuario
use App\Models\User;

class EventController extends Controller
{
    public function index(){
        $search = request('search');
        //Se a busca for preenchida retorna a mesma, senão torna o evento
        if($search){
            //Consulta where no Laravel. 
            $events = Event::where([
                ['title','like','%'.$search.'%']
            ])->get();
        }else{
            $events = Event::all();
        }

        
    
        return view('welcome',['events' => $events,'search'=>$search]            
        );
    }

    //Actions
    public function create(){
        return view ('events.create');
    }

    //Recebe o Request que vem do formulario
    public function store(Request $request){
        $event = new Event;
        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items; 

        //Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()){
            $requestImage = $request->image;    
            $extension = $requestImage->extension();

            //Deixa o nome do arquivo unico
            $imageName = md5($requestImage->getClientOriginalName().strtotime("Now").".".$extension);

            $requestImage->move(public_path('img/events'),$imageName);

            $event->image = $imageName;
        }

        //Pegar o usuario logado
        $user = auth()->user();
        $event->user_id = $user->id; //propriedade id do usuario logado

        $event->save();

        //redireciona o usuario para a index
        return redirect('/')->with('msg','Evento criado com sucesso');
    }

    public function show($id){
            $event = Event::findOrFail($id);

            $user = auth()->user();
            $hasUserJoined = false;

            //Verifica se o usuario está realmente logado
            if($user){
                $userEvents = $user->eventsAsParticipant->toArray();
                //Percorre todo o Array de eventos
                foreach($userEvents as $userEvent){
                    //Comprada o id do usuario do evento com o id do usuario que quer confirmar presença
                    if($userEvent['id']==$id){
                        $hasUserJoined = true;
                    }
                }
            }

            $eventOwner = User::where('id', $event->user_id)->first()->toArray(); //id = $user_id

            return view('events.show',['event'=>$event, 'eventOwner'=>$eventOwner,'hasUserJoined'=>$hasUserJoined]);
    }

    public function dashboard(){
        //Pega usuário autenticado
        $user = auth()->user();
        
        //Pega a propriedade do módel que foi criada que pega todos os eventos de um determiando usuário
        $events = $user->events;

        //Pegar os eventos que o usuario participa
        $eventsAsParticipant = $user->eventsAsParticipant; 

        return view('events.dashboard',['events'=>$events, 'eventsasparticipant'=>$eventsAsParticipant]);
    }

    public function destroy($id){
        Event::findOrFail($id)->delete();

        return redirect('dashboard')->with('msg','Evento excluido com sucesso!');
    }

    public function edit($id){
        $user = auth()->user();
        $event = Event::findOrFail($id);

        //Evitar edição manipulada pela dashbaord
        if($user->id != $event->user_id){
            return redirect('/dashboard');
        }

        return view('events.edit',['event'=>$event]);
    }

    public function update(Request $request){
        $data = $request->all();

        //Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()){
            $requestImage = $request->image;    
            $extension = $requestImage->extension();

            //Deixa o nome do arquivo unico
            $imageName = md5($requestImage->getClientOriginalName().strtotime("Now").".".$extension);

            $requestImage->move(public_path('img/events'),$imageName);

            $data['image'] = $imageName;
        }
        

        Event::findOrFail($request->id)->update($data);

        return redirect('dashboard')->with('msg','Evento editado com sucesso!');
    }

    public function joinEvent($id) {

        $user = auth()->user();

        $user->eventsAsParticipant()->attach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Sua presença está confirmada no evento ' . $event->title);

    }

    public function leaveEvent($id){
        $user = auth()->user();

        $user->eventsAsParticipant()->detach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Você saiu do evento ' . $event->title);
    }
}
