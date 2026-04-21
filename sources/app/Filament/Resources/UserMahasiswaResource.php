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
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\UserMahasiswaResource\Pages\ListUserMahasiswa;
use App\Filament\Resources\UserMahasiswaResource\Pages;
//use App\Filament\Resources\UserMahasiswaResource\RelationManagers;
use App\Models\UserMahasiswa;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserMahasiswaResource extends Resource
{
    protected static ?string $model = UserMahasiswa::class;


    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'User Mahasiswa';
    protected static ?string $modelLabel = 'User Mahasiswa';
    protected static ?string $pluralModelLabel = 'User Mahasiswa';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['username', 'name', 'email'];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->can('homebase:user-mahasiswa:view-any');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('homebase:user-mahasiswa:create');
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->can('homebase:user-mahasiswa:update');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->can('homebase:user-mahasiswa:delete');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Nomor Induk Mahasiswa')
                    ->placeholder('Masukkan NIM Mahasiswa')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Mahasiswa')
                    ->placeholder('Masukkan Nama Lengkap Mahasiswa')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->placeholder('Masukkan Email TSU Mahasiswa')
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->required(),

                // ROLE & PERMISSIONS SPATIE (Utama)
                Select::make('roles')
                    ->label('Roles')
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
                        PertanyaanKeamanan::query()->where('jenis', 'q1')->get() // 1. Ambil semua data sebagai collection
                        ->mapWithKeys(function ($item) { // 2. Lakukan iterasi untuk setiap item
                            // 3. Buat array [id => "Pertanyaan... ?"]
                            return [$item->id => $item->pertanyaan . '?'];
                        })
                    )
                    ->searchable(),
                Select::make('q2') // Ini akan menyimpan ID pertanyaan
                    ->label('Pertanyaan Keamanan 2')
                    ->options(
                        PertanyaanKeamanan::query()->where('jenis', 'q2')->get() // 1. Ambil semua data sebagai collection
                        ->mapWithKeys(function ($item) { // 2. Lakukan iterasi untuk setiap item
                            // 3. Buat array [id => "Pertanyaan... ?"]
                            return [$item->id => $item->pertanyaan . '?'];
                        })
                    )
                    ->searchable(),
                TextInput::make('a1') // <-- Jangan lupa field untuk jawabannya
                    ->label('Jawaban Keamanan 1'),
                TextInput::make('a2') // <-- Jangan lupa field untuk jawabannya
                    ->label('Jawaban Keamanan 2'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label('NIM mahasiswa')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('profile_photo_path')
                    ->label('Foto Profil')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->profile_photo_url),
                TextColumn::make('name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),

                // ROLE & PERMISSIONS SPATIE (Utama)
                TextColumn::make('roles.name')
                    ->label('Role (Guard)')
                    ->badge()
                    ->color('secondary')
                    ->placeholder('Belum di set')
                    ->separator('<br>')
                    ->getStateUsing(function (Model $record) {
                        if ($record->roles->isEmpty()) {
                            return null;
                        }
                        // Ubah implode menjadi return array biasa
                        return $record->roles->map(fn($role) => "{$role->name} ({$role->guard_name})")->all();
                    })
                    ->html()
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('roles', fn(Builder $q) => $q->where('name', 'like', "%{$search}%"));
                        }
                    ),
                TextColumn::make('permissions.name')
                    ->label('Izin Tambahan')
                    ->badge()
                    ->placeholder('Tidak ada izin tambahan')
                    ->color('success') // Beri warna berbeda agar mudah dibedakan dari roles
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),
//                    ->tooltip(function (Model $record): string {
//                        // Spatie 'permissions' relationship hanya mengambil direct permissions
//                        return $record->permissions->pluck('name')->implode(', ');
//                    }),

                // ROLE SIAKAD & PMB LEGACY (Dari MasterGroup)
//                TextColumn::make('MasterGroup.NamaGroup')
//                    ->label('Role Legacy')
//                    ->placeholder('Tidak ada role')
//                    ->badge()
//                    ->color('secondary')
//                    ->searchable(),
//                TextColumn::make('MasterGroupPMB.NamaGroup')
//                    ->label('Privilege PMB')
//                    ->placeholder('Tidak ada role')
//                    ->badge()
//                    ->color('secondary')
//                    ->searchable(),

                // Security Question
//                TextColumn::make('q1')
//                    ->label('Pertanyaan Keamanan 1')
//                    ->formatStateUsing(fn (string $state): string => "{$state}?")
//                    ->placeholder('Belum di set')
//                    ->searchable(),
//                TextColumn::make('a1')
//                    ->label('Jawaban Keamanan 1')
//                    ->placeholder('Belum di set')
//                    ->searchable(),
//                TextColumn::make('q2')
//                    ->label('Pertanyaan Keamanan 2')
//                    ->formatStateUsing(fn (string $state): string => "{$state}?")
//                    ->placeholder('Belum di set')
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
                SharedRemoteLogin::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotificationTitle('Data Mahasiswaberhasil dihapus dari sistem!'),

                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles', 'permissions']);
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
            'index' => ListUserMahasiswa::route('/'),
//            'create' => Pages\CreateUserMahasiswa::route('/create'),
            'edit' => Pages\EditUserMahasiswa::route('/{record}/edit'),
        ];
    }
}
