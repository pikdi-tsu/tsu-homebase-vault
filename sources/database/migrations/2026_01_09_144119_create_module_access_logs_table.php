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
        Schema::create('module_access_logs', static function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('target_user');
            $table->foreignId('module_id');
            $table->foreignUuid('admin_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('login_method')->default('REMOTE_IMPERSONATE');
            $table->timestamp('accessed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_access_logs');
    }
};
