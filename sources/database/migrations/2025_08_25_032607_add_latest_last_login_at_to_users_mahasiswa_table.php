<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users_mahasiswa', 'last_login_at')) {
            Schema::table('users_mahasiswa', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable()->after('isactive');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_mahasiswa', function (Blueprint $table) {
            //
        });
    }
};
