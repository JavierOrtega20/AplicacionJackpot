<?php

namespace App\Http\Controllers;

use App\desplegable;
use Illuminate\Http\Request;
use App\Models\estados;
use DB as DB;

class DesplegablesController extends Controller
{
   public function divisas()
   {
      $divisas = DB::table('monedas')->where('mon_status','=','ACTIVO')->orderBy('mon_nombre', 'asc')->get();
      return response()->json($divisas);
   }

   public function estados()
   {
       $estado = DB::table('estados')->where('id','>','0')->orderBy('nombre', 'asc')->get();
       return response()->json($estado);
      
   }
}
