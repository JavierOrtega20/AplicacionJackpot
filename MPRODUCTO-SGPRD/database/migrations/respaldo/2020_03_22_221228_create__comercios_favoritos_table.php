<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComerciosFavoritosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comercios_favoritos', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('fk_dni_miembros');
			$table->unsignedInteger('fk_id_comer');
            $table->timestamps();
			
            $table->foreign('fk_dni_miembros')->references('id')->on('users');
            $table->foreign('fk_id_comer')->references('id')->on('comercios');			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comercios_favoritos');
    }
}
