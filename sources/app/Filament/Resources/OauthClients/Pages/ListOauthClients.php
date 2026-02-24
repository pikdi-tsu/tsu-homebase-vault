<?php

namespace App\Filament\Resources\OauthClients\Pages;

use App\Filament\Resources\OauthClients\OauthClientResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ListOauthClients extends ListRecords
{
    protected static string $resource = OauthClientResource::class;


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Oauth')
                ->color('secondary')
                ->modalHeading('Tambah Oauth')
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
                ->using(function (array $data): Model {
                    $name = ['name'];
//                    $data['id'] = (string) Str::orderedUuid();
                    $secret = Str::random(40);
                    $data['secret'] = $secret;
                    $data['provider'] = 'users';
                    $data['revoked'] = false; // Atur agar tidak dicabut (aktif)

                    Notification::make()
                        ->title('Oauth Client Berhasil Dibuat!')
//                        ->body("Client Secret untuk '{$data["name"]}' adalah: {$secret}")
                        ->body("Secret Key {$data["name"]} sudah siap. Klik tombol di bawah untuk menyalin.")
                        ->actions([
                            Action::make('copyNewOauthSecret')
                                ->label('Salin Secret Key')
                                ->button()
                                ->color('gray')
                                ->icon('heroicon-o-clipboard-document')
                                // Kirim event dengan data secret key
                                ->dispatch('copy-to-clipboard', [
                                    'token' => $secret,
                                ])
                        ])
                        ->success()
                        ->persistent()
                        ->send();

                    return static::getModel()::create($data);
                }),
        ];
    }
}
