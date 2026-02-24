<?php

namespace App\Filament\Resources\Modules\Tables;

use App\Models\Passport\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('url')
                    ->icon('heroicon-o-link')
                    ->searchable(),
                TextColumn::make('passportClient.name')
                ->label('Aplikasi Client (SSO)')
                    ->description(fn ($record) => 'ID: ' . $record->passport_client_id)
                    ->placeholder('Belum terhubung')
                    ->icon('heroicon-m-key')
                    ->copyable()
                    ->sortable(),
                IconColumn::make('isactive')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
