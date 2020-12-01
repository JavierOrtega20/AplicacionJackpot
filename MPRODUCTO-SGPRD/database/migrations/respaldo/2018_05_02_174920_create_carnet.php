<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarnet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carnet', function (Blueprint $table) {
            $table->increments('id');
            $table->string('carnet',40);
            $table->decimal('limite',10,2)->default(0);
            $table->unsignedInteger('fk_id_miembro');
            $table->unsignedInteger('fk_id_banco');
            $table->timestamps();
            
            $table->foreign('fk_id_miembro')->references('id')->on('users');
            $table->foreign('fk_id_banco')->references('id')->on('bancos');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carnet');
    }
}
