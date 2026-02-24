<?php

namespace Database\Seeders;

use App\Models\PertanyaanKeamanan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PertanyaanKeamananSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $question_1 = array(
            'what is the first film you watched in theaters',
            'what is your nickname',
            'what is your grandmothers maiden name',
            'what is the name of your favorite elementary school teacher',
            'where did you meet your partner',
            'where is your mothers city born'
        );

        $question_2 = array(
            'what is your favorite food',
            'what is the name of your favorite sports team',
            'what is your best hero name',
            'what is the name of your favorite singer',
            'where did your parents city meet',
            'where did you first work'
        );

        foreach ($question_1 as $key => $value) {
            PertanyaanKeamanan::firstOrCreate(
                ['pertanyaan' => $value,],
                ['jenis' => 'q1',]
            );
        }

        foreach ($question_2 as $key => $value) {
            PertanyaanKeamanan::firstOrCreate(
                ['pertanyaan' => $value,],
                ['jenis' => 'q2',]
            );
        }
    }
}
