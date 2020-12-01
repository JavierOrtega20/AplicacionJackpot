<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\bancos;
use App\Http\Requests\BancoRequest;

class BancoController extends Controller
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
    public function index()
    {
        $bancos = bancos::select('bancos.*')
        ->get();

        return view('bancos.index')->with(['bancos' => $bancos]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Log::info('Ingreso exitoso a BancoController - create(), del usuario: '.Auth::user()->first_name);

        $bancos = bancos::select('bancos.*')
        ->get();

        return view('bancos.create')->with([
            'bancos'       => $bancos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BancoRequest $request)
    {
        try{

            $existing_bank = bancos::select('*')
            ->where('rif',$request->rif)
            ->first();

            if($existing_bank){
                flash('Banco ya registrado en la plataforma.', '¡Alert!')->error();
                return back();
            }else{
                $bancos = new \App\Models\bancos($request->all());


                // dd($bancos);
                if( $bancos->save()){

                    $data= $bancos;
        flash('Se ha realizado la operacion solicitada exitosamente.', '¡Operación Exitosa!')->success();

                }

            }
        }catch (\Exception $e) {
            DB::rollBack();
            flash('El banco no se pudo registrar, intente más tarde.', '¡Alert!')->error();
        }

        return redirect()->route('bancos.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Log::info('Ingreso exitoso a BancoController - show(), del usuario: '.Auth::user()->email);

         $bancos = bancos::select('bancos.*')
        ->where('id','=',$id)
        ->get();
        // dd($members_clubs);
        // $members_departments = MemberDepartment::select('members_departments.*')
        // ->where('members_departments.fk_id_member', $id)
        // ->first();

        return response()->json([
            'data'      => $bancos
        ],200);

        // return view('bancos.index')->with(['bancos' => $bancos]);


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Log::info('Ingreso exitoso a BancoController - edit(), del usuario: '.Auth::guard('user')->user()->first_name);


        $bancos = bancos::find($id);

    // dd($bancos);

        return view('bancos.edit')
        ->with([
            'bancos' => $bancos
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BancoRequest $request, $id)
    {


        try{

            $bancos = bancos::where('id',$id)
            ->update([
                'descripcion'   => $request -> descripcion,
                'telefono1'    => $request -> telefono1,
                'telefono2'     => $request -> telefono2,
                'rif'     => $request -> rif,
                'contacto'         => $request -> contacto,
            ]);

        flash('Se ha realizado la operacion solicitada exitosamente.', '¡Operación Exitosa!')->success();


        }catch(\Exception $e) {
            DB::rollBack();
            flash('El banco no se pudo registrar intente mas tarde.', '¡Alert!')->error();
        }

        return redirect()->route('bancos.index');

    }

    public function mod_banco(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     //
    // }
}
