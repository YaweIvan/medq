<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use App\Services\DashboardStatisticsService;
use App\Services\QuestionAttemptResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExcelImportLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_accepts_bom_and_variants_in_headers(): void
    {
        $this->withoutExceptionHandling();

        $admin = new AdminController(app(DashboardStatisticsService::class));
        $quiz = Quiz::create(['title' => 'CSV Import Test Quiz']);
        $subject = Subject::create(['name' => 'CSV Import Subject']);

        $tmp = tempnam(sys_get_temp_dir(), 'medq_csv');
        $csv = "\xEF\xBB\xBFNumber,\xEF\xBB\xBFQuestion,Option A,Option B,Option C,Option D,Correct Answer\n";
        $csv .= "1,What is 2 + 2?,3,4,5,6,A\n";
        $csv .= "2,Which organ produces insulin?,Liver,Pancreas,Kidney,Spleen,B\n";

        file_put_contents($tmp, $csv);

        $file = new UploadedFile($tmp, 'questions.csv', 'text/csv', null, true);

        $method = new \ReflectionMethod(AdminController::class, 'processCsvFile');
        $method->setAccessible(true);

        $count = $method->invoke($admin, $file, $quiz->id, $subject->id, 60);

        unlink($tmp);

        $this->assertSame(2, $count);
        $this->assertSame(2, Question::where('quiz_id', $quiz->id)->where('subject_id', $subject->id)->count());
        $this->assertSame('A', Question::where('quiz_id', $quiz->id)->where('subject_id', $subject->id)->first()->correct_answer);
    }

    public function test_expired_question_attempt_is_closed_and_locked(): void
    {
        $resolver = new QuestionAttemptResolver();

        $quiz = Quiz::create(['title' => 'Locking Test Quiz']);
        $subject = Subject::create(['name' => 'Locking Test Subject']);
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'subject_id' => $subject->id,
            'question' => 'What is 2 + 2?',
            'option_a' => '3',
            'option_b' => '4',
            'option_c' => '5',
            'option_d' => '6',
            'correct_answer' => 'B',
            'time_per_question' => 60,
        ]);

        $userId = 999;
        $attempt = \App\Models\QuizAttempt::create([
            'user_id' => $userId,
            'quiz_id' => $quiz->id,
            'subject_id' => $subject->id,
            'question_id' => $question->id,
            'started_at' => now()->subMinutes(3),
            'expires_at' => now()->subMinute(),
            'locked' => false,
            'submitted' => false,
            'is_correct' => false,
            'is_auto_expired' => false,
            'selected_answer' => null,
        ]);

        $resolver->resolveExpired($userId, $quiz->id);
        $attempt->refresh();

        $this->assertTrue((bool) $attempt->locked);
        $this->assertTrue((bool) $attempt->is_auto_expired);
        $this->assertNotNull($attempt->submitted_at);
    }
}
