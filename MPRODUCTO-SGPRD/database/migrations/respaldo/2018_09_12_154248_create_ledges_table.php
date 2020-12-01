<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fk_id_trans_head');
            $table->unsignedInteger('fk_dni_miembros');
            $table->decimal('monto',10,2)->default(0);
            $table->decimal('propina',10,2)->default(0);
            $table->decimal('disp_pre',10,2)->default(0);
            $table->decimal('disp_post',10,2)->default(0);
            $table->timestamps();

            $table->foreign('fk_id_trans_head')->references('id')->on('trans_head');            
            $table->foreign('fk_dni_miembros')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger');
    }
}
