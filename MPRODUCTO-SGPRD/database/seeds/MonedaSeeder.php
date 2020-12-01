<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MonedaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moneda=
    [

       'BOLIVARES'=>
      [
        ['BOLIVAR', 'BS', 1, 'ACTIVO', 'CREACION DE MONEDA'],
      ],

      'DOLARES'=>
      [
        ['DOLAR', 'USD', 1, 'ACTIVO', 'CREACION DE MONEDA'],       
      ]
    ];
    $mon_id=1;
      foreach ($moneda as $monedas) {
	      foreach ($monedas as $mon) {
		        DB::table('monedas')->insert(
               [
	              'mon_nombre' =>$mon[0],
                  'mon_simbolo' =>$mon[1],
                  'user_id' =>$mon[2],
                  'mon_status' =>$mon[3],
                  'mon_observaciones' =>$mon[4],
	              'created_at' =>  Carbon::now(),
	              'updated_at' =>  Carbon::now(),
	 	            ]
            );
	      }
	     $mon_id++;
      }
    }
}
