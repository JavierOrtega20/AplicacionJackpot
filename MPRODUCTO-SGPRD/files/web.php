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

Route::get('/', function () {
	if (Auth::check()) {
        return redirect('/home');
    }else{
    	return view('welcome');
    }

})->name('welcome');



Route::auth();

Route::group(['middleware' => ['auth','ValidarSesion']], function() {

	Route::get('home',['as'=>'home','uses'=>'TransaccionesController@indexConsolidado']);

	Route::get('/password/change', ['as'=>'password.change','uses'=>'HomeController@password']);
	Route::patch('/password/{id}',['as'=>'password.update','uses'=>'HomeController@changePassword']);

	Route::get('logout', function(){
		$user = Auth::user();
		if ($user) {
			$user->session_id = null;
			$user->save();
		}
		Auth::logout();
        return redirect('/')->with('success','Su sesión ha cerrado satisfactoriamente.');
	});

	Route::get('roles',['as'=>'roles.index','uses'=>'RoleController@index','middleware' => ['permission:role-list|role-create|role-edit|role-delete']]);
	Route::get('roles/create',['as'=>'roles.create','uses'=>'RoleController@create','middleware' => ['permission:role-create']]);
	Route::post('roles/create',['as'=>'roles.store','uses'=>'RoleController@store','middleware' => ['permission:role-create']]);
	Route::get('roles/{id}',['as'=>'roles.show','uses'=>'RoleController@show']);
	Route::get('roles/{id}/edit',['as'=>'roles.edit','uses'=>'RoleController@edit','middleware' => ['permission:role-edit']]);
	Route::patch('roles/{id}',['as'=>'roles.update','uses'=>'RoleController@update','middleware' => ['permission:role-edit']]);
	Route::delete('roles/{id}',['as'=>'roles.destroy','uses'=>'RoleController@destroy','middleware' => ['permission:role-delete']]);


	Route::get('users/import',['as'=>'users.import','uses'=>'UserController@importar','middleware' => ['permission:carga-usuarios']]);
	Route::post('users/import',['as'=>'users.cargar_datos_usuarios','uses'=>'UserController@cargar_datos_usuarios','middleware' => ['permission:carga-usuarios']]);
	Route::get('users/reports','UserController@reports');
	Route::get('users/export_clients/{fecha_desde}/{fecha_hasta}/{estado}/{cliente}','UserController@export_clients');
	Route::get('users/limites',['as'=>'users.limites','uses'=>'UserController@limites','middleware' => ['permission:carga-limites']]);
	Route::post('users/limites',['as'=>'users.cargar_limites','uses'=>'UserController@cargar_limites','middleware' => ['permission:carga-limites']]);

	Route::post('users/search','UserController@search');
	Route::get('users',['as'=>'users.index','uses'=>'UserController@index','middleware' => ['permission:user-list|user-create|user-edit|user-delete']]);
	Route::get('users/create',['as'=>'users.create','uses'=>'UserController@create','middleware' => ['permission:user-create']]);
	Route::post('users/create',['as'=>'users.store','uses'=>'UserController@store','middleware' => ['permission:user-create']]);
	Route::get('users/{id}/edit',['as'=>'users.edit','uses'=>'UserController@edit','middleware' => ['permission:user-edit']]);
	Route::patch('users/{id}',['as'=>'users.update','uses'=>'UserController@update','middleware' => ['permission:user-edit']]);
	Route::get('users/activar/{id}',['as'=>'users.activar','uses'=>'UserController@activar','middleware' => ['permission:user-delete']]);
	Route::get('users/desactivar/{id}',['as'=>'users.desactivar','uses'=>'UserController@desactivar','middleware' => ['permission:user-delete']]);
	Route::get('users/{id}',['as'=>'users.show','uses'=>'UserController@show']);

	/////////// MODULO DE BANCOS //////////////

	Route::get('bancos',['as'=>'bancos.index','uses'=>'BancoController@index','middleware' => ['permission:banco-list|banco-create|banco-edit|banco-delete']]);
	Route::get('bancos/create',['as'=>'bancos.create','uses'=>'BancoController@create','middleware' => ['permission:banco-create']]);
	Route::post('bancos/create',['as'=>'bancos.store','uses'=>'BancoController@store','middleware' => ['permission:banco-create']]);
	Route::get('bancos/{id}',['as'=>'bancos.show','uses'=>'BancoController@show']);
	Route::get('bancos/{id}/edit',['as'=>'bancos.edit','uses'=>'BancoController@edit','middleware' => ['permission:banco-edit']]);
	Route::patch('bancos/{id}',['as'=>'bancos.update','uses'=>'BancoController@update','middleware' => ['permission:banco-edit']]);
	Route::delete('bancos/{id}',['as'=>'bancos.destroy','uses'=>'BancoController@destroy','middleware' => ['permission:banco-delete']]);

	//////////// MODULO DE COMERCIOS ///////////////
	Route::get('comercios/report_tl_comercios','ComercioController@report_tl_comercios');
	Route::post('comercios/report_tl_comercios2','ComercioController@report_tl_comercios2');
	Route::get('comercios/export_report_tl_comercios/{fecha_desde}/{fecha_hasta}/{estado}/{rif}/{nombreComercio}','ComercioController@export_report_tl_comercios');
	Route::get('comercios/reports','ComercioController@reports');
	Route::post('comercios/export_comercio','ComercioController@export_comercio');
	Route::get('comercios',['as'=>'comercios.index','uses'=>'ComercioController@index','middleware' => ['permission:comercio-list|comercio-create|comercio-edit|comercio-delete']]);
	Route::get('comercios/create',['as'=>'comercios.create','uses'=>'ComercioController@create','middleware' => ['permission:comercio-create']]);
	Route::post('comercios/create',['as'=>'comercios.store','uses'=>'ComercioController@store','middleware' => ['permission:comercio-create']]);

	Route::get('comercios/{id}',['as'=>'comercios.show','uses'=>'ComercioController@show']);
	Route::get('comercios/{id}/edit',['as'=>'comercios.edit','uses'=>'ComercioController@edit','middleware' => ['permission:comercio-edit']]);
	Route::patch('comercios/{id}',['as'=>'comercios.update','uses'=>'ComercioController@update','middleware' => ['permission:comercio-edit']]);
	Route::delete('comercios/{id}',['as'=>'comercios.destroy','uses'=>'ComercioController@destroy','middleware' => ['permission:comercio-delete']]);

	/////////////// MODULO DE TRANSACCIONES //////////////
	Route::get('pretransacciones/','TransaccionesController@pretransacciones');

	Route::get('transacciones/create/{id}',['as'=>'transacciones.comerciosTrans','uses'=>'TransaccionesController@comerciosTrans']);
	Route::get('transacciones/cargaPagos','TransaccionesController@cargaPagos');
	Route::post('transacciones/uploadPagos','TransaccionesController@uploadPagos');

	Route::post('transacciones/preview_transactions','TransaccionesController@preview_transactions');
	Route::get('transacciones/filter','TransaccionesController@filter');
	Route::get('transacciones/logTrans/{id}','TransaccionesController@logTrans');
	Route::get('transacciones/montosConsolidados/{mes}/{anio}','TransaccionesController@montosConsolidados');

	Route::get('transacciones/reports_preview','TransaccionesController@reports_preview');
	Route::get('transacciones/reports_liq_comercios','TransaccionesController@reports_liq_comercios');
	Route::get('transacciones/export_transactions/{fecha_desde}/{fecha_hasta}/{estado}/{comercio}/{monto}/{cliente}','TransaccionesController@export_transactions');

	Route::get('transacciones/getbanco/{id}','TransaccionesController@getbanco');

	Route::post('transacciones/export_liq_comercios','TransaccionesController@export_liq_comercios');
	Route::post('transacciones/search','TransaccionesController@search');

	Route::get('transacciones',['as'=>'transacciones.index','uses'=>'TransaccionesController@index','middleware' => ['permission:transacciones-list|transacciones-create|transacciones-edit|transacciones-delete']]);
	Route::get('transacciones/create',['as'=>'transacciones.create','uses'=>'TransaccionesController@create','middleware' => ['permission:transacciones-create']]);
	Route::post('transacciones/create',['as'=>'transacciones.store','uses'=>'TransaccionesController@store','middleware' => ['permission:transacciones-create']]);
	Route::post('transacciones/autorizar',['as'=>'transacciones.autorizar','uses'=>'TransaccionesController@autorizar','middleware' => ['permission:transacciones-create']]);
	Route::post('transacciones/reversar',['as'=>'transacciones.reversar','uses'=>'TransaccionesController@reversar','middleware' => ['permission:transacciones-create']]);
	//Route::post('autorizar',['as'=>'autorizar','uses'=>'TransaccionesController@autorizar']);
	Route::get('transacciones/{id}',['as'=>'transacciones.show','uses'=>'TransaccionesController@show']);
	Route::get('transacciones/consultaDatos/{ced}','TransaccionesController@consultaDatos');

	Route::get('transacciones/{id}/edit',['as'=>'transacciones.edit','uses'=>'TransaccionesController@edit','middleware' => ['permission:transacciones-edit']]);
	Route::patch('transacciones/{id}',['as'=>'transacciones.update','uses'=>'TransaccionesController@update','middleware' => ['permission:transacciones-edit']]);
	Route::delete('transacciones/{id}',['as'=>'transacciones.destroy','uses'=>'TransaccionesController@destroy','middleware' => ['permission:transacciones-delete']]);

	Route::get('reporte/LimitesDisponibles','TransaccionesController@ReportLimitesDisponibles');
	Route::get('descarga/LimitesDisponibles','TransaccionesController@DescargaLimitesDisponibles');

	Route::get('EmailCedulaInvalida','TransaccionesController@EmailCedulaInvalida');
	Route::get('MontoExcedido','TransaccionesController@MontoExcedido');

});
