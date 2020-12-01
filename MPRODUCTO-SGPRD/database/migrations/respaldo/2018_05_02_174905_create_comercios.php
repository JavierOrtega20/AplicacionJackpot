<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComercios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comercios', function (Blueprint $table) {
            $table->increments('id');
            $table->text('descripcion');
            $table->text('direccion');
            $table->string('telefono1',85)->nullable();
            $table->string('telefono2',85)->nullable();
            $table->string('rif',20)->unique();
            $table->string('email',100);
            $table->string('razon_social',100);
            $table->string('codigo_afi_come',100)->nullable();
            $table->boolean('propina_act')->default(false);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comercios');
    }
}
