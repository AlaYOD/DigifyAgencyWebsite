<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['key' => 'applied', 'en' => 'Applied', 'ar' => 'تم التقديم', 'color' => '#64748B', 'is_default' => true, 'is_terminal' => false, 'outcome' => null],
            ['key' => 'screening', 'en' => 'Screening', 'ar' => 'الفرز', 'color' => '#3B82F6', 'is_default' => false, 'is_terminal' => false, 'outcome' => null],
            ['key' => 'interview', 'en' => 'Interview', 'ar' => 'المقابلة', 'color' => '#8B5CF6', 'is_default' => false, 'is_terminal' => false, 'outcome' => null],
            ['key' => 'offer', 'en' => 'Offer', 'ar' => 'عرض وظيفي', 'color' => '#F59E0B', 'is_default' => false, 'is_terminal' => false, 'outcome' => null],
            ['key' => 'hired', 'en' => 'Hired', 'ar' => 'تم التوظيف', 'color' => '#10B981', 'is_default' => false, 'is_terminal' => true, 'outcome' => 'positive'],
            ['key' => 'rejected', 'en' => 'Rejected', 'ar' => 'مرفوض', 'color' => '#EF4444', 'is_default' => false, 'is_terminal' => true, 'outcome' => 'negative'],
        ];

        foreach ($stages as $sortOrder => $stage) {
            PipelineStage::updateOrCreate(
                ['key' => $stage['key']],
                [
                    'name' => ['en' => $stage['en'], 'ar' => $stage['ar']],
                    'color' => $stage['color'],
                    'sort_order' => $sortOrder + 1,
                    'is_default' => $stage['is_default'],
                    'is_terminal' => $stage['is_terminal'],
                    'outcome' => $stage['outcome'],
                ],
            );
        }
    }
}
