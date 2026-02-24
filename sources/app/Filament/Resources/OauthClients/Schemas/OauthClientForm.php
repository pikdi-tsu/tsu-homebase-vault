<?php

namespace App\Filament\Resources\OauthClients\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OauthClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Modul/Aplikasi')
                    ->required(),
//                Select::make('owner_type')
//                    ->label('Tipe Pemilik (Owner)')
//                    ->options([
//                        'App\\Models\\UserDosenTendik' => 'User Dosen/Tendik',
//                        'App\\Models\\Mahasiswa' => 'Mahasiswa',
//                    ])
//                    // Hanya muncul jika kondisi terpenuhi
//                    ->visible(fn ($get) => in_array('personal_access', $get('grant_types') ?? []))
//                    ->required(),
//                TextInput::make('owner_id')
//                    ->label('ID Pemilik (Owner)')
//                    // Hanya muncul jika kondisi terpenuhi
//                    ->visible(fn ($get) => in_array('personal_access', $get('grant_types') ?? []))
//                    ->required(),
                Select::make('grant_types')
                    ->label('Tipe Akses yang Diizinkan')
                    ->placeholder('Pilih Satu atau Lebih Tipe Akses')
                    ->multiple()
                    ->searchable()
                    ->options([
                        'client_credentials' => 'Client Credentials',
                        'authorization_code' => 'Authorization Code',
                        'password' => 'Password Grant',
                        'personal_access' => 'Personal Access',
                    ])
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        if (! in_array('authorization_code', $get('grant_types') ?? [], true)) {
                            $set('redirect_uris', []);
                        }
                    })
                    ->dehydrateStateUsing(function (?array $state): array {
                        // cek $state
                        if (in_array('password', $state ?? [], true)) {
                            // Jika admin memilih 'password', tambahkan 'refresh_token'
                            $state[] = 'refresh_token';
                        }

                        return array_unique($state ?? []);
                    })
                    ->afterStateHydrated(function (Select $component, ?array $state) {
                        // $state ['password', 'refresh_token'] adalah data dari DB
                        if (is_null($state)) {
                            return;
                        }

                        // hapus 'refresh_token' dari array
                        $newState = array_filter($state, static function ($grant) {
                            return $grant !== 'refresh_token';
                        });

                        // lalu set state ke komponen Select-nya
                        $component->state($newState);
                    })
                    ->required(),
                TagsInput::make('redirect_uris')
                    ->label('URL Redirect')
                    ->columnSpanFull()
                    ->placeholder('Masukkan URL lalu tekan Enter')
                    ->visible(fn ($get): bool => in_array('authorization_code', $get('grant_types') ?? [], true))
//                    ->disabled(fn ($get) => !in_array('authorization_code', $get('grant_types') ?? [], true))
                    ->required(fn ($get): bool => in_array('authorization_code', $get('grant_types') ?? [], true))
                ,
                TagsInput::make('scopes')
                    ->label('Scopes (Izin)')
                    ->placeholder('Ketik scope baru lalu tekan Enter')
                    ->helperText('Contoh: view-users create-users. Pisahkan dengan spasi jika meminta token.')
                    ->columnSpanFull(),
                Placeholder::make('grant_type_descriptions')
                    ->label('Deskripsi Tipe Akses:')
                    ->content(new HtmlString(
                        '<ul class="list-disc list-inside text-sm text-gray-500 dark:text-gray-400">
                            <li><strong>Client Credentials:</strong> Paling umum untuk komunikasi antar server.</li>
                            <li><strong>Authorization Code:</strong> Untuk aplikasi web pihak ketiga (alur "Login dengan Google").</li>
                            <li><strong>Password Grant:</strong> Hanya untuk aplikasi pihak pertama yang sangat dipercaya.</li>
                            <li><strong>Refresh Token:</strong> Izinkan klien untuk memperbarui access token. (Satu Kesatuan dengan Password Grant)</li>
                            <li><strong>Personal Access:</strong> Untuk mengizinkan user membuat token pribadinya sendiri.</li>
                        </ul>'
                    ))
                    ->columnSpanFull(),
            ]);
    }
}
