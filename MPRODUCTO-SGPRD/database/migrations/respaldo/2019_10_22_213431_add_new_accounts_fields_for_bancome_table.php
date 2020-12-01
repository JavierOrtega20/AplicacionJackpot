<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewAccountsFieldsForBancomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banc_comer', function (Blueprint $table) {
            $table->string('num_cta_princ_dolar', 20)->nullable()->default('');
            $table->string('num_cta_secu_dolar', 20)->nullable()->default('');
            $table->string('num_cta_princ_euro', 20)->nullable()->default('');
            $table->string('num_cta_secu_euro', 20)->nullable()->default('');

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
                $table->dropColumn('num_cta_princ_dolar');
                $table->dropColumn('num_cta_secu_dolar');
                $table->dropColumn('num_cta_princ_euro');
                $table->dropColumn('num_cta_secu_euro');

        });
    }
}
