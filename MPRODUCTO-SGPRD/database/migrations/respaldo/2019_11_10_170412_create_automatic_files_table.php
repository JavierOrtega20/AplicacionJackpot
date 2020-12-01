<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutomaticFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automatic_files', function (Blueprint $table) {
            $table->increments('id');
			$table->text('Filename');
			$table->unsignedInteger('TotalRows')->default(0);
			$table->unsignedInteger('TotalProcessed')->default(0);
			$table->unsignedInteger('TotalErrors')->default(0);
			$table->unsignedInteger('ProcessType');
			$table->text('ErrorDetail')->nullable();
			$table->boolean('processed')->default(0);
			$table->boolean('InProgress')->default(0);
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
        Schema::dropIfExists('automatic_files');
    }
}
