<?php

use App\Traits\HasCommonUserColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasCommonUserColumns;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_dosen_tendik', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('nidn')->unique()->nullable();
            $this->addCommonUserColumns($table);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', static function (Blueprint $table) {
            $table->string('id')->primary();
//            $table->foreignId('user_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_dosen_tendik');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
