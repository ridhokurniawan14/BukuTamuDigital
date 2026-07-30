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
        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            // Identitas
            $table->string('nama_instansi')->default('Buku Tamu Digital');
            $table->string('singkatan_instansi')->default('BTD');
            $table->string('logo_instansi')->nullable();
            $table->string('favicon')->nullable();
            $table->string('gambar_background')->nullable(); // Latar belakang halaman depan
            $table->string('warna_utama')->default('#f59e0b'); // Default warna Amber Filament

            // Informasi Laporan
            $table->text('alamat_instansi')->nullable();

            // Preferensi Sistem
            $table->text('pesan_sambutan')->nullable();
            $table->boolean('wajib_foto')->default(true);
            $table->boolean('wajib_ttd')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturans');
    }
};
