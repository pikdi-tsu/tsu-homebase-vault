<?php

namespace App\Filament\Resources\OauthClients;

use App\Filament\Resources\OauthClients\Pages\CreateOauthClient;
use App\Filament\Resources\OauthClients\Pages\EditOauthClient;
use App\Filament\Resources\OauthClients\Pages\ListOauthClients;
use App\Filament\Resources\OauthClients\Schemas\OauthClientForm;
use App\Filament\Resources\OauthClients\Tables\OauthClientsTable;
use App\Models\OauthCLient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OauthClientResource extends Resource
{
    protected static ?string $model = OauthCLient::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static ?string $modelLabel = 'Oauth Client';
    protected static ?string $pluralModelLabel = 'Oauth Clients';
    protected static ?string $navigationLabel = 'Oauth Clients';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'id', 'grant_types'];
    }

    public static function form(Schema $schema): Schema
    {
        return OauthClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OauthClientsTable::configure($table);
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
            'index' => ListOauthClients::route('/'),
//            'create' => CreateOauthClient::route('/create'),
            'edit' => EditOauthClient::route('/{record}/edit'),
        ];
    }
}
