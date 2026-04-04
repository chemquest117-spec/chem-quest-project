<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class NewQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $stages = Stage::orderBy('order')->get();

        // Let's seed LO1 (Stage 6) first
        $lo1StageId = Stage::where('title', 'LIKE', 'LO1%')->first()->id;

        $lo1Questions = [
            [
                'q' => 'In the following compound, calculate the oxidation number of nitrogen: N₂H₄',
                'type' => 'mcq',
                'opts' => ['-1', '-2', '-3', '+2'],
                'correct' => 'b',
                'explanation' => 'Hydrogen = +1. 4H = +4. 2N + 4 = 0. 2N = -4. N = -2.',
            ],
            [
                'q' => 'Calculate the oxidation number of carbon in the compound: CH₃COOH (First carbon CH₃)',
                'type' => 'mcq',
                'opts' => ['-2', '-3', '+3', '+4'],
                'correct' => 'b',
                'explanation' => '3H = +3. C + 3 = 0. C = -3.',
            ],
            // Add more...
        ];
    }
}
