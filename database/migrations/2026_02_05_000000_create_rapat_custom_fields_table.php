<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRapatCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rapat_custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rapat_id');
            $table->string('field_key', 255);
            $table->text('field_value');
            $table->integer('field_order')->default(0);
            $table->timestamps();
            
            // Index for better query performance
            $table->index('rapat_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rapat_custom_fields');
    }
}
