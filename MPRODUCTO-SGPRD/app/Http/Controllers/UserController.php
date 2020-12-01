<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\bancos;
use App\Models\comercios;
use App\Models\carnet;
use App\Models\miem_come;
use App\Models\miem_ban;
use App\Models\banc_comer;
use App\Models\emisores;
use App\Models\Errors;
use App\Models\Ledge;
use App\Moneda;
use App\Http\Requests\UsersRequest;
use App\Http\Requests\UsersEditRequest;
use Carbon\Carbon;
use DB;
use Hash;
use Excel;
use Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Collection as Collection;

class UserController extends Controller
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
      public function index(Request $request)
    {

        foreach (Auth::user()->roles as $v){
            $rolUser = $v->id;
        }

        if($rolUser ==1 || $rolUser ==2){

            $data = User::with('roles')
                       ->whereHas('roles', function($q) {
                           $q->where('id', '!=', 5);
                       })
                       ->orderBy('id','DESC')->withTrashed()->take(100);
                       if ($request->nombre != '') {
                                $data = $data->where(DB::raw('LOWER(first_name)'), strtolower($request->nombre));
                                //dd($data);
                            }
                            if ($request->apellido != '') {
                                $data = $data->where(DB::raw('LOWER(last_name)'), strtolower($request->apellido));
                            }
                            if ($request->cedula != '') {
                                $data = $data->where('dni',$request->cedula);
                            }
                            if ($request->correo != '') {
                                $data = $data->where(DB::raw('LOWER(email)'), strtolower($request->correo));
                            }
                            if ($request->estatus != '') {
                                if ($request->estatus == 1) {
                                    $data = $data->where('deleted_at',null);
                                }else{
                                    $data = $data->where('deleted_at','!=',null);
                                }
                            }
            $data = $data->get();
            $countUser = $data->count();
        }else{


            $data = User::select("users.id","first_name","last_name","dni","email", "created_at","deleted_at")
                            
                            ->whereHas('roles', function($q) {
                                   $q->where('id', '=', 5);
                               })
                            ->withTrashed()->orderBy('users.created_at', 'desc')->take(100);
                            if ($request->nombre != '') {
                                $data = $data->where(DB::raw('LOWER(first_name)'), strtolower($request->nombre));
                                //dd($data);
                            }
                            if ($request->apellido != '') {
                                $data = $data->where(DB::raw('LOWER(last_name)'), strtolower($request->apellido));
                            }
                            if ($request->cedula != '') {
                                $data = $data->where('dni',$request->cedula);
                            }
                            if ($request->correo != '') {
                                $data = $data->where(DB::raw('LOWER(email)'), strtolower($request->correo));
                            }
                            if ($request->estatus != '') {
                                if ($request->estatus == 1) {
                                    $data = $data->where('deleted_at',null);
                                }else{
                                    $data = $data->where('deleted_at','!=',null);
                                }
                            }
                            
            $data = $data->get();
    //dd($data);
            //count users 
            $dataCount = User::select("users.id","first_name","last_name","dni","email",
			DB::raw("substring(carnet from 1 for 4) || ' XXXX XXXX ' || substring(carnet,length(carnet)-4,length(carnet)) as carnet"),
			"limite","users.created_at","deleted_at")
                            ->join("carnet","carnet.fk_id_miembro","users.id")
                            ->whereHas('roles', function($q) {
                                   $q->where('id', '=', 5);
                               })
                            ->withTrashed()->orderBy('users.created_at', 'desc')->get();
            $countUser = $dataCount->count();

        }
        return view('users.index',compact('data','countUser','rolUser','request'/*,'dataMiembro'*/))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        foreach (Auth::user()->roles as $v){
            $rolUser = $v->id;
            $rolName = $v->display_name;
        }

        if($rolUser ==1 || $rolUser ==2){
            $roles = Role::where('id','!=',5)
            ->pluck('display_name','id');
        }else{
            $roles = Role::where('id','=',5)
            ->pluck('display_name','id');
        }
		
        $bancos = bancos::select('bancos.*')->get();
        $emisor = emisores::select('cod_emisor', 'nombre')->get();



        $comercios = comercios::select('comercios.id',
        DB::raw("CASE WHEN comercios.nombre_sucursal IS NULL THEN comercios.descripcion ELSE comercios.descripcion || ' (' || comercios.nombre_sucursal || ')' END as descripcion")
        )->where('razon_social','!=','jackpotImportPagos')->get();
        $monedas = Moneda::where('mon_status','=','ACTIVO')->orderBy('mon_nombre', 'asc')->get();
        return view('users.create',compact('roles','bancos','comercios','rolUser', 'monedas', 'emisor'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UsersRequest $request)
    {  
//dd($request);
        foreach (Auth::user()->roles as $v){
            $rolUser = $v->id;
        }
        
        if($request->perfil == 5){   
        $carnets = new Collection();

        foreach ($request->carnets['fk_monedas'] as $key => $carnet){

                if ($carnet== "") {
                    return redirect('users/create')->with('status', 'NoMoneda');   
                }
                if ($request->carnets['limite'][$key] == "") {
                    return redirect('users/create')->with('status', 'NoLimite');
                }
                if ($request->carnets['carnet'][$key] == "") {
                    return redirect('users/create')->with('status', 'NoCarnet');
                }
                if ($request->carnets['carnet_real'][$key] == "") {
                    return redirect('users/create')->with('status', 'NoCarnet_real');
                }
               
                if ($request->carnets['tipoProducto'][$key] == "" || $request->carnets['tipoProducto'][$key] == null) {

                   return redirect()->route('users.create', [$id])
                            ->with('error',' El campo Tipo de Producto es requerido, por favor valide los datos ingresados');
                }
                if($request->carnets['tipoProducto'][$key] == "2" && $request->carnets['emisor'][$key] == null){

                    return redirect()->route('users.create', [$id])
                            ->with('error',' El campo Emisor es requerido para Tipo de Producto Externo, por favor valide los datos ingresados');
                }
                if($request->carnets['tipoProducto'][$key] == "2" && $request->carnets['codClienteEmisor'][$key] == null){
                    return redirect()->route('users.create', [$id])
                            ->with('error',' El campo Código Cliente Emisor es requerido para Tipo de Producto Externo, por favor valide los datos ingresados');
                }

            }//end foreach validacion de campos vacios
            foreach ($request->carnets['fk_monedas'] as $key => $carnet){
//dd($request->carnets);
                $carnets->push([
                    'fk_monedas' => $carnet,
                    'limite' => $request->carnets['limite'][$key],
                    'carnet' => $request->carnets['carnet'][$key],
                    'carnet_real' => $request->carnets['carnet_real'][$key],

                    'tipo_producto' => $request->carnets['tipoProducto'][$key],
                    'cod_emisor' => $request->carnets['emisor'][$key],
                    'cod_cliente_emisor' => $request->carnets['codClienteEmisor'][$key],
                ]);

            } 
        }

        $this->validate($request, [
            'nacionalidad' => 'required',
            'cod_tel' => 'required',
        ]);



        $input = $request->all();

        if($rolUser == 6){
            $perfil = 5;
        }else{
            $perfil = $input['perfil'];
        }
        /*
        dd($input);
        */

        //$limite = str_replace(".", "",$input['limite']);
        //$limite = str_replace(",", ".",$limite);

        if ($perfil == 5) {//Perfil Miembro
            $input['password'] = Hash::make('qwerty123456');
        }else{
            $input['password'] = Hash::make($input['password']);
        }
		
        $user = User::create([
                'nacionalidad'      => $input['nacionalidad'],
                'dni'               => $input['dni'],
                'first_name'        => $input['first_name'],
                'last_name'         => $input['last_name'],
                'email'             => $input['email'],
                'password'          => $input['password'],
                'birthdate'         => $input['birthdate'],
                'kind'              => 1,
                'cod_tel'           => $input['cod_tel'],
                'num_tel'           => $input['num_tel']
                ]);

        if($rolUser == 6){
            $user->attachRole(5);
        }else{
            foreach ($request->input('roles') as $key => $value) {
                $user->attachRole($value);
            }
        }


        if ($perfil == 2) { //Perfil Banco

            miem_ban::create([
                'fk_id_banco' => 1,//$input['mbanco'],
                'fk_dni_miembro' => $user->id,
                'credito_apro' => 0,
                'credito_disp' => 0,
                'fk_id_limite' => 3,
            ]);

            miem_come::create([
                'fk_id_comercio' => 3,
                'fk_id_miembro' => $user->id,
            ]);
        }

        if ($perfil == 3) { //Perfil Comercio
            miem_come::create([
                'fk_id_comercio' => $input['comercio'],
                'fk_id_miembro' => $user->id,
            ]);
        }

        if ($perfil == 4) { //Perfil Call center
             miem_ban::create([
                'fk_id_banco' => 1,//$input['mbanco'],
                'fk_dni_miembro' => $user->id,
                'credito_apro' => 0,
                'credito_disp' => 0,
                'fk_id_limite' => 3,
            ]);
        }

        if ($perfil == 5) {//Perfil Miembro

            foreach ($carnets as $key => $value) {
                
//dd($value);
                $limite = str_replace(".", "",$value['limite']);
                $limite = str_replace(",", ".",$limite);
                $tipoProd = $value['tipo_producto'];
                //dd($tipoProd);
                if($tipoProd=='1'){
                    $codEmi = "";
                    $codCliEmi = "";
                }else{
                    $codEmi = $value['cod_emisor'];
                    $codCliEmi = $value['cod_cliente_emisor'];
                }

                    carnet::create([
                        'carnet' => $value['carnet'],
                        'limite' => $limite,
            			'disponible' => $limite,
                        'fk_id_banco' => 1,//$input['banco'],
                        'fk_id_miembro' => $user->id,
                        'fk_monedas' =>$value['fk_monedas'],
                        'carnet_real' => $value['carnet_real'],

                        'tipo_producto' => $value['tipo_producto'],
                        'cod_emisor' => $codEmi,
                        'cod_cliente_emisor' => $codCliEmi,
                      ]);
                    }

        }

         if ($perfil == 6) { //Perfil Operacion
             miem_ban::create([
                'fk_id_banco' => 1,//$input['mbanco'],
                'fk_dni_miembro' => $user->id,
                'credito_apro' => 0,
                'credito_disp' => 0,
                'fk_id_limite' => 3,
            ]);
        };


        return redirect()->route('users.index')
                        ->with('success','Usuario creado Satisfactoriamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$user = User::find($id);
         foreach (Auth::user()->roles as $v){
            $rolUser = $v->id;
        }
        if($rolUser == 6){
            $user=User::select("users.id","users.nacionalidad","users.birthdate","first_name","last_name","dni","email","carnet","limite")
                            ->join("carnet","carnet.fk_id_miembro","users.id")
                            ->where('users.id','=',$id)
                            ->with('carnets')
                            ->withTrashed()
                            ->get();
            
        }else{
            $user = User::select('users.*')
            ->where('id','=',$id)
            ->with('carnets')
            ->withTrashed()
            ->get();
        }
        $user->each(function ($user) {
                if (count($user->carnets)) {
                    $user->carnets->each(function ($carnet){
                        $carnet->moneda = moneda::find($carnet->fk_monedas)->mon_nombre;

                    });
                }
        });

        return response()->json([
            'data'      => $user

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

        $user = User::find($id);
        foreach ($user->roles as $v) {
            $rolUser = $v->id;
        }
        if ($rolUser =='2' || $rolUser =='4' || $rolUser =='6') {
            $roles = Role::whereIn('id', [2,4,6])
            ->pluck('display_name','id');

            $userRole = $user->roles->pluck('id','id')->toArray();
            //dd($roles);
        }else{
            $roles = Role::pluck('display_name','id');

            $userRole = $user->roles->pluck('id','id')->toArray();
        }
		
        if ($rolUser == 5) {
            $monedas = Moneda::where('mon_status','=',strtoupper('activo'))->orderBy('mon_nombre', 'asc')->get();

             $emisor = emisores::select('cod_emisor', 'nombre')->get();

            

            $carnets = carnet::select('carnet.*', 'monedas.mon_status')
            
            ->join('monedas','monedas.mon_id','carnet.fk_monedas')
            ->where('fk_id_miembro',$user->id)
            ->where('monedas.mon_status', '=',strtoupper('activo'))
            ->get();  

         


            /*$carnets = carnet::where('fk_id_miembro',$user->id)
            ->where('fk_monedas',$monedas[0]->mon_id)
            ->get();*/

            //dd($monedas);
            $carnets->each(function($carnet){
                
                if($carnet->transar == false){
                    $carnet->transar = "0";

                }else{
                     $carnet->transar = "1";
                }
                $carnet->limite = str_replace(".", ",",$carnet->limite);

            });
            //dd($carnets);
            /*$limit = explode(".", $carnet -> limite);
            $limite = $limit[0];
            $decLimite = $limit[1];*/
        }else{
            $carnets=[];
        }
//dd($carnets);
        return view('users.edit',compact('user','roles','userRole','carnets','monedas',/*'estados', 'userEstado',*/'rolUser', 'emisor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UsersEditRequest $request, $id)
    {
		//dd($request);
        $this->validate(
            $request, [
            //'email' => 'required|unique:users,email,'.$id,
            'carnet' => 'required',
            'carnet' => Rule::unique('carnet')->ignore($id, 'fk_id_miembro'),
            'nacionalidad' => 'required',
            'cod_tel' => 'required',
        ],
            $messages = [
            'carnet.unique' => 'El Carnet que ha ingresado ya está en uso.',
        ]);

                
        $input = $request->all();

        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = array_except($input,array('password'));
        }



        try{
            $user = User::find($id);
            $user->update($input);
            DB::table('role_user')->where('user_id',$id)->delete();

            foreach ($request->input('roles') as $key => $value) {
                $user->attachRole($value);
            }

            /* Actualizando Limites */
            foreach ($request->input('roles') as $v) {
                $rolUser = $v;
            }
            if ($rolUser == 5) {
                $carnets = new Collection();

                foreach ($request->carnets['fk_monedas'] as $key => $carnet){
//dd($request->carnets);

                if ($carnet== "") {
                     return redirect()->route('users.edit', [$id])
                            ->with('error',' El campo Carnet es requerido, por favor valide los datos ingresados'); 
                }
                if ($request->carnets['limite'][$key] == "") {
                   return redirect()->route('users.edit', [$id])
                            ->with('error',' El campo Límite es requerido, por favor valide los datos ingresados');
                }
                if ($request->carnets['carnet'][$key] == "") {
                     return redirect()->route('users.edit', [$id])
                            ->with('error',' El campo Tarjeta Virtual es requerido, por favor valide los datos ingresados');
                }
                if ($request->carnets['carnet_real'][$key] == "") {
                    return redirect()->route('users.edit', [$id])
                            ->with('error',' El campo Tarjeta Real es requerido, por favor valide los datos ingresados');
                }
                if ($request->carnets['tipoProducto'][$key] == "" || $request->carnets['tipoProducto'][$key] == null) {

                   return redirect()->route('users.edit', [$id])
                            ->with('error',' El campo Tipo de Producto es requerido, por favor valide los datos ingresados');
                }
                if($request->carnets['tipoProducto'][$key] == "2" && $request->carnets['emisor'][$key] == null){

                    return redirect()->route('users.edit', [$id])
                            ->with('error',' El campo Emisor es requerido para Tipo de Producto Externo, por favor valide los datos ingresados');
                }
                if($request->carnets['tipoProducto'][$key] == "2" && $request->carnets['codClienteEmisor'][$key] == null){
                    return redirect()->route('users.edit', [$id])
                            ->with('error',' El campo Código Cliente Emisor es requerido para Tipo de Producto Externo, por favor valide los datos ingresados');
                }


            }//end foreach validacion de campos
                foreach ($request->carnets['fk_monedas'] as $key => $carnet){


                    $carnets->push([
                        'id'=>$request->carnets['id'][$key],
                        'fk_monedas' => $carnet,
                        'limite' => $request->carnets['limite'][$key],
                        'carnet' => $request->carnets['carnet'][$key],
                        'carnet_real' => $request->carnets['carnet_real'][$key],

                        'tipo_producto' => $request->carnets['tipoProducto'][$key],
                        'cod_emisor' => $request->carnets['emisor'][$key],
                        'cod_cliente_emisor' => $request->carnets['codClienteEmisor'][$key],
                        'transar' => $request->carnets['transar'][$key],
                    ]);
                }

                carnet::whereNotIn('id', 
                    $carnets->filter(
                        function($carnet){return $carnet['id'];}
                    )->pluck('id')
                )
                ->where('fk_id_miembro', $user->id)
                ->delete();

                    $carnets->each(function($carnet) use ($user, $id){
//dd($carnet['tipo_producto']);
                        if($carnet['tipo_producto']=='1'){
                            $codEmi = null;
                            $codCliEmi = null;
                        }else{
                            $codEmi = $carnet['cod_emisor'];
                            $codCliEmi = $carnet['cod_cliente_emisor'];
                        }
                        
                        if($carnet['id']){
                            $limite = str_replace(".", "",$carnet['limite']);
                            $limiteUp = str_replace(",", ".",$limite);
                            
                            
                            $disponible = Ledge::select('disp_post')->where('fk_dni_miembros',$user->id)->get()->last();
                            $prelimite= carnet::select('limite','disponible')->where('id', $carnet['id'])->first();
                            $datLedge = Ledge::where("fk_dni_miembros",$id)->get();
            
                            if(count($datLedge) != 0){
                                $diff = $prelimite->limite - $disponible->disp_post;
                                $dispo = $limiteUp - $diff;
                            }

                            $limite = carnet::find($carnet['id']);
                                if( $limite->limite != $limiteUp){
                                    $limite->update([
                                        'limite'     => $limiteUp,
                                    ]);
                                }
                                //dd($carnet);
                            $limite->update([
                                        'carnet' => $carnet['carnet'],
                                        'carnet_real' => $carnet['carnet_real'],
										'disponible' => ($prelimite->disponible + (str_replace(",", ".",$limiteUp) - $prelimite->limite)),
                                        'tipo_producto' => $carnet['tipo_producto'],
                                        'cod_emisor' => $codEmi,
                                        'cod_cliente_emisor' =>  $codCliEmi,
                                        'transar' => $carnet['transar'],
                                    ]);									
							
                            }else{
                             $limite = str_replace(".", "",$carnet['limite']);
                                $limite = str_replace(",", ".",$limite);
                                    carnet::create([
                                        'carnet' => $carnet['carnet'],
                                        'limite' => $limite,
                                        'fk_id_banco' => 1,//$input['banco'],
                                        'fk_id_miembro' => $user->id,
                                        'fk_monedas' =>$carnet['fk_monedas'],
                                        'carnet_real' => $carnet['carnet_real'],
										'disponible'  => $limite,

                                        'tipo_producto' => $carnet['tipo_producto'],
                                        'cod_emisor' => $codEmi,
                                        'cod_cliente_emisor' => $codCliEmi,
                                        'transar' => $carnet['transar'],
                                    ]);
                                }								
				
                        // $updat = Ledge::select("id")->where('fk_dni_miembros',$user->id)->get()->last();
                            
                        //     if($updat){                   
                        //         if(count($updat) !=0){
                        //             $updat->update([
                        //                 'disp_post' => $dispo,
                        //             ]);
                        //         }
                        //     }else{
                        //     return redirect()->route('users.index')
                        //     ->with('success','Usuario editado Satisfactoriamente');
                        //     }
                    }); 
            }
            /* FIN Actualizando Limites */
            return redirect()->route('users.index')
                             ->with('success','Usuario editado Satisfactoriamente');
        }catch(\Exception $e){

            //dd($e);
           return redirect()->route('users.index')
                            ->with('error',' usuario no se pudo editar'.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activar($id){
        $user = User::withTrashed()->findOrFail($id);

        

       
        foreach ($user->roles as $v) {
            $rolUser = $v->id;
        }

        

        if($rolUser==3){
        $miem_come = miem_come::select("comercios.deleted_at")
                            ->join("comercios","comercios.id","miem_come.fk_id_comercio")
                            ->where('fk_id_miembro','=',$id)
                            ->first();
 
        if($miem_come->deleted_at==null){
            $user->update(['deleted_at' => NULL,]);
                return redirect()->route('users.index')->with('success','Usuario activado Satisfactoriamente');
        }else{
            return redirect()->route('users.index')->with('success','El comercio no se encuentra activo');
        }
        }else{
             $user->update(['deleted_at' => NULL,]);
                return redirect()->route('users.index')->with('success','Usuario activado Satisfactoriamente');
        }
    }

    public function desactivar($id){
            $user = User::find($id);
            $user->update([
                'deleted_at'         => Carbon::now(),
            ]);

        return redirect()->route('users.index')
                        ->with('success','Usuario desactivado Satisfactoriamente');
    }
    public function importar()
    {
        $bancos = bancos::select('bancos.*')
        ->get();

        return view('users.import')->with(['bancos' => $bancos]);
    }
     public function cargar_datos_usuarios(Request $request)
    {
        //try{
       $archivo = $request->file('archivo');
       $banco = $request['banco'];
       //dd($banco);
       $nombre_original=$archivo->getClientOriginalName();
       $extension=$archivo->getClientOriginalExtension();
       $r1=Storage::disk('archivos')->put($nombre_original,  \File::get($archivo) );
       $ruta  =  storage_path('archivos') ."/". $nombre_original;

       if($r1){
            $ct=0;
            Excel::selectSheetsByIndex(0)->load($ruta, function($hoja) {

                $hoja->each(function($fila,$i) {
                    ++$i;

                    if(empty($fila->codigo_del_proveedor) && empty($fila->cedula) && empty($fila->nombres) && empty($fila->apellidos) && empty($fila->telefono) && empty($fila->email) && empty($fila->nacimiento) && empty($fila->carnet) && empty($fila->limite) && empty($fila->monedas) && empty($fila->carnet_real)  ){
                                $errors[] = 23;
                                $cargaErrors = new Errors;
                                $cargaErrors->error_descr = '';
                                $cargaErrors->error_filas = $i;
                                $cargaErrors->fila_descr = 'La Linea '. $i .' esta vacia, por favor verifique';
                                $cargaErrors->user_id = Auth::user()->id;
                                $cargaErrors->save();
                            }else{
                                if(empty($fila->cedula)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'La Cédula esta Vacia';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->cedula;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }/*else{
                                    $cedula = explode('-', $fila->cedula);
                                    $userscedula=User::where("dni","=",$cedula[1])->withTrashed()->first();
                                    //dd($userscedula);
                                    if(!empty($userscedula)){
                                        //dd($userscedula);
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'La Cédula ya esta en uso';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->cedula;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();
                                    }
                                }*/
                                if(empty($fila->nombres)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'El Nombre esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->nombres;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }
                                if(empty($fila->apellidos)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'El Apellido esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->apellidos;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }
                                if(empty($fila->telefono)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'El Telefono esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->telefono;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }else{
                                    
                                    $telefono = explode('-', trim($fila->telefono));

                                    if($telefono[0] != '412' && $telefono[0] != '414' && $telefono[0] != '424' && $telefono[0] != '416' && $telefono[0] != '416')
                                    {
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'El número de teléfono es incorrecto ';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->telefono;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();                                        
                                    }
                                    else
                                    {
                                        if(strlen($telefono[1]) != 7)
                                        {
                                            $errors[] = 1;
                                            $cargaErrors = new Errors;
                                            $cargaErrors->error_descr = 'El número de teléfono es incorrecto ';
                                            $cargaErrors->error_filas = $i;
                                            $cargaErrors->fila_descr = $fila->telefono;
                                            $cargaErrors->user_id = Auth::user()->id;
                                            $cargaErrors->save();                                              
                                        }
                                        else{
                                            $cedula = explode('-', trim($fila->cedula));

                                            $userstelefono=User::where("num_tel","=",$telefono[1])->where("cod_tel", "=", "58".$telefono[0])->first();
    
                                            if(!empty($userstelefono)){
                                                if($userstelefono->dni != $cedula[1])
                                                {
                                                    $errors[] = 1;
                                                    $cargaErrors = new Errors;
                                                    $cargaErrors->error_descr = 'El Teléfono ya esta en uso para este nuevo cliente';
                                                    $cargaErrors->error_filas = $i;
                                                    $cargaErrors->fila_descr = $fila->telefono;
                                                    $cargaErrors->user_id = Auth::user()->id;
                                                    $cargaErrors->save();
                                                }
                                            }  
                                        }                                    
                                    }                            
                                }
                                if(empty($fila->email)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'El Email esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->email;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }else{
                                    $usersemails=User::where("email","=",trim($fila->email))->first();

                                    if(!empty($usersemails)){
                                        $cedula = explode('-', trim($fila->cedula));

                                        if($usersemails->dni != $cedula[1])
                                        {
                                            $errors[] = 1;
                                            $cargaErrors = new Errors;
                                            $cargaErrors->error_descr = 'El Email ya esta en uso para este nuevo cliente';
                                            $cargaErrors->error_filas = $i;
                                            $cargaErrors->fila_descr = $fila->email;
                                            $cargaErrors->user_id = Auth::user()->id;
                                            $cargaErrors->save();                                            
                                        }
                                    }
                                }
                                /* FECHA DE NACIMIENTO COMENTADA */
                                /*if(empty($fila->nacimiento)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'La Fecha de Nacimiento esta Vacia';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->nacimiento;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }*/
                                if(empty($fila->carnet)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'El Carnet esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->carnet;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }else{
                                    //dd(strlen($fila->carnet));
                                    if(strlen($fila->carnet) != 16){
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'El Carnet Debe contener 16 dígitos';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->carnet;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();
                                    }else{
                                        $carnet=carnet::where("carnet","=",$fila->carnet)->first();
                                        //dd($carnet);
                                        if(!empty($carnet)){
                                            $errors[] = 1;
                                            $cargaErrors = new Errors;
                                            $cargaErrors->error_descr = 'El Carnet ya esta en uso';
                                            $cargaErrors->error_filas = $i;
                                            $cargaErrors->fila_descr = $fila->carnet;
                                            $cargaErrors->user_id = Auth::user()->id;
                                            $cargaErrors->save();
                                        }
                                    }
                                }
                                if(empty($fila->limite) && $fila->tipo_producto != 2){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'El Limite esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->limite;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }
                                if(empty($fila->moneda)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'La Moneda esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->moneda;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }
                                else
                                {
                                    $moneda= Moneda::where("mon_id","=",$fila->moneda)->first();
                                    if(empty($moneda)){
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'Código de moneda inválido';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->moneda;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();                                        
                                    }                                     
                                }

                                if(empty($fila->carnet_real)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'El Carnet Real esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->carnet_real;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }else{
                                    //dd(strlen($fila->carnet));
                                    if(strlen($fila->carnet_real) != 16){
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'El Carnet Real Debe contener 16 dígitos';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->carnet_real;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();
                                    }else{
                                        $carnet_real=carnet::where("carnet_real","=",$fila->carnet_real)->first();
                                        //dd($carnet);
                                        if(!empty($carnet_real)){
                                            $errors[] = 1;
                                            $cargaErrors = new Errors;
                                            $cargaErrors->error_descr = 'El Carnet Real ya esta en uso';
                                            $cargaErrors->error_filas = $i;
                                            $cargaErrors->fila_descr = $fila->carnet_real;
                                            $cargaErrors->user_id = Auth::user()->id;
                                            $cargaErrors->save();
                                        }
                                    }
                                }

                                if(!empty($fila->cod_emisor)){
                                    $emisor=emisores::where("cod_emisor","=",$fila->cod_emisor)->first();
                                    if(empty($emisor)){
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'Código de emisor inválido';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->cod_emisor;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();                                        
                                    }                                    
                                }                                                   

                                if(!empty($fila->cod_cliente_emisor)){
                                    if(empty($fila->cod_emisor)){
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'Si especifica el código del cliente emisor debe ingresar un código de emisor';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->cod_cliente_emisor;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();                                        
                                    }
                                    else
                                    {
                                        $CodClienteEmisor = carnet::select("users.dni")
                                        ->join('users', 'carnet.fk_id_miembro', 'users.id')
                                        ->where("cod_cliente_emisor","=",$fila->cod_cliente_emisor)->first();

                                        if(!empty($CodClienteEmisor)){
                                            $cedula = explode('-', trim($fila->cedula));

                                            if($CodClienteEmisor->dni != $cedula[1])
                                            {
                                                $errors[] = 1;
                                                $cargaErrors = new Errors;
                                                $cargaErrors->error_descr = 'El Codigo Cliente Emisor ya esta en uso';
                                                $cargaErrors->error_filas = $i;
                                                $cargaErrors->fila_descr = $fila->email;
                                                $cargaErrors->user_id = Auth::user()->id;
                                                $cargaErrors->save();                                            
                                            }                                            
                                        }
                                    }
                                }                                

                                if(empty($fila->tipo_producto)){
                                    $errors[] = 1;
                                    $cargaErrors = new Errors;
                                    $cargaErrors->error_descr = 'Tipo producto esta Vacio';
                                    $cargaErrors->error_filas = $i;
                                    $cargaErrors->fila_descr = $fila->tipo_producto;
                                    $cargaErrors->user_id = Auth::user()->id;
                                    $cargaErrors->save();
                                }
                                else{
                                    if($fila->tipo_producto != 1 && $fila->tipo_producto != 2)
                                    {
                                        $errors[] = 1;
                                        $cargaErrors = new Errors;
                                        $cargaErrors->error_descr = 'Tipo producto inválido';
                                        $cargaErrors->error_filas = $i;
                                        $cargaErrors->fila_descr = $fila->tipo_producto;
                                        $cargaErrors->user_id = Auth::user()->id;
                                        $cargaErrors->save();                                        
                                    }
                                    else
                                    {
                                        if($fila->tipo_producto == 2)
                                        {
                                            if(empty($fila->cod_emisor) || empty($fila->cod_cliente_emisor))
                                            {
                                                $errors[] = 1;
                                                $cargaErrors = new Errors;
                                                $cargaErrors->error_descr = 'Para productos externos debe especificar el código del cliente y emisor';
                                                $cargaErrors->error_filas = $i;
                                                $cargaErrors->fila_descr = $fila->tipo_producto;
                                                $cargaErrors->user_id = Auth::user()->id;
                                                $cargaErrors->save();  
                                            }
                                        }
                                        else
                                        {
                                            if(!empty($fila->cod_emisor) || !empty($fila->cod_cliente_emisor))
                                            {
                                                $errors[] = 1;
                                                $cargaErrors = new Errors;
                                                $cargaErrors->error_descr = 'Para productos internos no debe especificar codigo de emisor o cliente';
                                                $cargaErrors->error_filas = $i;
                                                $cargaErrors->fila_descr = $fila->tipo_producto;
                                                $cargaErrors->user_id = Auth::user()->id;
                                                $cargaErrors->save();  
                                            }                                            
                                        }
                                    }
                                }                                
                            }//END ELSE PRINCIPAL


                    if(empty($errors)){

                        $cedula = explode('-', trim($fila->cedula));
                        $userscedula=User::where("dni","=",$cedula[1])->withTrashed()->first();

                        if(!empty($userscedula)){
                            //SI EXISTE EL USUARIO SOLO SE CREA SOLO EL NUEVO CARNET
                             $moneda=Moneda::where("mon_id","=", strtoupper($fila->moneda))->first();
                           //dd("segunda vuelta ", $userscedula->id);
                            $carnet= new carnet;
                            $carnet->carnet= $fila->carnet;
                            $carnet->carnet_real= $fila->carnet_real;
                            $carnet->limite= $fila->limite;
                            $carnet->fk_id_banco= 1;
                            $carnet->fk_id_miembro= $userscedula->id;
                            $carnet->created_at= Carbon::now();
                            $carnet->updated_at= Carbon::now();
                            $carnet->fk_monedas= $moneda->mon_id;
                            if(!empty($fila->cod_emisor)){
                            $carnet->cod_emisor= $fila->cod_emisor;
                            }
                            $carnet->cod_cliente_emisor= $fila->cod_cliente_emisor;
                            $carnet->tipo_producto= $fila->tipo_producto;
                            $carnet->save();
                        }else{
                            //SI NO EXISTE EL SUSUARIO SE CREA EL USUARIO Y EL CARNET
                            //dd("primera vuelta ", $fila->nombres);
                            $usuario=new User;
                            $cedula = explode("-",trim($fila->cedula));
                            $telefono = explode("-",trim($fila->telefono));
                            $usuario->nacionalidad= $cedula[0];
                            $usuario->dni= $cedula[1];
                            $usuario->first_name= trim($fila->nombres);
                            $usuario->last_name= trim($fila->apellidos);
                            $usuario->cod_tel= "58".(string)(int)$telefono[0];
                            $usuario->num_tel= $telefono[1];
                            $usuario->email= trim($fila->email);
                            $usuario->kind=1;
                            if ($fila->nacimiento) {
                                $usuario->birthdate= $fila->nacimiento;
                            }
                            $usuario->rif= 1;
                            //$usuario->password= bcrypt($fila->password);
                            $usuario->password= Hash::make('qwerty123456');
                            $usuario->created_at= Carbon::now();
                            $usuario->updated_at= Carbon::now();
                            $is_saved = $usuario->save();
                            if ($is_saved) {
                                //SI SE CREA EL USUARIO EXITOSAMENTE SE CREA EL CARNET
                                $moneda=Moneda::where("mon_id","=", strtoupper($fila->moneda))->first();
                           
                                $carnet= new carnet;
                                $carnet->carnet= $fila->carnet;
                                $carnet->carnet_real= $fila->carnet_real;
                                $carnet->limite= $fila->limite;
                                $carnet->fk_id_banco= 1;
                                $carnet->fk_id_miembro= $usuario->id;
                                $carnet->created_at= Carbon::now();
                                $carnet->updated_at= Carbon::now();
                                $carnet->fk_monedas= $moneda->mon_id;
                                if(!empty($fila->cod_emisor)){
                                    $carnet->cod_emisor= $fila->cod_emisor;
                                    }
                                $carnet->cod_cliente_emisor= $fila->cod_cliente_emisor;
                                $carnet->tipo_producto= $fila->tipo_producto;                                
                                $carnet->save();
                            }//END IS IS_SAVED
                            $usuario->attachRole(5);
                        }//END ELSE SI NO EXISTE EL USUARIO
                    };//END IF EMPTY ERRORS
                });
            });

            $cargaErrors = Errors::where('created_at',Carbon::now())
            ->orderBy('error_filas', 'asc')
            ->get();

            if ($cargaErrors!='[]') {
                //dd($cargaErrors);
                carnet::where('created_at',Carbon::now())->delete();
                Errors::where('created_at',Carbon::now())->delete();
                User::where('created_at',Carbon::now())->forceDelete();

                // instantiate and use the dompdf class
                $pdf = \App::make('dompdf.wrapper');

                $pdf->loadView('users.errorPdf', compact(['cargaErrors']));

                // Output the generated PDF to Browser
                return $pdf->stream('error_carga_masiva.pdf');

                //flash('Error al subir el archivo, compruebe los datos insertados e intentelo nuevamente.', '¡Operación Fallida!')->error();

                return redirect()->route('users.index');

            }else{

                return redirect()->route('users.index')->with('success','Los usuarios han sido importados satisfactoriamente');
            }

       }
       /*}catch(\Exception $e){
            return redirect()->route('users.import')->with('error','Error al subir el archivo, compruebe los datos insertados e intentelo nuevamente.');
       }*/

    }

    public function limites()
    {

        return view('users.limites');
    }

    public function cargar_limites(Request $request)
    {

        $err="";
        $arrayCed = [];
        $cantRep = 0;
        $arrayDup = array();

        if($request->hasFile('archivo')){
            $file = $request->file('archivo');

            if($file->getClientOriginalExtension() != 'xls' &&
            $file->getClientOriginalExtension() != 'xlsx'){
                //mensaje de error de extension de archivo
                flash('¡Importación cancelada, el archivo no posee el formato adecuado, por favor intente de nuevo con archivos xls ó xlsx!', '¡Error en Importación!')->error();

                return view('users/limites');
           }
           $path = Input::file('archivo')->getRealPath();
           $data = Excel::load($path, function($reader) {
                      $reader->formatDates(true, 'Y-m-d');
            })->get();

           $data = $data->toArray();

           if($file->getClientOriginalExtension() == 'xls'){

                for($i=0;$i<=count($data)-1;$i++){
                    if(!isset($data[$i]['cedula'])){
                        return redirect()->route('users.limites')->with('error','Error al subir el archivo, el mismo debe constar de un solo libro');
                    }else{
                        //$arrayCed[$i] = (string)$data[$i]['cedula'];
                        $cedula = (string)$data[$i]['cedula'];
                        $cedula = explode("-", $cedula);
                        $arrayNac[$i] = $cedula[0];

                        if(!isset($cedula[1])){
                            return redirect()->route('users.limites')->with('error','Error al subir el archivo, formato de cédulas incorrecto, el mismo debe ser ej: V-XXXXXXX ó E-XXXXXXXX');
                        }else{
                            $arrayCed[$i] = $cedula[1];
                        }

                        /*if($data[$i]['disponible'] > $data[$i]['limite']){
                            return redirect()->route('users.limites')->with('error','Error al subir el archivo, El campo disponible no puede ser mayor al campo limite');
                        }*/

                    }
                }

            /*TEMPORAL
                for($a=0;$a<=count($data);$a++){
                    for($b=$a+1;$b<=count($data)-1;$b++) {
                        //dd($data[$b]);
                        if($data[$a]['cedula']==$data[$b]['cedula']){
                            $cantRep++;
                            array_push($arrayDup, (object)$data[$b]);
                        }
                    }
                }
            TEMPORAL*/
                 if($cantRep != 0){

                    //mensaje de error de duplicados
                    /*flash('¡Importación cancelada, existen '.$cantRep.' cédulas duplicadas en el archivo, verifique y vuelva a importar!', '¡Error en Importación!')->error();
                    return view('users.limites')->with('duplicados',$arrayDup);*/

                    return redirect()->route('users.limites')->with('error','¡Importación cancelada, existen '.$cantRep.' cédulas duplicadas en el archivo, verifique y vuelva a importar!');

                }

                for($a=0;$a<=count($data)-1;$a++){



                    $cedInteger = (string)$data[$a]['cedula'];
                    $cedInteger = explode("-", $cedInteger);
                    $nacionalidad = $cedInteger[0];
                    $ced = $cedInteger[1];
                    $monedas = $data[$a]['monedas'];

                    //dd($moneda);
                    /*TEMPORALif($data[$a]['disponible'] <= $data[$a]['limite']){*/
                        $usersdni=User::select('users.id','users.dni','users.first_name','users.last_name','carnet.limite')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('monedas', 'carnet.fk_monedas', 'monedas.mon_id')
                        ->where("users.nacionalidad",$nacionalidad)
                        ->where("users.dni","=",$ced)
                        ->where("monedas.mon_nombre","=",strtoupper($monedas))

                        ->first();

                        $usersdniCount=User::select('users.id','users.dni','users.first_name','users.last_name','carnet.limite')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->whereIn("users.nacionalidad",$arrayNac)
                        ->whereIn("users.dni",$arrayCed)
                        ->get();

                        if(count($usersdniCount) == count($arrayCed)){
                            $lastRegLedger = Ledge::select('id','fk_dni_miembros','disp_post')
                            ->where('fk_dni_miembros',$usersdni->id)
                            ->get()
                            ->last();
                            $limite = carnet::where('fk_id_miembro',$usersdni->id)->first();
                            if(count($lastRegLedger)!=0){
                                $lastRegLedger->id = $lastRegLedger->id * 1;
                                $lastRegL = Ledge::select('disp_pre')
                                    ->where('id',$lastRegLedger->id)->first();
                                $datLedge = Ledge::where("fk_dni_miembros",$lastRegLedger->fk_dni_miembros)->first();

                                if(count($datLedge) != 0){
                                    $diff = $limite->limite - $datLedge->disp_post;
                                    $disp = $data[$a]['limite'] - $diff;
                                    /*if($disp < 0){
                                        $diferecia = $limite->limite - $data[$a]['limite'];
                                        $subtot = $data[$a]['limite'] * 100;
                                        $porc = $subtot / $limite->limite;
                                        $disp = $porc * $lastRegLedger->disp_post;
                                        $disp = $disp / 100;
                                        $disp = number_format($disp,2,".","");
                                        $disp = $disp *1;
                                    }*/
                                }

                                $updat = Ledge::select("id")->where('fk_dni_miembros',$lastRegLedger->fk_dni_miembros)->get()->last();
                                if(count($updat) !=0 ){
                                    $updat->update(['disp_post' => $disp,]);
                                }

                            }
                            $limite = carnet::where('fk_id_miembro',$usersdni->id)->first();
                            $limite->update([
                                'limite' => $data[$a]['limite'],
                            ]);


                            /*$lastRegLedger = Ledge::where('id',$lastRegLedger->id)
                            ->update(['disp_post'   =>  $data[$a]['limite'] ]);*/


                            $clientIP = \Request::ip();
                            $user= User::find(Auth::user()->id);

                            $idbanco = miem_ban::select('fk_id_banco')
                            ->where('fk_dni_miembro',$user->id)
                            ->first();

                            $idcomer = miem_come::select('fk_id_comercio')
                            ->where('fk_id_miembro',$user->id)
                            ->first();

                    /*TEMPORAL
                            $trans_head = new \App\Models\trans_head();
                            $trans_head->fk_dni_miembros = $usersdni->id;
                            $trans_head->fk_id_banco  = $idbanco->fk_id_banco;
                            $trans_head->fk_id_comer  = $idcomer->fk_id_comercio;
                            $trans_head->monto        = 0;
                            $trans_head->propina = 0;
                            $trans_head->neto = 0;
                            $trans_head->cancela_a    = 0;
                            $trans_head->token    = 0;
                            $trans_head->status    = 5;
                            $trans_head->ip     = $clientIP;
                            $trans_head->token_status    = 0;

                            if( $trans_head->save()){
                                $ledge = new \App\Models\Ledge();
                                $ledge->fk_id_trans_head= $trans_head->id;
                                $ledge->fk_dni_miembros= $trans_head->fk_dni_miembros;
                                $ledge->monto= $trans_head->monto;
                                $ledge->propina= $trans_head->propina;
                                $ledge->disp_pre= $lastRegL->disp_pre;
                                $ledge->disp_post= $data[$a]['disponible'];
                                if($ledge->save()){
                                    $err=false;
                                }else{
                                    return redirect()->route('users.limites')->with('error','Error al subir el archivo.');
                                }
                            }else{
                                return redirect()->route('users.limites')->with('error','Error al subir el archivo.');
                            }
                    TEMPORAL*/
                        }else{
                            $err=true;
                        }

                /*TEMPORAL
                    }else{
                        return redirect()->route('users.limites')->with('error','Error al subir el archivo, los montos disponibles no pueden ser mayor al limite.');
                    }
                TEMPORAL*/
                }/*ENDFOR*/
           }

           if($file->getClientOriginalExtension() == 'xlsx'){

                for($i=0;$i<=count($data)-1;$i++){

                    if(!isset($data[$i]['cedula'])){
                        return redirect()->route('users.limites')->with('error','Error al subir el archivo, el mismo debe constar de un solo libro');
                    }else{

                        $cedula = (string)$data[$i]['cedula'];

                        $cedula = explode("-", $cedula);
                        $arrayNac[$i] = $cedula[0];
                        if(!isset($cedula[1])){
                            return redirect()->route('users.limites')->with('error','Error al subir el archivo, formato de cédulas incorrecto, el mismo debe ser ej: V-XXXXXXX ó E-XXXXXXXX');
                        }else{
                            $arrayCed[$i] = $cedula[1];
                        }
                    }
                }
            /*TEMPORAL
                for($a=0;$a<=count($data);$a++){
                    for($b=$a+1;$b<=count($data)-1;$b++) {
                        //dd($data[$b]);
                        if($data[$a]['cedula']==$data[$b]['cedula']){
                            $cantRep++;
                            array_push($arrayDup, (object)$data[$b]);
                        }
                    }
                }
            TEMPORAL*/

                if($cantRep != 0){
                    //mensaje de error de duplicados
                    /*flash('¡Importación cancelada, existen '.$cantRep.' cédulas duplicadas en el archivo, verifique y vuelva a importar!', '¡Error en Importación!')->error();
                    return view('transacciones/cargaPagos')->with('duplicados',$arrayDup);*/

                    return redirect()->route('users.limites')->with('error','¡Importación cancelada, existen '.$cantRep.' cédulas duplicadas en el archivo, verifique y vuelva a importar!');
                }

                for($a=0;$a<=count($data)-1;$a++){

                    $cedInteger = (string)$data[$a]['cedula'];
                    $cedInteger = explode("-", $cedInteger);
                    $nacionalidad = $cedInteger[0];
                    $ced = $cedInteger[1];
                    $monedas = $data[$a]['monedas'];

        /*TEMPORAL  if($data[$a]['disponible'] <= $data[$a]['limite']){*/
                        $usersdni=User::select('users.id','users.nacionalidad','users.dni','users.first_name','users.last_name','carnet.limite')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('monedas', 'carnet.fk_monedas', 'monedas.mon_id')
                        ->where("users.nacionalidad",$nacionalidad)
                        ->where("users.dni",$ced)
                        ->where("monedas.mon_nombre","=",strtoupper($monedas))
                        ->first();

                        $usersdniCount=User::select('users.id','users.dni','users.first_name','users.last_name','carnet.limite')
                        ->join('carnet','carnet.fk_id_miembro','users.id')
                        ->join('monedas', 'carnet.fk_monedas', 'monedas.mon_id')
                        ->where("monedas.mon_nombre","=",strtoupper($monedas))
                        ->whereIn("users.nacionalidad",$arrayNac)
                        ->whereIn("users.dni",$arrayCed)
                        ->get();


                        if(count($usersdniCount) == count($arrayCed)){
                            $lastRegLedger = Ledge::select('id','disp_post','fk_dni_miembros')
                            ->where('fk_dni_miembros',$usersdni->id)
                            ->get()
                            ->last();

                             $moneda=Moneda::where("mon_nombre","=", strtoupper($monedas))->first();

                             $limite = carnet::where('fk_id_miembro',$usersdni->id)->where('fk_monedas',$moneda->mon_id)->first();

                            if(count($lastRegLedger)!=0){

                                $lastRegLedger->id = $lastRegLedger->id * 1;
                                $lastRegL = Ledge::select('disp_pre')
                                    ->where('id',$lastRegLedger->id)->first();

                                $datLedge = Ledge::where("fk_dni_miembros",$lastRegLedger->fk_dni_miembros)->first();

                                if(count($datLedge) != 0){

                                    $diff = $limite->limite - $datLedge->disp_post;
                                    $disp = $data[$a]['limite'] - $diff;
                                    /*if($disp < 0){
                                        $diferecia = $limite->limite - $data[$a]['limite'];
                                        $subtot = $data[$a]['limite'] * 100;
                                        $porc = $subtot / $limite->limite;
                                        $disp = $porc * $lastRegLedger->disp_post;
                                        $disp = $disp / 100;
                                        $disp = number_format($disp,2,".","");
                                        $disp = $disp *1;
                                    }*/
                                }
                                $updat = Ledge::select("id")->where('fk_dni_miembros',$lastRegLedger->fk_dni_miembros)->get()->last();

                                if(count($updat) !=0 ){
                                    $updat->update(['disp_post' => $disp,]);
                                }

                            }

                            $moneda=Moneda::where("mon_nombre","=", strtoupper($monedas))->first();

                            $limite = carnet::where('fk_id_miembro',$usersdni->id)->where('fk_monedas',$moneda->mon_id)->first();

                            $limite->update([
                                'limite' => $data[$a]['limite'],
                            ]);

                            /*$lastRegLedger = Ledge::where('id',$lastRegLedger->id)
                            ->update(['disp_post'   =>  $data[$a]['limite'] ]);*/


                            $clientIP = \Request::ip();
                            $user= User::find(Auth::user()->id);

                            $idbanco = miem_ban::select('fk_id_banco')
                            ->where('fk_dni_miembro',$user->id)
                            ->first();

                            $idcomer = miem_come::select('fk_id_comercio')
                            ->where('fk_id_miembro',$user->id)
                            ->first();

                    /*TEMPORAL
                            $trans_head = new \App\Models\trans_head();
                            $trans_head->fk_dni_miembros = $usersdni->id;
                            $trans_head->fk_id_banco  = $idbanco->fk_id_banco;
                            $trans_head->fk_id_comer  = $idcomer->fk_id_comercio;
                            $trans_head->monto        = 0;
                            $trans_head->propina = 0;
                            $trans_head->neto = 0;
                            $trans_head->cancela_a    = 0;
                            $trans_head->token    = 0;
                            $trans_head->status    = 5;
                            $trans_head->ip     = $clientIP;
                            $trans_head->token_status    = 0;

                            if( $trans_head->save()){
                                $ledge = new \App\Models\Ledge();
                                $ledge->fk_id_trans_head= $trans_head->id;
                                $ledge->fk_dni_miembros= $trans_head->fk_dni_miembros;
                                $ledge->monto= $trans_head->monto;
                                $ledge->propina= $trans_head->propina;
                                $ledge->disp_pre= $lastRegL->disp_pre;
                                $ledge->disp_post= $data[$a]['disponible'];
                                if($ledge->save()){
                                    $err=false;
                                }else{
                                    return redirect()->route('users.limites')->with('error','Error al subir el archivo.');
                                }
                            }else{
                                return redirect()->route('users.limites')->with('error','Error al subir el archivo.');
                            }
                    TEMPORAL*/

                        }else{
                            $err=true;

                        }
                /*TEMPORAL
                    }else{
                        return redirect()->route('users.limites')->with('error','Error al subir el archivo, los montos disponibles no pueden ser mayor al limite.');
                    }
                TEMPORAL*/
                }//ENDFOR


           }


           if($err==true){
                return redirect()->route('users.limites')->with('error','Error al subir el archivo, una o más cédula(s) no se encuentran registradas en sistema');
           }else{
                return redirect()->route('users.limites')->with('success','Los limites han sido actualizados satisfactoriamente');
           }

        }

      /*try{
       $archivo = $request->file('archivo');
       $banco = $request['banco'];
       //dd($banco);
       $nombre_original=$archivo->getClientOriginalName();
       $extension=$archivo->getClientOriginalExtension();
       $r1=Storage::disk('archivos')->put($nombre_original,  \File::get($archivo) );
       $ruta  =  storage_path('archivos') ."/". $nombre_original;

       if($r1){
            $ct=0;
            Excel::selectSheetsByIndex(0)->load($ruta, function($hoja) {

                $hoja->each(function($fila) {
                    $cedula = explode("-",$fila->cedula);

                    $usersdni=User::select('users.id','users.dni','users.first_name','users.last_name','carnet.limite')
                    ->join('carnet','carnet.fk_id_miembro','users.id')
                    ->where("users.dni","=",$cedula[1])
                    ->get();

                    if(count( $usersdni)!=0){
                        $limite = carnet::where('fk_id_miembro',$usersdni->id)->first();

                        $limite->update([
                            'limite' => $fila->limite,
                        ]);

                        return redirect()->route('users.limites')->with('success','Los limites han sido actualizados satisfactoriamente');
                    }else{

                        return redirect()->route('users.limites')->with('error','Error al subir el archivo, una o más cédula(s) no se encuentran registradas en sistema');
                    }

                });

            });


       }
       }catch(\Exception $e){
            return redirect()->route('users.limites')->with('error','Error al subir el archivo, compruebe los datos insertados e intentelo nuevamente.');
       }*/

    }

    /*index para interface de reporte consolidado de cliente*/
    public function reports(){
        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

        $time_desde= date('Y-m-d 00:00:00');
        $time_hasta = date('Y-m-d 23:59:59');
        //$time_hasta= strtotime ( '+1 day' , strtotime ( $fecha_hasta ) ) ;
        /*$time_hasta= strtotime ( $fecha_hasta );
        $time_hasta = date ( 'Y-m-d 23:59:59' , $time_hasta );*/


        if($rol == 1){

                         $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel||'-'||users.num_tel as telefono"),
                            "users.email as correo",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            "ledger.disp_post as disponible",
                            "carnet.limite",
                            "trans_head.status as estatus"
                        )
                            ->join("carnet","carnet.fk_id_miembro","users.id")
                            ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                            ->join("comercios","comercios.id","trans_head.fk_id_comer")
                            ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                            ->where('trans_head.status',0)
                            ->where('trans_head.reverso',null)
                            ->whereBetween('trans_head.created_at',array(
                                /*Carbon::now()->format("Y-m-d H:m:s"),
                                Carbon::now()->addDay()->format("Y-m-d H:m:s")*/
                                $time_desde,$time_hasta
                            ))
                            ->groupBy(
                                "users.dni",
                                "users.nacionalidad",
                                "carnet.carnet",
                                "users.first_name",
                                "users.last_name",
                                "telefono",
                                "users.email",
                                "ledger.disp_post",
                                "carnet.limite",
                                "trans_head.status"
                            );

                        $query2 = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("'0' as telefono"),
                            DB::raw("'0' as correo"),
                            DB::raw("'0' as consumos"),
                            DB::raw("'0' as propinas")
                        )
                        ->join("carnet","carnet.fk_id_miembro","users.id")
                        ->whereNotExists(function($q) use($time_desde,$time_hasta){
                            $q->select(DB::raw(1))
                            ->from("trans_head")
                            ->whereRaw("trans_head.fk_dni_miembros = users.id and trans_head.created_at BETWEEN '".$time_desde."' AND '".$time_hasta."'");
                        });

                         //$clientes = $query->union($query2)->get();
                        $clientes = $query->get();
                        $comercios = comercios::select("id","descripcion")->get();

                        if(count($clientes) != 0){
                            return view('users.reports')->with(['clientes'=>$clientes,'rol'=>$rol,'userCount'=>count($clientes),'comercios'=>$comercios]);
                        }else{
                            return view('users.reports')
                                ->with(['rol'=>$rol,'clientes'=> $clientes,'userCount'=>count($clientes),'comercios'=>$comercios]);
                        }

        }else if($rol == 2 || $rol == 4 || $rol == 6 ){

                        $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();

                        $descripbanco = bancos::select("descripcion")->where("id",$banco->fk_id_banco)->first();

                            $query = User::select(
                                DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                                "carnet.carnet as carnet",
                                "users.first_name as nombre",
                                "users.last_name as apellido",
                                DB::raw("users.cod_tel||'-'||users.num_tel as telefono"),
                                "users.email as correo",
                                DB::raw("SUM(trans_head.monto) as consumos"),
                                DB::raw("SUM(trans_head.propina) as propinas"),
                                //"ledger.disp_post as disponible",
                                "carnet.limite",
                                "carnet.fk_monedas",
                                "monedas.mon_nombre",
                                "trans_head.status as estatus"
                            )                        
                            ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                            ->join("carnet","trans_head.carnet_id","carnet.id")
                            ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                            ->join("monedas","carnet.fk_monedas","monedas.mon_id")
                            ->where('trans_head.status',0)
                            ->where('trans_head.reverso',null)
                            ->where('monedas.mon_id',2)
                            ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                            ->orderBy('users.dni','DESC')
                            ->groupBy(
                                    "users.nacionalidad",
                                    "users.dni",
                                    "carnet.carnet",
                                    "users.first_name",
                                    "users.last_name",
                                    "telefono",
                                    "users.email",
                                    //"ledger.disp_post",
                                    "carnet.limite",
                                    "carnet.fk_monedas",
                                    "monedas.mon_id",                            
                                    "trans_head.status"
                                );                            
                        
                        $clientes = $query->get();

                        //dd($clientes);

                        if(count($clientes) != 0){
                            return view('users.reports')->with(['clientes'=>$clientes,'rol'=>$rol,'userCount'=>count($clientes)]);
                        }else{
                            return view('users.reports')
                                ->with(['rol'=>$rol,'clientes'=> $clientes,'userCount'=>count($clientes)]);
                        }

        }else if ($rol == 3){

            //dd($time_desde."   ".$time_hasta);
                        $comercio =  miem_come::select("fk_id_comercio")->where("fk_id_miembro",$user->id)->first();

                        $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel||'-'||users.num_tel as telefono"),
                            "users.email as correo",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            "ledger.disp_post as disponible",
                            "carnet.limite",
                            "trans_head.status as estatus"
                        )
                        ->join("carnet","carnet.fk_id_miembro","users.id")
                        ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                        ->join("bancos","bancos.id","trans_head.fk_id_banco")
                        ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                        ->where("trans_head.fk_id_comer",$comercio->fk_id_comercio)
                        ->where('trans_head.status',0)
                        ->where('trans_head.reverso',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->groupBy(
                            "users.dni",
                            "users.nacionalidad",
                            "carnet.carnet",
                            "users.first_name",
                            "users.last_name",
                            "telefono",
                            "users.email",
                            "ledger.disp_post",
                            "carnet.limite",
                            "trans_head.status"
                        );

                        $clientes = $query->get();


                        $comercios = comercios::select("id","descripcion")->get();

                        if(count($clientes) != 0){

                            return view('users.reports')
                                ->with(['rol'=>$rol,'clientes'=> $clientes,'userCount'=>count($clientes)]);
                        }else{

                            return view('users.reports')
                                ->with(['rol'=>$rol,'clientes'=> $clientes,'userCount'=>count($clientes)]);
                        }

        }
        //return view('users.reports')->with('rol',$rol);
    }

    /*metodo que realiza la busqueda para la tabla preview del reporte*/
    public function search(Request $request){

                //dd($request);
                $user= User::find(Auth::user()->id);
                $roles= $user->roles;
                $rol = null;
                foreach ($roles as $value) {
                    $rol = $value->id;
                }

                if( $request->fecha_desde == null ){
                    $time_desde= date('Y-m-d');

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
                    $time_hasta = date('Y-m-d');
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

                    if($rol == 1){


                         $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel||'-'||users.num_tel as telefono"),
                            "users.email as correo",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            //"ledger.disp_post as disponible",
                            "carnet.limite",
                            "carnet.fk_monedas",
                            "monedas.mon_nombre",
                            "trans_head.status as estatus"
                        )
                            ->join("carnet","carnet.fk_id_miembro","users.id")
                            ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                            ->join("comercios","comercios.id","trans_head.fk_id_comer")
                            ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                            ->join("monedas","carnet.fk_monedas","monedas.mon_id")
                            ->where('trans_head.status',0)

                            ->where('trans_head.reverso',null)
                            ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                            ->groupBy(
                                "users.nacionalidad",
                                "users.dni",
                                "carnet.carnet",
                                "users.first_name",
                                "users.last_name",
                                "telefono",
                                "users.email",
                                //"ledger.disp_post",
                                "carnet.limite",
                                "carnet.fk_monedas",
                                "monedas.mon_id",
                                "trans_head.status"
                            );

                        /*$query2 = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("'0' as telefono"),
                            DB::raw("'0' as correo"),
                            DB::raw("'0' as consumos"),
                            DB::raw("'0' as propinas")
                        )
                        ->join("carnet","carnet.fk_id_miembro","users.id")
                        ->whereNotExists(function($q) use($time_desde,$time_hasta){
                            $q->select(DB::raw(1))
                            ->from("trans_head")
                            ->whereRaw("trans_head.fk_dni_miembros = users.id and trans_head.created_at BETWEEN '".$time_desde."' AND '".$time_hasta."'");
                        });*/

                         //$clientes = $query->union($query2)->get();
                        /*if($request->estado != "1000"){
                            $query = $query->where("trans_head.status",$request->estado);
                        }

                        if($request->cliente != 0){
                            $query = $query->where("users.dni",$request->cliente);
                        }*/
                        if($request->mon_nombre){
                            $query = $query->where("carnet.fk_monedas",$request->mon_nombre);
                        }

                        if($request->mon_nombre){
                            $query = $query->where("carnet.fk_monedas",$request->mon_nombre);
                        }

                         $clientes = $query->get();

                        if(count($clientes) != 0){
                            return view('users.reports')->with(['clientes'=>$clientes,'rol'=>$rol,'userCount'=>count($clientes)]);
                        }else{
                            return view('users.reports')
                                ->with(['rol'=>$rol,'clientes'=> $clientes,'userCount'=>count($clientes)]);
                        }


                    }else if($rol == 2 || $rol == 4 || $rol == 6 ){


                        $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();

                        $descripbanco = bancos::select("descripcion")->where("id",$banco->fk_id_banco)->first();

                        $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel||'-'||users.num_tel as telefono"),
                            "users.email as correo",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            //"ledger.disp_post as disponible",
                            "carnet.limite",
                            "carnet.fk_monedas",
                            "monedas.mon_nombre",
                            "trans_head.status as estatus"
                        )                        
                        ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                        ->join("carnet","trans_head.carnet_id","carnet.id")
                        ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                        ->join("monedas","carnet.fk_monedas","monedas.mon_id")
                        ->where('trans_head.status',0)
                        ->where('trans_head.reverso',null)
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->orderBy('users.dni','DESC')
                        ->groupBy(
                                "users.nacionalidad",
                                "users.dni",
                                "carnet.carnet",
                                "users.first_name",
                                "users.last_name",
                                "telefono",
                                "users.email",
                                //"ledger.disp_post",
                                "carnet.limite",
                                "carnet.fk_monedas",
                                "monedas.mon_id",                            
                                "trans_head.status"
                            );

                        $query2 = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("'0' as correo"),
                            DB::raw("'0' as telefono"),
                            DB::raw("'0' as consumos"),
                            DB::raw("'0' as propinas")
                        )
                        ->join("carnet","carnet.fk_id_miembro","users.id")
                        ->where("carnet.fk_id_banco",$banco->fk_id_banco)
                        ->whereNotExists(function($q) use($time_desde,$time_hasta){
                            $q->select(DB::raw(1))
                            ->from("trans_head")
                            ->whereRaw("trans_head.fk_dni_miembros = users.id and trans_head.created_at BETWEEN '".$time_desde."' AND '".$time_hasta."'");
                        });

                        //$clientes = $query->union($query2)->get();
                        /*if($request->estado != "1000"){
                            $query = $query->where("trans_head.status",$request->estado);
                        }*/

                        if($request->cliente != 0){
                            $query = $query->where("users.dni",$request->cliente);
                        }
                        if($request->mon_nombre){
                            $query = $query->where("carnet.fk_monedas",$request->mon_nombre);
                        }
  
                        $clientes = $query->get();

                       

                        //dd($clientes);

                        if(count($clientes) != 0){
                            return view('users.reports')->with(['clientes'=>$clientes,'rol'=>$rol,'userCount'=>count($clientes)]);
                        }else{

                            return view('users.reports')
                                ->with(['rol'=>$rol,'userCount'=>count($clientes)]);
                        }

                    }else if ($rol == 3){
                        $comercio =  miem_come::select("fk_id_comercio")->where("fk_id_miembro",$user->id)->first();

                        $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel||'-'||users.num_tel as telefono"),
                            "users.email as correo",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            //"ledger.disp_post as disponible",
                            "carnet.limite",
                            "carnet.fk_monedas",
                            "monedas.mon_nombre",
                            "trans_head.status as estatus"
                        )
                        ->join("carnet","carnet.fk_id_miembro","users.id")
                        ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                        ->join("bancos","bancos.id","trans_head.fk_id_banco")
                        ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                        ->join("monedas","carnet.fk_monedas","monedas.mon_id")
                        ->where("trans_head.fk_id_comer",$comercio->fk_id_comercio)
                        ->where('trans_head.status',0)
                        ->where('trans_head.reverso',null)
                        ->orderBy('users.dni','DESC')
                        ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                        ->groupBy(
                            "users.nacionalidad",
                            "users.dni",
                            "carnet.carnet",
                            "users.first_name",
                            "users.last_name",
                            "telefono",
                            "users.email",
                            //"ledger.disp_post",
                            "carnet.limite",
                            "carnet.fk_monedas",
                            "monedas.mon_id",
                            "trans_head.status"
                        );

                        /*if($request->estado != "1000"){
                            $query = $query->where("trans_head.status",$request->estado);
                        }*/

                        if($request->cliente != 0){
                            $query = $query->where("users.dni",$request->cliente);
                        }

                        if($request->mon_nombre){
                            $query = $query->where("carnet.fk_monedas",$request->mon_nombre);
                        }

                        $clientes = $query->get();

                        if(count($clientes) != 0){

                            return view('users.reports')
                                ->with(['rol'=>$rol,'clientes'=> $clientes,'userCount'=>count($clientes)]);
                        }else{

                            return view('users.reports')
                                ->with(['rol'=>$rol,'clientes'=> $clientes,'userCount'=>count($clientes),'cliente'=>$request->cliente]);
                        }

                    }

                }catch(\Exception $e){
                    flash(' '.$e, '¡Alert!')->error();
                }

    }



    /*metodo que genera el reporte en formato excel*/
    public function export_clients($fecha_desde,$fecha_hasta,$estado,$cliente, $moneda){

    //dd($moneda);
        $user= User::find(Auth::user()->id);
        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

        $time_desde = date('Y-m-d 00:00:00',strtotime($fecha_desde));
        $time_hasta = date('Y-m-d 23:59:59',strtotime($fecha_hasta));

        try{

                if($rol == 1){

                        $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as tarjeta_membresia",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel ||'-'||users.num_tel as telefono"),
                            "users.email as correo_electronico",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            /*"ledger.disp_post as disponible",*/
                            "carnet.limite",
                            "monedas.mon_nombre as moneda",
                            "trans_head.status as estatus"
                        )
                            ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                            ->join("carnet","trans_head.carnet_id","carnet.id")
                            ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                            ->join("monedas","carnet.fk_monedas","monedas.mon_id")
                            ->where('trans_head.status',0)
                            ->where('trans_head.reverso',null)
                            ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                            ->groupBy(
                                "users.nacionalidad",
                                "users.dni",
                                "carnet.carnet",
                                "users.first_name",
                                "users.last_name",
                                "telefono",
                                "users.email",
                                "ledger.disp_post",
                                "carnet.limite",
                                "monedas.mon_id",
                                "trans_head.status"
                            );

                        $query2 = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("'0' as telefono"),
                            DB::raw("'0' as correo"),
                            DB::raw("'0' as consumos"),
                            DB::raw("'0' as propinas")
                        )
                            ->join("carnet","carnet.fk_id_miembro","users.id")
                            ->whereNotExists(function($q) use($time_desde,$time_hasta){
                                    $q->select(DB::raw(1))
                                        ->from("trans_head")
                                        ->whereRaw("trans_head.fk_dni_miembros = users.id and trans_head.created_at BETWEEN '".$time_desde."' AND '".$time_hasta."'");
                            });


                        //$clientes = $query->union($query2)->get();
                        /*if($estado != "1000"){
                            $query = $query->where("trans_head.status",$estado);
                        }*/

                        if($cliente != 0){
                            $query = $query->where("users.dni",$cliente);
                        }

                        if($moneda){
                            $query = $query->where("carnet.fk_monedas",$moneda);
                        }
                        $clientes = $query->get();

                        foreach ($clientes as $key => $value) {
                            $clientes[$key]->disponible = number_format($clientes[$key]->disponible, 2, ',', '.');
                            $clientes[$key]->limite = number_format($clientes[$key]->limite, 2, ',', '.');
                            $clientes[$key]->moneda = $clientes[$key]->moneda;
                            if($clientes[$key]->estatus == 0){
                                $clientes[$key]->estatus = "Aprobada";
                            }elseif($clientes[$key]->estatus == 1){
                                $clientes[$key]->estatus = "Por Aprobar";
                            }elseif($clientes[$key]->estatus == 2){
                                $clientes[$key]->estatus = "Cancelada";
                            }elseif($clientes[$key]->estatus == 3){
                                $clientes[$key]->estatus = "Rechazada";
                            }elseif($clientes[$key]->estatus == 4){
                                $clientes[$key]->estatus = "Reversada";
                            }
                            $clientes[$key]->consumos = number_format($clientes[$key]->consumos, 2, ',', '.');
                            $clientes[$key]->propinas = number_format($clientes[$key]->propinas, 2, ',', '.');
                        }

                         Excel::create('Reporte Totalizado de Clientes desde '.$fecha_desde.' hasta '.$fecha_hasta.' '.$clientes[0]->descripcion,function($excel) use($clientes){
                                $excel->sheet('Operaciones', function($sheet) use($clientes) {
                                    $sheet->setOrientation('lanscape');
                                    $sheet->fromArray($clientes);
                                });
                            })->export('xls');

                    }else if($rol == 2 || $rol == 4 || $rol == 6 ){
                        $banco = miem_ban::select("fk_id_banco")->where("fk_dni_miembro",$user->id)->first();

                        $descripbanco = bancos::select("descripcion")->where("id",$banco->fk_id_banco)->first();

                        $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as tarjeta_membresia",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel ||'-'||users.num_tel as telefono"),
                            "users.email as correo_electronico",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            /*"ledger.disp_post as disponible",*/
                            "carnet.limite",
                            "monedas.mon_nombre as moneda",
                            "trans_head.status as estatus"
                        )
                            ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                            ->join("carnet","trans_head.carnet_id","carnet.id")
                            ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                            ->join("monedas","carnet.fk_monedas","monedas.mon_id")
                            ->where("carnet.fk_id_banco",$banco->fk_id_banco)
                            ->where('trans_head.status',0)
                            ->where('trans_head.reverso',null)
                            ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                            ->groupBy(
                                "users.nacionalidad",
                                "users.dni",
                                "carnet.carnet",
                                "users.first_name",
                                "users.last_name",
                                "telefono",
                                "users.email",
                                /*"ledger.disp_post",*/
                                "carnet.limite",
                                "monedas.mon_id", 
                                "trans_head.status"
                            );

                        $query2 = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            "carnet.carnet as carnet",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("'0' as telefono"),
                            DB::raw("'0' as correo_electronico"),
                            DB::raw("'0' as consumos"),
                            DB::raw("'0' as propinas")
                        )
                            ->join("carnet","carnet.fk_id_miembro","users.id")
                            ->where("carnet.fk_id_banco",$banco->fk_id_banco)
                            ->whereNotExists(function($q) use($time_desde,$time_hasta){
                                    $q->select(DB::raw(1))
                                        ->from("trans_head")
                                        ->whereRaw("trans_head.fk_dni_miembros = users.id and trans_head.created_at BETWEEN '".$time_desde."' AND '".$time_hasta."'");
                            });

                        //$clientes = $query->union($query2)->get();
                        /*if($estado != "1000"){
                            $query = $query->where("trans_head.status",$estado);
                        }*/

                        if($cliente != 0){
                            $query = $query->where("users.dni",$cliente);
                        }

                        if($moneda){
                            $query = $query->where("carnet.fk_monedas",$moneda);
                        }

                        $clientes = $query->get();

                        foreach ($clientes as $key => $value) {
                            /*$clientes[$key]->disponible = number_format($clientes[$key]->disponible, 2, ',', '.');*/
                            $clientes[$key]->limite = number_format($clientes[$key]->limite, 2, ',', '.');
                            $clientes[$key]->moneda = $clientes[$key]->moneda;
                            if ($rol != 2) {
                            $value->tarjeta_membresia = substr($value->tarjeta_membresia,-20,4) .' XXXX XXXX '. substr($value->tarjeta_membresia,-4);
                            }
                            if($clientes[$key]->estatus == 0){
                                $clientes[$key]->estatus = "Aprobada";
                            }elseif($clientes[$key]->estatus == 1){
                                $clientes[$key]->estatus = "Por Aprobar";
                            }elseif($clientes[$key]->estatus == 2){
                                $clientes[$key]->estatus = "Cancelada";
                            }elseif($clientes[$key]->estatus == 3){
                                $clientes[$key]->estatus = "Rechazada";
                            }elseif($clientes[$key]->estatus == 4){
                                $clientes[$key]->estatus = "Reversada";
                            }
                            $clientes[$key]->consumos = number_format($clientes[$key]->consumos, 2, ',', '.');
                            $clientes[$key]->propinas = number_format($clientes[$key]->propinas, 2, ',', '.');
                        }

                         Excel::create('Reporte Totalizado de Clientes desde '.$fecha_desde.' hasta '.$fecha_hasta.' '.$clientes[0]->descripcion,function($excel) use($clientes){
                                $excel->sheet('Operaciones', function($sheet) use($clientes) {
                                    $sheet->setOrientation('lanscape');
                                    $sheet->fromArray($clientes);
                                });
                            })->export('xls');


                    }else if ($rol == 3){

                        $comercio =  miem_come::select("fk_id_comercio")->where("fk_id_miembro",$user->id)->first();

                        $query = User::select(
                            DB::raw("users.nacionalidad ||'-'|| users.dni as cedula"),
                            //"carnet.carnet as tarjeta_membresia",
                            "users.first_name as nombre",
                            "users.last_name as apellido",
                            DB::raw("users.cod_tel ||'-'||users.num_tel as telefono"),
                            "users.email as correo_electronico",
                            DB::raw("SUM(trans_head.monto) as consumos"),
                            DB::raw("SUM(trans_head.propina) as propinas"),
                            "ledger.disp_post as disponible",
                            "carnet.limite",
                            "monedas.mon_nombre",
                            "trans_head.status as estatus"
                        )

                            ->join("carnet","carnet.fk_id_miembro","users.id")
                            ->join("trans_head","trans_head.fk_dni_miembros","users.id")
                            ->join("bancos","bancos.id","trans_head.fk_id_banco")
                            ->join("ledger","ledger.fk_id_trans_head","trans_head.id")
                            ->join("monedas","carnet.fk_monedas","monedas.mon_id")
                            ->where("trans_head.fk_id_comer",$comercio->fk_id_comercio)
                            ->where('trans_head.status',0)
                            ->where('trans_head.reverso',null)
                            ->whereBetween('trans_head.created_at',array($time_desde,$time_hasta))
                            ->groupBy(
                                "users.nacionalidad",
                                "users.dni",
                                "carnet.carnet",
                                "users.first_name",
                                "users.last_name",
                                "telefono",
                                "users.email",
                                "ledger.disp_post",
                                "carnet.limite",
                                "monedas.mon_id", 
                                "trans_head.status"
                            );

                           /*if($estado != "1000"){
                                $query = $query->where("trans_head.status",$estado);
                           }*/

                           if($cliente != 0){
                                $query = $query->where("users.dni",$cliente);
                           }

                           if($moneda){
                            $query = $query->where("carnet.fk_monedas",$moneda);
                            }

                           $clientes = $query->get();

                           foreach ($clientes as $key => $value) {
                                $clientes[$key]->disponible = number_format($clientes[$key]->disponible, 2, ',', '.');
                                $clientes[$key]->limite = number_format($clientes[$key]->limite, 2, ',', '.');
                                $clientes[$key]->moneda = $clientes[$key]->moneda;
                                if($clientes[$key]->estatus == 0){
                                    $clientes[$key]->estatus = "Aprobada";
                                }elseif($clientes[$key]->estatus == 1){
                                    $clientes[$key]->estatus = "Por Aprobar";
                                }elseif($clientes[$key]->estatus == 2){
                                    $clientes[$key]->estatus = "Cancelada";
                                }elseif($clientes[$key]->estatus == 3){
                                    $clientes[$key]->estatus = "Rechazada";
                                }elseif($clientes[$key]->estatus == 4){
                                    $clientes[$key]->estatus = "Reversada";
                                }
                                $clientes[$key]->consumos = number_format($clientes[$key]->consumos, 2, ',', '.');
                                $clientes[$key]->propinas = number_format($clientes[$key]->propinas, 2, ',', '.');
                            }

                           Excel::create('Reporte Totalizado de Clientes desde '.$fecha_desde.' hasta '.$fecha_hasta.' '.$clientes[0]->descripcion,function($excel) use($clientes){
                                $excel->sheet('Operaciones', function($sheet) use($clientes) {
                                    $sheet->setOrientation('lanscape');
                                    $sheet->fromArray($clientes);
                                });
                            })->export('xls');
                    }


        }catch(\Exception $e){
            flash(' '.$e, '¡Alert!')->error();
        }

    }
    function checkCarnets($carnet){

        //dd($carnet);
        $ConsultaCarnet = carnet::where('carnet', $carnet)
        ->orWhere('carnet_real', '=', $carnet)
        ->first();
        
        if($ConsultaCarnet){
           return response()->json(true,200);

        }else{

            return response()->json(false,200);
        }

    }//END FUNCTION CHECK CARNETS

    function checkCarnetReal($carnet){

        //dd($carnet);
        $ConsultaCarnetReal = carnet::where('carnet_real', $carnet)
        ->orWhere('carnet', '=', $carnet)
        ->first();
        
        if($ConsultaCarnetReal){
           return response()->json(true,200);

        }else{

            return response()->json(false,200);
        }
        
    }//END FUNCTION CHECK CARNET REAL

    function checkEmail($email){

        
        $ConsultaEmail = User::where(DB::raw('LOWER(email)'), strtolower($email))->first();
 
        if($ConsultaEmail){
           return response()->json(true,200);

        }else{

            return response()->json(false,200);
        }
        
    }//END FUNCTION CHECK EMAIL

    function checkCarnetEdit($carnet, $id){
    
        $ConsultaCarnet = carnet::where('carnet','=', $carnet)
        ->where('fk_id_miembro', '!=', $id)
        ->first();       

        if($ConsultaCarnet){
           return response()->json(true,200);
        }else{ 
            return response()->json(false,200);
        }

    }//END FUNCTION CHECK CARNETS EDIT

    function checkCarnetRealEdit($carnet, $id){
    
        $ConsultaCarnet = carnet::where('carnet_real','=', $carnet)
        ->where('fk_id_miembro', '!=', $id)
        ->first();

        if($ConsultaCarnet){
           return response()->json(true,200);
        }else{ 
            return response()->json(false,200);
        }

    }//END FUNCTION CHECK CARNETS REAL EDIT

      function checkCodClientEmisor($codClientEmisor, $id){
   
        $ConsultaCodClientEmisor = carnet::where('fk_id_miembro','!=', $id)
        ->where('cod_cliente_emisor', '=', $codClientEmisor)
        ->first();

        if($ConsultaCodClientEmisor){
           return response()->json(true,200);
        }else{ 
            return response()->json(false,200);
        }

    }//END FUNCTION CHECK CARNETS REAL EDIT   

    function checkCodClientEmisorCreate($codClientEmisor){
   
        $ConsultaCodClientEmisor = carnet::where('cod_cliente_emisor', '=', $codClientEmisor)
        ->first();
        //dd($ConsultaCodClientEmisor);

        if($ConsultaCodClientEmisor){
           return response()->json(true,200);
        }else{ 
            return response()->json(false,200);
        }

    }//END FUNCTION CHECK CARNETS REAL EDIT 

    function checkEmailEdit($email, $id){

        
        $ConsultaEmail = User::where(DB::raw('LOWER(email)'), strtolower($email))
        ->where('id', '!=', $id)->first();

        
 
        if($ConsultaEmail){
           return response()->json(true,200);

        }else{

            return response()->json(false,200);
        }
        
    }//END FUNCTION CHECK EMAIL

}
