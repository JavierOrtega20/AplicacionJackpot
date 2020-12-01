<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBancComerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banc_comer', function (Blueprint $table) {
             $table->decimal('tasa_cobro_comer_dolar',10,2)->nullable();
             $table->decimal('tasa_cobro_comer_euro',10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banc_comer', function (Blueprint $table) {
             $table->dropColumn('tasa_cobro_comer_dolar');
              $table->dropColumn('tasa_cobro_comer_euro');
        });
    }
}
