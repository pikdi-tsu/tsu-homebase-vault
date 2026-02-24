<?php

namespace App\Filament\Resources\Permissions\Schemas;

use App\Traits\GuardSelectable;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    use GuardSelectable;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Scope (Permission)')
                    ->placeholder('Format Input = modul:model:aksi')
                    ->helperText('Contoh = homebase:user:view-any')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->minLength(3),
                Select::make('guard_name')
                    ->label('Guard')
                    ->options(self::getGuardOptions())
                    ->default(config('auth.defaults.guard'))
                    ->live() // <-- Penting! Agar form bereaksi saat pilihan berubah
                    ->required(),
            ]);
    }
}
