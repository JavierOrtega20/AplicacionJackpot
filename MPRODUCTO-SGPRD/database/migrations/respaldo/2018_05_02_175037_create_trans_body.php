<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransBody extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
    {
        Schema::create('trans_body', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('linea');
            $table->unsignedInteger('fk_id_head');
            $table->unsignedInteger('fk_dni_miembro');
            $table->unsignedInteger('fk_id_banco');
            $table->unsignedInteger('fk_id_comer');
            $table->decimal('precio_uni',10,2);            
            $table->timestamps();

            $table->foreign('fk_id_head')->references('id')->on('trans_head');
            $table->foreign('fk_dni_miembro')->references('id')->on('users');
            $table->foreign('fk_id_banco')->references('id')->on('bancos');
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
        Schema::dropIfExists('trans_body');
    }
}
