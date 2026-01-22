<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitKerjaUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('unit_kerja_user', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_model_id')->unsigned(); // Matches UserModel ID
        $table->integer('unit_kerja_model_id')->unsigned(); // Matches UnitKerjaModel ID
        $table->integer('tahun'); // 'tahun' for the year (e.g., 2026)

        // Indexing these makes searches much faster
        $table->index(['user_model_id', 'tahun']);

        // Foreign keys to keep data clean
        // $table->foreign('user_model_id')->references('id')->on('users')->onDelete('cascade');
        // $table->foreign('unit_kerja_model_id')->references('id')->on('unit_kerja')->onDelete('cascade');
    });

    // MOVE OLD DATA: This copies your current team_id into the new table
    $users = DB::table('users')->whereNotNull('unit_kerja')->get();
    foreach ($users as $user) {
        DB::table('unit_kerja_user')->insert([
            'user_model_id' => $user->id,
            'unit_kerja_model_id' => $user->unit_kerja,
            'tahun' => 2025 // Or date('Y') for the current year
        ]);
    }
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
