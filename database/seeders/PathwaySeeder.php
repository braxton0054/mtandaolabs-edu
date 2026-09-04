<?php

namespace Database\Seeders;

use App\Models\Pathway;
use Illuminate\Database\Seeder;

class PathwaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $pathways = [
            [
                'code' => 'stem',
                'name' => 'STEM',
                'description' => 'Science, Technology, Engineering and Mathematics.',
            ],
            [
                'code' => 'social_sciences',
                'name' => 'Social Sciences',
                'description' => 'Humanities, commerce, and languages.',
            ],
            [
                'code' => 'arts_sports',
                'name' => 'Arts and Sports Science',
                'description' => 'Creative arts, performance, and sports science.',
            ],
        ];

        foreach ($pathways as $pathway) {
            Pathway::firstOrCreate(['code' => $pathway['code']], $pathway);
        }
    }
}
