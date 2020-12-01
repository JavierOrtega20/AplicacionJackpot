<?php

use Illuminate\Database\Seeder;

class Bancoseeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bancos')->insert([
				[
					'id' => 1,
					'descripcion' => 'Banplus',
					'telefono1' => '02121234567',
					'telefono2' => '02121123456',
					'rif' => 'V123456789',
					'contacto' => 'Banplus',
				],
			]);
    }
}
