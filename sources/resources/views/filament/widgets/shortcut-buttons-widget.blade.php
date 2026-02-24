<x-filament-widgets::widget>
{{--    <x-filament::section>--}}
        <x-slot name="header">
            <h2>Aksi Cepat</h2>
        </x-slot>

        <div class="flex space-x-4">
            <x-filament::button
                style="margin: 5px"
                color="secondary"
                tag="a"
                href="{{ \App\Filament\Resources\UserDosenTendikResource::getUrl('index') }}?action=create"
                icon="fas-user-plus"
            >
                Tambah User Dosen/Tendik
            </x-filament::button>

            <x-filament::button
                style="margin: 5px"
                color="secondary"
                tag="a"
                href="{{ \App\Filament\Resources\UserMahasiswaResource::getUrl('index') }}?action=create"
                icon="fas-user-plus"
            >
                Tambah Mahasiswa
            </x-filament::button>
        </div>
{{--    </x-filament::section>--}}
</x-filament-widgets::widget>
