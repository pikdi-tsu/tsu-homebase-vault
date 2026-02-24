<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\OauthCLient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $passportId = OauthCLient::query()->where('name', 'Authorization Code Access TSU Template')->pluck('id')->first();

        Module::query()->create([
            'name' => 'TSU Template',
            'url' => 'https:/tsu-project-template.tsu.ac.id',
            'passport_client_id' => $passportId,
        ]);
    }
}
