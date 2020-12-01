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
            $table->uuid('TransId')->default(DB::raw('uuid_generate_v4()'))->after('updated_at');
			$table->uuid('CanalId')->default(null)->after('updated_at')->nullable();;
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
