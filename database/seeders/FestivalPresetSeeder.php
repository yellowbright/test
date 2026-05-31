<?php

namespace Database\Seeders;

use App\Models\FestivalPreset;
use Illuminate\Database\Seeder;

class FestivalPresetSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            ['category' => 'love', 'name' => '情人节', 'month' => 2, 'day' => 14],
            ['category' => 'love', 'name' => '七夕', 'month' => 8, 'day' => 22],
            ['category' => 'western', 'name' => '圣诞节', 'month' => 12, 'day' => 25],
            ['category' => 'western', 'name' => '感恩节', 'month' => 11, 'day' => 28],
            ['category' => 'traditional', 'name' => '春节', 'month' => 2, 'day' => 10],
            ['category' => 'traditional', 'name' => '中秋节', 'month' => 9, 'day' => 17],
            ['category' => 'memorial', 'name' => '清明节', 'month' => 4, 'day' => 4],
            ['category' => 'memorial', 'name' => '烈士纪念日', 'month' => 9, 'day' => 30],
        ];

        foreach ($presets as $preset) {
            FestivalPreset::query()->updateOrCreate(
                [
                    'category' => $preset['category'],
                    'name' => $preset['name'],
                    'month' => $preset['month'],
                    'day' => $preset['day'],
                ],
                ['is_active' => true]
            );
        }
    }
}
