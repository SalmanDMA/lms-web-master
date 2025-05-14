<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\QuestionAttachmentBank;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StaffSoalBankController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_bank_soal()
    {
        $this->authorizeStaff();

        $data = $this->fetchCommonData();
        $data['question_bank_data'] = $this->fetchData('/api/v1/cms/staff-curriculum/question-bank')->data ?? [];
        $data['question_bank_data'] = collect($data['question_bank_data'])->map(function ($item) use ($data) {
            $item->category = collect($data['question_category'])->where('id', $item->category_id)->first();
            return $item;
        });
        $data['question_bank_data'] = collect($data['question_bank_data'])->map(function ($item) use ($data) {
            $item->course = collect($data['courses'])->where('id', $item->course)->first();
            return $item;
        });

        return view('staff-curriculum.bank.soal.index', $data);
    }

    public function v_add_soal()
    {
        $this->authorizeStaff();

        $data = $this->fetchCommonData();

        return view('staff-curriculum.bank.soal.add', $data);
    }

    public function add_soal(Request $request)
    {
        $this->authorizeStaff();

        $validate = $this->validateSoal($request);
        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $form_data = $this->prepareSoalData($request);
        $form_data['teacher_id'] = 'TEA-000000000001';

        $response_data = $this->postData('/api/v1/cms/staff-curriculum/question-bank', $form_data, 'json');
        $this->handleFileManagement($request, $response_data->data->id);

        return $this->handleResponse($response_data, 'add');
    }

    public function v_edit_soal($id)
    {
        $this->authorizeStaff();

        $data = $this->fetchCommonData();
        $learningTeacher = $this->fetchData('/api/v1/cms/staff-curriculum/learning');
        $learningCourseIds = collect($learningTeacher->data)->pluck('course.id');
        $data['courses'] = collect($data['courses'])->whereIn('id', $learningCourseIds);

        $data['question_bank'] = $this->fetchData('/api/v1/cms/staff-curriculum/question-bank/' . $id)->data ?? [];

        return view('staff-curriculum.bank.soal.edit', $data);
    }

    public function edit_soal(Request $request, $id)
    {
        $this->authorizeStaff();

        $validate = $this->validateSoal($request);
        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $form_data = $this->prepareSoalData($request);
        $form_data['teacher_id'] = 'TEA-000000000003/20';

        $response_data = $this->putData('/api/v1/cms/staff-curriculum/question-bank/' . $id, $form_data, 'json');
        $this->handleFileManagement($request, $response_data->data->id);

        return $this->handleResponse($response_data, 'edit');
    }

    public function delete_soal($id)
    {
        $this->authorizeStaff();

        $response_data = $this->deleteData('/api/v1/cms/staff-curriculum/question-bank/' . $id);

        return $this->handleResponse($response_data, 'delete');
    }

    public function multi_delete_soal(Request $request)
    {
        $this->authorizeStaff();

        $deleteIds = json_decode($request->input('deleteIds'), true);
        $validate = Validator::make(['deleteIds' => $deleteIds], [
            'deleteIds' => 'required|array|min:1',
            'deleteIds.*' => 'required',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $response_data = new \stdClass();
        $response_data->success = true;
        $response_data->message = 'Semua soal berhasil dihapus';

        foreach ($deleteIds as $id) {
            $result = $this->deleteData('/api/v1/cms/staff-curriculum/question-bank/' . $id);

            if (!$result->success) {
                $response_data->success = false;
                $response_data->message = 'Terjadi kesalahan saat menghapus beberapa soal';
                break;
            }
        }

        return $this->handleResponse($response_data, 'delete');
    }

    public function share_soal(Request $request)
    {
        $this->authorizeStaff();

        $selectedItems = json_decode($request->input('selectedItems'), true);
        $selectedIds = array_map(function ($item) {
            return $item['id'];
        }, $selectedItems);

        $validate = Validator::make(['selectedItems' => $selectedIds], [
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*' => 'required|exists:bank_questions,id',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }

        $response_data = new \stdClass();
        $response_data->success = true;
        $response_data->message = 'Soal berhasil dikirim';

        foreach ($selectedIds as $item) {
            $result = $this->putData('/api/v1/cms/staff-curriculum/question-bank/' . $item . '/update-status', [], 'json');

            if (!$result->success) {
                $response_data->success = false;
                $response_data->message = 'Terjadi kesalahan saat mengirim beberapa soal';
                break;
            }
        }

        return $this->handleResponse($response_data, 'share');
    }

    public function download_soal($id)
    {
        $resource = QuestionAttachmentBank::find($id);

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
            'category_id' => 'required|exists:question_categories,id',
            'question_text' => 'required|string|max:255',
            'question_type' => 'required|in:Essay,Pilihan Ganda,Pilihan Ganda Complex,True False',
            'point' => 'required|integer',
            'grade_method' => 'nullable|string|max:255',
            'choices' => 'nullable|array',
            'choices.*.choice_text' => 'nullable|string|max:255',
            'choices.*.is_true' => 'nullable|boolean',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable',
            'resources.*.file_type' => 'nullable|string',
        ]);
    }

    private function prepareSoalData(Request $request)
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
            'category_id' => $request->input('category_id'),
            'question_text' => $request->input('question_text'),
            'question_type' => $request->input('question_type'),
            'point' => $request->input('point'),
            'grade_method' => $request->input('grade_method'),
            'course' => $request->input('course'),
            'class_level' => $request->input('class_level'),
            'choices' => $choices,
            'shared_at' => $request->input('shared_at') ?? 'Belum pernah dikirim',
            'shared_count' => $request->input('shared_count') ?? 0,
        ];
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
                $resource = QuestionAttachmentBank::find($resourceId);
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

                    QuestionAttachmentBank::create([
                        'id' => IdGenerator::generate(['table' => 'question_attachment_banks', 'length' => 16, 'prefix' => 'QAB-']),
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

    private function handleResponse($response_data, $type)
    {
        $data = $this->fetchCommonData();
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        return redirect()->route('staff_curriculum.bank.v_bank.soal')->with('message', $message)
            ->with('alertClass', $alertClass)
            ->with($data);
    }

    private function fetchCommonData()
    {
        return [
            'courses' => $this->fetchCourses(),
            'levels' => $this->fetchLevels(),
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $this->fetchData('/api/v1/cms/staff-curriculum/category-question')->data ?? [],
        ];
    }

    private function getResponseMessage($success, $type, $apiMessage)
    {
        $operation = match ($type) {
            'add' => 'menambahkan',
            'edit' => 'mengubah',
            'delete' => 'menghapus',
            default => '',
        };

        return $success ? "Berhasil $operation soal." : "Gagal $operation soal. $apiMessage";
    }

    public function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/course');
        return $response_data->data ?? [];
    }

    public function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/class');
        return $response_data->data ?? [];
    }
}
