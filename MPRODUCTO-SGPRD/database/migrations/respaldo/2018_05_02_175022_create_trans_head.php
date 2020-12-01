<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransHead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trans_head', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('referencia')->nullable();
            $table->unsignedInteger('fk_dni_miembros');
            $table->unsignedInteger('fk_id_banco');
            $table->unsignedInteger('fk_id_comer');
            $table->decimal('monto',10,2)->default(0);
            $table->decimal('propina',10,2)->default(0);
            $table->decimal('comision',10,2)->default(0);
            $table->decimal('neto',10,2)->default(0);
            $table->decimal('monto_propina',10,2)->default(0);
            $table->decimal('porc_propina',10,2)->default(0);
            $table->boolean('cancela_a')->default(false);
            $table->string('token');
            $table->integer('reverso')->nullable();
            $table->integer('status')->nullable();
            $table->string('procesado')->nullable();
            $table->string('ip')->nullable();
            $table->integer('token_status')->nullable();
            $table->dateTime('token_time')->nullable();
            $table->timestamps();
            
            $table->foreign('fk_dni_miembros')->references('id')->on('users');
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
        Schema::dropIfExists('trans_head');
    }
}
