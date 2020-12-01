<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonedasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monedas', function (Blueprint $table) {
            $table->increments('mon_id');
            $table->string('mon_nombre')->unique()->required();
            $table->string('mon_simbolo')->unique()->required();
            $table->integer('user_id')->unsigned();
            $table->string('mon_status')->required();
            $table->string('mon_observaciones')->required();
            $table->timestamps();

            // FK
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('monedas');

    }
}
