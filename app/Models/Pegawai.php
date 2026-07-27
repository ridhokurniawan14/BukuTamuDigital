<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Pegawai extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id']; // Memperbolehkan semua kolom diisi kecuali ID

    // Relasi: 1 Pegawai bisa didatangi banyak Tamu
    public function tamus()
    {
        return $this->hasMany(Tamu::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Sebutkan kolomnya secara spesifik agar Spatie tidak bingung
            ->logOnly(['nama', 'jabatan', 'no_hp', 'is_active'])
            // Opsional tapi sangat disarankan: Cuma merekam data yang benar-benar berubah
            ->logOnlyDirty();
    }
}
