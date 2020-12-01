<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBancos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bancos', function (Blueprint $table) {
            $table->increments('id');
            $table->text('descripcion');
            $table->string('telefono1',85)->nullable();
            $table->string('telefono2',85)->nullable();
            $table->string('rif',20)->unique();
            $table->string('contacto',100);
            $table->string('codigo_afi_banc',100)->nullable();
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
        Schema::dropIfExists('bancos');
    }
}
