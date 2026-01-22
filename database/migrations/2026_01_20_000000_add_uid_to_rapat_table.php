<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUidToRapatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rapat', function (Blueprint $table) {
            $table->string('uid', 6)->nullable()->unique()->after('id');
        });

        // Generate UIDs for existing meetings
        $meetings = DB::table('rapat')->whereNull('uid')->get();
        
        foreach ($meetings as $meeting) {
            $uid = $this->generateUniqueUid();
            DB::table('rapat')->where('id', $meeting->id)->update(['uid' => $uid]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rapat', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }

    /**
     * Generate a unique 6-character alphabetic UID
     *
     * @return string
     */
    private function generateUniqueUid()
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $uid = '';
            for ($i = 0; $i < 6; $i++) {
                $uid .= $alphabet[rand(0, strlen($alphabet) - 1)];
            }
            $exists = DB::table('rapat')->where('uid', $uid)->exists();
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        if ($attempts >= $maxAttempts) {
            // Fallback: use timestamp-based approach if too many collisions
            $uid = substr(str_shuffle($alphabet), 0, 6);
            $counter = 1;
            while (DB::table('rapat')->where('uid', $uid)->exists()) {
                $uid = substr(str_shuffle($alphabet), 0, 5) . substr($counter, -1);
                $counter++;
            }
        }

        return $uid;
    }
}

