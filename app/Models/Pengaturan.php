<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    // UBAH BARIS INI BRO: Kosongkan isi kurung sikunya
    protected $guarded = [];

    protected $casts = [
        'hari_kerja' => 'array',
    ];
}
