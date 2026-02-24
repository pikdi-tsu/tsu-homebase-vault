<?php

namespace App\Filament\Resources\UserDosenTendikResource\Pages;

use App\Filament\Resources\UserDosenTendikResource;
use App\Models\BackupUsersDosenTendik;
use App\Models\MasterGroup;
use App\Models\PertanyaanKeamanan;
use App\Models\PrivilegePMB;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateUserDosenTendik extends CreateRecord
{
    protected static string $resource = UserDosenTendikResource::class;

    public static function getCreateFormSchema(): array
    {
        return [
            Grid::make()
                ->columns(2) // Buat 2 kolom
                ->schema([
                    TextInput::make('username')
                        ->label('Nomor Induk Karyawan')
                        ->placeholder('Masukkan NIK Karyawan')
                        ->required(),
                    TextInput::make('nidn')
                        ->label('Nomor Induk Dosen Nasional')
                        ->placeholder('Masukkan NIDN Dosen'),
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->placeholder('Masukkan Nama dan Gelar')
                        ->columnSpanFull()
                        ->required(),
                    TextInput::make('email')
                        ->email()
                        ->placeholder('Masukkan Email TSU')
                        ->unique(ignoreRecord: true)
                        ->required(),
                    TextInput::make('unit')
                        ->label('Department')
                        ->placeholder('Masukkan Tempat Unit Karyawan'),

                    // ROLE & PERMISSIONS SPATIE (Utama)
                    Select::make('roles')
                        ->label('Jabatan (Roles)')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Role $record) => "{$record->name} ({$record->guard_name})")
                        ->searchable()
                        ->preload()
                        ->helperText('Format: Nama Roles (guard)')
                        ->required(),
                    Select::make('permissions')
                        ->label('Izin Tambahan (Direct Permissions)')
                        ->multiple()
                        ->relationship('permissions', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Permission $record) => "{$record->name} ({$record->guard_name})")
                        ->searchable()
                        ->preload()
                        ->helperText('Format: Nama Permission (guard)'),

                    // ROLE SIAKAD & PMB LEGACY (Dari MasterGroup)
                    Select::make('role_access')
                        ->label('Role Legacy')
                        ->options(MasterGroup::all()->pluck('NamaGroup', 'KodeGroupUser'))
                        ->searchable(),
                    Select::make('privilege_pmb')
                        ->label('Privilege PMB')
                        ->options(PrivilegePMB::all()->pluck('NamaGroup', 'KodeGroupUser'))
                        ->searchable(),

                    // Security Question
                    Select::make('q1')
                        ->label('Pertanyaan Keamanan 1')
                        ->options(
                            PertanyaanKeamanan::query()->where('jenis', 'q1')->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id => $item->pertanyaan . '?'];
                            })
                        )
                        ->searchable()
                        ->placeholder('Pilih salah satu pertanyaan keamanan')
                        ->searchPrompt('Ketik untuk mencari...'),
                    Select::make('q2')
                    ->label('Pertanyaan Keamanan 2')
                        ->options(
                            PertanyaanKeamanan::query()->where('jenis', 'q2')->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id => $item->pertanyaan . '?'];
                            })
                        )
                        ->searchable()
                        ->placeholder('Pilih salah satu pertanyaan keamanan')
                        ->searchPrompt('Ketik untuk mencari...'),
                    TextInput::make('a1')
                        ->label('Jawaban Keamanan 1'),
                    TextInput::make('a2')
                        ->label('Jawaban Keamanan 2'),
                ])
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                static::getCreateFormSchema()
            ]);
    }
}
