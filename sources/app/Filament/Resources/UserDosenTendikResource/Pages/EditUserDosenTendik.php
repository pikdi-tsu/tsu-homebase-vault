<?php

namespace App\Filament\Resources\UserDosenTendikResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\UserDosenTendikResource;
use Filament\Resources\Pages\EditRecord;

class EditUserDosenTendik extends EditRecord
{
    protected static string $resource = UserDosenTendikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();

        if (isset($actions[0])) {
            $actions[0]->color('secondary');
        }

        return $actions;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Perubahan Data Karyawan Berhasil Disimpan!';
    }
}
