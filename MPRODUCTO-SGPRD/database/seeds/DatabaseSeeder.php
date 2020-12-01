<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(AdminSeeder::class);
        //$this->call(Bancoseeder::class);
        //$this->call(Comercioseeder::class);
        //$this->call(MonedaSeeder::class);
        $this->call(UpdateCarnetSeeder::class);
        //$this->call(Trans_HeadTableSeeder::class);
    }
}
