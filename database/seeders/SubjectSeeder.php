<?php

namespace Database\Seeders;

use App\Enums\CbcLevel;
use App\Models\MyClass;
use App\Models\Pathway;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $classes = MyClass::query()
            ->whereHas('classGroup', fn ($query) => $query->where('school_id', 1)->whereNotNull('level'))
            ->with('classGroup')
            ->get()
            ->keyBy('name');

        $pathways = Pathway::query()->get()->keyBy('code');

        foreach ($this->subjects() as [$className, $name, $shortName, $options]) {
            $class = $classes->get($className);
            if ($class === null) {
                continue;
            }

            Subject::firstOrCreate(
                ['name' => $name, 'my_class_id' => $class->id],
                [
                    'short_name' => $shortName,
                    'school_id' => 1,
                    'pathway_id' => isset($options['pathway']) ? $pathways->get($options['pathway'])?->id : null,
                    'is_compulsory' => $options['compulsory'] ?? true,
                    'is_examinable' => $options['examinable'] ?? true,
                ]
            );
        }
    }

    /**
     * KICD learning areas per class.
     *
     * Each entry: class name, subject name, short name, flags. Electives
     * carry their Senior School pathway; PE and ICT are non-examinable.
     *
     * @return list<array{0: string, 1: string, 2: string, 3: array<string, mixed>}>
     */
    private function subjects(): array
    {
        $rows = [];

        $prePrimary = [
            ['Mathematical Activities', 'mat'],
            ['Language Activities', 'lan'],
            ['Environmental Activities', 'env'],
            ['Psychomotor and Creative Activities', 'pca'],
            ['Religious Education Activities', 'rel'],
        ];
        foreach (CbcLevel::PrePrimary->classNames() as $class) {
            foreach ($prePrimary as [$name, $short]) {
                $rows[] = [$class, $name, $short, []];
            }
        }

        $lowerPrimary = [
            ['Literacy', 'lit'],
            ['Kiswahili', 'kis'],
            ['English', 'eng'],
            ['Mathematical Activities', 'mat'],
            ['Environmental Activities', 'env'],
            ['Hygiene and Nutrition', 'hyn'],
            ['Religious Education', 'rel'],
            ['Creative Arts', 'cre'],
        ];
        foreach (CbcLevel::LowerPrimary->classNames() as $class) {
            foreach ($lowerPrimary as [$name, $short]) {
                $rows[] = [$class, $name, $short, []];
            }
        }

        $upperPrimary = [
            ['English', 'eng'],
            ['Kiswahili', 'kis'],
            ['Mathematics', 'mat'],
            ['Integrated Science', 'sci'],
            ['Social Studies', 'sst'],
            ['Religious Education', 'rel'],
            ['Creative Arts', 'cre'],
            ['Physical and Health Education', 'phe'],
            ['Agriculture', 'agr'],
            ['Home Science', 'hsc'],
        ];
        foreach (CbcLevel::UpperPrimary->classNames() as $class) {
            foreach ($upperPrimary as [$name, $short]) {
                $rows[] = [$class, $name, $short, []];
            }
        }

        $junior = [
            ['English', 'eng'],
            ['Kiswahili', 'kis'],
            ['Mathematics', 'mat'],
            ['Integrated Science', 'sci'],
            ['Pre-Technical Studies', 'pts'],
            ['Social Studies', 'sst'],
            ['Business Studies', 'bus'],
            ['Agriculture', 'agr'],
            ['Home Science', 'hsc'],
            ['Creative Arts and Sports', 'cas'],
            ['Religious Education', 'rel'],
            ['Life Skills Education', 'lsk'],
        ];
        foreach (CbcLevel::JuniorSchool->classNames() as $class) {
            foreach ($junior as [$name, $short]) {
                $rows[] = [$class, $name, $short, []];
            }
        }

        $seniorCore = [
            ['English', 'eng'],
            ['Kiswahili', 'kis'],
            ['Mathematics', 'mat'],
            ['Community Service Learning', 'csl'],
        ];
        $seniorNonExaminable = [
            ['Physical Education', 'phe'],
            ['ICT', 'ict'],
        ];
        $seniorElectives = [
            ['Physics', 'phy', 'stem'],
            ['Chemistry', 'che', 'stem'],
            ['Biology', 'bio', 'stem'],
            ['Computer Studies', 'com', 'stem'],
            ['Agriculture', 'agr', 'stem'],
            ['General Science', 'gsc', 'stem'],
            ['History and Citizenship', 'his', 'social_sciences'],
            ['Geography', 'geo', 'social_sciences'],
            ['Business Studies', 'bus', 'social_sciences'],
            ['Christian Religious Education', 'cre', 'social_sciences'],
            ['Literature in English', 'lit', 'social_sciences'],
            ['Music', 'mus', 'arts_sports'],
            ['Theatre and Film', 'thf', 'arts_sports'],
            ['Fine Arts', 'fin', 'arts_sports'],
            ['Sports and Recreation', 'spr', 'arts_sports'],
        ];
        foreach (CbcLevel::SeniorSchool->classNames() as $class) {
            foreach ($seniorCore as [$name, $short]) {
                $rows[] = [$class, $name, $short, []];
            }
            foreach ($seniorNonExaminable as [$name, $short]) {
                $rows[] = [$class, $name, $short, ['examinable' => false]];
            }
            foreach ($seniorElectives as [$name, $short, $pathway]) {
                $rows[] = [$class, $name, $short, ['compulsory' => false, 'pathway' => $pathway]];
            }
        }

        return $rows;
    }
}
