<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $guarded = ['id']; // Memperbolehkan semua kolom diisi kecuali ID

    // Relasi: 1 Pegawai bisa didatangi banyak Tamu
    public function tamus()
    {
        return $this->hasMany(Tamu::class);
    }
}
