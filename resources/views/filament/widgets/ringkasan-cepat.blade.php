<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-3">
            <h3 class="text-sm font-bold text-gray-500 uppercase">Aksi Cepat</h3>
            <div class="flex flex-wrap gap-2">
                <x-filament::button tag="a" :href="\App\Filament\Resources\Tamus\TamuResource::getUrl('create')" icon="heroicon-m-plus" color="primary">
                    Tambah Tamu Manual
                </x-filament::button>

                <x-filament::button tag="a" :href="\App\Filament\Resources\Tamus\TamuResource::getUrl('index')" icon="heroicon-m-users" color="gray">
                    Lihat Semua Tamu
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
