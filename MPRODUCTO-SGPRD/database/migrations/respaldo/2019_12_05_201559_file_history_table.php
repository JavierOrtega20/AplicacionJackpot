<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FileHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files_history', function (Blueprint $table) {
            $table->increments('id');
			$table->text('Filename');
			$table->unsignedInteger('ProcessType');
			$table->text('email')->nullable();
			$table->integer('user_id')->nullable();
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
		Schema::dropIfExists('files_history');
    }
}
