<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCanalComerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canal_comer', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('fk_id_comer')->nullable();
			$table->uuid('fk_id_canal')->nullable();
			$table->integer('num_terminales')->nullable();
	
            $table->timestamps();						
			
			$table->foreign('fk_id_comer')->references('id')->on('comercios');
			$table->foreign('fk_id_canal')->references('id')->on('canal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('canal_comer');
    }
}
