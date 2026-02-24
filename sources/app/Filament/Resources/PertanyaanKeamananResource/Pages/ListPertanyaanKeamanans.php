<?php

namespace App\Filament\Resources\PertanyaanKeamananResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PertanyaanKeamananResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPertanyaanKeamanans extends ListRecords
{
    protected static string $resource = PertanyaanKeamananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pertanyaan Keamanan')
                ->color('secondary')
                ->modalHeading('Tambah Pertanyaan Keamanan')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal')
                ->createAnotherAction(fn (Action $action) => $action->label('Simpan & Tambah Lagi'))
                ->successNotificationTitle('Berhasil Menambahkan Pertanyaan Keamanan! 🎉'),
        ];
    }
}
