<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterComerciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comercios', function (Blueprint $table) {
			$table->integer('estatus')->default(2);
			$table->integer('estatus_motivo')->nullable();
			$table->string('calle_av')->nullable();
			$table->string('casa_edif_torre')->nullable();
			$table->string('local_oficina')->nullable();
			$table->string('urb_sector')->nullable();
			$table->string('ciudad')->nullable();
			$table->integer('estado')->default(0);
            $table->uuid('fk_id_subcategoria')->nullable();
			
			$table->foreign('fk_id_subcategoria')->references('id')->on('comercios_subcategoria');			
        });			
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
