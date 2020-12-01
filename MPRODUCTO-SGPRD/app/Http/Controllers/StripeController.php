<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Illuminate\Http\Request;
use Config\webConfig;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\trans_head;
use App\Models\trans_body;
use App\Models\User;
use App\Models\comercios;
use App\Models\bancos;
use App\Models\carnet;
use App\Models\miem_come;
use App\Models\Automatic_Files;
use App\Models\trans_gift_card;
use App\Models\Files_History;
use App\Models\banc_comer;
use App\Models\miem_ban;
use App\Models\Role;
use App\Models\Ledge;
use App\Moneda;
use App\Models\log_trans;
use App\Shortcute;
use Carbon\Carbon;
use App\Http\Resources\ResourceFunctions;
use Excel;
use Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\transaccionesEmail;
use App\Mail\transaccionesEmailNoLink;
use App\Mail\CedulaInvalida;
use App\Mail\MontoExcedido;
use App\Mail\ClienteRestriccion;
use App\Models\emisores;
use App\Mail\autorizacionTransEmail;
use App\Mail\autorizacionTransAlComercioEmail;
use App\Soap\GetSendSms;
use App\Soap\GetSendSmsResponse;
use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Facades\Input;
use GuzzleHttp\Client;
use Hash;
use App\Http\Controllers\transaccionesController;
use App\Models\meritop_send; 

use Stripe\Charge;
use Stripe\Stripe_Error; 

class StripeController extends Controller
{
    protected $soapWrapper;
    public function __construct(SoapWrapper $soapWrapper)
    {
        $this->soapWrapper = $soapWrapper;
    }    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function create(Request $request)
    {
		//dd($request);
        Log::info('Ingreso exitoso a BancoController - create(), del usuario: '.Auth::user()->first_name);

        $user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

         $comercios = comercios::select('comercios.id',
		 DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))
		 ->join('banc_comer','comercios.id','banc_comer.fk_id_comer')
        ->where('rif','!=','J-0000000000')
		->whereNotNull('banc_comer.tasa_cobro_comer_stripe')
        ->where('posee_sucursales','=',false)
		->distinct()
         ->get();

         $cedula_giftcard = "";
         $total_monto_giftcard = "";
         $nacionalidad_giftcard = "";
         $fk_dni_recibe = "";
         $fk_carnet_id_recibe = "";
         $monto_original = "";
         $comision_monto = "";
         $dias_vencimiento = "";
         $giftcard_id = "";
         $giftcard = 0;
		 $giftcard_imagen = "";

         if(isset($request->gift_card))
         {
            $Comprador = User::select('nacionalidad')->where('dni','=',$request->cedula)->first();

            $giftcard = 1;
            $cedula_giftcard = $request->cedula;
            $nacionalidad_giftcard = $Comprador->nacionalidad;
            $total_monto_giftcard = number_format(str_replace(",", ".",$request->monto), 2, '.', ',');
            $fk_dni_recibe = $request->fk_dni_recibe;
            $fk_carnet_id_recibe = $request->fk_carnet_id_recibe;
            $monto_original = $request->monto_original;
            $comision_monto = $request->comision_monto;
            $dias_vencimiento = $request->dias_vencimiento;
            $giftcard_id = $request->giftcard_id;
			$giftcard_imagen = $request->giftcard_imagen;
         }


        return view('stripe.create', compact('rol', 'comercios'))
        ->with('status', 'new')
        ->with('giftcard', $giftcard)
        ->with('cedula_giftcard', $cedula_giftcard)
        ->with('nacionalidad_giftcard', $nacionalidad_giftcard)
        ->with('total_monto_giftcard', $total_monto_giftcard)
        ->with('fk_dni_recibe', $fk_dni_recibe)
        ->with('fk_carnet_id_recibe', $fk_carnet_id_recibe)
        ->with('monto_original', $monto_original)
        ->with('comision_monto', $comision_monto)
        ->with('dias_vencimiento', $dias_vencimiento)
        ->with('giftcard_id', $giftcard_id);
    }

    public function pay(Request $request){     
        try{  
		
			$emisor = emisores::select('emisores.bin')
			->Where('emisores.cod_emisor', '=', 'INTICARD001')
			->first();		
  
            Log::info('Ingreso exitoso a BancoController - create(), del usuario: '.Auth::user()->first_name);

            $token = mt_rand(100000, 999999);
            $token_encrypt=Crypt::encrypt($token);
            $hoy = date("Y-m-d H:i:s");
            // dd($hoy);

            $extra_min= date("Y-m-d H:i:s",strtotime($hoy."+ 20 minutes"));
            $otpBCO = null;
            $expiraOtpBco = null;

            $user= User::find(Auth::user()->id);

            $roles= $user->roles;
            $rol = null;
            foreach ($roles as $value) {
                $rol = $value->id;
            }

            $iduser=Auth::user()->id;

            $existing_user= User::select('users.id as idMiembros', 'users.dni', 'users.nacionalidad', 'users.email')
            ->where('dni', $request->cedula)
            ->first();
            $dniLen = strlen($request->cedula);
			
			$carnet = str_pad($emisor->bin.$request->cedula, 16, '0');
                        

            if(!$existing_user){
                $input['password'] = Hash::make('qwerty123456');

                $user = User::create([
                'nacionalidad'      => $request->nacionalidad,
                'dni'               => $request->cedula,
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'email'             => $request->email,
                'password'          => $input['password'],
                'birthdate'         => null,
                'kind'              => 1,
                'cod_tel'           => $request->cod_tel,
                'num_tel'           => $request->num_tel,
                ]);

                $user->attachRole(5);

                carnet::create([
                        'carnet' => $carnet,
                        'limite' => 0,
                        'disponible' => 0,
                        'fk_id_banco' => 1,//$input['banco'],
                        'fk_id_miembro' => $user->id,
                        'fk_monedas' =>1,
                        'carnet_real' => $carnet,
                        'tipo_producto' => 2,
                        'cod_emisor' => "INTICARD001",
                        'cod_cliente_emisor' => $carnet,
                        'nombre' => 'Stripe',
                      ]);

                $existing_user= User::select('users.id as idMiembros', 'users.dni', 'users.nacionalidad', 'users.email')
                ->where('dni', $request->cedula)
                ->first();
            }            

            $existing_carnet= carnet::select('carnet.*','users.*', 'carnet.id as carnet_id')
                ->join('users','users.id','carnet.fk_id_miembro')
                ->where('carnet.cod_emisor','INTICARD001')
                ->where('fk_id_miembro',$existing_user->idMiembros)
                ->first();
     
            $fk_id_comer= miem_come::select('miem_come.id','miem_come.fk_id_comercio')
                ->where('fk_id_miembro',$iduser)
                ->first();
            if($rol == 3){
                $nameComer = comercios::select('comercios.razon_social')->where('id', $fk_id_comer->fk_id_comercio)->first();
            }else{
                $nameComer = comercios::select('comercios.razon_social')->where('id', $request->fk_id_comercio)->first();
            }

            if(!$existing_carnet)
            {
                $carnet = str_pad($emisor->bin.$request->cedula, 16, '0');

                carnet::create([
                    'carnet' => $carnet,
                    'limite' => 0,
                    'disponible' => 0,
                    'fk_id_banco' => 1,
                    'fk_id_miembro' => $existing_user->idMiembros,
                    'fk_monedas' =>1,
                    'carnet_real' => $carnet,
                    'tipo_producto' => 2,
                    'cod_emisor' => "INTICARD001",
                    'cod_cliente_emisor' => $carnet,
                    'nombre' => 'Stripe',
                  ]); 
                  
                  $existing_carnet= carnet::select('carnet.*','users.*', 'carnet.id as carnet_id')
                  ->join('users','users.id','carnet.fk_id_miembro')
                  ->where('carnet.cod_emisor','INTICARD001')
                  ->where('fk_id_miembro',$existing_user->idMiembros)
                  ->first();                  
            }

            if ($existing_user && $existing_carnet){
                //dd($request->amount);
                //dd($existing_carnet->carnet_id);  

                Stripe::setApiKey(config('services.stripe.secret'));
                $token = $request->stripeToken;

                $monto = str_replace(".", "", $request->amount);
                $monto = str_replace(",", "",$monto);
                //dd($monto);
                $charge = Charge::create([
                    'amount' => $monto,
                    'currency' => 'usd',
                    'description' => 'Pago '.$nameComer->razon_social,
                    'source' => $token,
                ]); 



                if ($charge->id != null || $charge->id != ""){
                    $clientIP = \Request::ip();

                    $monto = str_replace(".", "", $request->amount);
                    $monto = str_replace(",", ".",$monto);

                    $trans_head = new \App\Models\trans_head();
                    
                    $trans_head->fk_dni_miembros = $existing_user->idMiembros;
                    $trans_head->fk_id_banco  = /*$request -> fk_id_banco*/ 1;
                    if($rol == 4){
                        $trans_head->fk_id_comer  = $request->fk_id_comercio;
                        $trans_head->origen  = "Cobro (BCO)";
                    }else{
                        $trans_head->fk_id_comer = $fk_id_comer->fk_id_comercio;
                        $trans_head->origen  = "Cobro (INT)";
                    }
					
						//MARCAR COMPRA GIFT
						if(isset($request->gift_card))
						{
							$trans_head->origen  = "Cobro (Gift)";
						}
						
                        $trans_head->monto        = $monto;
                        $trans_head->cancela_a    = 0;
                        $trans_head->token    = $token_encrypt;
                        $trans_head->status    = 0;
                        $trans_head->ip     = $clientIP;
                        $trans_head->token_status    = 0;
                        $trans_head->token_time    = $extra_min;
                        $trans_head->propina = 0;
                        $trans_head->neto = (0+$monto);
                        $trans_head->fk_monedas = 1;
                        $trans_head->carnet_id = $existing_carnet->carnet_id;
                        $trans_head->otp_bco = $otpBCO;
                        $trans_head->otp_bco_time = $expiraOtpBco;


                    if( $trans_head->save()){

                        $ledge = new \App\Models\Ledge();
                        $ledge->fk_id_trans_head= $trans_head->id;
                        $ledge->fk_dni_miembros= $trans_head->fk_dni_miembros;
                        $ledge->monto= $trans_head->monto+0;
                        $ledge->propina= 0;
                        $ledge->disp_pre= 0;
                        $ledge->carnet_id = $existing_carnet->carnet_id;
                        $ledge->disp_post = $ledge->disp_pre;

                        if ($ledge->save()) {

                            $transacciones = trans_head::select('trans_head.id as idTrans', 'trans_head.monto','trans_head.propina', 'trans_head.created_at as fechaTrans', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos')
                            ->join('users','users.id','trans_head.fk_dni_miembros')
                            ->join('carnet','carnet.fk_id_miembro','users.id')
                            ->join('comercios','comercios.id','trans_head.fk_id_comer')
                            ->join('bancos','bancos.id','trans_head.fk_id_banco')
                            ->where('trans_head.id' ,$trans_head->id)
                            ->first();             

                            $this->insertLog('Transacción creada exitosamente',$trans_head->id);           

                            // if(isset($transacciones -> descripcionComercios)){
                            $desc_comercio= $transacciones->descripcionComercios;
                            // }
                            $idTrans= $transacciones->idTrans;
                            $first_name= $transacciones->first_name;
                            $last_name= $transacciones->last_name;
                            $montos = $transacciones->monto + $transacciones->propina;
                            $montos = number_format($montos , 2, ',', '.');
                            $telefono = $transacciones->cod_tel.''.$transacciones->num_tel;

                            $data= $trans_head;

                            //CREAR ASOCIACION DE GIFTCARD
                            if($request->giftcard == 1)
                            {
                                $fecha_vencimiento = Carbon::now();
                                $fecha_vencimiento = $fecha_vencimiento->addDays($request->dias_vencimiento);

                                $giftcard_parameters = emisores::select('emisores.id','emisores.paga_comision')->where('emisores.id','=',$request->giftcard_id)->first();
                                
                                trans_gift_card::create([
                                                        'fk_trans_id' => $trans_head->id,
                                                        'fk_dni_recibe' => $request->fk_dni_recibe,
                                                        'fk_carnet_id_recibe' => $request->fk_carnet_id_recibe,
                                                        'monto' => str_replace(",", ".",$request->monto_original),
                                                        'comision_monto' => str_replace(",", ".",$request->comision_monto),
                                                        'vencimiento' => $fecha_vencimiento,
                                                        'pago_comision' => $giftcard_parameters->paga_comision,
                                                        'giftcard_id' => $giftcard_parameters->id,
														'imagen' => $request->giftcard_imagen,
                                                    ]);

                                                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD
                                                    $datos_gift = trans_gift_card::select('trans_gift_card.fk_carnet_id_recibe','trans_gift_card.monto')
                                                    ->Where('trans_gift_card.fk_trans_id', '=', $trans_head->id)
                                                    ->first();

                                                    if($datos_gift)
                                                    {
                                                        $caret_gift = carnet::select('carnet.disponible','carnet.cod_emisor','users.cod_tel','users.num_tel')
                                                        ->join('users','carnet.fk_id_miembro','users.id')
                                                        ->Where('carnet.id', '=', $datos_gift->fk_carnet_id_recibe)
                                                        ->first();

                                                        $disponible_final_gift_card = $caret_gift->disponible + $datos_gift->monto;

                                                        carnet::where('id',$datos_gift->fk_carnet_id_recibe)
                                                        ->update([
                                                            'disponible' => $disponible_final_gift_card,
                                                        ]);

                                                        $gift = emisores::select('emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
                                                        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
                                                        ->Where('emisores.cod_emisor', '=', $caret_gift->cod_emisor)
                                                        ->first();
                                                        
                                                        $telefono = $caret_gift->cod_tel.''.$caret_gift->num_tel;
                                                        
                                                        try{

                                                            $client = new Client([
                                                                'timeout'  => 5.0,
                                                            ]);
                                        
                                                            $request = $client->post('http://200.74.195.98:14000/smsbanplus', [
                                                                'headers' =>
                                                                    [
                                                                        'cache-control' => 'no-cache',
                                                                        'Connection' => 'keep-alive',
                                                                        'Content-Length' => '126',
                                                                        'Accept-Encoding' => 'gzip, deflate',
                                                                        'Host' => '200.74.195.98:14000',
                                                                        'Postman-Token' => 'dd0f1648-e417-4e7f-aa6d-48769003beef,94c12482-8714-4ce0-aea9-7433bd54c72f',
                                                                        'Cache-Control' => 'no-cache',
                                                                        'Accept' => '*/*',
                                                                        'User-Agent' => 'PostmanRuntime/7.17.1',
                                                                        'X-Auth-Apikey' => 'ZtlDZDAv',
                                                                        'Content-Type' => 'application/json'                              
                                                                    ],
                                                                'body' => json_encode(
                                                                    [
                                                                        "mensaje" => 'Usted ha recibido una Gift Card '.$gift->nombregift.' por '.$gift->mon_simbolo.' '.$datos_gift->monto,
                                                                        "num_dest" => $telefono,
                                                                        "prior" => "0",
                                                                        "dep" => "01",
                                                                        "id" => "2019102112072015"
                                                                    
                                                                    ]
                                                                )
                                                            ]);
                                        
                                                        }
                                                        catch(\Exception $e)
                                                        {
                                                            $this->soapWrapper->add('Sms', function ($service) {
                                                                $service
                                                                    ->wsdl('http://api.tedexis.com:8086/m4.in.wsint/services/M4WSIntSR?wsdl')
                                                                    ->trace(true)
                                                                    ->classmap([
                                                                    GetSmsSend::class,
                                                                    GetSmsSendResponse::class,
                                                                    ]);
                                                                });
                                                                header("Content-Type: text/html;charset=utf-8");
                                                                $response = $this->soapWrapper->call('Sms.sendSMS', [
                                                                //new GetSendSms('VXMERI', '$1e2M3r1t0p', $telefono, 'Transaccion Presidents Pay por Bs '. $montos .' de fecha: '. $fecha .'. Clave Aut: '. $token .'. Para mas informacion contactenos al 0412Banplus (2267587) o 02129092003')
                                                                new GetSendSms('VXBANPLUS', 'H41H30E0', $telefono, 'Usted ha recibido una Gift Card '.$gift->nombregift.' por '.$gift->mon_simbolo.' '.$datos_gift->monto)
                                                                ]);                     
                                                        }                        
                                                    }

                                                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD                                                        
                            }                             

                            flash('Se ha realizado la operacion solicitada exitosamente.', '¡Operación Exitosa!')->success();
                            return redirect()->route('transacciones.index')->with('status','ok');               
                        }
                    }
                }else{
                    //else si el cargo no fue autorizado
                   
                    return redirect()->route('Stripe.create')->with('status','error1');
                }
            }else{
                //else si el usuario no existe
                
                return redirect()->route('Stripe.create')->with('status','error2');
            }
        }catch(\Stripe\Exception\CardException $e) {
  // Since it's a decline, \Stripe\Exception\CardException will be caught
  $errorCode = $e->getError()->code;
  //dd($e);
  $Message = $this->ErrorsStripe($errorCode);
  flash($Message, '¡Alert!')->error();
  return redirect()->route('Stripe.create')->with('status',$errorCode);   


} catch (\Stripe\Exception\RateLimitException $e) {
  // Too many requests made to the API too quickly
    flash('Demasiadas solicitudes realizadas a la API demasiado rápido', '¡Alert!')->error();
            return redirect()->route('Stripe.create');
           
} catch (\Stripe\Exception\InvalidRequestException $e) {
    // Invalid parameters were supplied to Stripe's API
    $errorCode = $e->getError()->code;

    $Message = $this->ErrorsStripe($errorCode);
    flash($Message, '¡Alert!')->error();
    return redirect()->route('Stripe.create')->with('status',$errorCode);    

    switch ($errorCode) {
        case 'amount_too_large':

            break;        
        default:
            flash( 'Generic parameters error', '¡Alert!')->error();
            return redirect()->route('Stripe.create');
            break;
    } 
} catch (\Stripe\Exception\AuthenticationException $e) {
  // Authentication with Stripe's API failed
  // (maybe you changed API keys recently)
    flash( 'Generic authentication Stripe API error', '¡Alert!')->error();
    return redirect()->route('Stripe.create');
        
} catch (\Stripe\Exception\ApiConnectionException $e) {
  // Network communication with Stripe failed
    flash( 'Generic authentication Stripe API error', '¡Alert!')->error();
    return redirect()->route('Stripe.create');
} catch (\Stripe\Exception\ApiErrorException $e) {
  // Display a very generic error to the user, and maybe send
  // yourself an email
    flash( 'Generic error', '¡Alert!')->error();
    return redirect()->route('Stripe.create');
} catch (Exception $e) {
  // Something else happened, completely unrelated to Stripe
    flash( 'Error interno, completamente ajeno a Stripe', '¡Alert!')->error();
    return redirect()->route('Stripe.create');
        }       
    }//final del metodo 

    public function ErrorsStripe($code)
    {
        switch ($code) {
            case 'account_already_exists':
            return "La dirección de correo electrónico proporcionada para la creación de una cuenta diferida ya tiene una cuenta asociada. Utilice el flujo de OAuth para conectar la cuenta existente a su plataforma."; break;
            
            case 'account_country_invalid_address':
            return "El país de la dirección comercial proporcionada no coincide con el país de la cuenta. Las empresas deben estar ubicadas en el mismo país que la cuenta."; break;
            
            case 'account_invalid':
            return "El ID de cuenta proporcionado como valor para el Stripe-Account encabezado no es válido. Verifique que sus solicitudes especifiquen una identificación de cuenta válida."; break;
            
            case 'account_number_invalid':
            return "El número de cuenta bancaria proporcionado no es válido (por ejemplo, faltan dígitos). La información de la cuenta bancaria varía de un país a otro. Recomendamos crear validaciones en sus formularios de inscripción en función de los formatos de cuenta bancaria que proporcionamos."; break;
            
            case 'alipay_upgrade_required':
            return "Este método para crear pagos de Alipay ya no es compatible. Actualice su integración para utilizar Fuentes en su lugar."; break;
            
            case 'amount_too_large':
            return "La cantidad especificada es mayor que la cantidad máxima permitida. Use una cantidad menor y vuelva a intentarlo."; break;
            
            case 'amount_too_small':
            return "La cantidad especificada es menor que la cantidad mínima permitida. Use una cantidad mayor y vuelva a intentarlo."; break;
            
            case 'api_key_expired':
            return "La clave API proporcionada ha caducado. Obtenga sus claves API actuales del Panel de control y actualice su integración para usarlas."; break;
            
            case 'authentication_required':
            return "El pago requiere autenticación para continuar. Si su cliente es fuera de sesión, notifique a su cliente que regrese a su aplicación y complete el pago. Si proporcionó el parámetro error_on_requires_action, su cliente debería probar con otra tarjeta que no requiera autenticación."; break;
            
            case 'balance_insufficient':
            return "La transferencia o el pago no se pudo completar porque la cuenta asociada no tiene suficiente saldo disponible. Cree una nueva transferencia o pago utilizando una cantidad menor o igual al saldo disponible de la cuenta."; break;
            
            case 'bank_account_declined':
            return "La cuenta bancaria proporcionada no se puede utilizar para realizar cargos, ya sea porque aún no está verificada o porque no es compatible."; break;
            
            case 'bank_account_exists':
            return "La cuenta bancaria proporcionada ya existe en el Clienteobjeto. Si la cuenta bancaria también debe adjuntarse a un cliente diferente, incluya el ID de cliente correcto cuando vuelva a realizar la solicitud."; break;
            
            case 'bank_account_unusable':
            return "La cuenta bancaria proporcionada no se puede utilizar para pagos. Se debe utilizar una cuenta bancaria diferente."; break;
            
            case 'bank_account_unverified':
            return "Su plataforma Connect está intentando compartir una cuenta bancaria no verificada con una cuenta conectada."; break;
            
            case 'bank_account_verification_failed':
            return "La cuenta bancaria no se puede verificar, ya sea porque los montos del microdepósito proporcionados no coinciden con los montos reales o porque la verificación ha fallado demasiadas veces."; break;
            
            case 'bitcoin_upgrade_required':
            return "Este método para crear pagos con Bitcoin ya no es compatible. Actualice su integración para utilizar Fuentes en su lugar."; break;
            
            case 'card_decline_rate_limit_exceeded':
            return "Esta tarjeta ha sido rechazada demasiadas veces. Puede intentar cargar esta tarjeta nuevamente después de 24 horas. Le sugerimos que se comunique con su cliente para asegurarse de que haya ingresado toda su información correctamente y de que no haya problemas con su tarjeta."; break;
            
            case 'card_declined':
            return "La tarjeta ha sido rechazada. Cuando se rechaza una tarjeta, el error devuelto también incluye el decline_code atributo con la razón por la que se rechazó la tarjeta. Consulte nuestra documentación de códigos de rechazo para obtener más información."; break;
            
            case 'charge_already_captured':
            return "La carga que intentas capturar ya ha sido capturada. Actualice la solicitud con un ID de cargo no capturado."; break;
            
            case 'charge_already_refunded':
            return "El cargo que está intentando reembolsar ya se reembolsó. Actualice la solicitud para usar el ID de un cargo que no ha sido reembolsado."; break;
            
            case 'charge_disputed':
            return "Se ha devuelto el cargo que está intentando reembolsar . Consulte la documentación de disputas para saber cómo responder a la disputa."; break;
            
            case 'charge_exceeds_source_limit':
            return "Este cargo haría que exceda el límite de procesamiento de la ventana móvil para este tipo de fuente. Vuelva a intentar el cargo más tarde o comuníquese con nosotros para solicitar un límite de procesamiento más alto."; break;
            
            case 'charge_expired_for_capture':
            return "El cargo no se puede capturar porque la autorización ha expirado. Los cargos de autenticación y captura deben capturarse en un plazo de siete días."; break;
            
            case 'charge_invalid_parameter':
            return "No se permitieron uno o más parámetros proporcionados para la operación dada en el Cargo. Consulte nuestro Referencia de API o el mensaje de error devuelto para ver qué valores no eran correctos para ese Cargo."; break;
            
            case 'country_code_invalid':
            return "El código de país proporcionado no es válido."; break;
            
            case 'country_unsupported':
            return "Su plataforma intentó crear una cuenta personalizada en un país que aún no es compatible. Asegúrese de que los usuarios solo puedan registrarse en países admitidos por cuentas personalizadas."; break;
            
            case 'coupon_expired':
            return "El cupón proporcionado para un suscripción o ordenha expirado. Cree un nuevo cupón o use uno existente que sea válido."; break;
            
            case 'customer_max_payment_methods':
            return "El número máximo de Métodos de pago para esto Cliente ha sido conseguido. Ya sea despegar algunos Métodos de pago de este Cliente o proceda con un Cliente diferente."; break;
            
            case 'customer_max_subscriptions':
            return "Se alcanzó el número máximo de suscripciones para un cliente. Contáctanos si recibes este error."; break;
            
            case 'email_invalid':
            return "La dirección de correo electrónico no es válida (por ejemplo, no está formateada correctamente). Verifique que la dirección de correo electrónico esté formateada correctamente y solo incluya los caracteres permitidos."; break;
            
            case 'expired_card':
            return "La tarjeta ha caducado. Verifique la fecha de vencimiento o use una tarjeta diferente."; break;
            
            case 'idempotency_key_in_use':
            return "La clave de idempotencia proporcionada se está utilizando actualmente en otra solicitud. Esto ocurre si su integración está realizando solicitudes duplicadas simultáneamente."; break;
            
            case 'incorrect_address':
            return "La dirección de la tarjeta es incorrecta. Verifique la dirección de la tarjeta o use una tarjeta diferente."; break;
            
            case 'incorrect_cvc':
            return "El código de seguridad de la tarjeta es incorrecto. Verifique el código de seguridad de la tarjeta o use una tarjeta diferente."; break;
            
            case 'incorrect_number':
            return "El número de la tarjeta es incorrecto. Verifique el número de la tarjeta o use una tarjeta diferente."; break;
            
            case 'incorrect_zip':
            return "El código postal de la tarjeta es incorrecto. Verifique el código postal de la tarjeta o use una tarjeta diferente."; break;
            
            case 'instant_payouts_unsupported':
            return "Esta tarjeta no es elegible para pagos instantáneos. Pruebe con una tarjeta de débito de un banco admitido."; break;
            
            case 'intent_invalid_state':
            return "La intención no es el estado que se requiere para realizar la operación."; break;
            
            case 'intent_verification_method_missing':
            return "Intent no tiene un método de verificación especificado en su objeto PaymentMethodOptions."; break;
            
            case 'invalid_card_type':
            return "La tarjeta proporcionada como una cuenta externa no es compatible con los pagos. En su lugar, proporcione una tarjeta de débito no prepaga."; break;
            
            case 'invalid_characters':
            return "Este valor proporcionado al campo contiene caracteres que no son compatibles con el campo."; break;
            
            case 'invalid_charge_amount':
            return "La cantidad especificada no es válida. El monto a cobrar debe ser un número entero positivo en la unidad monetaria más pequeña y no exceder la cantidad mínima o máxima."; break;
            
            case 'invalid_cvc':
            return "El código de seguridad de la tarjeta no es válido. Verifique el código de seguridad de la tarjeta o use una tarjeta diferente."; break;
            
            case 'invalid_expiry_month':
            return "El mes de vencimiento de la tarjeta es incorrecto. Verifique la fecha de vencimiento o use una tarjeta diferente."; break;
            
            case 'invalid_expiry_year':
            return "El año de vencimiento de la tarjeta es incorrecto. Verifique la fecha de vencimiento o use una tarjeta diferente."; break;
            
            case 'invalid_number':
            return "El número de tarjeta no es válido. Verifique los detalles de la tarjeta o use una tarjeta diferente."; break;
            
            case 'invalid_source_usage':
            return "La fuente no se puede utilizar debido a que no está en el estado correcto (por ejemplo, una petición de cargo está intentando utilizar una fuente con una pending, failed o consumed fuente). Comprobar la estado de la fuente que está intentando utilizar."; break;
            
            case 'invoice_no_customer_line_items':
            return "No se puede generar una factura para el cliente especificado porque no hay elementos de factura pendientes. Verifique que se especifique el cliente correcto o cree primero los elementos de factura necesarios."; break;
            
            case 'invoice_no_payment_method_types':
            return "No se puede finalizar una factura porque no hay tipos de métodos de pago disponibles para procesar el pago. La configuración de su plantilla de factura o la payment_settings podría estar restringiendo qué métodos de pago están disponibles, o puede que necesite activar más métodos de pago en el Panel de control."; break;
            
            case 'invoice_no_subscription_line_items':
            return "No se puede generar una factura para la suscripción especificada ya que no hay elementos de factura pendientes. Compruebe que se esté especificando la suscripción correcta o cree primero los elementos de factura necesarios."; break;
            
            case 'invoice_not_editable':
            return "La factura especificada ya no se puede editar. En su lugar, considere crear elementos de factura adicionales que se aplicarán a la próxima factura. Puede generar manualmente la siguiente factura o esperar a que se genere automáticamente al final del ciclo de facturación."; break;
            
            case 'invoice_payment_intent_requires_action':
            return "Este pago requiere una acción adicional del usuario antes de que se pueda completar con éxito. El pago se puede completar utilizando el PaymentIntent asociado con la factura. Consulte esta página para obtener más detalles."; break;
            
            case 'invoice_upcoming_none':
            return "No hay una próxima factura del cliente especificado para obtener una vista previa. Solo los clientes con suscripciones activas o elementos de factura pendientes tienen facturas que se pueden previsualizar."; break;
            
            case 'livemode_mismatch':
            return "Las claves, las solicitudes y los objetos de API de modo de prueba y en vivo solo están disponibles en el modo en el que se encuentran."; break;
            
            case 'lock_timeout':
            return "No se puede acceder a este objeto en este momento porque otra solicitud de API o proceso de Stripe está accediendo actualmente. Si ve este error de forma intermitente, vuelva a intentar la solicitud. Si ve este error con frecuencia y realiza varias solicitudes simultáneas a un solo objeto, haga sus solicitudes en serie o con una frecuencia menor. Consulte la documentación del límite de tarifa para obtener más detalles."; break;
            
            case 'missing':
            return "Se han proporcionado tanto un ID de cliente como de fuente, pero la fuente no se ha guardado para el cliente. Para crear un cargo para un cliente con una fuente específica, primero debe guardar los detalles de la tarjeta ."; break;
            
            case 'not_allowed_on_standard_account':
            return "No se permiten transferencias ni pagos en nombre de una cuenta conectada estándar."; break;
            
            case 'order_creation_failed':
            return "No se pudo crear el pedido. Verifique los detalles del pedido y vuelva a intentarlo."; break;
            
            case 'order_required_settings':
            return "El pedido no se pudo procesar porque falta la información requerida. Verifique la información proporcionada y vuelva a intentarlo."; break;
            
            case 'order_status_invalid':
            return "El orden no puede ser actualizadoporque el estado proporcionado no es válido o no sigue el ciclo de vida del pedido (p. ej., un pedido no puede pasar de created a fulfilled sin primero hacer la transición a paid)."; break;
            
            case 'order_upstream_timeout':
            return "La petición caducó. Inténtelo de nuevo más tarde."; break;
            
            case 'out_of_inventory':
            return "El SKU está agotado. Si hay más stock disponible, actualice la cantidad de inventario de SKU e intente nuevamente."; break;
            
            case 'parameter_invalid_empty':
            return "No se proporcionaron uno o más valores obligatorios. Asegúrese de que las solicitudes incluyan todos los parámetros necesarios."; break;
            
            case 'parameter_invalid_integer':
            return "Uno o más de los parámetros requieren un número entero, pero los valores proporcionados eran de un tipo diferente. Asegúrese de que solo se proporcionen valores admitidos para cada atributo. Consulte nuestra documentación de API para buscar el tipo de datos que admite cada atributo."; break;
            
            case 'parameter_invalid_string_blank':
            return "Uno o más valores proporcionados solo incluían espacios en blanco. Verifique los valores en su solicitud y actualice los que contengan solo espacios en blanco."; break;
            
            case 'parameter_invalid_string_empty':
            return "Uno o más valores de cadena obligatorios están vacíos. Asegúrese de que los valores de cadena contengan al menos un carácter."; break;
            
            case 'parameter_missing':
            return "Faltan uno o más valores obligatorios. Consulte nuestro Documentación API para ver qué valores son necesarios para crear o modificar el recurso especificado."; break;
            
            case 'parameter_unknown':
            return "La solicitud contiene uno o más parámetros inesperados. Elimínelos y vuelva a intentarlo."; break;
            
            case 'parameters_exclusive':
            return "Se proporcionaron dos o más parámetros mutuamente excluyentes. Consulte nuestro Documentación API o el mensaje de error devuelto para ver qué valores están permitidos al crear o modificar el recurso especificado."; break;
            
            case 'payment_intent_action_required':
            return "El método de pago proporcionado requiere que el cliente complete las acciones, pero <code>error_on_requires_action':se configuró. Si desea agregar este método de pago a su integración, le recomendamos que primero actualice su integración para manejar las acciones ."; break;
            
            case 'payment_intent_authentication_failure':
            return "El método de pago proporcionado ha fallado la autenticación. Proporcione un nuevo método de pago para intentar cumplir este PaymentIntent nuevamente."; break;
            
            case 'payment_intent_incompatible_payment_method':
            return "PaymentIntent esperaba un método de pago con propiedades diferentes a las proporcionadas."; break;
            
            case 'payment_intent_invalid_parameter':
            return "Uno o más parámetros proporcionados no se permitieron para la operación dada en PaymentIntent. Consulte nuestro Referencia de API o el mensaje de error devuelto para ver qué valores no eran correctos para ese PaymentIntent."; break;
            
            case 'payment_intent_payment_attempt_failed':
            return "El último intento de pago para PaymentIntent ha fallado. Comprobar la last_payment_error propiedad en el PaymentIntent para obtener más detalles, y proporcione un nuevo método de pago para intentar cumplir este PaymentIntent nuevamente."; break;
            
            case 'payment_intent_unexpected_state':
            return "El estado de PaymentIntent era incompatible con la operación que intentaba realizar."; break;
            
            case 'payment_method_invalid_parameter':
            return "Se proporcionó un parámetro no válido en el objeto de método de pago. Consulte nuestro Documentación API o el mensaje de error devuelto para más contexto."; break;
            
            case 'payment_method_provider_decline':
            return "El pago fue rechazado por el emisor o el cliente. Comprobar la last_payment_error propiedad en el PaymentIntent para obtener más detalles, y proporcione un nuevo método de pago para intentar cumplir este PaymentIntent nuevamente."; break;
            
            case 'payment_method_provider_timeout':
            return "El método de pago falló debido a un tiempo de espera. Comprobar la last_payment_error propiedad en el PaymentIntent para obtener más detalles, y proporcione un nuevo método de pago para intentar cumplir este PaymentIntent nuevamente."; break;
            
            case 'payment_method_unactivated':
            return "La operación no se puede realizar porque la forma de pago utilizada no ha sido activada. Active el método de pago en el Panel de control y vuelva a intentarlo."; break;
            
            case 'payment_method_unexpected_state':
            return "El estado del método de pago proporcionado no era compatible con la operación que intentaba realizar. Confirme que el método de pago esté en un estado permitido para la operación dada antes de intentar realizarla."; break;
            
            case 'payouts_not_allowed':
            return "Los pagos se han desactivado en la cuenta conectada. Verifique el estado de la cuenta conectada para ver si es necesario proporcionar información adicional o si los pagos se han desactivado por otro motivo ."; break;
            
            case 'platform_api_key_expired':
            return "La clave API proporcionada por su plataforma Connect ha caducado. Esto ocurre si su plataforma ha generado una nueva clave o la cuenta conectada se ha desconectado de la plataforma. Obtenga sus claves API actuales del Panel y actualice su integración, o comuníquese con el usuario y vuelva a conectar la cuenta."; break;
            
            case 'postal_code_invalid':
            return "El código postal proporcionado es incorrecto."; break;
            
            case 'processing_error':
            return "Se produjo un error al procesar la tarjeta. Vuelve a intentarlo más tarde o con otro método de pago."; break;
            
            case 'product_inactive':
            return "El producto al que pertenece este SKU ya no está disponible para su compra."; break;
            
            case 'rate_limit':
            return "Demasiadas solicitudes llegan a la API demasiado rápido. Recomendamos una retirada exponencial de sus solicitudes."; break;
            
            case 'resource_already_exists':
            return "Ya existe un recurso con un ID especificado por el usuario (p. Ej., Plan o cupón). Utilice un valor único y diferente para id e inténtelo de nuevo."; break;
            
            case 'resource_missing':
            return "La identificación proporcionada no es válida. El recurso no existe o se ha proporcionado un ID para un recurso diferente."; break;
            
            case 'routing_number_invalid':
            return "El número de ruta bancaria proporcionado no es válido."; break;
            
            case 'secret_key_required':
            return "La clave API proporcionada es una clave publicable, pero se requiere una clave secreta. Obtenga sus claves API actuales del Panel de control y actualice su integración para usarlas."; break;
            
            case 'sepa_unsupported_account':
            return "Su cuenta no admite pagos SEPA."; break;
            
            case 'setup_attempt_failed':
            return "El último intento de instalación de SetupIntent ha fallado. Consulte la last_setup_error propiedad en SetupIntent para obtener más detalles y proporcione un nuevo método de pago para intentar configurarlo nuevamente."; break;
            
            case 'setup_intent_authentication_failure':
            return "El método de pago proporcionado ha fallado la autenticación. Proporcione un nuevo método de pago para intentar cumplir este SetupIntent nuevamente."; break;
            
            case 'setup_intent_invalid_parameter':
            return "Uno o más parámetros proporcionados no se permitieron para la operación dada en SetupIntent. Consulte nuestro Referencia de API o el mensaje de error devuelto para ver qué valores no eran correctos para ese SetupIntent."; break;
            
            case 'setup_intent_unexpected_state':
            return "El estado de SetupIntent era incompatible con la operación que intentaba realizar."; break;
            
            case 'shipping_calculation_failed':
            return "El cálculo del envío falló porque la información proporcionada era incorrecta o no se pudo verificar."; break;
            
            case 'sku_inactive':
            return "El SKU está inactivo y ya no está disponible para su compra. Use un SKU diferente o active el SKU actual nuevamente."; break;
            
            case 'state_unsupported':
            return "Ocurre al proporcionar la legal_entity información de una cuenta personalizada de EE. UU., Si el estado proporcionado no es compatible. (Se trata principalmente de estados y territorios asociados)."; break;
            
            case 'tax_id_invalid':
            return "El número de identificación fiscal proporcionado no es válido (por ejemplo, faltan dígitos). La información de identificación fiscal varía de un país a otro, pero debe tener al menos nueve dígitos."; break;
            
            case 'taxes_calculation_failed':
            return "Error en el cálculo de impuestos para el pedido."; break;
            
            case 'terminal_location_country_unsupported':
            return "Actualmente, la terminal solo está disponible en algunos países. No se pueden crear ubicaciones en su país en modo en vivo."; break;
            
            case 'testmode_charges_only':
            return "Su cuenta no ha sido activada y solo puede realizar cargos de prueba. Active su cuenta en el Panel de control para comenzar a procesar cargos reales."; break;
            
            case 'tls_version_unsupported':
            return "Su integración utiliza una versión anterior de TLS que no es compatible. Debe utilizar TLS 1.2 o superior."; break;
            
            case 'token_already_used':
            return "El token proporcionado ya se ha utilizado. Debes crear un nuevo token antes de poder volver a intentar esta solicitud."; break;
            
            case 'token_in_use':
            return "El token proporcionado se está utilizando actualmente en otra solicitud. Esto ocurre si su integración está realizando solicitudes duplicadas simultáneamente."; break;
            
            case 'transfers_not_allowed':
            return "No se puede crear la transferencia solicitada. Contáctanos si recibes este error."; break;
            
            case 'upstream_order_creation_failed':
            return "No se pudo crear el pedido. Verifique los detalles del pedido y vuelva a intentarlo."; break;
            
            case 'url_invalid':
            return "La URL proporcionada no es válida."; break;   
            
            case 'approve_with_id':
            return "No se puede autorizar el pago."; break;
            
            case 'call_issuer':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'card_not_supported':
            return "La tarjeta no admite este tipo de compra."; break;
            
            case 'card_velocity_exceeded':
            return "El cliente ha excedido el saldo o el límite de crédito disponible en su tarjeta."; break;
            
            case 'currency_not_supported':
            return "La tarjeta no admite la moneda especificada."; break;
            
            case 'do_not_honor':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'do_not_try_again':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'duplicate_transaction':
            return "Recientemente, se envió una transacción con la misma cantidad e información de tarjeta de crédito."; break;
            
            case 'expired_card':
            return "La tarjeta ha caducado."; break;
            
            case 'fraudulent':
            return "El pago ha sido rechazado porque Stripe sospecha que es fraudulento."; break;
            
            case 'generic_decline':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'incorrect_number':
            return "El número de la tarjeta es incorrecto."; break;
            
            case 'incorrect_cvc':
            return "El número CVC es incorrecto."; break;
            
            case 'incorrect_pin':
            return "El PIN ingresado es incorrecto. Este código de rechazo solo se aplica a los pagos realizados con un lector de tarjetas."; break;
            
            case 'incorrect_zip':
            return "El código postal es incorrecto."; break;
            
            case 'insufficient_funds':
            return "La tarjeta no tiene fondos suficientes para completar la compra."; break;
            
            case 'invalid_account':
            return "La tarjeta, o cuenta a la que está conectada, no es válida."; break;
            
            case 'invalid_amount':
            return "El monto del pago no es válido o excede el monto permitido."; break;
            
            case 'invalid_cvc':
            return "El número CVC es incorrecto."; break;
            
            case 'invalid_expiry_month':
            return "El mes de vencimiento no es válido."; break;
            
            case 'invalid_expiry_year':
            return "El año de vencimiento no es válido."; break;
            
            case 'invalid_number':
            return "El número de la tarjeta es incorrecto."; break;
            
            case 'invalid_pin':
            return "El PIN ingresado es incorrecto. Este código de rechazo solo se aplica a los pagos realizados con un lector de tarjetas."; break;
            
            case 'issuer_not_available':
            return "No se pudo contactar al emisor de la tarjeta, por lo que no se pudo autorizar el pago."; break;
            
            case 'lost_card':
            return "Se ha rechazado el pago porque se informó que la tarjeta se perdió."; break;
            
            case 'merchant_blacklist':
            return "El pago ha sido rechazado porque coincide con un valor en la lista de bloqueo del usuario de Stripe."; break;
            
            case 'new_account_information_available':
            return "La tarjeta, o cuenta a la que está conectada, no es válida."; break;
            
            case 'no_action_taken':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'not_permitted':
            return "No se permite el pago."; break;
            
            case 'offline_pin_required':
            return "La tarjeta ha sido rechazada porque requiere un PIN."; break;
            
            case 'online_or_offline_pin_required':
            return "La tarjeta ha sido rechazada porque requiere un PIN."; break;
            
            case 'pickup_card':
            return "La tarjeta no se puede utilizar para realizar este pago (es posible que haya sido denunciada como perdida o robada)."; break;
            
            case 'pin_try_exceeded':
            return "Se superó el número permitido de intentos de PIN."; break;
            
            case 'processing_error':
            return "Se produjo un error al procesar la tarjeta."; break;
            
            case 'reenter_transaction':
            return "El pago no pudo ser procesado por el emisor por un motivo desconocido."; break;
            
            case 'restricted_card':
            return "La tarjeta no se puede utilizar para realizar este pago (es posible que haya sido denunciada como perdida o robada)."; break;
            
            case 'revocation_of_all_authorizations':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'revocation_of_authorization':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'security_violation':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'service_not_allowed':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'stolen_card':
            return "El pago se ha rechazado porque se denuncia el robo de la tarjeta."; break;
            
            case 'stop_payment_order':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'testmode_decline':
            return "Se utilizó un número de tarjeta de prueba de Stripe."; break;
            
            case 'transaction_not_allowed':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'try_again_later':
            return "La tarjeta ha sido rechazada por un motivo desconocido."; break;
            
            case 'withdrawal_count_limit_exceeded':
            return "El cliente ha excedido el saldo o el límite de crédito disponible en su tarjeta."; break;            
            default:
                return "Error Genérico";
                break;
        }        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function ValidarCliente($id)
    {
        $user= User::select('users.*')
            ->where('dni', $id)
            ->first();
        return response()->json(["data" => $user]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Stripe  $stripe
     * @return \Illuminate\Http\Response
     */
    public function show(Stripe $stripe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Stripe  $stripe
     * @return \Illuminate\Http\Response
     */
    public function edit(Stripe $stripe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Stripe  $stripe
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stripe $stripe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Stripe  $stripe
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stripe $stripe)
    {
        //
    }

    public function insertLog($accion,$trans_id,$visual = null){
         /*log de auditoria*/
            if($visual == "BCO")
            {
                $accion = "[Fintech] " . $accion;
            }

            if($visual == "CRESKA")
            {
                $accion = "[Creska] " . $accion;
                $visual = "BCO";
            }
            
            if(isset(Auth::user()->id))
            {
                $UserId = Auth::user()->id;
            }
            else
            {
                $transacciones = trans_head::select('trans_head.fk_dni_miembros')
                ->where('trans_head.id',$trans_id)
                ->first(); 
                
                $UserId = $transacciones->fk_dni_miembros;
            }
                        
            $user= User::find($UserId); 
            $id_user=$user->id;
            $email = $user->email;
            $ip = \Request::ip();

            $log = new \App\Models\log_trans();
            $log->user_id = $id_user;
            $log->username = $email;
            $log->trans_id = $trans_id;
            $log->accion = $accion;
            $log->ip = $ip;
            $log->visual = $visual;
            $log->save();
    }
}
