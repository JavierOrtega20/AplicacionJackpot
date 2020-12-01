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

use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;



Route::get('/', function () {
	if (Auth::check()) {
        return redirect('/home');
    }else{
    	return view('welcome');
    }

})->name('welcome');



Route::auth();

Route::get('autorizacion/{transHash}/{type?}', 'TransaccionesController@linkAuth');
Route::get('SendNotificationTransactionCreate/{id}', 'TransaccionesController@SendNotificationTransactionCreate');
Route::get('SendNotificationTransactionAuthorized/{id}', 'TransaccionesController@SendNotificationTransactionAuthorized');
Route::get('SendNotificationTransactionAuthorizedToCommerce/{id}', 'TransaccionesController@SendNotificationTransactionAuthorizedToCommerce');
Route::get('SendNotificationTransactionFailed/{mensaje}/{referencia}', 'TransaccionesController@SendNotificationTransactionFailed');
Route::get('checkStatus/{transId}', 'TransaccionesController@checkStatus');

Route::get('/excel', function () {
    return Excel::download(new UsersExport, 'users.xlsx');
});

Route::get('autorizacion/{transHash}/{type?}', 'TransaccionesController@linkAuthSms');
Route::get('autorizacion', 'TransaccionesController@linkAuth');

Route::get('checkStatus/{transId}', 'TransaccionesController@checkStatus');


Route::group(['middleware' => ['auth','ValidarSesion']], function() {

	Route::get('/dataUpdate','HomeController@dataUpdate');

	Route::get('home/{moneda?}',['as'=>'home','uses'=>'TransaccionesController@indexConsolidado']);
	
	Route::get('/sessions', ['as'=>'sessions','uses'=>'SessionController@index']);
	Route::get('/sessions/active', ['as'=>'sessions.active','uses'=>'SessionController@getSessions']);
	Route::post('/sessions/revoke', ['as'=>'sessions.revoke','uses'=>'SessionController@revoke']);

	Route::get('/password/change', ['as'=>'password.change','uses'=>'HomeController@password']);
	Route::patch('/password/{id}',['as'=>'password.update','uses'=>'HomeController@changePassword']);

	Route::get('logout', function(){
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

    Route::get('users/checkCarnets/{carnet}', 'UserController@checkCarnets' );
    Route::get('users/checkCarnetReal/{carnet}', 'UserController@checkCarnetReal' );
    Route::get('users/checkEmail/{email}', 'UserController@checkEmail' );

    Route::get('users/checkCarnetEdit/{carnet}/{id}', 'UserController@checkCarnetEdit' );
    Route::get('users/checkCarnetRealEdit/{carnet}/{id}', 'UserController@checkCarnetRealEdit' );
    Route::get('users/checkEmailEdit/{email}/{id}', 'UserController@checkEmailEdit' );

     Route::get('users/checkCodClientEmisor/{codClientEmisor}/{id}', 'UserController@checkCodClientEmisor' );
     Route::get('users/checkCodClientEmisorCreate/{codClientEmisor}', 'UserController@checkCodClientEmisorCreate' );

	Route::get('users/import',['as'=>'users.import','uses'=>'UserController@importar','middleware' => ['permission:carga-usuarios']]);
	Route::post('users/import',['as'=>'users.cargar_datos_usuarios','uses'=>'UserController@cargar_datos_usuarios','middleware' => ['permission:carga-usuarios']]);
	Route::get('users/reports','UserController@reports');
	Route::get('users/export_clients/{fecha_desde}/{fecha_hasta}/{estado}/{cliente}/{moneda}','UserController@export_clients');
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
	Route::get('comercios/canales/{id}/{retorno?}',['as'=>'comercios.canales','uses'=>'ComercioController@canales','middleware' => ['permission:comercio-edit']]);
	Route::post('comercios/report_tl_comercios2','ComercioController@report_tl_comercios2');
	Route::get('comercios/export_report_tl_comercios/{fecha_desde}/{fecha_hasta}/{estado}/{rif}/{nombreComercio}/{moneda}','ComercioController@export_report_tl_comercios');
	Route::get('comercios/reports','ComercioController@reports');
	Route::post('comercios/export_comercio','ComercioController@export_comercio');
	Route::get('comercios',['as'=>'comercios.index','uses'=>'ComercioController@index','middleware' => ['permission:comercio-list|comercio-create|comercio-edit|comercio-delete']]);
	Route::get('comercios/{idPrincipal}/create',['as'=>'comercios.create','uses'=>'ComercioController@create','middleware' => ['permission:comercio-create']]);
	Route::post('comercios/create',['as'=>'comercios.store','uses'=>'ComercioController@store','middleware' => ['permission:comercio-create']]);
	Route::get('comercios/consultaSubcategoria/{categoria}','ComercioController@consultaSubcategoria');

	Route::get('comercios/{id}',['as'=>'comercios.show','uses'=>'ComercioController@show']);
	Route::get('comercios/{id}/{retorno?}/delete',['as'=>'comercios.delete','uses'=>'ComercioController@delete']);
	Route::get('comercios/{id}/{retorno?}/restore',['as'=>'comercios.restore','uses'=>'ComercioController@restore']);

	Route::get('comercios/{id}/{retorno?}/edit',['as'=>'comercios.edit','uses'=>'ComercioController@edit','middleware' => ['permission:comercio-edit']]);
	Route::patch('comercios/{id}',['as'=>'comercios.update','uses'=>'ComercioController@update','middleware' => ['permission:comercio-edit']]);
	Route::delete('comercios/{id}',['as'=>'comercios.destroy','uses'=>'ComercioController@destroy','middleware' => ['permission:comercio-delete']]);
	
	Route::post('comercios/canales','ComercioController@ActualizarCanalTerminales')->name('canale.update');
	Route::post('comercios/agregarcanales','ComercioController@AgregarCanalTerminales')->name('canale.create');
	Route::get('comercios/desactivarTerminal/{id}/{comercio}/{retorno?}',['as'=>'comercios.desactivarTerminal','uses'=>'ComercioController@desactivarTerminal']);
	Route::get('comercios/activarTerminal/{id}/{comercio}/{retorno?}',['as'=>'comercios.activarTerminal','uses'=>'ComercioController@activarTerminal']);	
	Route::group([
		'middleware' => ['cors','permission:comercio-create'],
	], function ($router) {
		 Route::get('comercios/importar/{id}',['as'=>'comercios.importar','uses'=>'ComercioController@importar']);
		 Route::post('comercios/import',['as'=>'comercios.afiliar_comercios','uses'=>'ComercioController@afiliar_comercios']);
	});	

	/////////////// MODULO DE TRANSACCIONES //////////////
	Route::get('pretransacciones/','TransaccionesController@pretransacciones');

	Route::get('transacciones/create/{id}',['as'=>'transacciones.comerciosTrans','uses'=>'TransaccionesController@comerciosTrans']);
	Route::get('transacciones/cargaPagos','TransaccionesController@cargaPagos');
	Route::post('transacciones/uploadPagos','TransaccionesController@uploadPagos');
	Route::post('transacciones/insertFile','TransaccionesController@insertFile');
	Route::get('transacciones/LimitesDisponibles','TransaccionesController@LimitesDisponibles');
	Route::get('LimitesDisponibles',['as'=>'LimitesDisponibles','uses'=>'TransaccionesController@LimitesDisponibles']);

	Route::post('transacciones/preview_transactions','TransaccionesController@preview_transactions');
	Route::get('transacciones/filter','TransaccionesController@filter');
	Route::get('transacciones/logTrans/{id}','TransaccionesController@logTrans');
	Route::get('transacciones/montosConsolidados/{mes}/{anio}/{moneda?}','TransaccionesController@montosConsolidados');

	Route::get('transacciones/reports_preview','TransaccionesController@reports_preview');
	Route::get('transacciones/reports_liq_comercios','TransaccionesController@reports_liq_comercios');
	Route::get('transacciones/export_transactions/{fecha_desde}/{fecha_hasta}/{estado}/{comercio}/{monto}/{cliente}/{moneda}','TransaccionesController@export_transactions');

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
	Route::get('transacciones/consultaDatos/{ced}/{comer}','TransaccionesController@consultaDatos');
	Route::get('monedas/consultaDatos/{ced}/{comer}','MonedaController@consultaDatos');


	Route::get('transacciones/{id}/edit',['as'=>'transacciones.edit','uses'=>'TransaccionesController@edit','middleware' => ['permission:transacciones-edit']]);
	Route::patch('transacciones/{id}',['as'=>'transacciones.update','uses'=>'TransaccionesController@update','middleware' => ['permission:transacciones-edit']]);
	Route::delete('transacciones/{id}',['as'=>'transacciones.destroy','uses'=>'TransaccionesController@destroy','middleware' => ['permission:transacciones-delete']]);

	Route::get('reporte/LimitesDisponibles','TransaccionesController@ReportLimitesDisponibles');
	Route::get('descarga/LimitesDisponibles','TransaccionesController@DescargaLimitesDisponibles');


	//ROUTES MONEDAS
	Route::get('create/monedas','MonedaController@index');
	Route::get('list/monedas','MonedaController@list');
	Route::get('list/monedas/{id}', 'MonedaController@show');
	Route::post('store/monedas',['as'=>'monedas.store','uses'=>'MonedaController@store']);
	Route::get('edit/monedas/{id}',['as'=>'monedas.edit','uses'=>'MonedaController@edit']);
	Route::patch('update/monedas/{id}',['as'=>'monedas.update','uses'=>'MonedaController@update']);

	Route::get('activar/monedas/{id}',['as'=>'monedas.activar','uses'=>'MonedaController@activar']);
	Route::get('desactivar/monedas/{id}',['as'=>'monedas.desactivar','uses'=>'MonedaController@desactivar']);

	//ROUTES DESPLEGABLESCONTROLLERS
	Route::get('/divisas','DesplegablesController@divisas');
	Route::get('/totalizaciones/{moneda}','DesplegablesController@totalizaciones');





	Route::get('EmailCedulaInvalida','TransaccionesController@EmailCedulaInvalida');
	Route::get('MontoExcedido','TransaccionesController@MontoExcedido');
	Route::get('ClienteRestriccion','TransaccionesController@ClienteRestriccion');

	//EXPORT FILE

	Route::get('UserExcel', 'ExportController@UserExcel');
	Route::get('ComercioExcel', 'ExportController@ComercioExcel');
	Route::get('/estados','DesplegablesController@estados');
	Route::patch('/addressupdate/{id}',['as'=>'address.update','uses'=>'HomeController@update']);
	Route::get('Contratos', 'ComercioController@Contratos');

	Route::get('serial/checkSerial/{id}', 'ComercioController@ValidarSerial');

	//STRIPE AUTORIZACION CON TARJETAS INTERNACIONALES

	Route::get('Stripe',['as'=>'Stripe.create','uses'=>'StripeController@create']);
	Route::get('Stripe/store',['as'=>'Stripe.store','uses'=>'StripeController@store']);
	Route::post('Stripe/payment',['as'=>'Stripe.payment','uses'=>'StripeController@pay']);
	Route::get('checkCliente/{id}', 'StripeController@ValidarCliente');
	
	
	//COMPRA DE GIFT CARD
	Route::get('gift/list',['as'=>'gift.step1','uses'=>'GiftController@gift_cards_step1']);
	Route::get('gift/step2/{id}',['as'=>'gift.step2','uses'=>'GiftController@gift_cards_step2']);
	Route::post('gift/step3',['as'=>'gift.step3','uses'=>'GiftController@gift_cards_step3']);
	Route::post('gift/step4',['as'=>'gift.step4','uses'=>'GiftController@gift_cards_step4']);
	Route::post('gift/step5',['as'=>'gift.step5','uses'=>'GiftController@gift_cards_step5']);
	Route::post('gift/step6',['as'=>'gift.step6','uses'=>'GiftController@gift_cards_step6']);
	Route::post('gift/step7',['as'=>'gift.step7','uses'=>'GiftController@gift_cards_step7']);
	
	
	Route::get('gift/venta/{id}',['as'=>'gift.venta','uses'=>'GiftController@gift_cards_venta']);
	Route::post('gift/pagar',['as'=>'gift.pagar','uses'=>'GiftController@gift_cards_pagar']);
	
	Route::get('gift/comprador/{id}',['as'=>'gift.comprador','uses'=>'GiftController@gift_cards_comprador']);
	Route::post('gift/receptor',['as'=>'gift.receptor','uses'=>'GiftController@gift_cards_receptor']);
	Route::post('gift/metodopago',['as'=>'gift.metodopago','uses'=>'GiftController@gift_cards_metodopago']);
	//Route::post('gift/pagar',['as'=>'gift.pagar','uses'=>'GiftController@gift_cards_pagar']);
	Route::get('gift/consultaDatos/{ced}/{nac}','GiftController@consultaDatos');	
	Route::get('gift/create',['as'=>'gift.create','uses'=>'GiftController@create']);
	Route::get('gift/ventas',['as'=>'gift.ventas','uses'=>'GiftController@ventas']);
	Route::post('gift/ventas',['as'=>'gift.ventas','uses'=>'GiftController@ventas']);
	Route::get('gift/consolidado',['as'=>'gift.consolidado','uses'=>'GiftController@consolidado']);
	Route::post('gift/consolidado',['as'=>'gift.consolidado','uses'=>'GiftController@consolidado']);
	Route::get('gift/{id}/detallecliente',['as'=>'gift.detallecliente','uses'=>'GiftController@detallecliente']);
	Route::get('gift',['as'=>'gift.index','uses'=>'GiftController@index']);
	Route::post('gift',['as'=>'gift.index','uses'=>'GiftController@index']);
	Route::post('gift/store',['as'=>'gift.store','uses'=>'GiftController@store']);
	Route::get('gift/{id}/edit',['as'=>'gift.edit','uses'=>'GiftController@edit']);
	Route::post('gift/update',['as'=>'gift.update','uses'=>'GiftController@update']);
	
	
	




});
