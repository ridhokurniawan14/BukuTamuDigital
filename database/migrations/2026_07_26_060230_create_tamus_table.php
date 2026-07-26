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
        Schema::create('tamus', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('asal_instansi')->nullable();
            $table->string('alamat');
            $table->string('no_hp');

            $table->string('kategori_keperluan')->nullable(); // Kategori (Dinas, Pribadi, Penawaran, dll)
            $table->string('keperluan'); // Deskripsi detail tujuan

            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();

            $table->string('foto_selfie');
            $table->text('tanda_tangan');
            $table->boolean('is_lsm')->default(false);
            $table->timestamp('waktu_keluar')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tamus');
    }
};
