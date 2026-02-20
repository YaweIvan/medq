<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_quizzes' => Quiz::count(),
            'total_users' => User::where('role', 'quizzer')->count(),
            'pending_approvals' => User::where('is_approved', false)->count(),
            'total_questions' => Question::count(),
        ];
        
        // Get top 3 users by correct answers across all quizzes
        $topUsers = User::where('role', 'quizzer')
            ->withCount(['attempts as total_attempts'])
            ->withCount(['attempts as correct_answers' => function($q) {
                $q->where('is_correct', true);
            }])
            ->having('total_attempts', '>', 0)
            ->get()
            ->map(function($user) {
                $user->accuracy = round(($user->correct_answers / $user->total_attempts) * 100, 2);
                return $user;
            })
            ->sortByDesc('accuracy')
            ->take(3)
            ->values();
        
        return view('admin.dashboard', compact('stats', 'topUsers'));
    }

    public function approvals()
    {
        // Get all quizzers (approved, rejected, and pending)
        $users = User::where('role', 'quizzer')->get();
        return view('admin.approvals', compact('users'));
    }

    public function approveUser($id)
    {
        User::findOrFail($id)->update(['is_approved' => true]);
        return back()->with('success', 'User approved successfully.');
    }

    public function rejectUser($id)
    {
        // Mark as rejected (not approved) but don't delete
        User::findOrFail($id)->update(['is_approved' => false]);
        return back()->with('success', 'User rejected successfully.');
    }

    public function cancelApproval($id)
    {
        // Cancel approval (set back to pending/rejected)
        User::findOrFail($id)->update(['is_approved' => false]);
        return back()->with('success', 'User approval cancelled.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return back()->with('success', 'User deleted permanently.');
    }

    public function quizzes()
    {
        $quizzes = Quiz::with('users', 'questions')->get();
        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function createQuiz()
    {
        $users = User::where('role', 'quizzer')->where('is_approved', true)->get();
        $subjects = Subject::all();
        return view('admin.quizzes.create', compact('users', 'subjects'));
    }

    public function storeQuiz(Request $request)
    {
        \DB::beginTransaction();
        
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'participant_ids' => 'required|string',
                'subjects' => 'required|array',
                'is_active' => 'boolean',
            ]);

            $participantIds = json_decode($request->participant_ids, true);
            
            if (empty($participantIds)) {
                return response()->json(['success' => false, 'message' => 'Please select at least one participant'], 400);
            }
            
            $quiz = Quiz::create([
                'title' => $request->title,
                'is_active' => $request->boolean('is_active'),
            ]);

            $quiz->users()->attach($participantIds);
            
            $totalQuestions = 0;

            foreach ($request->subjects as $index => $subjectData) {
                if (empty($subjectData['name'])) {
                    \DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Subject name is required'], 400);
                }
                
                // Log the subject data for debugging
                \Log::info("Processing subject {$index}: " . $subjectData['name']);
                
                // Trim and validate subject name
                $subjectName = trim($subjectData['name']);
                
                // Prevent invalid subject names
                $invalidNames = ['option e', 'option a', 'option b', 'option c', 'option d', 'correct answer', 'answer', 'number', 'question'];
                if (in_array(strtolower($subjectName), $invalidNames)) {
                    \DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Invalid subject name: '{$subjectName}'. This appears to be a column header from your Excel file. Please enter a proper subject name (e.g., Anatomy, Cardiology)."], 400);
                }
                
                $subject = Subject::firstOrCreate(['name' => $subjectName]);
                
                if (isset($subjectData['file'])) {
                    $questionsCreated = $this->processExcelFile($subjectData['file'], $quiz->id, $subject->id);
                    $totalQuestions += $questionsCreated;
                }
            }
            
            if ($totalQuestions === 0) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No questions were imported. Please check your Excel file format.'], 400);
            }
            
            \DB::commit();
            return response()->json(['success' => true, 'message' => "Quiz created successfully with {$totalQuestions} questions"]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Quiz creation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    private function processExcelFile($file, $quizId, $subjectId)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $questionsCreated = 0;
        
        try {
            if (in_array($extension, ['xlsx', 'xls'])) {
                $questionsCreated = $this->processExcelFileContent($file, $quizId, $subjectId);
            } else {
                $questionsCreated = $this->processCsvFile($file, $quizId, $subjectId);
            }
        } catch (\Exception $e) {
            Log::error('File processing error: ' . $e->getMessage());
        }
        
        return $questionsCreated;
    }
    
    private function processCsvFile($file, $quizId, $subjectId)
    {
        $questionsCreated = 0;
        $filePath = $file->getRealPath();
        
        Log::info('Processing CSV file: ' . $filePath);
        
        if (!file_exists($filePath)) {
            Log::error('File not found: ' . $filePath);
            return 0;
        }
        
        // Try different delimiters to detect the correct one
        $delimiters = ["\t", ',', ';', '|'];
        $bestDelimiter = "\t"; // Default to tab since most Excel exports use tabs
        $maxColumns = 0;
        
        // Test each delimiter to find which gives the most columns
        foreach ($delimiters as $delimiter) {
            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                $firstRow = fgetcsv($handle, 10000, $delimiter);
                if ($firstRow && count($firstRow) > $maxColumns) {
                    $maxColumns = count($firstRow);
                    $bestDelimiter = $delimiter;
                }
                fclose($handle);
            }
        }
        
        Log::info('Detected delimiter: ' . ($bestDelimiter === "\t" ? 'TAB' : $bestDelimiter) . ', Columns: ' . $maxColumns);
        
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 10000, $bestDelimiter)) !== FALSE) {
                // Skip the header row
                if ($row === 0) {
                    Log::info('Header row: ' . json_encode($data));
                    $row++;
                    continue;
                }
                
                // Log the data for debugging
                if ($row <= 3) {
                    Log::info('Row ' . $row . ' data: ' . json_encode($data) . ' (columns: ' . count($data) . ')');
                }
                
                // Ensure we have at least 7 columns (Number, Question, A, B, C, D, Answer)
                // Can have 8 columns if Option E is included (Number, Question, A, B, C, D, E, Answer)
                if (count($data) >= 7) {
                    // Clean up the data
                    $questionText = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[1] ?? ''));
                    $optionA = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[2] ?? ''));
                    $optionB = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[3] ?? ''));
                    $optionC = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[4] ?? ''));
                    $optionD = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[5] ?? ''));
                    
                    // Check if we have 8 columns (with option E) or 7 columns (without)
                    if (count($data) >= 8) {
                        // 8 columns: Number, Question, A, B, C, D, E, Answer
                        $optionE = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[6]));
                        $correctAnswer = strtoupper(trim($data[7] ?? ''));
                        if (empty($optionE)) {
                            $optionE = null;
                        }
                    } else {
                        // 7 columns: Number, Question, A, B, C, D, Answer (no option E)
                        $optionE = null;
                        $correctAnswer = strtoupper(trim($data[6] ?? ''));
                    }
                    
                    // Validate the data - option E is optional
                    $hasValidOptions = !empty($questionText) && !empty($optionA) && !empty($optionB) && !empty($optionC) && !empty($optionD);
                    $hasValidAnswer = in_array($correctAnswer, ['A', 'B', 'C', 'D', 'E']);
                    
                    // If answer is E, option E must be present
                    if ($correctAnswer === 'E' && empty($optionE)) {
                        $hasValidAnswer = false;
                    }
                    
                    if ($hasValidOptions && $hasValidAnswer) {
                        try {
                            $questionData = [
                                'quiz_id' => $quizId,
                                'subject_id' => $subjectId,
                                'question' => $questionText,
                                'option_a' => $optionA,
                                'option_b' => $optionB,
                                'option_c' => $optionC,
                                'option_d' => $optionD,
                                'correct_answer' => $correctAnswer,
                            ];
                            
                            if (!empty($optionE)) {
                                $questionData['option_e'] = $optionE;
                            }
                            
                            Question::create($questionData);
                            $questionsCreated++;
                            if ($row <= 3) {
                                Log::info('Question ' . $row . ' created successfully');
                            }
                        } catch (\Exception $e) {
                            Log::error('Question creation error on row ' . $row . ': ' . $e->getMessage());
                        }
                    } else {
                        if ($row <= 3) {
                            Log::warning('Skipping row ' . $row . ' - validation failed. Q: "' . substr($questionText, 0, 50) . '...", Answer: "' . $correctAnswer . '"');
                        }
                    }
                } else {
                    if ($row <= 5) {
                        Log::warning('Row ' . $row . ' has only ' . count($data) . ' columns, expected 7');
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        
        Log::info('Total questions created: ' . $questionsCreated . ' from ' . ($row - 1) . ' data rows');
        return $questionsCreated;
    }
    
    private function processExcelFileContent($file, $quizId, $subjectId)
    {
        $questionsCreated = 0;
        $questionsSkipped = 0;
        $skippedReasons = [];
        $filePath = $file->getRealPath();
        
        try {
            Log::info('Processing Excel file with PhpSpreadsheet: ' . $filePath);
            
            // Load the spreadsheet using PhpSpreadsheet
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            
            Log::info("Excel file loaded: {$highestRow} rows, columns up to {$highestColumn}");
            
            // Process each row (skip header row)
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $worksheet->rangeToArray(
                    'A' . $row . ':H' . $row,
                    NULL,
                    TRUE,
                    FALSE
                )[0];
                
                if ($row <= 4) {
                    Log::info("Row {$row} data: " . json_encode($rowData));
                }
                
                // Ensure we have at least 7 columns (can have 8 for option E)
                if (count($rowData) >= 7) {
                    $questionText = trim(str_replace(["\r\n", "\r", "\n"], ' ', $rowData[1] ?? ''));
                    $optionA = trim(str_replace(["\r\n", "\r", "\n"], ' ', $rowData[2] ?? ''));
                    $optionB = trim(str_replace(["\r\n", "\r", "\n"], ' ', $rowData[3] ?? ''));
                    $optionC = trim(str_replace(["\r\n", "\r", "\n"], ' ', $rowData[4] ?? ''));
                    $optionD = trim(str_replace(["\r\n", "\r", "\n"], ' ', $rowData[5] ?? ''));
                    
                    // Check if we have 8 columns (with option E) or 7 columns (without)
                    if (count($rowData) >= 8) {
                        // 8 columns: Number, Question, A, B, C, D, E, Answer
                        $optionE = trim(str_replace(["\r\n", "\r", "\n"], ' ', $rowData[6]));
                        $correctAnswer = strtoupper(trim($rowData[7] ?? ''));
                        if (empty($optionE)) {
                            $optionE = null;
                        }
                    } else {
                        // 7 columns: Number, Question, A, B, C, D, Answer (no option E)
                        $optionE = null;
                        $correctAnswer = strtoupper(trim($rowData[6] ?? ''));
                    }
                    
                    // Validate the data - option E is optional
                    $hasValidOptions = !empty($questionText) && !empty($optionA) && !empty($optionB) && !empty($optionC) && !empty($optionD);
                    $hasValidAnswer = in_array($correctAnswer, ['A', 'B', 'C', 'D', 'E']);
                    
                    // If answer is E, option E must be present
                    if ($correctAnswer === 'E' && empty($optionE)) {
                        $hasValidAnswer = false;
                    }
                    
                    if ($hasValidOptions && $hasValidAnswer) {
                        try {
                            $questionData = [
                                'quiz_id' => $quizId,
                                'subject_id' => $subjectId,
                                'question' => $questionText,
                                'option_a' => $optionA,
                                'option_b' => $optionB,
                                'option_c' => $optionC,
                                'option_d' => $optionD,
                                'correct_answer' => $correctAnswer,
                            ];
                            
                            if (!empty($optionE)) {
                                $questionData['option_e'] = $optionE;
                            }
                            
                            Question::create($questionData);
                            $questionsCreated++;
                            if ($row <= 4) {
                                Log::info("Question from row {$row} created successfully");
                            }
                        } catch (\Exception $e) {
                            Log::error("Question creation error on row {$row}: " . $e->getMessage());
                            $questionsSkipped++;
                            $skippedReasons[] = "Row {$row}: Database error - " . $e->getMessage();
                        }
                    } else {
                        $questionsSkipped++;
                        $reason = [];
                        if (empty($questionText)) $reason[] = 'empty question';
                        if (empty($optionA)) $reason[] = 'empty option A';
                        if (empty($optionB)) $reason[] = 'empty option B';
                        if (empty($optionC)) $reason[] = 'empty option C';
                        if (empty($optionD)) $reason[] = 'empty option D';
                        if (!$hasValidAnswer) $reason[] = "invalid answer '{$correctAnswer}'";
                        if ($correctAnswer === 'E' && empty($optionE)) $reason[] = 'answer is E but option E is empty';
                        
                        $reasonText = implode(', ', $reason);
                        $skippedReasons[] = "Row {$row}: {$reasonText}";
                        
                        Log::warning("Skipping row {$row} - validation failed: {$reasonText}");
                    }
                } else {
                    $questionsSkipped++;
                    $skippedReasons[] = "Row {$row}: only " . count($rowData) . " columns (expected 7 or 8)";
                    Log::warning("Row {$row} has only " . count($rowData) . " columns, expected 7 or 8");
                }
            }
            
            Log::info("=== UPLOAD SUMMARY ===");
            Log::info("Total rows processed: " . ($highestRow - 1));
            Log::info("Questions created: {$questionsCreated}");
            Log::info("Questions skipped: {$questionsSkipped}");
            if (!empty($skippedReasons)) {
                Log::warning("Skipped questions details:");
                foreach ($skippedReasons as $reason) {
                    Log::warning("  - {$reason}");
                }
            }
            Log::info("======================");
            
            return $questionsCreated;
            
        } catch (\Exception $e) {
            Log::error('PhpSpreadsheet processing failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Fallback to CSV processing
            Log::info('Attempting CSV fallback...');
            return $this->processCsvFile($file, $quizId, $subjectId);
        }
    }

    public function statistics()
    {
        $stats = QuizAttempt::selectRaw('
            quiz_id,
            COUNT(*) as total_attempts,
            SUM(is_correct) as correct_answers,
            AVG(is_correct) * 100 as accuracy
        ')->groupBy('quiz_id')->with('quiz')->get();

        return view('admin.statistics', compact('stats'));
    }

    private function getQuizRankings($quizId)
    {
        $subjects = Subject::whereHas('questions', function($query) use ($quizId) {
            $query->where('quiz_id', $quizId);
        })->get();
        
        // Get all users who attempted this quiz
        $userIds = QuizAttempt::where('quiz_id', $quizId)
            ->distinct()
            ->pluck('user_id');
        
        $rankings = [];
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;
            
            $userStats = [
                'user_id' => $userId,
                'user_name' => $user->name,
                'subjects' => [],
                'total_correct' => 0,
                'total_attempted' => 0,
            ];
            
            // Calculate stats for each subject
            foreach ($subjects as $subject) {
                $subjectAttempts = QuizAttempt::where('quiz_id', $quizId)
                    ->where('user_id', $userId)
                    ->whereHas('question', function($query) use ($subject) {
                        $query->where('subject_id', $subject->id);
                    })
                    ->get();
                
                $correct = $subjectAttempts->where('is_correct', true)->count();
                $total = $subjectAttempts->count();
                
                $userStats['subjects'][$subject->id] = [
                    'correct' => $correct,
                    'total' => $total,
                ];
                
                $userStats['total_correct'] += $correct;
                $userStats['total_attempted'] += $total;
            }
            
            // Calculate percentage
            $userStats['percentage'] = $userStats['total_attempted'] > 0 
                ? round(($userStats['total_correct'] / $userStats['total_attempted']) * 100, 2) 
                : 0;
            
            $rankings[] = $userStats;
        }
        
        // Sort by total correct (descending), then by percentage
        usort($rankings, function($a, $b) {
            if ($a['total_correct'] == $b['total_correct']) {
                return $b['percentage'] <=> $a['percentage'];
            }
            return $b['total_correct'] <=> $a['total_correct'];
        });
        
        // Add position
        foreach ($rankings as $index => &$ranking) {
            $ranking['position'] = $index + 1;
        }
        
        return ['rankings' => $rankings, 'subjects' => $subjects];
    }

    public function quizAnalysis($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $data = $this->getQuizRankings($quizId);
        $rankings = $data['rankings'];
        $subjects = $data['subjects'];
        
        return view('admin.quiz_analysis', compact('quiz', 'subjects', 'rankings'));
    }

    public function exportQuizRankings($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $data = $this->getQuizRankings($quizId);
        $rankings = $data['rankings'];
        $subjects = $data['subjects'];
        
        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', $quiz->title . ' - Overall Rankings');
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + count($subjects)) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $row = 3;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Position');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Student Name');
        
        foreach ($subjects as $subject) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $subject->name);
        }
        
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Total');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Percentage');
        
        // Style headers - bold only, no colors
        $headerRange = 'A' . $row . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1) . $row;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        
        // Data rows
        $row++;
        foreach ($rankings as $ranking) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $row, $ranking['position']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $ranking['user_name']);
            
            foreach ($subjects as $subject) {
                $value = isset($ranking['subjects'][$subject->id]) 
                    ? $ranking['subjects'][$subject->id]['correct'] . '/' . $ranking['subjects'][$subject->id]['total']
                    : '-';
                $sheet->setCellValueByColumnAndRow($col++, $row, $value);
            }
            
            $sheet->setCellValueByColumnAndRow($col++, $row, $ranking['total_correct'] . '/' . $ranking['total_attempted']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $ranking['percentage'] . '%');
            
            $row++;
        }
        
        // Auto-size columns
        for ($i = 1; $i < $col; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }
        
        // Generate file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $quiz->title) . '_Rankings_' . date('Y-m-d') . '.xlsx';
        
        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'quiz_rankings_');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function subjectAnalysis($quizId, $subjectId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $subject = Subject::findOrFail($subjectId);
        
        $rankings = QuizAttempt::where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->selectRaw('user_id, COUNT(*) as total, SUM(is_correct) as correct')
            ->groupBy('user_id')
            ->with('user')
            ->get()
            ->map(function($item) {
                $item->percentage = $item->total > 0 ? round(($item->correct / $item->total) * 100, 2) : 0;
                return $item;
            })
            ->sortByDesc('percentage')
            ->values();
        
        return view('admin.subject_analysis', compact('quiz', 'subject', 'rankings'));
    }

    public function studentSubjectReview($quizId, $subjectId, $userId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $subject = Subject::findOrFail($subjectId);
        $user = User::findOrFail($userId);
        
        $attempts = QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->with('question')
            ->get();
        
        // Add question numbers
        $allQuestions = Question::where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
        
        foreach ($attempts as $attempt) {
            $attempt->question_number = array_search($attempt->question_id, $allQuestions) + 1;
        }
        
        return view('admin.student_subject_review', compact('quiz', 'subject', 'user', 'attempts'));
    }

    public function leaderboard()
    {
        $quizzes = Quiz::withCount('questions')
            ->withCount(['attempts as total_attempts'])
            ->withCount(['users as participants_count'])
            ->get();

        return view('admin.leaderboard', compact('quizzes'));
    }

    public function quizLeaderboard($quizId)
    {
        $quiz = Quiz::with('questions', 'users')->findOrFail($quizId);
        
        $leaderboard = User::where('role', 'quizzer')
            ->whereHas('quizzes', function($q) use ($quizId) {
                $q->where('quizzes.id', $quizId);
            })
            ->withCount(['attempts as total_attempts' => function($q) use ($quizId) {
                $q->where('quiz_id', $quizId);
            }])
            ->withCount(['attempts as correct_answers' => function($q) use ($quizId) {
                $q->where('quiz_id', $quizId)->where('is_correct', true);
            }])
            ->withCount(['attempts as failed_answers' => function($q) use ($quizId) {
                $q->where('quiz_id', $quizId)->where('is_correct', false);
            }])
            ->get()
            ->map(function($user) {
                $user->accuracy = $user->total_attempts > 0 
                    ? round(($user->correct_answers / $user->total_attempts) * 100, 2) 
                    : 0;
                return $user;
            })
            ->sortByDesc('correct_answers')
            ->values();

        return view('admin.quiz_leaderboard', compact('quiz', 'leaderboard'));
    }

    public function studentQuizDetails($quizId, $userId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $user = User::findOrFail($userId);
        
        $attempts = QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->with(['question' => function($q) {
                $q->with('subject');
            }])
            ->get();
        
        $stats = [
            'total' => $attempts->count(),
            'correct' => $attempts->where('is_correct', true)->count(),
            'failed' => $attempts->where('is_correct', false)->count(),
            'accuracy' => $attempts->count() > 0 ? round(($attempts->where('is_correct', true)->count() / $attempts->count()) * 100, 2) : 0
        ];

        return view('admin.student_quiz_details', compact('quiz', 'user', 'attempts', 'stats'));
    }

    public function randomizer()
    {
        $quizzes = Quiz::where('is_active', true)->get();
        return view('admin.quizzes.randomizer', compact('quizzes'));
    }

    public function randomizeQuiz(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'type' => 'required|in:questions,users,both',
        ]);

        $quiz = Quiz::findOrFail($request->quiz_id);
        
        if ($request->type === 'questions' || $request->type === 'both') {
            // Reset all questions to unused and randomize order
            $quiz->questions()->update(['is_used' => false]);
        }
        
        return back()->with('success', 'Quiz randomized successfully.');
    }
    
    public function deleteQuiz($id)
    {
        Quiz::findOrFail($id)->delete();
        return back()->with('success', 'Quiz deleted successfully.');
    }
    
    public function toggleQuizStatus($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update(['is_active' => !$quiz->is_active]);
        return back()->with('success', 'Quiz status updated successfully.');
    }
    
    public function getUsersApi()
    {
        // Get all quizzer users (both approved and pending) for assignment
        $users = User::where('role', 'quizzer')
                    ->select('id', 'name', 'email', 'is_approved')
                    ->orderBy('name', 'asc')
                    ->get();
        return response()->json(['users' => $users]);
    }
    
    public function downloadTemplate()
    {
        $csv = "Number,Question,Option A,Option B,Option C,Option D,Option E,Correct Answer\n";
        $csv .= "1,What is the normal heart rate?,60-100 bpm,40-60 bpm,100-120 bpm,120-140 bpm,,A\n";
        $csv .= "2,Which organ produces insulin?,Liver,Pancreas,Kidney,Spleen,Heart,B\n";
        $csv .= "3,What is the largest bone in the human body?,Femur,Tibia,Humerus,Radius,,A\n";
        $csv .= "4,How many chambers does the human heart have?,2,3,4,5,6,C\n";
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="quiz_template.csv"');
    }
    
    public function selectEditQuiz()
    {
        $quizzes = Quiz::withCount(['users', 'questions', 'questions as subjects_count' => function($query) {
            $query->distinct('subject_id');
        }])
        ->orderBy('created_at', 'desc')
        ->get();
        
        return view('admin.quizzes.select_edit', compact('quizzes'));
    }
    
    public function editQuiz($id)
    {
        $quiz = Quiz::with(['users', 'questions.subject'])->findOrFail($id);
        return view('admin.quizzes.edit', compact('quiz'));
    }
    
    public function updateQuiz(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update([
            'title' => $request->title,
            'is_active' => $request->boolean('is_active'),
        ]);
        return response()->json(['success' => true]);
    }
    
    public function updateQuizUsers(Request $request, $id)
    {
        $request->validate([
            'user_ids' => 'sometimes|array', // Allow empty array to unassign all users
            'user_ids.*' => 'exists:users,id',
        ]);
        
        $quiz = Quiz::findOrFail($id);
        
        // If user_ids is provided (even if empty), sync them
        $userIds = $request->input('user_ids', []);
        $quiz->users()->sync($userIds);
        
        // Clear any cached user data
        \Cache::forget("quiz_{$id}_users");
        
        return response()->json([
            'success' => true, 
            'message' => 'User assignments updated successfully',
            'assigned_count' => count($userIds)
        ]);
    }
    
    public function resetQuiz($id)
    {
        $quiz = Quiz::findOrFail($id);
        
        // Count before reset
        $questionsCount = Question::where('quiz_id', $id)->where('is_used', true)->count();
        $attemptsCount = QuizAttempt::where('quiz_id', $id)->count();
        
        // Reset all questions to unused
        Question::where('quiz_id', $id)->update(['is_used' => false]);
        
        // Delete all attempts for this quiz
        QuizAttempt::where('quiz_id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Quiz reset successfully',
            'questions_reset' => $questionsCount,
            'attempts_deleted' => $attemptsCount
        ]);
    }

    public function addSubjects(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        
        \DB::beginTransaction();
        
        try {
            $totalQuestions = 0;
            
            foreach ($request->subjects as $index => $subjectData) {
                $subjectName = trim($subjectData['name']);
                $subject = Subject::firstOrCreate(['name' => $subjectName]);
                
                if (isset($subjectData['file'])) {
                    $questionsCreated = $this->processExcelFile($subjectData['file'], $quiz->id, $subject->id);
                    $totalQuestions += $questionsCreated;
                }
            }
            
            \DB::commit();
            return response()->json(['success' => true, 'message' => "Added {$totalQuestions} questions successfully"]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}