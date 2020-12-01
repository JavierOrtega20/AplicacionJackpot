<?php

use Illuminate\Database\Seeder;
use App\Models\trans_head;

class Trans_HeadTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        trans_head::where('id', '>=', '1')->update(['fk_monedas' => 1]);
    }
}
