<?php

use Illuminate\Http\Request;
use App\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@authenticate');
Route::get('validate-token', function () {
    return ['user' => User::where("username", "brettsacuna")->get(),'valid' => true];
});

Route::middleware(['jwt.verify'])->group(function () {
    Route::get('/persona/search', 'PersonaController@getpersonaid');
    Route::get('/rhabitacion/search','ReservaEstanciaHabitacionController@gethabitacionreservaestancia');
    Route::get('/rhuesped/search','ReservaEstanciaHuespedController@gethuespedreservaestancia');
    //RUTAS QUE INCLUYEN TODOS LOS MÉTODOS HTTP
    Route::resource('/', 'UsuarioController');
    Route::resource('/usuario', 'UsuarioController');
    Route::resource('/habitacion', 'HabitacionController');
    Route::resource('/clasehabitacion', 'ClaseHabitacionController');
    Route::resource('/aerolinea', 'AerolineaController');
    Route::resource('/reserva', 'ReservaEstanciaController');
    Route::resource('/persona','PersonaController');
    Route::resource('/pago','PagoController');
    Route::resource('/rhabitacion','ReservaEstanciaHabitacionController');
    Route::resource('/rhuesped','ReservaEstanciaHuespedController');
    
});