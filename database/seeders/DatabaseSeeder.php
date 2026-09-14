<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name'  => 'User',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Default achievement badges (Table 16: Badges)
        Badge::firstOrCreate(
            ['name' => 'First Steps'],
            [
                'description'    => 'Complete your very first quiz.',
                'icon'           => 'badges/first-steps.svg',
                'criteria_type'  => 'quiz_count',
                'criteria_value' => '1',
            ]
        );

        Badge::firstOrCreate(
            ['name' => 'Perfect Score'],
            [
                'description'    => 'Answer every question correctly in a quiz.',
                'icon'           => 'badges/perfect-score.svg',
                'criteria_type'  => 'score_threshold',
                'criteria_value' => '100',
            ]
        );

        Badge::firstOrCreate(
            ['name' => 'On a Streak'],
            [
                'description'    => 'Complete quizzes 3 days in a row.',
                'icon'           => 'badges/streak.svg',
                'criteria_type'  => 'streak',
                'criteria_value' => '3',
            ]
        );

        $this->call(QuestionSeeder::class);
    }
}
