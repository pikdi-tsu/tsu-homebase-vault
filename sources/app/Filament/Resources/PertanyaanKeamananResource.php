<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PertanyaanKeamananResource\Pages\ListPertanyaanKeamanans;
use App\Filament\Resources\PertanyaanKeamananResource\Pages\EditPertanyaanKeamanan;
use App\Filament\Resources\PertanyaanKeamananResource\Pages;
use App\Filament\Resources\PertanyaanKeamananResource\RelationManagers;
use App\Models\PertanyaanKeamanan;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PertanyaanKeamananResource extends Resource
{
    protected static ?string $model = PertanyaanKeamanan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Pertanyaan Keamanan';

    protected static ?string $modelLabel = 'Pertanyaan Keamanan';

    protected static ?string $pluralModelLabel = 'Pertanyaan Keamanan';
    protected static ?string $recordTitleAttribute = 'pertanyaan';

    public static function getGloballySearchableAttributes(): array
    {
        return ['jenis', 'pertanyaan'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('jenis')
                    ->required()
                    ->maxLength(255),
                TextInput::make('pertanyaan')
                    ->required()
                    ->maxLength(255)
                    ->afterLabel('Huruf kecil semua dan tanpa tanda tanya (?)')
                    ->rules(['regex:/^[a-zA-Z ]*$/'])
                    ->validationMessages([
                        'regex' => 'Input hanya boleh huruf dan spasi.',
                    ]),
//                Select::make('status')
//                    ->options([
//                        'aktif' => 'Aktif',
//                        'nonaktif' => 'Nonaktif',
//                    ])
//                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis')
                    ->searchable(),
                TextColumn::make('pertanyaan')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->color('secondary')
                    ->modalHeading('Edit Pertanyaan Keamanan')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal')
                    ->successNotificationTitle('Berhasil Mengedit Pertanyaan Keamanan! 🎉'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListPertanyaanKeamanans::route('/'),
//            'create' => Pages\CreatePertanyaanKeamanan::route('/create'),
//            'edit' => EditPertanyaanKeamanan::route('/{record}/edit'),
        ];
    }
}
