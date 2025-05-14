<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\QuestionAttachment;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SchoolExamQuestionController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
        $this->authorizeTeacher();
        $this->authorizeTeacherPengelola();
    }

    public function v_add_soal($ujian_id)
    {
        $examSections = collect($this->fetchData('/api/v1/cms/teacher/exam-section/')->data ?? [])
            ->filter(fn($section) => $section->exam_id == $ujian_id)
            ->toArray();

        $data = [
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $this->fetchData('/api/v1/cms/teacher/category-question')->data ?? [],
            'ujian_id' => $ujian_id,
            'exam_sections' => $examSections,
        ];

        return view('teacher.sekolah.soal.add', $data);
    }

    public function add_soal(Request $request, $ujian_id)
    {

        $validate = $this->validateSoal($request);
        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $form_data = $this->prepareSoalData($request, $ujian_id);

        $response_data = $this->postData('/api/v1/cms/teacher/question', $form_data, 'json');

        $this->handleFileManagement($request, $response_data->data->id);

        return $this->handleUlanganResponse($response_data, 'add', $ujian_id);
    }

    public function v_edit_soal($ujian_id, $soal_id)
    {
        $examSections = collect($this->fetchData('/api/v1/cms/teacher/exam-section/')->data ?? [])
            ->filter(fn($section) => $section->exam_id == $ujian_id)
            ->toArray();

        $data = [
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $this->fetchData('/api/v1/cms/teacher/category-question')->data ?? [],
            'ujian_id' => $ujian_id,
            'question' => $this->fetchData('/api/v1/cms/teacher/question/' . $soal_id)->data ?? [],
            'soal_id' => $soal_id,
            'exam_sections' => $examSections,
        ];

        return view('teacher.sekolah.soal.edit', $data);
    }

    public function edit_soal(Request $request, $ujian_id, $soal_id)
    {

        $validate = $this->validateSoal($request);
        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $form_data = $this->prepareSoalData($request, $ujian_id);

        $response_data = $this->putData('/api/v1/cms/teacher/question/' . $soal_id, $form_data, 'json');

        $this->handleFileManagement($request, $response_data->data->id);

        return $this->handleUlanganResponse($response_data, 'edit', $ujian_id);
    }

    public function import_soal(Request $request, $ujian_id)
    {

        $validator = Validator::make($request->all(), [
            'bank_question_ids' => 'required|array|exists:bank_questions,id',
            'difficulty_levels' => 'required|array',
            'difficulty_levels.*' => 'in:Sangat Mudah,Mudah,Sedang,Sulit,Sangat Sulit',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $bankQuestionIds = $request->input('bank_question_ids', []);
        $difficultyLevels = $request->input('difficulty_levels', []);

        $responses = [];
        $successfulIds = [];
        $allSuccessful = true;

        foreach ($bankQuestionIds as $bankQuestionId) {
            $response_question_bank = $this->fetchData('/api/v1/cms/teacher/question-bank/' . $bankQuestionId);

            if (!isset($response_question_bank->data)) {
                $allSuccessful = false;
                $responses[$bankQuestionId] = ['success' => false, 'message' => 'Data tidak ditemukan untuk ID ' . $bankQuestionId];
                continue;
            }

            $difficulty = $difficultyLevels[$bankQuestionId] ?? 'Sedang';

            $form_data = [
                'school_exam_id' => $ujian_id,
                'question_text' => $response_question_bank->data->question_text,
                'question_type' => $response_question_bank->data->question_type,
                'point' => $response_question_bank->data->point,
                'grade_method' => $response_question_bank->data->grade_method,
                'choices' => array_map(function ($choice) {
                    return [
                        'choice_text' => $choice->choice_text,
                        'is_true' => $choice->is_true,
                    ];
                }, $response_question_bank->data->choices_banks ?? []),
                'category_id' => $response_question_bank->data->category_id,
                'difficult' => $difficulty,
                'resources' => array_map(function ($resource) {
                    return [
                        'file_name' => $resource->file_name,
                        'file_type' => $resource->file_type,
                        'file_url' => $resource->file_url,
                        'file_size' => $resource->file_size,
                        'file_extension' => $resource->file_extension,
                    ];
                }, $response_question_bank->data->question_attachment_banks ?? []),
            ];


            $response_data = $this->postData('/api/v1/cms/teacher/question', $form_data, 'json');

            if (!isset($response_data->success) || !$response_data->success) {
                $allSuccessful = false;
                $responses[$bankQuestionId] = ['success' => false, 'message' => 'Gagal mengimpor soal untuk ID ' . $bankQuestionId];
                break;
            } else {
                $responses[$bankQuestionId] = ['success' => true];
                $successfulIds[] = $bankQuestionId;
            }
        }

        if ($allSuccessful) {
            return $this->handleUlanganResponse($response_data, 'import', $ujian_id);
        } else {
            foreach ($successfulIds as $successfulId) {
                $this->deleteData('/api/v1/cms/teacher/question/' . $successfulId);
            }

            $errorMessages = array_filter($responses, function ($response) {
                return !$response['success'];
            });
            $firstErrorMessage = reset($errorMessages)['message'];

            return redirect()->back()->withInput()->withErrors(['message' => $firstErrorMessage]);
        }
    }


    public function delete_soal($ujian_id, $soal_id)
    {

        $response_data = $this->deleteData('/api/v1/cms/teacher/question/' . $soal_id);

        return $this->handleUlanganResponse($response_data, 'delete', $ujian_id);
    }

    public function multi_delete_soal(Request $request, $ujian_id)
    {

        $deleteIds = json_decode($request->input('deleteIds'), true);
        $validate = Validator::make(['deleteIds' => $deleteIds], [
            'deleteIds' => 'required|array|min:1',
            'deleteIds.*' => 'required|exists:questions,id',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $response_data = new \stdClass();
        $response_data->success = true;
        $response_data->message = 'Semua soal berhasil dihapus';

        foreach ($deleteIds as $id) {
            $result = $this->deleteData('/api/v1/cms/teacher/question/' . $id);

            if (!$result->success) {
                $response_data->success = false;
                $response_data->message = 'Terjadi kesalahan saat menghapus beberapa soal';
                break;
            }
        }

        return $this->handleUlanganResponse($response_data, 'delete', $ujian_id);
    }

    public function download_soal($ujian_id, $soal_id)
    {
        $resource = QuestionAttachment::find($soal_id);

        if (!$resource) {
            return redirect()->back()->withInput()->withErrors(['message' => 'Resource not found.']);
        }

        $filePath = str_replace('storage/public/', 'public/', $resource->file_url);

        $absolutePath = storage_path('app/' . $filePath);

        if (!file_exists($absolutePath)) {
            return redirect()->back()->withInput()->withErrors(['message' => 'File not found.']);
        }

        $fileName = basename($filePath);

        return response()->download($absolutePath, $fileName);
    }

    private function validateSoal(Request $request)
    {
        return Validator::make($request->all(), [
            'section_id' => 'required|exists:exam_sections,id',
            'category_id' => 'required|exists:question_categories,id',
            'question_text' => 'required|string|max:255',
            'question_type' => 'required|in:Essay,Pilihan Ganda,Pilihan Ganda Complex,True False',
            'point' => 'required|integer',
            'grade_method' => 'nullable|string|max:255',
            'difficult' => 'required|string|max:255',
            'choices' => 'nullable|array',
            'choices.*.choice_text' => 'nullable|string|max:255',
            'choices.*.is_true' => 'nullable|boolean',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable',
            'resources.*.file_type' => 'nullable|string',
        ]);
    }

    private function prepareSoalData(Request $request, $ujian_id)
    {
        $choices = $request->input('choices', []);
        $correctChoiceIndex = $request->input('correct_choice', null);
        if (!is_array($correctChoiceIndex)) {
            foreach ($choices as $index => $choice) {
                $choices[$index]['is_true'] = ($index == $correctChoiceIndex);
            }
        } else {
            foreach ($choices as $index => $choice) {
                $choices[$index]['is_true'] = in_array($index, $correctChoiceIndex);
            }
        }

        return [
            'school_exam_id' => $ujian_id,
            'section_id' => $request->input('section_id'),
            'category_id' => $request->input('category_id'),
            'question_text' => $request->input('question_text'),
            'question_type' => $request->input('question_type'),
            'point' => $request->input('point'),
            'grade_method' => $request->input('grade_method'),
            'difficult' => $request->input('difficult'),
            'choices' => $choices,
        ];
    }

    private function handleUlanganResponse($response_data, $type, $ujian_id)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $dataToSend = $this->prepareViewData('/api/v1/cms/teacher/school-exam');

        return redirect()->route('teacher.sekolah.v_ujian_detail', ['ujian_id' => $ujian_id])
            ->with('message', $message)->with('alertClass', $alertClass)->with('dataToSend', $dataToSend);
    }

    private function prepareViewData($apiUrl)
    {
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $schoolExams = $this->fetchData($apiUrl)->data ?? [];

        if (is_array($schoolExams)) {
            foreach ($schoolExams as &$item) {
                if (is_object($item)) {
                    $item->courses_name = $this->transformCourse($courseData, $item->course);
                    $item->class_level = $this->transformLevel($levels, $item->class_level);
                }
            }
        }

        $viewData = [
            'school_exams' => $schoolExams,
            'courses' => $courseData,
            'levels' => $levels,
        ];

        return $viewData;
    }

    private function handleFileManagement(Request $request, $questionId)
    {
        $deletedResources = json_decode($request->input('deleted_resources'), true);
        $this->deleteResources($deletedResources);
        $this->saveResources($request->resources, $questionId);
    }

    private function deleteResources($deletedResources)
    {
        if (!empty($deletedResources)) {
            foreach ($deletedResources as $resourceId) {
                $resource = QuestionAttachment::find($resourceId);
                if ($resource) {
                    $filePath = str_replace('storage/public/', 'public/', $resource->file_url);
                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }
                    $resource->delete();
                }
            }
        }
    }

    private function saveResources($resources, $questionId)
    {
        if ($resources) {
            foreach ($resources as $resource) {
                if ($resource['file_type'] && $resource['file_name']) {
                    $resourceData = $this->mapResourceData($resource);
                    $uploadResult = $this->processResource($resourceData, 'questions');
                    if ($uploadResult['error']) {
                        return $this->sendError($uploadResult['message'], null, 200);
                    }

                    QuestionAttachment::create([
                        'id' => IdGenerator::generate(['table' => 'question_attachments', 'length' => 16, 'prefix' => 'QUA-']),
                        'question_id' => $questionId,
                        'file_name' => $resource['file_name'],
                        'file_type' => $resource['file_type'],
                        'file_url' => $uploadResult['path'],
                        'file_size' => $uploadResult['size'],
                        'file_extension' => $uploadResult['extension'],
                    ]);
                }
            }
        }
    }

    private function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/course');
        return $response_data->data ?? [];
    }

    private function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/class');
        return $response_data->data ?? [];
    }

    private function getResponseMessage($success, $type, $message)
    {

        if ($success) {
            switch ($type) {
                case 'add':
                    return 'Data berhasil ditambahkan.';
                case 'edit':
                    return 'Data berhasil diperbarui.';
                case 'delete':
                    return 'Data berhasil dihapus.';
                case 'import':
                    return 'Data berhasil diimport.';
                default:
                    return $message;
            }
        }

        return 'Terjadi kesalahan: ' . $message;
    }
}
