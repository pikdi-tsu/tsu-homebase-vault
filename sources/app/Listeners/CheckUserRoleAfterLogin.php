<?php

namespace App\Listeners;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class CheckUserRoleAfterLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        /** @var Authenticatable $user */
        $user = $event->user;
        $user->forgetCachedPermissions();

        // Cek apakah user punya model dan method yang kita butuhkan
        if (method_exists($user, 'hasAnyRole')) {
//            dd($user->hasAnyRole('super admin'));
            if ($user->hasAnyRole('admin|super admin')) {
                $user->last_login_at = now();
                $user->save();
            }
//            else {
//                Auth::logout();
//                request()->session()->flash('error', 'Anda tidak memiliki hak akses untuk masuk.');
//            }
        }
    }
}
