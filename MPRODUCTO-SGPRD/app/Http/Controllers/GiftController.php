<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Config\webConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\VentaGiftCardCompleteRequest;
use App\Http\Requests\BeneficiarioGiftCardCreateRequest;
use App\Http\Requests\GiftRequest;
use App\Http\Requests\GiftEditRequest;
use App\Models\User;
use App\Models\emisores;
use App\Models\carnet;
use App\Models\trans_head;
use App\Models\miem_come;
use App\Models\comercios;
use App\Models\trans_gift_card;
use App\Models\giftcard_imagenes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Hash;

class GiftController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
	 
	public function index(Request $request)
	{
		//ELIMINAR GIFTCARDS RECHAZADAS
		$this->Eliminar_Giftcards_Rechazadas();
		
		$user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
		}
		
		if($rol != 6)
		{
			return redirect()->route('home');
		}
			
        $listgift = emisores::select('emisores.id','emisores.rif','emisores.cod_emisor','emisores.nombre as emisor','emisores.producto as nombregift', 'monedas.mon_nombre')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.categoria', '=', 'GiftCard');
		
		
		if($request->has('filter'))
		{
			if($request->has('comercio_emisor'))
			{
				if($request->comercio_emisor != "")
				{
					$listgift = $listgift->where('emisores.rif','=',$request->comercio_emisor);
				}
			}
		}		
		
		$listgift = $listgift->get();		

		$comercios = comercios::select('comercios.rif','comercios.descripcion')
		->join('emisores','emisores.rif','comercios.rif')
		->Where('emisores.categoria', '=', 'GiftCard')
		->distinct()
		->get();

		$num_resultado = count($listgift);

				
		return view('gift.index')
		->with('listgift',$listgift)
		->with('comercios',$comercios)
		->with('num_resultado',$num_resultado);
	}
	 
	public function gift_cards_step1()
	{
		$Comercio = miem_come::select('comercios.rif')
		->join('comercios','comercios.id','miem_come.fk_id_comercio')
		->where('miem_come.fk_id_miembro',Auth::user()->id)
		->first();

        $listgift = emisores::select('emisores.imagen','emisores.cod_emisor','emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
		->Where('emisores.categoria', '=', 'GiftCard')
		->Where('emisores.rif', '=', $Comercio->rif)
        ->get();

		
		return view('gift.step1')->with('listgift',$listgift);
	}
	
	public function gift_cards_venta($id)
	{
        $gift = emisores::select('emisores.id','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo', 'emisores.monto_minimo','emisores.monto_fijo','emisores.tasa_comision','emisores.paga_comision')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $id)
        ->first();
		
        $Imagenes = giftcard_imagenes::select('giftcard_imagenes.monto','giftcard_imagenes.nombre_imagen')
        ->Where('giftcard_imagenes.fk_giftcard', '=' , $gift->id)
		->orderBy('giftcard_imagenes.monto','desc')
        ->get();
		
        $ImagenesRadioButton = giftcard_imagenes::select('giftcard_imagenes.monto','giftcard_imagenes.nombre_imagen')
        ->Where('giftcard_imagenes.fk_giftcard', '=' , $gift->id)
		->Where('giftcard_imagenes.monto', '!=' , 'Otros')
		->orderByRaw('monto::int asc')
        ->get();		
		
		
		$MontoMaximo = config('webConfig.MontoMaximoGiftcard');
		
		return view('gift.venta')->with('gift',$gift)->with('MontoMaximo',$MontoMaximo)->with('Imagenes',$Imagenes)->with('ImagenesRadioButton',$ImagenesRadioButton);
	}	
	
	public function gift_cards_comprador($id)
	{
        $gift = emisores::select('emisores.imagen','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $id)
        ->first();

		
		return view('gift.comprador')->with('gift',$gift);
	}

	public function gift_cards_receptor(PagadorGiftCardCreateRequest $request)
	{
		//dd($request);
        $gift = emisores::select('emisores.imagen','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $request->cod_emisor)
        ->first();
		
		$fk_id_comprador = 0;
		
		if($request->existe_cliente == 1)
		{
            $comprador = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
            ->join('role_user','users.id','role_user.user_id')
            ->where('role_user.role_id','=',5)
            ->where('users.dni','=',$request->cedula_comprador_e)
            ->where('users.nacionalidad','=',$request->nacionalidad_comprador_e)
            ->first();
			
			$fk_id_comprador = $comprador->id;
		}
		else{

			$user = User::create([
				'nacionalidad'      => $request->nacionalidad_comprador,
				'dni'               => $request->cedula_comprador,
				'first_name'        => $request->first_name_comprador,
				'last_name'         => $request->last_name_comprador,
				'email'             => $request->email_comprador,
				'password'          => Hash::make('qwerty123456'),
				'birthdate'         => null,
				'kind'              => 1,
				'cod_tel'           => $request->cod_tel_comprador,
				'num_tel'           => $request->num_tel_comprador
				]);

			$user->attachRole(5);
			
			$carnet = str_pad('6880'.$request->cedula_receptor, 16, '0');
			
			$nuevo_Carnet = carnet::create([
										'carnet' => $carnet,
										'limite' => 0,
										'disponible' => 0,
										'fk_id_banco' => 1,
										'fk_id_miembro' => $user->id,
										'fk_monedas' => 1,
										'carnet_real' => $carnet,
										'cod_emisor' => 'INTICARD001',
										'nombre' => $gift->nombregift,
									]);

			$fk_id_comprador = $user->id;			
		}
		
		return view('gift.receptor')
		->with('gift',$gift)
		->with('fk_id_comprador',$fk_id_comprador);
	}

	public function gift_cards_metodopago(BeneficiarioGiftCardCreateRequest $request)
	{
		//dd($request);	
			
        $gift = emisores::select('emisores.bin','emisores.imagen','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id','emisores.monto_minimo')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $request->cod_emisor)
        ->first();
		
		$fk_id_comprador = (int)$request->fk_id_comprador;
		
		$fk_id_receptor = 0;
		$fk_carnet_id_receptor = 0;
		$productos = null;
		$productos_consolidados =[];
		
		if($request->existe_cliente == 1)
		{
			
            $beneficiario = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
            ->join('role_user','users.id','role_user.user_id')
            ->where('role_user.role_id','=',5)
            ->where('users.dni','=',$request->cedula_receptor_e)
            ->where('users.nacionalidad','=',$request->nacionalidad_receptor_e)
            ->first();
			
			$fk_id_receptor = $beneficiario->id;			

            $existing_gift_card = carnet::select('users.id', 'carnet.id as carnet_id')
            ->join('users','users.id','carnet.fk_id_miembro')
            ->where('carnet.cod_emisor','=',$request->cod_emisor)
            ->where('users.id',$beneficiario->id)
			->get();

			$num_giftcards = count($existing_gift_card) + 1;

			$carnet = str_pad($gift->bin.$request->cedula_receptor_e.$num_giftcards, 16, '0');
			
			$nuevo_Carnet = carnet::create([
				'carnet' => $carnet,
				'limite' => 0,
				'disponible' => 0,
				'fk_id_banco' => 1,
				'fk_id_miembro' => $beneficiario->id,
				'fk_monedas' => 1,
				'carnet_real' => $carnet,
				'cod_emisor' => $request->cod_emisor,
				'nombre' => $gift->nombregift,
			  ]);
			  
			$fk_carnet_id_receptor = $nuevo_Carnet->id;

		}
		else{

			$user = User::create([
					'nacionalidad'      => $request->nacionalidad_receptor,
					'dni'               => $request->cedula_receptor,
					'first_name'        => $request->first_name_receptor,
					'last_name'         => $request->last_name_receptor,
					'email'             => $request->email_receptor,
					'password'          => Hash::make('qwerty123456'),
					'birthdate'         => null,
					'kind'              => 1,
					'cod_tel'           => $request->cod_tel_receptor,
					'num_tel'           => $request->num_tel_receptor
					]);

			$user->attachRole(5);
			
			$carnet = str_pad($gift->bin.$request->cedula_receptor, 16, '0');
			
            $nuevo_Carnet = carnet::create([
										'carnet' => $carnet,
										'limite' => 0,
										'disponible' => 0,
										'fk_id_banco' => 1,
										'fk_id_miembro' => $user->id,
										'fk_monedas' => 1,
										'carnet_real' => $carnet,
										'cod_emisor' => $request->cod_emisor,
										'nombre' => $gift->nombregift,
									  ]);			
									  
			$fk_id_receptor = $user->id;
			$fk_carnet_id_receptor = $nuevo_Carnet->id;
		}
		
		$productos = carnet::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor',DB::raw("CASE carnet.tipo_producto WHEN 1 THEN 'Interno' ELSE 'Externo' END AS tipo"))
		->join('monedas','monedas.mon_id','carnet.fk_monedas')
		->join('users','users.id','carnet.fk_id_miembro')
		->where('carnet.transar',true)
		->where('carnet.fk_monedas',$gift->mon_id)
		->where('users.id',$fk_id_comprador)
		->whereRaw("substring(carnet.carnet, 1, 4) <> '6540'")
		->whereRaw("COALESCE(carnet.cod_emisor, '') <> 'INTICARD001'")
		->distinct()
		->get();
		
		if(count($productos) > 0)
		{	
			foreach ($productos as $key => $value) {
				
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

				if($value['tipo'] == "Externo")
				{
					$creditoDisponible = 0;
					$value['limite'] = 0;
				}

				array_push($productos_consolidados, [
					'id'          => $value['id'],
					'limite'      => str_replace(".", ",",$value['limite']),
					'disponible'  => str_replace(".", ",",$creditoDisponible),
					'carnet'      =>$value['carnet'],
					'mon_id'      =>$value['fk_monedas'],
					'transar'  	  =>$value['transar'],
					'tipo_carnet' =>$value['tipo']
					]);									
				
			}
		}	
		
		array_push($productos_consolidados, [
			'id'          => 'Stripe',
			'limite'      => str_replace(".", ",", '0.00'),
			'disponible'  => str_replace(".", ",", '0.00'),
			'carnet'      => 'Stripe',
			'mon_id'      => 1,
			'transar'  	  => true,
			'tipo_carnet' => 'Externo'
			]);			
		
		return view('gift.metodopago')
		->with('gift',$gift)
		->with('fk_id_comprador',$fk_id_comprador)
		->with('fk_id_receptor',$fk_id_receptor)
		->with('fk_carnet_id_receptor',$fk_carnet_id_receptor)
		->with('productos',$productos_consolidados);
	}

	public function gift_cards_pagar(VentaGiftCardCompleteRequest $request)
	{
		//dd($request);
		
		$gift = emisores::select('emisores.id','emisores.tasa_comision','emisores.paga_comision','emisores.monto_fijo','emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id','emisores.dias_vencimiento','requiere_pin','pin')
		->join('monedas','emisores.fk_monedas','monedas.mon_id')
		->Where('emisores.cod_emisor', '=', $request->cod_emisor)
		->first();
		
		$fk_carnet_comprador = 0;
		$fk_carnet_receptor = 0;
		
		$tipo_carnet_medio = $request->carnet_real;
		
		
		//PAGADOR
		if($request->existe_comprador == 0)
		{
			$user = User::create([
				'nacionalidad'      => $request->nacionalidad_comprador,
				'dni'               => $request->cedula_comprador,
				'first_name'        => $request->first_name_comprador,
				'last_name'         => $request->last_name_comprador,
				'email'             => $request->email_comprador,
				'password'          => Hash::make('qwerty123456'),
				'birthdate'         => null,
				'kind'              => 1,
				'cod_tel'           => $request->cod_tel_comprador,
				'num_tel'           => $request->num_tel_comprador
				]);

			$user->attachRole(5);
			
			if($request->carnet_real == 'Stripe')
			{
				$carnet = str_pad('6880'.$request->cedula_comprador, 16, '0');
				
				$nuevo_Carnet = carnet::create([
											'carnet' => $carnet,
											'limite' => 0,
											'disponible' => 0,
											'fk_id_banco' => 1,
											'fk_id_miembro' => $user->id,
											'fk_monedas' => 1,
											'carnet_real' => $carnet,
											'cod_emisor' => 'INTICARD001',
											'cod_cliente_emisor' => $carnet,
											'nombre' => 'Stripe',
											'tipo_producto' => 2,
										]);
										
				$fk_carnet_comprador = $nuevo_Carnet->id;				
			}
			else{
				$carnet = str_pad('6890'.$request->cedula_comprador, 16, '0');
				
				$nuevo_Carnet = carnet::create([
											'carnet' => $carnet,
											'limite' => 0,
											'disponible' => 0,
											'fk_id_banco' => 1,
											'fk_id_miembro' => $user->id,
											'fk_monedas' => 1,
											'transar' => false,
											'carnet_real' => $carnet,
											'cod_emisor' => 'OTROSPAGOS001',
											'cod_cliente_emisor' => $carnet,
											'nombre' => 'Otros Pagos',
											'tipo_producto' => 2,
										]);
										
				$fk_carnet_comprador = $nuevo_Carnet->id;

				$request->carnet_real = $carnet;
			}
									
			$comprador = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
			->join('role_user','users.id','role_user.user_id')
			->where('role_user.role_id','=',5)
			->where('users.dni','=',$request->cedula_comprador)
			->where('users.nacionalidad','=',$request->nacionalidad_comprador)
			->first();
		
		}
		else{
			$comprador = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
			->join('role_user','users.id','role_user.user_id')
			->where('role_user.role_id','=',5)
			->where('users.dni','=',$request->cedula_comprador_e)
			->where('users.nacionalidad','=',$request->nacionalidad_comprador_e)
			->first();

			if($request->carnet_real == 'Otros')
			{
				$existing_tarjeta_otros_pagos = carnet::select('users.id', 'carnet.id as carnet_id', 'carnet.carnet')
				->join('users','users.id','carnet.fk_id_miembro')
				->where('carnet.cod_emisor','=','OTROSPAGOS001')
				->where('users.id',$comprador->id)
				->first();

				if(!$existing_tarjeta_otros_pagos)				
				{
					$carnet = str_pad('6890'.$request->cedula_comprador_e, 16, '0');
					
					$nuevo_Carnet = carnet::create([
												'carnet' => $carnet,
												'limite' => 0,
												'disponible' => 0,
												'fk_id_banco' => 1,
												'fk_id_miembro' => $comprador->id,
												'fk_monedas' => 1,
												'transar' => false,
												'carnet_real' => $carnet,
												'cod_emisor' => 'OTROSPAGOS001',
												'cod_cliente_emisor' => $carnet,
												'nombre' => 'Otros Pagos',
												'tipo_producto' => 2,
											]);
											
					$fk_carnet_comprador = $nuevo_Carnet->id;

					$request->carnet_real = $carnet;
				}
				else{
					$request->carnet_real = $existing_tarjeta_otros_pagos->carnet;
				}
			}
			else{
				if($request->carnet_real == 'Stripe')
				{
					$existing_tarjeta_stripe = carnet::select('users.id', 'carnet.id as carnet_id')
					->join('users','users.id','carnet.fk_id_miembro')
					->where('carnet.cod_emisor','=','INTICARD001')
					->where('users.id',$comprador->id)
					->get();

					if(!$existing_tarjeta_otros_pagos)				
					{
						$carnet = str_pad('6890'.$request->cedula_comprador_e, 16, '0');
						
						$nuevo_Carnet = carnet::create([
													'carnet' => $carnet,
													'limite' => 0,
													'disponible' => 0,
													'fk_id_banco' => 1,
													'fk_id_miembro' => $comprador->id,
													'fk_monedas' => 1,
													'transar' => true,
													'carnet_real' => $carnet,
													'cod_emisor' => 'INTICARD001',
													'cod_cliente_emisor' => $carnet,
													'nombre' => 'Stripe',
													'tipo_producto' => 2,
												]);
												
						$fk_carnet_comprador = $nuevo_Carnet->id;					
					}					
				}	
			}
		}

		//RECEPTOR
		if($request->existe_receptor == 1)
		{
			
            $beneficiario = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
            ->join('role_user','users.id','role_user.user_id')
            ->where('role_user.role_id','=',5)
            ->where('users.dni','=',$request->cedula_receptor_e)
            ->where('users.nacionalidad','=',$request->nacionalidad_receptor_e)
            ->first();				

            $existing_gift_card = carnet::select('users.id', 'carnet.id as carnet_id')
            ->join('users','users.id','carnet.fk_id_miembro')
            ->where('users.id',$beneficiario->id)
			//->whereRaw("substring(carnet.carnet, 1, 4) <> '6540'")
			->get();

			$num_giftcards = count($existing_gift_card) + 1;
			
			$carnet_LEFT = $gift->bin.$request->cedula_receptor_e;

			$carnet_RIGHT = str_pad($num_giftcards , (16 - strlen($carnet_LEFT)), '0', STR_PAD_LEFT);
			
			$carnet = $carnet_LEFT.$carnet_RIGHT;
			
			$nuevo_Carnet_Receptor = carnet::create([
				'carnet' => $carnet,
				'limite' => 0,
				'disponible' => 0,
				'fk_id_banco' => 1,
				'fk_id_miembro' => $beneficiario->id,
				'fk_monedas' => 1,
				'carnet_real' => $carnet,
				'transar' => false,
				'cod_emisor' => $request->cod_emisor,
				'nombre' => $gift->nombregift,
				'tipo_producto' => 1,
			  ]);

			$fk_carnet_receptor = $nuevo_Carnet_Receptor->id;

		}
		else{

			$user = User::create([
					'nacionalidad'      => $request->nacionalidad_receptor,
					'dni'               => $request->cedula_receptor,
					'first_name'        => $request->first_name_receptor,
					'last_name'         => $request->last_name_receptor,
					'email'             => $request->email_receptor,
					'password'          => Hash::make('qwerty123456'),
					'birthdate'         => null,
					'kind'              => 1,
					'cod_tel'           => $request->cod_tel_receptor,
					'num_tel'           => $request->num_tel_receptor
					]);

			$user->attachRole(5);
			
			$carnet = str_pad($gift->bin.$request->cedula_receptor.'1', 16, '0');
			
            $nuevo_Carnet_Receptor = carnet::create([
										'carnet' => $carnet,
										'limite' => 0,
										'disponible' => 0,
										'fk_id_banco' => 1,
										'fk_id_miembro' => $user->id,
										'fk_monedas' => 1,
										'transar' => false,
										'carnet_real' => $carnet,
										'cod_emisor' => $request->cod_emisor,
										'nombre' => $gift->nombregift,
										'tipo_producto' => 1,
									  ]);
									  
			$fk_carnet_receptor = $nuevo_Carnet_Receptor->id;
									  
            $beneficiario = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
            ->join('role_user','users.id','role_user.user_id')
            ->where('role_user.role_id','=',5)
            ->where('users.dni','=',$request->cedula_receptor)
            ->where('users.nacionalidad','=',$request->nacionalidad_receptor)
            ->first();									  
		}
			
		$Comercio = miem_come::select('comercios.es_sucursal','comercios.rif','comercios.id')
		->join('comercios','comercios.id','miem_come.fk_id_comercio')
		->where('miem_come.fk_id_miembro',Auth::user()->id)
		->first();
		
		$monto = str_replace(".", "",$request->monto_real);
		$monto = str_replace(",", ".",$monto);

		$comision_monto = ($monto * ($gift->tasa_comision / 100)) + $gift->monto_fijo;

		if($gift->paga_comision == 2) //PAGA EL CLIENTE LA COMISION
		{
			$monto = $monto + $comision_monto;
		}

		$monto = str_replace(".", ",",$monto);		
		$comision_monto = str_replace(".", ",",$comision_monto);

		if($request->carnet_real == 'Stripe')
		{
			$requestTarjetaInternacional = new \Illuminate\Http\Request();
			$requestTarjetaInternacional->setMethod('POST');
			$requestTarjetaInternacional->request->add([
				'cedula' => $comprador->dni,
				'monto' => $monto,
				'gift_card' => true,
				'fk_dni_recibe' => $beneficiario->id,
				'fk_carnet_id_recibe' => $fk_carnet_receptor,
				'monto_original' => str_replace(".", "",$request->monto_real),
				'comision_monto' => $comision_monto,
				'dias_vencimiento' => $gift->dias_vencimiento,
				'giftcard_id' => $gift->id,
				'giftcard_imagen' => $request->imagen_gift_back,
			]);
			
			return app(\App\Http\Controllers\StripeController::class)->create($requestTarjetaInternacional);			
		}
		else{

			$requestTarjetaNacional = new \Illuminate\Http\Request();
			$requestTarjetaNacional->setMethod('POST');
			$requestTarjetaNacional->request->add([
									'fk_id_comercio' => $Comercio->id,
									'cedula' => $comprador->dni,
									'carnet' => $request->carnet_real,
									'monto' => $monto,
									'comercioPropina' => null,
									'prop' => null,
									'propina_monto' => null,
									'gift_card' => true,
									'fk_dni_recibe' => $beneficiario->id,
									'fk_carnet_id_recibe' => $fk_carnet_receptor,
									'monto_original' => str_replace(".", "",$request->monto_real),
									'comision_monto' => $comision_monto,
									'dias_vencimiento' => $gift->dias_vencimiento,
									'giftcard_id' => $gift->id,
									'giftcard_imagen' => $request->imagen_gift_back,
									'tipo_producto' => $tipo_carnet_medio,
									'requiere_pin' => $gift->requiere_pin,
									'pin' => $gift->pin,
								]);
								
			return app(\App\Http\Controllers\TransaccionesController::class)->store($requestTarjetaNacional);			

		}
				
	}	
	
	public function create()
	{
		$user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
		}
		
		if($rol != 6)
		{
			return redirect()->route('home');
		}
		
        $comercios = comercios::select('comercios.rif','comercios.descripcion')
        ->where('comercios.razon_social','!=','jackpotImportPagos')
		->where('comercios.es_sucursal','=','false')
		->whereRaw("comercios.rif NOT IN (SELECT rif FROM emisores WHERE categoria = 'GiftCard')")
        ->orderBy('comercios.id','desc')
		->get();
		
		$MontoMaximo = config('webConfig.MontoMaximoGiftcard');
		
		return view('gift.create')->with('comercios',$comercios)
		->with('MontoMaximo',$MontoMaximo);
	}
	
	public function store(GiftRequest $request)
	{
		//dd($request);
		
        $comercio = comercios::select('comercios.descripcion')
        ->where('comercios.rif','=',$request->comercio_emisor)
		->first();	
					
		$nueva_Gift = emisores::create([
						'cod_emisor' 		=> $request->comercio_emisor,
						'nombre' 			=> $comercio->descripcion,
						'producto' 			=> $request->nombre,
						'fk_monedas' 		=> 1,
						'tipo' 				=> 'Interno',
						'categoria' 		=> 'GiftCard',
						'descripcion' 		=> $request->descripcion,
						'lema' 				=> $request->lema_comercial,
						'bin' 				=> '6540',
						'rif' 				=> $request->comercio_emisor,
						'paga_comision' 	=> $request->paga_comision == 'cliente' ? 2 : 1,
						'monto_fijo' 		=> str_replace(",", ".",$request->m_comision_fijo),
						'tasa_comision' 	=> str_replace(",", ".",$request->p_comision),
						'monto_minimo' 		=> str_replace(",", ".",$request->monto_minimo),
						'dias_vencimiento' 	=> $request->dias_vencimiento,
					  ]);
					  
		$file_name = "";
		$PathImages = config('webConfig.UnidadImagenesGiftcard');
		//$PathImages = base_path('public/img/GiftCard');
		
		if($request->has('CHECKg25'))
		{
			if ($request->hasFile('IMGg25')) {
				//  Let's do everything here
				if ($request->file('IMGg25')->isValid()) {
					//
					$name = uniqid();
					$extension = $request->IMGg25->extension();
					$file_name = $name.".".$extension;
					$request->IMGg25->move($PathImages, $file_name);
					
					$this->InsertGiftImage($nueva_Gift->id, $request->CHECKg25, $file_name);
				}
			}			
		}	

		if($request->has('CHECKg50'))
		{
			if ($request->hasFile('IMGg50')) {
				//  Let's do everything here
				if ($request->file('IMGg50')->isValid()) {
					//
					$name = uniqid();
					$extension = $request->IMGg50->extension();
					$file_name = $name.".".$extension;
					$request->IMGg50->move($PathImages, $file_name);
					
					$this->InsertGiftImage($nueva_Gift->id, $request->CHECKg50, $file_name);
				}
			}			
		}

		if($request->has('CHECKg100'))
		{
			if ($request->hasFile('IMGg100')) {
				//  Let's do everything here
				if ($request->file('IMGg100')->isValid()) {
					//
					$name = uniqid();
					$extension = $request->IMGg100->extension();
					$file_name = $name.".".$extension;
					$request->IMGg100->move($PathImages, $file_name);
					
					$this->InsertGiftImage($nueva_Gift->id, $request->CHECKg100, $file_name);
				}
			}			
		}

		if($request->has('CHECKg200'))
		{
			if ($request->hasFile('IMGg200')) {
				//  Let's do everything here
				if ($request->file('IMGg200')->isValid()) {
					//
					$name = uniqid();
					$extension = $request->IMGg200->extension();
					$file_name = $name.".".$extension;
					$request->IMGg200->move($PathImages, $file_name);
					
					$this->InsertGiftImage($nueva_Gift->id, $request->CHECKg200, $file_name);
				}
			}			
		}	


		if ($request->hasFile('IMGgOtros')) {
			//  Let's do everything here
			if ($request->file('IMGgOtros')->isValid()) {
				//
				$name = uniqid();
				$extension = $request->IMGgOtros->extension();
				$file_name = $name.".".$extension;
				$request->IMGgOtros->move($PathImages, $file_name);
				
				$this->InsertGiftImage($nueva_Gift->id, 'Otros', $file_name);
			}
		}			
	

		flash('GiftCard creada Satisfactoriamente', '¡Alert!')->success();
		return redirect()->route('gift.index');
	}
	
	public function InsertGiftImage($fk_giftcard, $monto, $nombre_imagen)
	{
		$nueva_Gift_imagen = giftcard_imagenes::create([
						'fk_giftcard' 		=> $fk_giftcard,
						'monto' 			=> $monto,
						'nombre_imagen'		=> $nombre_imagen,
					  ]);		
	}
	
	public function UpdateImages($gift_id, $monto, $nombre_imagen)
	{
		$image_giftcard = giftcard_imagenes::where('fk_giftcard' , '=' , $gift_id)->where('monto' , '=' , $monto)
		->update([
			'nombre_imagen'	=> $nombre_imagen,
		]);		
	}

	public function DeleteImage($gift_id, $monto)
	{
		DB::delete('delete from giftcard_imagenes where fk_giftcard = ? and monto = ?',[$gift_id, $monto]);
	}	
	
	public function edit($id)
	{
        $comercios = comercios::select('comercios.rif','comercios.descripcion')
        ->where('comercios.razon_social','!=','jackpotImportPagos')
        ->where('comercios.es_sucursal','=','false')
        ->orderBy('comercios.id','desc')
		->get();

        $gift = emisores::select('emisores.*')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.id', '=', $id)
		->first();

        $imagen25 = giftcard_imagenes::select('giftcard_imagenes.*')
        ->Where('giftcard_imagenes.fk_giftcard', '=', $id)
		->Where('giftcard_imagenes.monto', '=', '25')
		->first();

        $imagen50 = giftcard_imagenes::select('giftcard_imagenes.*')
        ->Where('giftcard_imagenes.fk_giftcard', '=', $id)
		->Where('giftcard_imagenes.monto', '=', '50')
		->first();

        $imagen100 = giftcard_imagenes::select('giftcard_imagenes.*')
        ->Where('giftcard_imagenes.fk_giftcard', '=', $id)
		->Where('giftcard_imagenes.monto', '=', '100')
		->first();

        $imagen200 = giftcard_imagenes::select('giftcard_imagenes.*')
        ->Where('giftcard_imagenes.fk_giftcard', '=', $id)
		->Where('giftcard_imagenes.monto', '=', '200')
		->first();

        $imagenOtros = giftcard_imagenes::select('giftcard_imagenes.*')
        ->Where('giftcard_imagenes.fk_giftcard', '=', $id)
		->Where('giftcard_imagenes.monto', '=', 'Otros')
		->first();

		$MontoMaximo = config('webConfig.MontoMaximoGiftcard');
		
		return view('gift.edit')
		->with('comercios',$comercios)
		->with('gift',$gift)
		->with('imagen25',$imagen25)
		->with('imagen50',$imagen50)
		->with('imagen100',$imagen100)
		->with('imagen200',$imagen200)
		->with('imagenOtros',$imagenOtros)
		->with('MontoMaximo',$MontoMaximo);
	}

	public function update(GiftEditRequest $request)
	{
		//dd($request);
		$user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
		}				

		$monedas = emisores::where('id',$request->id)
		->update([
			'producto' 			=> $request->nombre,
			'descripcion' 		=> $request->descripcion,
			'lema' 				=> $request->lema_comercial,
			'paga_comision' 	=> $request->paga_comision == 'cliente' ? 2 : 1,
			'monto_fijo' 		=> str_replace(",", ".",$request->m_comision_fijo),
			'tasa_comision' 	=> str_replace(",", ".",$request->p_comision),
			'monto_minimo' 		=> str_replace(",", ".",$request->monto_minimo),
			'dias_vencimiento' 	=> $request->dias_vencimiento,
		]);
		
		//ACTUALIZAR EL PIN
		if($request->has('requiere_pin'))
		{
			if($request->pin != "6dd42a")
			{
				$pin_encrypt=Crypt::encrypt($request->pin);
				
				$pin = emisores::where('id',$request->id)
				->update([
					'requiere_pin' 	=> true,
					'pin' 	=> $pin_encrypt,
				]);	
			}			
		}
		else{
			$pin = emisores::where('id',$request->id)
			->update([
				'requiere_pin' 	=> false,
				'pin' 	=> null,
			]);				
		}
		
		$file_name = "";
		$PathImages = config('webConfig.UnidadImagenesGiftcard');
		//$PathImages = base_path('public/img/GiftCard');

        if ($request->hasFile('IMGgOtros')) {
            //  Let's do everything here
            if ($request->file('IMGgOtros')->isValid()) {
							
				$name = uniqid();
				$extension = $request->IMGgOtros->extension();
				$file_name = $name.".".$extension;
				$request->IMGgOtros->move($PathImages, $file_name);
				
				if($request->ExitsImageOtros == "0")
				{
					$this->InsertGiftImage($request->id, "Otros", $file_name);
				}
				else{
					$this->UpdateImages($request->id, 'Otros', $file_name);
				}
            }
        }

        if ($request->hasFile('IMGg25')) {
            //  Let's do everything here
            if ($request->file('IMGg25')->isValid()) {
							
				$name = uniqid();
				$extension = $request->IMGg25->extension();
				$file_name = $name.".".$extension;
				$request->IMGg25->move($PathImages, $file_name);
				
				if($request->ExitsImage25 == "0")
				{
					$this->InsertGiftImage($request->id, "25", $file_name);
				}
				else{
					$this->UpdateImages($request->id, '25', $file_name);
				}
            }
        }
		else{
			if(!$request->has('CHECKg25'))
			{
				$this->DeleteImage($request->id, "25");
			}			
		}
		
        if ($request->hasFile('IMGg50')) {
            //  Let's do everything here
            if ($request->file('IMGg50')->isValid()) {
							
				$name = uniqid();
				$extension = $request->IMGg50->extension();
				$file_name = $name.".".$extension;
				$request->IMGg50->move($PathImages, $file_name);
				
				if($request->ExitsImage50 == "0")
				{
					$this->InsertGiftImage($request->id, "50", $file_name);
				}
				else{
					$this->UpdateImages($request->id, '50', $file_name);
				}
            }
        }
		else{
			if(!$request->has('CHECKg50'))
			{
				$this->DeleteImage($request->id, "50");
			}	
		}

        if ($request->hasFile('IMGg100')) {
            //  Let's do everything here
            if ($request->file('IMGg100')->isValid()) {
							
				$name = uniqid();
				$extension = $request->IMGg100->extension();
				$file_name = $name.".".$extension;
				$request->IMGg100->move($PathImages, $file_name);
				
				if($request->ExitsImage100 == "0")
				{
					$this->InsertGiftImage($request->id, "100", $file_name);
				}
				else{
					$this->UpdateImages($request->id, '100', $file_name);
				}
            }
        }
		else{
			if(!$request->has('CHECKg100'))
			{
				$this->DeleteImage($request->id, "100");
			}	
		}

        if ($request->hasFile('IMGg200')) {
            //  Let's do everything here
            if ($request->file('IMGg200')->isValid()) {
							
				$name = uniqid();
				$extension = $request->IMGg200->extension();
				$file_name = $name.".".$extension;
				$request->IMGg200->move($PathImages, $file_name);
				
				if($request->ExitsImage200 == "0")
				{
					$this->InsertGiftImage($request->id, "200", $file_name);
				}
				else{
					$this->UpdateImages($request->id, '200', $file_name);
				}
            }
        }
		else{
			if(!$request->has('CHECKg200'))
			{
				$this->DeleteImage($request->id, "200");
			}
		}		
        	
		
		if($rol == 3)
		{
			flash('GiftCard actualizada satisfactoriamente', '¡Alert!')->success();
			return redirect()->route('home');
		}
		else{
			flash('GiftCard actualizada satisfactoriamente', '¡Alert!')->success();
			return redirect()->route('gift.index');						
		}
	}
	
	public function Eliminar_Giftcards_Rechazadas()
	{
		//VALIDAR SI HAY GIFTCARDS CUYA TRANSACCION SALIO RECHAZADA
		$gift_card_rechazadas = trans_gift_card::select('trans_gift_card.id','trans_gift_card.fk_trans_id','trans_gift_card.fk_carnet_id_recibe','trans_gift_card.fk_dni_recibe')
		->join('trans_head','trans_gift_card.fk_trans_id','trans_head.id')
		->where('trans_head.status','=', 3)
		->get();
		
		
		if(count($gift_card_rechazadas) > 0)
		{
			foreach ($gift_card_rechazadas as $key => $value) {
				
				DB::delete('delete from trans_gift_card where id = ?',[$value->id]);
				
				DB::delete('delete from carnet where id = ?',[$value->fk_carnet_id_recibe]);
			}			
		}				
	}		
	
	public function ventas(Request $request)
	{		
		//ELIMINAR GIFTCARDS RECHAZADAS
		$this->Eliminar_Giftcards_Rechazadas();
		
		$user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
		}
		
        $time_desde= date('Y-m-d 00:00:00');
        $time_hasta= date('Y-m-d 23:59:59');		
		
		if($request->has('filter'))
		{
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
		}		
		

		if($rol == 3)
		{
			$comercio = comercios::select('comercios.rif')
			->join('miem_come','miem_come.fk_id_comercio','comercios.id')
			->where('miem_come.fk_id_miembro','=', Auth::user()->id)
			->first();

			$ventas_giftcard = emisores::select('trans_gift_card.created_at as fecha', 'emisores.nombre as comercio','emisores.rif','users.nacionalidad','users.dni','trans_gift_card.monto','trans_gift_card.comision_monto','trans_gift_card.pago_comision','monedas.mon_simbolo','ProducCompra.nombre as tipo_producto_compra')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->join('users','users.id','trans_gift_card.fk_dni_recibe')
			->join('monedas','monedas.mon_id','emisores.fk_monedas')
			->join('trans_head','trans_head.id','trans_gift_card.fk_trans_id')
			->join('carnet','carnet.id','trans_head.carnet_id')
			->leftJoin('emisores as ProducCompra','ProducCompra.cod_emisor','carnet.cod_emisor')
			->where('emisores.rif','=',$comercio->rif)
			->where('trans_head.status','!=',1)
			->whereBetween('trans_gift_card.created_at',array(
						$time_desde,
						$time_hasta
			));

			if($request->has('filter'))
			{
				if($request->has('cedula'))
				{
					if($request->cedula != "")
					{
						$ventas_giftcard = $ventas_giftcard->where('users.dni','=',$request->cedula);
					}
				}
			}
			
			$ventas_giftcard = $ventas_giftcard->get();			
			
			$comercios = emisores::select('emisores.nombre as comercio','emisores.rif')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->where('emisores.rif','=',$comercio->rif)
			->distinct()
			->get();
			
		}
		else{
			$ventas_giftcard = emisores::select('trans_gift_card.created_at as fecha', 'emisores.nombre as comercio','emisores.rif','users.nacionalidad','users.dni','trans_gift_card.monto','trans_gift_card.comision_monto','trans_gift_card.pago_comision','monedas.mon_simbolo','ProducCompra.nombre as tipo_producto_compra')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->join('users','users.id','trans_gift_card.fk_dni_recibe')
			->join('monedas','monedas.mon_id','emisores.fk_monedas')
			->join('trans_head','trans_head.id','trans_gift_card.fk_trans_id')
			->join('carnet','carnet.id','trans_head.carnet_id')
			->leftJoin('emisores as ProducCompra','ProducCompra.cod_emisor','carnet.cod_emisor')
			->where('trans_head.status','!=',1)
			->whereBetween('trans_gift_card.created_at',array(
						$time_desde,
						$time_hasta
			));

			if($request->has('filter'))
			{
				if($request->has('cedula'))
				{
					if($request->cedula != "")
					{
						$ventas_giftcard = $ventas_giftcard->where('users.dni','=',$request->cedula);
					}
				}
				
				if($request->has('comercio_emisor'))
				{
					if($request->comercio_emisor != "")
					{
						$ventas_giftcard = $ventas_giftcard->where('emisores.rif','=',$request->comercio_emisor);
					}
				}				
			}
			
			$ventas_giftcard = $ventas_giftcard->get();	
			
			$comercios = emisores::select('emisores.nombre as comercio','emisores.rif')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->distinct()
			->get();
			
		}

		$num_resultados = count($ventas_giftcard);

		return view('gift.ventas')
		->with('ventas_giftcard',$ventas_giftcard)
		->with('num_resultados',$num_resultados)
		->with('comercios',$comercios);
	}	
	
	public function consolidado(Request $request)
	{
		//ELIMINAR GIFTCARDS RECHAZADAS
		$this->Eliminar_Giftcards_Rechazadas();
		
		$user= User::find(Auth::user()->id);

        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
		}
		
        $time_desde= date('Y-m-d 00:00:00');
        $time_hasta= date('Y-m-d 23:59:59');		
		
		if($request->has('filter'))
		{
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
		}		
		

		if($rol == 3)
		{
			$comercio = comercios::select('comercios.rif')
			->join('miem_come','miem_come.fk_id_comercio','comercios.id')
			->where('miem_come.fk_id_miembro','=', Auth::user()->id)
			->first();

			$ventas_giftcard = emisores::select('trans_gift_card.id','trans_gift_card.created_at as fecha', 'emisores.nombre as comercio','emisores.rif','users.nacionalidad','users.dni','trans_gift_card.monto','monedas.mon_simbolo',DB::raw('COALESCE(SUM(trans_head.neto), 0) as total_compras'),DB::raw('trans_gift_card.monto - COALESCE(SUM(trans_head.neto), 0) as saldo'),'ProducCompra.nombre as tipo_producto_compra')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->join('users','users.id','trans_gift_card.fk_dni_recibe')
			->join('monedas','monedas.mon_id','emisores.fk_monedas')
			->join('carnet','carnet.id','trans_gift_card.fk_carnet_id_recibe')
			->join('trans_head as trans_compra','trans_compra.id','trans_gift_card.fk_trans_id')
			->join('carnet as carnet_compra','carnet_compra.id','trans_compra.carnet_id')
			->leftJoin('emisores as ProducCompra','ProducCompra.cod_emisor','carnet_compra.cod_emisor')						
			->leftJoin('trans_head','trans_head.carnet_id','carnet.id')
			->whereRaw("COALESCE(trans_head.status, '0') = '0'")
			->whereRaw("COALESCE(trans_compra.status, '0') != 1")
			->whereRaw("COALESCE(trans_head.reverso, '-1') = '-1'")
			->where('emisores.rif','=',$comercio->rif)
			
			->whereBetween('trans_gift_card.created_at',array(
						$time_desde,
						$time_hasta
			));

			if($request->has('filter'))
			{
				if($request->has('cedula'))
				{
					if($request->cedula != "")
					{
						$ventas_giftcard = $ventas_giftcard->where('users.dni','=',$request->cedula);
					}
				}
			}
			
			$ventas_giftcard = $ventas_giftcard->groupBy('trans_gift_card.id','trans_gift_card.created_at','emisores.nombre','emisores.rif','users.nacionalidad','users.dni','trans_gift_card.monto','monedas.mon_simbolo','ProducCompra.nombre')->get();
			
			$comercios = emisores::select('emisores.nombre as comercio','emisores.rif')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->distinct()
			->get();			

		}
		else{
			$ventas_giftcard = emisores::select('trans_gift_card.id','trans_gift_card.created_at as fecha', 'emisores.nombre as comercio','emisores.rif','users.nacionalidad','users.dni','trans_gift_card.monto','monedas.mon_simbolo',DB::raw('COALESCE(SUM(trans_head.neto), 0) as total_compras'),DB::raw('trans_gift_card.monto - COALESCE(SUM(trans_head.neto), 0) as saldo'),'ProducCompra.nombre as tipo_producto_compra')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->join('users','users.id','trans_gift_card.fk_dni_recibe')
			->join('monedas','monedas.mon_id','emisores.fk_monedas')
			->join('carnet','carnet.id','trans_gift_card.fk_carnet_id_recibe')
			->join('trans_head as trans_compra','trans_compra.id','trans_gift_card.fk_trans_id')
			->join('carnet as carnet_compra','carnet_compra.id','trans_compra.carnet_id')
			->leftJoin('emisores as ProducCompra','ProducCompra.cod_emisor','carnet_compra.cod_emisor')				
			->leftJoin('trans_head','trans_head.carnet_id','carnet.id')
			->whereRaw("COALESCE(trans_head.status, '0') = '0'")
			->whereRaw("COALESCE(trans_compra.status, '0') != 1")
			->whereRaw("COALESCE(trans_head.reverso, '-1') = '-1'")
			->whereBetween('trans_gift_card.created_at',array(
						$time_desde,
						$time_hasta
			));
			
			if($request->has('filter'))
			{
				if($request->has('cedula'))
				{
					if($request->cedula != "")
					{
						$ventas_giftcard = $ventas_giftcard->where('users.dni','=',$request->cedula);
					}
				}
				
				if($request->comercio_emisor != "")
				{
					$ventas_giftcard = $ventas_giftcard->where('emisores.rif','=',$request->comercio_emisor);
				}				
			}			

			$ventas_giftcard = $ventas_giftcard->groupBy('trans_gift_card.id','trans_gift_card.created_at','emisores.nombre','emisores.rif','users.nacionalidad','users.dni','trans_gift_card.monto','monedas.mon_simbolo','ProducCompra.nombre')->get();
			
			$comercios = emisores::select('emisores.nombre as comercio','emisores.rif')
			->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
			->distinct()
			->get();

		}

		$num_resultados = count($ventas_giftcard);

		return view('gift.consolidado')
		->with('ventas_giftcard',$ventas_giftcard)
		->with('num_resultados',$num_resultados)
		->with('comercios',$comercios);		
	}	
	
	public function detallecliente($id)
	{
		$datos_giftcard = emisores::select('monedas.mon_simbolo','trans_gift_card.vencimiento','trans_gift_card.id','trans_gift_card.created_at as fecha', 'emisores.nombre as comercio','emisores.rif','users.nacionalidad','users.dni','users.first_name','users.last_name', 'users.email', 'users.cod_tel', 'users.num_tel','trans_gift_card.monto')
		->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
		->join('users','users.id','trans_gift_card.fk_dni_recibe')
		->join('monedas','monedas.mon_id','emisores.fk_monedas')
		->where('trans_gift_card.id','=', $id)
		->first();

		$saldo = emisores::select(DB::raw('trans_gift_card.monto - COALESCE(SUM(trans_head.neto), 0) as monto'))
		->join('trans_gift_card','trans_gift_card.giftcard_id','emisores.id')
		->join('users','users.id','trans_gift_card.fk_dni_recibe')
		->join('monedas','monedas.mon_id','emisores.fk_monedas')
		->join('carnet','carnet.id','trans_gift_card.fk_carnet_id_recibe')
		->leftJoin('trans_head','trans_head.carnet_id','carnet.id')
		->whereRaw("COALESCE(trans_head.status, '0') = '0'")
		->whereRaw("COALESCE(trans_head.reverso, '-1') = '-1'")
		->where('trans_gift_card.id','=', $id)
		->groupBy('trans_gift_card.monto')
		->first();

		$ultima_compra = trans_head::select('trans_head.created_at as fecha')
		->join('carnet','carnet.id','trans_head.carnet_id')
		->join('trans_gift_card','trans_gift_card.fk_carnet_id_recibe','carnet.id')
		->where('trans_gift_card.id','=', $id)
		->whereRaw("COALESCE(trans_head.status, '0') = '0'")
		->whereRaw("COALESCE(trans_head.reverso, '-1') = '-1'")		
		->orderBy('trans_head.id', 'DESC')
		->first();

		$compra_giftcard = trans_gift_card::select('trans_gift_card.created_at as fecha','users.nacionalidad','users.dni','trans_gift_card.monto',DB::raw("0.00 as monto_consumo"),'trans_gift_card.monto as saldo')
		->join('users','users.id','trans_gift_card.fk_dni_recibe')
		->where('trans_gift_card.id','=', $id)
		->get();

		$consumos_giftcard = trans_head::select('trans_head.created_at as fecha','users.nacionalidad','users.dni',DB::raw("0.00 as monto"),'trans_head.neto as monto_consumo',DB::raw("0.00 as saldo"))
		->join('carnet','carnet.id','trans_head.carnet_id')
		->join('trans_gift_card','trans_gift_card.fk_carnet_id_recibe','carnet.id')
		->join('users','users.id','trans_gift_card.fk_dni_recibe')
		->where('trans_gift_card.id','=', $id)
		->where('trans_head.status','=', '0')
		->whereRaw("COALESCE(trans_head.reverso, '0') = '0'")		
		->orderBy('trans_head.id', 'ASC')
		->get();

		if(count($compra_giftcard) > 0)
		{
			$total_monto_giftcard = $compra_giftcard[0]->monto;

			foreach ($consumos_giftcard as $key => $value) {

				$total_monto_giftcard = $total_monto_giftcard - $consumos_giftcard[$key]->monto_consumo;

				$consumos_giftcard[$key]->saldo = number_format(round($total_monto_giftcard, 2), 2, ',', '.');
			}			
		}

		$num_resultados = count($consumos_giftcard) + count($compra_giftcard);



		return view('gift.detallecliente')
		->with('datos_giftcard',$datos_giftcard)
		->with('saldo',$saldo)
		->with('ultima_compra',$ultima_compra)
		->with('consumos_giftcard',$consumos_giftcard)
		->with('compra_giftcard',$compra_giftcard)
		->with('num_resultados',$num_resultados);
	}	
	
	public function gift_cards_step2($id)
	{
        $gift = emisores::select('emisores.imagen','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $id)
        ->first();

		
		return view('gift.step2')->with('gift',$gift);
	}
	
	public function gift_cards_step3(Request $request)
	{
		//dd($request);
		
        $gift = emisores::select('emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $request->cod_emisor)
        ->first();
		
		$quien_compra = User::select('users.id','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
		->join('role_user','users.id','role_user.user_id')
		->where('role_user.role_id','=',5)
		->where('users.dni','=',$request->cedula)
		->where('users.nacionalidad','=',$request->nacionalidad)
		->first();
		
		$existe_cliente = false;
		$puede_pagar_nac = false;
		$cedula = $request->cedula;
		$nacionalidad = $request->nacionalidad;

		if($quien_compra != null)
		{
			$existe_cliente = true;
			
			$productos = carnet::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor','emisores.tipo','emisores.producto')
			->join('emisores','emisores.cod_emisor','carnet.cod_emisor')
			->join('monedas','monedas.mon_id','emisores.fk_monedas')
			->join('users','users.id','carnet.fk_id_miembro')
			->where('carnet.transar',true)
			->where('carnet.fk_monedas',$gift->mon_id)
			->where('users.id',$quien_compra->id)
			->where('emisores.cod_emisor','!=','INTICARD001')
			->distinct()
			->get();
			
			if(count($productos) > 0)
			{
				$puede_pagar_nac = true;
			}
		}
		
		$monto = $request->monto;
		
		return view('gift.step3')->with('gift',$gift)
		->with('monto',$monto)
		->with('existe_cliente',$existe_cliente)
		->with('cedula',$cedula)
		->with('nacionalidad',$nacionalidad)
		->with('puede_pagar_nac',$puede_pagar_nac);
	}

	public function gift_cards_step4(Request $request)
	{
		//dd($request);
		
        $gift = emisores::select('emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $request->cod_emisor)
        ->first();
		
		$quien_compra = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
		->join('role_user','users.id','role_user.user_id')
		->where('role_user.role_id','=',5)
		->where('users.dni','=',$request->cedula)
		->where('users.nacionalidad','=',$request->nacionalidad)
		->first();
		
		$existe_cliente = false;
		$puede_pagar_nac = false;
		$cedula = $request->cedula;
		$nacionalidad = $request->nacionalidad;
		$productos = null;
		$productos_consolidados =[];
		$tipo_tarjeta = $request->metodo_pago;
		
		if($quien_compra != null)
		{
			$existe_cliente = true;
			
			$productos = carnet::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor','emisores.tipo','emisores.producto')
			->join('emisores','emisores.cod_emisor','carnet.cod_emisor')
			->join('monedas','monedas.mon_id','emisores.fk_monedas')
			->join('users','users.id','carnet.fk_id_miembro')
			->where('carnet.transar',true)
			->where('carnet.fk_monedas',$gift->mon_id)
			->where('users.id',$quien_compra->id)
			->where('emisores.cod_emisor','!=','INTICARD001')
			->where('emisores.categoria','=','Producto')
			->distinct()
			->get();
			
			if(count($productos) > 0)
			{
				$puede_pagar_nac = true;
				
				foreach ($productos as $key => $value) {
					
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

					if($value['tipo'] == "Externo")
					{
						$creditoDisponible = 0;
						$value['limite'] = 0;
					}

					array_push($productos_consolidados, [
					'id'          => $value['id'],
					'limite'      => str_replace(".", ",",$value['limite']),
					'disponible'  => str_replace(".", ",",$creditoDisponible),
					'carnet'      =>$value['carnet'],
					'mon_id'      =>$value['fk_monedas'],
					'transar'  	  =>$value['transar'],
					'tipo_carnet' =>$value['tipo'],
					'nombre_producto' => $value['producto']
					]);					
					
				}
			}

			array_push($productos_consolidados, [
				'id'          => 'Stripe',
				'limite'      => str_replace(".", ",", '0.00'),
				'disponible'  => str_replace(".", ",", '0.00'),
				'carnet'      => 'Stripe',
				'mon_id'      => 1,
				'transar'  	  => true,
				'tipo_carnet' => 'Externo'
				]);				
		}
		
		$monto = $request->monto;
		
		return view('gift.step4')->with('gift',$gift)
		->with('monto',$monto)
		->with('quien_compra',$quien_compra)
		->with('productos',$productos_consolidados)
		->with('existe_cliente',$existe_cliente)
		->with('cedula',$cedula)
		->with('nacionalidad',$nacionalidad)
		->with('tipo_tarjeta',$tipo_tarjeta)
		->with('puede_pagar_nac',$puede_pagar_nac);
	}
	
	public function gift_cards_step5(PagadorGiftCardCreateRequest $request)
	{
		//dd($request);
        $gift = emisores::select('emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $request->cod_emisor)
        ->first();
		
		$existe_cliente = $request->existe_cliente;
		$tipo_tarjeta = $request->tipo_tarjeta;
		$monto = $request->monto;
		
		$producto_pagador = $request->carnet;
		
		if($existe_cliente == 0)
		{
			$cedula = $request->cedula;
			$nacionalidad = $request->nacionalidad;			
		}
		else{
			$cedula = $request->cedula_existe;
			$nacionalidad = $request->nacionalidad_existe;
					
		}
		
		if($existe_cliente == 0)
		{
			$user = User::create([
					'nacionalidad'      => $request->nacionalidad,
					'dni'               => $request->cedula,
					'first_name'        => $request->first_name,
					'last_name'         => $request->last_name,
					'email'             => $request->email,
					'password'          => Hash::make('qwerty123456'),
					'birthdate'         => null,
					'kind'              => 1,
					'cod_tel'           => $request->cod_tel,
					'num_tel'           => $request->num_tel
					]);

			$user->attachRole(5);
			
			$carnet = str_pad($gift->bin.$request->cedula, 16, '0');
			
			carnet::create([
				'carnet' => $carnet,
				'limite' => 0,
				'disponible' => 0,
				'fk_id_banco' => 1,
				'fk_id_miembro' => $user->id,
				'fk_monedas' => 1,
				'carnet_real' => $carnet,
				'cod_emisor' => 'INTICARD001',
				'cod_cliente_emisor' => $carnet,
				'nombre' => 'Stripe',
			  ]);			
		}
		else{
			
			if($tipo_tarjeta == 'tarjeta_internacional')
			{
				$quien_compra = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
				->join('role_user','users.id','role_user.user_id')
				->where('role_user.role_id','=',5)
				->where('users.dni','=',$request->cedula_existe)
				->where('users.nacionalidad','=',$request->nacionalidad_existe)
				->first();
				
				
				$existing_carnet_internacional = carnet::select('users.id', 'carnet.id as carnet_id')
					->join('users','users.id','carnet.fk_id_miembro')
					->where('carnet.cod_emisor','=','INTICARD001')
					->where('users.id',$quien_compra->id)
					->first();
				
				if(!$existing_carnet_internacional)
				{
					$carnet = str_pad($gift->bin.$request->cedula_existe, 16, '0');
					
					carnet::create([
						'carnet' => $carnet,
						'limite' => 0,
						'disponible' => 0,
						'fk_id_banco' => 1,
						'fk_id_miembro' => $quien_compra->id,
						'fk_monedas' => 1,
						'carnet_real' => $carnet,
						'cod_emisor' => 'INTICARD001',
						'cod_cliente_emisor' => $carnet,
						'nombre' => 'Stripe',
					  ]);					
				}
			}					
		}		
		
		//dd($request);
		
		return view('gift.step5')
		->with('tipo_tarjeta',$tipo_tarjeta)
		->with('monto',$monto)
		->with('cedula_pagador',$cedula)
		->with('producto_pagador',$producto_pagador)
		->with('existe_beneficiario',true)
		->with('buscar_beneficiario',true)
		->with('gift',$gift);
				
	}

	public function gift_cards_step6(BeneficiarioGiftCardCreateRequest $request)
	{
		//dd($request);
		
        $gift = emisores::select('emisores.fk_id_comer', 'emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
        ->join('monedas','emisores.fk_monedas','monedas.mon_id')
        ->Where('emisores.cod_emisor', '=', $request->cod_emisor)
        ->first();		

		$beneficiario = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
		->join('role_user','users.id','role_user.user_id')
		->where('role_user.role_id','=',5)
		->where('users.dni','=',$request->cedula_buscar)
		->where('users.nacionalidad','=',$request->nacionalidad_buscar)
		->first();
		
		$existe_beneficiario = false;
		
		if($beneficiario)
		{
			$existe_beneficiario = true;
		}
		
		return view('gift.step6')
		->with('gift',$gift)
		->with('tipo_tarjeta',$request->tipo_tarjeta)
		->with('monto',$request->monto)
		->with('cedula_pagador',$request->cedula_pagador)
		->with('cedula_beneficiario',$request->cedula_buscar)
		->with('nacionalidad_beneficiario',$request->nacionalidad_buscar)
		->with('producto_pagador',$request->producto_pagador)		
		->with('existe_beneficiario',$existe_beneficiario)
		->with('buscar_beneficiario',false)
		->with('beneficiario',$beneficiario);
	}

	public function gift_cards_step7(BeneficiarioGiftCardCreateRequest $request)
	{
		//dd($request);
		$fk_dni_recibe = 0;
		$fk_carnet_id_recibe = 0;
		
		$gift = emisores::select('emisores.bin','emisores.cod_emisor','emisores.lema', 'emisores.nombre as emisor','emisores.producto as nombregift','emisores.descripcion', 'monedas.mon_simbolo','monedas.mon_id')
				->join('monedas','emisores.fk_monedas','monedas.mon_id')
				->Where('emisores.cod_emisor', '=', $request->cod_emisor)
				->first();				

        if($request->existe_beneficiario == 0)
        {
            $user = User::create([
                'nacionalidad'      => $request->nacionalidad,
                'dni'               => $request->cedula,
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'email'             => $request->email,
                'password'          => Hash::make('qwerty123456'),
                'birthdate'         => null,
                'kind'              => 1,
                'cod_tel'           => $request->cod_tel,
                'num_tel'           => $request->num_tel
                ]);
    
            $user->attachRole(5);
    
            $carnet = str_pad($gift->bin.$request->cedula, 16, '0');
    
            $nuevo_Carnet = carnet::create([
										'carnet' => $carnet,
										'limite' => 0,
										'disponible' => 0,
										'fk_id_banco' => 1,
										'fk_id_miembro' => $user->id,
										'fk_monedas' => 1,
										'carnet_real' => $carnet,
										'cod_emisor' => $request->cod_emisor,
										'nombre' => $gift->nombregift,
									  ]);
			
			$fk_dni_recibe = $user->id;
			$fk_carnet_id_recibe = $nuevo_Carnet->id;
        }
        else
        {
            $beneficiario = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
            ->join('role_user','users.id','role_user.user_id')
            ->where('role_user.role_id','=',5)
            ->where('users.dni','=',$request->cedula_beneficiario)
            ->where('users.nacionalidad','=',$request->nacionalidad_beneficiario)
            ->first();
			
			$fk_dni_recibe = $beneficiario->id;

            $existing_gift_card = carnet::select('users.id', 'carnet.id as carnet_id')
            ->join('users','users.id','carnet.fk_id_miembro')
            ->where('carnet.cod_emisor','=',$request->cod_emisor)
            ->where('users.id',$beneficiario->id)
            ->first();

            if(!$existing_gift_card)
            {
                $carnet = str_pad($gift->bin.$request->cedula_beneficiario, 16, '0');
                
                $nuevo_Carnet = carnet::create([
												'carnet' => $carnet,
												'limite' => 0,
												'disponible' => 0,
												'fk_id_banco' => 1,
												'fk_id_miembro' => $beneficiario->id,
												'fk_monedas' => 1,
												'carnet_real' => $carnet,
												'cod_emisor' => $request->cod_emisor,
												'nombre' => $gift->nombregift,
											  ]);
											  
				$fk_carnet_id_recibe = $nuevo_Carnet->id;
            }
			else
			{
				$fk_carnet_id_recibe = $existing_gift_card->carnet_id;
			}
        }

		if($request->tipo_tarjeta == 'tarjeta_nacional')
		{
			$requestTarjetaNacional = new \Illuminate\Http\Request();
			$requestTarjetaNacional->setMethod('POST');
			$requestTarjetaNacional->request->add([
									'fk_id_comercio' => $request->fk_id_comercio,
									'cedula' => $request->cedula_pagador,
									'carnet' => $request->carnet,
									'monto' => $request->monto,
									'comercioPropina' => null,
									'prop' => null,
									'propina_monto' => null,
									'gift_card' => true,
									'fk_dni_recibe' => $fk_dni_recibe,
									'fk_carnet_id_recibe' => $fk_carnet_id_recibe,
								]);
								
			return app(\App\Http\Controllers\TransaccionesController::class)->store($requestTarjetaNacional);			
			
			//dd($requestTarjetaNacional);
		}
		else{
			dd($request);
		}		
	}
    

    public function consultaDatos($cedula, $nacionalidad){
              
		$quien_compra = User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','users.email','users.cod_tel','users.num_tel')
		->join('role_user','users.id','role_user.user_id')
		->where('role_user.role_id','=',5)
		->where('users.dni','=',$cedula)
		->where('users.nacionalidad','=',$nacionalidad)
		->first();

        $values =[];
		$productos_consolidados =[];

        if($quien_compra){
			
			$productos = carnet::select('users.id','users.id As UserId', 'carnet.id','carnet.limite','carnet.carnet','carnet.disponible','carnet.fk_monedas', 'monedas.mon_status','carnet.transar','carnet.cod_emisor',DB::raw("CASE carnet.tipo_producto WHEN 1 THEN 'Interno' ELSE 'Externo' END AS tipo"))
			->join('monedas','monedas.mon_id','carnet.fk_monedas')
			->join('users','users.id','carnet.fk_id_miembro')
			->where('carnet.transar',true)
			->where('carnet.fk_monedas',1)
			->where('users.id',$quien_compra->id)
			->whereRaw("substring(carnet.carnet, 1, 4) <> '6540'")
			->whereRaw("COALESCE(carnet.cod_emisor, '') <> 'INTICARD001'")
			->distinct()
			->get();

			if(count($productos) > 0)
			{
				foreach ($productos as $key => $value) {
					
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
					
					if($value['tipo'] == "Externo")
					{
						$creditoDisponible = 0;
						$value['limite'] = 0;
					}

					array_push($productos_consolidados, [
						'id'          => $value['id'],
						'limite'      => str_replace(".", ",",$value['limite']),
						'disponible'  => str_replace(".", ",",$creditoDisponible),
						'carnet'      =>$value['carnet'],
						'mon_id'      =>$value['fk_monedas'],
						'transar'  	  =>$value['transar'],
						'tipo_carnet' =>$value['tipo']
						]);					
				
				}
			}
			
			//ENMASCARAR EMAIL
			$array_email = explode("@",$quien_compra->email);
			
			$email_comprador = str_pad(substr($array_email[0],0,1), (strlen($array_email[0]) - 3) ,"*") . "***" .substr($array_email[0],-1). "@" . $array_email[1];
			
			//ENMASCARAR TELEFONO
			$telefono_comprador = "****" . substr($quien_compra->num_tel,4);
			
			array_push($values, [
				'id'			=> $quien_compra->id,
				'nacionalidad'  => $quien_compra->nacionalidad,
				'cedula'    		=> $quien_compra->dni,
				'first_name'    => $quien_compra->first_name,
				'last_name'     => $quien_compra->last_name,
				'email'			=> $email_comprador,
				'cod_tel'		=> $quien_compra->cod_tel,
				'num_tel'		=> $telefono_comprador,
				'productos'		=> $productos_consolidados,
			]);

			return response()->json($values,200);

        }else{

            return response()->json([
                'fallido'      => true,
            ],200);

        }
    }  

  

}
