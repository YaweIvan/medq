<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizSound;
use App\Models\Setting;
use App\Services\DashboardStatisticsService;
use App\Services\QuestionAttemptResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class AdminController extends Controller
{
    protected $dashboardStats;

    public function __construct(DashboardStatisticsService $dashboardStats)
    {
        $this->dashboardStats = $dashboardStats;
    }

    public function dashboard()
    {
        $stats = $this->dashboardStats->getStatistics();
        
        return response()
            ->view('admin.dashboard', compact('stats'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    public function sounds()
    {
        // Get quiz sounds from database
        $quizSounds = QuizSound::with('uploader')->get()->keyBy('sound_type');
        
        return view('admin.sounds', compact('quizSounds'));
    }

    public function settings()
    {
        $settings = [
            'splash_logo'     => Setting::get('splash_logo'),
            'welcome_logo'    => Setting::get('welcome_logo'),
            'org_name'        => Setting::get('org_name', 'MAKERERE UNIVERSITY MEDICAL STUDENTS ASSOCIATION (MUMSA)'),
            'org_tagline'     => Setting::get('org_tagline', 'All Rights Reserved © 2026'),
            'primary_color'   => Setting::get('primary_color', '#93c5fd'),
            'secondary_color' => Setting::get('secondary_color', '#bfdbfe'),
        ];
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'splash_logo'     => 'nullable|mimes:png,jpg,jpeg,gif,svg,webp|max:2048',
            'welcome_logo'    => 'nullable|mimes:png,jpg,jpeg,gif,svg,webp|max:2048',
            'org_name'        => 'nullable|string|max:255',
            'org_tagline'     => 'nullable|string|max:255',
            'primary_color'   => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        if ($request->hasFile('splash_logo')) {
            $old = Setting::get('splash_logo');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('splash_logo')->store('logos', 'public');
            Setting::set('splash_logo', $path);
        }

        if ($request->hasFile('welcome_logo')) {
            $old = Setting::get('welcome_logo');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('welcome_logo')->store('logos', 'public');
            Setting::set('welcome_logo', $path);
        }

        if ($request->filled('org_name'))        Setting::set('org_name', $request->org_name);
        if ($request->filled('org_tagline'))     Setting::set('org_tagline', $request->org_tagline);
        if ($request->filled('primary_color'))   Setting::set('primary_color', $request->primary_color);
        if ($request->filled('secondary_color')) Setting::set('secondary_color', $request->secondary_color);

        return back()->with('success', 'Settings saved successfully.');
    }

    public function resetSettings()
    {
        // Delete uploaded logos
        foreach (['splash_logo', 'welcome_logo'] as $key) {
            $old = Setting::get($key);
            if ($old) Storage::disk('public')->delete($old);
        }

        // Reset all settings to defaults
        Setting::set('splash_logo',     null);
        Setting::set('welcome_logo',    null);
        Setting::set('org_name',        'MAKERERE UNIVERSITY MEDICAL STUDENTS ASSOCIATION (MUMSA)');
        Setting::set('org_tagline',     'All Rights Reserved © 2026');
        Setting::set('primary_color',   '#93c5fd');
        Setting::set('secondary_color', '#bfdbfe');

        return back()->with('success', 'All settings have been reset to defaults.');
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
                'subjects.*.marks_per_question' => 'nullable|integer|min:1',
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
                
                $maxQuestions = isset($subjectData['max_questions']) ? (int)$subjectData['max_questions'] : 5;
                $marksPerQuestion = isset($subjectData['marks_per_question']) ? (int)$subjectData['marks_per_question'] : 1;
                
                $subject = Subject::firstOrCreate(
                    ['name' => $subjectName],
                    ['max_questions' => $maxQuestions, 'marks_per_question' => $marksPerQuestion]
                );
                
                // Update max_questions for existing subject
                $subject->update([
                    'max_questions' => $maxQuestions,
                    'marks_per_question' => $marksPerQuestion,
                ]);
                
                $timePerQuestion = isset($subjectData['time_per_question']) ? (int)$subjectData['time_per_question'] : 60;
                
                if (isset($subjectData['file'])) {
                    $questionsCreated = $this->processExcelFile($subjectData['file'], $quiz->id, $subject->id, $timePerQuestion);
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

    private function processExcelFile($file, $quizId, $subjectId, $timePerQuestion = 60)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $questionsCreated = 0;
        
        try {
            if (in_array($extension, ['xlsx', 'xls'])) {
                $questionsCreated = $this->processExcelFileContent($file, $quizId, $subjectId, $timePerQuestion);
            } else {
                $questionsCreated = $this->processCsvFile($file, $quizId, $subjectId, $timePerQuestion);
            }
        } catch (\Exception $e) {
            Log::error('File processing error: ' . $e->getMessage());
        }
        
        return $questionsCreated;
    }
    
    private function processCsvFile($file, $quizId, $subjectId, $timePerQuestion = 60)
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
            $columnMapping = [];
            
            while (($data = fgetcsv($handle, 10000, $bestDelimiter)) !== FALSE) {
                // Process the header row to create column mapping
                if ($row === 0) {
                    Log::info('Header row: ' . json_encode($data));
                    
                    // Create case-insensitive column mapping
                    foreach ($data as $index => $header) {
                        $normalizedHeader = $this->normalizeImportHeader($header);
                        if ($normalizedHeader !== '') {
                            $columnMapping[$normalizedHeader] = $index;
                        }
                    }
                    
                    // Determine column indices (try both header names and fixed positions as fallback)
                    $numCol = $this->getColumnIndex($columnMapping, 'number', 'num', '#', 'no') ?? 0;
                    $questionCol = $this->getColumnIndex($columnMapping, 'question', 'q') ?? 1;
                    $optionACol = $this->getColumnIndex($columnMapping, 'option a', 'a', 'option_a') ?? 2;
                    $optionBCol = $this->getColumnIndex($columnMapping, 'option b', 'b', 'option_b') ?? 3;
                    $optionCCol = $this->getColumnIndex($columnMapping, 'option c', 'c', 'option_c') ?? 4;
                    $optionDCol = $this->getColumnIndex($columnMapping, 'option d', 'd', 'option_d') ?? 5;
                    $optionECol = $this->getColumnIndex($columnMapping, 'option e', 'e', 'option_e') ?? 6;
                    $answerCol = $this->getColumnIndex($columnMapping, 'correct answer', 'answer', 'ans', 'correct') ?? 7;
                    
                    Log::info("CSV Column mapping: Question={$questionCol}, A={$optionACol}, B={$optionBCol}, C={$optionCCol}, D={$optionDCol}, E={$optionECol}, Answer={$answerCol}");
                    
                    $row++;
                    continue;
                }
                
                // Log the data for debugging
                if ($row <= 3) {
                    Log::info('Row ' . $row . ' data: ' . json_encode($data) . ' (columns: ' . count($data) . ')');
                }
                
                // Ensure we have data
                if (count($data) > max($questionCol, $optionACol, $optionBCol, $optionCCol, $optionDCol, $answerCol)) {
                    // Clean up the data
                    $questionText = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[$questionCol] ?? ''));
                    $optionA = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[$optionACol] ?? ''));
                    $optionB = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[$optionBCol] ?? ''));
                    $optionC = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[$optionCCol] ?? ''));
                    $optionD = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[$optionDCol] ?? ''));
                    
                    // Check if option E column exists and has data
                    if (isset($data[$optionECol])) {
                        $optionE = trim(str_replace(["\r\n", "\r", "\n"], ' ', $data[$optionECol]));
                        if (empty($optionE)) {
                            $optionE = null;
                        }
                    } else {
                        $optionE = null;
                    }
                    
                    $correctAnswer = strtoupper(trim($data[$answerCol] ?? ''));
                    
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
                                'time_per_question' => $timePerQuestion,
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
    
    private function extractFormattedText($cell)
    {
        $cellValue = $cell->getValue();
        $baseBold = false;
        $baseColorCode = null;
        
        try {
            $baseFont = $cell->getWorksheet()->getStyle($cell->getCoordinate())->getFont();
            $baseBold = (bool) $baseFont->getBold();
            $baseColor = $baseFont->getColor();
            if ($baseColor) {
                $code = $baseColor->getRGB();
                if ($code && $code !== '000000' && preg_match('/^[0-9A-F]{6}$/i', $code)) {
                    $baseColorCode = $code;
                }
            }
        } catch (\Exception $e) {}

        if ($cellValue instanceof RichText) {
            $html = '';
            foreach ($cellValue->getRichTextElements() as $element) {
                $text = htmlspecialchars($element->getText(), ENT_QUOTES, 'UTF-8');
                $font = ($element instanceof \PhpOffice\PhpSpreadsheet\RichText\Run) ? $element->getFont() : null;

                $isBold = ($font !== null && $font->getBold() !== null) ? (bool) $font->getBold() : $baseBold;
                $colorCode = null;
                
                if ($font !== null && $font->getColor() !== null) {
                    $code = $font->getColor()->getRGB();
                    if ($code && $code !== '000000' && preg_match('/^[0-9A-F]{6}$/i', $code)) {
                        $colorCode = $code;
                    } else {
                        $colorCode = $baseColorCode;
                    }
                } else {
                    $colorCode = $baseColorCode;
                }

                if ($isBold && $colorCode) {
                    $html .= '<strong style="color:#' . $colorCode . '">' . $text . '</strong>';
                } elseif ($isBold) {
                    $html .= '<strong>' . $text . '</strong>';
                } elseif ($colorCode) {
                    $html .= '<span style="color:#' . $colorCode . '">' . $text . '</span>';
                } else {
                    $html .= $text;
                }
            }
            return $html;
        }

        $text = htmlspecialchars((string) $cellValue, ENT_QUOTES, 'UTF-8');
        if ($baseBold && $baseColorCode) {
            return '<strong style="color:#' . $baseColorCode . '">' . $text . '</strong>';
        } elseif ($baseBold) {
            return '<strong>' . $text . '</strong>';
        } elseif ($baseColorCode) {
            return '<span style="color:#' . $baseColorCode . '">' . $text . '</span>';
        }
        return $text;
    }
    
    /**
     * Get column headers from first row and make them case-insensitive
     */
    private function normalizeImportHeader($value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $normalized = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $normalized = str_replace(['_', '-', ' '], '', strtolower(trim((string) $normalized)));
        $normalized = preg_replace('/\s+/', '', $normalized);

        return trim($normalized);
    }

    private function getColumnMapping($worksheet)
    {
        $headerRow = $worksheet->rangeToArray('A1:H1', NULL, TRUE, FALSE)[0];
        $mapping = [];
        
        foreach ($headerRow as $index => $header) {
            $normalizedHeader = $this->normalizeImportHeader($header);
            if ($normalizedHeader !== '') {
                $mapping[$normalizedHeader] = $index;
            }
        }
        
        return $mapping;
    }
    
    /**
     * Get column index by name (case-insensitive)
     */
    private function getColumnIndex($mapping, ...$possibleNames)
    {
        foreach ($possibleNames as $name) {
            $normalized = $this->normalizeImportHeader((string) $name);
            if ($normalized !== '' && isset($mapping[$normalized])) {
                return $mapping[$normalized];
            }
        }
        
        return null;
    }
    
    private function processExcelFileContent($file, $quizId, $subjectId, $timePerQuestion = 60)
    {
        $questionsCreated = 0;
        $questionsSkipped = 0;
        $skippedReasons = [];
        $filePath = $file->getRealPath();
        
        try {
            Log::info('Processing Excel file with PhpSpreadsheet: ' . $filePath);
            
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            
            Log::info("Excel file loaded: {$highestRow} rows, columns up to {$highestColumn}");
            
            $columnMapping = $this->getColumnMapping($worksheet);
            
            $numCol = $this->getColumnIndex($columnMapping, 'number', 'num', '#', 'no') ?? 0;
            $questionCol = $this->getColumnIndex($columnMapping, 'question', 'q') ?? 1;
            $diagramCol = $this->getColumnIndex($columnMapping, 'diagram', 'image', 'picture') ?? 2;
            $optionACol = $this->getColumnIndex($columnMapping, 'option a', 'a', 'option_a') ?? 3;
            $optionBCol = $this->getColumnIndex($columnMapping, 'option b', 'b', 'option_b') ?? 4;
            $optionCCol = $this->getColumnIndex($columnMapping, 'option c', 'c', 'option_c') ?? 5;
            $optionDCol = $this->getColumnIndex($columnMapping, 'option d', 'd', 'option_d') ?? 6;
            $optionECol = $this->getColumnIndex($columnMapping, 'option e', 'e', 'option_e') ?? 7;
            $answerCol = $this->getColumnIndex($columnMapping, 'correct answer', 'answer', 'ans', 'correct') ?? 8;
            
            Log::info("Column mapping: Question={$questionCol}, Diagram={$diagramCol}, A={$optionACol}, B={$optionBCol}, C={$optionCCol}, D={$optionDCol}, E={$optionECol}, Answer={$answerCol}");
            
            $imagesByRow = $this->extractImagesFromWorksheet($worksheet);
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $questionText = $this->extractFormattedText($worksheet->getCellByColumnAndRow($questionCol + 1, $row));
                $optionA = $this->extractFormattedText($worksheet->getCellByColumnAndRow($optionACol + 1, $row));
                $optionB = $this->extractFormattedText($worksheet->getCellByColumnAndRow($optionBCol + 1, $row));
                $optionC = $this->extractFormattedText($worksheet->getCellByColumnAndRow($optionCCol + 1, $row));
                $optionD = $this->extractFormattedText($worksheet->getCellByColumnAndRow($optionDCol + 1, $row));
                $optionE = $this->extractFormattedText($worksheet->getCellByColumnAndRow($optionECol + 1, $row));
                $correctAnswer = strtoupper(trim(strip_tags($this->extractFormattedText($worksheet->getCellByColumnAndRow($answerCol + 1, $row)))));
                
                $questionText = trim(preg_replace('/\s+/', ' ', $questionText));
                $optionA = trim(preg_replace('/\s+/', ' ', $optionA));
                $optionB = trim(preg_replace('/\s+/', ' ', $optionB));
                $optionC = trim(preg_replace('/\s+/', ' ', $optionC));
                $optionD = trim(preg_replace('/\s+/', ' ', $optionD));
                $optionE = trim(preg_replace('/\s+/', ' ', $optionE));
                
                $questionTextPlain = strip_tags($questionText);
                $optionAPlain = strip_tags($optionA);
                $optionBPlain = strip_tags($optionB);
                $optionCPlain = strip_tags($optionC);
                $optionDPlain = strip_tags($optionD);
                $optionEPlain = strip_tags($optionE);
                
                if ($row <= 4) {
                    Log::info("Row {$row} - Question: {$questionTextPlain}, Answer: {$correctAnswer}");
                }
                
                if (empty($optionEPlain)) {
                    $optionE = null;
                }
                
                $hasValidOptions = !empty($questionTextPlain) && !empty($optionAPlain) && !empty($optionBPlain) && !empty($optionCPlain) && !empty($optionDPlain);
                $hasValidAnswer = in_array($correctAnswer, ['A', 'B', 'C', 'D', 'E']);
                
                if ($correctAnswer === 'E' && empty($optionEPlain)) {
                    $hasValidAnswer = false;
                }
                
                if ($hasValidOptions && $hasValidAnswer) {
                    try {
                        $diagramFilename = null;
                        if (isset($imagesByRow[$row])) {
                            $diagramFilename = $this->saveImageToStorage($imagesByRow[$row], $questionsCreated + 1);
                        }
                        
                        $questionData = [
                            'quiz_id' => $quizId,
                            'subject_id' => $subjectId,
                            'question' => $questionText,
                            'diagram' => $diagramFilename,
                            'option_a' => $optionA,
                            'option_b' => $optionB,
                            'option_c' => $optionC,
                            'option_d' => $optionD,
                            'correct_answer' => $correctAnswer,
                            'time_per_question' => $timePerQuestion,
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
                    if (empty($questionTextPlain)) $reason[] = 'empty question';
                    if (empty($optionAPlain)) $reason[] = 'empty option A';
                    if (empty($optionBPlain)) $reason[] = 'empty option B';
                    if (empty($optionCPlain)) $reason[] = 'empty option C';
                    if (empty($optionDPlain)) $reason[] = 'empty option D';
                    if (!$hasValidAnswer) $reason[] = "invalid answer '{$correctAnswer}'";
                    if ($correctAnswer === 'E' && empty($optionEPlain)) $reason[] = 'answer is E but option E is empty';
                    
                    $reasonText = implode(', ', $reason);
                    $skippedReasons[] = "Row {$row}: {$reasonText}";
                    
                    Log::warning("Skipping row {$row} - validation failed: {$reasonText}");
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
        ')
        ->whereNotNull('submitted_at')
        ->groupBy('quiz_id')
        ->with(['quiz' => function($q) {
            $q->select('id', 'title', 'is_active');
        }])
        ->get();

        return view('admin.statistics', compact('stats'));
    }

    private function getQuizRankings($quizId)
    {
        // 6.3 — resolve expiry for all users in this quiz before computing rankings
        $resolver = app(QuestionAttemptResolver::class);
        $quizUserIds = DB::table('quiz_user')->where('quiz_id', $quizId)->pluck('user_id');
        foreach ($quizUserIds as $uid) {
            $resolver->resolveAllExpiredForQuiz($uid, $quizId);
        }

        $subjects = Subject::whereHas('questions', function($query) use ($quizId) {
            $query->where('quiz_id', $quizId);
        })->get();

        $totalMaxPoints = 0;
        foreach ($subjects as $subject) {
            $totalMaxPoints += ($subject->max_questions ?? 5) * ($subject->marks_per_question ?? 1);
        }

        // 6.3 — only count submitted attempts; fix N+1 by bulk-loading users
        $subjectCorrects = DB::table('quiz_attempts')
            ->join('questions', 'quiz_attempts.question_id', '=', 'questions.id')
            ->where('quiz_attempts.quiz_id', $quizId)
            ->whereNotNull('quiz_attempts.submitted_at')
            ->select('quiz_attempts.user_id', 'questions.subject_id',
                DB::raw('SUM(quiz_attempts.is_correct) as correct_count'))
            ->groupBy('quiz_attempts.user_id', 'questions.subject_id')
            ->get()
            ->groupBy('user_id');

        $lastAttempts = DB::table('quiz_attempts')
            ->where('quiz_id', $quizId)
            ->whereNotNull('submitted_at')
            ->select('user_id', DB::raw('MAX(submitted_at) as last_at'))
            ->groupBy('user_id')
            ->pluck('last_at', 'user_id');

        // Bulk-load all users in one query — fixes N+1
        $userIds   = $subjectCorrects->keys();
        $usersById = User::whereIn('id', $userIds)->get()->keyBy('id');
        $rankings  = [];

        foreach ($userIds as $userId) {
            $user = $usersById->get($userId);
            if (!$user) continue;

            $lastAt    = $lastAttempts[$userId] ?? null;
            $lastCarbon = $lastAt ? \Carbon\Carbon::parse($lastAt) : null;

            $userStats = [
                'user_id'           => $userId,
                'user_name'         => $user->name,
                'subjects'          => [],
                'total_points'      => 0,
                'total_max_points'  => $totalMaxPoints,
                // kept for backward-compat references
                'total_correct'     => 0,
                'total_expected'    => $totalMaxPoints,
                'last_attempt_date' => $lastCarbon ? $lastCarbon->format('M j, Y') : 'N/A',
                'last_attempt_time' => $lastCarbon ? $lastCarbon->format('g:i A') : '',
            ];

            foreach ($subjects as $subject) {
                $marks    = $subject->marks_per_question ?? 1;
                $maxQ     = $subject->max_questions ?? 5;
                $maxPts   = $maxQ * $marks;

                $correctRow = $subjectCorrects[$userId]
                    ->firstWhere('subject_id', $subject->id);
                $correct = $correctRow ? (int)$correctRow->correct_count : 0;
                $points  = $correct * $marks;

                $userStats['subjects'][$subject->id] = [
                    'correct'    => $correct,
                    'expected'   => $maxQ,
                    'marks'      => $marks,
                    'points'     => $points,
                    'max_points' => $maxPts,
                ];

                $userStats['total_points']  += $points;
                $userStats['total_correct'] += $correct;   // raw correct count
            }

            $userStats['percentage'] = $totalMaxPoints > 0
                ? round(($userStats['total_points'] / $totalMaxPoints) * 100, 2)
                : 0;

            $rankings[] = $userStats;
        }

        // Sort by total points (descending), then by percentage
        usort($rankings, function($a, $b) {
            if ($a['total_points'] == $b['total_points']) {
                return $b['percentage'] <=> $a['percentage'];
            }
            return $b['total_points'] <=> $a['total_points'];
        });

        foreach ($rankings as $index => &$ranking) {
            $ranking['position'] = $index + 1;
        }

        return ['rankings' => $rankings, 'subjects' => $subjects];
    }

    public function quizAnalysis($quizId)
    {
        $quiz = Quiz::select('id', 'title', 'is_active')->findOrFail($quizId);
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
                    ? $ranking['subjects'][$subject->id]['points'] . '/' . $ranking['subjects'][$subject->id]['max_points']
                    : '-';
                $sheet->setCellValueByColumnAndRow($col++, $row, $value);
            }

            $sheet->setCellValueByColumnAndRow($col++, $row, $ranking['total_points'] . '/' . $ranking['total_max_points']);
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
        $quiz    = Quiz::select('id', 'title')->findOrFail($quizId);
        $subject = Subject::select('id', 'name', 'max_questions', 'marks_per_question')->findOrFail($subjectId);

        // 6.3 — resolve expiry for all users in this quiz before computing stats
        $resolver    = app(QuestionAttemptResolver::class);
        $quizUserIds = DB::table('quiz_user')->where('quiz_id', $quizId)->pluck('user_id');
        foreach ($quizUserIds as $uid) {
            $resolver->resolveAllExpiredForQuiz($uid, $quizId);
        }

        $marks  = $subject->marks_per_question ?? 1;
        $maxQ   = $subject->max_questions ?? 5;
        $maxPts = $maxQ * $marks;

        $rankings = QuizAttempt::where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->whereNotNull('submitted_at')
            ->selectRaw('user_id, COUNT(*) as total, SUM(is_correct) as correct, MAX(submitted_at) as last_attempt_at')
            ->groupBy('user_id')
            ->with(['user' => function($q) {
                $q->select('id', 'name', 'email');
            }])
            ->get()
            ->map(function($item) use ($marks, $maxPts) {
                $item->points = (int)$item->correct * $marks;
                $item->max_points = $maxPts;
                $item->percentage = $maxPts > 0 ? round(($item->points / $maxPts) * 100, 2) : 0;
                $item->last_attempt_date = $item->last_attempt_at ? \Carbon\Carbon::parse($item->last_attempt_at)->format('M j, Y') : 'N/A';
                $item->last_attempt_time = $item->last_attempt_at ? \Carbon\Carbon::parse($item->last_attempt_at)->format('g:i A') : '';
                return $item;
            })
            ->sortByDesc('percentage')
            ->values();

        return view('admin.subject_analysis', compact('quiz', 'subject', 'rankings'));
    }

    public function studentSubjectReview($quizId, $subjectId, $userId)
    {
        $quiz    = Quiz::select('id', 'title')->findOrFail($quizId);
        $subject = Subject::select('id', 'name')->findOrFail($subjectId);
        $user    = User::select('id', 'name', 'email')->findOrFail($userId);

        // 6.3/6.7 — resolve expiry before reading attempts
        app(QuestionAttemptResolver::class)->resolveAllExpiredForQuiz($userId, $quizId);

        $allQuestions = Question::where('quiz_id', $quizId)
            ->where('subject_id', $subjectId)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        $attempts = QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->whereHas('question', function($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->whereNotNull('submitted_at')
            ->with(['question' => function($q) {
                $q->select('id', 'question', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_answer');
            }])
            ->get();

        foreach ($attempts as $attempt) {
            $attempt->question_number = array_search($attempt->question_id, $allQuestions) + 1;
        }

        return view('admin.student_subject_review', compact('quiz', 'subject', 'user', 'attempts'));
    }

    public function leaderboard()
    {
        $quizzes = Quiz::select('id', 'title', 'is_active', 'created_at')
            ->withCount('questions')
            ->withCount(['attempts as total_attempts'])
            ->withCount(['users as participants_count'])
            ->get();

        return view('admin.leaderboard', compact('quizzes'));
    }

    public function quizLeaderboard($quizId)
    {
        $quiz = Quiz::select('id', 'title', 'is_active')->findOrFail($quizId);
        $data = $this->getQuizRankings($quizId);
        $rankings = $data['rankings'];
        $subjects = $data['subjects'];
        
        return view('admin.quiz_leaderboard', compact('quiz', 'subjects', 'rankings'));
    }

    public function studentQuizDetails($quizId, $userId)
    {
        $quiz = Quiz::select('id', 'title')->findOrFail($quizId);
        $user = User::select('id', 'name', 'email')->findOrFail($userId);

        // 6.3/6.7 — resolve expiry before reading attempts
        app(QuestionAttemptResolver::class)->resolveAllExpiredForQuiz($userId, $quizId);

        $attempts = QuizAttempt::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->with(['question' => function($q) {
                $q->select('id', 'question', 'subject_id', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_answer')
                  ->with(['subject' => function($sq) {
                      $sq->select('id', 'name');
                  }]);
            }])
            ->get();

        // 6.6 — three distinct outcome states
        $correct    = $attempts->where('is_correct', true)->count();
        $incorrect  = $attempts->where('is_correct', false)->where('is_auto_expired', false)->count();
        $unanswered = $attempts->where('is_correct', false)->where('is_auto_expired', true)->count();

        $stats = [
            'total'      => $attempts->count(),
            'correct'    => $correct,
            'incorrect'  => $incorrect,
            'unanswered' => $unanswered,
            'accuracy'   => $attempts->count() > 0
                ? round(($correct / $attempts->count()) * 100, 2) : 0,
        ];

        return view('admin.student_quiz_details', compact('quiz', 'user', 'attempts', 'stats'));
    }

    public function deleteQuiz($id)
    {
        $quiz = Quiz::findOrFail($id);

        DB::transaction(function () use ($id, $quiz) {
            // 6.2 — cascade-close any open attempts before deleting
            QuizAttempt::where('quiz_id', $id)
                ->where('locked', false)
                ->whereNotNull('started_at')
                ->update([
                    'locked'          => true,
                    'submitted'       => true,
                    'is_auto_expired' => true,
                    'submitted_at'    => now(),
                    'locked_at'       => now(),
                ]);

            $questionIds = Question::where('quiz_id', $id)->pluck('id');
            QuizAttempt::whereIn('question_id', $questionIds)->delete();
            QuizAttempt::where('quiz_id', $id)->delete();
            Question::where('quiz_id', $id)->delete();
            $quiz->delete();
        });

        return back()->with('success', 'Quiz deleted successfully.');
    }

    public function dashboardStatsApi()
    {
        $stats = $this->dashboardStats->getStatistics();
        
        // Calculate from actual database records
        $totalQuestions = Question::count();
        $totalQuizzes = Quiz::count();
        $totalUsers = User::where('role', 'quizzer')->count();
        $pendingApprovals = User::where('role', 'quizzer')->where('is_approved', false)->count();
        
        // Get actual user performance from attempts
        $userStats = User::where('role', 'quizzer')
            ->where('is_approved', true)
            ->withCount([
                'attempts as total_attempts' => function($query) {
                    $query->where('submitted', true);
                },
                'attempts as correct_answers' => function($query) {
                    $query->where('submitted', true)->where('is_correct', true);
                }
            ])
            ->having('total_attempts', '>', 0)
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'total_attempts' => $user->total_attempts,
                    'correct_answers' => $user->correct_answers,
                    'accuracy' => $user->total_attempts > 0 ? round(($user->correct_answers / $user->total_attempts) * 100, 1) : 0
                ];
            })
            ->sortByDesc('accuracy')
            ->take(3)
            ->values();

        return response()->json([
            'total_questions' => $totalQuestions,
            'total_quizzes' => $totalQuizzes,
            'total_users' => $totalUsers,
            'pending_approvals' => $pendingApprovals,
            'top3' => $userStats
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
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
        $csv = "Number,Question,Diagram,Option A,Option B,Option C,Option D,Option E,Correct Answer\n";
        $csv .= "1,What is the normal heart rate?,[paste image here],60-100 bpm,40-60 bpm,100-120 bpm,120-140 bpm,,A\n";
        $csv .= "2,Which organ produces insulin?,[paste image here],Liver,Pancreas,Kidney,Spleen,Heart,B\n";
        $csv .= "3,What is the largest bone in the human body?,[paste image here],Femur,Tibia,Humerus,Radius,,A\n";
        $csv .= "4,How many chambers does the human heart have?,[paste image here],2,3,4,5,6,C\n";
        
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
            'title'     => $request->title,
            'is_active' => $request->boolean('is_active'),
            'lock_mode' => in_array($request->lock_mode, ['per_user', 'global'])
                ? $request->lock_mode : 'per_user',
        ]);
        return response()->json(['success' => true]);
    }
    
    public function updateQuizUsers(Request $request, $id)
    {
        $request->validate([
            'user_ids'   => 'sometimes|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $quiz    = Quiz::findOrFail($id);
        $userIds = $request->input('user_ids', []);

        DB::transaction(function () use ($quiz, $id, $userIds) {
            // 6.2 — find users being removed and cascade-close their open attempts
            $currentUserIds = $quiz->users()->pluck('users.id')->toArray();
            $removedUserIds = array_diff($currentUserIds, $userIds);

            if (!empty($removedUserIds)) {
                QuizAttempt::where('quiz_id', $id)
                    ->whereIn('user_id', $removedUserIds)
                    ->where('locked', false)
                    ->whereNotNull('started_at')
                    ->update([
                        'locked'          => true,
                        'submitted'       => true,
                        'is_auto_expired' => true,
                        'submitted_at'    => now(),
                        'locked_at'       => now(),
                    ]);
            }

            $quiz->users()->sync($userIds);
        });

        return response()->json([
            'success'        => true,
            'message'        => 'User assignments updated successfully',
            'assigned_count' => count($userIds),
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
                $maxQuestions = isset($subjectData['max_questions']) ? (int)$subjectData['max_questions'] : 5;
                $marksPerQuestion = isset($subjectData['marks_per_question']) ? (int)$subjectData['marks_per_question'] : 1;
                
                $subject = Subject::firstOrCreate(
                    ['name' => $subjectName],
                    ['max_questions' => $maxQuestions, 'marks_per_question' => $marksPerQuestion]
                );
                
                // Update max_questions for existing subject
                $subject->update([
                    'max_questions' => $maxQuestions,
                    'marks_per_question' => $marksPerQuestion,
                ]);
                
                $timePerQuestion = isset($subjectData['time_per_question']) ? (int)$subjectData['time_per_question'] : 60;
                
                if (isset($subjectData['file'])) {
                    $questionsCreated = $this->processExcelFile($subjectData['file'], $quiz->id, $subject->id, $timePerQuestion);
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

    public function deleteQuizSubject($quizId, $subjectId)
    {
        $quiz = Quiz::findOrFail($quizId);
        
        \DB::beginTransaction();
        
        try {
            // Get all question IDs for this quiz and subject
            $questionIds = Question::where('quiz_id', $quizId)
                                 ->where('subject_id', $subjectId)
                                 ->pluck('id');
            
            // Delete quiz attempts for these questions
            if ($questionIds->isNotEmpty()) {
                QuizAttempt::whereIn('question_id', $questionIds)->delete();
            }
            
            // Delete all questions for this quiz and subject
            $deletedCount = Question::where('quiz_id', $quizId)
                                  ->where('subject_id', $subjectId)
                                  ->delete();
            
            \DB::commit();
            
            return response()->json([
                'success' => true, 
                'message' => "Subject deleted successfully. {$deletedCount} questions removed."
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Failed to delete subject: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadSound(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $request->validate([
                'sound_type' => 'required|in:correct,incorrect,timer,warning',
                'sound_file' => 'required|file|mimes:mp3,wav,ogg|max:2048', // Max 2MB
            ]);

            $soundType = $request->sound_type;
            $file = $request->file('sound_file');
            
            // Create sounds directory if it doesn't exist
            $soundsPath = public_path('sounds');
            if (!file_exists($soundsPath)) {
                mkdir($soundsPath, 0755, true);
            }

            // Check if sound already exists in database
            $existingSound = QuizSound::where('sound_type', $soundType)->first();
            
            if ($existingSound) {
                // Delete old file
                $oldFilePath = public_path($existingSound->file_path);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Get the file extension and generate filename
            $extension = $file->getClientOriginalExtension();
            $fileName = $soundType . '.' . $extension;
            $filePath = 'sounds/' . $fileName;
            
            // Get file info BEFORE moving (important - file info not available after move)
            $mimeType = $file->getClientMimeType();
            $fileSize = $file->getSize();
            
            // Save the new file
            $file->move($soundsPath, $fileName);

            // Save or update in database
            QuizSound::updateOrCreate(
                ['sound_type' => $soundType],
                [
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'uploaded_by' => Auth::id(),
                ]
            );

            DB::commit();
            Log::info("Sound uploaded successfully: {$fileName} by user " . Auth::id());

            return redirect()->route('admin.sounds')
                ->with('audio_success', ucfirst($soundType) . ' answer sound uploaded successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->route('admin.sounds')
                ->with('audio_error', 'Invalid file. Please upload MP3, WAV, or OGG file (max 2MB).');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sound upload error: ' . $e->getMessage());
            return redirect()->route('admin.sounds')
                ->with('audio_error', 'Error uploading sound: ' . $e->getMessage());
        }
    }

    public function deleteSound(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $request->validate([
                'sound_type' => 'required|in:correct,incorrect,timer,warning',
            ]);

            $soundType = $request->sound_type;
            
            // Find the sound in database
            $sound = QuizSound::where('sound_type', $soundType)->first();

            if (!$sound) {
                return redirect()->route('admin.sounds')
                    ->with('audio_error', 'Sound record not found in database.');
            }

            // Delete the physical file
            $filePath = public_path($sound->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info("Sound file deleted: {$sound->file_name}");
            }

            // Delete from database
            $sound->delete();
            
            DB::commit();

            return redirect()->route('admin.sounds')
                ->with('audio_success', ucfirst($soundType) . ' answer sound deleted. Using default generated sound.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sound deletion error: ' . $e->getMessage());
            return redirect()->route('admin.sounds')
                ->with('audio_error', 'Error deleting sound: ' . $e->getMessage());
        }
    }

    public function updateSubjectTime(Request $request, $quizId, $subjectId)
    {
        DB::beginTransaction();

        try {
            $timePerQuestion = (int) $request->input('time_per_question');

            if ($timePerQuestion < 10 || $timePerQuestion > 600) {
                return response()->json(['success' => false, 'message' => 'Time must be between 10 and 600 seconds'], 400);
            }

            // 6.1 — block if any student has an active attempt on these questions
            $hasActiveAttempt = QuizAttempt::where('quiz_id', $quizId)
                ->whereHas('question', fn($q) => $q->where('subject_id', $subjectId))
                ->where('locked', false)
                ->whereNotNull('started_at')
                ->exists();

            if ($hasActiveAttempt) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Cannot update time while a student has an active attempt on this subject.'], 409);
            }

            // 6.1 — only update time_per_question on questions; never touch expires_at on existing attempts
            $updatedCount = Question::where('quiz_id', $quizId)
                ->where('subject_id', $subjectId)
                ->lockForUpdate()
                ->update(['time_per_question' => $timePerQuestion]);

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Time updated successfully for all questions in this subject.',
                'updated_count' => $updatedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update time: ' . $e->getMessage()], 500);
        }
    }

    // 6.4 — was private, route was registered but would fatal-error; now public
    public function updateSubjectSettings(Request $request, $quizId, $subjectId)
    {
        DB::beginTransaction();

        try {
            $timePerQuestion  = (int) $request->input('time_per_question');
            $maxQuestions     = (int) $request->input('max_questions', 5);
            $marksPerQuestion = (int) $request->input('marks_per_question', 1);

            if ($timePerQuestion < 10 || $timePerQuestion > 600) {
                return response()->json(['success' => false, 'message' => 'Time must be between 10 and 600 seconds'], 400);
            }
            if ($marksPerQuestion < 1) {
                return response()->json(['success' => false, 'message' => 'Marks per question must be at least 1'], 400);
            }

            // 6.1 — block if any student has an active attempt on these questions
            $hasActiveAttempt = QuizAttempt::where('quiz_id', $quizId)
                ->whereHas('question', fn($q) => $q->where('subject_id', $subjectId))
                ->where('locked', false)
                ->whereNotNull('started_at')
                ->exists();

            if ($hasActiveAttempt) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Cannot update settings while a student has an active attempt on this subject.'], 409);
            }

            // 6.5 — lockForUpdate prevents concurrent admin edits corrupting the same rows
            $updatedCount = Question::where('quiz_id', $quizId)
                ->where('subject_id', $subjectId)
                ->lockForUpdate()
                ->update(['time_per_question' => $timePerQuestion]);

            $subject = Subject::find($subjectId);
            if ($subject) {
                $subject->lockForUpdate();
                $subject->max_questions     = $maxQuestions;
                $subject->marks_per_question = $marksPerQuestion;
                $subject->save();
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Settings updated successfully for all questions in this subject.',
                'updated_count' => $updatedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update settings: ' . $e->getMessage()], 500);
        }
    }

    private function extractImagesFromWorksheet($worksheet)
    {
        $imagesByRow = [];
        
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            $coordinates = $drawing->getCoordinates();
            preg_match('/([A-Z]+)(\d+)/', $coordinates, $matches);
            
            if (isset($matches[2])) {
                $row = (int)$matches[2];
                
                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {
                    $imagesByRow[$row] = [
                        'type' => 'file',
                        'path' => $drawing->getPath()
                    ];
                } elseif ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                    $imagesByRow[$row] = [
                        'type' => 'memory',
                        'resource' => $drawing->getImageResource(),
                        'mime' => $drawing->getMimeType()
                    ];
                }
            }
        }
        
        return $imagesByRow;
    }

    private function saveImageToStorage($imageData, $questionNumber)
    {
        $storagePath = storage_path('app/public/question_diagrams');
        
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        $filename = 'question_' . $questionNumber . '.png';
        $fullPath = $storagePath . '/' . $filename;
        
        try {
            if ($imageData['type'] === 'file') {
                copy($imageData['path'], $fullPath);
            } elseif ($imageData['type'] === 'memory') {
                imagepng($imageData['resource'], $fullPath);
            }
            
            return $filename;
        } catch (\Exception $e) {
            Log::error('Image save error: ' . $e->getMessage());
            return null;
        }
    }
}