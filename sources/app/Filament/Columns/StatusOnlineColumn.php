<?php

namespace App\Filament\Columns;

use Filament\Tables\Columns\TextColumn;
use App\Models\ModuleAccessLog;

class StatusOnlineColumn
{
    public static function make(string $name = 'status_online'): TextColumn
    {
        return TextColumn::make($name)
            ->label('Status Aktivitas')
            ->badge()
            ->alignCenter()
            ->getStateUsing(function ($record) {
                return self::determineStatus($record)['status'];
            })
            ->color(function ($record) {
                return self::determineStatus($record)['color'];
            })
            ->icon(function ($record) {
                return self::determineStatus($record)['icon'];
            })
            ->tooltip(function ($record) {
                return self::determineStatus($record)['tooltip'];
            });
    }

    protected static function determineStatus($record): array
    {
        // AMBIL DATA
        $lastSeen = $record->last_seen_at;
        $sessionLifetime = config('session.lifetime');
        $activeThreshold = 2;

        $lastRemoteLog = ModuleAccessLog::query()
            ->where('target_user_id', $record->id)
            ->where('target_user_type', $record->getMorphClass())
            ->latest('accessed_at')
            ->first();

        // BANDINGKAN WAKTU
        $diffMinutes = $lastRemoteLog ? abs(now()->diffInMinutes($lastRemoteLog->accessed_at)) : 9999;
        $homebaseDiff = $lastSeen ? abs(now()->diffInMinutes($lastSeen)) : 9999;
        $isRemoteActive = $lastRemoteLog && $diffMinutes < $sessionLifetime;
        $isHomebaseActive = $lastSeen && $homebaseDiff < $activeThreshold;
        $moduleName = $lastRemoteLog->module->name ?? 'Aplikasi Belum Terdaftar';

        // Skenario aktif di Module
        if ($isRemoteActive && (!$isHomebaseActive || $lastRemoteLog->accessed_at->gte($lastSeen->subMinute()))) {
            // Emergency login admin
            if ($lastRemoteLog->admin_id && $lastRemoteLog->admin_id !== $record->id) {
                return [
                    'status'  => 'Dipinjam Admin',
                    'color'   => 'warning',
                    'icon'    => 'heroicon-m-eye',
                    'tooltip' => "Login oleh Admin: {$lastRemoteLog->admin->name}\nTerakhir akses: {$lastRemoteLog->accessed_at->diffForHumans()}\n,di {$moduleName}.",
                ];
            }

            // User Login Sendiri (SSO)
            return [
                'status'  => 'Di Aplikasi: ' . $moduleName,
                'color'   => 'info',
                'icon'    => 'heroicon-m-computer-desktop',
                'tooltip' => "Terakhir akses: {$lastRemoteLog->accessed_at->diffForHumans()}.",
            ];
        }

        // Skenario aktif di Homebase
        if ($isHomebaseActive) {
            return [
                'status'  => 'Online di Homebase',
                'color'   => 'success',
                'icon'    => 'heroicon-m-wifi',
                'tooltip' => "Terakhir terlihat: {$lastSeen->diffForHumans()}",
            ];
        }

        // Skenario IDLE di Homebase
        if ($lastSeen && $homebaseDiff < $sessionLifetime) {
            return [
                'status'  => 'Idle',
                'color'   => 'gray',
                'icon'    => 'heroicon-m-clock',
                'tooltip' => "User masih login, tapi tidak aktif.\nSisa sesi: " . ($sessionLifetime - $homebaseDiff) . " menit lagi.",
            ];
        }

        // Skenario Offline
        return [
            'status'  => 'Offline',
            'color'   => 'gray',
            'icon'    => 'heroicon-m-moon',
            'tooltip' => $lastSeen ? "Sesi habis (Auto Logout).\nTerakhir: {$lastSeen->diffForHumans()}" : "Belum pernah login",
        ];
    }
}
