<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class Comercioseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('comercios')->insert([
				/*[
					'descripcion' => 'La Esquina',
					'direccion' => '3ra Transversal entre Av. Luis Roche y Av. San Juan Bosco Hotel VIP Altamira, Caracas - Altamira - Caracas',
					'telefono1' => '04242023164',
					'telefono2' => null,
					'rif' => 'J-1234567891',
					'email' => 'info@laesquina.com',
					'razon_social' => 'La Esquina del Este',
					'propina_act' => 1,
					'codigo_afi_come' => '123456789123',
					'created_at' => Carbon::now(),
           			'updated_at' => Carbon::now(),
				],
				[
					'descripcion' => "D'tapas Bar",
					'direccion' => "Calle La Paz, D'Tapas Bar (a 1 Cuadra de la Plaza Bolívar), El Hatillo, Caracas",
					'telefono1' => '04242023164',
					'telefono2' => '02122512121',
					'rif' => 'J-2345678915',
					'email' => 'info@dtapas.com',
					'razon_social' => 'Tapas Españolas & Bar',
					'propina_act' => 0,
					'codigo_afi_come' => '987654321987',
					'created_at' => Carbon::now(),
            		'updated_at' => Carbon::now(),
				],*/
				[
					'id'		=>	3,
					'descripcion' => "jackpotImportPagos",
					'direccion' => "DIRECCION DE TRANSITO, SEDES DE LA DIV. DE CAPACITACION, SECTOR ESTE DEL AREA METROPOLITANA DE CARACAS Y EL SVTT AUTOPISTAS CARACAS",
					'telefono1' => '04242023163',
					'telefono2' => '02122512122',
					'rif' => 'J-0000000000',
					'email' => 'jackpotImportPagos@dtapas.com',
					'razon_social' => 'jackpotImportPagos',
					'propina_act' => 0,
					'codigo_afi_come' => '000000000000',
					'created_at' => Carbon::now(),
            		'updated_at' => Carbon::now(),
				]
			]);

        DB::table('miem_ban')->insert([
        	[
                'fk_id_banco' => 1,
                'fk_dni_miembro' => 2,
                'credito_apro' => 0,
                'credito_disp' => 0,
                'fk_id_limite' => 3,
                'created_at' => Carbon::now(),
            	'updated_at' => Carbon::now(),
            ],
            [
                'fk_id_banco' => 1,
                'fk_dni_miembro' => 3,
                'credito_apro' => 0,
                'credito_disp' => 0,
                'fk_id_limite' => 3,
                'created_at' => Carbon::now(),
            	'updated_at' => Carbon::now(),
            ],
            [
            	'fk_id_banco' => 1,
                'fk_dni_miembro' => 4,
                'credito_apro' => 0,
                'credito_disp' => 0,
                'fk_id_limite' => 3,
                'created_at' => Carbon::now(),
            	'updated_at' => Carbon::now(),
            ]
        ]);

        /*DB::table('banc_comer')->insert([
				[
					'fk_id_banco' => 1,
					'fk_id_comer' => 1,
					'tasa_cobro_banco' => 0,
					'tasa_cobro_comer' => 5,
					'num_cta_princ' => '01820220202020202020',
					'num_cta_secu' => '01236547999999999999',
					'created_at' => Carbon::now(),
           			'updated_at' => Carbon::now(),
				],
				[
					'fk_id_banco' => 1,
					'fk_id_comer' => 2,
					'tasa_cobro_banco' => 0,
					'tasa_cobro_comer' => 2,
					'num_cta_princ' => '12120504789822220145',
					'num_cta_secu' => null,
					'created_at' => Carbon::now(),
           			'updated_at' => Carbon::now(),
				]
			]);

	        DB::table('miem_come')->insert([
	        	[
	                'fk_id_comercio' => 1,
	                'fk_id_miembro' => 5,
	                'created_at' => Carbon::now(),
            		'updated_at' => Carbon::now(),
	            ],
	            [
	            	'fk_id_comercio' => 2,
                	'fk_id_miembro' => 6,
                	'created_at' => Carbon::now(),
            		'updated_at' => Carbon::now(),
		        ]
		    ]);*/

    }
}
