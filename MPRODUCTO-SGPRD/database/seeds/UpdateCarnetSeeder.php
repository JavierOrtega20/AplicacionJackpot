<?php

use Illuminate\Database\Seeder;
use App\Models\carnet;

class UpdateCarnetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         carnet::where('id', '>=', '1')->update(['fk_monedas' => 2]);
    }
}
