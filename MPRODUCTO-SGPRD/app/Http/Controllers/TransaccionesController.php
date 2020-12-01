<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Config\webConfig;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\emisores;
use App\Models\trans_head;
use App\Models\trans_body;
use App\Models\User;
use App\Models\comercios;
use App\Models\bancos;
use App\Models\carnet;
use App\Models\miem_come;
use App\Models\Automatic_Files;
use App\Models\Files_History;
use App\Models\banc_comer;
use App\Models\miem_ban;
use App\Models\trans_gift_card;
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
use App\Mail\autorizacionTransEmail;
use App\Mail\autorizacionTransAlComercioEmail;
use App\Mail\AutorizacionFallidaFintech;
use App\Mail\CompraGiftCardEmail;
use App\Soap\GetSendSms;
use App\Soap\GetSendSmsResponse;
use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Facades\Input;
use GuzzleHttp\Client;


class TransaccionesController extends Controller
{
    protected $soapWrapper;

    public function __construct(SoapWrapper $soapWrapper)
    {
        $this->soapWrapper = $soapWrapper;
        $this->middleware('auth', ['except' => [
            'linkAuth',
			'linkAuthSms',
			'SendNotificationTransactionCreate',
			'SendNotificationTransactionAuthorized',
			'SendNotificationTransactionAuthorizedToCommerce',
			'SendNotificationTransactionFailed',
        ]]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function insertLog($accion,$trans_id,$visual = null){
         /*log de auditoria*/
			if($visual == "BCO")
			{
				$accion = "[Fintech] " . $accion;
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
    
    public function index(Request $request)
    {

        $user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }
        //dd($rol);
        // dd($request->get('fechaIni'));
        // $fachaTrans = trans_head::fecha($request->get('fecha'))->get();
        $fechaActual = date('Y-m-d');
        /*$time_desde= date('Y-m-d H:m:s');
        $fecha_hasta= date('Y-m-d H:m:s');
        $fecha_hasta = strtotime($fecha_hasta."+1 days");
        $time_hasta = date('Y-m-d H:m:s',$fecha_hasta);*/

        $time_desde= date('Y-m-d 00:00:00');
        $time_hasta= date('Y-m-d 23:59:59');


        if($rol == 3){

            $IdsComercios = array();

            $comercio =  miem_come::select("miem_come.fk_id_comercio",'comercios.rif','comercios.es_sucursal','comercios.id')
            ->join('comercios','comercios.id','miem_come.fk_id_comercio')
            ->where("fk_id_miembro",$user->id)            
            ->first();

            $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))
            ->where('rif','=',$comercio->rif)
            ->get();

            //VALIDAR SI ES COMERCIO MASTER
            if(count($comercios) > 0 && !$comercio->es_sucursal)
            {
                $EsComercioMaster = true;

                foreach ($comercios as $key => $value) {
                    array_push($IdsComercios, $value->id);
                }                
            }
            else
            {
                array_push($IdsComercios, $comercio->id);
            }            

            $transacciones = trans_head::select(
                'trans_head.id as idTrans',
                'trans_head.status',
                'trans_head.reverso',
                'trans_head.monto',
                'trans_head.propina',
                'trans_head.token_status',
                'trans_head.procesado',
                'trans_head.fk_dni_miembros','trans_head.fk_monedas',
                'trans_head.created_at as fechaTrans',
                'trans_head.updated_at as fechaTransU',
                'trans_head.origen',
                'monedas.mon_nombre as moneda',

                'users.*',
                //'carnet.*',
                'comercios.id',
				'carnet.carnet as carnet_cliente',
				'emisores.requiere_pin',
                DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcioncomercios"),
                //'comercios.descripcion as descripcionComercios',
                'bancos.id',
                'bancos.descripcion as descripcionBancos',
                'terminal.codigo_terminal_comercio')
                ->join('users','users.id','trans_head.fk_dni_miembros')
                ->join('carnet','carnet.id','trans_head.carnet_id')
                ->join('comercios','comercios.id','trans_head.fk_id_comer')
                ->join('bancos','bancos.id','trans_head.fk_id_banco')
                ->join('monedas','monedas.mon_id','trans_head.fk_monedas')
                ->leftJoin('terminal', 'terminal.id', '=', 'trans_head.TerminalId')
				->leftJoin('trans_gift_card', 'trans_gift_card.fk_trans_id', '=', 'trans_head.id')
				->leftJoin('carnet as carnet_gift', 'carnet_gift.id', '=', 'trans_gift_card.fk_carnet_id_recibe')
				->leftJoin('emisores', 'emisores.cod_emisor', '=', 'carnet_gift.cod_emisor')
                //->where('comercios.id',$comercio->id)
                ->whereIn("comercios.id",$IdsComercios)
                ->where('comercios.razon_social','!=','jackpotImportPagos')
                ->whereBetween('trans_head.created_at',array(
                            $time_desde,
                            $time_hasta
                ))
                ->orderBy('idTrans', 'DESC')
                ->get();

        }else{
            $transacciones = trans_head::select('trans_head.id as idTrans', 'trans_head.carnet_id', 'trans_head.status','trans_head.fk_dni_miembros','trans_head.fk_monedas','trans_head.propina',  'monedas.mon_nombre as moneda', 'trans_head.reverso', 'trans_head.monto','trans_head.procesado', 'trans_head.token_status', 'trans_head.created_at as fechaTrans','trans_head.origen','trans_head.updated_at as fechaTransU', 'users.*', 'comercios.id',DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcioncomercios"),'bancos.id','bancos.descripcion as descripcionBancos','terminal.codigo_terminal_comercio','carnet.carnet as carnet_cliente','emisores.requiere_pin')
                ->join('users','users.id','trans_head.fk_dni_miembros')
                ->join('carnet','carnet.id','trans_head.carnet_id')
                ->join('comercios','comercios.id','trans_head.fk_id_comer')
                ->join('bancos','bancos.id','trans_head.fk_id_banco')
                ->join('monedas','monedas.mon_id','trans_head.fk_monedas')
                ->leftJoin('terminal', 'terminal.id', '=', 'trans_head.TerminalId')
				->leftJoin('trans_gift_card', 'trans_gift_card.fk_trans_id', '=', 'trans_head.id')
				->leftJoin('carnet as carnet_gift', 'carnet_gift.id', '=', 'trans_gift_card.fk_carnet_id_recibe')
				->leftJoin('emisores', 'emisores.cod_emisor', '=', 'carnet_gift.cod_emisor')				
                ->where('comercios.razon_social','!=','jackpotImportPagos')
                ->whereBetween('trans_head.created_at',array(
                            $time_desde,
                            $time_hasta
                
                ))

                ->orderBy('idTrans', 'DESC')
                // ->where('trans_head.created_at', 'like' ,'%'.$fechaActual.'%')
                ->get();
        }

        //dd($transacciones);

        //$monto=$transacciones[0] -> monto;
        //$transacciones->each(function($trans){
            
            //$trans->carnet = carnet::where([
              //  ['fk_monedas', '=', $trans->fk_monedas],
               // ['id', '=', $trans->carnet_id]
            //])->first();
        //});

        $bancos = bancos::select('bancos.*')
        ->get();

        return view('transacciones.index')->with(['transacciones' => $transacciones, 'bancos' => $bancos, 'fechaActual' => $fechaActual, 'rol' => $rol]);
    }
	
	public function enviar_sms($telefono,$mensaje)
	{
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
						"mensaje" => $mensaje,
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
				new GetSendSms('VXBANPLUS', 'H41H30E0', $telefono, $mensaje)
				]);                     
		} 		
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function create()
     {
         Log::info('Ingreso exitoso a BancoController - create(), del usuario: '.Auth::user()->first_name);

         $user= User::find(Auth::user()->id);

         $roles= $user->roles;
         $rol = null;
         foreach ($roles as $value) {
             $rol = $value->id;
         }
		
         if($rol == 3){			
             $Comercio = miem_come::select('comercios.es_sucursal','comercios.rif','comercios.id')
             ->join('comercios','comercios.id','miem_come.fk_id_comercio')
             ->where('miem_come.fk_id_miembro',Auth::user()->id)
             ->first();
             
             if(!$Comercio->es_sucursal)
             {
                 $Sucursales = comercios::select('id')
                 ->where('rif','=',$Comercio->rif)
                 ->where('id','!=',$Comercio->id)                 
                 ->get();
                 
                 if(count($Sucursales) > 0)
                 {
                    return redirect()->route('transacciones.index');
                 }
             }
         }         

        $comercios = comercios::select('comercios.id',
        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))
        ->where('rif','!=','J-0000000000')
        ->where('posee_sucursales','=',false)
         ->get();

         $time_hasta= date('Y-m-d 23:59:59');
         $time_desde= date('Y-m-d 00:00:00', strtotime(' - 31 days'));

         $transacciones = trans_head::select('trans_head.id as idTrans', 'trans_head.monto', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos')
         ->join('users','users.id','trans_head.fk_dni_miembros')
         ->join('carnet','carnet.fk_id_miembro','users.id')
         ->join('comercios','comercios.id','trans_head.fk_id_comer')
         ->join('bancos','bancos.id','trans_head.fk_id_banco')
         ->whereBetween('trans_head.created_at', [$time_desde, $time_hasta])
         ->orderByRaw('trans_head.id DESC');

         $fk_id_comer= miem_come::select('miem_come.id','miem_come.fk_id_comercio')
         ->where('fk_id_miembro',$user->id)
         ->first();

         if ($rol != 4) {
         $comercio = comercios::select('comercios.*')
         ->where('id',$fk_id_comer->fk_id_comercio)
         ->first();
         }else{
             $comercio = '';
         }
         $bancos = bancos::select('bancos.*')
         ->get();

         // dd($bancos);


        $monedas= Moneda::all();

        return view('transacciones.create',compact('transacciones','bancos','rol','comercios','comercio', 'monedas'));
    }



      public function getbanco($id){

        $bancos = carnet::select('carnet.id as carnetId', 'carnet.carnet', 'carnet.fk_id_banco', 'bancos.id as bancosId', 'bancos.descripcion')
        ->join('bancos','bancos.id','carnet.fk_id_banco')
        ->where('carnet', $id)->get();
         // dd($bancos);


        // dd($countries);
        return response()->json([
                   'data'      => $bancos
               ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    //dd($request);
      try{
          //dd($request);

        $token = mt_rand(100000, 999999);

// dd($token);
        $token_encrypt=Crypt::encrypt($token);
  // dd($token_encrypt);


// $token_descrypt = Crypt::decrypt($token_encrypt);

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
        ->join('carnet','carnet.fk_id_miembro','users.id')
        ->where('dni',$request->cedula)
        ->first();



        if(!$existing_user){

            return redirect()->route('transacciones.create')
              ->with('error','No existe la cédula del cliente.');
        }

        $existing_carnet= carnet::select('carnet.*','users.*', 'carnet.id as carnet_id')
        ->join('users','users.id','carnet.fk_id_miembro')
        ->where('carnet',$request->carnet)
        ->where('fk_id_miembro',$existing_user->idMiembros)
        ->first();

        ///CONSULTAR LAS APIS DEL BANCO
        if($request->cod_emisor == "174")
        {
            $response = $this->GenerateBankOTP(Moneda::find(carnet::find($existing_carnet->carnet_id)->fk_monedas)->mon_simbolo, $existing_user->nacionalidad, $existing_user->dni);

            if(isset($response['code'], $response['message']))
            {
                if($response['code'] == 4604)
                return redirect()->route('transacciones.create')->with('error','Producto no afiliado.');

                if($response['code'] == 3447)
                return redirect()->route('transacciones.create')->with('error','El cliente no posee cuenta registrada en el tipo de moneda seleccionada.');

                if($response['code'] == 500)
                return redirect()->route('transacciones.create')->with('error','Intente crear la transacción nuevamente.');
            }
            else{
                if(isset($response['otp'], $response['expiresOn']))
                {
                    $otpBCO = Crypt::encrypt($response['otp']);

                    $expiraOtpBco = $response['expiresOn'];
                }
            }

            if($expiraOtpBco == null || $otpBCO == null || $expiraOtpBco == "" || $otpBCO == "")
            {
                return redirect()->route('transacciones.create')->with('error','No se pueden procesar transacciones para este producto en este momento.');
            }
        }
        ///FIN
        


        $fk_id_comer= miem_come::select('miem_come.id','miem_come.fk_id_comercio')
        ->where('fk_id_miembro',$iduser)
        ->first();

    if ($existing_user && $existing_carnet)

        if (!empty($request -> monto)) {
                # Tiene data...
            }else{
                $request -> monto = 0;
        }

            $subtotal = str_replace(".", "",$request -> monto);
            $subtotal = str_replace(",", ".",$subtotal);
            $porcent_max= $subtotal*0.30;


            if ($request -> monto == 0) {
                //dd('El campo Monto es Obligatorio');
                $amount_cero = false;
            }else{
                $amount_cero = true;
            }

            if (str_replace(".", "",$request -> propina_monto) > $porcent_max) {
                    $verify_amount = false;
                }else{
                    $verify_amount = true;
            }

            /* Propinas */
                if(!empty($request -> propina)){
                    if ($request -> propina == 1) {
                        $porcentaje = str_replace(".", "",$request -> monto);
                        $porcentaje = str_replace(",", ".",$porcentaje)*0.05;
                    }
                    if ($request -> propina == 2) {
                        $porcentaje = str_replace(".", "",$request -> monto);
                        $porcentaje = str_replace(",", ".",$porcentaje)*0.10;
                    }
                    if ($request -> propina == 3) {
                        $porcentaje = str_replace(".", "",$request -> monto);
                        $porcentaje = str_replace(",", ".",$porcentaje)*0.15;
                    }
                    if ($request -> propina == 4) {
                        $porcentaje = str_replace(".", "",$request -> propina_monto);
                        $porcentaje = str_replace(",", ".",$porcentaje);
                    }
                }else{
                    $porcentaje = 0;
                }

            /* FIN Propinas */

            /* Limites */

            if ($existing_carnet) {

                $findLedge = Ledge::where('fk_dni_miembros',$existing_user->idMiembros)
                            ->where('carnet_id',$existing_carnet->carnet_id)
                            //->orderBy('fk_id_trans_head', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();


                if (!empty($findLedge)){
                    $preLimite = intval($findLedge->disp_post);
                }else{
                    $pre = intval($existing_carnet->limite);
                    $preLimite = $pre;
                }

                /*SE COMENTA LA VALIDACION CONTRA EL LIMITE*/
                /*$limite = $preLimite - $subtotal - $porcentaje;

                if ($limite < 0) {
                    $limite = false;
                }else{
                    $limite = true;
                }*/
                /*SE COMENTA LA VALIDACION CONTRA EL LIMITE*/

                /*VALIDACION POR DISPONIBLE*/
                /*if($subtotal < $findLedge->disp_post){
                    $disponible = true;
                }else{
                    $disponible = false;
                } */

                /*FIN DE VALIDACION POR DISPONIBLE*/

            }
                //dd($limite);
            /* FIN Limites */

        /*se toma la ip del cliente para registrar en base de datos*/
        $clientIP = \Request::ip();

        if ($existing_user && $existing_carnet && $verify_amount && $amount_cero /*&& $limite && $disponible*/){


                $monto = str_replace(".", "",$request -> monto);
                $monto = str_replace(",", ".",$monto);

                $trans_head = new \App\Models\trans_head();
                $trans_head->fk_dni_miembros = $existing_user-> idMiembros;
                $trans_head->fk_id_banco  = /*$request -> fk_id_banco*/ 1;
                if($rol == 4){
                    $trans_head->fk_id_comer  = $request -> fk_id_comercio;
                    $trans_head->origen  = "Cobro (BCO)";
                }else{
                    $trans_head->fk_id_comer = $fk_id_comer -> fk_id_comercio;
                    $trans_head->origen  = "Cobro";
                }
				
				//MARCAR COMPRA GIFT
				if(isset($request->gift_card))
				{
					$trans_head->origen  = "Cobro (Gift)";
					$trans_head->rompe_liquidacion  = 2;
				}
				
                $trans_head->monto        = $monto;
                $trans_head->cancela_a    = 0;
                $trans_head->token    = $token_encrypt;
				
				//TRANSACCION AUTORIZADA SI ES GIFT
				if(isset($request->gift_card))
				{
					//SI ES PRODUCTO OTROS
					if($request->tipo_producto == "Otros")
					{
						if($request->requiere_pin)
						{
							$trans_head->status  = 1;
							$trans_head->token = $request->pin;
						}
						else{
							$trans_head->status  = 0;
						}						
					}
					else{
						$trans_head->status  = 1;
					}
				}
				else{
					//SI ES PRODUCTO PRESIDENTS					
					$trans_head->status  = 1;
				}
                
                $trans_head->ip     = $clientIP;
                $trans_head->token_status    = 0;
                $trans_head->token_time    = $extra_min;
                $trans_head->propina = $porcentaje;
                $trans_head->neto = ($trans_head->propina+$monto);
                $trans_head->fk_monedas = $existing_carnet->fk_monedas;
                $trans_head->carnet_id = $existing_carnet->carnet_id;
                $trans_head->otp_bco = $otpBCO;
                $trans_head->otp_bco_time = $expiraOtpBco;


            if( $trans_head->save()){
                /*if (empty($findLedge)){
                    $limit = intval($existing_carnet->limite);
                }
                dd($limit);*/

                $ledge = new \App\Models\Ledge();
                $ledge->fk_id_trans_head= $trans_head->id;
                $ledge->fk_dni_miembros= $trans_head->fk_dni_miembros;
                $ledge->monto= $trans_head->monto+$porcentaje;
                $ledge->propina= $porcentaje;
                $ledge->disp_pre= $preLimite;
                $ledge->carnet_id = $existing_carnet->carnet_id;
                $ledge->disp_post = $ledge->disp_pre;

                //$ledge->disp_post= $preLimite - $subtotal - $porcentaje;

                if ($ledge->save()) {

                $transacciones = trans_head::select('trans_head.id as idTrans', 'trans_head.monto','trans_head.propina', 'trans_head.created_at as fechaTrans', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos')
                ->join('users','users.id','trans_head.fk_dni_miembros')
                ->join('carnet','carnet.id','trans_head.carnet_id')
                ->join('comercios','comercios.id','trans_head.fk_id_comer')
                ->join('bancos','bancos.id','trans_head.fk_id_banco')
                ->where('trans_head.id' ,$trans_head->id)
                ->first();
                
                $this->insertLog('Transacción creada exitosamente',$trans_head->id);                

        // if(isset($transacciones -> descripcionComercios)){
           $desc_comercio= $transacciones -> descripcionComercios;
        // }
        $idTrans= $transacciones-> idTrans;
        $first_name= $transacciones -> first_name;
        $last_name= $transacciones -> last_name;
        $montos = $transacciones -> monto + $transacciones -> propina;
        $montos = number_format($montos , 2, ',', '.');
        $telefono = $transacciones-> cod_tel.''.$transacciones-> num_tel;

        //CREAR ASOCIACION DE GIFTCARD
        if(isset($request->gift_card))
        {
            $fecha_vencimiento = Carbon::now();
            $fecha_vencimiento = $fecha_vencimiento->addDays($request->dias_vencimiento);

            $giftcard_parameters = emisores::select('emisores.id','emisores.paga_comision')->where('emisores.id','=',$request->giftcard_id)->first();
            
            trans_gift_card::create([
                                    'fk_trans_id' => $idTrans,
                                    'fk_dni_recibe' => $request->fk_dni_recibe,
                                    'fk_carnet_id_recibe' => $request->fk_carnet_id_recibe,
                                    'monto' => str_replace(",", ".",$request->monto_original),
                                    'comision_monto' => str_replace(",", ".",$request->comision_monto),
                                    'vencimiento' => $fecha_vencimiento,
                                    'pago_comision' => $giftcard_parameters->paga_comision,
                                    'giftcard_id' => $giftcard_parameters->id,                                    
									'imagen' => $request->giftcard_imagen,
                                ]);
							
			//SI ES PRODUCTO OTROS REDIRIGIR AL INDEX
			if($request->tipo_producto == "Otros")
			{
				if(!$request->requiere_pin)
				{
					//VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD
					$datos_gift = trans_gift_card::select('trans_gift_card.fk_carnet_id_recibe','trans_gift_card.monto','trans_gift_card.imagen','trans_gift_card.vencimiento')
					->Where('trans_gift_card.fk_trans_id', '=', $idTrans)
					->first();

					if($datos_gift)
					{
						$caret_gift = carnet::select('carnet.disponible','carnet.cod_emisor','users.cod_tel','users.num_tel','users.first_name','users.first_name','users.last_name','users.email','users.nacionalidad','users.dni')
						->join('users','carnet.fk_id_miembro','users.id')
						->Where('carnet.id', '=', $datos_gift->fk_carnet_id_recibe)
						->first();

						$disponible_final_gift_card = $caret_gift->disponible + $datos_gift->monto;

						carnet::where('id',$datos_gift->fk_carnet_id_recibe)
						->update([
							'disponible' => $disponible_final_gift_card,
							'limite' => $disponible_final_gift_card,
							'transar' => true,
						]);

						$gift = emisores::select('emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
						->join('monedas','emisores.fk_monedas','monedas.mon_id')
						->Where('emisores.cod_emisor', '=', $caret_gift->cod_emisor)
						->first();
						
						$telefono = $caret_gift->cod_tel.''.$caret_gift->num_tel;
						
						$datos_pagador = User::select('users.first_name','users.last_name','users.email')
						->join('trans_head','trans_head.fk_dni_miembros','users.id')
						->Where('trans_head.id', '=', $idTrans)
						->first();

						$paraCedula = $caret_gift->nacionalidad.'-'.$caret_gift->dni;
						$paraEmail = $caret_gift->email;
						$paraTelefono = str_replace("58","0",$caret_gift->cod_tel.'-'.$caret_gift->num_tel);
						$imagen = $datos_gift->imagen;
						$vencimiento = $datos_gift->vencimiento;
						
						$bcc = array(config('webConfig.email'),config('webConfig.bcc'));
						Mail::to($caret_gift->email)->bcc($bcc)->send(new CompraGiftCardEmail($datos_gift->monto, $gift->mon_simbolo, $gift->emisor, $datos_pagador->first_name.' '.$datos_pagador->last_name, $caret_gift->first_name.' '.$caret_gift->last_name, $imagen, $paraCedula, $paraEmail, $paraTelefono, $vencimiento));						
						
						
						$fecha_vencimiento = \Carbon\Carbon::createFromTimeStamp(strtotime($vencimiento))->format('d-m-Y');
						
						$fecha_corta = $this->fecha_corta($fecha_vencimiento);
						
						$array_email = explode("@",$caret_gift->email);
						
						$email_receptor = str_pad(substr($array_email[0],0,1), (strlen($array_email[0]) - 3) ,"*") . "***" .substr($array_email[0],-1). "@" . $array_email[1];						
						
						$mensaje_giftcard = 'Hola '.ucwords(strtolower($caret_gift->first_name)).'. '.ucwords(strtolower($datos_pagador->first_name)).' te ha enviado un obsequio: Gift card por $'.$datos_gift->monto.' de '.strtoupper($gift->emisor).', vigente hasta '.$fecha_corta.'. Mas info en tu correo '.$email_receptor;
                        
						$this->enviar_sms($telefono, $mensaje_giftcard);						
						                       
					}				
					return redirect()->route('transacciones.index', ['estatus' => 'ok']);					
				}
				else{
					return redirect()->route('transacciones.create')
					->with('token_pin', true)
					->with('token_code', $idTrans);
				}
			}
        }


		$es_giftcard = false;
		
		if(substr($transacciones->carnet, 0, 4) == '6540')
		{
			$es_giftcard = true;
		}		
		
        //$fecha = Carbon::parse($transacciones -> created_at)->format('d/m/Y');
        //dd($fecha);
        $fecha = date('d/m/Y');
        $nombre_completo= $first_name.' '.$last_name;
        $hash = encrypt([
            "user" =>$transacciones->fk_dni_miembros,
            "transaction" => $idTrans,
            "token" =>$token
            ]);
			
        $shortLink = env('APP_URL').'/autorizacion/'.str_random(6).'/sms';
        Shortcute::create([
            "short_hash"=>$hash,
            "short_link"=> $shortLink
        ]);
                    $moneda = Moneda::find(trans_head::find($idTrans)->fk_monedas)->mon_simbolo;
					$producto = substr($transacciones->carnet, 0, 4) . '-****-****-'. substr($transacciones->carnet, (strlen($transacciones->carnet) - 4), 4);

                    //dd($moneda);

				try{
                    //Mail::to(config('webConfig.email'))
                    //->send(new transaccionesEmail($montos, $token, $fecha, $desc_comercio, $idTrans, $nombre_completo, $moneda, $producto, $hash));
					
                    $bcc = array(config('webConfig.bcc'),config('webConfig.email'));
                    
                    Mail::to($transacciones->email)->bcc($bcc)
                    ->send(new transaccionesEmail($montos, $token, $hoy, $desc_comercio, $idTrans, $nombre_completo, $moneda, $producto, $hash, $es_giftcard));
				}
				catch(\Exception $e)
				{
				}

                 /* /////////// ENVIO DE SMS ///////////// */
				 if($es_giftcard)
				 {
					 $this->enviar_sms($telefono,'Su codigo Aut:'. $token .'. Clic para autorizar: '.$shortLink.'. Para mas info giftcard@banplus.com');
				 }
				 else{
					$this->enviar_sms($telefono,'Su codigo Aut:'. $token .'. Clic para autorizar: '.$shortLink.'. Para mas informacion 0412Banplus (2267587) o 02129092003'); 
				 }				 
				 
                /* /////////// FIN ENVIO DE SMS ///////////// */

                $data= $trans_head;
                flash('Se ha realizado la operacion solicitada exitosamente.', '¡Operación Exitosa!')->success();
                }
            }
        }elseif($existing_user) {
            if($existing_carnet == null){
                return redirect()->route('transacciones.create')->with('error','No existe el carnet o no corresponde al cliente solicitado.');
            }
        }elseif($existing_user == null) {
           return redirect()->route('transacciones.create')->with('error','No existe el cliente solicitado.');
       }
       if($amount_cero == false) {
            return redirect()->route('transacciones.create')->with('error','El campo Monto es Obligatorio.');
        }
        if($verify_amount == false) {
            return redirect()->route('transacciones.create')->with('error','La propina debe ser menor al 30%.');
        }
        if($limite == false) {
            return redirect()->route('transacciones.create')->with('error','Fondo insuficiente para realizar la operación.');
        }
        if($disponible == false){
            return redirect()->route('transacciones.create')->with('error','El monto no puede ser mayor al disponible.');
        }

   }catch (\Exception $e) {

    DB::rollBack();
    flash('La transacción no se pudo registrar, intente más tarde.'.$e, '¡Alert!')->error();

}
if (!empty($idTrans)) {
    
    return redirect()->route('transacciones.create')->with('token_code', $idTrans);
}

}


    public function show($id)
    {


        $transacciones = trans_head::select('trans_head.id as idTrans', 'trans_head.carnet_id as carnet_id','trans_head.monto', 'trans_head.propina', 'trans_head.created_at as fechaTrans', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos')
        ->join('users','users.id','trans_head.fk_dni_miembros')
        ->join('carnet','carnet.id','trans_head.carnet_id')
        ->join('comercios','comercios.id','trans_head.fk_id_comer')
        ->join('bancos','bancos.id','trans_head.fk_id_banco')
        ->where('trans_head.id', $id)
        ->orderBy('idTrans', 'DESC')
        ->get();

        
        $transacciones->each(function($trans){
            $trans2= trans_head::find($trans->idTrans);
            //dd($trans2);
            if($trans->carnet_id){
                //dd($trans->carnet_id);

                //$carnet = carnet::find($trans->carnet_id);

                $monedas= carnet::select('carnet.*', 'monedas.mon_nombre')
                ->join('monedas', 'monedas.mon_id', 'carnet.fk_monedas')
                ->where('carnet.id', $trans->carnet_id)
                ->get()[0]->mon_nombre;

                //dd($monedas);

                //$monedas = Moneda::where('mon_id', $trans->fk_monedas)->get()[0]->mon_nombre;
            
            }else{

                $monedas= carnet::select('carnet.*', 'monedas.mon_nombre')
                ->join('monedas', 'monedas.mon_id', 'carnet.fk_monedas')
                ->where('carnet.id', $trans->carnet)
                ->get()[0]->mon_nombre;

                //$monedas = carnet::where('carnet', $trans->carnet)->get()[0]->moneda;

                //$monedas = Moneda::where('mon_id', $trans->fk_monedas)->get()[0]->mon_nombre;
                
            }
            $trans->moneda = $monedas;

            //dd($trans->moneda);

        });

//dd($transacciones);
        // $members_departments = MemberDepartment::select('members_departments.*')
        // ->where('members_departments.fk_id_member', $id)
        // ->first();
        return response()->json([
            'data'      => $transacciones
        ],200);
    }

    public function comerciosTrans($id)
    {

        $comercio = comercios::select('comercios.propina_act')
        ->where('id','=',$id)
        ->get();
        return response()->json([
            'data'      => $comercio
        ],200);

    }
    
    public function logTrans($id)
    {
        $user= User::find(Auth::user()->id);

        $roles= $user->roles;

        $rol = null;

        foreach ($roles as $value) {
            $rol = $value->id;
        }

        if($rol == 3)
        {
            $log = log_trans::select('log_trans.username','log_trans.created_at','log_trans.accion')
            ->where('trans_id','=',$id)
            ->where('visual','=',null)
            ->get();            
        }
        else{
            $log = log_trans::select('log_trans.username','log_trans.created_at','log_trans.accion')
            ->where('trans_id','=',$id)
            ->get();            
        }

        return response()->json([
            'data'      => $log
        ],200);
    }       

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $transacciones = trans_head::select('trans_head.id as idTrans', 'trans_head.monto', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id as idBanco','bancos.descripcion as descripcionBancos')
        ->join('users','users.id','trans_head.fk_dni_miembros')
        ->join('carnet','carnet.fk_id_miembro','users.id')
        ->join('comercios','comercios.id','trans_head.fk_id_comer')
        ->join('bancos','bancos.id','trans_head.fk_id_banco')
        ->where('trans_head.id', $id)
        ->first();

        $bancos = bancos::select('bancos.*')
        ->get();

        return view('transacciones.edit')->with(['transacciones' => $transacciones, 'bancos' => $bancos]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{

            $iduser=Auth::user()->id;

            $existing_user= User::select('users.id as idMiembros', 'users.dni')
            ->where('dni',$request->dni)
            ->first();
            // dd( $existing_user);

            $existing_carnet= carnet::select('carnet.*','users.*')
            ->join('users','users.id','carnet.fk_id_miembro')
            ->where('carnet',$request->carnet)
            ->first();

            $fk_id_comer= miem_come::select('miem_come.id','miem_come.fk_id_comercio')
            ->where('fk_id_miembro',$iduser)
            ->first();

            /*se toma la ip del cliente para registrar en base de datos*/
            $clientIP = \Request::ip();

            if ($existing_user && $existing_carnet) {

                $trans_head = trans_head::where('id',$id)
                ->update([
                    // 'fk_dni_miembros'   => $request -> dni,
                    'fk_id_banco'    => /*$request -> fk_id_banco*/ 1,
                    'fk_id_comer'     => $fk_id_comer -> fk_id_comercio,
                    'monto'     => $request -> monto,
                    'cancela_a'         => 0,
                    'ip'    => $clientIP,
                ]);

                flash('Se ha realizado la operacion solicitada exitosamente.', '¡Operación Exitosa!')->success();

            }elseif($existing_user) {

                if($existing_carnet == null){
                    return redirect()->route('transacciones.create')->with('error','No existe carnet.');
                }
            }elseif($existing_user == null) {
             return redirect()->route('transacciones.create')->with('error','No existe el usuario.');
         }

     }catch (\Exception $e) {
        DB::rollBack();
        flash('La transacción no se pudo registrar, intente más tarde. '.$e, '¡Alert!')->error();

    }

    return redirect()->route('transacciones.index');


    }

    public function SendNotificationTransactionCreate($id)
    {
		try{
		
		$token = mt_rand(100000, 999999);

        $token_encrypt=Crypt::encrypt($token);
		
		$hoy = date("Y-m-d H:i:s");
		
		$transacciones = trans_head::select('trans_head.id as idTrans','trans_head.status', 'trans_head.monto','trans_head.propina', 'trans_head.created_at as fechaTrans', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos','trans_head.otp_bco')
		->join('users','users.id','trans_head.fk_dni_miembros')
		->join('carnet','carnet.fk_id_miembro','users.id')
		->join('comercios','comercios.id','trans_head.fk_id_comer')
		->join('bancos','bancos.id','trans_head.fk_id_banco')
		->where('trans_head.id' ,$id)
		->where('trans_head.status', 1)
		->first();
		
		if(is_null($transacciones))
		{
            $response = [
                'success' => false,
                'message' => 'No se pudo encontrar la transacción pendiente de autorización',
            ];
            return response()->json($response, 404);			
		}
		else{			
				$otpBco = $transacciones->otp_bco;
				
				$otpBco_encrypt = null;
				
				if($otpBco != null)
				{
					$otpBco_encrypt = Crypt::encrypt($otpBco);
				}
								
				$trans_head = trans_head::where('id',$transacciones->idTrans)
				->update([
					'token'    => $token_encrypt,
					'otp_bco' => $otpBco_encrypt
				]);		
				
				$desc_comercio= $transacciones -> descripcionComercios;

				$idTrans= $transacciones-> idTrans;
				$first_name= $transacciones -> first_name;
				$last_name= $transacciones -> last_name;
				$montos = $transacciones -> monto + $transacciones -> propina;
				$montos = number_format($montos , 2, ',', '.');
				$telefono = $transacciones-> cod_tel.''.$transacciones-> num_tel;
				//$fecha = Carbon::parse($transacciones -> created_at)->format('d/m/Y');
				//dd($fecha);
				$fecha = date('d/m/Y');
				$nombre_completo= $first_name.' '.$last_name;		
					
				$hash = encrypt([
					"user" =>$transacciones->fk_dni_miembros,
					"transaction" => $idTrans,
					"token" =>$token
					]);
				$shortLink = env('APP_URL').'/autorizacion/'.str_random(6).'/sms';
				Shortcute::create([
					"short_hash"=>$hash,
					"short_link"=> $shortLink
				]);
				
						$moneda = Moneda::find(trans_head::find($idTrans)->fk_monedas)->mon_simbolo;
						
						$producto = substr($transacciones->carnet, 0, 4) . '-****-****-'. substr($transacciones->carnet, (strlen($transacciones->carnet) - 4), 4);
				
						try{
							//Mail::to(config('webConfig.email'))
							//->send(new transaccionesEmailNoLink($montos, $token, $fecha, $desc_comercio, $idTrans, $nombre_completo, $moneda, $producto));
							$bcc = array(config('webConfig.email'),config('webConfig.bcc'));                    

							Mail::to($transacciones->email)->bcc($bcc)
							//->send(new transaccionesEmailNoLink($montos, $token, $hoy, $desc_comercio, $idTrans, $nombre_completo, $moneda, $producto));	
							->send(new transaccionesEmail($montos, $token, $hoy, $desc_comercio, $idTrans, $nombre_completo, $moneda, $producto, $hash));
						}
						catch(\Exception $e)
						{
							
						}


						 /* /////////// ENVIO DE SMS ///////////// */
						 $this->enviar_sms($telefono,'Su codigo Aut:'. $token .'. Clic para autorizar: '.$shortLink.'. Para mas informacion 0412Banplus (2267587) o 02129092003');	
						 
					$response = [
						'success' => true,
						'message' => 'Notificaciones enviadas con éxito'
					];
					return response()->json($response, 200);			
		}			
		}
		catch(\Exception $e)
		{
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return response()->json($response, 404);			
		}


        		
	}

    public function SendNotificationTransactionAuthorized($id)
    {
		try{
			$transacciones = trans_head::select('trans_head.id as idTrans','trans_head.fk_dni_miembros','trans_head.status', 'trans_head.monto', 'trans_head.created_at as fechaTrans', 'trans_head.token', 'trans_head.token_status', 'trans_head.token_time', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos')
			->join('users','users.id','trans_head.fk_dni_miembros')
			->join('carnet','carnet.fk_id_miembro','users.id')
			->join('comercios','comercios.id','trans_head.fk_id_comer')
			->join('bancos','bancos.id','trans_head.fk_id_banco')
			->where('trans_head.id', $id)
			->where('trans_head.status', 0)
			->get();
			
			if(is_null($transacciones))
			{
				$response = [
					'success' => false,
					'message' => 'No se pudo encontrar la transacción autorizada',
				];
				return response()->json($response, 404);			
			}
			else{
				if(count($transacciones) > 0)
				{				
					$hoy = date("Y-m-d H:i:s");
					$montos = $transacciones[0]->monto;
					$desc_comercio = $transacciones[0]->descripcionComercios;
					$idTrans = $transacciones[0]->idTrans;
					$first_name = $transacciones[0]->first_name;
					$last_name = $transacciones[0]->last_name;					
                    $nombre_completo = $first_name.' '.$last_name;
                    
                    $moneda = Moneda::find(trans_head::find($idTrans)->fk_monedas)->mon_simbolo;
					
					$producto = substr($transacciones[0]->carnet, 0, 4) . '-****-****-'. substr($transacciones[0]->carnet, (strlen($transacciones[0]->carnet) - 4), 4);
					
					$bcc = array(config('webConfig.email'),config('webConfig.bcc'));
					Mail::to($transacciones[0]->email)->bcc($bcc)->send(new autorizacionTransEmail($montos, $hoy, $desc_comercio, $idTrans, $nombre_completo,$moneda, $producto));	
					
					$response = [
						'success' => true,
						'message' => 'Notificaciones enviadas con éxito'
					];
					return response()->json($response, 200);					
				}
				else{
					$response = [
						'success' => false,
						'message' => 'No se pudo encontrar la transacción autorizada',
					];
					return response()->json($response, 404);					
				}
			}			
		}
		catch(\Exception $e)
		{
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return response()->json($response, 404);			
		}	
	}

    public function SendNotificationTransactionAuthorizedToCommerce($id)
    {
		try{
			$transacciones = trans_head::select('trans_head.id as idTrans','trans_head.fk_dni_miembros','trans_head.status', 'trans_head.monto', 'trans_head.created_at as fechaTrans', 'trans_head.token', 'trans_head.token_status', 'trans_head.token_time', 'users.*','carnet.*', 'comercios.id','comercios.rif','comercios.email as emailcomercio', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos')
			->join('users','users.id','trans_head.fk_dni_miembros')
			->join('carnet','carnet.fk_id_miembro','users.id')
			->join('comercios','comercios.id','trans_head.fk_id_comer')
			->join('bancos','bancos.id','trans_head.fk_id_banco')
			->where('trans_head.id', $id)
			->where('trans_head.status', 0)
			->get();
			
			if(is_null($transacciones))
			{
				$response = [
					'success' => false,
					'message' => 'No se pudo encontrar la transacción autorizada',
				];
				return response()->json($response, 404);			
			}
			else{
				if(count($transacciones) > 0)
				{				
					$hoy = date("Y-m-d H:i:s");
					$montos = $transacciones[0]->monto;
					$desc_comercio = $transacciones[0]->descripcionComercios . ' (' .$transacciones[0]->rif . ')';
					$idTrans = $transacciones[0]->idTrans;
					$first_name = $transacciones[0]->first_name;
					$last_name = $transacciones[0]->last_name;					
					$nombre_completo = $first_name.' '.$last_name;
					
					$moneda = Moneda::find(trans_head::find($idTrans)->fk_monedas)->mon_simbolo;
					
					$bcc = array(config('webConfig.email'),config('webConfig.bcc'));
					Mail::to($transacciones[0]->emailcomercio)->bcc($bcc)->send(new autorizacionTransAlComercioEmail($montos, $hoy, $desc_comercio, $idTrans, $nombre_completo, $moneda));	
					
					$response = [
						'success' => true,
						'message' => 'Notificaciones enviadas con éxito'
					];
					return response()->json($response, 200);					
				}
				else{
					$response = [
						'success' => false,
						'message' => 'No se pudo encontrar la transacción autorizada',
					];
					return response()->json($response, 404);					
				}
			}			
		}
		catch(\Exception $e)
    {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return response()->json($response, 404);			
		}	
    }
	
	public function SendNotificationTransactionFailed($mensaje, $referencia)
	{	
        try
        {
            Mail::to(config('webConfig.emailRechazo'))
            ->send(new AutorizacionFallidaFintech($referencia, $mensaje));	

            $response = [
                'success' => true,
                'message' => 'Notificacion enviadas con éxito'
            ];
            return response()->json($response, 200);            
        }	
        catch(\Exception $e)
        {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return response()->json($response, 404);
        }
	}

        public function linkAuth(Request $request)
    {
		try{
            $extRef = null;
            $createdOn = null; 

            $transHash = $request->token;

            $data = decrypt($transHash);


               //dd($request);
               $id=$data['transaction'];

               //dd($id);
              $tokensele=$data['token'];
              $hoy = date("Y-m-d H:i:s");
              /*se toma la ip del cliente para registrar en base de datos*/
              $clientIP = \Request::ip();
      

      
      
           
                      $transacciones = trans_head::select('trans_head.id as idTrans','trans_head.fk_dni_miembros','trans_head.status', 'trans_head.monto', 'trans_head.created_at as fechaTrans', 'trans_head.token', 'trans_head.token_status', 'trans_head.token_time', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos','carnet.cod_emisor','otp_bco','comercios.rif','carnet.cod_cliente_emisor','trans_head.otp_bco_time','trans_head.neto', 'carnet.id as CarnetId')
                      ->join('users','users.id','trans_head.fk_dni_miembros')
                      ->join('carnet','carnet.id','trans_head.carnet_id')
                      ->join('comercios','comercios.id','trans_head.fk_id_comer')
                      ->join('bancos','bancos.id','trans_head.fk_id_banco')
                      ->where('trans_head.id', $id)
                      ->get();
                       //dd($transacciones);
      
                      $desc_comercio= $transacciones[0] -> descripcionComercios;
                      $idTrans= $transacciones[0] -> idTrans;
                      $first_name= $transacciones[0] -> first_name;
                      $last_name= $transacciones[0] -> last_name;
                      $montos= $transacciones[0] -> neto;
                      $token_real=$transacciones[0] -> token;
                      $token_time=$transacciones[0] -> token_time;
                      $telefono = $transacciones[0] -> cod_tel.''.$transacciones[0] -> num_tel;
					  $UserID = $transacciones[0]->fk_dni_miembros;
					  $Transaction_Status = $transacciones[0]->status;
					  $CarnetId = $transacciones[0]->CarnetId;
					  $Carnet = $transacciones[0]->carnet;
					  
						$es_giftcard = false;
						
						if(substr($Carnet, 0, 4) == '6540')
						{
							$es_giftcard = true;
						}					  
      

                      $token_status=$transacciones[0] -> token_status;
                      $nombre_completo= $first_name.' '.$last_name;
                      $token_realHastaLaMuerte = Crypt::decrypt($token_real);
					  
					  //Si ya la transaccion se encuentra en proceso
					  if($Transaction_Status == 10)
					  {
						  return view('transacciones.autorizar')->with('estatus', 'InProgress')->with('es_giftcard', $es_giftcard);
					  }					  
      
					  //Si ya la transaccion fue tratada enviar un error
					  if($Transaction_Status != 1)
					  {
						  if ($hoy <= $token_time && $Transaction_Status == 0) {
							  return view('transacciones.autorizar')->with('estatus', 'ok')->with('es_giftcard', $es_giftcard);
						  }
						  else{
							  return view('transacciones.autorizar')->with('estatus', 'NoValida')->with('es_giftcard', $es_giftcard);
						  }
					  }
					  else
					  {
                      if ($hoy <= $token_time) {
      
      
      
                      if($tokensele == $token_realHastaLaMuerte && $token_status != 3){
                          // dd("sisisisi");
						  
							$trans_head = trans_head::where('id',$id)
							->update([
								'status'     => 10,
							]);
							
                          $moneda = Moneda::find(trans_head::find($id)->fk_monedas)->mon_simbolo;

                          $datTrans = trans_head::select('neto')->where('id',$id)->first();
                          $datLedge = Ledge::select('id','disp_post')->where('fk_id_trans_head',$id)->first();
                          $disp_post_ledge = $datLedge->disp_post - $datTrans->neto;

                            ///CONSULTAR LAS APIS DEL BANCO
                            if($transacciones[0]->cod_emisor == "174")
                            {										
									
                                    if ($hoy >= $transacciones[0]->otp_bco_time){
                                        $response = $this->GenerateBankOTP($moneda, $transacciones[0]->nacionalidad, $transacciones[0]->dni);

                                        if(isset($response['code'], $response['message']))
                                        {
                                            $mensaje = "";

                                            if($response['code'] == 4604)
                                            $mensaje = "Adiliado no existe.";

                                            if($response['code'] == 3447)
                                            $mensaje = "El cliente no posee cuenta registrada en ese tipo moneda.";

                                            if($response['code'] == 500)
                                            $mensaje = "Error interno en los servicios.";                                    

                                            $trans_head = trans_head::where('id',$id)
                                            ->update([
                                                'token_status'    => 3,
                                                'status'     => 3,
                                                'ip'  => $clientIP,
                                            ]);
                                        
                                            $this->insertLog($mensaje, $id, "BCO");
                                            $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);
                                            $this->SendNotificationTransactionFailed($mensaje, $id);
                                            return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
                                        
                                        }
                                        else{
                                            if(isset($response['otp'], $response['expiresOn']))
                                            {
                                                $transacciones[0]->otp_bco = Crypt::encrypt($response['otp']);            
                                            }
                                        }                                
                                    }

                                    $otp_bco = Crypt::decrypt($transacciones[0]->otp_bco);
                                    $data_array =  array(
                                        "ip"        => \Request::ip(),
                                        "affiliationNumber"  => (String)$transacciones[0]->cod_cliente_emisor,
                                        "traderTaxNumber"  => (String)$transacciones[0]->rif,
                                        "internalRef"  => (string)$id,
                                        "currency"  => (String)$moneda,
                                        "amount"  => $transacciones[0]->neto,
                                        "createdOn"  => $transacciones[0]->fechaTrans,
                                        "otp"  => $otp_bco
                                );

                                $make_call = $this->callAPI('POST', config('webConfig.ProviderBanplusGenerateCreateTransaction'), json_encode($data_array));
                                $response = json_decode($make_call, true);

                                if(isset($response['code'], $response['message']))
                                {
                                    $mensaje = "";

                                    if($response['code'] == 3320)
                                    $mensaje = "El comercio no se encuentra registrado.";

                                    if($response['code'] == 3505)
                                    $mensaje = "OTP Invalido.";

                                    if($response['code'] == 3504)
                                    $mensaje = "Su OTP expiro.";

                                    if($response['code'] == 3324)
                                    $mensaje = "Transacción ya anulada.";

                                    if($response['code'] == 3463)
                                    $mensaje = "La cuenta no se encuentra activa.";

                                    if($response['code'] == 4630)
                                    $mensaje = "El Monto a transferir es invalido.";

                                    if($response['code'] == 9011)
                                    $mensaje = "El Monto ingresado debe ser un Numerico.";    
                                    
                                    if($response['code'] == 4632)
                                    $mensaje = "El numero de cuenta a debitar es invalido.";                            
        
                                    if($mensaje == '')
                                    {
                                        $mensaje = $response['message'];
                                    }                                    

                                    $trans_head = trans_head::where('id',$id)
                                    ->update([
                                        'token_status'    => 3,
                                        'status'     => 3,
                                        'ip'  => $clientIP,
                                    ]);

                                    $this->insertLog($mensaje, $id, "BCO");

                                    $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);                                    

                                    $this->SendNotificationTransactionFailed($mensaje, $id);

                                    return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
                                }
                                else{
                                    if(isset($response['extRef'], $response['createdOn']))
                                    {
                                        $extRef = (String)$response['extRef'];
                                        $createdOn = (String)$response['createdOn'];                                
                                    }
                                }

                                if($extRef == null  || $createdOn == null || $extRef == "" || $createdOn == "")
                                {
                                    $trans_head = trans_head::where('id',$id)
                                    ->update([
                                        'token_status'    => 3,
                                        'status'     => 3,
                                        'ip'  => $clientIP,
                                    ]);

                                    $this->insertLog("No se recibio ninguna respuesta de los servicios del banco.", $id, "BCO");

                                    $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);

                                    $this->SendNotificationTransactionFailed("No se recibio ninguna respuesta de los servicios del banco.", $id);

                                    return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
                                }
                            }

                            ///FIN
      
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'status'    => 0,
                              'ip'  => $clientIP,
                              'ref_bco'  => $extRef,
                              'trans_bco_time'  => $createdOn,
                          ]);                          
      
                          $ledge = Ledge::where('id',$datLedge->id)
                          ->update([
                              'disp_post' => $disp_post_ledge,
                          ]);
						  
                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD
                    $datos_gift = trans_gift_card::select('trans_gift_card.fk_carnet_id_recibe','trans_gift_card.monto','trans_gift_card.imagen', 'trans_gift_card.vencimiento')
                    ->Where('trans_gift_card.fk_trans_id', '=', $id)
                    ->first();                    

                    if($datos_gift)
                    {
                        $caret_gift = carnet::select('carnet.disponible','carnet.cod_emisor','users.cod_tel','users.num_tel','users.first_name','users.first_name','users.last_name','users.email', 'users.nacionalidad', 'users.dni')
                        ->join('users','carnet.fk_id_miembro','users.id')
                        ->Where('carnet.id', '=', $datos_gift->fk_carnet_id_recibe)
                        ->first();

                        $disponible_final_gift_card = $caret_gift->disponible + $datos_gift->monto;

                        carnet::where('id',$datos_gift->fk_carnet_id_recibe)
                        ->update([
                            'disponible' => $disponible_final_gift_card,
							'limite' => $disponible_final_gift_card,
							'transar' => true,
                        ]);

                        $gift = emisores::select('emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
                        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
                        ->Where('emisores.cod_emisor', '=', $caret_gift->cod_emisor)
                        ->first();
                        
                        $telefono = $caret_gift->cod_tel.''.$caret_gift->num_tel;
						
						$datos_pagador = User::select('users.first_name','users.last_name','users.email')
                        ->join('trans_head','trans_head.fk_dni_miembros','users.id')
                        ->Where('trans_head.id', '=', $id)
                        ->first();

						$paraCedula = $caret_gift->nacionalidad.'-'.$caret_gift->dni;
						$paraEmail = $caret_gift->email;
						$paraTelefono = str_replace("58","0",$caret_gift->cod_tel.'-'.$caret_gift->num_tel);
						$imagen = $datos_gift->imagen;
						$vencimiento = $datos_gift->vencimiento;
						
						$bcc = array(config('webConfig.email'),config('webConfig.bcc'));
						Mail::to($caret_gift->email)->bcc($bcc)->send(new CompraGiftCardEmail($datos_gift->monto, $gift->mon_simbolo, $gift->emisor, $datos_pagador->first_name.' '.$datos_pagador->last_name, $caret_gift->first_name.' '.$caret_gift->last_name, $imagen, $paraCedula, $paraEmail, $paraTelefono, $vencimiento));						
                        
						$fecha_vencimiento = \Carbon\Carbon::createFromTimeStamp(strtotime($vencimiento))->format('d-m-Y');
						
						$fecha_corta = $this->fecha_corta($fecha_vencimiento);
						
						$array_email = explode("@",$caret_gift->email);
						
						$email_receptor = str_pad(substr($array_email[0],0,1), (strlen($array_email[0]) - 3) ,"*") . "***" .substr($array_email[0],-1). "@" . $array_email[1];						
						
						$mensaje_giftcard = 'Hola '.ucwords(strtolower($caret_gift->first_name)).'. '.ucwords(strtolower($datos_pagador->first_name)).' te ha enviado un obsequio: Gift card por $'.$datos_gift->monto.' de '.strtoupper($gift->emisor).', vigente hasta '.$fecha_corta.'. Mas info en tu correo '.$email_receptor;
                        
						$this->enviar_sms($telefono, $mensaje_giftcard);
                    }

                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD  

					//VALIDAR SI SE TRATA DE UN PRODUCTO DE VALEVEN
					if($transacciones[0]->cod_emisor == "VALEVEN001")
					{
						try{
							
							$UserValeven = User::find($UserID);
							$CarnetUserValeven = carnet::find($CarnetId);
							
							$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->where('procesado', null)
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$creditoDisponible = number_format(($CarnetUserValeven->disponible - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');							
																			
							$telefono = $UserValeven->cod_tel.''.$UserValeven->num_tel;
							
							$this->enviar_sms($telefono,'Compra VALEVEN por '.$montos.' '.$moneda. '. Saldo disponible '.$creditoDisponible. ' ' .$moneda);
        
                         }
                         catch(\Exception $e)
                         {							 
                         }    						
					}	

					//FIN VALEVEN
					
					//VALIDAR SI SE TRATA DE UNA COMPRA CON GIFTCARD
					$saldo_gift = 0;
					
					if($es_giftcard)
					{
						try{
							
							$UserGift = User::find($UserID);
							$CarnetUserGift = carnet::find($CarnetId);
							
							$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->where('procesado', null)
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$creditoDisponible = number_format(($CarnetUserGift->disponible - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');							
							
							$saldo_gift = $creditoDisponible;
																			
							$telefono = $UserGift->cod_tel.''.$UserGift->num_tel;
							
							$this->enviar_sms($telefono,'Aprobo un consumo por $'.$montos.' en el comercio '.strtoupper($desc_comercio).'. Saldo disponible en GiftCard $'.$creditoDisponible.'. Para mas info giftcard@banplus.com');

                         }
                         catch(\Exception $e)
                         {                   
                         }    						
					}					
      								                            
						  $producto = substr($transacciones[0]->carnet, 0, 4) . '-****-****-'. substr($transacciones[0]->carnet, (strlen($transacciones[0]->carnet) - 4), 4);
						  $bcc = array(config('webConfig.email'),config('webConfig.bcc'));
                          Mail::to($transacciones[0]->email)->bcc($bcc)->send(new autorizacionTransEmail($montos, $hoy, $desc_comercio, $idTrans, $nombre_completo,$moneda,$producto,$es_giftcard,$saldo_gift));


									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha aprobado la autorización exitosamente por medio del link.';
									$log->ip = $ip;
									$log->save();												
														
			  
                          flash('Se ha aprobado la autorización exitosamente.', '¡Operación Exitosa!')->success();
								  return view('transacciones.autorizar')->with('estatus', 'ok')->with('es_giftcard', $es_giftcard);
      
                      }elseif($tokensele != $token_realHastaLaMuerte && $token_status==0){
                          // dd("1er error");
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'token_status'    => 1,
                              'ip'  => $clientIP,
                          ]);
								  
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha intentado aprobar la transaccion con un còdigo incorecto por medio del link.';
									$log->ip = $ip;
									$log->save();								  
								  
								  //flash('El código es incorrecto.', '¡Alert!')->error();
      
                      }elseif ($tokensele != $token_realHastaLaMuerte && $token_status==1) {
                          // dd("2do error");
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'token_status'    => 2,
                              'ip'  => $clientIP,
                          ]);
								  
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha indicado un código de validación incorrecto por medio del link.';
									$log->ip = $ip;
									$log->save();									  
								  
								  //flash('El código es incorrecto.', '¡Alert!')->error();
									return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
      
                      }elseif ($tokensele != $token_realHastaLaMuerte && $token_status==2) {
                          // dd("3er error");
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'token_status'    => 3,
                              'status'     => 3,
                              'ip'  => $clientIP,
                          ]);
								  
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha indicado un código de validación incorrecto y la transaccion ha sido rechazada por medio del link.';
									$log->ip = $ip;
									$log->save();							  
								  
								  //flash('El código es incorrecto, la transacción ha sido rechazada.', '¡Alert!')->error();
								  return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
								  
      
                      }
      
      
                  }else{
                      $trans_head = trans_head::where('id',$id)
                          ->update([
                              'status'     => 3,
                              'ip'  => $clientIP,
                          ]);
      
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha indicado un código de validación que ha expirado por medio del link.';
									$log->ip = $ip;
									$log->save();
									
								  //flash('Su código de validación ha expirado, por favor genere otra autorización.', '¡Alert!')->error();
									return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);;
						  }						  
                  }
      
               
      
          }catch(\Exception $e){
             return view('transacciones.autorizar')->with('estatus', 'NoValida')->with('es_giftcard', $es_giftcard);
          }
      
              

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
          }
      
    public function linkAuthSms($transHash, $type=null)
    {
		try{
			
        if($type == "sms"){
           $link = env('APP_URL').'/autorizacion/'.$transHash.'/sms';
           $data = decrypt(Shortcute::where('short_link', $link)->first()->short_hash);
        }else{
            $data = decrypt($transHash);

        }
               //dd($request);
               $id=$data['transaction'];

               $extRef = null;
               $createdOn = null;                 

               //dd($id);
              $tokensele=$data['token'];
              $hoy = date("Y-m-d H:i:s");
              /*se toma la ip del cliente para registrar en base de datos*/
              $clientIP = \Request::ip();
      

      
      
           
                      $transacciones = trans_head::select('trans_head.id as idTrans','trans_head.status', 'trans_head.monto','trans_head.fk_dni_miembros', 'trans_head.created_at as fechaTrans', 'trans_head.token', 'trans_head.token_status', 'trans_head.token_time', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos','carnet.cod_emisor','otp_bco','comercios.rif','carnet.cod_cliente_emisor','trans_head.otp_bco_time','trans_head.neto','carnet.id as CarnetId')
                      ->join('users','users.id','trans_head.fk_dni_miembros')
                      ->join('carnet','carnet.id','trans_head.carnet_id')
                      ->join('comercios','comercios.id','trans_head.fk_id_comer')
                      ->join('bancos','bancos.id','trans_head.fk_id_banco')
                      ->where('trans_head.id', $id)
                      ->get();
                       //dd($transacciones);
      
                      $desc_comercio= $transacciones[0] -> descripcionComercios;
                      $idTrans= $transacciones[0] -> idTrans;
                      $first_name= $transacciones[0] -> first_name;
                      $last_name= $transacciones[0] -> last_name;
                      $montos= $transacciones[0] -> monto;
                      $token_real=$transacciones[0] -> token;
                      $token_time=$transacciones[0] -> token_time;
                      $telefono = $transacciones[0] -> cod_tel.''.$transacciones[0] -> num_tel;
					  $UserID = $transacciones[0]->fk_dni_miembros;
                      $Transaction_Status = $transacciones[0]->status;
                      $CarnetId = $transacciones[0]->CarnetId;
					  $Carnet = $transacciones[0]->carnet;
                      
						$es_giftcard = false;
						
						if(substr($Carnet, 0, 4) == '6540')
						{
							$es_giftcard = true;
						}					  
      
      

                      $token_status=$transacciones[0] -> token_status;
                      $nombre_completo= $first_name.' '.$last_name;
                      $token_realHastaLaMuerte = Crypt::decrypt($token_real);
					  
					  //Si ya la transaccion se encuentra en proceso
					  if($Transaction_Status == 10)
					  {
						  return view('transacciones.autorizar')->with('estatus', 'InProgress')->with('es_giftcard', $es_giftcard);
					  }					  
      
					  //Si ya la transaccion fue tratada enviar un error
					  if($Transaction_Status != 1)
					  {
						  if ($hoy <= $token_time && $Transaction_Status == 0) {
							  return view('transacciones.autorizar')->with('estatus', 'ok')->with('es_giftcard', $es_giftcard);
						  }
						  else{
							  return view('transacciones.autorizar')->with('estatus', 'NoValida')->with('es_giftcard', $es_giftcard);
						  }
					  }
					  else
					  {
                      if ($hoy <= $token_time) {
      
      
      
                      if($tokensele == $token_realHastaLaMuerte && $token_status != 3){
                          // dd("sisisisi");
							$trans_head = trans_head::where('id',$id)
							->update([
								'status'     => 10,
							]);
							
							
                          $moneda = Moneda::find(trans_head::find($id)->fk_monedas)->mon_simbolo;
                          $datTrans = trans_head::select('neto')->where('id',$id)->first();
                          $datLedge = Ledge::select('id','disp_post')->where('fk_id_trans_head',$id)->first();
                          $disp_post_ledge = $datLedge->disp_post - $datTrans->neto;

                            ///CONSULTAR LAS APIS DEL BANCO
                            if($transacciones[0]->cod_emisor == "174")
                            {
                                    if ($hoy >= $transacciones[0]->otp_bco_time){
                                        $response = $this->GenerateBankOTP($moneda, $transacciones[0]->nacionalidad, $transacciones[0]->dni);

                                        if(isset($response['code'], $response['message']))
                                        {
                                            $mensaje = "";

                                            if($response['code'] == 4604)
                                            $mensaje = "Adiliado no existe.";

                                            if($response['code'] == 3447)
                                            $mensaje = "El cliente no posee cuenta registrada en ese tipo moneda.";

                                            if($response['code'] == 500)
                                            $mensaje = "Error interno en los servicios.";                                    

                                            $trans_head = trans_head::where('id',$id)
                                            ->update([
                                                'token_status'    => 3,
                                                'status'     => 3,
                                                'ip'  => $clientIP,
                                            ]);
                                        
                                            $this->insertLog($mensaje, $id, "BCO");

                                            $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);

                                            $this->SendNotificationTransactionFailed($mensaje, $id);

                                            return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
                                        
                                        }
                                        else{
                                            if(isset($response['otp'], $response['expiresOn']))
                                            {
                                                $transacciones[0]->otp_bco = Crypt::encrypt($response['otp']);            
                                            }
                                        }                                
                                    }

                                    $otp_bco = Crypt::decrypt($transacciones[0]->otp_bco);
                                    $data_array =  array(
                                        "ip"        => \Request::ip(),
                                        "affiliationNumber"  => (String)$transacciones[0]->cod_cliente_emisor,
                                        "traderTaxNumber"  => (String)$transacciones[0]->rif,
                                        "internalRef"  => (string)$id,
                                        "currency"  => (String)$moneda,
                                        "amount"  => $transacciones[0]->neto,
                                        "createdOn"  => $transacciones[0]->fechaTrans,
                                        "otp"  => $otp_bco
                                );

                                $make_call = $this->callAPI('POST', config('webConfig.ProviderBanplusGenerateCreateTransaction'), json_encode($data_array));
                                $response = json_decode($make_call, true);

                                if(isset($response['code'], $response['message']))
                                {
                                    $mensaje = "";

                                    if($response['code'] == 3320)
                                    $mensaje = "El comercio no se encuentra registrado.";

                                    if($response['code'] == 3505)
                                    $mensaje = "OTP Invalido.";

                                    if($response['code'] == 3504)
                                    $mensaje = "Su OTP expiro.";

                                    if($response['code'] == 3324)
                                    $mensaje = "Transacción ya anulada.";

                                    if($response['code'] == 3463)
                                    $mensaje = "La cuenta no se encuentra activa.";

                                    if($response['code'] == 4630)
                                    $mensaje = "El Monto a transferir es invalido.";

                                    if($response['code'] == 9011)
                                    $mensaje = "El Monto ingresado debe ser un Numerico.";   
                                    
                                    if($response['code'] == 4632)
                                    $mensaje = "El numero de cuenta a debitar es invalido.";                            
        
                                    if($mensaje == '')
                                    {
                                        $mensaje = $response['message'];
                                    }                                    

                                    $trans_head = trans_head::where('id',$id)
                                    ->update([
                                        'token_status'    => 3,
                                        'status'     => 3,
                                        'ip'  => $clientIP,
                                    ]);

                                    $this->insertLog($mensaje, $id, "BCO");

                                    $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);                                    

                                    $this->SendNotificationTransactionFailed($mensaje, $id);

                                    return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
                                }
                                else{
                                    if(isset($response['extRef'], $response['createdOn']))
                                    {
                                        $extRef = (String)$response['extRef'];
                                        $createdOn = (String)$response['createdOn'];                                
                                    }
                                }

                                if($extRef == null  || $createdOn == null || $extRef == ""  || $createdOn == "")
                                {
                                    $trans_head = trans_head::where('id',$id)
                                    ->update([
                                        'token_status'    => 3,
                                        'status'     => 3,
                                        'ip'  => $clientIP,
                                    ]);

                                    $this->insertLog("No se recibio ninguna respuesta de los servicios del banco.", $id, "BCO");

                                    $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);

                                    $this->SendNotificationTransactionFailed("No se recibio ninguna respuesta de los servicios del banco.", $id);

                                    return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
                                }
                            }
                            ///FIN                          
      
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'status'    => 0,
                              'ip'  => $clientIP,
                              'ref_bco'  => $extRef,
                              'trans_bco_time'  => $createdOn,
                          ]);                            
      
                          $ledge = Ledge::where('id',$datLedge->id)
                          ->update([
                              'disp_post' => $disp_post_ledge,
                          ]);
						  
                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD
                    $datos_gift = trans_gift_card::select('trans_gift_card.fk_carnet_id_recibe','trans_gift_card.monto','trans_gift_card.imagen', 'trans_gift_card.vencimiento')
                    ->Where('trans_gift_card.fk_trans_id', '=', $id)
                    ->first();

                    if($datos_gift)
                    {
                        $caret_gift = carnet::select('carnet.disponible','carnet.cod_emisor','users.cod_tel','users.num_tel','users.first_name','users.first_name','users.last_name','users.email', 'users.nacionalidad', 'users.dni')
                        ->join('users','carnet.fk_id_miembro','users.id')
                        ->Where('carnet.id', '=', $datos_gift->fk_carnet_id_recibe)
                        ->first();

                        $disponible_final_gift_card = $caret_gift->disponible + $datos_gift->monto;

                        carnet::where('id',$datos_gift->fk_carnet_id_recibe)
                        ->update([
                            'disponible' => $disponible_final_gift_card,
							'limite' => $disponible_final_gift_card,
							'transar' => true,
                        ]);

                        $gift = emisores::select('emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
                        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
                        ->Where('emisores.cod_emisor', '=', $caret_gift->cod_emisor)
                        ->first();
                        
                        $telefono = $caret_gift->cod_tel.''.$caret_gift->num_tel;
						
						$datos_pagador = User::select('users.first_name','users.last_name','users.email')
                        ->join('trans_head','trans_head.fk_dni_miembros','users.id')
                        ->Where('trans_head.id', '=', $id)
                        ->first();

						$paraCedula = $caret_gift->nacionalidad.'-'.$caret_gift->dni;
						$paraEmail = $caret_gift->email;
						$paraTelefono = str_replace("58","0",$caret_gift->cod_tel.'-'.$caret_gift->num_tel);
						$imagen = $datos_gift->imagen;
						$vencimiento = $datos_gift->vencimiento;						
						
						$bcc = array(config('webConfig.email'),config('webConfig.bcc'));
						Mail::to($caret_gift->email)->bcc($bcc)->send(new CompraGiftCardEmail($datos_gift->monto, $gift->mon_simbolo, $gift->emisor, $datos_pagador->first_name.' '.$datos_pagador->last_name, $caret_gift->first_name.' '.$caret_gift->last_name, $imagen, $paraCedula, $paraEmail, $paraTelefono, $vencimiento));						
                        
						$fecha_vencimiento = \Carbon\Carbon::createFromTimeStamp(strtotime($vencimiento))->format('d-m-Y');
						
						$fecha_corta = $this->fecha_corta($fecha_vencimiento);
						
						$array_email = explode("@",$caret_gift->email);
						
						$email_receptor = str_pad(substr($array_email[0],0,1), (strlen($array_email[0]) - 3) ,"*") . "***" .substr($array_email[0],-1). "@" . $array_email[1];						
						
						$mensaje_giftcard = 'Hola '.ucwords(strtolower($caret_gift->first_name)).'. '.ucwords(strtolower($datos_pagador->first_name)).' te ha enviado un obsequio: Gift card por $'.$datos_gift->monto.' de '.strtoupper($gift->emisor).', vigente hasta '.$fecha_corta.'. Mas info en tu correo '.$email_receptor;
                        
						$this->enviar_sms($telefono, $mensaje_giftcard);			
						                                           
                    }

                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD  	
                    
					//VALIDAR SI SE TRATA DE UN PRODUCTO DE VALEVEN
					if($transacciones[0]->cod_emisor == "VALEVEN001")
					{
						try{
							
							$UserValeven = User::find($UserID);
							$CarnetUserValeven = carnet::find($CarnetId);
							
							$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->where('procesado', null)
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$creditoDisponible = number_format(($CarnetUserValeven->disponible - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');							
																			
							$telefono = $UserValeven->cod_tel.''.$UserValeven->num_tel;
							
							$this->enviar_sms($telefono,'Compra VALEVEN por '.$montos.' '.$moneda. '. Saldo disponible '.$creditoDisponible. ' ' .$moneda);

                         }
                         catch(\Exception $e)
                         {
                    
                         }    						
					}	

					//FIN VALEVEN		
					
					//VALIDAR SI SE TRATA DE UNA COMPRA CON GIFTCARD
					
					$saldo_gift = 0;
					
					if($es_giftcard)
					{
						try{
							
							$UserGift = User::find($UserID);
							$CarnetUserGift = carnet::find($CarnetId);
							
							$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->where('procesado', null)
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$creditoDisponible = number_format(($CarnetUserGift->disponible - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');							
							
							$saldo_gift = $creditoDisponible;
																			
							$telefono = $UserGift->cod_tel.''.$UserGift->num_tel;
							
							$this->enviar_sms($telefono,'Aprobo un consumo por $'.$montos.' en el comercio '.strtoupper($desc_comercio).'. Saldo disponible en GiftCard $'.$creditoDisponible.'. Para mas info giftcard@banplus.com');

                         }
                         catch(\Exception $e)
                         {                   
                         }    						
					}					
      						  
						  $producto = substr($transacciones[0]->carnet, 0, 4) . '-****-****-'. substr($transacciones[0]->carnet, (strlen($transacciones[0]->carnet) - 4), 4);
						  $bcc = array(config('webConfig.email'),config('webConfig.bcc'));
                          Mail::to($transacciones[0]->email)->bcc($bcc)->send(new autorizacionTransEmail($montos, $hoy, $desc_comercio, $idTrans, $nombre_completo,$moneda,$producto,$es_giftcard,$saldo_gift));

      
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha aprobado la autorización exitosamente por medio del link.';
									$log->ip = $ip;
									$log->save();												
														
			  
                          flash('Se ha aprobado la autorización exitosamente.', '¡Operación Exitosa!')->success();
								  return view('transacciones.autorizar')->with('estatus', 'ok')->with('es_giftcard', $es_giftcard);
      
                      }elseif($tokensele != $token_realHastaLaMuerte && $token_status==0){
                          // dd("1er error");
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'token_status'    => 1,
                              'ip'  => $clientIP,
                          ]);
								  
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha intentado aprobar la transaccion con un còdigo incorecto por medio del link.';
									$log->ip = $ip;
									$log->save();								  
								  
								  //flash('El código es incorrecto.', '¡Alert!')->error();
      
                      }elseif ($tokensele != $token_realHastaLaMuerte && $token_status==1) {
                          // dd("2do error");
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'token_status'    => 2,
                              'ip'  => $clientIP,
                          ]);
								  
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha indicado un código de validación incorrecto por medio del link.';
									$log->ip = $ip;
									$log->save();									  
								  
								  //flash('El código es incorrecto.', '¡Alert!')->error();
									return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
      
                      }elseif ($tokensele != $token_realHastaLaMuerte && $token_status==2) {
                          // dd("3er error");
                          $trans_head = trans_head::where('id',$id)
                          ->update([
                              'token_status'    => 3,
                              'status'     => 3,
                              'ip'  => $clientIP,
                          ]);
								  
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();

									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha indicado un código de validación incorrecto y la transaccion ha sido rechazada por medio del link.';
									$log->ip = $ip;
									$log->save();							  
								  
								  //flash('El código es incorrecto, la transacción ha sido rechazada.', '¡Alert!')->error();
								  return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
								  
      
                      }
      
      
                  }else{
                      $trans_head = trans_head::where('id',$id)
                          ->update([
                              'status'     => 3,
                              'ip'  => $clientIP,
                          ]);
      
									$user= User::find($UserID);
									$id_user=$user->id;
									$email = $user->email;
									$ip = \Request::ip();
      
									$log = new \App\Models\log_trans();
									$log->user_id = $id_user;
									$log->username = $email;
									$log->trans_id = $idTrans;
									$log->accion = 'El cliente ha indicado un código de validación que ha expirado por medio del link.';
									$log->ip = $ip;
									$log->save();
               
								  //flash('Su código de validación ha expirado, por favor genere otra autorización.', '¡Alert!')->error();
									return view('transacciones.autorizar')->with('estatus', 'error')->with('es_giftcard', $es_giftcard);
						  }						  
					  }					  
      
          }catch(\Exception $e){
              return view('transacciones.autorizar')->with('estatus', 'NoValida')->with('es_giftcard', $es_giftcard);
          }
      
    }
    public function checkStatus($transID){
        $status = trans_head::find($transID)->status;
        return response()->json(["status" => $status]);

    }
    public function autorizar(Request $request)
    {
        //dd($request);
        $id=$request -> idTrans;
        $extRef = null;
        $createdOn = null;

         //dd($id);
        $tokensele=$request -> tokensele;
        $hoy = date("Y-m-d H:i:s");
        /*se toma la ip del cliente para registrar en base de datos*/
        $clientIP = \Request::ip();

        // $extra_min= date("Y-m-d H:i:s",strtotime($hoy."+ 10 minutes"));
        // // dd($hoy);
        // dd($extra_min);
     try{
                $transacciones = trans_head::select('trans_head.id as idTrans', 'trans_head.monto','trans_head.neto', 'trans_head.created_at as fechaTrans', 'trans_head.token', 'trans_head.token_status', 'trans_head.token_time', 'users.*','carnet.*', 'comercios.id', 'comercios.descripcion as descripcionComercios','bancos.id','bancos.descripcion as descripcionBancos','carnet.cod_emisor','otp_bco','comercios.rif','carnet.cod_cliente_emisor','trans_head.otp_bco_time','trans_head.status','carnet.id as CarnetId')
                ->join('users','users.id','trans_head.fk_dni_miembros')
                ->join('carnet','carnet.id','trans_head.carnet_id')
                ->join('comercios','comercios.id','trans_head.fk_id_comer')
                ->join('bancos','bancos.id','trans_head.fk_id_banco')
                ->where('trans_head.id', $id)
                ->get();
                 //dd($transacciones);

                $desc_comercio= $transacciones[0] -> descripcionComercios;
                $idTrans= $transacciones[0] -> idTrans;
                $first_name= $transacciones[0] -> first_name;
                $last_name= $transacciones[0] -> last_name;
                $montos= $transacciones[0]->neto;
                $token_real=$transacciones[0] -> token;
                $token_time=$transacciones[0] -> token_time;
                $telefono = $transacciones[0] -> cod_tel.''.$transacciones[0] -> num_tel;
                $UserId = $transacciones[0]->fk_id_miembro;
                $carnet = $transacciones[0]->carnet;
				$emisor = $transacciones[0]->cod_emisor;
				$CarnetId = $transacciones[0]->CarnetId;


                // dd($token_time);

                // $extra_min= date("Y-m-d H:i:s",strtotime($token_time."+ 10 minutes"));
                // // dd($hoy);
                // dd($extra_min);

                 // dd( $token_real);
                $token_status=$transacciones[0] -> token_status;
                $nombre_completo= $first_name.' '.$last_name;
                $token_realHastaLaMuerte = Crypt::decrypt($token_real);
				
				if($transacciones[0]->status == 10)
				{
					flash('Esta transacción se encuentra en proceso de autorización en este momento.', '¡Alert!')->error();
					return redirect()->route('transacciones.index');
				}

                // dd($desc_comercio);
                // dd($montos);
                // dd($token_status);
                // dd($token_realHastaLaMuerte);
                 // dd($token_realHastaLaMuerte);


                // dd($token_realHastaLaMuerte);

                if ($hoy <= $token_time) {



                if($tokensele == $token_realHastaLaMuerte && $token_status != 3){

					$trans_head = trans_head::where('id',$id)
					->update([
						'status'     => 10,
					]);
					
                    $moneda = Moneda::find(trans_head::find($id)->fk_monedas)->mon_simbolo;
                    // dd("sisisisi");
                    $datTrans = trans_head::select('neto')->where('id',$id)->first();
                    $datLedge = Ledge::select('id','disp_post')->where('fk_id_trans_head',$id)->first();
                    $disp_post_ledge = $datLedge->disp_post - $datTrans->neto;

                    ///CONSULTAR LAS APIS DEL BANCO
                    if($transacciones[0]->cod_emisor == "174")
                    {
						
                            if ($hoy >= $transacciones[0]->otp_bco_time){
                                $response = $this->GenerateBankOTP($moneda, $transacciones[0]->nacionalidad, $transacciones[0]->dni);

                                if(isset($response['code'], $response['message']))
                                {
                                    $mensaje = "";

                                    if($response['code'] == 4604)
                                    $mensaje = "Adiliado no existe.";

                                    if($response['code'] == 3447)
                                    $mensaje = "El cliente no posee cuenta registrada en ese tipo moneda.";

                                    if($response['code'] == 500)
                                    $mensaje = "Error interno en los servicios.";                                    

                                    $trans_head = trans_head::where('id',$id)
                                    ->update([
                                        'token_status'    => 3,
                                        'status'     => 3,
                                        'ip'  => $clientIP,
                                    ]);
                                
                                    $this->insertLog($mensaje, $id, "BCO");
                                    $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);
                                    $this->SendNotificationTransactionFailed($mensaje, $id);
                                    flash('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', '¡Alert!')->error();

                                    return redirect()->route('transacciones.index');
                                   
                                }
                                else{
                                    if(isset($response['otp'], $response['expiresOn']))
                                    {
                                        $transacciones[0]->otp_bco = Crypt::encrypt($response['otp']);            
                                    }
                                }                                
                            }

                            $otp_bco = Crypt::decrypt($transacciones[0]->otp_bco);
                            $data_array =  array(
                                "ip"        => \Request::ip(),
                                "affiliationNumber"  => (String)$transacciones[0]->cod_cliente_emisor,
                                "traderTaxNumber"  => (String)$transacciones[0]->rif,
                                "internalRef"  => (string)$id,
                                "currency"  => (String)$moneda,
                                "amount"  => $transacciones[0]->neto,
                                "createdOn"  => $transacciones[0]->fechaTrans,
                                "otp"  => $otp_bco
                        );

                        $make_call = $this->callAPI('POST', config('webConfig.ProviderBanplusGenerateCreateTransaction'), json_encode($data_array));
                        $response = json_decode($make_call, true);

                        if(isset($response['code'], $response['message']))
                        {
                            $mensaje = "";

                            if($response['code'] == 3320)
                            $mensaje = "El comercio no se encuentra registrado.";

                            if($response['code'] == 3505)
                            $mensaje = "OTP Invalido.";

                            if($response['code'] == 3504)
                            $mensaje = "Su OTP expiro.";

                            if($response['code'] == 3324)
                            $mensaje = "Transacción ya anulada.";

                            if($response['code'] == 3463)
                            $mensaje = "La cuenta no se encuentra activa.";

                            if($response['code'] == 4630)
                            $mensaje = "El Monto a transferir es invalido.";

                            if($response['code'] == 9011)
                            $mensaje = "El Monto ingresado debe ser un Numerico.";

                            if($response['code'] == 4632)
                            $mensaje = "El numero de cuenta a debitar es invalido.";                            

                            if($mensaje == '')
                            {
                                $mensaje = $response['message'];
                            }

                            $trans_head = trans_head::where('id',$id)
                            ->update([
                                'token_status'    => 3,
                                'status'     => 3,
                                'ip'  => $clientIP,
                            ]);

                            $this->insertLog($mensaje, $id, "BCO");
                            $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);
                            $this->SendNotificationTransactionFailed($mensaje, $id);
                            flash('La transacción ha sido rechazada, no se puede procesar esta transacción en este momento.', '¡Alert!')->error();

                            return redirect()->route('transacciones.index');
                        }
                        else{
                            if(isset($response['extRef'], $response['createdOn']))
                            {
                                $extRef = (String)$response['extRef'];
                                $createdOn = (String)$response['createdOn'];                                
                            }
                        }

                        if($extRef == null  || $createdOn == null || $extRef == ""  || $createdOn == "")
                        {
                            $trans_head = trans_head::where('id',$id)
                            ->update([
                                'token_status'    => 3,
                                'status'     => 3,
                                'ip'  => $clientIP,
                            ]);

                            $this->insertLog("No se recibio ninguna respuesta de los servicios del banco.", $id, "BCO");

                            $this->insertLog('La transacción ha sido rechazada, no se puede autorizar esta transacción en este momento.', $id);

                            $this->SendNotificationTransactionFailed("No se recibio ninguna respuesta de los servicios del banco.", $id);

                            flash('La transacción ha sido rechazada, no se puede procesar esta transacción en este momento.', '¡Alert!')->error();                            

                            return redirect()->route('transacciones.index');
                        }
                    }
                    ///FIN                    
					
                    $trans_head = trans_head::where('id',$id)
                    ->update([
                        'status'    => 0,
                        'ip'  => $clientIP,
                        'ref_bco'  => $extRef,
                        'trans_bco_time'  => $createdOn,
                    ]);

                    $ledge = Ledge::where('id',$datLedge->id)
                    ->update([
                        'disp_post' => $disp_post_ledge,
                    ]);

                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD
                    $datos_gift = trans_gift_card::select('trans_gift_card.fk_carnet_id_recibe','trans_gift_card.monto','trans_gift_card.imagen', 'trans_gift_card.vencimiento')
                    ->Where('trans_gift_card.fk_trans_id', '=', $id)
                    ->first();

                    if($datos_gift)
                    {
                        $caret_gift = carnet::select('carnet.disponible','carnet.cod_emisor','users.cod_tel','users.num_tel','users.first_name','users.first_name','users.last_name','users.email','users.nacionalidad','users.dni')
                        ->join('users','carnet.fk_id_miembro','users.id')
                        ->Where('carnet.id', '=', $datos_gift->fk_carnet_id_recibe)
                        ->first();

                        $disponible_final_gift_card = $caret_gift->disponible + $datos_gift->monto;

                        carnet::where('id',$datos_gift->fk_carnet_id_recibe)
                        ->update([
                            'disponible' => $disponible_final_gift_card,
							'limite' => $disponible_final_gift_card,
							'transar' => true,
                        ]);

                        $gift = emisores::select('emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
                        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
                        ->Where('emisores.cod_emisor', '=', $caret_gift->cod_emisor)
                        ->first();
                        
                        $telefono = $caret_gift->cod_tel.''.$caret_gift->num_tel;
						
						$datos_pagador = User::select('users.first_name','users.last_name','users.email')
                        ->join('trans_head','trans_head.fk_dni_miembros','users.id')
                        ->Where('trans_head.id', '=', $id)
                        ->first();

						$paraCedula = $caret_gift->nacionalidad.'-'.$caret_gift->dni;
						$paraEmail = $caret_gift->email;
						$paraTelefono = str_replace("58","0",$caret_gift->cod_tel.'-'.$caret_gift->num_tel);
						$imagen = $datos_gift->imagen;
						$vencimiento = $datos_gift->vencimiento;
						
						$bcc = array(config('webConfig.email'),config('webConfig.bcc'));
						Mail::to($caret_gift->email)->bcc($bcc)->send(new CompraGiftCardEmail($datos_gift->monto, $gift->mon_simbolo, $gift->emisor, $datos_pagador->first_name.' '.$datos_pagador->last_name, $caret_gift->first_name.' '.$caret_gift->last_name, $imagen, $paraCedula, $paraEmail, $paraTelefono, $vencimiento));						
						
						$fecha_vencimiento = \Carbon\Carbon::createFromTimeStamp(strtotime($vencimiento))->format('d-m-Y');
						
						$fecha_corta = $this->fecha_corta($fecha_vencimiento);
						
						$array_email = explode("@",$caret_gift->email);
						
						$email_receptor = str_pad(substr($array_email[0],0,1), (strlen($array_email[0]) - 3) ,"*") . "***" .substr($array_email[0],-1). "@" . $array_email[1];						
						
						$mensaje_giftcard = 'Hola '.ucwords(strtolower($caret_gift->first_name)).'. '.ucwords(strtolower($datos_pagador->first_name)).' te ha enviado un obsequio: Gift card por $'.$datos_gift->monto.' de '.strtoupper($gift->emisor).', vigente hasta '.$fecha_corta.'. Mas info en tu correo '.$email_receptor;
                        
						$this->enviar_sms($telefono, $mensaje_giftcard);
						
                    }

                    //VALIDAR SI SE TRATA DE UNA APROBACIÓN DE GIFTCARD     

					//VALIDAR SI SE TRATA DE UN PRODUCTO DE VALEVEN
					if($transacciones[0]->cod_emisor == "VALEVEN001")
					{
						try{
							
							$UserValeven = User::find($UserId);
							$CarnetUserValeven = carnet::find($CarnetId);
							
							$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->where('procesado', null)
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$creditoDisponible = number_format(($CarnetUserValeven->disponible - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');							
																			
							$telefono = $UserValeven->cod_tel.''.$UserValeven->num_tel;
							
							$this->enviar_sms($telefono,'Compra VALEVEN por '.$montos.' '.$moneda. '. Saldo disponible '.$creditoDisponible. ' ' .$moneda);

                         }
                         catch(\Exception $e)
                         {                   
                         }    						
					}
					
					$es_giftcard = false;
					
					if(substr($carnet, 0, 4) == '6540')
					{
						$es_giftcard = true;
					}				

					//VALIDAR SI SE TRATA DE UNA COMPRA CON GIFTCARD
					$saldo_gift = 0;
					
					if($es_giftcard)
					{
						try{
							
							$UserGift = User::find($UserId);
							$CarnetUserGift = carnet::find($CarnetId);
							
							$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->where('procesado', null)
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
							->where('reverso', null )
							->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
							->where('status', 0)
							->where('carnet_id', $CarnetId)
							->first();
							
							$creditoDisponible = number_format(($CarnetUserGift->disponible - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');							
							
							$saldo_gift = $creditoDisponible;
																			
							$telefono = $UserGift->cod_tel.''.$UserGift->num_tel;
							
							$this->enviar_sms($telefono,'Aprobo un consumo por $'.$montos.' en el comercio '.strtoupper($desc_comercio).'. Saldo disponible en GiftCard $'.$creditoDisponible.'. Para mas info giftcard@banplus.com');

                         }
                         catch(\Exception $e)
                         {                   
                         }    						
					}	

					//FIN VALEVEN					

					//FIN VALEVEN	

					if($emisor != "OTROSPAGOS001")
					{
						$producto = substr($carnet, 0, 4) . '-****-****-'. substr($carnet, (strlen($carnet) - 4), 4);
						$bcc = array(config('webConfig.email'),config('webConfig.bcc'));
						Mail::to($transacciones[0]->email)->bcc($bcc)->send(new autorizacionTransEmail($montos, $hoy, $desc_comercio, $idTrans, $nombre_completo,$moneda,$producto,$es_giftcard,$saldo_gift));
					}
                    
                    $this->insertLog('Se ha aprobado la autorización exitosamente.',$id);
                    flash('Se ha aprobado la autorización exitosamente.', '¡Operación Exitosa!')->success();

                }elseif($tokensele != $token_realHastaLaMuerte && $token_status==0){
                    // dd("1er error");
                    $trans_head = trans_head::where('id',$id)
                    ->update([
                        'token_status'    => 1,
                        'ip'  => $clientIP,
                    ]);
                    $this->insertLog('Autorización fallida, código de verificación inválido',$id);
                    flash('El código es incorrecto.', '¡Alert!')->error();

                }elseif ($tokensele != $token_realHastaLaMuerte && $token_status==1) {
                    // dd("2do error");
                    $trans_head = trans_head::where('id',$id)
                    ->update([
                        'token_status'    => 2,
                        'ip'  => $clientIP,
                    ]);
                    $this->insertLog('Autorización fallida, código de verificación inválido',$id);
                    flash('El código es incorrecto.', '¡Alert!')->error();

                }elseif ($tokensele != $token_realHastaLaMuerte && $token_status==2) {
                    // dd("3er error");
                    $trans_head = trans_head::where('id',$id)
                    ->update([
                        'token_status'    => 3,
                        'status'     => 3,
                        'ip'  => $clientIP,
                    ]);
                    $this->insertLog('Autorización fallida, código de verificación inválido',$id);
                    flash('El código es incorrecto, la transacción ha sido rechazada.', '¡Alert!')->error();

                }


            }else{
                $trans_head = trans_head::where('id',$id)
                    ->update([
                        'status'     => 3,
                        'ip'  => $clientIP,
                    ]);
                    $this->insertLog('Autorización rechazada, código de verificación expirado',$id);
                    flash('Su código de validación ha expirado, por favor genere otra autorización.', '¡Alert!')->error();

            }

            return redirect()->route('transacciones.index');

    }catch(\Exception $e){
            flash(' '.$e, '¡Alert!')->error();
    }

    return redirect()->route('transacciones.index');

    }
	
	public function fecha_corta($fecha)
	{
		$arregle_fecha = explode("-" , $fecha);
		
		$mes_letras = '';
		
        switch ($arregle_fecha[1]){
           case "01":
              $mes_letras = 'ENE';
              break;
           case "02":
              $mes_letras = 'FEB';
              break;
           case "03":
              $mes_letras = 'MAR';
              break;
           case "04":
              $mes_letras = 'ABR';
              break;
           case "05":
              $mes_letras = 'MAY';
              break;
           case "06":
              $mes_letras = 'JUN';
              break;
           case "07":
              $mes_letras = 'JUL';
              break;
           case "08":
              $mes_letras = 'AGO';
              break;
           case "09":
              $mes_letras = 'SEP';
              break;
           case "10":
              $mes_letras = 'OCT';
              break;
           case "11":
              $mes_letras = 'NOV';
              break;
           case "12":
              $mes_letras = 'DIC';
              break;			  
           default:
              $mes_letras = '';
              break;
        }

		return $arregle_fecha[0].$mes_letras.substr($arregle_fecha[2],2);
	}

    public function reversar(Request $request){
        $id=$request->idTrans;
        $otp_bco = null;
        $reverse_bco_ref = null;
        $reverse_bco_time = null;

        //dd($id);

        /*se toma la ip del cliente para registrar en base de datos*/
        $clientIP = \Request::ip();

        $reverso = trans_head::find($id);
        $findLedge = Ledge::where('fk_dni_miembros',$reverso->fk_dni_miembros)
                            ->orderBy('fk_id_trans_head', 'desc')
                            ->first();

        $UserCarnet = User::select('users.dni','users.nacionalidad','carnet.cod_emisor','carnet.cod_cliente_emisor','monedas.mon_simbolo')
        ->join('carnet','carnet.fk_id_miembro','users.id')
        ->join('monedas','monedas.mon_id','carnet.fk_monedas')
        ->where('carnet.id',$reverso->carnet_id)
        ->first();
        
        if($UserCarnet->cod_emisor == "174")
        {
            $response = $this->GenerateBankOTP($UserCarnet->mon_simbolo, $UserCarnet->nacionalidad, $UserCarnet->dni);

            if(isset($response['code'], $response['message']))
            {
                $mensaje = "";

                if($response['code'] == 4604)
                $mensaje = "Adiliado no existe.";

                if($response['code'] == 3447)
                $mensaje = "El cliente no posee cuenta registrada en ese tipo moneda.";

                if($response['code'] == 500)
                $mensaje = "Error interno en los servicios."; 
                
                $this->insertLog($mensaje, $id, "BCO");
                $this->insertLog('No se puede reversar esta transacción en este momento.', $id);
                
                flash('No se puede reversar esta transacción en este momento.', '¡Alert!')->error();
                
                return redirect()->route('transacciones.index'); 
            }
            else{
                if(isset($response['otp'], $response['expiresOn']))
                {
                    $otp_bco = $response['otp'];
                }
            }

            if($otp_bco == null)
            {
                $this->insertLog("No se recibio respuesta de los servicios.", $id, "BCO");

                flash('No se puede reversar esta transacción en este momento.', '¡Alert!')->error();

                return redirect()->route('transacciones.index');
            }

            $data_array =  array(
                "ip"        => \Request::ip(),
                "affiliationNumber"  => (String)$UserCarnet->cod_cliente_emisor,
                "internalRef"  => (String)$reverso->id,
                "externalRef"  => $reverso->ref_bco,
                "transactionDate"  => $reverso->trans_bco_time,
                "otp"  => $otp_bco
            );

            $make_call = $this->callAPI('POST', config('webConfig.ProviderBanplusGenerateReverseTransaction'), json_encode($data_array));
            $response = json_decode($make_call, true);

            if(isset($response['code'], $response['message']))
            {
                $mensaje = "";

                if($response['code'] == 3324)
                $mensaje = 'La transacción ya fue anulada.';

                if($response['code'] == 3505)
                $mensaje = 'OTP Inválido.';

                if($response['code'] == 3323)
                $mensaje = 'Referencia de core es inválida.';

                if($response['code'] == 3504)
                $mensaje = 'Su OTP Expiró.';

                if($response['code'] == 500)
                $mensaje = 'No se recibio respuesta de los servicios.';

                $this->insertLog("No se recibio respuesta de los servicios.", $id, "BCO");               

                flash('Intente reversar la transacción nuevamente.', '¡Alert!')->error();

                return redirect()->route('transacciones.index');

            }
            else{
                if(isset($response['extRef'], $response['extCancelRef'], $response['createdOn']))
                {
                    $reverse_bco_ref = $response['extCancelRef'];
                    $reverse_bco_time = $response['createdOn'];
                }
            }

            if($reverse_bco_ref == null || $reverse_bco_time == null || $reverse_bco_ref == "" || $reverse_bco_time == "")
            {
                $this->insertLog("No se recibio respuesta de los servicios.", $id, "BCO");

                flash('Intente reversar la transacción nuevamente.', '¡Alert!')->error();

                return redirect()->route('transacciones.index');
            }
        }

        

        //dd(intval($findLedge->disp_post));

            $reversar = new \App\Models\trans_head();
            $reversar->fk_dni_miembros  = $reverso-> fk_dni_miembros;
            $reversar->fk_id_banco      = $reverso-> fk_id_banco;
            $reversar->fk_id_comer      = $reverso-> fk_id_comer;
            $reversar->monto    =   '-'.$reverso -> monto;



            $reversar->cancela_a        = $reverso-> cancela_a;
            $reversar->token            = $reverso-> token;
            $reversar->reverso          = $id;
            $reversar->status           = 4;
            $reversar->ip               = $clientIP;
            $reversar->token_status     = $reverso-> token_status;
            $reversar->token_time       = $reverso-> token_time;
            $reversar->origen           = $reverso-> origen;
            $reversar->created_at       = Carbon::now();
            $reversar->updated_at       = Carbon::now();
			$reversar->fk_monedas       = $reverso->fk_monedas;
            $reversar->carnet_id        = $reverso->carnet_id;
            $reversar->reverse_bco_ref  = $reverse_bco_ref;
            $reversar->reverse_bco_time = $reverse_bco_time;
            $reversar->save();
            $this->insertLog('Reverso de la transacción '.$id,$reversar->id);

        $trans_head = trans_head::find($id)
        ->update([
                'reverso'                 => $reversar->id,
                'reverse_bco_ref'         => $reverse_bco_ref,
                'reverse_bco_time'        => $reverse_bco_time,
                'ip'    => $clientIP,
            ]);

        //carga a la tabla ledge


            $ledge = new \App\Models\Ledge();
            $ledge->fk_id_trans_head= $id;
            $ledge->fk_dni_miembros= $findLedge->fk_dni_miembros;
            $ledge->monto= '-'.$findLedge->monto;
            $ledge->propina= '-'.$findLedge->propina;
            $ledge->disp_pre= intval($findLedge->disp_post);
            $ledge->disp_post= $findLedge->disp_post + $findLedge->monto /*+ $findLedge->propina*/;
            $ledge->save();

        $this->insertLog('Transacción cancelada por reverso',$id);
        flash('La transaccion ha sido reversada exitosamente.', '¡Operación Exitosa!')->success();
        return redirect()->route('transacciones.index');
    }


    public function filter(Request $request)
    {
        //dd($request);
        $user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

       if( $request->fecha_desde == null ){
            $time_desde= date('Y-m-d 00:00:00');

       }else{

        $fecha_desde = explode("/",$request->fecha_desde);
        $fecha_dia = $fecha_desde[0];
        $fecha_mes = $fecha_desde[1];
        $fecha_anio = $fecha_desde[2];
        $fecha_desde = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

        $fecha_desde = strtotime($fecha_desde);
        $time_desde = date('Y-m-d 00:00:00',$fecha_desde);
       }


       if ($request->fecha_hasta == null) {
          $time_hasta= date('Y-m-d 23:59:59');
       }else{


        $fecha_hasta = explode("/",$request->fecha_hasta);
        $fecha_dia = $fecha_hasta[0];
        $fecha_mes = $fecha_hasta[1];
        $fecha_anio = $fecha_hasta[2];
        $fecha_hasta = $fecha_anio."-".$fecha_mes."-".$fecha_dia;


        $fecha_hasta = strtotime($fecha_hasta);
        $time_hasta = date('Y-m-d 23:59:59',$fecha_hasta);
       }


    $user_comer = miem_come::where('fk_id_miembro',$user->id)->first();

       if ($rol == 3) {

        $IdsComercios = array();

        $comercio =  miem_come::select("miem_come.fk_id_comercio",'comercios.rif','comercios.es_sucursal','comercios.id')
        ->join('comercios','comercios.id','miem_come.fk_id_comercio')
        ->where("fk_id_miembro",$user->id)            
        ->first();

        $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))
        ->where('rif','=',$comercio->rif)
        ->get();

        //VALIDAR SI ES COMERCIO MASTER
        if(count($comercios) > 0 && !$comercio->es_sucursal)
        {
            $EsComercioMaster = true;

            foreach ($comercios as $key => $value) {
                array_push($IdsComercios, $value->id);
            }                
        }
        else
        {
            array_push($IdsComercios, $comercio->id);
        }   

        $query = trans_head::select(
            'trans_head.id as idTrans',
            'trans_head.created_at as fechaTrans',
            'trans_head.fk_dni_miembros','trans_head.fk_monedas',
            'users.nacionalidad',
            'users.dni',
            'users.first_name',
            'users.last_name',
            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcioncomercios"),
            //'comercios.descripcion as descripcionComercios',
            'trans_head.monto',
            'trans_head.propina',
            'trans_head.status',
            'trans_head.reverso',
            'monedas.mon_nombre as moneda',
            'trans_head.procesado',
            'trans_head.origen',
            'terminal.codigo_terminal_comercio',
			'carnet.carnet as carnet_cliente',
			'emisores.requiere_pin'			
        )
        ->join('users','users.id','trans_head.fk_dni_miembros')
        ->join('comercios','comercios.id','trans_head.fk_id_comer')
        ->join('bancos','bancos.id','trans_head.fk_id_banco')
        ->join('monedas','trans_head.fk_monedas','monedas.mon_id')
		->join('carnet','carnet.id','trans_head.carnet_id')
        ->leftJoin('terminal', 'terminal.id', '=', 'trans_head.TerminalId')
		->leftJoin('trans_gift_card', 'trans_gift_card.fk_trans_id', '=', 'trans_head.id')
		->leftJoin('carnet as carnet_gift', 'carnet_gift.id', '=', 'trans_gift_card.fk_carnet_id_recibe')
		->leftJoin('emisores', 'emisores.cod_emisor', '=', 'carnet_gift.cod_emisor')		
        ->whereIn('comercios.id',$IdsComercios)
        ->whereIn('trans_head.fk_id_comer',$IdsComercios)
        ->where('comercios.razon_social','!=','jackpotImportPagos')
        //->whereNotIn('comercios.id',[3])
        ->whereBetween('trans_head.created_at', [$time_desde, $time_hasta]);

        if($request->cedula){
            $transacciones = $query->where('users.dni','like',$request->cedula);
        }

        if($request->tarjeta){
            $transacciones = $query->where('carnet.carnet','like',$request->tarjeta);
        }

        if($request->mon_nombre){
            $transacciones = $query->where("trans_head.fk_monedas",$request->mon_nombre);
        }

        if($request->monto){
            $monto = str_replace(".", "",$request->monto);
            $monto = str_replace(",",".",$monto);
            $monto = $monto * 1;
            $trx = $query->where('trans_head.monto',$monto);
        }

        $transacciones = $query->orderBy('idTrans', 'ASC')->get();
       }else{
        $query = trans_head::select(
            'trans_head.id as idTrans',
            'trans_head.created_at as fechaTrans',
            'trans_head.fk_dni_miembros','trans_head.fk_monedas',
            'users.nacionalidad',
            'users.dni',
            'users.first_name',
            'users.last_name',
            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcioncomercios"),
            //'comercios.descripcion as descripcionComercios',
            'trans_head.monto',
            'trans_head.propina',
            'trans_head.status',
            'trans_head.reverso',
            'monedas.mon_nombre as moneda',
            'trans_head.procesado',
            'trans_head.origen',
            'terminal.codigo_terminal_comercio'
        )
        ->join('users','users.id','trans_head.fk_dni_miembros')
        ->join('comercios','comercios.id','trans_head.fk_id_comer')
        ->join('bancos','bancos.id','trans_head.fk_id_banco')
        ->leftJoin('carnet', 'carnet.id', '=', 'trans_head.carnet_id')
        ->join('monedas','trans_head.fk_monedas','monedas.mon_id')
        ->leftJoin('terminal', 'terminal.id', '=', 'trans_head.TerminalId')
        ->where('comercios.razon_social','!=','jackpotImportPagos')        
        //->whereNotIn('comercios.id',[3])
        ->whereBetween('trans_head.created_at', [$time_desde, $time_hasta]);
        
        
        if($request->cedula){
            $transacciones = $query->where('users.dni','like',$request->cedula);
            
        }

        if($request->tarjeta){
            $transacciones = $query->where('carnet.carnet','like',$request->tarjeta);
        }

        if($request->mon_nombre){
            $transacciones = $query->where("trans_head.fk_monedas",$request->mon_nombre);
             
            //dd($transacciones);
        }

        if($request->monto){
            $monto = str_replace(".", "",$request->monto);
            $monto = str_replace(",",".",$monto);
            $monto = $monto * 1;
            $trx = $query->where('trans_head.monto',$monto);
        }

        $transacciones = $query->orderBy('idTrans', 'DESC')->get();

        dd($transacciones);
        $transacciones->each(function($trans){
            $trans->carnet = carnet::where([
                ['fk_monedas', '=', $trans->fk_monedas],
                ['fk_id_miembro', '=', $trans->fk_dni_miembros]
            ])->first();
        });

       }
        return view('transacciones.index' )->with([
            'transacciones' => $transacciones,
            'rol' => $rol
        ]);


    }

/*VISTA DEL REPORTE CONSOLIDADO DE TRANSACCIONES*/
    public function reports_preview(){

        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        $EsComercioMaster = false;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

           $time_desde= date('Y-m-d 00:00:00');
           $fecha_hasta= date('Y-m-d 23:59:59');
           $fecha_hasta = strtotime($fecha_hasta);
           $time_hasta = date('Y-m-d 23:59:59',$fecha_hasta);

           //dd($time_desde.'------------------'.$time_hasta);


        if($rol== 3){
            /*$miem_come = miem_come::select("fk_id_comercio")->where("fk_id_miembro","=",Auth::user()->id)->get();
            $comercios = comercios::select("id","descripcion")->where("id","=",$miem_come[0]->fk_id_comercio)->get();*/
            $bancos = bancos::all();
            $comercio =  miem_come::select("miem_come.fk_id_comercio",'comercios.rif','comercios.es_sucursal')
            ->join('comercios','comercios.id','miem_come.fk_id_comercio')
            ->where("fk_id_miembro",$user->id)            
            ->first();

            $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))
            ->where('rif','=',$comercio->rif)
            ->get();

            //VALIDAR SI ES COMERCIO MASTER
            if(count($comercios) > 0 && !$comercio->es_sucursal)
            {
                $EsComercioMaster = true;
            }



                        $query = trans_head::select(
                            'trans_head.id as REFERENCIA',
                            'trans_head.created_at as FECHA',
                            'users.nacionalidad as NACIONALIDAD',
                            'users.dni as CEDULA',
                            'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                            'users.first_name as NOMBRE',
                            'users.last_name as APELLIDO',
                            'comercios.rif as RIF',
                            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                            //'comercios.razon_social as NOMBRE_COMERCIO',
                            'trans_head.monto as CONSUMO_CLIENTE',
                            'trans_head.propina as PROPINA',
                            'trans_head.comision as COMISION',
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                            WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                            ElSE
                            banc_comer.tasa_cobro_comer
                            END as tasa_afiliacion"),                                    
                            /* 'banc_comer.tasa_cobro_comer as TASA_AFILIACION', */
                            'banc_comer.num_cta_princ',
                            'banc_comer.num_cta_secu',
                            'trans_head.fk_monedas',
                            'monedas.mon_nombre',
                            /* DB::raw("trans_head.monto + trans_head.propina -
                                trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                 as ABONO_AL_COMERCIO"), */
                                 DB::raw("CASE 
                                 WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                                 WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                                 ElSE
                                 trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                 END as ABONO_AL_COMERCIO"),                                         
                            /*DB::raw("
                                (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                            /*DB::raw("
                                (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),*/
                            /* DB::raw("
                                    trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                    +
                                    trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                                 as COMISION_AFILIADO"), */
                                 DB::raw("CASE 
                                 WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                                 WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                                 ElSE
                                 trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                 END as COMISION_AFILIADO"),                                         
                            'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                            'trans_head.status AS ESTADO',
                            'trans_head.procesado as PROCESADO',
                            'trans_head.origen',
                            'trans_head.reverso')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        //->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')

                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        //->where("trans_head.fk_id_comer",$comercio->fk_id_comercio)
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")
                        ->where('monedas.mon_id',2)
                        ->whereBetween('trans_head.created_at',array(
                            $time_desde,
                            $time_hasta
                        ))
                        ->orderBy('trans_head.id','DESC');


                        if($EsComercioMaster)
                        {
                            $IdsComercios = array();

                            foreach ($comercios as $key => $value) {
                                array_push($IdsComercios, $value->id);
                            }

                            $trx = $query->whereIn("trans_head.fk_id_comer",$IdsComercios);                                
                        }
                        else
                        {
                            $trx = $query->where("trans_head.fk_id_comer",$comercio->fk_id_comercio);
                        }
                    

                        $transacciones = $trx->get();
                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;
                        }

                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>$transacciones,'transaccionesCount'=>count($transacciones),  'moneda' => '','EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios]);
                        }else{

                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=> $transacciones,'transaccionesCount'=>count($transacciones),  'moneda' => '','EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios]);
                        }

            /*return view('transacciones.reports_preview')
            ->with(['bancos' => $bancos,'comercios'=>$comercios,'selectComer'=>$miem_come[0]->fk_id_comercio]);*/

        }else if($rol== 2){
            $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))->get();
            $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();

            $query = trans_head::select(
                'trans_head.id as REFERENCIA',
                'trans_head.created_at as FECHA',
                'users.nacionalidad as NACIONALIDAD',
                'users.dni as CEDULA',
                'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                'users.first_name as NOMBRE',
                'users.last_name as APELLIDO',
                'comercios.rif as RIF',
                DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                //'comercios.razon_social as NOMBRE_COMERCIO',
                'trans_head.monto as CONSUMO_CLIENTE',
                'trans_head.propina as PROPINA',
                'trans_head.comision as COMISION',
                DB::raw("CASE 
                WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                ElSE
                banc_comer.tasa_cobro_comer
                END as tasa_afiliacion"),                                    
                /* 'banc_comer.tasa_cobro_comer as TASA_AFILIACION', */
                'banc_comer.num_cta_princ',
                'banc_comer.num_cta_secu',
                'trans_head.fk_monedas',
                'monedas.mon_nombre',
                /* DB::raw("trans_head.monto + trans_head.propina -
                    trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     as ABONO_AL_COMERCIO"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                     ElSE
                     trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     END as ABONO_AL_COMERCIO"),                                         
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),*/
                /* DB::raw("
                        trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                        +
                        trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                     as COMISION_AFILIADO"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                     ElSE
                     trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                     END as COMISION_AFILIADO"),                                         
                'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                'trans_head.status AS ESTADO',
                'trans_head.procesado as PROCESADO',
                'trans_head.origen',
                'trans_head.reverso',
                'canal.Nombre as canal')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        //->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')

                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        //->where("carnet.fk_id_banco",$banco->fk_id_banco)
                        //->where('trans_head.procesado',null)
                        ->leftJoin('canal_comer', function($join){
                            $join->on('canal_comer.fk_id_comer', '=', 'trans_head.fk_id_comer')
                                 ->on('canal_comer.fk_id_canal', '=', 'trans_head.CanalId');
                        })
                        ->leftjoin('canal','canal.id','canal_comer.fk_id_canal')
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")
                        ->where('monedas.mon_id',2)
                        ->whereBetween('trans_head.created_at',array(
                            $time_desde,
                            $time_hasta
                        ))
                        ->orderBy('trans_head.id','DESC');
                        $transacciones = $query->get();

                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;
                            if($value->PROCESADO){
                                $fecha_Unix_PROCESADO=strtotime($value->PROCESADO);
                                $cambio_formato_PROCESADO=date('d-m-Y',$fecha_Unix_PROCESADO);
                                $value->PROCESADO=$cambio_formato_PROCESADO;
                            }else{
                                $value->PROCESADO = "--";
                            }
                        }

                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>$transacciones,'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios, 'moneda' => '']);
                        }else{
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>count($transacciones),'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios,  'moneda' => '']);
                        }

        }else if($rol== 1){
            $bancos = bancos::all();
            $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))->get();

            $query = trans_head::select(
                'trans_head.id as REFERENCIA',
                'trans_head.created_at as FECHA',
                'users.nacionalidad as NACIONALIDAD',
                'users.dni as CEDULA',
                'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                'users.first_name as NOMBRE',
                'users.last_name as APELLIDO',
                'comercios.rif as RIF',
                DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                //'comercios.razon_social as NOMBRE_COMERCIO',
                'trans_head.monto as CONSUMO_CLIENTE',
                'trans_head.propina as PROPINA',
                'trans_head.comision as COMISION',
                DB::raw("CASE 
                WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                ElSE
                banc_comer.tasa_cobro_comer
                END as tasa_afiliacion"),                                    
                /* 'banc_comer.tasa_cobro_comer as TASA_AFILIACION', */
                'banc_comer.num_cta_princ',
                'banc_comer.num_cta_secu',
                'trans_head.fk_monedas',
                'monedas.mon_nombre',
                /* DB::raw("trans_head.monto + trans_head.propina -
                    trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     as ABONO_AL_COMERCIO"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                     ElSE
                     trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     END as ABONO_AL_COMERCIO"),                                         
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),*/
                /* DB::raw("
                        trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                        +
                        trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                     as COMISION_AFILIADO"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                     ElSE
                     trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                     END as COMISION_AFILIADO"),                                         
                'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                'trans_head.status AS ESTADO',
                'trans_head.procesado as PROCESADO',
                'trans_head.origen',
                'trans_head.reverso',
                'canal.Nombre as canal')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        //->join('carnet','carnet.fk_id_miembro','users.id')

                        ->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')


                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->leftJoin('canal_comer', function($join){
                            $join->on('canal_comer.fk_id_comer', '=', 'trans_head.fk_id_comer')
                                 ->on('canal_comer.fk_id_canal', '=', 'trans_head.CanalId');
                        })
                        ->leftjoin('canal','canal.id','canal_comer.fk_id_canal')                        
                        //->where('trans_head.procesado',null)
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->where('monedas.mon_id',2)
                        ->whereBetween('trans_head.created_at',array(
                            $time_desde,
                            $time_hasta
                        ))
                        ->orderBy('trans_head.id','DESC');
                        $transacciones = $query->get();
                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;
                        }
                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['bancos' => $bancos,'comercios'=>$comercios,'rol'=>$rol,'transacciones'=>$transacciones,'EsComercioMaster'=>$EsComercioMaster,'transaccionesCount'=>count($transacciones), 'moneda' => '']);
                        }else{
                            return view('transacciones.reports_preview')
                            ->with(['bancos' => $bancos,'comercios'=>$comercios,'rol'=>$rol,'transaccionesCount'=>count($transacciones), 'moneda' => '','EsComercioMaster'=>$EsComercioMaster]);
                        }

        }else if($rol== 6){
            $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))->get();
            $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();
 
            $query = trans_head::select(
                'trans_head.id as REFERENCIA',
                'trans_head.created_at as FECHA',
                'users.nacionalidad as NACIONALIDAD',
                'users.dni as CEDULA',
                'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                'users.first_name as NOMBRE',
                'users.last_name as APELLIDO',
                'comercios.rif as RIF',
                DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                //'comercios.razon_social as NOMBRE_COMERCIO',
                'trans_head.monto as CONSUMO_CLIENTE',
                'trans_head.propina as PROPINA',
                'trans_head.comision as COMISION',
                DB::raw("CASE 
                WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                ElSE
                banc_comer.tasa_cobro_comer
                END as tasa_afiliacion"),                                    
                /* 'banc_comer.tasa_cobro_comer as TASA_AFILIACION', */
                'banc_comer.num_cta_princ',
                'banc_comer.num_cta_secu',
                'trans_head.fk_monedas',
                'monedas.mon_nombre',
                /* DB::raw("trans_head.monto + trans_head.propina -
                    trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     as ABONO_AL_COMERCIO"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                     ElSE
                     trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     END as ABONO_AL_COMERCIO"),                                         
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),*/
                /* DB::raw("
                        trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                        +
                        trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                     as COMISION_AFILIADO"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                     ElSE
                     trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                     END as COMISION_AFILIADO"),                                         
                'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                'trans_head.status AS ESTADO',
                'trans_head.procesado as PROCESADO',
                'trans_head.origen',
                'trans_head.reverso',
                'canal.Nombre as canal')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->leftJoin('canal_comer', function($join){
                            $join->on('canal_comer.fk_id_comer', '=', 'trans_head.fk_id_comer')
                                 ->on('canal_comer.fk_id_canal', '=', 'trans_head.CanalId');
                        })
                        ->leftjoin('canal','canal.id','canal_comer.fk_id_canal')                        
                        
                        //->where('trans_head.procesado',null)
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->where('monedas.mon_id',2)
                        ->whereBetween('trans_head.created_at',array(
                            $time_desde,
                            $time_hasta
                        ));


                        if($banco){
                            $query = $query->where('carnet.fk_id_banco',$banco->fk_id_banco);
                        }


                        $query->orderBy('trans_head.id','DESC');
                        $transacciones = $query->get();

                        //dd($transacciones);
                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;
                        }
                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>$transacciones,'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios,  'moneda' => '']);
                        }else{
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>count($transacciones),'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios]);
                        }


        }else if($rol== 4){
            $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))->get();
            $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();

            $query = trans_head::select(
                                    'trans_head.id as REFERENCIA',
                                    'trans_head.created_at as FECHA',
                                    'users.nacionalidad as NACIONALIDAD',
                                    'users.dni as CEDULA',
                                    'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                                    'users.first_name as NOMBRE',
                                    'users.last_name as APELLIDO',
                                    'comercios.rif as RIF',
                                    DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                                    //'comercios.razon_social as NOMBRE_COMERCIO',
                                    'trans_head.monto as CONSUMO_CLIENTE',
                                    'trans_head.propina as PROPINA',
                                    'trans_head.comision as COMISION',
                                    'trans_head.reverso as REVERSO',
                                    'trans_head.origen',
                                    'banc_comer.tasa_cobro_comer as TASA_AFILIACION',
                                    'trans_head.fk_monedas',
                                    'monedas.mon_nombre',
                                    DB::raw("banc_comer.num_cta_princ as num_cta_princ"),
                                    DB::raw("banc_comer.num_cta_secu as num_cta_secu"),
                                    /*DB::raw("
                                        (trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as ABONO_AL_COMERCIO"),*/
                                    DB::raw("
                                        trans_head.monto + trans_head.propina - (
                                        trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.propina * (banc_comer.tasa_cobro_comer/100)))
                                         as ABONO_AL_COMERCIO"),
                                    /*DB::raw("
                                        (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                                    DB::raw("
                                        (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),
                                    'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                                    'trans_head.status AS ESTADO',
                                    'trans_head.procesado as PROCESADO',
                                    'canal.Nombre as canal')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        //->join('carnet','carnet.fk_id_miembro','users.id')

                        ->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')


                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->leftJoin('canal_comer', function($join){
                            $join->on('canal_comer.fk_id_comer', '=', 'trans_head.fk_id_comer')
                                 ->on('canal_comer.fk_id_canal', '=', 'trans_head.CanalId');
                        })
                        ->leftjoin('canal','canal.id','canal_comer.fk_id_canal')                        
                        //->where("carnet.fk_id_banco",$banco->fk_id_banco)
                        //->where('trans_head.procesado',null)
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->where('monedas.mon_id',2)
                        ->whereBetween('trans_head.created_at',array(
                            $time_desde,
                            $time_hasta
                        ))
                        ->orderBy('trans_head.id','DESC');
                        $transacciones = $query->get();
                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;

                            if($value->PROCESADO){
                                $fecha_Unix_PROCESADO=strtotime($value->PROCESADO);
                                $cambio_formato_PROCESADO=date('d-m-Y',$fecha_Unix_PROCESADO);
                                $value->PROCESADO=$cambio_formato_PROCESADO;
                            }else{
                                $value->PROCESADO = "--";
                            }
                        }
                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>$transacciones,'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios, 'moneda' => '']);
                        }else{
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>count($transacciones),'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios,  'moneda' => '']);
                        }
        }

    }


    /*METODO PARA GENERAR LA VISTA PRELIMINAR DEL HISTORICO CONSOLIDADO DE TRANSACCIONES*/
    public function preview_transactions(Request $request){

         /*se toma el rol y el usuario*/
            $user= User::find(Auth::user()->id);
            $roles= $user->roles;
            $rol = null;
            $EsComercioMaster = false;
            foreach ($roles as $value) {
                $rol = $value->id;
            }

            

            if( $request->fecha_desde == null ){
                    $time_desde= date('Y-m-d 00:00:00');

           }else{

                    $fecha_desde = explode("/",$request->fecha_desde);
                    $fecha_dia = $fecha_desde[0];
                    $fecha_mes = $fecha_desde[1];
                    $fecha_anio = $fecha_desde[2];
                    $fecha_desde = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                    $fecha_desde = strtotime($fecha_desde);
                    $time_desde = date('Y-m-d 00:00:00',$fecha_desde);
           }


           if( $request->fecha_hasta == null ){
                    $time_hasta = date('Y-m-d 23:59:59');
           }else{
                    $fecha_hasta = explode("/",$request->fecha_hasta);
                    $fecha_dia = $fecha_hasta[0];
                    $fecha_mes = $fecha_hasta[1];
                    $fecha_anio = $fecha_hasta[2];
                    $fecha_hasta = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                    $fecha_hasta = strtotime($fecha_hasta);
                    $time_hasta = date('Y-m-d 23:59:59',$fecha_hasta);
           }


           try{
                if( $rol == 1 || $rol == 2 ){

                    $query = trans_head::select(
                                    'trans_head.id as REFERENCIA',
                                    'trans_head.created_at as FECHA',
                                    'users.nacionalidad as NACIONALIDAD',
                                    'users.dni as CEDULA',
                                    'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                                    'users.first_name as NOMBRE',
                                    'users.last_name as APELLIDO',
                                    'comercios.rif as RIF',
                                    DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                                    //'comercios.razon_social as NOMBRE_COMERCIO',
                                    'trans_head.monto as CONSUMO_CLIENTE',
                                    'trans_head.propina as PROPINA',
                                    'trans_head.comision as COMISION',
                                    DB::raw("CASE 
                                    WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                                    WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                                    ElSE
                                    banc_comer.tasa_cobro_comer
                                    END as TASA_AFILIACION"),                                    
                                    /* 'banc_comer.tasa_cobro_comer as TASA_AFILIACION', */
                                    'banc_comer.num_cta_princ',
                                    'banc_comer.num_cta_secu',
                                    'trans_head.fk_monedas',
                                    'monedas.mon_nombre',
                                    /* DB::raw("trans_head.monto + trans_head.propina -
                                        trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                         as ABONO_AL_COMERCIO"), */
                                         DB::raw("CASE 
                                         WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                                         WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                                         ElSE
                                         trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                         END as ABONO_AL_COMERCIO"),                                         
                                    /*DB::raw("
                                        (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                                    /*DB::raw("
                                        (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),*/
                                    /* DB::raw("
                                            trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                            +
                                            trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                                         as COMISION_AFILIADO"), */
                                         DB::raw("CASE 
                                         WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                                         WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                                         ElSE
                                         trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                         END as COMISION_AFILIADO"),                                         
                                    'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                                    'trans_head.status AS ESTADO',
                                    'trans_head.procesado as PROCESADO',
                                    'trans_head.origen',
                                    'trans_head.reverso',
                                    'canal.Nombre as canal')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        //->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('carnet','carnet.id','trans_head.carnet_id')

                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')


                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')
                        ->leftJoin('canal_comer', function($join){
                            $join->on('canal_comer.fk_id_comer', '=', 'trans_head.fk_id_comer')
                                 ->on('canal_comer.fk_id_canal', '=', 'trans_head.CanalId');
                        })
                        ->leftjoin('canal','canal.id','canal_comer.fk_id_canal')
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->whereNotIn('status',[5])
                        //->where('trans_head.procesado',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));
                        if ($request->moneda) {
                            $query = $query->where('monedas.mon_id', $request->moneda);
                        }
                        /*if($request->estado != "1000"){
                            $estado = $request->estado * 1;
                            $trx = $query->where('trans_head.status',$request->estado);
                        }*/
                        if($request->monto){
                            $monto = str_replace(".", "",$request->monto);
                            $monto = str_replace(",",".",$monto);
                            $monto = $monto * 1;

                            $trx = $query->where('trans_head.monto',$monto);
                        }
                        $trx = $query->orderBy('trans_head.id','DESC');
                        $transacciones = $trx->get();

                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;
                        }
                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>$transacciones,'EsComercioMaster'=>$EsComercioMaster,'transaccionesCount'=>count($transacciones),'moneda' => $request->moneda ?? '']);
                        }else{
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transaccionesCount'=>count($transacciones), 'moneda' => $request->moneda ?? '','EsComercioMaster'=>$EsComercioMaster]);
                        }

                }else if($rol == 2 || $rol == 4 || $rol == 6 ){
                    //dd($request);
                    $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))->get();
                    $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();

                    $query = trans_head::select(
                        'trans_head.id as REFERENCIA',
                        'trans_head.created_at as FECHA',
                        'users.nacionalidad as NACIONALIDAD',
                        'users.dni as CEDULA',
                        'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                        'users.first_name as NOMBRE',
                        'users.last_name as APELLIDO',
                        'comercios.rif as RIF',
                        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                        //'comercios.razon_social as NOMBRE_COMERCIO',
                        'trans_head.monto as CONSUMO_CLIENTE',
                        'trans_head.propina as PROPINA',
                        'trans_head.comision as COMISION',
                        DB::raw("CASE 
                        WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                        WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                        ElSE
                        banc_comer.tasa_cobro_comer
                        END as tasa_afiliacion"),                                    
                        /* 'banc_comer.tasa_cobro_comer as TASA_AFILIACION', */
                        'banc_comer.num_cta_princ',
                        'banc_comer.num_cta_secu',
                        'trans_head.fk_monedas',
                        'monedas.mon_nombre',
                        /* DB::raw("trans_head.monto + trans_head.propina -
                            trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                             as ABONO_AL_COMERCIO"), */
                             DB::raw("CASE 
                             WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                             WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                             ElSE
                             trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                             END as ABONO_AL_COMERCIO"),                                         
                        /*DB::raw("
                            (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                        /*DB::raw("
                            (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),*/
                        /* DB::raw("
                                trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                +
                                trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                             as COMISION_AFILIADO"), */
                             DB::raw("CASE 
                             WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                             WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                             ElSE
                             trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                             END as COMISION_AFILIADO"),                                         
                        'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                        'trans_head.status AS ESTADO',
                        'trans_head.procesado as PROCESADO',
                        'trans_head.origen',
                        'trans_head.reverso',
                        'canal.Nombre as canal')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        //->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('carnet','carnet.id','trans_head.carnet_id')

                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')
                        ->leftJoin('canal_comer', function($join){
                            $join->on('canal_comer.fk_id_comer', '=', 'trans_head.fk_id_comer')
                                 ->on('canal_comer.fk_id_canal', '=', 'trans_head.CanalId');
                        })
                        ->leftjoin('canal','canal.id','canal_comer.fk_id_canal')
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->where("carnet.fk_id_banco",$banco->fk_id_banco)						
                        ->whereNotIn('status',[5])
                        //->where('trans_head.procesado',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));
                        if ($request->moneda) {
                            $query = $query->where('monedas.mon_id', $request->moneda);
                        }
                        /*if($request->estado != "1000" ){
                            $estado = $request->estado * 1;
                            $trx = $query->where('trans_head.status',$request->estado);
                        }*/

                        if($request->monto){
                            $monto = str_replace(".", "",$request->monto);
                            $monto = str_replace(",",".",$monto);
                            $monto = $monto * 1;

                            $trx = $query->where('trans_head.monto',$monto);
                        }
                        if($request->comercio){
                            $comercio = $request->comercio * 1;
                            $trx = $query->where('trans_head.fk_id_comer',$comercio);
                        }
                        if($request->cliente){
                            $trx = $query->where('users.dni','like','%'.$request->cliente.'%');
                        }
                        $trx = $query->orderBy('trans_head.id','DESC');
                        $transacciones = $trx->get();

                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;
                        }


                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>$transacciones,'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios, 'moneda' => $request->moneda ?? '']);
                        }else{
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>count($transacciones),'transaccionesCount'=>count($transacciones),'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios, 'moneda' => $request->moneda ?? '']);
                        }

                }else if ($rol == 3){
                    //dd($request);
                    $comercio =  miem_come::select("miem_come.fk_id_comercio",'comercios.rif','comercios.es_sucursal')
                    ->join('comercios','comercios.id','miem_come.fk_id_comercio')
                    ->where("fk_id_miembro",$user->id)            
                    ->first();

                    $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))
                    ->where('rif','=',$comercio->rif)
                    ->get();

                    //VALIDAR SI ES COMERCIO MASTER
                    if(count($comercios) > 0 && !$comercio->es_sucursal)
                    {
                        $EsComercioMaster = true;
                    }
                        $query = trans_head::select(
                            'trans_head.id as REFERENCIA',
                            'trans_head.created_at as FECHA',
                            'users.nacionalidad as NACIONALIDAD',
                            'users.dni as CEDULA',
                            'carnet.carnet as NUM_TARJETA_MEMBRESIA',
                            'users.first_name as NOMBRE',
                            'users.last_name as APELLIDO',
                            'comercios.rif as RIF',
                            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                            //'comercios.razon_social as NOMBRE_COMERCIO',
                            'trans_head.monto as CONSUMO_CLIENTE',
                            'trans_head.propina as PROPINA',
                            'trans_head.comision as COMISION',
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                            WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                            ElSE
                            banc_comer.tasa_cobro_comer
                            END as TASA_AFILIACION"),                                    
                            /* 'banc_comer.tasa_cobro_comer as TASA_AFILIACION', */
                            'banc_comer.num_cta_princ',
                            'banc_comer.num_cta_secu',
                            'trans_head.fk_monedas',
                            'monedas.mon_nombre',
                            /* DB::raw("trans_head.monto + trans_head.propina -
                                trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                 as ABONO_AL_COMERCIO"), */
                                 DB::raw("CASE 
                                 WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                                 WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                                 ElSE
                                 trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                 END as ABONO_AL_COMERCIO"),                                         
                            /*DB::raw("
                                (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as TASA_AFILIACION"),*/
                            /*DB::raw("
                                (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as COMISION_AFILIADO"),*/
                            /* DB::raw("
                                    trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                    +
                                    trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                                 as COMISION_AFILIADO"), */
                                 DB::raw("CASE 
                                 WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                                 WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                                 ElSE
                                 trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                 END as COMISION_AFILIADO"),                                         
                            'trans_head.neto as TOTAL_CONSUMO_CLIENTE',
                            'trans_head.status AS ESTADO',
                            'trans_head.procesado as PROCESADO',
                            'trans_head.origen',
                            'trans_head.reverso')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        //->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('carnet','carnet.id','trans_head.carnet_id')

                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')

                        //->where("trans_head.fk_id_comer",$comercio->fk_id_comercio)
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->whereNotIn('status',[5])
                        //->where('trans_head.procesado',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));
                        if ($request->moneda) {
                            $query = $query->where('monedas.mon_id', $request->moneda);
                        }
                        if($request->comercio)
                        {
                            $trx = $query->where("trans_head.fk_id_comer",$request->comercio);
                        }
                        else
                        {
                            if($EsComercioMaster)
                            {
                                $IdsComercios = array();

                                foreach ($comercios as $key => $value) {
                                    array_push($IdsComercios, $value->id);
                                }
    
                                $trx = $query->whereIn("trans_head.fk_id_comer",$IdsComercios);                                
                            }
                            else
                            {
                                $trx = $query->where("trans_head.fk_id_comer",$comercio->fk_id_comercio);
                            }
                        }                    
                        /*if($request->estado != "1000"){

                            $estado = $request->estado * 1;
                            $trx = $query->where('trans_head.status',$request->estado);
                        }*/
                        if($request->monto){
                            $monto = str_replace(".", "",$request->monto);
                            $monto = str_replace(",",".",$monto);
                            $monto = $monto * 1;

                            $trx = $query->where('trans_head.monto',$monto);
                        }

                        if($request->cliente){
                            $trx = $query->where('users.dni','like','%'.$request->cliente.'%');
                        }

                        $trx = $query->orderBy('trans_head.id','DESC');
                        $transacciones = $trx->get();

                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->FECHA);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->FECHA=$cambio_formato;
                        }
                        

                        if(count($transacciones) != 0){
                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=>$transacciones,'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios,'transaccionesCount'=>count($transacciones), 'moneda' => $request->moneda ?? '']);
                        }else{

                            return view('transacciones.reports_preview')
                            ->with(['rol'=>$rol,'transacciones'=> $transacciones,'EsComercioMaster'=>$EsComercioMaster,'comercios'=>$comercios,'transaccionesCount'=>count($transacciones), 'moneda' => $request->moneda ?? '']);
                        }
                }


           }catch(\Exception $e){
                flash(' '.$e, '¡Alert!')->error();
           }

    }


    /*VISTA DEL REPORTE DE LIQUIDACIÓN DE COMERCIOS*/
    public function reports_liq_comercios(){
        /*se toma el rol y el usuario*/
        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

        $Path = public_path('liquidacion-domiciliacion');

        try{                
            $directory = opendir($Path);

            $file = readdir($directory);                                    
        
        }
        catch(\Exception $e)
        {
            flash("No se pudo leer el directorio '". $Path ."', por favor valide que exista y que se posean los privilegios necesarios para leerlo.", '¡Alert!')->error();
        }

        $ProcessedFiles = Files_History::select('files_history.id','files_history.Filename','files_history.email','files_history.created_at')
        ->where('files_history.ProcessType', 1)
        ->orderBy('created_at', 'desc')
        ->take(20)
        ->get()
        ->toArray();         
    
        return view('transacciones.reports_liq_comercios', compact("ProcessedFiles"))
        ->with(['rol'=>$rol,'comercio'=>1,'domiciliacion'=>1,'nopermission'=>'']);
    }


    /*BLOQUEO TEMPORAL DE AQUELLOS REGISTROS APROBADOS QUE NO TENGA 30 HORAS*/
    public function bloquearTrans(){
        $transacciones = trans_head::select(
            'id')
        ->where('trans_head.procesado',null)
        ->whereIn('trans_head.status',[0])
        ->whereNotIn('trans_head.status',[5])
        ->where('trans_head.reverso',null)
        ->where(DB::raw("trans_head.created_at"),'>',DB::raw("current_timestamp - interval '30 hours'"))
        ->get();

        foreach($transacciones as $value => $v){
            $actualizar = trans_head::find($v->id)
                            ->update([
                                    'status' => 6,
            ]);
        }

    }

    /*DESBLOQUEO TEMPORAL DE AQUELLOS REGISTROS APROBADOS QUE NO TENGA 30 HORAS*/
    /*public function desbloquearTrans(){
        $transacciones = trans_head::select(
            'id')
        ->where('trans_head.procesado',null)
        ->whereIn('trans_head.status',[0])
        ->whereNotIn('trans_head.status',[5])
        ->where('trans_head.reverso',null)
        ->where(DB::raw("trans_head.created_at"),'>',DB::raw("current_timestamp - interval '30 hours'"))
        ->get();

        foreach($transacciones as $value => $v){
            $actualizar = trans_head::find($v->id)
                            ->update([
                                    0                                            => 0,
            ]);
        }



    }*/


    /*EXPORT DEL REPORTE DE LIQUIDACIÓN DE COMERCIOS*/
    public function export_liq_comercios(Request $request){
            $moneda = $request->moneda;
			$NombreMoneda = Moneda::find($moneda)->mon_nombre;
            /*se toma el rol y el usuario*/
            $user= User::find(Auth::user()->id);
            $roles= $user->roles;
            $rol = null;
            foreach ($roles as $value) {
                $rol = $value->id;
            }



            /*se toma la ip del cliente para registrar en base de datos*/
            $clientIP = \Request::ip();

           if( $request->fecha_desde == null ){

                    $time_desde= date('Y-m-d 00:00:00');

           }else{
                    $fecha_desde = explode("/",$request->fecha_desde);
                    $fecha_dia = $fecha_desde[0];
                    $fecha_mes = $fecha_desde[1];
                    $fecha_anio = $fecha_desde[2];
                    $fecha_desde = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                    $fecha_desde = strtotime($fecha_desde);
                    $time_desde = date('Y-m-d 00:00:00',$fecha_desde);
           }


           if( $request->fecha_hasta == null ){
                    $time_hasta = date('Y-m-d 23:59:59');
           }else{
                    $fecha_hasta = explode("/",$request->fecha_hasta);
                    $fecha_dia = $fecha_hasta[0];
                    $fecha_mes = $fecha_hasta[1];
                    $fecha_anio = $fecha_hasta[2];
                    $fecha_hasta = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                    //$fecha_hasta = strtotime($fecha_hasta."+1 days");
                    $fecha_hasta = strtotime($fecha_hasta);
                    $time_hasta = date('Y-m-d 23:59:59',$fecha_hasta);
           }

           if($request->fecha_desde == $request->fecha_hasta){
                $fecha_desde = explode("/",$request->fecha_desde);
                $fecha_dia = $fecha_desde[0];
                $fecha_mes = $fecha_desde[1];
                $fecha_anio = $fecha_desde[2];
                $fecha_desde = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                $fecha_hasta = explode("/",$request->fecha_hasta);
                $fecha_dia = $fecha_hasta[0];
                $fecha_mes = $fecha_hasta[1];
                $fecha_anio = $fecha_hasta[2];
                $fecha_hasta = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                $fecha_desde = strtotime($fecha_desde);
                $time_desde = date('Y-m-d 00:00:00',$fecha_desde);

                //$fecha_hasta = strtotime($fecha_hasta."+1 days");
                $fecha_hasta = strtotime($fecha_hasta);
                $time_hasta = date('Y-m-d 23:59:59',$fecha_hasta);


           }

           if(date('Y-m-d') >= date('Y-m-d',$fecha_hasta)){
                $fechaTope = date('Y-m-d H:i:s');
                $fechaTope = strtotime($fechaTope);
                $fechaTope = date('Y-m-d H:i:s',$fechaTope);
           }
           //dd($time_desde.'-----'.$time_hasta);
           //dd($fechaTope);
           try{
                    $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();
                    $descripbanco = '';
                    if($banco){
                        $descripbanco = bancos::select("descripcion")->where("id",$banco->fk_id_banco)->first();

                    }

                    //QUERY DOMICILIACION CLIENTES
                    $query3 = trans_head::select(
                        'trans_head.id as id',
                        'trans_head.id as referencia',
                        'trans_head.created_at as fecha_hora',
                        DB::raw("users.nacionalidad ||''|| users.dni as cedula_cliente"),
                        'carnet.carnet_real as carnet',                        
                        //'banc_comer.num_cta_secu',
                        //'comercios.razon_social as nombre_comercio',
                        DB::raw('SUM(trans_head.monto) as consumo'),
                        'trans_head.propina as propina',
                        DB::raw('SUM(trans_head.neto) as total_consumo'),
                        'monedas.mon_nombre as moneda',
                        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as razon_social"),
                        'comercios.codigo_afi_real as codigo',
                        'comercios.rif as rif',
                        'canal.Nombre as canal',
                        'terminal.codigo_terminal_comercio as terminal',
						'trans_head.ref_bco',
                        'trans_head.procesado as descargado'
                        /*,
                        'trans_head.status'*/
                    )
                    ->join('users','users.id','trans_head.fk_dni_miembros')
                    ->join('comercios','comercios.id','trans_head.fk_id_comer')
                    ->join('bancos','bancos.id','trans_head.fk_id_banco')
                    ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                    ->join('monedas', 'trans_head.fk_monedas', 'monedas.mon_id')
                    ->join('carnet', 'trans_head.carnet_id', 'carnet.id')
                    ->leftJoin('terminal', 'terminal.id', '=', 'trans_head.TerminalId')

                    ->leftJoin('canal_comer', 'canal_comer.id', '=', 'terminal.fk_id_comer_canal')
                    ->leftJoin('canal', 'canal.id', '=', 'canal_comer.fk_id_canal')

                    ->where('bancos.id',$banco->fk_id_banco)
                    ->where('trans_head.procesado',null)
                    ->whereIn('trans_head.status',[0,6])
                    ->whereNotIn('trans_head.status',[5])
                    ->where('trans_head.reverso',null)
                    ->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
					->whereRaw("substring(carnet.carnet,1,4) <> '6890'")					
                    //->where(DB::raw("trans_head.created_at"),'<',DB::raw("current_timestamp - interval '30 hours'"))
                    ->whereNotExists(function($q){
                            $q->select(DB::raw(1))
                            ->from("trans_head")
                            ->whereRaw("trans_head.referencia = users.id");
                    })
                    ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));
 
                    if(isset($fechaTope)){
                        $query3 = $query3->where('trans_head.created_at','<=',$fechaTope);
                    }
                    if($banco){
                        $query3 = $query3->where('bancos.id',$banco->fk_id_banco);
                    }
                    if($moneda){
                        $query3 = $query3->where('trans_head.fk_monedas',$moneda);
                     
                    }

                    $query3 = $query3->groupBy(
                        'trans_head.id','trans_head.created_at','cedula_cliente',
                        'comercios.rif','banc_comer.num_cta_secu','comercios.razon_social',
                        'trans_head.propina', 'trans_head.status','trans_head.procesado',
                        'monedas.mon_nombre', 'comercios.razon_social', 'carnet.carnet_real','comercios.rif','comercios.nombre_sucursal','terminal.codigo_terminal_comercio', 'canal.Nombre', 'comercios.codigo_afi_real')
                     //->orderBy('trans_head.created_at','DESC');
                    ->orderBy('trans_head.id','DESC');

                     $domiciliacion = $query3->get();


                     foreach ($domiciliacion as $key => $value) {
                            $fecha_Unix=strtotime($value->fecha_hora);
                            $cambio_formato=date('d-m-Y H:m:s',$fecha_Unix);
                            $value->fecha_hora=$cambio_formato;
                     }

                     $monto_total_enviado =0;

                    //QUERY CONSUMO

                    $query1 = trans_head::select(
                        'comercios.rif as rif',
                        //'banc_comer.num_cta_princ as cuenta',
                        DB::raw("CASE 
                                    WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.num_cta_princ_dolar
                                    WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.num_cta_princ_euro
                                    ElSE
                                    banc_comer.num_cta_princ
                                END as num_cuenta"),
                        'monedas.mon_nombre as moneda',
                        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                        DB::raw("CASE
									WHEN monedas.mon_nombre = 'DOLAR' THEN 
									(CASE WHEN trans_head.rompe_liquidacion = 2 THEN SUM(trans_gift_card.monto) ELSE SUM(trans_head.monto) END)
									ELSE
									SUM(trans_head.monto)
									END as venta_bruta"),
                        DB::raw('SUM(trans_head.propina) as propina'),
                        DB::raw("CASE
                            WHEN monedas.mon_nombre = 'DOLAR' THEN
                            (CASE WHEN trans_head.rompe_liquidacion = 2 THEN
								SUM(trans_gift_card.monto * (banc_comer.tasa_cobro_comer_dolar / 100)) ELSE SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)) END)

                             WHEN monedas.mon_nombre = 'EURO' THEN
                             SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100))

                             ELSE
                             SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100))
                             END as comision_afiliado_consumo

                        "),

                        DB::raw("CASE
                            WHEN monedas.mon_nombre = 'DOLAR' THEN
                            SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_dolar / 100))

                             WHEN monedas.mon_nombre = 'EURO' THEN
                             SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_euro / 100))

                             ELSE
                            SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100))
                             END as comision_afiliado_propina

                        "),
                        /*B::raw("
                            SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100)) as comision_afiliado_propina
                        "),*/
                        DB::raw("
                            CASE
                            WHEN monedas.mon_nombre = 'DOLAR' THEN
								(CASE WHEN trans_head.rompe_liquidacion = 2 THEN
									SUM(trans_gift_card.monto + trans_head.propina - trans_gift_card.monto * (banc_comer.tasa_cobro_comer_dolar/100) -  trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)) 
								ELSE 
									SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) -  trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)) 
								END)

                            WHEN monedas.mon_nombre = 'EURO' THEN
                            SUM(trans_head.monto + trans_head.propina -
                            trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) -     trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100))

                            ELSE
                            SUM(trans_head.monto + trans_head.propina -
                            trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100))

                            END as abono_al_comercio"),
                        'trans_head.procesado',
                        'trans_head.procesado as descargado',
                        'banc_comer.num_cta_secu as num_cta_secu',
                        DB::raw("generate_series( 1, 2 ) as v")
                    )

                    ->join('users','users.id','trans_head.fk_dni_miembros')
                    ->join('comercios','comercios.id','trans_head.fk_id_comer')
                    ->join('bancos','bancos.id','trans_head.fk_id_banco')
                    ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                    ->join('monedas', 'trans_head.fk_monedas', 'monedas.mon_id')
					->join('carnet', 'trans_head.carnet_id', 'carnet.id')
					->leftJoin('trans_gift_card', 'trans_gift_card.fk_trans_id', '=', 'trans_head.id')
                    ->where('bancos.id',$banco->fk_id_banco)
                    ->where('trans_head.procesado',null)
                    ->whereIn('trans_head.status',[0,6])
                    ->whereNotIn('trans_head.status',[5])
                    ->where('trans_head.reverso',null)
                    ->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
					->whereRaw("substring(carnet.carnet,1,4) <> '6890'")					
                    ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));


                    if(isset($fechaTope)){
                        $query1 = $query1->where('trans_head.created_at','<=',$fechaTope);
                    }
                    if($banco){
                        $query1 = $query1->where('bancos.id',$banco->fk_id_banco);
                    }
                    if($moneda){
                        $query1 = $query1->where('trans_head.fk_monedas',$moneda);
                    }
                    $query1 = $query1->groupBy(
                        'comercios.rif',
                        'banc_comer.num_cta_princ',
                        'banc_comer.num_cta_princ_dolar',
                        'banc_comer.num_cta_princ_euro',
                        'banc_comer.num_cta_secu',
                        'comercios.razon_social',
                        'trans_head.status',
                        'trans_head.procesado',
                        'monedas.mon_nombre',
                        'comercios.nombre_sucursal',
						'trans_head.rompe_liquidacion'
                    )
                    ->orderBy('comercios.rif','ASC');

                    //dd($query);



                    $consumo = $query1->get();
   
                    $queryTotales = trans_head::select(
                        DB::raw('SUM(trans_head.monto) as venta_bruta'),
                        DB::raw('SUM(trans_head.propina) as propina'),
                        /*DB::raw("
                                        SUM(trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.propina * (banc_comer.tasa_cobro_comer/100))) as comision_afiliado"),*/
                        /* DB::raw("
                            SUM(
                                trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                +
                                trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                            ) as comision_afiliado
                        "), */
                        DB::raw("CASE 
                        WHEN monedas.mon_nombre = 'DOLAR' THEN 
                        SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100))

                        WHEN monedas.mon_nombre = 'EURO' THEN 
                        SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100))

                        ElSE
                        
                        SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100))
                        END as comision_afiliado"),                        
                        /*DB::raw("
                                        SUM(trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as abono_al_comercio")*/
                        /* DB::raw("
                                  SUM(trans_head.monto + trans_head.propina -
                                        trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100))
                                         as abono_al_comercio"), */
                        DB::raw("CASE 
                        WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100))
                        WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100))
                        ElSE
                        SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100))
                        END as abono_al_comercio")                                         
                    )
                    ->join('users','users.id','trans_head.fk_dni_miembros')
                    ->join('comercios','comercios.id','trans_head.fk_id_comer')
                    ->join('bancos','bancos.id','trans_head.fk_id_banco')
                    ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                    ->join('monedas', 'trans_head.fk_monedas', 'monedas.mon_id')
                    ->join('carnet', 'trans_head.carnet_id', 'carnet.id')
                    ->where('bancos.id',$banco->fk_id_banco)
                    ->where('trans_head.procesado',null)
                    ->whereIn('trans_head.status',[0,6])
                    ->whereNotIn('trans_head.status',[5])
                    ->where('trans_head.reverso',null)
                    ->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
					->whereRaw("substring(carnet.carnet,1,4) <> '6890'")					
                    //->where(DB::raw("trans_head.created_at"),'<',DB::raw("current_timestamp - interval '30 hours'"))
                    ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));
                    if(isset($fechaTope)){
                        $queryTotales = $queryTotales->where('trans_head.created_at','<=',$fechaTope);
                    }
                    if($banco){
                        $queryTotales= $queryTotales->where('bancos.id',$banco->fk_id_banco);
                    }
                    if($moneda){
                        $queryTotales = $queryTotales->where('trans_head.fk_monedas',$moneda);
                     
                    }
                    $queryTotales = $queryTotales->groupBy(
                        'monedas.mon_nombre'
                    );
                    $queryTotales = $queryTotales->first();

//dd($queryTotales);

					$TotalVentaBruta = 0.0;
					$TotalPropina = 0.0;
					$TotalComision_Afiliado_Consumo = 0.0;
					$TotalComision_Afiliado_Propina = 0.0;
					$TotalAbonoComercio = 0.0;
					
					
                    foreach ($consumo as $key => $value) {
                        $procesado = trans_head::select(
                            'trans_head.procesado',
                            'trans_head.status'
                        )
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->where('comercios.rif',$value->rif)
                        ->first();


                        /*if($procesado->status == 2){
                            $consumo[$key]->estado = "Cancelada";
                        }else if($procesado->status == 0){
                            $consumo[$key]->estado = "Aprobada";
                        }else if($procesado->status == 1){
                            $consumo[$key]->estado = "Por Aprobar";
                        }else if($procesado->status == 3){
                            $consumo[$key]->estado = "Rechazada";
                        }else if($procesado->status == 4){
                            $consumo[$key]->estado = "Reversada";
                        }*/

                        if($value->v == 1){
                            $consumo[$key]->num_cuenta = $value->num_cuenta;
                        }else{
                            $consumo[$key]->num_cuenta = $value->num_cta_secu;
                        }


                        $consumo[$key]->procesado = $procesado->procesado;
                        //$consumo[$key]->descargado = $procesado->procesado;
                        $VentaBruta = round($consumo[$key]->venta_bruta, 2);
                        $consumo[$key]->venta_bruta = number_format(round($consumo[$key]->venta_bruta, 2), 2, ',', '.');						
						$TotalVentaBruta = $TotalVentaBruta + $VentaBruta;
                        
                        $Propina = round($consumo[$key]->propina, 2);
                        $consumo[$key]->propina = number_format(round($consumo[$key]->propina, 2), 2, ',', '.');						
						$TotalPropina = $TotalPropina + $Propina;
                        
                        $Comision_Afiliado_Consumo = round($consumo[$key]->comision_afiliado_consumo, 2);
                        $consumo[$key]->comision_afiliado_consumo = number_format(round($consumo[$key]->comision_afiliado_consumo, 2), 2, ',', '.');						
						$TotalComision_Afiliado_Consumo = $TotalComision_Afiliado_Consumo + $Comision_Afiliado_Consumo;
                        
                        $Comision_Afiliado_Propina = round($consumo[$key]->comision_afiliado_propina, 2);
                        $consumo[$key]->comision_afiliado_propina = number_format(round($consumo[$key]->comision_afiliado_propina, 2), 2, ',', '.');						
						$TotalComision_Afiliado_Propina = $TotalComision_Afiliado_Propina + $Comision_Afiliado_Propina;

                        $consumo[$key]->abono_al_comercio = number_format(($VentaBruta + $Propina) - ($Comision_Afiliado_Consumo + $Comision_Afiliado_Propina), 2, ',', '.');
						$TotalAbonoComercio = $TotalAbonoComercio + (($VentaBruta + $Propina) - ($Comision_Afiliado_Consumo + $Comision_Afiliado_Propina));
                     }

                     foreach ($domiciliacion as $key => $value) {

                       if($value->estado == 1){

                            $actualizar = trans_head::find($value->id)
                            ->update([
                                    'procesado' => (String)Carbon::now(),
                                    'ip'    => $clientIP,
                                    'status' => 2,
                            ]);

                            //$domiciliacion[$key]->estado = "Cancelada";

                       }else if($value->estado == 0 || $value->estado == 6){
                            $actualizar = trans_head::find($value->id)
                            ->update([
                                    'procesado' => (String)Carbon::now(),
                                    'ip'    => $clientIP,
                                    'status' => 0,
                            ]);
                            //$domiciliacion[$key]->estado = "Aprobada";

                       }else if($value->estado == 2){
                            //$domiciliacion[$key]->estado = "Cancelada";
                       }else if($value->estado == 3){
                            //$domiciliacion[$key]->estado = "Rechazada";
                       }else if($value->estado == 4){
                            //$domiciliacion[$key]->estado = "Reversada";
                       }

                        $procesado = trans_head::select(
                            'trans_head.procesado'
                        )
                        ->where('trans_head.id',$value->id)
                        ->first();

                        //ACTUALIZAR SALDO DEL CLIENTE CON LAS TRANSACCIONES AUTORIZADAS
                        //$CurrentUserTransaction = trans_head::select("carnet_id")
                        //->where("id", $value->id)
                        //->first();
                        
                        //$UserCarnet = carnet::select("id","disponible")                        
                        //->where("id", $CurrentUserTransaction->carnet_id)
                        //->first();

                        //$actualizarDisponible = carnet::find($UserCarnet->id)
                        //->update([
                                //'updated_at' => (String)Carbon::now(),
                                //'disponible'    => ($UserCarnet->disponible - $value->consumo),
                        //]);
                        //FIN ACTUALIZAR SALDO DEL CLIENTE CON LAS TRANSACCIONES AUTORIZADAS

                        $domiciliacion[$key]->descargado = $procesado->procesado;
                        $venta_bruta = (integer)$value->venta_bruta;
                        $monto_total_enviado = $monto_total_enviado + $venta_bruta;

                        $domiciliacion[$key]->consumo = number_format(round($domiciliacion[$key]->consumo, 2), 2, ',', '.');
                        $domiciliacion[$key]->propina = number_format(round($domiciliacion[$key]->propina, 2), 2, ',', '.');
                        $domiciliacion[$key]->total_consumo = number_format(round($domiciliacion[$key]->total_consumo, 2), 2, ',', '.');
                    }


                     foreach ($domiciliacion as $key => $value) {
                            $value->descargado=(String)Carbon::now();
                     }

                     foreach ($consumo as $key => $value) {
                        $value->procesado = (String)Carbon::now();
                        //$value->descargado = (String)Carbon::now();
                     }

                    if(count($domiciliacion) != 0 || count($consumo) != 0){

                            foreach ($consumo as $key =>$v) {

                                if( $key%2 == 0){
                                    $consumo[$key]->propina = '';
                                    $consumo[$key]->comision_afiliado_propina = '';
                                }else{
                                    $consumo[$key]->venta_bruta = '';
                                    $consumo[$key]->venta_neto = '';
                                    $consumo[$key]->abono_al_comercio = '';
                                    $consumo[$key]->comision_afiliado_consumo = '';
                                }

                                $consumo[$key]->v = '';
                                $consumo[$key]->num_cta_secu = '';
                            }

                            $path = public_path('liquidacion-domiciliacion');
                            $FileName = 'Reporte Liquidacion de Comercios '.str_replace('/', '-', $request->fecha_desde).' hasta '.str_replace('/', '-', $request->fecha_hasta).str_replace(':', '', ' ('.Carbon::now().')').' '.$NombreMoneda;

                            /*Insertar Archivo a Almacenar*/
                            $user= User::find(Auth::user()->id);
                            $id_user=$user->id;
                            $email = $user->email;
                            $ip = \Request::ip();

                            $File = new \App\Models\Files_History();
                            $File->user_id = $id_user;
                            $File->email = $email;
                            $File->ip = $ip;
                            $File->Filename = $FileName.'.xls';
                            $File->ProcessType = 1;
                            $File->save();                               

                            //$bloqueoTransacciones = $this->bloquearTrans();
                            Excel::create($FileName,function($excel) use($consumo,$domiciliacion,$queryTotales,$TotalVentaBruta,$TotalPropina,$TotalComision_Afiliado_Consumo,$TotalAbonoComercio){

                                        $queryTotales->venta_bruta = number_format(($TotalVentaBruta / 2), 2, ',', '.');
                                        $queryTotales->propina = number_format(($TotalPropina / 2), 2, ',', '.');
                                        $queryTotales->comision_afiliado = number_format(($TotalComision_Afiliado_Consumo / 2), 2, ',', '.');
                                        $queryTotales->abono_al_comercio = number_format(($TotalAbonoComercio / 2), 2, ',', '.');

                                        $excel->sheet('Liquidación a Comercio', function($sheet) use($consumo,$queryTotales) {

                                               $sheet->cell('A1', function($cells) {
                                                    $cells->setValue('Totales');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                    $cells->setBackground('ffff00');
                                                });

                                                $sheet->cell('D1', function($cells) {
                                                    $cells->setValue('Suma Venta Bruta');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                    $cells->setBackground('ffff00');
                                                });

                                                $sheet->cell('D2', function($cells)use($queryTotales) {

                                                    $cells->setValue($queryTotales->venta_bruta);
                                                    $cells->setAlignment('center');

                                                });

                                                $sheet->cell('E1', function($cells) {
                                                    $cells->setValue('Suma Propina');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                    $cells->setBackground('ffff00');
                                                });

                                                $sheet->cell('E2', function($cells)use($queryTotales) {
                                                    $cells->setValue($queryTotales->propina);
                                                    $cells->setAlignment('center');

                                                });

                                                $sheet->cell('F1', function($cells) {
                                                    $cells->setValue('Suma Comisión');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                    $cells->setBackground('ffff00');
                                                });

                                                $sheet->cell('F2', function($cells)use($queryTotales) {
                                                    $cells->setValue($queryTotales->comision_afiliado);
                                                    $cells->setAlignment('center');

                                                });

                                                $sheet->cell('G1', function($cells) {
                                                    $cells->setValue('Suma Abono al Comercio');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                    $cells->setBackground('ffff00');
                                                });

                                                $sheet->cell('G2', function($cells)use($queryTotales) {
                                                    $cells->setValue($queryTotales->abono_al_comercio);
                                                    $cells->setAlignment('center');

                                                });

                                                $sheet->cell('A4', function($cells) {
                                                    $cells->setValue('RIF');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                /*$sheet->cell('B4', function($cells)use($cuentas) {
                                                    $cells->setValue($cuentas->num_cuenta);
                                                    $cells->setAlignment('center');

                                                });*/
                                                $sheet->cell('B4', function($cells) {
                                                    $cells->setValue('N° CUENTA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('C4', function($cells) {
                                                    $cells->setValue('MONEDAS');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('D4', function($cells) {
                                                    $cells->setValue('NOMBRE COMERCIO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('E4', function($cells) {
                                                    $cells->setValue('VENTA BRUTA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('F4', function($cells) {
                                                    $cells->setValue('PROPINA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('G4', function($cells) {
                                                    $cells->setValue('COMISIÓN AFILIADO CONSUMO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('H4', function($cells) {
                                                    $cells->setValue('COMISIÓN AFILIADO PROPINA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('I4', function($cells) {
                                                    $cells->setValue('ABONO COMERCIO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                /*$sheet->cell('H4', function($cells) {
                                                    $cells->setValue('ESTADO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });*/
                                                $sheet->cell('J4', function($cells) {
                                                    $cells->setValue('PROCESADO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->setOrientation('lanscape');
                                                $sheet->fromArray($consumo,null,'A5',false,false);

                                        });

                                        $excel->sheet('Cargo a la Tarjeta', function($sheet) use($domiciliacion) {

                                                $sheet->cell('A1', function($cells) {
                                                    $cells->setValue('ID');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('B1', function($cells) {
                                                    $cells->setValue('REFERENCIA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('C1', function($cells) {
                                                    $cells->setValue('FECHA Y HORA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('D1', function($cells) {
                                                    $cells->setValue('CÉDULA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('E1', function($cells) {
                                                    $cells->setValue('CARNET');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('F1', function($cells) {
                                                    $cells->setValue('VENTA BRUTA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('G1', function($cells) {
                                                    $cells->setValue('PROPINA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('H1', function($cells) {
                                                    $cells->setValue('VENTA NETA');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('I1', function($cells) {
                                                    $cells->setValue('MONEDAS');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                $sheet->cell('J1', function($cells) {
                                                    $cells->setValue('COMERCIO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                
                                                $sheet->cell('K1', function($cells) {
                                                    $cells->setValue('CODIGO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });
                                                
                                                $sheet->cell('L1', function($cells) {
                                                    $cells->setValue('RIF');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->cell('M1', function($cells) {
                                                    $cells->setValue('CANAL');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });  

                                                $sheet->cell('N1', function($cells) {
                                                    $cells->setValue('TERMINAL');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });    

                                                $sheet->cell('O1', function($cells) {
                                                    $cells->setValue('REFERENCIA BANPLUS');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });  												

                                                 $sheet->cell('P1', function($cells) {
                                                    $cells->setValue('DESCARGADO');
                                                    $cells->setAlignment('center');
                                                    $cells->setFont(array('bold' => true));
                                                });

                                                $sheet->setOrientation('lanscape');
                                                $sheet->fromArray($domiciliacion,null,'A2',false,false);
                                        });


                                    })->store('xls',$path);

                                    flash('¡El archivo de Liquidacion de Comercios fue generado exitosamente con el nombre: '.$FileName .', lo encontrara primero en la lista.', '¡Generación exitosa!')->success();

                                    return response()->json([
                                        'Massage'      => "Generated",
                                    ],200);

                    }else{
                        return response()->json([
                            'Massage'      => "NoResult",
                        ],200);
                        //return view('transacciones.reports_liq_comercios')
                        //->with(['comercio'=>null,'domiciliacion'=>null,'nopermission'=>'']);
                    }


           }catch(\Exception $e){
                //dd($e);
                flash(' '.$e, '¡Alert!')->error();
           }

    }


	public function export_transactions($fecha_desde,$fecha_hasta,$estado,$comercio,$monto,$cliente,$moneda){
            //TODO: add moneda to excels
            /*se toma el rol y el usuario*/
            $user= User::find(Auth::user()->id);
            $roles= $user->roles;
            $rol = null;
            foreach ($roles as $value) {
                $rol = $value->id;
            }

            /*se toma la ip del cliente para registrar en base de datos*/
            $clientIP = \Request::ip();



        $time_desde = date('Y-m-d 00:00:00',strtotime($fecha_desde));
        $time_hasta = date('Y-m-d 23:59:59',strtotime($fecha_hasta));

        try{

            if($rol == 2 || $rol == 4 || $rol == 6){
                        $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();
                        $query = trans_head::select(
                                    'trans_head.id as referencia',
                                    'trans_head.origen as tipo',
                                    'trans_head.created_at as fecha_hora',
                                    DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                                    'carnet.carnet as num_tarjeta_membresia',
                                    'users.first_name as nombre',
                                    'users.last_name as apellido',
                                    'comercios.rif as rif',
                                    DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                                    //'comercios.razon_social as nombre_comercio',
                                    'trans_head.monto as consumo_cliente',
                                    'trans_head.propina as propina',
                                    'monedas.mon_nombre as moneda',
                                    //DB::raw("banc_comer.num_cta_princ as num_cta_princ"),
                                    //DB::raw("banc_comer.num_cta_secu as num_cta_secu"),
                                    /*DB::raw("
                                        (trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as abono_al_comercio"),*/
                                    /* DB::raw("trans_head.monto + trans_head.propina -
                                        trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                         as abono_al_comercio"), */
                                         DB::raw("CASE 
                                         WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                                         WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                                         ElSE
                                         trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                         END as abono_al_comercio"),
                                         DB::raw("CASE 
                                         WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                                         WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                                         ElSE
                                         banc_comer.tasa_cobro_comer
                                         END as tasa_afiliacion"),                                         
                                    /* 'banc_comer.tasa_cobro_comer as tasa_afiliacion', */
                                    /* DB::raw("
                                            trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                            +
                                            trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                                         as comision_afiliado"), */
                                         DB::raw("CASE 
                                         WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                                         WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                                         ElSE
                                         trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                         END as comision_afiliado"),                                         
                                    /*DB::raw("
                                        (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as tasa_afiliacion"),*/
                                    /*DB::raw("
                                        (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as comision_afiliado"),*/
                                    'trans_head.neto as total_consumo_cliente',
                                    'trans_head.status AS estado',
                                    'trans_head.procesado as procesado',
                                    'trans_head.reverso as v')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->join("monedas","monedas.mon_id","trans_head.fk_monedas")
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")
                        ->where("carnet.fk_id_banco",$banco->fk_id_banco)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->whereNotIn('status',[5]);
                        
                        if($estado != '1000'){
                            $query = $query->where("trans_head.status",$estado);
                        }

                        if($comercio != 0){
                             $query = $query->where("comercios.id",$comercio);
                        }

                        if($monto){
                            $monto = str_replace(".", "",$monto);
                            $monto = str_replace(",",".",$monto);
                            $monto = $monto * 1;

                            $trx = $query->where('trans_head.monto',$monto);
                        }

                        /*if($monto != 0){
                            $query = $query->where("trans_head.monto",$monto);
                        }*/

                        if($cliente != 0){
                            $query = $query->where("users.dni",$cliente);
                        }


                        if($moneda){
                            $query = $query->where("carnet.fk_monedas",$moneda);
                        }

                        $query->orderBy('trans_head.id','DESC');
                        $transacciones = $query->get();

                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->fecha_hora);
                            $cambio_formato=date('d-m-Y H:i:s',$fecha_Unix);
                            $value->fecha_hora=$cambio_formato;
                            $fecha_Unix_Descarga=strtotime($value->procesado);
                            $cambio_formato_Descarga=date('d-m-Y H:i:s',$fecha_Unix_Descarga);
                            if ($rol != 2) {
                            $value->num_tarjeta_membresia = substr($value->num_tarjeta_membresia,-20,4) .' XXXX XXXX '. substr($value->num_tarjeta_membresia,-4);
                            }
                            if($value->procesado == null){
                                 $value->procesado = "--";
                            }else{
                                $value->procesado=$cambio_formato_Descarga;
                            }

                        }
                        foreach ($transacciones as $key => $value) {
                                if($value->estado == 0){
                                    if($value->v != null){
                                        $transacciones[$key]->estado = "Cancelada por Reverso";
                                    }else{
                                        $transacciones[$key]->estado = "Aprobada";
                                    }
                                }else if($value->estado == 1){
                                        $transacciones[$key]->estado = "Por Autorizar";
                                }else if($value->estado == 2){
                                        $transacciones[$key]->estado = "Cancelada";
                                }else if($value->estado == 3){
                                        $transacciones[$key]->estado = "Rechazada";
                                }else if($value->estado == 4){
                                        $transacciones[$key]->estado = "Reverso";
                                }else if($value->estado == 6){
                                        $transacciones[$key]->estado = "Aprobada";
                                }

                                $transacciones[$key]->consumo_cliente = number_format($transacciones[$key]->consumo_cliente, 2, ',', '.');
                                $transacciones[$key]->propina = number_format($transacciones[$key]->propina, 2, ',', '.');
                                $transacciones[$key]->abono_al_comercio = number_format($transacciones[$key]->abono_al_comercio, 2, ',', '.');
                                $transacciones[$key]->tasa_afiliacion = number_format($transacciones[$key]->tasa_afiliacion, 2, ',', '.');
                                $transacciones[$key]->comision_afiliado = number_format($transacciones[$key]->comision_afiliado, 2, ',', '.');
                                $transacciones[$key]->total_consumo_cliente = number_format($transacciones[$key]->total_consumo_cliente, 2, ',', '.');

                                $transacciones[$key]->v = '';
                        }

        }else if ($rol == 3){

                        $comerciousuario =  miem_come::select("miem_come.fk_id_comercio",'comercios.rif','comercios.es_sucursal')
                        ->join('comercios','comercios.id','miem_come.fk_id_comercio')
                        ->where("fk_id_miembro",$user->id)            
                        ->first();
            
                        $comercios = comercios::select('id', DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"))
                        ->where('rif','=',$comerciousuario->rif)
                        ->get();
            
                        //VALIDAR SI ES COMERCIO MASTER
                        if(count($comercios) > 0 && !$comerciousuario->es_sucursal)
                        {
                            $EsComercioMaster = true;
                        }
                        else
                        {
                            $EsComercioMaster = false;
                        }

                        $query = trans_head::select(
                            'trans_head.id as referencia',
                            'trans_head.origen as tipo',
                            'trans_head.created_at as fecha_hora',
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            'carnet.carnet as num_tarjeta_membresia',
                            'users.first_name as nombre',
                            'users.last_name as apellido',
                            'comercios.rif as rif',
                            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                            //'comercios.razon_social as nombre_comercio',
                            'trans_head.monto as consumo_cliente',
                            'trans_head.propina as propina',
                            'monedas.mon_nombre as moneda',
                            //DB::raw("banc_comer.num_cta_princ as num_cta_princ"),
                            //DB::raw("banc_comer.num_cta_secu as num_cta_secu"),
                            /*DB::raw("
                                (trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as abono_al_comercio"),*/
                            /* DB::raw("trans_head.monto + trans_head.propina -
                                trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                 as abono_al_comercio"), */
                                 DB::raw("CASE 
                                 WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                                 WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                                 ElSE
                                 trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                                 END as abono_al_comercio"),
                                 DB::raw("CASE 
                                 WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                                 WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                                 ElSE
                                 banc_comer.tasa_cobro_comer
                                 END as tasa_afiliacion"),                                         
                            /* 'banc_comer.tasa_cobro_comer as tasa_afiliacion', */
                            /* DB::raw("
                                    trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                    +
                                    trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                                 as comision_afiliado"), */
                                 DB::raw("CASE 
                                 WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                                 WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                                 ElSE
                                 trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                                 END as comision_afiliado"),                                         
                            /*DB::raw("
                                (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as tasa_afiliacion"),*/
                            /*DB::raw("
                                (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as comision_afiliado"),*/
                            'trans_head.neto as total_consumo_cliente',
                            'trans_head.status AS estado',
                            'trans_head.procesado as procesado',
                            'trans_head.reverso as v')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
						->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')

                        //->where("trans_head.fk_id_comer",$comercio->fk_id_comercio)
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->whereNotIn('status',[5]);
                        if ($moneda) {
                            $query = $query->where('monedas.mon_id', $moneda);
                        }
                        if($estado != '1000'){
                            $query = $query->where("trans_head.status",$estado);
                        }

                        if($monto){
                            $monto = str_replace(".", "",$monto);
                            $monto = str_replace(",",".",$monto);
                            $monto = $monto * 1;

                            $query = $query->where('trans_head.monto',$monto);
                        }

                        if($comercio != 0)
                        {
                            $query = $query->where("trans_head.fk_id_comer",$comercio);
                        }
                        else
                        {
                            if($EsComercioMaster)
                            {
                                $IdsComercios = array();

                                foreach ($comercios as $key => $value) {
                                    array_push($IdsComercios, $value->id);
                                }
    
                                $query = $query->whereIn("trans_head.fk_id_comer",$IdsComercios);                                
                            }
                            else
                            {
                                $query = $query->where("trans_head.fk_id_comer",$comerciousuario->fk_id_comercio);
                            }
                        }

                        /*if($monto != 0){
                            $query = $query->where("trans_head.monto",$monto);
                        }*/

                        if($cliente != 0){
                            $query = $query->where("users.dni",$cliente);
                        }

                        $query->orderBy('trans_head.id','DESC');

                        $transacciones = $query->get();

                        foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->fecha_hora);
                            $cambio_formato=date('d-m-Y',$fecha_Unix);
                            $value->fecha_hora=$cambio_formato;
                        }
                        foreach ($transacciones as $key => $value) {
                                if($value->estado == 0){
                                    if($value->v != null){
                                        $transacciones[$key]->estado = "Cancelada por Reverso";
                                    }else{
                                        $transacciones[$key]->estado = "Aprobada";
                                    }
                                }else if($value->estado == 1){
                                        $transacciones[$key]->estado = "Por Autorizar";
                                }else if($value->estado == 2){
                                        $transacciones[$key]->estado = "Cancelada";
                                }else if($value->estado == 3){
                                        $transacciones[$key]->estado = "Rechazada";
                                }else if($value->estado == 4){
                                        $transacciones[$key]->estado = "Reverso";
                                }else if($value->estado == 6){
                                    $transacciones[$key]->estado = "Aprobada";
                                }

                                $transacciones[$key]->consumo_cliente = number_format($transacciones[$key]->consumo_cliente, 2, ',', '.');
                                $transacciones[$key]->propina = number_format($transacciones[$key]->propina, 2, ',', '.');
                                $transacciones[$key]->abono_al_comercio = number_format($transacciones[$key]->abono_al_comercio, 2, ',', '.');
                                $transacciones[$key]->tasa_afiliacion = number_format($transacciones[$key]->tasa_afiliacion, 2, ',', '.');
                                $transacciones[$key]->comision_afiliado = number_format($transacciones[$key]->comision_afiliado, 2, ',', '.');
                                $transacciones[$key]->total_consumo_cliente = number_format($transacciones[$key]->total_consumo_cliente, 2, ',', '.');
                                $transacciones[$key]->v = '';                                

                        }
                        unset($transacciones->v);

        }else if ($rol == 1){
                    
            $query = trans_head::select(
                'trans_head.id as referencia',
                'trans_head.origen as tipo',
                'trans_head.created_at as fecha_hora',
                DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                'carnet.carnet as num_tarjeta_membresia',
                'users.first_name as nombre',
                'users.last_name as apellido',
                'comercios.rif as rif',
                DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                //'comercios.razon_social as nombre_comercio',
                'trans_head.monto as consumo_cliente',
                'trans_head.propina as propina',
                'monedas.mon_nombre as moneda',
                //DB::raw("banc_comer.num_cta_princ as num_cta_princ"),
                //DB::raw("banc_comer.num_cta_secu as num_cta_secu"),
                /*DB::raw("
                    (trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as abono_al_comercio"),*/
                /* DB::raw("trans_head.monto + trans_head.propina -
                    trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     as abono_al_comercio"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100)
                     ElSE
                     trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)
                     END as abono_al_comercio"),
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                     WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                     ElSE
                     banc_comer.tasa_cobro_comer
                     END as tasa_afiliacion"),                                         
                /* 'banc_comer.tasa_cobro_comer as tasa_afiliacion', */
                /* DB::raw("
                        trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                        +
                        trans_head.propina * (banc_comer.tasa_cobro_comer / 100)
                     as comision_afiliado"), */
                     DB::raw("CASE 
                     WHEN monedas.mon_nombre = 'DOLAR' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100)
                     WHEN monedas.mon_nombre = 'EURO' THEN trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100)
                     ElSE
                     trans_head.monto * (banc_comer.tasa_cobro_comer / 100)
                     END as comision_afiliado"),                                         
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as tasa_afiliacion"),*/
                /*DB::raw("
                    (trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100))) as comision_afiliado"),*/
                'trans_head.neto as total_consumo_cliente',
                'trans_head.status AS estado',
                'trans_head.procesado as procesado',
                'trans_head.reverso as v')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
						->join('carnet','carnet.id','trans_head.carnet_id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->join('monedas','monedas.mon_id','trans_head.fk_monedas')

                        //->where('trans_head.procesado',null)
						->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
						->whereRaw("substring(carnet.carnet,1,4) <> '6890'")						
                        ->whereBetween('trans_head.created_at',array($time_desde,
                            $time_hasta))
                        ->whereNotIn('status',[5])
                        ->orderBy('trans_head.id','DESC');
                        if ($moneda) {
                            $query = $query->where('monedas.mon_id', $moneda);
                        }
            $transacciones = $query->get();
            foreach ($transacciones as $key => $value) {
                            $fecha_Unix=strtotime($value->fecha_hora);
                            $cambio_formato=date('d-m-Y H:i:s',$fecha_Unix);
                            $fecha_Unix_Descarga=strtotime($value->procesado);
                            $cambio_formato_Descarga=date('d-m-Y H:i:s',$fecha_Unix_Descarga);
                            $value->fecha_hora=$cambio_formato;
                            $value->procesado=$cambio_formato_Descarga;

                            if($value->procesado == null){
                                 $value->procesado = $cambio_formato;
                            }else{
                                $value->procesado=$cambio_formato_Descarga;
                            }

                        }
            foreach ($transacciones as $key => $value) {
                if($value->estado == 0){
                        if($value->v != null){
                            $transacciones[$key]->estado = "Cancelada por Reverso";
                        }else{
                            $transacciones[$key]->estado = "Aprobada";
                        }
                }else if($value->estado == 1){
                        $transacciones[$key]->estado = "Por Autorizar";
                }else if($value->estado == 2){
                        $transacciones[$key]->estado = "Cancelada";
                }else if($value->estado == 3){
                        $transacciones[$key]->estado = "Rechazada";
                }else if($value->estado == 4){
                        $transacciones[$key]->estado = "Reverso";
                }else if($value->estado == 6){
                        $transacciones[$key]->estado = "Aprobada";
                }

                $transacciones[$key]->consumo_cliente = number_format($transacciones[$key]->consumo_cliente, 2, ',', '.');
                $transacciones[$key]->propina = number_format($transacciones[$key]->propina, 2, ',', '.');
                $transacciones[$key]->abono_al_comercio = number_format($transacciones[$key]->abono_al_comercio, 2, ',', '.');
                $transacciones[$key]->tasa_afiliacion = number_format($transacciones[$key]->tasa_afiliacion, 2, ',', '.');
                $transacciones[$key]->comision_afiliado = number_format($transacciones[$key]->comision_afiliado, 2, ',', '.');
                $transacciones[$key]->total_consumo_cliente = number_format($transacciones[$key]->total_consumo_cliente, 2, ',', '.');
                $transacciones[$key]->v = '';
            }

        }
              
                //dd($transacciones);
                Excel::create('Reporte Consolidado de Transacciones '.$fecha_desde.' hasta '.$fecha_hasta,function($excel) use($transacciones){
                    $excel->sheet('Operaciones', function($sheet) use($transacciones) {
                        $sheet->setOrientation('lanscape');
                        $sheet->fromArray($transacciones);
                    });
                })->export('xls');


        }catch(\Exception $e){
            flash(' ', '¡Alert!')->error();
        }

    }



    //metodo que trae informacion para la interfaz del dashboard

    public function indexConsolidado($moneda=null){
           
            $user= User::find(Auth::user()->id);
            $roles= $user->roles;
            $rol = null;

            if($moneda == null){
                $moneda = config('webConfig.bolivar');               
            }


            foreach ($roles as $value) {
                 $rol = $value->id;
            }
 
            $anio = [];
            if($rol == 3){


                        $fk_id_comercio = miem_come::where("fk_id_miembro",$user->id)->first();

                        $totalAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalautorizaciones"))
                                ->where("status","=",0)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($totalAutorizaciones->totalautorizaciones!=null){
                            $totalAutorizaciones = $totalAutorizaciones->totalautorizaciones;
                        }else{
                            $totalAutorizaciones = 0;
                        }


                        $cantidadAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadautorizaciones"))
                                ->where("status","=",0)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadAutorizaciones->cantidadautorizaciones!=null){
                            $cantidadAutorizaciones = $cantidadAutorizaciones->cantidadautorizaciones;
                        }else{
                            $cantidadAutorizaciones = 0;
                        }

                        $totalPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalporautorizar"))
                                ->where("status","=",1)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($totalPorAutorizar->totalporautorizar != null){
                            $totalPorAutorizar = $totalPorAutorizar->totalporautorizar;
                            $totalPorAutorizar = number_format($totalPorAutorizar, 2, ',', '.');
                        }else{
                            $totalPorAutorizar = number_format(0, 2, ',', '.');
                        }


                        $cantidadPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadporautorizar"))
                                ->where("status","=",1)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadPorAutorizar->cantidadporautorizar != null){
                            $cantidadPorAutorizar = $cantidadPorAutorizar->cantidadporautorizar;
                        }else{
                            $cantidadPorAutorizar = 0;
                        }


                        $totalCanceladas = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalcanceladas"))
                                ->where("status","=",4)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($totalCanceladas->totalcanceladas != null){
                            $totalCanceladas = $totalCanceladas->totalcanceladas;
                            $totalCanceladas = number_format($totalCanceladas, 2, ',', '.');
                        }else{
                            $totalCanceladas = number_format(0, 2, ',', '.');
                        }

                        $cantidadCanceladas = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadcanceladas"))
                                ->where("status","=",4)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadCanceladas->cantidadcanceladas != null){
                            $cantidadCanceladas = $cantidadCanceladas->cantidadcanceladas;
                        }else{
                            $cantidadCanceladas = 0;
                        }

                        $totalReversadas = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalreversadas"))
                                ->where("status","=",4)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($totalReversadas != null){
                            $totalReversadas = $totalReversadas->totalreversadas;
                        }else{
                            $totalReversadas = 0;
                        }


                        $cantidadReversadas = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadreversadas"))
                                ->where("status","=",4)
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadReversadas->cantidadreversadas != null){
                            $cantidadReversadas = $cantidadReversadas->cantidadreversadas;
                        }else{
                            $cantidadReversadas = 0;
                        }

                        if($totalReversadas != null){
                            $totalReversadas = $totalReversadas * -1;
                        }else{
                            $totalReversadas = 0;
                        }

                        if($totalAutorizaciones != null || $totalReversadas != null){

                            if($totalAutorizaciones > $totalReversadas){
                                $totalAutorizaciones = ($totalAutorizaciones - $totalReversadas);
                            }else{
                                $totalAutorizaciones = ($totalReversadas - $totalAutorizaciones);
                            }
                            $totalAutorizaciones = number_format($totalAutorizaciones, 2, ',', '.');
                        }else{
                            $totalAutorizaciones = number_format(0, 2, ',', '.');
                        }

 
                        }else{
                                //dd($moneda);
                        //AQUI SE CARGA LA DATA SI ES OTRO PERFIL DIFERENTE A COMERCIO
                        $totalAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalautorizaciones"))
                                ->where("status","=",0) 
                                ->where("fk_monedas","=",$moneda)                             
                                ->first();

                        if($totalAutorizaciones->totalautorizaciones!=null){
                            $totalAutorizaciones = $totalAutorizaciones->totalautorizaciones;
                        }else{
                            $totalAutorizaciones = 0;
                        }


                        $cantidadAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadautorizaciones"))
                                ->where("status","=",0)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadAutorizaciones->cantidadautorizaciones!=null){
                            $cantidadAutorizaciones = $cantidadAutorizaciones->cantidadautorizaciones;
                        }else{
                            $cantidadAutorizaciones = 0;
                        }

                        $totalPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalporautorizar"))
                                ->where("status","=",1)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($totalPorAutorizar->totalporautorizar != null){
                            $totalPorAutorizar = $totalPorAutorizar->totalporautorizar;
                            $totalPorAutorizar = number_format($totalPorAutorizar, 2, ',', '.');
                        }else{
                            $totalPorAutorizar = number_format(0, 2, ',', '.');
                        }


                        $cantidadPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadporautorizar"))
                                ->where("status","=",1)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadPorAutorizar->cantidadporautorizar != null){
                            $cantidadPorAutorizar = $cantidadPorAutorizar->cantidadporautorizar;
                        }else{
                            $cantidadPorAutorizar = 0;
                        }


                        $totalCanceladas = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalcanceladas"))
                                ->where("status","=",4)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($totalCanceladas->totalcanceladas != null){
                            $totalCanceladas = $totalCanceladas->totalcanceladas;
                            $totalCanceladas = number_format($totalCanceladas, 2, ',', '.');
                        }else{
                            $totalCanceladas = number_format(0, 2, ',', '.');
                        }

                        $cantidadCanceladas = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadcanceladas"))
                                ->where("status","=",4)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadCanceladas->cantidadcanceladas != null){
                            $cantidadCanceladas = $cantidadCanceladas->cantidadcanceladas;
                        }else{
                            $cantidadCanceladas = 0;
                        }

                        $totalReversadas = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalreversadas"))
                                ->where("status","=",4)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($totalReversadas != null){
                            $totalReversadas = $totalReversadas->totalreversadas;
                        }else{
                            $totalReversadas = 0;
                        }


                        $cantidadReversadas = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadreversadas"))
                                ->where("status","=",4)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        if($cantidadReversadas->cantidadreversadas != null){
                            $cantidadReversadas = $cantidadReversadas->cantidadreversadas;
                        }else{
                            $cantidadReversadas = 0;
                        }

                        if($totalReversadas != null){
                            $totalReversadas = $totalReversadas * -1;
                        }else{
                            $totalReversadas = 0;
                        }

                        if($totalAutorizaciones != null || $totalReversadas != null){

                            if($totalAutorizaciones > $totalReversadas){
                                $totalAutorizaciones = ($totalAutorizaciones - $totalReversadas);
                            }else{
                                $totalAutorizaciones = ($totalReversadas - $totalAutorizaciones);
                            }
                            $totalAutorizaciones = number_format($totalAutorizaciones, 2, ',', '.');
                        }else{
                            $totalAutorizaciones = number_format(0, 2, ',', '.');
                        }

            }


            $anio = date(2000);
            $anio = $anio * 1;
            $anios = [];
            for($i=$anio;$i<=($anio+100);$i++){
                array_push($anios,$i);
            }

        if(empty(Auth::user()->setup)){

            $user = User::find(Auth::user()->id);
            return view('auth.passwords.change',compact('user'));

        }else{

            return view('home')
                    ->with(['totalAutorizaciones' => $totalAutorizaciones,
                            'cantidadAutorizaciones'=>$cantidadAutorizaciones,
                            'totalPorAutorizar'=>$totalPorAutorizar,
                            'cantidadPorAutorizar'=>$cantidadPorAutorizar,
                            'totalCanceladas'=>$totalCanceladas,
                            'cantidadCanceladas'=>$cantidadCanceladas,
                            'rol'=>$rol,
                            'anios'=>$anios,
                            'moneda'=> $moneda]);
        }
    }// END METODO INDEX CONSOLIDADO


    public function montosConsolidados($mes,$anio, $moneda=null){
        if(strlen($mes) < 2){
            $mes="0".$mes;
        }
        $fecha = $anio."-".$mes;
        $data = [];
        $consolidados = [];

        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

        $ini = 01;
        $fechas = explode('-', $fecha);
        $anio = $fechas[0];
        $mes  = $fechas[1];
        $numDias = cal_days_in_month(CAL_GREGORIAN,$mes,$anio);

        if($moneda == null){
                $moneda = config('webConfig.bolivar');
            }

        try{

            if($rol == 3){

                        $fk_id_comercio = miem_come::where("fk_id_miembro",$user->id)->first();

                        $totalAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalAutorizaciones"))
                                ->where("status","=",0)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();

                        $totalAutorizaciones = $totalAutorizaciones->totalautorizaciones;

                        $totalAutorizaciones = number_format($totalAutorizaciones, 2, ',', '.');
                        array_push($consolidados,$totalAutorizaciones);

                        $cantidadAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadAutorizaciones"))
                                ->where("status","=",0)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $cantidadAutorizaciones = $cantidadAutorizaciones->cantidadautorizaciones;
                        array_push($consolidados,$cantidadAutorizaciones);

                        $totalPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalPorAutorizar"))
                                ->where("status","=",1)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $totalPorAutorizar = $totalPorAutorizar->totalporautorizar;
                        $totalPorAutorizar = number_format($totalPorAutorizar, 2, ',', '.');
                        array_push($consolidados,$totalPorAutorizar);

                        $cantidadPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadPorAutorizar"))
                                ->where("status","=",1)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $cantidadPorAutorizar = $cantidadPorAutorizar->cantidadporautorizar;
                        array_push($consolidados,$cantidadPorAutorizar);


                        $totalCanceladas = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalCanceladas"))
                                ->where("status","=",4)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $totalCanceladas = $totalCanceladas->totalcanceladas;
                        $totalCanceladas = number_format($totalCanceladas, 2, ',', '.');
                        array_push($consolidados,$totalCanceladas);


                        $cantidadCanceladas = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadCanceladas"))
                                ->where("status","=",4)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_id_comer","=",$fk_id_comercio->fk_id_comercio)
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $cantidadCanceladas = $cantidadCanceladas->cantidadcanceladas;

                        array_push($consolidados,$cantidadCanceladas);

            } else {

                        $totalAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalAutorizaciones"))
                                ->where("status","=",0)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $totalAutorizaciones = $totalAutorizaciones->totalautorizaciones;
                        $totalAutorizaciones = number_format($totalAutorizaciones, 2, ',', '.');
                        array_push($consolidados,$totalAutorizaciones);

                        $cantidadAutorizaciones = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadAutorizaciones"))
                                ->where("status","=",0)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $cantidadAutorizaciones = $cantidadAutorizaciones->cantidadautorizaciones;
                        array_push($consolidados,$cantidadAutorizaciones);

                        $totalPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalPorAutorizar"))
                                ->where("status","=",1)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $totalPorAutorizar = $totalPorAutorizar->totalporautorizar;
                        $totalPorAutorizar = number_format($totalPorAutorizar, 2, ',', '.');
                        array_push($consolidados,$totalPorAutorizar);

                        $cantidadPorAutorizar = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadPorAutorizar"))
                                ->where("status","=",1)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $cantidadPorAutorizar = $cantidadPorAutorizar->cantidadporautorizar;
                        array_push($consolidados,$cantidadPorAutorizar);


                        $totalCanceladas = DB::table("trans_head")
                                ->select(DB::raw("SUM(monto) as totalCanceladas"))
                                ->where("status","=",4)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $totalCanceladas = $totalCanceladas->totalcanceladas;
                        $totalCanceladas = number_format($totalCanceladas, 2, ',', '.');
                        array_push($consolidados,$totalCanceladas);


                        $cantidadCanceladas = DB::table("trans_head")
                                ->select(DB::raw("count(monto) as cantidadCanceladas"))
                                ->where("status","=",4)
                                ->whereBetween('created_at',[$fecha.'-'.$ini,$fecha.'-'.$numDias])
                                ->where("fk_monedas","=",$moneda)
                                ->first();
                        $cantidadCanceladas = $cantidadCanceladas->cantidadcanceladas;
                        array_push($consolidados,$cantidadCanceladas);
            }


        }catch(\Exception $e){
            DB::rollBack();
            flash('Ocurrió un error en la consulta.', '¡Alert!')->error();
        }


        return response()->json([
            'data'      => $consolidados
        ],200);

    }//END METODO MONTOS CONSOLIDADOS

    public function cargaPagos(){
        return view('transacciones.cargaPagos');
    }
    
    public function LimitesDisponibles(){
            $files = array();
            $Path = config('webConfig.PathFolderFileXLS');

            try{                
                $directory = opendir($Path);
                while ($file = readdir($directory))
                {
                    if (!is_dir($file))
                    {
                        $arrayFile = array();

                        $FileandExtension = explode(".",$file);
                        
                        if(count($FileandExtension) > 1)
                        {
                            if($FileandExtension[1] == "xlsx" || $FileandExtension[1] == "xls")
                            {
                                $AutomaticFileName = Automatic_Files::select('automatic_files.id','automatic_files.processed')
                                ->where('Filename',$file)->first();                    
                        
                                if($AutomaticFileName){
                                    if($AutomaticFileName->processed){
                                            $arrayFile = array($file, 1);//Processed
                                            array_push($files, $arrayFile); 
                                    }                                   
                                }
                                else
                                {
                                    $arrayFile = array($file, 0);//Not Processed
                                    array_push($files, $arrayFile); 
                                }  
                            }
                            else{
                                $arrayFile = array($file, 2);//Not allowed
                                array_push($files, $arrayFile); 
                            }

                                                
                        }
                    }
                }                           
            
            }
            catch(\Exception $e)
            {
                flash("No se pudo leer el directorio '". $Path ."', por favor valide que exista y que se posean los privilegios necesarios para leerlo.", '¡Alert!')->error();
            }
        $ProcessedFiles = Automatic_Files::select('automatic_files.id','automatic_files.Filename','automatic_files.TotalRows','automatic_files.TotalProcessed','automatic_files.TotalErrors','automatic_files.ErrorDetail','automatic_files.processed','automatic_files.InProgress','automatic_files.email','automatic_files.created_at')
        ->whereIn("ProcessType",[1,2])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->toArray();;

        return view('transacciones.LimitesDisponibles', compact("files","ProcessedFiles"));
    }

    public function insertFile(Request $request){

        $AutomaticFileName = Automatic_Files::select('automatic_files.id')
        ->where('Filename',$request->FileName)->first();

        if($AutomaticFileName){
            return redirect()->route('LimitesDisponibles');
        }

         /*Insertar Archivo a Procesar*/
            $user= User::find(Auth::user()->id);
            $id_user=$user->id;
            $email = $user->email;
            $ip = \Request::ip();

            $File = new \App\Models\Automatic_Files();
            $File->user_id = $id_user;
            $File->email = $email;
            $File->ip = $ip;
            $File->Filename = $request->FileName;
            $File->ProcessType = 1;
            $File->ErrorDetail = '';
            $File->processed = false;
            $File->save();
            
            return redirect()->route('LimitesDisponibles');
    }   

    public function uploadPagos(Request $request){

        if($request->hasFile('file')){
           $file = $request->file('file');
           /*validar que las extensiones del archivo sean xls o xlsx*/
           if($file->getClientOriginalExtension() != 'xls' &&
            $file->getClientOriginalExtension() != 'xlsx'){
                //mensaje de error de extension de archivo
                flash('¡Importación cancelada, el archivo no posee el formato adecuado, por favor intente de nuevo con archivos tipo xlsx!', '¡Error en Importación!')->error();

                return view('transacciones/cargaPagos');
           }

            $path = Input::file('file')->getRealPath();

            $data = Excel::load($path, function($reader) {
                      $reader->formatDates(true, 'Y-m-d');
            })->get();


            $data = $data->toArray();
            $cantRep = 0;
            $cantNoInt = 0;
            $arrayDup = array();
            $arrayCed = array();
            $arrayNoNum = array();
            $arrayCedNoExist = array();
            $arrayPagoMayorSaldo = array();
            $countRegAct = 0;

            if($file->getClientOriginalExtension() == 'xls' || $file->getClientOriginalExtension() == 'xlsx'){
                for($a=0;$a<=count($data)-1;$a++){

                    if(!isset($data[$a]['cedula'])){

                        flash('¡Importación cancelada, Error al subir el archivo, el mismo debe constar de un solo libro!', '¡Error en Importación!')->error();
                        return view('transacciones/cargaPagos');

                    }else{
                        $cedInteger = (string)$data[$a]['cedula'];
                        $pago = (string)$data[$a]['saldo'];
                    }

                    if(is_numeric($cedInteger)){

                        if(in_array($cedInteger, $arrayCed))
                        {
                            $cantRep++;
                            array_push($arrayDup, (object)$data[$a]);
                        }

                        array_push($arrayCed, $cedInteger);
                    }

                    if(!is_numeric($cedInteger) && is_numeric($pago)){

                        $cantNoInt++;
                        array_push($arrayNoNum, (object)$data[$a]);
                    }else if( is_numeric($cedInteger) && !is_numeric($pago)){

                        $cantNoInt++;
                        array_push($arrayNoNum, (object)$data[$a]);
                    }else if(!is_numeric($cedInteger) && !is_numeric($pago)){

                        $cantNoInt++;
                        array_push($arrayNoNum, (object)$data[$a]);
                    }
                }

            }

            if($cantRep != 0){
                //mensaje de error de duplicados

                flash('¡Importación cancelada, existen '.$cantRep.' cédulas duplicadas en el archivo, verifique y vuelva a importar!', '¡Error en Importación!')->error();


                return view('transacciones/cargaPagos')->with('duplicados',$arrayDup);
            }

            if($cantNoInt){
                //mensaje de error del formato de los registros
                flash('¡Importación cancelada, los registros solo deben estar compuestos por números, verifique e intente de nuevo!', '¡Error en Importación!')->error();

                return view('transacciones/cargaPagos')->with('nonum',$arrayNoNum);
            }

            if($file->getClientOriginalExtension() == 'xls' || $file->getClientOriginalExtension() == 'xlsx'){
                for($a=0;$a<=count($data)-1;$a++){
                    //validamos que la cedula se encuentre en base de datos
                    $cedExist = $this->verifCedula((string)$data[$a]['cedula']);

                    if($cedExist == 0){
                        array_push($arrayCedNoExist, (object)$data[$a]);
                    }
                }
            }

            if($arrayCedNoExist){
                /*Cedulas que no existen en el sistema*/
                flash('¡Importación cancelada, la(s) cédula(s) a continuación no existen en base de datos, verifique e intente de nuevo!')->error();

                return view('transacciones/cargaPagos')->with('cedNoExist',$arrayCedNoExist);
            }

            if($file->getClientOriginalExtension() == 'xls' || $file->getClientOriginalExtension() == 'xlsx'){
                for($a=0;$a<=count($data)-1;$a++){
                    //validación de saldos
                    $valSaldo = $this->valSaldos((string)$data[$a]['cedula'],(string)$data[$a]['saldo']);

                    if($valSaldo == 1){
                        array_push($arrayPagoMayorSaldo, (object)$data[$a]);
                    }

                }
            }

            if($arrayPagoMayorSaldo==1){
                /*pago mayor a saldo*/
                flash('¡Importación cancelada, hay pagos que exceden del saldo, verifique e intente de nuevo!')->error();

                return view('transacciones/cargaPagos')->with('arrayPagoMayorSaldo',$arrayPagoMayorSaldo);
            }


            if($file->getClientOriginalExtension() == 'xls' || $file->getClientOriginalExtension() == 'xlsx'){
                for($a=0;$a<=count($data)-1;$a++){
                    /*se procede a actualizar los saldos*/
                    if($this->importSaldos((string)$data[$a]['cedula'],(string)$data[$a]['saldo'], (string) $data[$a]['monedas'] )!=""){

                        $resp = $this->importSaldos((string)$data[$a]['cedula'],(string)$data[$a]['saldo'], (string) $data[$a]['monedas'] );

                        flash('¡Importación cancelada a partir de la cédula '.$resp.', el saldo a recargar supera el limite asignado!')->error();
                        return view('transacciones/cargaPagos');

                    }else{
                        $resp = 0;
                        $countRegAct++;
                    }

                }
            }

            if($file->getClientOriginalExtension() == 'xlsx'){
                for($a=0;$a<=count($data)-1;$a++){
                    /*se procede a actualizar los saldos*/

                    if($this->importSaldos((string)$data[$a]['cedula'],(string)$data[$a]['saldo'], (string) $data[$a]['monedas'] )!=""){

                        $resp = $this->importSaldos((string)$data[$a]['cedula'],(string)$data[$a]['saldo']);

                        flash('¡Importación cancelada a partir de la cédula '.$resp.', el saldo a recargar supera el limite asignado!')->error();
                        return view('transacciones/cargaPagos');

                    }else{
                        $resp = 0;
                        $countRegAct++;
                    }

                }
            }

                /*importación realizada exitosamente*/
                flash('¡Importación realizada de forma exitosa, se importaron '.$countRegAct.' registros!')->success();

                return view('transacciones/cargaPagos');


        }

    }


    //método para validar que una cedula este en base de datos como rol miembro
    public function verifCedula($cedula){

        $user = User::find(Auth::user()->id);
        $banco = miem_ban::select('fk_id_banco')
            ->where('fk_dni_miembro',$user->id)
            ->first();

        $query = User::select('users.dni')
                ->join('carnet','carnet.fk_id_miembro','users.id')
                ->where('users.dni',$cedula)
                ->where('carnet.fk_id_banco',$banco->fk_id_banco)
                ->first();

        return count($query);
    }

    /*Método para validación de los saldos*/
    public function valSaldos($cedula,$pago){

        $idMiembro = User::select('id')->where('dni',$cedula)->first();
        $saldo = Ledge::where('fk_dni_miembros',$idMiembro->id)->get()->last();
        $pago = (double)$pago;
        if(count($saldo)==0){
            $saldo = 0;
        }else{
            $saldo = $saldo->disp_post;
        }

        $saldo = (double)$saldo;

        if($pago <= $saldo){
            return 0;
        }else if($pago > $saldo){
            return 1;
        }

    }


    /*método para actualiza saldos*/
    public function importSaldos($cedula,$pago, $moneda){

        $idUser = User:: select('id')->where('dni',$cedula)->first();

        $user = User::find(Auth::user()->id);
        $banco = miem_ban::select('fk_id_banco')
            ->where('fk_dni_miembro',$user->id)
            ->first();
        $moneda_id = moneda::where('mon_nombre', strtoupper($moneda))->first()->mon_id;
        
        

        $idComercio = comercios::select('id')->where('razon_social','jackpotImportPagos')->first();


        $query = trans_head::select(
            'users.id',
            DB::raw('SUM(trans_head.monto) as sumconsumo'),
            DB::raw('SUM(trans_head.monto) as sumneto'),
            'carnet.limite'
        )
        ->join('carnet','carnet.fk_id_miembro','trans_head.fk_dni_miembros')
        ->join('users','users.id','carnet.fk_id_miembro')
        ->where('users.dni',$cedula)
        ->where('carnet.fk_monedas', $moneda_id)
        ->groupBy(
            'carnet.limite',
            'users.id'
        )
        ->first();
        $lastTransHead = trans_head::where('fk_dni_miembros', $idUser->id)->where('fk_monedas', $moneda_id)->get()->last();
        $preLimite = Ledge::where('fk_dni_miembros',$idUser->id)->where('fk_id_trans_head', $lastTransHead->id)->get()->last();

        if(count($query) == 0){
            $sumaConsumo = 0;
            $sumaNeto = 0;
            $limite = 0;
        }else{
            $sumaConsumo = $query->sumconsumo;
            $sumaNeto = $query->sumneto;
            $limite = $query->limite;
        }
        //$disponible = (($limite - $sumaConsumo)+$pago);
        //$saldo = $limite - $disponible;
        /*if($pago < 0){
            //$saldo = $limite + $pago;
            if(count($preLimite)!=0){
                $saldo = $preLimite->disp_post + $pago;
            }else{
                $saldo = $limite + $pago;
            }
        }else{
            //$saldo = $limite - $pago;
            if(count($preLimite)!=0){
                $saldo = $preLimite->disp_post - $pago;
            }else{
                $saldo = $limite - $pago;
            }
        }*/

        if(count($preLimite)!=0){
                $saldo = $preLimite->disp_post + $pago;
        }else{
                $saldo = $limite + $pago;
        }

        //$saldo = str_replace(".", "",$saldo);
        //$saldo = (double)$saldo;
        $clientIP = \Request::ip();
        $pago = (double)$pago;

        $trans_head = new \App\Models\trans_head();
        $trans_head->fk_dni_miembros = $idUser->id;
        $trans_head->fk_id_banco  = $banco->fk_id_banco;
        $trans_head->fk_id_comer  = $idComercio->id;
        $trans_head->monto        = $pago;
        $trans_head->token = 0;
        $trans_head->status = 5;
        $trans_head->ip     = $clientIP;
        $trans_head->token_status    = 0;
        $trans_head->fk_monedas = $moneda_id;

        try{

                if( $trans_head->save()){

                    $ledge = new \App\Models\Ledge();
                    $ledge->fk_id_trans_head = $trans_head->id;
                    $ledge->fk_dni_miembros= $trans_head->fk_dni_miembros;
                    $ledge->monto = $pago;

                    if(count($preLimite)==0){
                        $disponible = carnet::select("limite")->where("fk_id_miembro",$idUser->id)->where('fk_monedas', $moneda_id)->first();

                        /*if((0 + $pago) >  $disponible->limite){
                            return $cedula;
                        }else{*/
                            $ledge->disp_pre = $disponible->limite;
                            $ledge->disp_post = $disponible->limite+$pago;
                        //}

                    }else{

                        $limite = carnet::select("limite")->where("fk_id_miembro",$idUser->id)->where('fk_monedas', $moneda_id)->first();
                        /*if(($preLimite->disp_post + $pago) >  $limite->limite){
                            return $cedula;
                        }else{*/
                            $ledge->disp_pre = $preLimite->disp_post;
                            $ledge->disp_post = $saldo;
                        //}

                    }

                    $ledge->save();

                }

        }catch(\Exception $e){
                    DB::rollBack();
                    flash('Ocurrió un error en la importación. '.$e, '¡Alert!')->error();
        }


    }

    public function GenerateBankOTP($Moneda, $Nacionalidad, $DNI)
    {
        $otpBCO = null;
        $expiraOtpBco = null;

        $data_array =  array(
            "ip"                => \Request::ip(),
            "currency"          => $Moneda,
            "clientid"          => array(
                "doctype"       => $Nacionalidad,
                "docid"         => $DNI
            ),
        );
        
        $make_call = $this->callAPI('POST', config('webConfig.ProviderBanplusGenerateOTP'), json_encode($data_array));

        $response = json_decode($make_call, true);

        if(isset($response['otp'], $response['expiresOn']))
        {
            $otpBCO = $response['otp'];

            $OtpDateTime = explode("T",$response['expiresOn']);
            $fecha_otp = explode("-",$OtpDateTime[0]);
            $fecha_otp_dia = $fecha_otp[2];
            $fecha_otp_mes = $fecha_otp[1];
            $fecha_otp_anio = $fecha_otp[0];

            $hora_otp = explode(":",str_replace("Z","",$OtpDateTime[1]));
            $fecha_otp_hora = $hora_otp[0];
            $fecha_otp_minuto = $hora_otp[1];
            $fecha_otp_segundo = explode(".",$hora_otp[2])[0];

            $fecha_otp = $fecha_otp_anio."-".$fecha_otp_mes."-".$fecha_otp_dia." ".$fecha_otp_hora.":".$fecha_otp_minuto.":".$fecha_otp_segundo;
            $fecha_otp = strtotime($fecha_otp);
            
            $expiraOtpBco = date('Y-m-d H:i:s',$fecha_otp);

            $response['otp'] = $otpBCO;
            $response['expiresOn'] = $expiraOtpBco;
        }        

        return $response;                
    }

    public function callAPI($method, $url, $data){
        $curl = curl_init();
        switch ($method){
           case "POST":
              curl_setopt($curl, CURLOPT_POST, 1);
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
              break;
           case "PUT":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
              break;
           default:
              if ($data)
                 $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,60);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        //   'APIKEY: 111111111111111111111',
           'Content-Type: application/json',
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){
            $data_array =  array(
                "code"        => "5000",
                "message"        => "No es posible establecer conexion con el servidor"
            );            
            return json_encode($data_array);
        }
        curl_close($curl);
        return $result;
     }    


    public function consultaDatos($cedula, $comercio){
        $user= User::find(Auth::user()->id);


        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

        if($rol == 3){

          $comercio= miem_come::where('fk_id_miembro', $user->id)->get()[0]->fk_id_comercio;        
        }
        
        //$comer = banc_comer::where('fk_id_comer',$comercio)->get()[0];

        $bolivar= banc_comer::where('fk_id_comer',$comercio)->get()[0]->num_cta_princ;

        $dolar= banc_comer::where('fk_id_comer',$comercio)->get()[0]->num_cta_princ_dolar;

        $euro= banc_comer::where('fk_id_comer',$comercio)->get()[0]->num_cta_princ_euro;

        //dd($bolivar);

        if(substr($cedula, 0,1) == "V"){
            $cedula = explode("V", $cedula);
            $cedula = $cedula[1];
        }

        if(substr($cedula, 0,1) == "v"){
            $cedula = explode("v", $cedula);
            $cedula = $cedula[1];
        }

        if(substr($cedula, 0,1) == "E"){
            $cedula = explode("E", $cedula);
            $cedula = $cedula[1];
        }

        if(substr($cedula, 0,1) == "e"){
            $cedula = explode("e", $cedula);
            $cedula = $cedula[1];
        }


        if($dolar!="" && $bolivar != "" && $euro!= ""){
            $idUser = User::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor')
            ->join('carnet','carnet.fk_id_miembro','users.id')
            ->join('monedas','monedas.mon_id','carnet.fk_monedas')
            ->where('dni',$cedula)
            ->where('mon_status', '=', 'ACTIVO')
			->where('carnet.transar', '=', 'true')
			->whereRaw("COALESCE(carnet.cod_emisor, '') <> 'INTICARD001'")
			->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
            ->get();
        }elseif($dolar!="" && $bolivar != ""){
            $idUser = User::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor')
            ->join('carnet','carnet.fk_id_miembro','users.id')
            ->join('monedas','monedas.mon_id','carnet.fk_monedas')
            ->where('dni',$cedula)
            ->where('fk_monedas', '!=', 3 )
            ->where('mon_status', '=', 'ACTIVO')
			->where('carnet.transar', '=', 'true')
			->whereRaw("COALESCE(carnet.cod_emisor, '') <> 'INTICARD001'")
			->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
            ->get();     
            
        }elseif($dolar!="" && $euro != ""){
           $idUser = User::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor')
            ->join('carnet','carnet.fk_id_miembro','users.id')
            ->join('monedas','monedas.mon_id','carnet.fk_monedas')
            ->where('dni',$cedula)
            ->where('fk_monedas', '!=', 2)
            ->where('mon_status', '=', 'ACTIVO')
			->where('carnet.transar', '=', 'true')
			->whereRaw("COALESCE(carnet.cod_emisor, '') <> 'INTICARD001'")
			->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
            ->get();
        }elseif($bolivar!="" && $euro != ""){
            $idUser = User::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor')
            ->join('carnet','carnet.fk_id_miembro','users.id')
            ->join('monedas','monedas.mon_id','carnet.fk_monedas')
            ->where('dni',$cedula)
            ->where('fk_monedas', '!=', 1)
            ->where('mon_status', '=', 'ACTIVO')
			->where('carnet.transar', '=', 'true')
			->whereRaw("COALESCE(carnet.cod_emisor, '') <> 'INTICARD001'")
			->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
            ->get();
        }elseif($bolivar!=""){
            $idUser = User::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor')
            ->join('carnet','carnet.fk_id_miembro','users.id')
            ->join('monedas','monedas.mon_id','carnet.fk_monedas')
            ->where('dni',$cedula)
            ->where('fk_monedas',2)
            ->where('mon_status', '=', 'ACTIVO')
			->where('carnet.transar', '=', 'true')
			->whereRaw("COALESCE(carnet.cod_emisor, '') <> 'INTICARD001'")
			->whereRaw("substring(carnet.carnet,1,4) <> '6540'")
            ->get();
        }  
/*foreach ($idUser as $key => $value) {
dd($value);
}*/
		$values =[];
		
		//INCLUIR LAS GIFTCARD
        if($rol == 3){
			
			$Comercio = miem_come::select('comercios.es_sucursal','comercios.rif','comercios.id')
			->join('comercios','comercios.id','miem_come.fk_id_comercio')
			->where('miem_come.fk_id_miembro',Auth::user()->id)
			->first();
			
            $gift_cards = User::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor')
            ->join('carnet','carnet.fk_id_miembro','users.id')
            ->join('monedas','monedas.mon_id','carnet.fk_monedas')
            ->where('users.dni',$cedula)
			->where('carnet.transar', '=', 'true')
			->where('carnet.cod_emisor', '=', $Comercio->rif)
			->where('carnet.disponible', '>', 0)
			->whereRaw("substring(carnet.carnet,1,4) = '6540'")
            ->get();
			
			
			//AGREGAR GIFTCARDS A LA LISTA
			if(count($gift_cards) > 0)
			{
				foreach ($gift_cards as $key => $value) {
					
						$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
						->where('reverso', null )
						->where('procesado', null)
						->where('status', 0)
						->where('fk_dni_miembros', $value['UserId'])
						->where('carnet_id', $value['id'])
						->first();
						
						$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
						->where('reverso', null )
						->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
						->where('status', 0)
						->where('fk_dni_miembros', $value['UserId'])
						->where('carnet_id', $value['id'])
						->first();
						
						$creditoDisponible = number_format(($value['disponible'] - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');

						array_push($values, [
						'id'          => $value['id'],
						'limite'      => str_replace(".", ",",$value['limite']),
						'disponible'  => str_replace(".", ",",$creditoDisponible),
						'carnet'      => (string)$value['carnet'],
						'mon_id'      =>$value['fk_monedas'],
						'transar'  	  =>$value['transar'],
						'cod_emisor'  =>$value['cod_emisor'],
					]);
				}		
			}			
        }		        

        if(count($idUser) == 0 && count($gift_cards) == 0){
            $User = User::select('users.id')
            ->where('dni',$cedula)
            ->get();

            if(count($User) > 0)
            {
                array_push($values, [
                    'id'          => 0,
                    'limite'      => 0,
                    'disponible'  => 0,
                    'carnet'      =>false,
                    'mon_id'      =>0,
                    'transar'  	  =>false,
                    'cod_emisor'  =>0,
                ]);

                return response()->json($values,200);
            }
        }

        if($idUser){

           
            //$idUser = carnet::select('limite')->where('fk_id_miembro',$idUser->id)->first();
            
            foreach ($idUser as $key => $value) {

                

                //$existTransaccion = Ledge::select('*')->where('carnet_id',$value['id'])
                //->get();
				
				$transEnTransito = trans_head::select(DB::raw('SUM(monto) AS total'))
				->where('reverso', null )
				->where('procesado', null)
				->where('status', 0)
				->where('fk_dni_miembros', $value['UserId'])
				->where('carnet_id', $value['id'])
				->first();
				
				$transLiquidadas = trans_head::select(DB::raw('SUM(monto) AS total'))
				->where('reverso', null )
				->whereRaw('(date(procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE "ProcessType" = 2 order by id desc limit 1) and date(procesado) <= current_date)')
				->where('status', 0)
				->where('fk_dni_miembros', $value['UserId'])
				->where('carnet_id', $value['id'])
				->first();
                
                $creditoDisponible = number_format(($value['disponible'] - ($transEnTransito->total + $transLiquidadas->total)), 2, '.', '');

                if($value['cod_emisor'] == "174")
                {
                    $creditoDisponible = 0;
                    $value['limite'] = 0;
                }

									
 /*                if(count($existTransaccion) == 0){
                    $creditoDisponible = $value['disponible'];

                }else{


                     $creditoDisponible = Ledge::select('disp_post')
                     ->where('carnet_id', $value['id'])

                    ->get()->last();
                    $creditoDisponible = $creditoDisponible->disp_post;

                } */
                array_push($values, [
                'id'          => $value['id'],
                'limite'      => str_replace(".", ",",$value['limite']),
                'disponible'  => str_replace(".", ",",$creditoDisponible),
                'carnet'      =>$value['carnet'],
                'mon_id'      =>$value['fk_monedas'],
                'transar'  	  =>$value['transar'],
                'cod_emisor'  =>$value['cod_emisor'],
            ]);
}//fin del foreach
            return response()->json($values,200);

        }else{

            return response()->json([
                'fallido'      => true,
            ],200);

        }//final del else

    }//fin del metodo de consulta de datos


    public function ReportLimitesDisponibles (){

        $user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

    /*  $data = DB::select("SELECT
    u.nacionalidad||'-'||u.dni as cedula,
    c.carnet as tarjeta_membresia,
    u.first_name as nombre,
    u.last_name as apellido,
    u.cod_tel ||'-'||u.num_tel as telefono,
    u.email as correo_electronico,
    c.limite,
    CASE
        WHEN l.disp_post IS NULL
        THEN c.limite
        ELSE l.disp_post
       END as disponible,
       th.created_at as ultima_operacion

    FROM users u

    INNER JOIN role_user ON u.id = role_user.user_id
    INNER JOIN carnet c ON u.id = c.fk_id_miembro
    LEFT JOIN trans_head th ON th.fk_dni_miembros = u.id
    LEFT JOIN ledger l ON l.fk_id_trans_head = th.id

    WHERE   role_user.role_id = 5
        and th.reverso is null
        /*and th.status != 5*/
      /*  and (th.created_at is null) or (th.created_at = (SELECT max(th1.created_at)
                FROM trans_head th1
                WHERE th1.fk_dni_miembros = u.id
                ))
        /*and th.created_at between '2018-02-17' and '2019-05-08'*/
  /*  GROUP BY
        u.nacionalidad,u.dni,c.carnet,u.first_name,u.last_name,u.cod_tel,u.num_tel,u.email,
        l.disp_post,u.email,c.limite,th.created_at
    LIMIT 20
        ");*/

      //dd($data);


      return view('transacciones.ReportLimitesDisponibles',compact('rol'));
    }

    public function DescargaLimitesDisponibles (){

        $user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

        $query = DB::select("SELECT
        u.nacionalidad||'-'||u.dni as cedula,
        c.carnet as tarjeta_membresia,
		c.carnet_real as tarjeta_real,
		CASE transar WHEN true THEN 'Activo' WHEN false THEN 'Inactivo' ELSE '' END AS estatus_carnet,
        m.mon_nombre as moneda,
        u.first_name as nombre,
        u.last_name as apellido,
        u.cod_tel ||'-'||u.num_tel as telefono,
        u.email as correo_electronico,
        c.limite,
        (c.disponible - (coalesce((select sum(thn.monto) from trans_head as thn where thn.carnet_id = c.id and thn.fk_dni_miembros = th.fk_dni_miembros and thn.status = 0 and thn.reverso is null and thn.procesado is null),0) + coalesce((select sum(thn.monto) from trans_head as thn where thn.carnet_id = c.id and thn.fk_dni_miembros = th.fk_dni_miembros and thn.status = 0 and thn.reverso is null and (date(thn.procesado) >= (SELECT date(created_at) FROM public.automatic_files WHERE \"ProcessType\" = 2 order by id desc limit 1) and date(thn.procesado) <= current_date)),0))) as disponible,
        /*CASE
            WHEN l.disp_post IS NULL
            THEN c.limite
            ELSE l.disp_post
           END as disponible,*/
           th.created_at as ultima_operacion,
		   CASE WHEN u.deleted_at IS NULL THEN 'ACTIVO' ELSE 'INACTIVO' END as estatus
    
        FROM users u
    
        INNER JOIN role_user ON u.id = role_user.user_id
        INNER JOIN carnet c ON u.id = c.fk_id_miembro
        INNER JOIN monedas m ON c.fk_monedas = m.mon_id
        LEFT JOIN trans_head th ON th.fk_dni_miembros = u.id
        /*LEFT JOIN ledger l ON l.fk_id_trans_head = th.id*/
    
        WHERE   role_user.role_id = 5
            and th.reverso is null
    
            and (th.created_at is null) or (th.created_at = (SELECT max(th1.created_at)
                    FROM trans_head th1
                    WHERE th1.fk_dni_miembros = u.id
                    ))
    
        GROUP BY
            u.nacionalidad,u.dni,c.carnet,c.id,u.deleted_at,u.first_name,u.last_name,u.cod_tel,u.num_tel,u.email,
            /*l.disp_post,*/
            u.email,c.limite,c.disponible,th.created_at,th.fk_dni_miembros,m.mon_nombre");        

    //   $query = DB::select("SELECT
    // u.nacionalidad||'-'||u.dni as cedula,
    // c.carnet as tarjeta_membresia,
    // u.first_name as nombre,
    // u.last_name as apellido,
    // u.cod_tel ||'-'||u.num_tel as telefono,
    // u.email as correo_electronico,
    // c.limite,
    // CASE
    //     WHEN l.disp_post IS NULL
    //     THEN c.limite
    //     ELSE l.disp_post
    //    END as disponible,
    //    th.created_at as ultima_operacion

    // FROM users u

    // INNER JOIN role_user ON u.id = role_user.user_id
    // INNER JOIN carnet c ON u.id = c.fk_id_miembro
    // LEFT JOIN trans_head th ON th.fk_dni_miembros = u.id
    // LEFT JOIN ledger l ON l.fk_id_trans_head = th.id

    // WHERE   role_user.role_id = 5
    //     and th.reverso is null
    //     /*and th.status != 5*/
    //     and (th.created_at is null) or (th.created_at = (SELECT max(th1.created_at)
    //             FROM trans_head th1
    //             WHERE th1.fk_dni_miembros = u.id
    //             ))
    //     /*and th.created_at between '2018-02-17' and '2019-05-08'*/
    // GROUP BY
    //     u.nacionalidad,u.dni,c.carnet,u.first_name,u.last_name,u.cod_tel,u.num_tel,u.email,
    //     l.disp_post,u.email,c.limite,th.created_at");

      //dd($data);


      $data = array();
        foreach ($query as $key => $value) {
                $fecha_Unix=strtotime($value->ultima_operacion);
                $cambio_formato=date('d-m-Y H:i:s',$fecha_Unix);
                if ($value->ultima_operacion ==null) {
                    $value->ultima_operacion=null;
                }else{
                    $value->ultima_operacion=$cambio_formato;
                }

                $value->limite= number_format($value->limite, 2, ',', '.');
                $value->disponible= number_format($value->disponible, 2, ',', '.');

                $data[] = (array)$value;
            }
           //dd($data);

      Excel::create('Reporte de Limites y Disponibles',function($excel) use($data){
                    $excel->sheet('Operaciones', function($sheet) use($data) {
                        $sheet->setOrientation('lanscape');
                        $sheet->fromArray($data);
                    });
                })->download('xls');
    }

    public function EmailCedulaInvalida(){
      try{
        $user= User::find(Auth::user()->id);

        //cedula ingresada por el comercios
        $cedula = $_GET['ci'];

        //consulta datos del comercio
        $comercio = comercios::select('comercios.rif', 'comercios.descripcion')
        ->join('miem_come','miem_come.fk_id_comercio','comercios.id')
        ->where('miem_come.fk_id_miembro',$user->id)
        ->first();
        //dd($comercio->rif,  $comercio->descripcion);
        $rif = $comercio->rif;
        $nombreComercio = $comercio->descripcion;

        $date = Carbon::now();



        //se envia email
            $To = array(config('webConfig.email'),config('webConfig.bcc') );
            Mail::to($To)
        ->send(new CedulaInvalida($rif, $nombreComercio, $cedula, $date));


        return response()->json(ok);
        }catch(\Exception $e){
          return response()->json($e);
        }//end catch
        //envio de email por cedulas invalidas
      }

        public function MontoExcedido(){
          try{
            $user= User::find(Auth::user()->id);

            //cedula ingresada por el comercios
            $cedula = $_GET['ci'];
            $monto = $_GET['monto'];
			$producto = $_GET['producto'];

            $cliente = User::select('first_name', 'last_name','cod_tel','num_tel','carnet.carnet','carnet.fk_monedas')
			->join('carnet','carnet.fk_id_miembro','users.id')
            ->where('dni',$cedula)
			->where('carnet.carnet',$producto)
            ->first();
			
			$producto = substr($cliente->carnet, 0, 4) . '-****-****-'. substr($cliente->carnet, (strlen($cliente->carnet) - 4), 4);
			//dd(Producto);

            $nombreCliente = $cliente->first_name.' '.$cliente->last_name;
            //dd($Ncliente);
            //dd($cedula, $monto, $carnet);
            //consulta datos del comercio
            $comercio = comercios::select('comercios.rif', 'comercios.descripcion')
            ->join('miem_come','miem_come.fk_id_comercio','comercios.id')
            ->where('miem_come.fk_id_miembro',$user->id)
            ->first();
            //dd($comercio->rif,  $comercio->descripcion);
            $rif = $comercio->rif;
            $nombreComercio = $comercio->descripcion;

            $date = Carbon::now();
			
			$moneda = Moneda::find($cliente->fk_monedas)->mon_simbolo;

            //se envia email
            $To = array(config('webConfig.email'),config('webConfig.bcc') );
            Mail::to($To)
            ->send(new MontoExcedido($rif, $nombreComercio, $cedula, $nombreCliente, $monto, $date, $producto, $moneda));
            
			$this->enviar_sms($cliente->cod_tel.$cliente->num_tel, 'La transaccion que intenta realizar supera el limite disponible que posee en su tarjeta, cualquier duda comunicarse al Centro de Atención President via Whatsapp al 0412 Banplus (2267587)');       

            return response()->json(ok);
            }catch(\Exception $e){
              return response()->json($e);
            }//end catch
            //envio de email por cedulas invalidas
          }

          public function ClienteRestriccion(){
            try{
              $user= User::find(Auth::user()->id);
  
              //cedula ingresada por el comercios
              $cedula = $_GET['ci'];
              $monto = $_GET['monto'];
			  $producto = $_GET['producto'];
  
			  
			$cliente = User::select('first_name', 'last_name','carnet.carnet','carnet.fk_monedas')
			->join('carnet','carnet.fk_id_miembro','users.id')
			->where('dni',$cedula)
			->where('carnet.carnet',$producto)
			->first();			  
  
              $nombreCliente = $cliente->first_name.' '.$cliente->last_name;
              //dd($Ncliente);
              //dd($cedula, $monto, $carnet);
              //consulta datos del comercio
              $comercio = comercios::select('comercios.rif', 'comercios.descripcion')
              ->join('miem_come','miem_come.fk_id_comercio','comercios.id')
              ->where('miem_come.fk_id_miembro',$user->id)
              ->first();
              //dd($comercio->rif,  $comercio->descripcion);
              $rif = $comercio->rif;
              $nombreComercio = $comercio->descripcion;
              $date = Carbon::now();

			  $producto = substr($cliente->carnet, 0, 4) . '-****-****-'. substr($cliente->carnet, (strlen($cliente->carnet) - 4), 4);
			  
			  $moneda = Moneda::find($cliente->fk_monedas)->mon_simbolo;
  
              //se envia email
              $To = array(config('webConfig.email'),config('webConfig.bcc') );
              Mail::to($To)
              ->send(new ClienteRestriccion($rif, $nombreComercio, $cedula, $nombreCliente, $monto, $date, $moneda, $producto));

              return response()->json('ok', 200);

              //return response()->json(ok);

              }catch(\Exception $e){
                return response()->json($e);
              }//end catch
              //envio de email por cedulas invalidas
            }          

}
