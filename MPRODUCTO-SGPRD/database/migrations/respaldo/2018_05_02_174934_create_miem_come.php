<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiemCome extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('miem_come', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fk_id_miembro');
            $table->unsignedInteger('fk_id_comercio');
            $table->timestamps();
            
            $table->foreign('fk_id_miembro')->references('id')->on('users');
            $table->foreign('fk_id_comercio')->references('id')->on('comercios');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miem_come');
    }
}
