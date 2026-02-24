<?php

namespace App\Filament\Resources\OauthClients\Tables;

use App\Models\OauthCLient;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OauthClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Modul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('id')
                    ->label('Client ID')
                    ->copyable(),
                TextColumn::make('secret')
                    ->label('Client Secret')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('grant_types')
                    ->label('Tipe Grant')
                    ->badge()
                    ->color('secondary'),
                ToggleColumn::make('first_party')
                    ->label('Auto Approve')
                    ->onColor('success')
                    ->offColor('gray')
                    ->tooltip('Jika ON: User langsung login tanpa ditanya "Izinkan Aplikasi?"')
                    ->sortable()
                    ->disabled(function ($record) {
                        // Ambil data grant_types
                        $grants = $record->grant_types;
                        if (is_string($grants)) {
                            $grants = json_decode($grants, true, 512, JSON_THROW_ON_ERROR) ?? [];
                        }

                        // Cek Personal atau Password
                        if (in_array('personal_access', $grants, true)) {
                            return true;
                        }
                        if (in_array('password', $grants, true)) {
                            return true;
                        }

                        // Cek Redirect URI
                        $redirect = $record->redirect_uris ?? $record->redirect;

                        return blank($redirect);
                    })
                    ->tooltip(function ($record) {
                        // Logika tooltip juga harus ngikutin yang atas
                        $grants = $record->grant_types;
                        if (is_string($grants)) {
                            $grants = json_decode($grants, true) ?? [];
                        }

                        if (in_array('personal_access', $grants, true)) {
                            return 'Tidak tersedia untuk Personal Access';
                        }
                        if (in_array('password', $grants, true)) {
                            return 'Tidak tersedia untuk Password Grant';
                        }

                        $redirect = $record->redirect_uris ?? $record->redirect;
                        if (blank($redirect)) return 'Client Credentials (Tanpa Redirect) tidak butuh Auto Approve';

                        return 'Aktifkan agar user tidak perlu klik tombol "Approve" manual.';
                    }),
                ToggleColumn::make('revoked')
                    ->label('Akses Dicabut')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->color('warning'),

                Action::make('regenerateSecret')
                    ->label('Regenerate Secret')
                    ->color('danger')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (OauthCLient $record) {
                        $newSecret = Str::random(40);
                        $record->forceFill(['secret' => $newSecret])->save();

                        Notification::make()
                            ->title('Secret Baru Berhasil Dibuat')
//                            ->body("Secret baru untuk klien '{$record->name}' adalah: {$newSecret}")
                            ->body("Secret Key baru {$record->name} sudah siap. Klik tombol di bawah untuk menyalin.")
                            ->persistent()
                            ->actions([
                                Action::make('copyNewSecret')
                                    ->label('Salin Secret Key')
                                    ->button()
                                    ->color('gray')
                                    ->icon('heroicon-o-clipboard-document')
                                    ->dispatch('copy-to-clipboard', [
                                        'token' => $newSecret,
                                    ])
                                ])
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
