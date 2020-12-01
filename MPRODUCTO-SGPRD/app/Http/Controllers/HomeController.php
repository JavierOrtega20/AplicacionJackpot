<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\PasswordRequest;
use App\Models\User;
use Carbon\Carbon;
use App\Models\miem_come;
use App\Models\miem_ban;
use App\Models\comercios;
use Auth;
use Hash;

class HomeController extends Controller
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
    

    public function password()
    {
        $user = User::find(Auth::user()->id);
		
		$roles= $user->roles;
		$rol = null;
		foreach ($roles as $value) {
			$rol = $value->id;
		}
        
        return view('auth.passwords.change')
		->with([
            'user'         => $user
        ]);
    }

     public function dataUpdate()
    {
        $user = User::find(Auth::user()->id);

        $comercio = miem_come::select('comercios.*', 'miem_come.*')
        ->join('comercios','comercios.id','miem_come.fk_id_comercio')
        ->Where('miem_come.fk_id_miembro', '=', $user->id)
        ->get();

        //dd($comercio);

        return view('auth.dataUpdate')->with('comercio',$comercio);
    }


    public function changePassword(PasswordRequest $request)
    {
        try{

            $input = $request->all();
            $user = User::find(Auth::user()->id);

            $roles= $user->roles;
            $rol = null;
            foreach ($roles as $value) {
                $rol = $value->id;
            }

            
           
            
             $lastpass = Hash::check($input['password'], $user->password);
             if ($lastpass== true) {
                $check = false;
             }else{
                $check = true;
             }
             

             if ($check) {

                if($rol!=3){
                    if(!empty($input['password'])){ 
                        $input['password'] = Hash::make($input['password']);
                    }else{
                        $input = array_except($input,array('password'));    
                    }

                
                    $user->update([
                        'password'      => $input['password'],
                        'setup'         => Carbon::now(),
                        ]);

                    return redirect()->route('home');

                }else{
                    $miem_come = miem_come::where('fk_id_miembro', $user->id)->first();
                    $comercio = comercios::where('id' , $miem_come->fk_id_comercio)->withTrashed()->first();
					
					if($comercio->estado_afiliacion_comercio != null)
					{
						if ($input['checked']) {
						
							if(!empty($input['password'])){ 
								$input['password'] = Hash::make($input['password']);
							}else{
								$input = array_except($input,array('password'));    
							}
					
							$user->update([
								'password'      => $input['password'],
								'setup'         => Carbon::now(),
							]);

							$comercio->update([
								'estado_afiliacion_comercio'  => '1',
								'deleted_at' => null,
								'estatus' => 1,
								'aceptacion_contrato'  => date("Y-m-d H:i:s"),
							]);

							$comercio->restore();
							
							return redirect('dataUpdate');
						}
					}
					else{
						if(!empty($input['password'])){ 
							$input['password'] = Hash::make($input['password']);
						}else{
							$input = array_except($input,array('password'));    
						}

					
						$user->update([
							'password'      => $input['password'],
							'setup'         => Carbon::now(),
							]);

						return redirect()->route('home');						
					}
					//if input checked
                }//else             
             }//if check

             if ($check == false) {
                 return redirect()->route('home')->with('error','La clave debe ser diferente a la anterior.');
             }

        
        }catch (\Exception $e) {
        DB::rollBack();
        }
    }

     public function update(Request $request, $id)
    {
      try{
        
        if($request->checkDir){
             return redirect()->route('home')->with('status','ok');
        }else{
			$comercio = comercios::where('id' , $id)->withTrashed()->first();
			$comercio->update([
            'estado'=> $request->estad,
            'ciudad'=> $request->ciudad,
            'direccion'=> $request->direccion,
            'calle_av'=> $request->calle,
            'casa_edif_torre'=> $request->casa,
            'local_oficina'=> $request->local,
            'urb_sector'=> $request->sector,
            'telefono1'=> $request->telefono
            ]);
            return redirect()->route('home')->with('status','Upok');
             
        }       
        
        
      }catch(\Exception $e){

          return response()->json($e);


      }
    }

  

}
