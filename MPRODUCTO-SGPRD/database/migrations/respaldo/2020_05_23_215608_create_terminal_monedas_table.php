<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTerminalMonedasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terminal_monedas', function (Blueprint $table) {
			
            $table->increments('id');
			
			$table->uuid('TerminalId')->default(DB::raw('uuid_generate_v4()'))->nullable();
			
            $table->integer('fk_moneda')->unsigned()->nullable();

            $table->foreign('fk_moneda')->references('mon_id')->on('monedas');
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('terminal_monedas');
    }
}
