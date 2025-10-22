<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Support\Facades\Hash;

class MedQSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@medq.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_approved' => true,
        ]);

        // Create sample quizzer users
        $quizzer1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'quizzer',
            'is_approved' => true,
        ]);

        $quizzer2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'quizzer',
            'is_approved' => true,
        ]);

        // Create subjects
        $anatomy = Subject::create(['name' => 'Anatomy']);
        $physiology = Subject::create(['name' => 'Physiology']);
        $pharmacology = Subject::create(['name' => 'Pharmacology']);

        // Create a sample quiz
        $quiz = Quiz::create([
            'title' => 'Medical Basics Quiz',
            'description' => 'Basic medical knowledge quiz covering anatomy and physiology',
            'is_active' => true,
        ]);

        // Assign users to quiz
        $quiz->users()->attach([$quizzer1->id, $quizzer2->id]);

        // Create sample questions
        Question::create([
            'quiz_id' => $quiz->id,
            'subject_id' => $anatomy->id,
            'question' => 'Which bone is the longest in the human body?',
            'option_a' => 'Tibia',
            'option_b' => 'Femur',
            'option_c' => 'Humerus',
            'option_d' => 'Radius',
            'correct_answer' => 'B',
        ]);

        Question::create([
            'quiz_id' => $quiz->id,
            'subject_id' => $anatomy->id,
            'question' => 'How many chambers does a human heart have?',
            'option_a' => '2',
            'option_b' => '3',
            'option_c' => '4',
            'option_d' => '5',
            'correct_answer' => 'C',
        ]);

        Question::create([
            'quiz_id' => $quiz->id,
            'subject_id' => $physiology->id,
            'question' => 'What is the normal resting heart rate for adults?',
            'option_a' => '40-60 bpm',
            'option_b' => '60-100 bpm',
            'option_c' => '100-120 bpm',
            'option_d' => '120-140 bpm',
            'correct_answer' => 'B',
        ]);

        // Add more questions for testing
        for ($i = 1; $i <= 10; $i++) {
            Question::create([
                'quiz_id' => $quiz->id,
                'subject_id' => $anatomy->id,
                'question' => "Sample anatomy question {$i}?",
                'option_a' => 'Option A',
                'option_b' => 'Option B',
                'option_c' => 'Option C',
                'option_d' => 'Option D',
                'correct_answer' => 'A',
            ]);
        }
    }
}