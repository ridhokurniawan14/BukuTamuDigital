<?php

namespace App\Livewire;

use Livewire\Component;

class HalamanDepan extends Component
{
    public function render()
    {
        // Ini akan memanggil tampilan dari folder resources/views/livewire/
        return view('livewire.halaman-depan');
    }
}
