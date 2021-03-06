<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dni',40);
            $table->string('nacionalidad',5)->nullable();
            $table->string('first_name',40)->nullable();
            $table->string('last_name',40)->nullable();
            $table->string('image',100)->nullable();
            $table->string('cod_tel',5);
            $table->string('num_tel',7);
            $table->string('email',100);
            $table->string('password',255);
            $table->boolean('kind');
            $table->date('birthdate')->nullable();
            $table->boolean('rif')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->date('setup')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
