<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\QuestionAttachment;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Psy\Readline\Hoa\Console;

class PembelajaranUlanganController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_ulangan(Request $request, $learning_id)
    {
        $dataToSend = $this->prepareUlanganDataToSend($learning_id);

        $filtered_class_exams = $dataToSend['class_exams'];
        $questions = $dataToSend['questions'];

        $filtered_class_exams = collect($filtered_class_exams)->map(function ($class_exam) use ($questions) {
            $class_exam->question_count = collect($questions ?? [])->where('exam_id', $class_exam->id)->count();
            return $class_exam;
        });

        $dataToSend['class_exams'] = $filtered_class_exams;

        return view('teacher.pengajar.pembelajaran.ulangan.index', $dataToSend);
    }

    public function v_add_ulangan(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareUlanganDataToSend($learning_id, null);

        return view('teacher.pengajar.pembelajaran.ulangan.add', $dataToSend);
    }

    public function add_ulangan(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $validator = $this->validateUlangan($request);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $form_data = $this->prepareUlanganData($request, $learning_id);
        $form_data['status'] = 'Inactive';

        $response_data = $this->postData('/api/v1/cms/teacher/class-exam', $form_data, 'json');

        return $this->handleUlanganResponse($response_data, 'add', $learning_id, true, $response_data->data->id);
    }

    public function v_edit_ulangan(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareUlanganDataToSend($learning_id, $ulangan_id);

        $categories = $dataToSend['categories'];
        $questions = $dataToSend['questions'];
        $learning = $dataToSend['learning'];

        $categoryCollection = collect($categories);
        $filtered_questions = collect($questions ?? [])->filter(function ($question) use ($ulangan_id) {
            return $question->exam_id == $ulangan_id;
        });

        $questionsWithCategoryNames = $filtered_questions->map(function ($question) use ($categoryCollection) {
            $category = $categoryCollection->firstWhere('id', $question->category_id);
            $question->category_name = $category ? $category->name : 'Unknown';

            return $question;
        });

        $bank_questions = $this->fetchData('/api/v1/cms/teacher/question-bank');
        $filtered_bank_questions = collect($bank_questions->data ?? [])->filter(function ($bank_question) use ($learning) {
            return $bank_question->course == optional($learning)->course->id;
        });

        $bank_questions_with_category_names = $filtered_bank_questions->map(function ($bank_question) use ($categoryCollection) {
            $category = $categoryCollection->firstWhere('id', $bank_question->category_id);
            $bank_question->category_name = $category ? $category->name : 'Unknown';

            return $bank_question;
        })->values()->toArray();

        $dataToSend['questions'] = $questionsWithCategoryNames;
        $dataToSend['bank_questions'] = $bank_questions_with_category_names;

        return view('teacher.pengajar.pembelajaran.ulangan.edit', $dataToSend);
    }

    public function edit_ulangan(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $validator = $this->validateUlangan($request);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $form_data = $this->prepareUlanganData($request, $learning_id);
        $form_data['status'] = 'Inactive';

        $response_data = $this->putData('/api/v1/cms/teacher/class-exam/' . $ulangan_id, $form_data, 'json');

        return $this->handleUlanganResponse($response_data, 'edit', $learning_id, true, $ulangan_id);
    }

    public function edit_ulangan_is_active(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $request->merge(['is_active' => $request->is_active === 'on']);

        $validator = Validator::make($request->all(), [
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $response_data = $this->putData('/api/v1/cms/teacher/class-exam/' . $ulangan_id . '/update-is-active', ['is_active' => $request->is_active], 'json');

        return $this->handleUlanganResponse($response_data, 'edit', $learning_id, true, $ulangan_id);
    }

    public function delete_ulangan(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $response_data = $this->deleteData('/api/v1/cms/teacher/class-exam/' . $ulangan_id);

        return $this->handleUlanganResponse($response_data, 'delete', $learning_id, null, $ulangan_id);
    }

    public function v_add_soal($learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $data = [
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $this->fetchData('/api/v1/cms/teacher/category-question')->data ?? [],
            'learning_id' => $learning_id,
            'ulangan_id' => $ulangan_id,
        ];

        return view('teacher.pengajar.pembelajaran.ulangan.soal.add', $data);
    }

    public function add_soal(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $validate = $this->validateSoal($request);
        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $form_data = $this->prepareSoalData($request, $learning_id, $ulangan_id);

        $response_data = $this->postData('/api/v1/cms/teacher/question', $form_data, 'json');

        $this->handleFileManagement($request, $response_data->data->id);

        return $this->handleUlanganResponse($response_data, 'add', $learning_id, true, $ulangan_id);
    }

    public function v_edit_soal($learning_id, $ulangan_id, $soal_id)
    {
        $this->authorizeTeacher();

        $data = [
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $this->fetchData('/api/v1/cms/teacher/category-question')->data ?? [],
            'learning_id' => $learning_id,
            'ulangan_id' => $ulangan_id,
            'question' => $this->fetchData('/api/v1/cms/teacher/question/' . $soal_id)->data ?? [],
            'soal_id' => $soal_id,
        ];

        return view('teacher.pengajar.pembelajaran.ulangan.soal.edit', $data);
    }

    public function edit_soal(Request $request, $learning_id, $ulangan_id, $soal_id)
    {
        $this->authorizeTeacher();

        $validate = $this->validateSoal($request);
        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $form_data = $this->prepareSoalData($request, $learning_id, $ulangan_id);

        $response_data = $this->putData('/api/v1/cms/teacher/question/' . $soal_id, $form_data, 'json');

        $this->handleFileManagement($request, $response_data->data->id);

        return $this->handleUlanganResponse($response_data, 'edit', $learning_id, true, $ulangan_id);
    }

    public function import_soal(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

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
                'learning_id' => $learning_id,
                'exam_id' => $ulangan_id,
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
            return $this->handleUlanganResponse($response_data, 'import', $learning_id, true, $ulangan_id);
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


    public function delete_soal($learning_id, $ulangan_id, $soal_id)
    {
        $this->authorizeTeacher();

        $response_data = $this->deleteData('/api/v1/cms/teacher/question/' . $soal_id);

        return $this->handleUlanganResponse($response_data, 'delete', $learning_id, true, $ulangan_id);
    }

    public function multi_delete_soal(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

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

        return $this->handleUlanganResponse($response_data, 'delete', $learning_id, true, $ulangan_id);
    }

    public function download_soal($learning_id, $ulangan_id, $soal_id)
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


    private function validateUlangan(Request $request)
    {

        $isRandomQuestion = $request->input('is_random_question') === 'on';
        $isRandomAnswer = $request->input('is_random_answer') === 'on';
        $isShowScore = $request->input('is_show_score') === 'on';
        $isShowResult = $request->input('is_show_result') === 'on';
        $isActive = $request->input('is_active') === 'on';

        $request->merge([
            'is_random_question' => $isRandomQuestion,
            'is_random_answer' => $isRandomAnswer,
            'is_show_score' => $isShowScore,
            'is_show_result' => $isShowResult,
            'is_active' => $isActive,
        ]);


        return Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'instruction' => 'nullable|string',
            'status' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'token' => 'nullable|string|max:255',
            'duration' => 'required|date_format:H:i',
            'repeat_chance' => 'required|integer',
            'device' => 'nullable|in:Web,Mobile,All',
            'maximum_user' => 'required|integer',
            'is_random_question' => 'boolean',
            'is_random_answer' => 'boolean',
            'is_show_score' => 'boolean',
            'is_show_result' => 'boolean',
        ]);
    }

    private function prepareUlanganData(Request $request, $learning_id)
    {
        $currentTime = Carbon::now('Asia/Jakarta');
        $endTime = Carbon::parse($request->end_time);
        $status = null;
        if ($currentTime->lessThan($endTime)) {
            if ($currentTime->isSameDay($endTime)) {
                $status = 'Sedang Berlangsung';
            } elseif ($currentTime->greaterThan($endTime->subDay())) {
                $status = 'Mendatang';
            }
        } else {
            $status = 'Sudah Lewat';
        }

        return [
            'learning_id' => $learning_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'instruction' => $request->instruction,
            'status' => $status,
            'start_time' => Carbon::parse($request->start_time)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('Y-m-d H:i:s'),
            'token' => $request->token,
            'duration' => Carbon::parse($request->duration)->format('H:i'),
            'repeat_chance' => $request->repeat_chance,
            'device' => $request->device,
            'maximum_user' => $request->maximum_user,
            'is_active' => $request->is_active,
            'is_random_question' => $request->is_random_question,
            'is_random_answer' => $request->is_random_answer,
            'is_show_score' => $request->is_show_score,
            'is_show_result' => $request->is_show_result,
        ];
    }

    private function validateSoal(Request $request)
    {
        return Validator::make($request->all(), [
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

    private function prepareSoalData(Request $request, $learning_id, $ulangan_id)
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
            'learning_id' => $learning_id,
            'exam_id' => $ulangan_id,
            'category_id' => $request->input('category_id'),
            'question_text' => $request->input('question_text'),
            'question_type' => $request->input('question_type'),
            'point' => $request->input('point'),
            'grade_method' => $request->input('grade_method'),
            'difficult' => $request->input('difficult'),
            'choices' => $choices,
        ];
    }

    private function prepareUlanganDataToSend($learning_id, $id = null)
    {
        $learning = $this->fetchData('/api/v1/mobile/teacher/learning/' . $learning_id);
        $teacherSubclasses = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');

        $filteredSubclasses = collect($teacherSubclasses->data ?? [])->filter(function ($subclass) use ($learning) {
            return $subclass->learning_id == optional($learning->data)->id;
        });

        $subclasses = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $nameSubclass = $filteredSubclasses->map(function ($filteredSubclass) use ($subclasses) {
            return collect($subclasses->data ?? [])->firstWhere('id', $filteredSubclass->sub_class_id);
        })->values()->first();

        $class_exams = $id ? $this->fetchData('/api/v1/cms/teacher/class-exam/' . $id) : $this->fetchData('/api/v1/cms/teacher/class-exam');
        $filtered_class_exams = $id ? ($class_exams->data ?? null) : collect($class_exams->data ?? [])->filter(function ($class_exam) use ($learning) {
            return $class_exam->learning_id == optional($learning->data)->id;
        });

        $categories = $this->fetchData('/api/v1/cms/teacher/category-question')->data ?? [];
        $questions = $this->fetchData('/api/v1/cms/teacher/question/class');

        return [
            'learning' => $learning->data ?? null,
            'subclasses' => $nameSubclass,
            'class_exams' => $filtered_class_exams,
            'learning_id' => $learning_id,
            'categories' => $categories,
            'questions' => $questions->data ?? [],
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $this->fetchData('/api/v1/cms/teacher/category-question')->data ?? [],
            'levels' => $this->fetchLevels(),
        ];
    }

    private function handleUlanganResponse($response_data, $type, $learning_id, $is_soal, $ulangan_id)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message, $is_soal);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $dataToSend = $this->prepareUlanganDataToSend($learning_id);

        $route = $is_soal ? 'teacher.pengajar.pembelajaran.v_ulangan_detail' : 'teacher.pengajar.pembelajaran.v_ulangan';

        return redirect()->route($route,  ['learning_id' => $learning_id, 'ulangan_id' => $ulangan_id])
            ->with('message', $message)->with('alertClass', $alertClass)->with('dataToSend', $dataToSend);
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

    private function getResponseMessage($success, $type, $message, $is_soal)
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

    public function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/class');
        return $response_data->data ?? [];
    }
}
