<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBancComer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banc_comer', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fk_id_banco');
            $table->unsignedInteger('fk_id_comer');
            $table->decimal('tasa_cobro_banco',10,2);
            $table->decimal('tasa_cobro_comer',10,2);
            $table->string('num_cta_princ',20)->nullable();
            $table->string('num_cta_secu',20)->nullable();
            $table->timestamps();
            
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
        Schema::dropIfExists('banc_comer');
    }
}
