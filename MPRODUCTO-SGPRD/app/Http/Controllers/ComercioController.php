<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\comercios;
use App\Http\Requests\ComercioRequest;
use App\Http\Requests\ComercioEditRequest;
use App\Models\bancos;
use App\Models\banc_comer;
use App\Models\miem_come;
use App\Models\miem_ban;
use App\Models\trans_head;
use App\Models\estados;
use App\Models\comercios_categoria;
use App\Models\comercios_subcategoria;
use App\Models\comercios_motivo;
use App\Models\comercios_estatus;
use App\Models\canal;
use App\Models\canal_comer;
use App\Models\terminal;
use Carbon\Carbon;
use Excel;
use Storage;
use App\Models\User;
use Illuminate\Support\Collection as Collection;

class ComercioController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function insertLog($accion){
         /*log de auditoria*/
            $user= User::find(Auth::user()->id);
            $id_user=$user->id;
            $email = $user->email;
            $ip = \Request::ip();

            $log = new \App\Models\log();
            $log->user_id = $id_user;
            $log->accion = "El usuario ".$email." ".$accion;
            $log->ip = $ip;
            $log->save();
    }

    public function importar($id)
    {
        $bancos = bancos::select('bancos.*')
        ->get();

        $URLServicio = config('webConfig.URLAfiliacionMasiva');

        return view('comercios.importar')->with(['bancos' => $bancos,'URLServicio' => $URLServicio]);
    }

    public function afiliar_comercios(Request $request)
    {
        $archivo = $request->file('archivo');
        $banco = $request['banco'];
        //dd($banco);
        $nombre_original=$archivo->getClientOriginalName();
        $extension=$archivo->getClientOriginalExtension();
        $r1=Storage::disk('archivos')->put($nombre_original,  \File::get($archivo) );
        $ruta  =  storage_path('archivos') ."/". $nombre_original;

        //$file = new CURLFile($ruta);

        $data_array =  array(
            'files' => '@' . $ruta,
            //"archivo"        => $archivo,
        );

        //Initialise the cURL var
        $ch = curl_init();

        //Get the response from cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Set the Url
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:53977/api/commerce/afiliacion');

        //Create a POST array with the file in it
        $postData = array(
            'files' => '@' . $ruta,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        // Execute the request
        $response = curl_exec($ch);        
        

        $make_call = $this->callAPI('POST', 'https://localhost:44308/api/commerce/afiliacion', json_encode($data_array));
        $response = json_decode($make_call, true);        

        return view('comercios.importar')->with(['bancos' => $bancos]);
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


    public function index(Request $request)
    {
       
        $accion = "ha accedido al módulo Listar de Comercio";
        $this->insertLog($accion); 

         
        $comercios = comercios::select('comercios.id as IdComer',
        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as fulldescripcion"),
        DB::raw("(SELECT COUNT(*) FROM comercios as Sucursales WHERE Sucursales.rif = comercios.rif and Sucursales.id != comercios.id) as sucursales"),
            'comercios.*',
            'banc_comer.*'
        )
        ->withTrashed()
        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
        ->where('comercios.razon_social','!=','jackpotImportPagos')
        ->where('comercios.es_sucursal','=','false')
        ->orderBy('comercios.id','desc')->take(100);

        if ($request->descripcion) {   
            $desc = strtolower($request->descripcion);        
            $comercios = $comercios->where(DB::raw('LOWER(descripcion)'),'LIKE', "%$desc%");
        }
        if ($request->razon_social) {
            $rSocial = strtolower($request->razon_social);   
            $comercios = $comercios->where(DB::raw('LOWER(razon_social)'),'LIKE', "%$rSocial%"); 
        }
        if ($request->rif) {
            $comercios = $comercios->where('rif',$request->rif);
        }
        /*if ($request->num_cta_princ) {
            $comercios = $comercios->where('num_cta_princ',$request->num_cta_princ);
        }
        if ($request->direccion) {
            $comercios = $comercios->where('direccion',$request->direccion);
        }
        if ($request->telefono1) {
            $comercios = $comercios->where('telefono1',$request->telefono1);
        }
        if ($request->email) {
            $comercios = $comercios->where('email',$request->email);
        }*/

        $comercios = $comercios->get();
		
        //dd($comercios);
        //print_r($comercios); 

        $countComer = $comercios->count();
        
        return view('comercios.index')->with(['request' => $request,'comercios' => $comercios,'countComer'=>$countComer]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($idPrincipal)
    {

        $accion = "ha accedido al módulo de Crear Comercio";
        $this->insertLog($accion);

        $comercios = comercios::select('comercios.*',
        DB::raw($idPrincipal . 'as retorno')
        )
        ->get();

        $bancos = bancos::all();
		
		$AfiliacionPP = str_pad(((int)comercios::select('codigo_afi_real')->orderBy('codigo_afi_real', 'desc')->first()->codigo_afi_real + 1), 14, "0", STR_PAD_LEFT);
		
        $lestados = estados::pluck('nombre', 'id');
        $lcategorias = comercios_categoria::orderBy('Nombre','asc')->pluck('Nombre', 'id');
        $lsubcategorias = comercios_subcategoria::where('id','00000000-0000-0000-0000-000000000000')->pluck('Nombre', 'id');
        $lcomercio_estatus = comercios_estatus::pluck('nombre', 'id');
        $lcomercio_motivo_estatus = comercios_motivo::pluck('nombre', 'id');

        return view('comercios.create')->with([
            'comercios'       => $comercios,
            'bancos'          => $bancos,
            'lestados'          => $lestados,
            'lcategorias'          => $lcategorias,
            'lsubcategorias'          => $lsubcategorias,
            'lcomercio_estatus'          => $lcomercio_estatus,
            'lcomercio_motivo_estatus'          => $lcomercio_motivo_estatus,
            'ComercioPrincipal'          => $idPrincipal,
			'AfiliacionPP'          => $AfiliacionPP
        ]);
    }

    public function consultaSubcategoria($categoria){

        $lsubcategorias = DB::table("comercios_subcategoria")->select('id','Nombre')->where("fk_id_categoria",$categoria)->get();

        //$lsubcategorias = comercios_subcategoria::select('id','Nombre')->where('fk_id_categoria',$categoria);
        //$lsubcategorias = comercios_subcategoria::where('fk_id_categoria',$categoria)->pluck('Nombre', 'id');

        if($lsubcategorias){
         	
            return response()->json($lsubcategorias,200);
        }else{

            return response()->json([
                'fallido'      => true,
            ],200);

        }

    }    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ComercioRequest $request)
    {
    
		//dd($request);
        $request->tasa_cobro_comer = str_replace(",", ".",$request->tasa_cobro_comer);

        if($request->tasa_cobro_comer_dolar){
            $tasa_dolar =str_replace(",", ".",$request->tasa_cobro_comer_dolar);
        }
        if($request->tasa_cobro_comer_euro){
            $tasa_euro = str_replace(",", ".",$request->tasa_cobro_comer_euro);
        }
		
        if($request->tasa_cobro_comer_stripe){
            $tasa_stripe = str_replace(",", ".",$request->tasa_cobro_comer_stripe);
        }		

        //dd($request->tasa_cobro_comer_dolar);
        		
        try{
            if(!filter_var($request->es_sucursal, FILTER_VALIDATE_BOOLEAN))
            {
				$existing_comer = comercios::select('*')
				->where('rif',$request->letrarif.$request->rif)
				->first();
            }

            if(isset($existing_comer)){
                flash('El RIF del Comercio ya se encuentra registrado en la plataforma', '¡Alert!')->error();
                return redirect()->back()->withInput();
            }else{
                //$comercios = new \App\Models\comercios($request->all());
         //dd($request->tasa_cobro_comer);
                $comercios = new \App\Models\comercios();
                $comercios->descripcion = $request->descripcion;
                $comercios->direccion = $request->direccion;
                $comercios->telefono1 = $request->telefono1; 
                $comercios->telefono2 = $request->telefono2; 
                $comercios->es_sucursal = $request->es_sucursal; 
                $comercios->fk_id_categoria = $request->fk_id_categoria; 
                $comercios->fk_id_subcategoria = $request->fk_id_subcategoria; 
                $comercios->calle_av = $request->calle_av; 
                $comercios->casa_edif_torre = $request->casa_edif_torre; 
                $comercios->local_oficina = $request->local_oficina; 
                $comercios->urb_sector = $request->urb_sector; 
                $comercios->ciudad = $request->ciudad; 
                $comercios->estado = $request->estado;
                $comercios->estatus = $request->estatus;
                $comercios->estatus_motivo = $request->estatus_motivo;
				$comercios->codigo_afi_real = $request->codigo_afi_real;
                if($request->estatus_motivo == 0)
                {
                    $comercios->estatus_motivo = null;
                }  

                if($request->es_sucursal == 'true')
                {
                    $comercios->nombre_sucursal = $request->nombre_sucursal; 
                }
                else{
                    $comercios->nombre_sucursal = null; 
                }                                
                $comercios->rif = $request->letrarif.$request->rif; 
                $comercios->email = $request->email; 
                $comercios->razon_social = $request->razon_social; 
                $comercios->codigo_afi_come = $request->codigo_afi_come; 
                if($request->propina_act == null){
                    $comercios->propina_act = false;
                }else{
                    $comercios->propina_act = $request->propina_act;
                }              
                
                if( $comercios->save()){

                    $banc_comer = new \App\Models\banc_comer();
                    //$idComer=$comercios::select('id')->where("rif","=",$comercios->rif)->first();

                    $banc_comer->fk_id_banco = $request->banco;
                    $banc_comer->fk_id_comer = $comercios->id;
                    $banc_comer->tasa_cobro_banco = 0;
                    $banc_comer->tasa_cobro_comer = $request->tasa_cobro_comer;
                    $banc_comer->num_cta_princ = $request->num_cta_princ;
                    $banc_comer->num_cta_secu = $request->num_cta_secu;
                    $banc_comer->num_cta_princ_dolar = $request->num_cta_princ_dolar;
                    $banc_comer->num_cta_secu_dolar = $request->num_cta_secu_dolar;
                    $banc_comer->num_cta_princ_euro = $request->num_cta_princ_euro;
                    $banc_comer->num_cta_secu_euro = $request->num_cta_secu_euro;


                    if($request->status_stripe == null){
                        $banc_comer->status_stripe = 0;
                        $tasa_stripe = null;
                    }else{
                        $banc_comer->status_stripe = 1;
                    }    	                    

                    //comision de comercios separadas por coma(,)

                    if($request->tasa_cobro_comer_dolar !=null || $request->tasa_cobro_comer_dolar !="" ){
                        $banc_comer->tasa_cobro_comer_dolar = $tasa_dolar;
                    }

                     if($request->tasa_cobro_comer_euro !=null || $request->tasa_cobro_comer_euro !="" ){
                        $banc_comer->tasa_cobro_comer_euro = $tasa_euro;
                    }
					
                     if($request->tasa_cobro_comer_stripe !=null || $request->tasa_cobro_comer_stripe !="" ){
                        $banc_comer->tasa_cobro_comer_stripe = $tasa_stripe;
                    }					
                    
                    


                    $banc_comer->save();

                    $data= $comercios;

                    $accion = "ha insertado un registro en las tablas comercio, banc_comer, id del registro: ".$comercios->id;
                    $this->insertLog($accion);

                }
            }


        }catch(\Exception $e){
            DB::rollBack();
            flash('El comercio no se pudo registrar intente mas tarde '.$e, '¡Alert!')->error();
        }

        if($request->ComercioPrincipal != 0)
        {
            $ComPrincipal = comercios::withTrashed()->find($request->ComercioPrincipal);
            $ComPrincipal->update([
                'posee_sucursales' => true,
            ]);

            flash('La sucursal <strong>' . $comercios->descripcion . ' (' . $comercios->nombre_sucursal .') </strong> ha sido creada de forma exitosa. <a id="irsucursales" href="#sucursales"></a>', '¡Alert!')->success();
            return redirect()->route('comercios.edit',[$request->ComercioPrincipal, 0]);
        }
        else{
            if($request->irsucursales == "true")
            {
                flash('Comercio creado satisfactoriamente. Como seleccionaste crear sucursales para este comercio presiona el botón <strong>Nueva sucursal</strong> mas abajo y repite este proceso tantas veces sea necesario. <a id="irsucursales" href="#sucursales"></a>', '¡Alert!')->success();
                return redirect()->route('comercios.edit',[$comercios->id, 0]);
            }
            else{
                flash('Se ha realizado la operacion solicitada exitosamente', '¡Operación Exitosa!')->success();
        return redirect()->route('comercios.index');
    }
        }        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT)) {
            $comercio = comercios::select('comercios.deleted_at',
            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion"),
            //'comercios.descripcion',
            'comercios.razon_social',
            'comercios.rif',
            'comercios.direccion',
            'comercios.telefono1',
            'comercios.telefono2',
            'comercios.email',
            'comercios.propina_act',
            'comercios.fk_id_subcategoria',
            'comercios.fk_id_categoria',
            'comercios_estatus.nombre as estatus',
            'banc_comer.id as banc_comer_id',
            'banc_comer.*')
            ->withTrashed()
            ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
            ->join('comercios_estatus','comercios_estatus.id','comercios.estatus')
            ->where('comercios.id','=',$id)
            ->get();
        } else {
        $comercio = comercios::select('comercios.*','banc_comer.id as banc_comer_id','banc_comer.*')
            ->withTrashed()
        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
            ->orWhere('comercios.rif', '=', $id)
        ->get();
        }

        if(isset($comercio->first()->rif))
        {
            $accion = "ha accedido al módulo detalle del Comercio con rif: ".$comercio->first()->rif;
            $this->insertLog($accion);   
        }        
        
        return response()->json([
            'data'      => $comercio
        ],200);

    }
	
    public function canales($id,$retorno=null)
    {
		$comercio = comercios::select('comercios.id',DB::raw($retorno . 'as retorno'),'comercios.rif','comercios.codigo_afi_real','comercios.descripcion')->where('comercios.id','=',$id)->withTrashed()->first();
		
		$IdsCanales = array();				
		
		$lCanalesComer = canal_comer::select('canal_comer.*','canal.Nombre', 'canal.fisico')
		->join('canal','canal.id','canal_comer.fk_id_canal')
        ->where('canal_comer.fk_id_comer',$id)
        ->orderBy('canal_comer.id','asc')
		->get();
		
		foreach ($lCanalesComer as $key => $value) {
			array_push($IdsCanales, $value->fk_id_canal);
		}
		
		$lCanales = canal::where('Nombre','!=','Presidents Pay - P2C')->where('Nombre','!=','Banplus Pay - P2c')->whereNotIn('id',$IdsCanales)->pluck('Nombre', 'id');
		//dd($lCanalesComer);
        return view('comercios.canales')->with([
            'lCanales'			=> $lCanales,
			'comercio'			=> $comercio,
			'lCanalesComer'		=> $lCanalesComer,
        ]);		
    }
	
	public function ActualizarCanalTerminales(Request $request)
	{
        //dd($request);

        $ComercioCanal = canal_comer::find($request->idComercioCanal);

        if($request->num_terminales > $ComercioCanal->num_terminales)
        {
            $t = $ComercioCanal->num_terminales + 1;
            for($i = $t;$i <= $request->num_terminales;$i++)
            {
                //GENERAR CODIGO DEL TERMINAL
				$terminal_random = mt_rand(1, 9);
                $CodigoTerminal = str_pad($i, 8 -  strlen ((string)$ComercioCanal->id.(string)$terminal_random), "0", STR_PAD_LEFT);
                $CodigoTerminal = (string)$ComercioCanal->id . (string)$terminal_random . $CodigoTerminal;
    
                $CanalTerminal = new \App\Models\terminal();
                $CanalTerminal->codigo_terminal_comercio = $CodigoTerminal;
                $CanalTerminal->serial = '';
                $CanalTerminal->fk_id_comer_canal = $ComercioCanal->id;
                $CanalTerminal->save();
            }

            $ComercioCanal->update([
                'num_terminales' => $request->num_terminales,
            ]);            
        }

        foreach ($request->terminales['idTerminal'] as $key => $carnet){

            $terminal = terminal::select('terminal.*')->where('codigo_terminal_comercio',$request->terminales['idTerminal'][$key])->first();

            
                //dd("diferente de null");
            $terminal->update([
                'serial' => $request->terminales['serial'][$key],
            ]);

            
            
        }
        
        return redirect()->route('comercios.canales', [$request->idComercio, $request->comPrincipal]);
    }
    
	public function AgregarCanalTerminales(Request $request)
	{
        //dd($request);
        $ComercioCanal = new \App\Models\canal_comer();
        $ComercioCanal->fk_id_comer = $request->idComercio;
        $ComercioCanal->num_terminales = $request->terminales;
        $ComercioCanal->fk_id_canal = $request->canales;

        if($ComercioCanal->save()){           
            for($i = 1;$i <= $request->terminales;$i++)
            {
                //GENERAR CODIGO DEL TERMINAL
				$terminal_random = mt_rand(1, 9);
                $CodigoTerminal = str_pad($i, 8 -  strlen ((string)$ComercioCanal->id.(string)$terminal_random), "0", STR_PAD_LEFT);
                $CodigoTerminal = (string)$ComercioCanal->id . (string)$terminal_random . $CodigoTerminal;
    
                $CanalTerminal = new \App\Models\terminal();
                $CanalTerminal->codigo_terminal_comercio = $CodigoTerminal;
                $CanalTerminal->serial = '';
                $CanalTerminal->fk_id_comer_canal = $ComercioCanal->id;
                $CanalTerminal->save();
            }
        }              

        return redirect()->route('comercios.canales', [$request->idComercio, $request->comPrincipal]);
    }
    
    public function desactivarTerminal($id,$comercio,$retorno)
    {
        $terminal = terminal::select('terminal.*')->where('codigo_terminal_comercio',$id)->first();
        $terminal->update([
            'status' => false,
        ]);    

        return redirect()->route('comercios.canales', [$comercio, $retorno]);
    }

    public function activarTerminal($id,$comercio,$retorno)
    {
        $terminal = terminal::select('terminal.*')->where('codigo_terminal_comercio',$id)->first();
        $terminal->update([
            'status' => true,
        ]);    

        return redirect()->route('comercios.canales', [$comercio, $retorno]);
    }      

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,$retorno=null)
    {

        $accion = "ha accedido al módulo edición del Comercio con rif: ".$id;
        $this->insertLog($accion);

        $comercio_select = comercios::select('comercios.id','comercios.rif')->where('comercios.id','=',$id)->withTrashed()->first();
        $banc_comer = banc_comer::select('fk_id_banco','tasa_cobro_banco','tasa_cobro_comer', 'tasa_cobro_comer_dolar', 'tasa_cobro_comer_euro')->where('fk_id_comer','=',$comercio_select->id)->first();    
        // $banco_select = bancos::select('id')->where('id','=',$banc_comer->fk_id_banco)->first();
        $bancos = bancos::select('id','descripcion')->first();
        $comercio = comercios::select(
            'comercios.*',
            DB::raw($retorno . 'as retorno'),
            'comercios.id as IdComercio',
            'banc_comer.*'
        )
        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
        ->where('comercios.id','=',$id)
        ->withTrashed()
        ->first();
        //dd($comercio);
        $tasa_cobro_comer = $banc_comer->tasa_cobro_comer * 1;
        $tasa_cobro_comer_dolar = $banc_comer->tasa_cobro_comer_dolar * 1;
        $tasa_cobro_comer_euro = $banc_comer->tasa_cobro_comer_euro * 1;
//dd($tasa_cobro_comer);
        //settype($tasa_cobro_comer, "integer");
       
        $accion = "ha accedido al módulo edición del Comercio con rif: ". $comercio_select->rif;
        $this->insertLog($accion);	
        
        $lestados = estados::pluck('nombre', 'id');        
        $lcomercio_estatus = comercios_estatus::pluck('nombre', 'id');
        $lcomercio_motivo_estatus = comercios_motivo::pluck('nombre', 'id');
        $lcategorias = comercios_categoria::orderBy('Nombre','asc')->pluck('Nombre', 'id');
        $lsubcategorias = comercios_subcategoria::where('fk_id_categoria',$comercio->fk_id_categoria)->pluck('Nombre', 'id');
        $sucursales = comercios::select('comercios.id as IdComer',
        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as fulldescripcion"),
        'comercios.rif',
        'comercios.descripcion',
        'comercios.razon_social',
        'comercios.deleted_at')
        ->where('comercios.rif','=',$comercio->rif)
        ->where('comercios.id','!=',$id)        
        ->withTrashed()
        ->get(); 
        
        $countSucursales = $sucursales->count();
       
        return view('comercios.edit')->with([
            'comercio'       => $comercio,
            'bancos'         => $bancos,
            'tasa_cobro_comer'    => $tasa_cobro_comer,
            'tasa_cobro_comer_dolar'    => $tasa_cobro_comer_dolar,
            'tasa_cobro_comer_euro'    => $tasa_cobro_comer_euro,
            'lestados'    => $lestados,
            'lcategorias'    => $lcategorias,
            'lsubcategorias'    => $lsubcategorias,
            'lcomercio_estatus'    => $lcomercio_estatus,
            'lcomercio_motivo_estatus'    => $lcomercio_motivo_estatus,
            'sucursales'    => $sucursales,
            'countSucursales'    => $countSucursales
            /*, 
            'banco_select'   => $banco_select->id*/

        ]);
       
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ComercioEditRequest $request, $id)
    {
        try{
			//dd($request);
            $ComercioActual = comercios::select('rif','es_sucursal','deleted_at')
                ->where('id','=',$id)
                ->withTrashed()
                ->get()
				->first();				
			
            
           $existing_comer = comercios::select('*')
            ->where('rif',$request->letrarif.$request->rif)
			->where('rif','<>',$ComercioActual->rif)
            ->first();

            if($existing_comer){
                flash('El RIF del Comercio ya se encuentra registrado en la plataforma', '¡Alert!')->error();
                return redirect()->back()->withInput();
            }               

            $comercio = comercios::select('comercios.*')
                ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                ->where('comercios.id','=',$id)
                ->withTrashed()
                ->get();
                
            $comercio[0]->descripcion = $request->descripcion;
            $comercio[0]->direccion = $request->direccion;
            $comercio[0]->telefono1 = $request->telefono1;
            $comercio[0]->telefono2 = $request->telefono2;
            $comercio[0]->rif = $request->letrarif.$request->rif;
            $comercio[0]->email = $request->email;			
            if($ComercioActual->es_sucursal)
            {
                $comercio[0]->nombre_sucursal = $request->nombre_sucursal; 
            }
            else{
                $comercio[0]->nombre_sucursal = null; 
            }
            $comercio[0]->fk_id_categoria = $request->fk_id_categoria; 
            $comercio[0]->fk_id_subcategoria = $request->fk_id_subcategoria; 
            $comercio[0]->calle_av = $request->calle_av; 
            $comercio[0]->casa_edif_torre = $request->casa_edif_torre; 
            $comercio[0]->local_oficina = $request->local_oficina; 
            $comercio[0]->urb_sector = $request->urb_sector; 
            $comercio[0]->ciudad = $request->ciudad; 
            $comercio[0]->estado = $request->estado;             
            $comercio[0]->estatus = $request->estatus;
            $comercio[0]->estatus_motivo = $request->estatus_motivo;
            if($request->estatus_motivo == 0)
            {
                $comercio[0]->estatus_motivo = null;
            }

            $comercio[0]->razon_social = $request->razon_social;
            $comercio[0]->codigo_afi_come = $request->codigo_afi_come;

            if($request->propina_act == null){
                   $comercio[0]->propina_act = 0;
            }else{
                   $comercio[0]->propina_act = 1;
            }

            //Validar estatus del comercio
            $Estatus_Comercio = DB::table("comercios_estatus")->select('nombre')->where("id",$comercio[0]->estatus)->get()->First();
            if($Estatus_Comercio->nombre != "Activo")
            {
                //Inactivar el Comercio                
                $comercio[0]->deleted_at = (String)Carbon::now();
                $Estatus_Comercio = (String)Carbon::now();
            }
            else{
                //Activar el comercio
                $comercio[0]->deleted_at = null;
                $Estatus_Comercio = null;
            }            

            if($comercio[0]->save()){

                //INACTIVAR USUARIOS DEL COMERCIO
                if($comercio[0]->deleted_at != null)
                {
                    $comerUsers = miem_come::where('fk_id_comercio', $comercio[0]->id)->get();
                    $comerUsers->each(function($miemUser){
                        User::where('id', $miemUser->fk_id_miembro )->delete();
                    });                            
                }
                
                $tasa_cobro_comer = str_replace(",", ".",  $request->tasa_cobro_comer);
                 if($request->tasa_cobro_comer_dolar){
                $tasa_dolar =str_replace(",", ".",$request->tasa_cobro_comer_dolar);
                }
                if($request->tasa_cobro_comer_euro){
                $tasa_euro = str_replace(",", ".",$request->tasa_cobro_comer_euro);
                }
				
                if($request->tasa_cobro_comer_stripe){
                $tasa_stripe = str_replace(",", ".",$request->tasa_cobro_comer_stripe);
                }				

                $id_banc_comer = banc_comer::select('id')->where('fk_id_comer',$comercio[0]->id)->where('fk_id_banco',$request->banco)->first();
				
				if($request->status_stripe == null){
					   $status_stripe = 0;
				}else{
					   $status_stripe = 1;
				}				
				
                DB::table('banc_comer')
                ->where('fk_id_comer',$comercio[0]->id)
                ->where('id',$id_banc_comer->id)
                ->update([
                    'fk_id_banco' => $request->banco,
                    'tasa_cobro_comer' => $tasa_cobro_comer,
                    'num_cta_princ' =>  $request->num_cta_princ,
                    'num_cta_secu'  =>  $request->num_cta_secu,
                    'num_cta_princ_dolar' =>  $request->num_cta_princ_dolar,
                    'num_cta_secu_dolar'  =>  $request->num_cta_secu_dolar,
                    'num_cta_princ_euro' =>  $request->num_cta_princ_euro,
                    'num_cta_secu_euro'  =>  $request->num_cta_secu_euro,
					'status_stripe'	 => $status_stripe
                ]);
                    if($request->tasa_cobro_comer_dolar !=null || $request->tasa_cobro_comer_dolar !="" ){
                        if(str_replace(",", ".",$request->tasa_cobro_comer_dolar) == 0){
                            $tasa_dolar= null;
                        }
                        $id_banc_comer = banc_comer::select('id')->where('fk_id_comer',$comercio[0]->id)->where('fk_id_banco',$request->banco)->first();
                        DB::table('banc_comer')
                        ->where('fk_id_comer',$comercio[0]->id)
                        ->where('id',$id_banc_comer->id)
                        ->update([
                        'tasa_cobro_comer_dolar' => $tasa_dolar,
                        ]);
                    }
                     if($request->tasa_cobro_comer_euro !=null || $request->tasa_cobro_comer_euro !="" ){
                        if(str_replace(",", ".",$request->tasa_cobro_comer_euro) == 0){
                            $tasa_euro= null;
                        }
						
						
                        $id_banc_comer = banc_comer::select('id')->where('fk_id_comer',$comercio[0]->id)->where('fk_id_banco',$request->banco)->first();

                        DB::table('banc_comer')
                        ->where('fk_id_comer',$comercio[0]->id)
                        ->where('id',$id_banc_comer->id)
                        ->update([
                        'tasa_cobro_comer_euro' => $tasa_euro,
                        ]);
                    }
                    if($request->tasa_cobro_comer_dolar ==null || $request->tasa_cobro_comer_dolar =="" ){
                    $id_banc_comer = banc_comer::select('id')->where('fk_id_comer',$comercio[0]->id)->where('fk_id_banco',$request->banco)->first();
                        DB::table('banc_comer')
                        ->where('fk_id_comer',$comercio[0]->id)
                        ->where('id',$id_banc_comer->id)
                        ->update([
                        'tasa_cobro_comer_dolar' => null,
                        ]);
                    }
                     if($request->tasa_cobro_comer_euro == null || $request->tasa_cobro_comer_euro =="" ){
                        $id_banc_comer = banc_comer::select('id')->where('fk_id_comer',$comercio[0]->id)->where('fk_id_banco',$request->banco)->first();
                        
                        DB::table('banc_comer')
                        ->where('fk_id_comer',$comercio[0]->id)
                        ->where('id',$id_banc_comer->id)
                        ->update([
                        'tasa_cobro_comer_euro' => null,
                        ]);
                    }
					
                     if($request->tasa_cobro_comer_stripe !=null || $request->tasa_cobro_comer_stripe !="" ){
                        if(str_replace(",", ".",$request->tasa_cobro_comer_stripe) == 0){
                            $tasa_stripe= null;
                        }
						
						
                        $id_banc_comer = banc_comer::select('id')->where('fk_id_comer',$comercio[0]->id)->where('fk_id_banco',$request->banco)->first();

                        DB::table('banc_comer')
                        ->where('fk_id_comer',$comercio[0]->id)
                        ->where('id',$id_banc_comer->id)
                        ->update([
                        'tasa_cobro_comer_stripe' => $tasa_stripe,
                        ]);
                    }					
					
                     if($request->tasa_cobro_comer_stripe == null || $request->tasa_cobro_comer_stripe =="" ){
                        $id_banc_comer = banc_comer::select('id')->where('fk_id_comer',$comercio[0]->id)->where('fk_id_banco',$request->banco)->first();
                        
                        DB::table('banc_comer')
                        ->where('fk_id_comer',$comercio[0]->id)
                        ->where('id',$id_banc_comer->id)
                        ->update([
                        'tasa_cobro_comer_stripe' => null,
                        ]);
                    }					


                //ACTUALIZAR LAS SUCURSALES SI EXISTEN
                $Comercios = comercios::select('id','deleted_at')
                ->where('id','!=',$id)
                ->withTrashed()
                ->where('rif','=',$ComercioActual->rif)
                ->get();

                //Actualizar datos comunes si es comercio principal
                if(!$ComercioActual->es_sucursal)
                {
                    foreach ($Comercios as $key => $value) {

                        $actualizar = comercios::withTrashed()->find($value->id)
                        ->update([
                                'rif' => $comercio[0]->rif,
                                'descripcion'    => $comercio[0]->descripcion,
                                'razon_social' => $comercio[0]->razon_social,
                                'updated_at' => (String)Carbon::now(),
                                'estatus' => $comercio[0]->estatus,
                                'estatus_motivo' => $comercio[0]->estatus_motivo
                        ]);

                        if($comercio[0]->deleted_at != null)
                        {
                            $comercioToDelete = comercios::withTrashed()
                            ->where('id', $value->id)
                            ->first();

                            $comerUsers = miem_come::where('fk_id_comercio', $value->id)->get();
                            $comerUsers->each(function($miemUser){
                                User::where('id', $miemUser->fk_id_miembro )->delete();
                            });                            

                            $comercioToDelete->delete();
                        }
                        else
                        {
                            if($value->deleted_at != null)
                            {
                                //COMENTADO HASTA NO VERIFICAR
                                // $comercioToDelete = comercios::withTrashed()
                                // ->where('id', $value->id)
                                // ->first();
                                
                                // $comercioToDelete->deleted_at = null;
                                // $comercioToDelete->save();
                                
                            }
                        }
                    }
                }               

                $accion = "ha modificado en el módulo edición del Comercio el registro con id: ".$comercio[0]->rif;
                $this->insertLog($accion);

            }else{
                flash('El comercio no se pudo actualizar intente mas tarde', '¡Alert!')->error();
            }

        }catch(\Exception $e){
            DB::rollBack();
            flash('El rif ya esta registrado con otro comercio en la plataforma, intente con otro rif '.$e, '¡Alert!')->error();
        }

        if($request->retorno != 0)
        {
            $ComPrincipal = comercios::withTrashed()->find($request->retorno);
            $ComPrincipal->update([
                'posee_sucursales' => true,
            ]);

            flash('La sucursal <strong>' . $comercio[0]->descripcion . ' (' . $comercio[0]->nombre_sucursal .') </strong> ha sido actualizada de forma exitosa. <a id="irsucursales" href="#sucursales"></a>', '¡Alert!')->success();
            return redirect()->route('comercios.edit',[$request->retorno, 0]);
        }
        else{
            flash('El comercio ha sido actualizado de forma exitosa', '¡Alert!')->success();
        return redirect()->route('comercios.index');
    }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reports(){

        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id; 
        }        

        if($rol== 3){
            $miem_come = miem_come::select("fk_id_comercio")->where("fk_id_miembro","=",Auth::user()->id)->get();
            $comercios = comercios::select("id","descripcion")->where('status','!=',4)->where("id","=",$miem_come[0]->fk_id_comercio)->get();
            $bancos = bancos::all();
            return view('comercios.reports')
            ->with(['bancos' => $bancos,'comercios'=>$comercios,'selectComer'=>$miem_come[0]->fk_id_comercio]);

        }else if($rol== 2){
            $miem_ban = miem_ban::select("fk_id_banco")->where("fk_dni_miembro","=",Auth::user()->id)->get();
            $bancos = bancos::select("id","descripcion")->where('status','!=',4)->where("id","=",$miem_ban[0]->fk_id_banco)->get();
            $comercios = comercios::all();

            return view('comercios.reports')
            ->with(['bancos' => $bancos,'selectBanco'=>$miem_ban[0]->fk_id_banco,'comercios'=>$comercios]);

        }else if($rol== 1){
            $bancos = bancos::all();
            $comercios = comercios::all();

            return view('comercios.reports')
            ->with(['bancos' => $bancos,'comercios'=>$comercios]);
        }
        
        

    }
    public function export_comercio(Request $request){


           if( $request->fecha_desde == null ){
                    $time_desde= date('Y-m-d');
                 
           }else{
            
                    $fecha_desde = explode("/",$request->fecha_desde);
                    $fecha_dia = $fecha_desde[0];
                    $fecha_mes = $fecha_desde[1];
                    $fecha_anio = $fecha_desde[2];
                    $fecha_desde = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                    $fecha_desde = strtotime($fecha_desde);
                    $time_desde = date('Y-m-d H:m:s',$fecha_desde);
           }


           if( $request->fecha_hasta == null ){
                    $time_hasta = date('Y-m-d');
           }else{
                    $fecha_hasta = explode("/",$request->fecha_hasta);
                    $fecha_dia = $fecha_hasta[0];
                    $fecha_mes = $fecha_hasta[1];
                    $fecha_anio = $fecha_hasta[2];
                    $fecha_hasta = $fecha_anio."-".$fecha_mes."-".$fecha_dia;

                    $fecha_hasta = strtotime($fecha_hasta);
                    $time_hasta = date('Y-m-d H:m:s',$fecha_hasta);
           }

        try{

                        $query = trans_head::select(
                                    'trans_head.id as ID',
                                    'trans_head.id as REFERENCIA',
                                    'trans_head.created_at as FECHA',
                                    'trans_head.monto as BRUTO',
                                    'trans_head.status AS ESTADO',
                                    'users.dni as CEDULA',
                                    'users.first_name as NOMBRE',
                                    'users.last_name as APELLIDO',
                                    'carnet.carnet as CARNET',
                                    'comercios.razon_social as NOMBRE_COMERCIO',
                                    'comercios.rif as RIF',
                                    'banc_comer.tasa_cobro_comer as PORCCOMISION',
                                    'trans_head.comision as COMISION',
                                    'trans_head.neto as NETO')
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        //->join('banc_comer','banc_comer.fk_id_banco','bancos.id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->where('status','!=',4);
                       /*$query = trans_head::select(
                                    'trans_head.id as ID',
                                    'trans_head.id as REFERENCIA',
                                    'trans_head.created_at as FECHA',
                                    'trans_head.monto as BRUTO',
                                    'trans_head.reverso',
                                    'trans_head.status AS ESTADO',
                                    'trans_head.token_status',
                                    'trans_head.token_time',
                                    'trans_head.cancela_a',
                                    'trans_head.created_at',
                                    'users.dni as CEDULA',
                                    'users.first_name as NOMBRE',
                                    'users.last_name as APELLIDO',
                                    'users.email',
                                    'users.rif as rif_miembro',
                                    'bancos.descripcion as nombre_banco',
                                    'bancos.telefono1 as telefono1_banco',
                                    'bancos.telefono2 as telefono2_banco',
                                    'bancos.telefono2 as rif_banco',
                                    'carnet.carnet as CARNET',
                                    'comercios.razon_social as NOMBRE_COMERCIO',
                                    'comercios.rif as RIF',
                                    'banc_comer.tasa_cobro_comer as PORCCOMISION',
                                    'trans_head.comision as COMISION',
                                    'trans_head.neto as NETO')

                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('banc_comer','banc_comer.fk_id_banco','bancos.id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));*/

                /*if($request->dni){
                    $query->where('users.dni','=',$request->dni);
                }
       
                if($request->carnet){
                    $query->Where('carnet.carnet','=',$request->carnet);
                }
                    
                if($request->bancos){
                    $query->Where('trans_head.fk_id_banco','=',$request->bancos);
                }

                if($request->comercios){
                    $query->Where('trans_head.fk_id_comer','=',$request->comercios);
                }*/

                $transacciones = $query->get();
                foreach ($transacciones as $key => $value) {
                    if($value->ESTADO == 0){
                            $transacciones[$key]->ESTADO = "Aprobada";
                    }else if($value[0]->ESTADO == 1){
                            $transacciones[$key]->ESTADO = "Por Autorizar";
                    }else if($value[0]->ESTADO == 2){
                            $transacciones[$key]->ESTADO = "Cancelada";
                    }else if($value[0]->ESTADO == 3){
                            $transacciones[$key]->ESTADO = "Rechazada";
                    }else if($value[0]->ESTADO == 4){
                            $transacciones[$key]->ESTADO = "Reverso";
                    }
                }
                
                Excel::create('Reporte de comercios',function($excel) use($transacciones){
                    $excel->sheet('Operaciones', function($sheet) use($transacciones) {
                        $sheet->setOrientation('lanscape');
                        $sheet->fromArray($transacciones);
                    });
                })->export('xls');
            
        }catch(\Exception $e){
            flash(' '.$e, '¡Alert!')->error();
        }

    }
    public function report_tl_comercios(){    

        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id; 
        }        

        $time_desde = date('d-m-Y',strtotime(date('d-m-Y')));        
        $time_desde = strtotime($time_desde);
        $time_desde = date('Y-m-d 00:00:00',$time_desde);

        $time_hasta = date('d-m-Y',strtotime(date('d-m-Y')));
        $time_hasta = strtotime($time_hasta);
        $time_hasta = date('Y-m-d 23:59:59',$time_hasta);
        
        if($rol == 2 || $rol == 4 || $rol == 6){
            $miem_ban = miem_ban::select("fk_id_banco")->where("fk_dni_miembro","=",Auth::user()->id)->get();
            $bancos = bancos::select("id","descripcion")->where("id","=",$miem_ban[0]->fk_id_banco)->get();
            //$comercios = comercios::all();
            $comercios = comercios::select('*')->where('razon_social','!=','jackpotImportPagos')->get();
            $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();
            /*$trans_Comer = trans_head::select(
                                    'comercios.rif as RIF',
                                    'comercios.razon_social as NOMBRE_COMERCIO',
                                    'banc_comer.num_cta_princ as CUENTA_PRINCIPAL', 
                                    DB::raw('sum(trans_head.monto) as CONSUMO_CLIENTE'),                                                    
                                    DB::raw('sum(trans_head.propina) as PROPINA'),
                                    DB::raw('sum(trans_head.comision) as TOTAL_ABONO_COMERCIO'),
                                    'banc_comer.tasa_cobro_comer as tasa_afiliacion', 
                                    DB::raw('sum(trans_head.monto + trans_head.propina) as TOTAL_CONSUMOS')
                                    )
                        ->join('users','users.id','trans_head.fk_dni_miembros')                        
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->where("carnet.fk_id_banco",$banco->fk_id_banco)                        
                        ->where('trans_head.procesado',null)
                        ->where('status',0)
                        ->where('reverso',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->groupBy('bancos.descripcion','comercios.rif','comercios.razon_social','banc_comer.num_cta_princ','banc_comer.tasa_cobro_comer')
                        ->get();*/

                        $trans_Comer = trans_head::select(
                            'comercios.rif as rif',
                            DB::raw("CASE 
                                        WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.num_cta_princ_dolar
                                        WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.num_cta_princ_euro
                                        ElSE
                                        banc_comer.num_cta_princ
                                    END as num_cuenta"),                        
                            //'banc_comer.num_cta_princ as num_cuenta',
                            'banc_comer.num_cta_secu as num_cuenta_secu',
                            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                            //'comercios.razon_social as nombre_comercio',
                            DB::raw('SUM(trans_head.monto) as venta_bruta'),
                            DB::raw('SUM(trans_head.propina) as propina'),
                            /*DB::raw("
                                            SUM(trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.propina * (banc_comer.tasa_cobro_comer/100))) as comision_afiliado"),*/
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100))
                            WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100))
                            ElSE
                            SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100))
                            END as comision_afiliado_vb"),
                            // DB::raw("
                            //     SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100)) as comision_afiliado_vb
                            // "),
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_dolar / 100))
                            WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_euro / 100))
                            ElSE
                            SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100))
                            END as comision_afiliado_prop"),                        
    /*                         DB::raw("
                                SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100)) as comision_afiliado_prop
                            "), */
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                            WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                            ElSE
                            banc_comer.tasa_cobro_comer
                            END as tasa_afiliacion"),
                            //'banc_comer.tasa_cobro_comer as  tasa_afiliacion',
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100))
                            WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100))
                            ElSE
                            SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100))
                            END as abono_al_comercio"),                        
                            /* DB::raw("
                                      SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)) 
                                             as abono_al_comercio"), */
                            /*DB::raw("
                                            SUM(trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as abono_al_comercio"),*/
                            /*DB::raw("
                                            SUM(trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as venta_neta"),*/
                            'trans_head.status AS estado',
                            'trans_head.procesado as descargado',
                            "monedas.mon_nombre",
                            DB::raw("generate_series( 1, 2 ) as v") 
                        )
    
                        ->join('users','users.id','trans_head.fk_dni_miembros')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->join("monedas","trans_head.fk_monedas","monedas.mon_id")
                        ->where('bancos.id',$banco->fk_id_banco)
                        //->where('trans_head.procesado',null)
                        ->where('trans_head.status',0)
                        ->where('monedas.mon_id',2)
                        ->whereNotIn('trans_head.status',[5])
                        ->where('trans_head.reverso',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->groupBy(
                            'comercios.rif',
                            'banc_comer.num_cta_princ',
                            'banc_comer.num_cta_secu',
                            'banc_comer.tasa_cobro_comer',
                            'comercios.razon_social',
                            'trans_head.status',
                            'trans_head.procesado',
                            "comercios.nombre_sucursal",
                            "monedas.mon_id",
                            "num_cta_princ_dolar",
                            "num_cta_princ_euro",
                            "tasa_cobro_comer_dolar",
                            "tasa_cobro_comer_euro"
                        )
                        ->orderBy('comercios.rif','ASC')        
                        ->get();


                    foreach ($trans_Comer as $key => $value) {
                        $procesado = trans_head::select(
                            'trans_head.procesado',
                            'trans_head.status'
                        )
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->where('comercios.rif',$value->rif)
                        ->first();
                        

                        if($procesado->status == 2){
                            $trans_Comer[$key]->estado = "Cancelada";
                        }else if($procesado->status == 0){
                            $trans_Comer[$key]->estado = "Aprobada";
                        }else if($procesado->status == 1){
                            $trans_Comer[$key]->estado = "Por Aprobar";
                        }else if($procesado->status == 3){
                            $trans_Comer[$key]->estado = "Rechazada";
                        }else if($procesado->status == 4){
                            $trans_Comer[$key]->estado = "Reversada";
                        }

                        /*if($value->v == 1){
                            $trans_Comer[$key]->num_cuenta = $value->num_cuenta;
                        }else{
                            $trans_Comer[$key]->num_cuenta = $value->num_cuenta_secu;
                        }*/


                        $trans_Comer[$key]->descargado = $procesado->procesado;

                        $trans_Comer[$key]->comision_afiliado = number_format($trans_Comer[$key]->comision_afiliado, 2, '.', '');
                        $trans_Comer[$key]->abono_al_comercio = number_format($trans_Comer[$key]->abono_al_comercio, 2, '.', '');

                     }

                 
            return view('comercios.report_tl_comercios')
            ->with(['bancos' => $bancos,'selectBanco'=>$miem_ban[0]->fk_id_banco,'comercios'=>$comercios,'transComer'=>$trans_Comer,'transComerCount'=>count($trans_Comer)]);

        }
        
        

    }
    public function report_tl_comercios2(Request $request){

        $time_desde=$request->fecha_desde;        
        $time_desde = explode("/",$time_desde);        
        $fecha_dia = $time_desde[0];
        $fecha_mes = $time_desde[1];
        $fecha_anio = $time_desde[2];
        $time_desde = $fecha_anio."-".$fecha_mes."-".$fecha_dia;
        $time_desde = strtotime($time_desde);
        $time_desde = date('Y-m-d 00:00:00',$time_desde);

        $time_hasta=$request->fecha_hasta;        
        $time_hasta = explode("/",$time_hasta);        
        $fecha_dia = $time_hasta[0];
        $fecha_mes = $time_hasta[1];
        $fecha_anio = $time_hasta[2];
        $time_hasta = $fecha_anio."-".$fecha_mes."-".$fecha_dia;
        $time_hasta = strtotime($time_hasta);
        $time_hasta = date('Y-m-d 23:59:59',$time_hasta);

        
        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id; 
        }        

        if($rol == 2 || $rol == 4 || $rol == 6){
            $miem_ban = miem_ban::select("fk_id_banco")->where("fk_dni_miembro","=",Auth::user()->id)->get();
            $bancos = bancos::select("id","descripcion")->where("id","=",$miem_ban[0]->fk_id_banco)->get();
            //$comercios = comercios::all();
            $comercios = comercios::select('*')->where('razon_social','!=','jackpotImportPagos')->get();
            $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();
            /*$trans_Comer = trans_head::select(
                                    'comercios.rif as RIF',
                                    'comercios.razon_social as NOMBRE_COMERCIO',
                                    'banc_comer.num_cta_princ as CUENTA_PRINCIPAL', 
                                    DB::raw('sum(trans_head.monto) as CONSUMO_CLIENTE'),                                                    
                                    DB::raw('sum(trans_head.propina) as PROPINA'),
                                    DB::raw('sum(trans_head.comision) as TOTAL_ABONO_COMERCIO'),
                                    'banc_comer.tasa_cobro_comer as tasa_afiliacion', 
                                    DB::raw('sum(trans_head.neto) as TOTAL_CONSUMOS')
                                    )
                        ->join('users','users.id','trans_head.fk_dni_miembros')                        
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->where("carnet.fk_id_banco",$banco->fk_id_banco)
                        ->where('status',0)
                        ->where('reverso',null)                        
                        //->where('trans_head.procesado',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->groupBy('bancos.descripcion','comercios.rif','comercios.razon_social','banc_comer.num_cta_princ','banc_comer.tasa_cobro_comer')
                        ->get();
                //dd($trans_Comer);*/

            $trans_Comer = trans_head::select(
                        'comercios.rif as rif',
                        DB::raw("CASE 
                                    WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.num_cta_princ_dolar
                                    WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.num_cta_princ_euro
                                    ElSE
                                    banc_comer.num_cta_princ
                                END as num_cuenta"),                        
                        //'banc_comer.num_cta_princ as num_cuenta',
                        'banc_comer.num_cta_secu as num_cuenta_secu',
                        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                        //'comercios.razon_social as nombre_comercio',
                        DB::raw('SUM(trans_head.monto) as venta_bruta'),
                        DB::raw('SUM(trans_head.propina) as propina'),
                        /*DB::raw("
                                        SUM(trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.propina * (banc_comer.tasa_cobro_comer/100))) as comision_afiliado"),*/
                        DB::raw("CASE 
                        WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100))
                        WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100))
                        ElSE
                        SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100))
                        END as comision_afiliado_vb"),
                        // DB::raw("
                        //     SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100)) as comision_afiliado_vb
                        // "),
                        DB::raw("CASE 
                        WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_dolar / 100))
                        WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_euro / 100))
                        ElSE
                        SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100))
                        END as comision_afiliado_prop"),                        
/*                         DB::raw("
                            SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100)) as comision_afiliado_prop
                        "), */
                        DB::raw("CASE 
                        WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                        WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                        ElSE
                        banc_comer.tasa_cobro_comer
                        END as tasa_afiliacion"),
                        //'banc_comer.tasa_cobro_comer as  tasa_afiliacion',
                        DB::raw("CASE 
                        WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100))
                        WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100))
                        ElSE
                        SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100))
                        END as abono_al_comercio"),                        
                        /* DB::raw("
                                  SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)) 
                                         as abono_al_comercio"), */
                        /*DB::raw("
                                        SUM(trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as abono_al_comercio"),*/
                        /*DB::raw("
                                        SUM(trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as venta_neta"),*/
                        'trans_head.status AS estado',
                        'trans_head.procesado as descargado',
						"monedas.mon_nombre",
                        DB::raw("generate_series( 1, 2 ) as v") 
                    )

                    ->join('users','users.id','trans_head.fk_dni_miembros')
                    ->join('comercios','comercios.id','trans_head.fk_id_comer')
                    ->join('bancos','bancos.id','trans_head.fk_id_banco')
                    ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                    ->join("monedas","trans_head.fk_monedas","monedas.mon_id")
                    ->where('bancos.id',$banco->fk_id_banco)
                    //->where('trans_head.procesado',null)
                    ->where('trans_head.status',0)
                    ->whereNotIn('trans_head.status',[5])
                    ->where('trans_head.reverso',null)
                    ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                    ->groupBy(
                        'comercios.rif',
                        'banc_comer.num_cta_princ',
                        'banc_comer.num_cta_secu',
                        'banc_comer.tasa_cobro_comer',
                        'comercios.razon_social',
                        'trans_head.status',
                        'trans_head.procesado',
                        "comercios.nombre_sucursal",
                        "monedas.mon_id",
                        "num_cta_princ_dolar",
                        "num_cta_princ_euro",
                        "tasa_cobro_comer_dolar",
                        "tasa_cobro_comer_euro"
                    )
                    ->orderBy('comercios.rif','ASC');

                    /*if($request->estado!="1000"){
                        $trans_Comer = $trans_Comer->where("trans_head.status",$request->estado);
                    }*/

                    if($request->rif != "0"){
                        $trans_Comer = $trans_Comer->where("comercios.rif",'ilike',"%".$request->rif."%");
                    }

                    if($request->nombreComercio != "-"){
                        $trans_Comer = $trans_Comer->where("comercios.razon_social",'ilike',"%".$request->nombreComercio."%");
                    }

                    if($request->mon_nombre){
                            $trans_Comer = $trans_Comer->where("trans_head.fk_monedas",$request->mon_nombre);
                        }

                    $trans_Comer = $trans_Comer->get();

                    foreach ($trans_Comer as $key => $value) {
                        $procesado = trans_head::select(
                            'trans_head.procesado',
                            'trans_head.status'
                        )
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->where('comercios.rif',$value->rif)
                        ->first();
                        

                        if($procesado->status == 2){
                            $trans_Comer[$key]->estado = "Cancelada";
                        }else if($procesado->status == 0){
                            $trans_Comer[$key]->estado = "Aprobada";
                        }else if($procesado->status == 1){
                            $trans_Comer[$key]->estado = "Por Aprobar";
                        }else if($procesado->status == 3){
                            $trans_Comer[$key]->estado = "Rechazada";
                        }else if($procesado->status == 4){
                            $trans_Comer[$key]->estado = "Reversada";
                        }

                        /*if($value->v == 1){
                            $trans_Comer[$key]->num_cuenta = $value->num_cuenta;
                        }else{
                            $trans_Comer[$key]->num_cuenta = $value->num_cuenta_secu;
                        }*/


                        //$trans_Comer[$key]->descargado = $procesado->procesado;

                        /*$trans_Comer[$key]->comision_afiliado_vb = number_format($trans_Comer[$key]->comision_afiliado_vb, 3, '.', '');
                        $trans_Comer[$key]->comision_afiliado_prop = number_format($trans_Comer[$key]->comision_afiliado_prop, 3, '.', '');
                        $trans_Comer[$key]->abono_al_comercio = number_format($trans_Comer[$key]->abono_al_comercio, 3, '.', '');*/

                     }

                 
            return view('comercios.report_tl_comercios')
            ->with(['bancos' => $bancos,'selectBanco'=>$miem_ban[0]->fk_id_banco,'comercios'=>$comercios,'transComer'=>$trans_Comer,'transComerCount'=>count($trans_Comer),'rif'=>$request->rif]);

        }
        
        

    }

    /*
    METODO QUE YA NO SE UTILIZA PERO SE DEJA EN LA CLASE HASTA QUE SE HAGA EL MERGE CON MASTER
    */
    public function export_report_tl_comercios($fecha_desde,$fecha_hasta,$estado,$rif,$nombreComercio, $moneda){
            
            /*se toma el rol y el usuario*/
            $user= User::find(Auth::user()->id);            
            $roles= $user->roles;
            $rol = null;
            $AuxFechaDesde = $fecha_desde;
            $AuxFechaHasta = $fecha_hasta;
            foreach ($roles as $value) {
                $rol = $value->id; 
            }              
            /*se toma la ip del cliente para registrar en base de datos*/
            $clientIP = \Request::ip();

           if( $fecha_desde == null ){
                    $time_desde= date('Y-m-d');
                 
           }else{
                    $fecha_desde = strtotime($fecha_desde);
                    $time_desde = date('Y-m-d 00:00:00',$fecha_desde);                    
           }


           if( $fecha_hasta == null ){
                    $time_hasta = date('Y-m-d');
           }else{
                    $fecha_hasta = strtotime($fecha_hasta);
                    $time_hasta = date('Y-m-d 23:59:59',$fecha_hasta);
           }
        try{

            if($rol == 2 || $rol == 4 || $rol == 6){
                        
                        $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();
                        
                        $caracas = DB::table('trans_head')->whereBetween('trans_head.created_at',array($time_desde,$time_hasta));
                        /*$query = trans_head::select(
                                    'comercios.rif as RIF',
                                    'comercios.razon_social as NOMBRE_COMERCIO',
                                    'banc_comer.num_cta_princ as CUENTA_PRINCIPAL', 
                                    DB::raw('sum(trans_head.monto) as CONSUMO_CLIENTE'),                                                    
                                    DB::raw('sum(trans_head.propina) as PROPINA'),
                                    DB::raw('sum(trans_head.comision) as TOTAL_ABONO_COMERCIO'),
                                    'banc_comer.tasa_cobro_comer as TASA_AFILIACION', 
                                    DB::raw('sum(trans_head.monto + trans_head.propina) as TOTAL_CONSUMOS')
                                    )
                        ->join('users','users.id','trans_head.fk_dni_miembros')                        
                        ->join('bancos','bancos.id','trans_head.fk_id_banco')
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                        ->where("carnet.fk_id_banco",$banco->fk_id_banco)   
                        ->where('status',0)
                        ->where('reverso',null)                     
                        //->where('trans_head.procesado',null)
                        //->where('trans_head.created_at', '>=', '$time_desde')
                        //->where('trans_head.created_at', '>=', '$time_hasta')
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->groupBy('bancos.descripcion','comercios.rif','comercios.razon_social','banc_comer.num_cta_princ','banc_comer.tasa_cobro_comer');

                        $transacciones = $query->get();
                        
                        foreach ($transacciones as $key => $value) {
                            
                        if($value->CUENTA_PRINCIPAL !== 0){
                            $rest = substr($value->CUENTA_PRINCIPAL , -10);
                                    $transacciones[$key]->CUENTA_PRINCIPAL = $rest;
                            }
                        } */


                        $transacciones = trans_head::select(                        
                            'comercios.rif as rif',
                            DB::raw("CASE 
                                        WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.num_cta_princ_dolar
                                        WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.num_cta_princ_euro
                                        ElSE
                                        banc_comer.num_cta_princ
                                    END as num_cuenta"),                        
                            //'banc_comer.num_cta_princ as num_cuenta',
                            'banc_comer.num_cta_secu as num_cuenta_secu',
                            DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.razon_social ELSE comercios.razon_social || ' (' || comercios.nombre_sucursal || ')' END as nombre_comercio"),
                            //'comercios.razon_social as nombre_comercio',
                            DB::raw('SUM(trans_head.monto) as venta_bruta'),
                            DB::raw('SUM(trans_head.propina) as propina'),
                            /*DB::raw("
                                            SUM(trans_head.monto * (banc_comer.tasa_cobro_comer/100) + (trans_head.propina * (banc_comer.tasa_cobro_comer/100))) as comision_afiliado"),*/
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_dolar / 100))
                            WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.monto * (banc_comer.tasa_cobro_comer_euro / 100))
                            ElSE
                            SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100))
                            END as comision_afiliado_consumo"),
                            // DB::raw("
                            //     SUM(trans_head.monto * (banc_comer.tasa_cobro_comer / 100)) as comision_afiliado_vb
                            // "),
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_dolar / 100))
                            WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.propina * (banc_comer.tasa_cobro_comer_euro / 100))
                            ElSE
                            SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100))
                            END as comision_afiliado_propina"),                        
    /*                         DB::raw("
                                SUM(trans_head.propina * (banc_comer.tasa_cobro_comer / 100)) as comision_afiliado_prop
                            "), */
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN banc_comer.tasa_cobro_comer_dolar
                            WHEN monedas.mon_nombre = 'EURO' THEN banc_comer.tasa_cobro_comer_euro
                            ElSE
                            banc_comer.tasa_cobro_comer
                            END as tasa_afiliacion"),
                            //'banc_comer.tasa_cobro_comer as  tasa_afiliacion',
                            DB::raw("CASE 
                            WHEN monedas.mon_nombre = 'DOLAR' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_dolar/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_dolar/100))
                            WHEN monedas.mon_nombre = 'EURO' THEN SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer_euro/100) - trans_head.propina * (banc_comer.tasa_cobro_comer_euro/100))
                            ElSE
                            SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100))
                            END as abono_al_comercio"),                        
                            /* DB::raw("
                                      SUM(trans_head.monto + trans_head.propina - trans_head.monto * (banc_comer.tasa_cobro_comer/100) - trans_head.propina * (banc_comer.tasa_cobro_comer/100)) 
                                             as abono_al_comercio"), */
                            /*DB::raw("
                                            SUM(trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as abono_al_comercio"),*/
                            /*DB::raw("
                                            SUM(trans_head.monto - (trans_head.monto * (banc_comer.tasa_cobro_comer/100)) + (trans_head.monto_propina - (trans_head.monto_propina * (banc_comer.tasa_cobro_comer/100)))) as venta_neta"),*/
                            'trans_head.status AS estado',
                            'trans_head.procesado as descargado',
                            "monedas.mon_nombre as moneda",
                            DB::raw("generate_series( 1, 2 ) as v") 
                        )
                        
                    

                    ->join('users','users.id','trans_head.fk_dni_miembros')
                    ->join('comercios','comercios.id','trans_head.fk_id_comer')
                    ->join('bancos','bancos.id','trans_head.fk_id_banco')
                    ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                    ->join("monedas","trans_head.fk_monedas","monedas.mon_id")
                    ->where('bancos.id',$banco->fk_id_banco)
                    //->where('trans_head.procesado',null)
                    ->where('trans_head.status',0)
                    ->whereNotIn('trans_head.status',[5])
                    ->where('trans_head.reverso',null)
                    ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                    ->groupBy(
                        'comercios.rif',
                        'banc_comer.num_cta_princ',
                        'banc_comer.num_cta_secu',
                        'banc_comer.tasa_cobro_comer',
                        'comercios.razon_social',
                        'trans_head.status',
                        'trans_head.procesado',
                        "comercios.nombre_sucursal",
                        "monedas.mon_id",
                        "num_cta_princ_dolar",
                        "num_cta_princ_euro",
                        "tasa_cobro_comer_dolar",
                        "tasa_cobro_comer_euro"
                    )
                    ->orderBy('comercios.rif','ASC');
                    

                    /*if($estado!="1000"){
                        $trans_Comer = $trans_Comer->where("trans_head.status",$estado);
                    }*/

                    if($rif != "0"){
                        $transacciones = $transacciones->where("comercios.rif",'ilike',"%".$rif."%");
                    }

                    if($nombreComercio != "-"){
                        $transacciones = $transacciones->where("comercios.razon_social",'ilike',"%".$nombreComercio."%");
                    }

                    if($moneda){
                            $transacciones = $transacciones->where("trans_head.fk_monedas",$moneda);
                        }

                    $transacciones=$transacciones->get();

                    foreach ($transacciones as $key => $value) {
                        $procesado = trans_head::select(
                            'trans_head.procesado',
                            'trans_head.status'
                        )
                        ->join('comercios','comercios.id','trans_head.fk_id_comer')
                        ->where('comercios.rif',$value->rif)
                        ->first();
                        

                        /*if($procesado->status == 2){
                            $transacciones[$key]->estado = "Cancelada";
                        }else if($procesado->status == 0){
                            $transacciones[$key]->estado = "Aprobada";
                        }else if($procesado->status == 1){
                            $transacciones[$key]->estado = "Por Aprobar";
                        }else if($procesado->status == 3){
                            $transacciones[$key]->estado = "Rechazada";
                        }else if($procesado->status == 4){
                            $transacciones[$key]->estado = "Reversada";
                        }*/

                        if($value->v == 1){
                            $transacciones[$key]->num_cuenta = $value->num_cuenta;
                            
                        }else{
                            $transacciones[$key]->num_cuenta = $value->c;
                            
                        }


                        //$transacciones[$key]->procesado = $procesado->procesado;
                        $transacciones[$key]->venta_bruta = number_format($transacciones[$key]->venta_bruta, 2, ',', '.');
                        $transacciones[$key]->propina = number_format($transacciones[$key]->propina, 2, ',', '.');
                        $transacciones[$key]->tasa_afiliacion = number_format($transacciones[$key]->tasa_afiliacion, 2, ',', '.');
                        $transacciones[$key]->comision_afiliado_consumo = number_format($transacciones[$key]->comision_afiliado_consumo, 2, ',', '.');
                        $transacciones[$key]->comision_afiliado_propina = number_format($transacciones[$key]->comision_afiliado_propina, 2, ',', '.');
                        $transacciones[$key]->abono_al_comercio = number_format($transacciones[$key]->abono_al_comercio, 2, ',', '.');
                        $transacciones[$key]->moneda = $transacciones[$key]->moneda;

                     }

                     foreach ($transacciones as $key =>$v) {
                             
                                if( $key%2 == 0){
                                    $transacciones[$key]->propina = '';
                                    $transacciones[$key]->comision_afiliado_propina = '';
                                }else{
                                    $transacciones[$key]->venta_bruta = '';
                                    $transacciones[$key]->venta_neto = '';
                                    $transacciones[$key]->abono_al_comercio = '';
                                    $transacciones[$key]->comision_afiliado_consumo = '';
                                }

                                $transacciones[$key]->v = '';    
                                $transacciones[$key]->c = '';
                    }
                        

        }
 
                Excel::create('Reporte totalizado de comercios '.$AuxFechaDesde.' hasta '.$AuxFechaHasta,function($excel) use($transacciones){
                    $excel->sheet('Operaciones', function($sheet) use($transacciones) {
                        $sheet->setOrientation('lanscape');
                        $sheet->fromArray($transacciones);
                    });
                })->export('xls');
     }catch(\Exception $e){
            flash(' '.$e, '¡Alert!')->error();
        }

    }
    public function destroy($id)
    {
        //
    }
    public function restore($id,$retorno)
    {
        $comercio = comercios::withTrashed()
        ->where('id', $id)
        ->first();
        /*$comerUsers = miem_come::where('fk_id_comercio', $comercio->id)->get();
        $comerUsers->each(function($miemUser){
            User::where('id', $miemUser->fk_id_miembro )->withTrashed()->restore();
        });*/
        $comercio->restore();

        $Estatus_Comercio = DB::table("comercios_estatus")->select('id')->where("nombre","Activo")->get()->First();

        if($retorno != 0)
        {
            $comercioppal = comercios::withTrashed()
            ->where('id', $retorno)
            ->first();

            if($comercioppal->estatus != $Estatus_Comercio->id)
            {
                flash('La sucursal <strong>' . $comercio->descripcion . ' (' . $comercio->nombre_sucursal .') </strong> no se puede activar. El comercio principal no esta Activo. <a id="irsucursales" href="#sucursales"></a>', '¡Alert!')->error();
                return redirect()->route('comercios.edit', [$retorno, 0]);                
            }
        }        
        
        $comercio->estatus = $Estatus_Comercio->id;
        $comercio->estatus_motivo = null;
        $comercio->save();

        if($retorno != 0)
        {
            flash('La sucursal <strong>' . $comercio->descripcion . ' (' . $comercio->nombre_sucursal .') </strong> ha sido activada satisfactoriamente. <a id="irsucursales" href="#sucursales"></a>', '¡Alert!')->success();
            return redirect()->route('comercios.edit', [$retorno, 0]);
        }
        else{
            return redirect()->route('comercios.index') ->with('success','Comercio activado Satisfactoriamente');
        }                 
    }
    public function delete($id,$retorno)
    {
        
        $comerUsers = miem_come::where('fk_id_comercio', $id)->get();
        $comerUsers->each(function($miemUser){
            User::where('id', $miemUser->fk_id_miembro )->delete();
        });

        $comercio = comercios::withTrashed()
        ->where('id', $id)
        ->first();

        $Estatus_Comercio = DB::table("comercios_estatus")->select('id')->where("nombre","Inactivo")->get()->First();
        $comercio->estatus = $Estatus_Comercio->id;
        $comercio->estatus_motivo = null;
        $comercio->save();        
       
        $comercio->delete();

        if($retorno != 0)
        {
            flash('La sucursal <strong>' . $comercio->descripcion . ' (' . $comercio->nombre_sucursal .') </strong> ha sido desactivada satisfactoriamente. <a id="irsucursales" href="#sucursales"></a>', '¡Alert!')->success();
            return redirect()->route('comercios.edit', [$retorno, 0]);
        }
        else{
            return redirect()->route('comercios.index')->with('success','Comercio desactivado Satisfactoriamente');
        }        
    }

    public function Contratos()
    {
      //$contratos_comer = comercios::where('estado_afiliacion_comercio', '!=', null)->get();
	  $contratos_comer = comercios::select(DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as fulldescripcion"),'comercios.rif','comercios.estado_afiliacion_comercio','comercios.aceptacion_contrato')->where('estado_afiliacion_comercio', '!=', null)->withTrashed()->get();
        //dd($contratos_comer);
      $cantidad = count($contratos_comer);
      //dd($cantidad);
      return view('comercios.reporte_contrato')->with('contratos', $contratos_comer)->with('cantidad', $cantidad);
    }

    public function ValidarSerial($idComercioCanal)
    {        
        $split = explode("|", $idComercioCanal);
        if($split[1]==""){
            $split[1]=0;
        }
        $can = canal_comer::select('canal_comer.*')->where('fk_id_canal', $split[0])->where('fk_id_comer','!=', $split[2])->get();
         for($i=0; $i<count($can); $i++){
            $terminal = terminal::select('terminal.serial')->where('fk_id_comer_canal', $can[$i]->id)->where('serial', $split[1])->first(); 
            if($terminal){                
            return response()->json(true,200);
            }
        }//FINAL FOR         
        if(!$terminal){                
            return response()->json(false,200);
        }

    }//FINAL FUNCTION   


}
