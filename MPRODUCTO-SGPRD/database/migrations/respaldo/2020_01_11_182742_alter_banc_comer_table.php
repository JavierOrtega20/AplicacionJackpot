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
            $table->decimal('latitud',10,10)->default(0)->after('num_cta_secu');
			$table->decimal('longitud',10,10)->default(0)->after('num_cta_secu');
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
