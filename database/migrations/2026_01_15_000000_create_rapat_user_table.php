<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRapatUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rapat_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rapat_id')->unsigned();
            $table->integer('attendee_id')->unsigned();
            $table->string('attendee_type'); // 'App\UserModel' or 'App\UnitKerjaModel'
            
            // Indexing for better performance
            $table->index(['rapat_id']);
            $table->index(['attendee_id', 'attendee_type']);
            
            // Foreign key to rapat table
            // $table->foreign('rapat_id')->references('id')->on('rapat')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rapat_user');
    }
}

