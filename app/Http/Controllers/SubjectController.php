<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Http\Requests\SubjectOrderRequest;
use App\Http\Requests\SubjectFileUpdateRequest;
use App\Services\SubjectFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    protected $fileService;

    public function __construct(SubjectFileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        $subjects = Subject::ordered()
            ->withCount('questions')
            ->get();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function updateOrder(SubjectOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $orderedIds = $request->validated()['subject_ids'];

            foreach ($orderedIds as $index => $subjectId) {
                Subject::where('id', $subjectId)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Subject order updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
    }

    public function replaceFile(SubjectFileUpdateRequest $request, Subject $subject)
    {
        try {
            $result = $this->fileService->replaceSubjectFile(
                $subject,
                $request->file('excel_file'),
                $request->validated()
            );

            return response()->json([
                'success'             => true,
                'message'             => $result['message'],
                'questions_processed' => $result['questions_count'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'File replacement failed: ' . $e->getMessage()], 500);
        }
    }
}