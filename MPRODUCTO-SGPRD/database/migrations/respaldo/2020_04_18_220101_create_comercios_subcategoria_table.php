<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComerciosSubcategoriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comercios_subcategoria', function (Blueprint $table) {
			$table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
			$table->string('Nombre',150);
            $table->uuid('fk_id_categoria')->nullable();
									
            $table->timestamps();
			
			$table->foreign('fk_id_categoria')->references('id')->on('comercios_categoria');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comercios_subcategoria');
    }
}
