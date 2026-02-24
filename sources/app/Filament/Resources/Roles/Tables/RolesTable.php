<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->color('secondary')
                    ->placeholder('Belum di set')
                    ->listWithLineBreaks()
                    ->limitList(2)
                    ->expandableLimitedList()
//                    ->tooltip(function (Model $record): string {
//                        return $record->permissions->pluck('name')->implode(', ');
//                    })
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->searchable(),
                ToggleColumn::make('is_identity')
                    ->label('Role Identitas (Global)')
                    ->onColor('success')
                    ->offColor('danger')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->color('secondary')
                    ->successNotificationTitle('Role berhasil di update')
                    ->hidden(fn (Role $record): bool => $record->name === 'super admin'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                ])
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->name !== 'super admin',
//                fn (Role $record): bool => !in_array($record->name, ['super admin', 'admin'])
            );
    }
}
