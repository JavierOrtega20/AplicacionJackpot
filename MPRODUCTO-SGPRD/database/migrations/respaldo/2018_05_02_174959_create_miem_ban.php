<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMiemBan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('miem_ban', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fk_dni_miembro');
            $table->unsignedInteger('fk_id_banco');
            $table->date('fecha_afili')->nullable();
            $table->decimal('credito_apro',10,2);
            $table->unsignedInteger('fk_id_limite');
            $table->decimal('credito_disp',10,2);
            /*
            estos dos campos pasarlos a la tabla trans_head
            $table->decimal('tasa_cobro_prop',10,2)->nullable();
            $table->decimal('monto_prop',10,2)->nullable(); 
            */
            $table->string('nro_cta_propina',20)->nullable();
            $table->decimal('tasa_cobro_cliente',10,2)->nullable();            
            $table->timestamps();
            
            $table->foreign('fk_dni_miembro')->references('id')->on('users');
            $table->foreign('fk_id_banco')->references('id')->on('bancos');
            $table->foreign('fk_id_limite')->references('id')->on('limite_cre');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miem_ban');
    }
}
