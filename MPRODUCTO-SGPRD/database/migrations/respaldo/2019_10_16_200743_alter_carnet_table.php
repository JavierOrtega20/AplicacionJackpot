<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCarnetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carnet', function (Blueprint $table) {
            $table->integer('fk_monedas')->unsigned()->nullable()->after('fk_id_banco');

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
        Schema::table('carnet', function (Blueprint $table) {
            //
        });
    }
}
