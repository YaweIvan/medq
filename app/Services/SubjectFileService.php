<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubjectFileService
{
    public function replaceSubjectFile(Subject $subject, UploadedFile $file, array $options = [])
    {
        DB::beginTransaction();
        
        try {
            $quizId = $options['quiz_id'];
            $timePerQuestion = $options['time_per_question'] ?? 60;

            // 6.1 — block if any student has an active attempt on this subject
            $hasActive = QuizAttempt::where('quiz_id', $quizId)
                ->whereHas('question', fn($q) => $q->where('subject_id', $subject->id))
                ->where('locked', false)
                ->whereNotNull('started_at')
                ->exists();

            if ($hasActive) {
                DB::rollBack();
                throw ValidationException::withMessages([
                    'excel_file' => 'Cannot replace questions while a student has an active attempt on this subject.',
                ]);
            }

            // Store old file path for cleanup
            $oldFilePath = $this->getSubjectFilePath($subject, $quizId);
            
            // Delete existing questions and attempts for this subject-quiz combination
            $this->deleteExistingQuestionsAndAttempts($subject->id, $quizId);
            
            // Process new file and create questions
            $questionsCount = $this->processUploadedFile($file, $quizId, $subject->id, $timePerQuestion);
            
            // Store new file path (if needed for tracking)
            $newFilePath = $this->storeFile($file, $subject, $quizId);
            
            // Clean up old file
            if ($oldFilePath) {
                $this->deleteOldFile($oldFilePath);
            }
            
            // Log the replacement
            Log::info("Subject file replaced", [
                'subject_id' => $subject->id,
                'quiz_id' => $quizId,
                'questions_created' => $questionsCount,
                'admin_id' => auth()->id()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "File replaced successfully. {$questionsCount} questions processed.",
                'questions_count' => $questionsCount
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Subject file replacement failed", [
                'subject_id' => $subject->id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id()
            ]);
            
            throw $e;
        }
    }

    private function deleteExistingQuestionsAndAttempts(int $subjectId, int $quizId): void
    {
        // Get question IDs that will be deleted
        $questionIds = Question::where('subject_id', $subjectId)
            ->where('quiz_id', $quizId)
            ->pluck('id');
        
        if ($questionIds->isNotEmpty()) {
            // Delete quiz attempts first (foreign key constraint)
            QuizAttempt::whereIn('question_id', $questionIds)->delete();
            
            // Delete questions
            Question::whereIn('id', $questionIds)->delete();
        }
    }

    private function processUploadedFile(UploadedFile $file, int $quizId, int $subjectId, int $timePerQuestion): int
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->processExcelFile($file, $quizId, $subjectId, $timePerQuestion);
        } else {
            return $this->processCsvFile($file, $quizId, $subjectId, $timePerQuestion);
        }
    }

    private function processExcelFile(UploadedFile $file, int $quizId, int $subjectId, int $timePerQuestion): int
    {
        try {
            $adminController = new \App\Http\Controllers\AdminController(app(\App\Services\DashboardStatisticsService::class));
            $reflection = new \ReflectionClass($adminController);
            $method = $reflection->getMethod('processExcelFileContent');
            $method->setAccessible(true);
            
            return $method->invoke($adminController, $file, $quizId, $subjectId, $timePerQuestion);
        } catch (\Exception $e) {
            Log::error('Excel processing failed in service: ' . $e->getMessage());
            return 0;
        }
    }

    private function processCsvFile(UploadedFile $file, int $quizId, int $subjectId, int $timePerQuestion): int
    {
        try {
            $adminController = new \App\Http\Controllers\AdminController(app(\App\Services\DashboardStatisticsService::class));
            $reflection = new \ReflectionClass($adminController);
            $method = $reflection->getMethod('processCsvFile');
            $method->setAccessible(true);
            
            return $method->invoke($adminController, $file, $quizId, $subjectId, $timePerQuestion);
        } catch (\Exception $e) {
            Log::error('CSV processing failed in service: ' . $e->getMessage());
            return 0;
        }
    }

    private function storeFile(UploadedFile $file, Subject $subject, int $quizId): string
    {
        $filename = "subject_{$subject->id}_quiz_{$quizId}." . $file->getClientOriginalExtension();
        return $file->storeAs('subject_files', $filename, 'local');
    }

    private function getSubjectFilePath(Subject $subject, int $quizId): ?string
    {
        // Check if file exists in storage
        $possibleExtensions = ['xlsx', 'xls', 'csv'];
        
        foreach ($possibleExtensions as $ext) {
            $path = "subject_files/subject_{$subject->id}_quiz_{$quizId}.{$ext}";
            if (Storage::disk('local')->exists($path)) {
                return $path;
            }
        }
        
        return null;
    }

    private function deleteOldFile(?string $filePath): void
    {
        if ($filePath && Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            Log::info("Old subject file deleted: {$filePath}");
        }
    }
}