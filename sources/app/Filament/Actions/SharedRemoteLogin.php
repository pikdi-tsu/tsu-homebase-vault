<?php

namespace App\Filament\Actions;

use App\Models\Module;
use App\Models\ModuleAccessLog;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Model;

class SharedRemoteLogin
{
    public static function make(): ActionGroup
    {
        return ActionGroup::make(
            Module::query()->where('isactive', true)->get()
                ->map(function ($module) {
                    return Action::make('login_to_' . $module->id)
                        ->label("Masuk ke {$module->name}")
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(function (Model $record = null) use ($module) {
                            if (!$record) {
                                return null;
                            }

                            // Kirim ID dan Tipe User
                            return route('jump-to-module', [
                                'module'      => $module->id,
                                'target_id'   => $record->id,
                                'target_type' => get_class($record),
                            ]);
                        })
                        ->openUrlInNewTab();
                })
                ->all()
        )
        ->label('Remote Login')
        ->icon('heroicon-o-rocket-launch')
        ->color('info')
        ->tooltip('Pilih aplikasi tujuan');
    }
}
