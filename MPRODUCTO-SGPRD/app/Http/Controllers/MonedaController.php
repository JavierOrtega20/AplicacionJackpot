<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Moneda as Monedas;
use App\Models\User;
use App\Models\Role;
use App\Models\bancos;
use App\Models\comercios;
use App\Models\carnet;
use App\Models\miem_come;
use App\Models\miem_ban;
use App\Models\banc_comer;
use App\Models\Errors;
use App\Models\Ledge;
use App\Http\Requests\UsersRequest;
use App\Http\Requests\UsersEditRequest;
use Carbon\Carbon;
use DB;
use Hash;
use Excel;
use Storage;
use Illuminate\Support\Facades\Input;

class MonedaController extends Controller
{
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function index()
   {
     foreach (Auth::user()->roles as $v){
         $rolUser = $v->id;
     }
       return view('monedas.index', compact('rolUser'))->with('status', 'new');
       //prueba de push
   }

   /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function create()
   {
       //
   }

   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function store(Request $request)
   {
       try {
         $moneda = new Monedas;

         $moneda->mon_nombre= trim(strtoupper($request->divisa));
         $moneda->mon_simbolo= trim(strtoupper($request->simbolo));
         $moneda->mon_status= "ACTIVO";
         $moneda->user_id= Auth::id();
         $moneda->mon_observaciones= trim(strtoupper($request->descripcion));

         $moneda->save();


         return redirect('create/monedas')->with('status', 'ok');

       } catch (\Exception $e) {

         if($e->errorInfo[0] == "23505"){
         return redirect('create/monedas')->with('status', 'duplicado');
       }//end if
       else{
         return redirect('create/monedas')->with('status', 'error');
       }//end else

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
     $monedas = Monedas::where('mon_id', $id)->get();
     return response()->json([
       'data' => $monedas
     ]);
   }

   /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function list()
   {
     $monedas = Monedas::all();

     return view('monedas.list')->with('monedas', $monedas);
   }


   /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
   public function edit($id)
   {
     $monedas = Monedas::find($id);
     //dd($monedas);
     return view('monedas.edit')->with('monedas', $monedas)->with('status','new');
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
         $monedas = Monedas::where('mon_id',$id)
         ->update([
             'mon_nombre'   => trim(strtoupper($request -> mon_nombre)),
             'mon_simbolo'    => trim(strtoupper($request -> mon_simbolo)),
             'mon_observaciones'     => trim(strtoupper($request -> mon_observaciones)),
         ]);

     }catch(\Exception $e) {
         DB::rollBack();
         if($e->errorInfo[0] == "23505"){
         return redirect('list/monedas')->with('status', 'duplicado');
       }//end if
       else{
           return redirect('list/monedas')->with('status','error');
       }

     }

     return redirect('list/monedas')->with('status','ok');
   }

   public function activar($id){
       $monedas = Monedas::find($id);
       $monedas->update([
               'mon_status' => "ACTIVO",
           ]);

       return redirect('list/monedas')->with('status','activo');
   }

   public function desactivar($id){
           $monedas = Monedas::find($id);
           $monedas->update([
               'mon_status' => "INACTIVO",
           ]);

       return redirect('list/monedas')->with('status','inactivo');
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

   public function consultaDatos($cedula, $comercio)
   {
    $user= User::find(Auth::user()->id);


        $roles= $user->roles;
        $rol = null;
        foreach ($roles as $value) {
            $rol = $value->id;
        }

        if($rol == 3){

          $comercio= miem_come::where('fk_id_miembro', $user->id)->get()[0]->fk_id_comercio;        
        }
        
    $comer = banc_comer::where('fk_id_comer',$comercio)->get()[0];


    $usuario = User::select('carnet.fk_id_miembro')->join('carnet','carnet.fk_id_miembro','users.id')
    ->where('dni', $cedula)
    ->first();

    $usuario_carnets = carnet::where('fk_id_miembro',$usuario->fk_id_miembro)->whereRaw("COALESCE(cod_emisor, '') <> 'INTICARD001'")->get();

    $usuario_monedas = [];
     
    $comer_monedas = [
      'BOLIVAR'
    ];
     if($comer->num_cta_princ_dolar != '' || $comer->num_cta_secu_dolar != ''){
       array_push($comer_monedas, 'DOLAR');
     }
     if($comer->num_cta_princ_euro != '' || $comer->num_cta_secu_euro != ''){
       array_push($comer_monedas, 'EURO');
     }
      foreach ($usuario_carnets as $key => $carnet) {

       if (in_array($carnet->moneda->mon_nombre, $comer_monedas) && $carnet->moneda->mon_status == 'ACTIVO') {
         array_push($usuario_monedas, $carnet->moneda);      

       } 
     } 

     return response()->json([
       "data" => $usuario_monedas
     ]);
   }
}
