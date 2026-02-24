<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;

trait HasCommonUserColumns
{
    /**
     * Adds common user columns to the table.
     *
     * @param Blueprint $table
     * @return void
     */
    protected function addCommonUserColumns(Blueprint $table): void
    {
        $table->bigInteger('username')->unique()->comment('Username Berisi NIM/NIK');
        $table->string('name');
        $table->string('email')->nullable()->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('profile_photo_path', 2048)->nullable();
        $table->string('unit')->nullable();
        $table->string('role_access')->nullable();
        $table->string('privilege_pmb')->nullable();
        $table->rememberToken();
        $table->string('q1')->nullable();
        $table->string('a1')->nullable();
        $table->string('q2')->nullable();
        $table->string('a2')->nullable();
        $table->tinyInteger('forgot_password_send_email')->default(0);
        $table->string('created_by');
        $table->string('updated_by')->nullable();
        $table->timestamps();
        $table->tinyInteger('isactive')->default(1);
        $table->timestamp('last_seen_at')->nullable();
    }
}
