<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Excel;
use App\Models\User;
use App\Models\comercios;
use App\Models\Role;

class ExportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function UserExcel()
    {
		foreach (Auth::user()->roles as $v){
			$rolUser = $v->id;
		}		
		
		if($rolUser == 6)
			$FileName = 'Clientes';		
		else
			$FileName = 'Usuarios';
		
        Excel::create($FileName , function($excel){
			foreach (Auth::user()->roles as $v){
				$rolUser = $v->id;
			}		
			
			if($rolUser == 6)
				$FileName = 'Clientes';		
			else
				$FileName = 'Usuarios';
            $excel->sheet($FileName, function($sheet){

                foreach (Auth::user()->roles as $v){
                    $rolUser = $v->id;
                }
                    if($rolUser ==1 || $rolUser ==2){
                        $data = User::select("dni", "nacionalidad", "first_name", "last_name", "cod_tel", "num_tel", "email", "created_at", "updated_at")->with('roles')->whereHas('roles', function($q){
                           $q->where('id', '!=', 5);
                        })->orderBy('first_name','asc')->withTrashed()->get();                        
                        }else{
                        $data = User::select("dni", "nacionalidad", "first_name", "last_name", "cod_tel", "num_tel", "email", "created_at", "updated_at")->with('roles')->whereHas('roles', function($q) {
                           $q->where('id', '=', 5);
                        })->orderBy('first_name','asc')->withTrashed()->get();                        
                        }

                        
                        $sheet->fromArray($data);
                    });
                })->export('xls');
    }//END USERS EXCEL DOWNLOAD

    public function ComercioExcel()
    {

        Excel::create('Comercios' , function($excel){

            $excel->sheet('Comercios', function($sheet){

                $comercios = comercios::select('comercios.*', 'banc_comer.*')
                    ->withTrashed()
                    ->join('banc_comer','banc_comer.fk_id_comer','comercios.id')
                    ->where('comercios.razon_social','!=','jackpotImportPagos')
                    ->orderBy('descripcion','asc')->withTrashed()->get();  
                $sheet->fromArray($comercios);
            });
        })->export('xls');
    }//END USERS EXCEL DOWNLOAD



}//END EXPORT CONTROLLER
