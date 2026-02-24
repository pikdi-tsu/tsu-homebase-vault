<?php

namespace App\Filament\Actions;

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

class ImportDosenTendikAction extends CreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'import_dosen_tendik';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Import Data Dosen/Tendik')
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
                            ->label('NIK - Nama Dosen/Tendik')
                            ->placeholder('Ketik NIK atau Nama untuk mencari...')
                            ->getSearchResultsUsing(function (string $search) {
                                if (strlen($search) < 3) {
                                    return [];
                                }
                                return BackupUsersDosenTendik::query()
                                    ->where('nama', 'like', "%{$search}%")
                                    ->orWhere('nip', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->pluck('nama_lengkap_dan_nip', 'nip');
                            })
                            ->searchable(['nama', 'nip'])
                            ->live(debounce: 500)
                            ->preload()
                            ->columnSpanFull()
                            ->getOptionLabelUsing(function ($value): ?string {
                                return BackupUsersDosenTendik::query()->where('nip', $value)->first()?->nama_lengkap_dan_nip;
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (is_null($state)) {
                                    $set('email', null);
                                    $set('nidn', null);
                                    $set('unit', null);
                                    return;
                                }

                                $user = BackupUsersDosenTendik::query()->where('nip', $state)->first();
                                if ($user) {
                                    $set('email', $user->email_kampus);
                                    $set('nidn', $user->nidn);
                                    $set('unit', $user->homebase);
                                } else {
                                    $set('email', 'Email tidak ditemukan di data backup');
                                    $set('nidn', 'NIDN tidak ditemukan di data backup');
                                    $set('unit', 'Tempat unit tidak ditemukan di data backup');
                                }
                            }),
                        TextInput::make('nidn')
                            ->label('Nomor Induk Dosen Nasional')
                            ->placeholder('jika ada NIDN akan terisi otomatis...')
                            ->readonly()
                            ->dehydrated(false),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->readonly()
                            ->placeholder('Email akan terisi otomatis...')
                            ->dehydrated(false),
                        TextInput::make('unit')
                            ->label('Department')
                            ->placeholder('Jika ada tempat unit karyawan akan terisi otomatis...')
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
                $nik = $data['username'];
                $backupUser = BackupUsersDosenTendik::query()->where('nip', $nik)->first();

                if ($backupUser) {
                    $namaLengkap = $backupUser->nama;
                    $data['email'] = $backupUser->email_kampus;

                    // Logic Gelar
                    $posisiKoma = strpos($namaLengkap, ',');
                    if ($posisiKoma !== false) {
                        $nama = substr($namaLengkap, 0, $posisiKoma);
                        $gelar = substr($namaLengkap, $posisiKoma);
                        $data['name'] = ImportDosenTendikAction . phpStr::title(strtolower(trim($nama))) . $gelar;
                    } else {
                        $data['name'] = Str::title(strtolower($namaLengkap));
                    }
                } else {
                    $data['name'] = $nik;
                }

                $data['password'] = (new DefaultPasswordService())->getDefaultHashedPassword();
                $data['created_by'] = Auth::user()->username;

                $rolesIds = $data['roles'] ?? [];
                // Bersihkan field dummy
//                unset(
//                    $data['nik'], $data['email'],
//                    $data['roles'], $data['permissions'],
//                    $data['roles_access'], $data['privilege_pmb'],
//                    $data['q1'], $data['a1'], $data['q2'], $data['a2']
//                );

                $newUser = $this->getModel()::create($data);
                $newUser->assignRole($rolesIds);

                return $newUser;
            });
    }
}
