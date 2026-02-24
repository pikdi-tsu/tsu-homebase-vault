<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Traits\GuardSelectable;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RoleForm
{
    use GuardSelectable;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Role')
                    ->minLength(2)
                    ->maxLength(255)
                    ->unique(ignoreRecord: true) // Nama role tidak boleh sama
                    ->required(),
                Select::make('guard_name')
                    ->label('Guard')
                    ->options(self::getGuardOptions())
                    ->default(config('auth.defaults.guard'))
                    ->live()
                    ->required(),
                Select::make('permissions')
                    ->label('Permissions')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->relationship(
                        'permissions',
                        'name',
                        fn (Builder $query, Get $get) => $query->where('guard_name', $get('guard_name'))
                    )
                    ->columnSpanFull(),
                Toggle::make('is_identity')
                    ->label('Role Identitas (Global)')
                    ->onColor('success')
                    ->offColor('danger')
                    ->helperText('Aktifkan jika role ini adalah STATUS UTAMA User (Dosen, Mahasiswa, Tendik). Role ini akan memaksa sinkronisasi di semua aplikasi Client.')
                    ->columnSpanFull(),
            ]);
    }
}
