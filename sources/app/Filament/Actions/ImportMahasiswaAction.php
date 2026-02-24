<?php

namespace App\Filament\Actions;

use App\Models\BackupUsersMahasiswa;
use App\Models\PrivilegePMB;
use App\Services\DefaultPasswordService;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\BackupUsersDosenTendik;
use App\Models\MasterGroup;
use App\Models\PertanyaanKeamanan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImportMahasiswaAction extends CreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'import_mahasiswa';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Import Data Mahasiswa')
            ->icon('heroicon-m-server-stack')
            ->color('primary')
            ->modalHeading('Import Data dari Database Lama')
            ->modalWidth('2xl')
            ->modalSubmitActionLabel('Simpan')
            ->createAnotherAction(fn ($action) => $action->label('Simpan & Tambah Lagi'))
            ->schema([
                Grid::make()
                    ->columns(2) // Buat 2 kolom
                    ->schema([
                        Select::make('username')
                            ->label('NIM - Nama Mahasiswa')
                            ->placeholder('Pilih salah satu data Mahasiswa')
                            ->searchPrompt('Ketik NIM atau Nama untuk mencari...')
                            ->getSearchResultsUsing(function (string $search): array {
                                if (strlen($search) < 3) {
                                    return [];
                                }

                                return BackupUsersMahasiswa::query()->where('nama', 'like', "%{$search}%")
                                    ->orWhere('nim', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->pluck('nama_lengkap_dan_nim', 'nim')
                                    ->all();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                return BackupUsersMahasiswa::query()->where('nim', $value)->first()?->nama_lengkap_dan_nim;
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (is_null($state)) {
                                    $set('email', null);
                                    return;
                                }

                                $user = BackupUsersMahasiswa::query()->where('nim', $state)->first();
                                if ($user) {
                                    $set('email', $user->email);
                                } else {
                                    $set('email', 'Email tidak ditemukan di data backup');
                                }
                            })
                            ->searchable(['nama', 'nim'])
                            ->live(debounce: 250)
                            ->preload()
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->readonly()
                            ->columnSpanFull()
                            ->placeholder('Email akan terisi otomatis...'),
                        TextInput::make('unit')
                            ->label('Department')
                            ->placeholder('Tempat Unit Mahasiswa akan terisi otomatis...')
                            ->readonly()
                            ->columnSpanFull(),

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
                    ]),
            ])
            ->using(function (array $data, $form): Model {
                $nim = $data['username'];
                $backupUser = BackupUsersMahasiswa::query()->where('nim', $nim)->first();

                $data['name'] = Str::title(strtolower($backupUser->nama));

                $data['password'] = (new DefaultPasswordService())->getDefaultHashedPassword();
                $data['created_by'] = Auth::user()->username;

                $rolesIds = $data['roles'] ?? [];

                $newUser = $this->getModel()::create($data);
                $newUser->assignRole($rolesIds);

                return $newUser;
            });
    }
}
