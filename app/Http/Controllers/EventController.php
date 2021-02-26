<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//Ter acesso ao model de Eventos para acessar o banco
use App\Models\Event;

class EventController extends Controller
{
    public function index(){
        $events = Event::all();
    
        return view('welcome',['events' => $events]            
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

        $event->save();

        //redireciona o usuario para a index
        return redirect('/')->with('msg','Evento criado com sucesso');
    }

    public function show($id){
            $event = Event::findOrFail($id);
            return view('events.show',['event'=>$event]);
    }
}
