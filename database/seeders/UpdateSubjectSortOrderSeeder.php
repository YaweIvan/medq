<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class UpdateSubjectSortOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Update existing subjects without sort_order
        Subject::whereNull('sort_order')
            ->orWhere('sort_order', 0)
            ->orderBy('created_at', 'asc')
            ->get()
            ->each(function ($subject, $index) {
                $subject->update(['sort_order' => $index + 1]);
            });

        $this->command->info('Updated sort_order for ' . Subject::count() . ' subjects.');
    }
}