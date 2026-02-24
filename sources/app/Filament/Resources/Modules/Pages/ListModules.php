<?php

namespace App\Filament\Resources\Modules\Pages;

use App\Filament\Resources\Modules\ModuleResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Module')
                ->color('secondary')
                ->modalHeading('Tambah Module')
                ->modalSubmitActionLabel('Simpan')
                ->createAnotherAction(function (Action $action) {
                    return $action
                        ->label('Simpan & Tambah Lagi')
                        ->extraAttributes([
                            'wire:loading.attr' => 'disabled',
                            'wire:loading.class' => '!cursor-wait !opacity-50',
                        ]);
                })
                ->modalCancelActionLabel('Batal')
        ];
    }
}
