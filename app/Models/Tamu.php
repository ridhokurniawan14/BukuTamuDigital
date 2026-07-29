<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Tamu extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = ['id'];

    // Relasi: 1 Tamu menemui 1 Pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    protected static function booted()
    {
        static::saving(function ($tamu) {
            // Jika kategori BUKAN 'Menemui Pegawai', maka paksa pegawai_id jadi kosong (null)
            if ($tamu->kategori_keperluan !== 'Menemui Guru / Pegawai / Kepsek') {
                $tamu->pegawai_id = null;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nama',
                'asal_instansi',
                'alamat',
                'no_hp',
                'kategori_keperluan',
                'keperluan',
                'pegawai_id',
                'is_lsm',
                'waktu_keluar',
                // 'foto_selfie' dan 'tanda_tangan' sengaja TIDAK saya masukkan, lihat catatan di bawah
            ])
            ->logOnlyDirty();
    }
}
