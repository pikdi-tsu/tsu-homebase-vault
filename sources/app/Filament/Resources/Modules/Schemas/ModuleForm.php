<?php

namespace App\Filament\Resources\Modules\Schemas;

use App\Models\Passport\Client;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Module')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('url')
                    ->label('App URL Module')
                    ->placeholder('https://aplikasi.tsu.ac.id')
                    ->url()
                    ->required(),
                Select::make('passport_client_id')
                    ->label('Passport Client App')
                    ->options(function () {
                        return Client::query()
                            ->where('revoked', false)
                            ->pluck('name', 'id');
                    })
                    ->columnSpanFull()
                    ->searchable()
                    ->helperText('Pilih Client ID Passport yang terhubung dengan modul ini.'),
            ]);
    }
}
