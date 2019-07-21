<?php

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

/*Route::get('/', function () {
    return view('/login');
});*/


/*Route::get('/inicio', function () {
    return view('inicio',['modulo'=>'Inicio']);
});*/

Route::get('/api', function () {
    return view('#',['modulo'=>'API']);
});
Route::get('/register','Auth\RegisterController@index')->name('register');

/*Route::get('/emisores', function () {
    return view('emisores/emisores',['modulo'=>'Emisores']);
});
Route::get('/home', function () {
    return view('inicio',['modulo'=>'Inicio']);
});*/



Route::get('Usuario/{id}', 'UsuarioController@show');
Route::get('UsuarioAll', 'UsuarioController@showAll');

Route::get('/', 'Auth\LoginController@showLoginForm')->name('loginget');
Route::get('login', 'Auth\LoginController@showLoginForm')->name('loginget');
Route::post('login', 'Auth\LoginController@login')->name('login');
/*Route::post('logout', 'Auth\LoginController@logout')->name('logout');*/


Auth::routes();
/*Route::match(['get', 'post'], 'register', function(){
    return redirect('/');
});*/

Route::get('home', 'HomeController@index')->name('home');
//RUTAS del EMISORES
Route::get('emisores', 'EmisorController@index')->name('emisores');
Route::get('emisores/agregar', 'EmisorController@create')->name('agregarEmisor');
Route::get('emisores/{id}', 'EmisorController@show')->name('verEmisor');
Route::post('crearEmisor', 'EmisorController@store')->name('crearEmisor');
//Route::put('actualizar', 'EmisorController@update')->name('actualizar');
Route::match(['put', 'patch'], '/emisores/actualizar/{id}','EmisorController@update');
//Route::get('emisores/{id}/{id2}/eliminar','EmisorController@destroy');
/*Route::get('emisores/{id}/{id2}', [
    'as' => 'eliminar',
    'uses' => 'EmisorController@destroy'
]);*/
/*Route::put('emisores/{id}', [
    'as' => 'actualizar',
    'uses' => 'EmisorController@update'
]);*/
//Rutas de Usuarios
Route::get('usuarios', 'UsuarioController@index')->name('usuarios');
Route::get('/getUsuario/{id}',['uses'=>'UsuarioController@getUsuariosById']);
Route::get('usuarios/agregarUsuario', 'UsuarioController@create')->name('agregarUsuario');
Route::get('usuarios/{id}', 'UsuarioController@show')->name('verUsuario');
Route::match(['put', 'patch'], '/usuarios/actualizar/{id}','UsuarioController@update');
//Rutas de ubicaciones
Route::get('/cantones/{id}',['uses'=>'EmisorController@cantones']);
Route::get('/distritos/{id}/{id2}',['uses'=>'EmisorController@distritos']);
Route::get('/barrios/{id}/{id2}/{id3}',['uses'=>'EmisorController@barrios']);
Route::get('/getEmisor/{id}/{id2}',['uses'=>'EmisorController@getEmisoresById']);
//Ruta para generar la API Key
Route::get('/getApiKey',['uses'=>'EmisorController@generarNuevaApiKey']);

//Ruta para los parámetros
Route::get('parametros', 'ParametroController@index')->name('parametros');
Route::post('setearParametros', 'ParametroController@save')->name('setearParametros');

//Rutas de la API
//Route::post('api/makeXML','ComprobanteController@makeXml');
Route::post('/api/makeXML', array('as' => 'makeXML',
    'uses' => 'ComprobanteController@makeXml'));
Route::post('/api/sendXml', array('as' => 'sendXml',
    'uses' => 'ComprobanteController@sendXml'));
Route::post('/api/consultadocumento', array('as' => 'consultadocumento',
    'uses' => 'ComprobanteController@checkreceiptATV'));
Route::post('/api/consultahacienda', array('as' => 'consultahacienda',
    'uses' => 'ComprobanteController@checkAllReceiptsATV'));
Route::post('/api/acceptbounce', array('as' => 'acceptbounce',
    'uses' => 'GastoController@makeXmlGA'));
Route::post('/api/makeClave', array('as' => 'makeClave',
    'uses' => 'ComprobanteController@makeClave'));
Route::post('/api/updatePOSConsecutive', array('as' => 'updatePOSConsecutive',
    'uses' => 'ComprobanteController@updateConsecutivePOS'));
Route::get('/api/getGaConsecutive', array('as' => 'getGaConsecutive',
    'uses' => 'GastoController@getConsecutivoGA'));


//Prueba del correo
Route::get('/api/testcorreo', array('as' => 'testcorreo',
    'uses' => 'ComprobanteController@testCorreo'));
//Ruta easy pos temporal
Route::get('/easypos', array('as' => 'easypos',
    'uses' => 'ComprobanteController@getEasypos'));
//Ruta para descargar el PDF
Route::get('/api/downloadReceipt', array('as' => 'downloadReceipt',
    'uses' => 'ComprobanteController@downloadPdfInvoice'));






