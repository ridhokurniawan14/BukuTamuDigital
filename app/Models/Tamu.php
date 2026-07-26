<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tamu extends Model
{
    use HasFactory, SoftDeletes; // Aktifkan SoftDeletes

    protected $guarded = ['id'];

    // Relasi: 1 Tamu menemui 1 Pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
