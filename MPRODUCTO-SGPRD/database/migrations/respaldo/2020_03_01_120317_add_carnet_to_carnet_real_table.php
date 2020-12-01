<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCarnetToCarnetRealTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carnet', function (Blueprint $table) {
            $table->string('carnet_real')->nullable()->after('carnet');
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
            $table->dropColumn('carnet_real');
        });
    }
}
