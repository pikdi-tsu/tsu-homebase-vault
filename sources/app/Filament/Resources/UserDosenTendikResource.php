<?php

namespace App\Filament\Resources;

use App\Filament\Actions\SharedRemoteLogin;
use App\Filament\Columns\StatusOnlineColumn;
use App\Models\MasterGroup;
use App\Models\Module;
use App\Models\ModuleAccessLog;
use App\Models\PertanyaanKeamanan;
use App\Models\PrivilegePMB;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserDosenTendikResource\Pages\CreateUserDosenTendik;
use App\Filament\Resources\UserDosenTendikResource\Pages\ListUserDosenTendik;
use App\Filament\Resources\UserDosenTendikResource\Pages\EditUserDosenTendik;
use App\Filament\Resources\UserDosenTendikResource\Pages;
//use App\Filament\Resources\UserDosenTendikResource\RelationManagers;
use App\Models\BackupUsersDosenTendik;
use App\Models\UserDosenTendik;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserDosenTendikResource extends Resource
{
    protected static ?string $model = UserDosenTendik::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'User Dosen & Tendik';
    protected static ?string $modelLabel = 'User Dosen & Tendik';
    protected static ?string $pluralModelLabel = 'User Dosen & Tendik';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['username', 'name', 'email'];
    }

    /**
     * Menentukan apakah user bisa melihat daftar resource ini di navigasi.
     * Jika hasilnya false, menu "User Dosen & Tendik" akan hilang.
     */
    public static function canViewAny(): bool
    {
        return Auth::user()->can('homebase:user-dosen-tendik:view-any');
    }

    /**
     * Menentukan apakah user bisa melihat halaman detail record.
     */
    public static function canView(Model $record): bool
    {
        return Auth::user()->can('homebase:user-dosen-tendik:view');
    }

    /**
     * Menentukan apakah user bisa membuat record baru.
     * Jika false, tombol "New user..." akan hilang.
     */
    public static function canCreate(): bool
    {
        return Auth::user()->can('homebase:user-dosen-tendik:create');
    }

    /**
     * Menentukan apakah user bisa mengedit record.
     * Jika false, tombol "Edit" di tabel akan hilang.
     */
    public static function canEdit(Model $record): bool
    {
        return Auth::user()->can('homebase:user-dosen-tendik:update');
    }

    /**
     * Menentukan apakah user bisa menghapus record.
     * Jika false, tombol "Delete" di tabel akan hilang.
     */
    public static function canDelete(Model $record): bool
    {
        return Auth::user()->can('homebase:user-dosen-tendik:delete');
    }

    public static function getEloquentQuery(): Builder
    {
        // Kita eager load 'roles' dan 'permissions' sekaligus
        return parent::getEloquentQuery()->with(['roles', 'permissions']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->placeholder('Masukkan Email TSU')
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull()
                    ->required(),

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
//                Select::make('role_access')
//                    ->label('Role Legacy')
//                    ->options(MasterGroup::all()->pluck('NamaGroup', 'KodeGroupUser'))
//                    ->searchable(),
//                Select::make('privilege_pmb')
//                    ->label('Privilege PMB')
//                    ->options(PrivilegePMB::all()->pluck('NamaGroup', 'KodeGroupUser'))
//                    ->searchable(),

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label('NIK dosen/tendik')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nidn')
                    ->label('NIDN dosen')
                    ->placeholder('Tidak ada NIDN')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('profile_photo_path')
                    ->label('Foto Profil')
                        ->disk('public')
                        ->circular()
                        ->defaultImageUrl(fn ($record) => $record->profile_photo_url),
                TextColumn::make('name')
                    ->label('Nama Dosen/Tendik')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),

                // ROLE & PERMISSIONS SPATIE (Utama)
                TextColumn::make('roles.name')
                    ->label('Role (Guard)')
                    ->placeholder('Tidak ada role')
                    ->badge()
                    ->limitList(2)
                    ->listWithLineBreaks()
                    ->expandableLimitedList()
                    ->color('secondary')
                    ->searchable(),
                TextColumn::make('permissions.name')
                    ->label('Izin Tambahan')
                    ->badge()
                    ->color('secondary')
                    ->placeholder('Tidak ada izin tambahan')
                    ->color('success')
                    ->limitList(2)
                    ->listWithLineBreaks()
                    ->expandableLimitedList()
                    ->searchable(),

                // ROLE SIAKAD & PMB LEGACY (Dari MasterGroup)
//                TextColumn::make('MasterGroup.NamaGroup')
//                    ->label('Role Legacy')
//                    ->placeholder('Tidak ada role')
//                    ->badge()
//                    ->limitList(2)
//                    ->listWithLineBreaks()
//                    ->expandableLimitedList()
//                    ->color('secondary')
//                    ->searchable(),
//                TextColumn::make('MasterGroupPMB.NamaGroup')
//                    ->label('Privilege PMB')
//                    ->placeholder('Tidak ada role')
//                    ->badge()
//                    ->limitList(2)
//                    ->listWithLineBreaks()
//                    ->expandableLimitedList()
//                    ->color('secondary')
//                    ->searchable(),

                // Security Question
//                TextColumn::make('pertanyaanKeamananSatu.pertanyaan')
//                    ->label('Pertanyaan Keamanan 1')
//                    ->placeholder('Belum di set')
//                    ->formatStateUsing(fn (string $state): string => "{$state}?")
//                    ->searchable(),
//                TextColumn::make('a1')
//                    ->label('Jawaban Keamanan 1')
//                    ->placeholder('Belum di set')
//                    ->searchable(),
//                TextColumn::make('pertanyaanKeamananDua.pertanyaan')
//                    ->label('Pertanyaan Keamanan 2')
//                    ->placeholder('Belum di set')
//                    ->formatStateUsing(fn (string $state): string => "{$state}?")
//                    ->searchable(),
//                TextColumn::make('a2')
//                    ->label('Jawaban Keamanan 2')
//                    ->placeholder('Belum di set')
//                    ->searchable(),
                StatusOnlineColumn::make(),
                ToggleColumn::make('isactive')
                    ->label('Aktif')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->color('warning'),
                Action::make('createToken')
                    ->label('Buat Token API Pribadi')
                    ->color('danger')
                    ->icon('heroicon-o-key')
                    // Aksi ini akan memunculkan modal dengan form
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Token')
                            ->placeholder('Contoh: Skrip laporan saya')
                            ->required(),
                    ])
                    // Tentukan apa yang terjadi saat form di-submit
                    ->action(function (array $data, UserDosenTendik $record) {
                        // 2. Ganti '$this->record' menjadi '$record'
                        $token = $record->createToken($data['name']);
                        $accessToken = $token->accessToken;

                        Notification::make()
                            ->title('Token Pribadi Dibuat!')
//                            ->body("Token untuk {$record->name} tidak akan ditampilkan lagi. Salin sekarang: {$accessToken}")
                            ->body("Token {$record->name} sudah siap. Klik tombol di bawah untuk menyalin.")
                            ->persistent()
                            ->actions([
                                Action::make('copy')
                                    ->label('Salin Token')
                                    ->button()
                                    ->color('gray')
                                    ->icon('heroicon-o-clipboard-document')
                                    ->dispatch('copy-to-clipboard', [
                                        'token' => $accessToken,
                                    ])
                            ])
                            ->success()
                            ->send();
                    }),
                SharedRemoteLogin::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Data Karyawan berhasil dihapus dari sistem!'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserDosenTendik::route('/'),
//            'create' => CreateUserDosenTendik::route('/create'),
            'edit' => EditUserDosenTendik::route('/{record}/edit'),
        ];
    }
}
