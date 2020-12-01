<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_trans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
			$table->integer('trans_id')->nullable();
			$table->text('username')->nullable();
            $table->text('accion')->nullable();
            $table->string('ip',20)->nullable();			
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
        Schema::dropIfExists('log_trans');
    }
}
