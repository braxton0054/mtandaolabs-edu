<?php

namespace Database\Seeders;

use App\Models\Fee;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\MyClass;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class FeeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Demo price lists showing grade-differentiated term fees: Grade 5
     * pays more than Grade 3 for the same term.
     */
    public function run()
    {
        $category = FeeCategory::firstOrCreate(
            ['school_id' => 1, 'name' => 'Tuition and Meals'],
            ['description' => 'Core term fees']
        );

        $fees = [];
        foreach (['Tuition' => 'Term tuition', 'Lunch' => 'Midday meals', 'Transport' => 'School bus'] as $name => $description) {
            $fees[$name] = Fee::firstOrCreate(
                ['name' => $name],
                ['description' => $description, 'fee_category_id' => $category->id]
            );
        }

        $term1 = Semester::query()
            ->where('school_id', 1)
            ->where('name', 'Term 1')
            ->orderBy('id')
            ->first();

        if ($term1 === null) {
            return;
        }

        $priceLists = [
            'Grade 3' => ['Tuition' => 12000, 'Lunch' => 3000],
            'Grade 5' => ['Tuition' => 15000, 'Lunch' => 3500, 'Transport' => 4000],
        ];

        foreach ($priceLists as $className => $lines) {
            $class = MyClass::query()
                ->where('name', $className)
                ->whereHas('classGroup', fn ($query) => $query->where('school_id', 1))
                ->first();

            if ($class === null) {
                continue;
            }

            foreach ($lines as $feeName => $amount) {
                FeeStructure::updateOrCreate(
                    [
                        'school_id' => 1,
                        'my_class_id' => $class->id,
                        'semester_id' => $term1->id,
                        'fee_id' => $fees[$feeName]->id,
                    ],
                    ['amount' => $amount]
                );
            }
        }
    }
}
