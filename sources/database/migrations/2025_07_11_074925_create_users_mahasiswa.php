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
        Schema::create('users_mahasiswa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $this->addCommonUserColumns($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_mahasiswa');
    }
};
