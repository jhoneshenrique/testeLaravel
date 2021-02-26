<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Importa o controller
use App\Http\Controllers\EventController;
//Passa como parametro o controller criado e o metodo index - index mostrar todos os registros
Route::get('/', [EventController::class,'index']);

//Rota de evento - criate mostrar o formulario de criar com registro no banco
Route::get('/events/create', [EventController::class,'create']);

//Rota de evento evento especifico - show mostrar um dado especifico
Route::get('/events/{id}', [EventController::class,'show']);

//Rota para criar evento - store cadastrar dados no banco
Route::post('/events',[EventController::class,'store']);

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/produtos', function () {

    $busca = request('search');

    return view('products',['busca' => $busca]);
});

Route::get('/produtos_teste/{id?}', function ($id = null) {
    return view('product',['id' => $id]);
});