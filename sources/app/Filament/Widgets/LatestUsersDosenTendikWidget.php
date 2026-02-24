<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use app\Models\UserDosenTendik;

class LatestUsersDosenTendikWidget extends TableWidget
{
    protected static ?string $heading = 'User Dosen & Tendik Terbaru';

    protected static ?int $sort = 1;

//    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => UserDosenTendik::query()->latest()->limit(5))
            ->columns([
                TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('View')
                    ->url(fn (UserDosenTendik $record): string => route('filament.admin.resources.user-dosen-tendiks.edit', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
