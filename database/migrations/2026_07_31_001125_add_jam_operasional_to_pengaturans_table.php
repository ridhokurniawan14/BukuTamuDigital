<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengaturans', function (Blueprint $table) {
            $table->time('jam_buka')->default('06:00:00')->nullable()->after('wajib_ttd');
            $table->time('jam_tutup')->default('18:00:00')->nullable()->after('jam_buka');
            $table->json('hari_kerja')->nullable()->after('jam_tutup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturans', function (Blueprint $table) {
            //
        });
    }
};
