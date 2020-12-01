<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransHeadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trans_head', function (Blueprint $table) {
			$table->string('otp_bco')->nullable();
			$table->dateTime('otp_bco_time')->nullable();
			$table->string('ref_bco')->nullable();
			$table->string('trans_bco_time')->nullable();
			$table->string('reverse_bco_ref')->nullable();
			$table->string('reverse_bco_time')->nullable();				
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
