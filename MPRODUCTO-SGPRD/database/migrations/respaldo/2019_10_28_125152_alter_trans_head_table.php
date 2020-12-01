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
            $table->integer('fk_monedas')->after('monto')->unsigned()->nullable();

            $table->foreign('fk_monedas')->references('mon_id')->on('monedas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trans_head', function (Blueprint $table) {
            //
        });
    }
}
