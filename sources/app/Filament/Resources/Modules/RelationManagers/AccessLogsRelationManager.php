<?php

namespace App\Filament\Resources\Modules\RelationManagers;

use App\Models\ModuleAccessLog;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccessLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'accessLogs';
    protected static string|null $title = 'Riwayat Akses Remote';
    protected static string|null|\BackedEnum $icon = 'heroicon-o-clock';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('admin.name')
                    ->label('Admin Eksekutor')
                    ->disabled(),

                TextInput::make('ip_address')
                    ->label('IP Address')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                // ADMIN
                TextColumn::make('admin.name')
                    ->label('Admin')
                    ->icon('heroicon-o-user-circle')
                    ->sortable(),

                // TARGET USER (Polymorphic)
                TextColumn::make('target_user_id')
                    ->label('User Login')
                    ->formatStateUsing(function ($state, ModuleAccessLog $record) {
                        return $record->targetUser->name ?? 'Unknown User';
                    })
                    ->description(fn (ModuleAccessLog $record) => class_basename($record->target_user_type))
                    ->badge()
                    ->color('warning'),

                // WAKTU & IP
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(),

                TextColumn::make('accessed_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('accessed_at', 'desc')
            ->filters([
                SelectFilter::make('rentang_waktu')
                    ->label('Rentang Waktu')
                    ->placeholder('Semua Waktu')
                    ->options([
                        'today' => 'Hari Ini',
                        'week'  => '1 Minggu Terakhir',
                        'month' => '1 Bulan Terakhir',
                        'year'  => '1 Tahun Terakhir',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'];

                        if ($value === 'today') {
                            return $query->whereDate('accessed_at', now());
                        }

                        if ($value === 'week') {
                            return $query->where('accessed_at', '>=', now()->subWeek());
                        }

                        if ($value === 'month') {
                            return $query->where('accessed_at', '>=', now()->subMonth());
                        }

                        if ($value === 'year') {
                            return $query->where('accessed_at', '>=', now()->subYear());
                        }

                        return $query;
                    }),
            ])
            ->headerActions([
//                CreateAction::make(),
//                AssociateAction::make(),
                Action::make('clear_logs')
                    ->label('Bersihkan Semua Log')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        // Hapus semua log milik modul ini saja
                        $this->getOwnerRecord()->accessLogs()->delete();
                    })
                    ->visible(fn () => auth()->user()->hasRole('super admin')),
            ])
            ->recordActions([
//                EditAction::make(),
//                DissociateAction::make(),
//                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
//                    DissociateBulkAction::make(),
//                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
