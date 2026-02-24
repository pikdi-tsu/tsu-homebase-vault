<?php

namespace App\Filament\Resources\OauthClients\Pages;

use App\Filament\Resources\OauthClients\OauthClientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOauthClient extends EditRecord
{
    protected static string $resource = OauthClientResource::class;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Cek kondisi grant_types dari data form yang akan disimpan
        if (! in_array('authorization_code', $data['grant_types'] ?? [], true)) {
            // Jika tidak ada, kita paksa nilai redirect_uris menjadi null di dalam data
            $data['redirect_uris'] = null;
        }

        // Lanjutkan proses update standar dengan data yang sudah kita modifikasi
        $record->update($data);

        return $record;
    }
}
