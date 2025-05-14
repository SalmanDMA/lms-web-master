<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\AssignmentAttachment;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TugasBankController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_bank_tugas()
    {
        $this->authorizeTeacher();

        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $tugas = $this->fetchData('/api/v1/cms/teacher/assignment-bank');

        // dd($tugas);

        $learningTeacher = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($course_data ?? [])->whereIn('id', $learningCourseIds);

        if ($tugas && isset($tugas->data)) {
            foreach ($tugas->data as &$item) {
                $item->courses_name = $this->transformCourse($course_data, $item->courses_name);
                $item->class_level = $this->transformLevel($levels, $item->class_level);
            }
        }

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.tugas.index', [
            'courses' => $course_data,
            'filteredCourseDataForShare' => $filteredCourseDataForShare,
            'levels' => $levels,
            'tugas' => $tugas->data ?? [],
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications
        ]);
    }

    public function v_add_tugas()
    {
        $this->authorizeTeacher();

        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.tugas.add', [
            'courses' => $course_data,
            'levels' => $levels,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications
        ]);
    }

    public function add_tugas(Request $request)
    {
        $this->authorizeTeacher();

        $validator = $this->validateTugas($request);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareTugasData($request);

        $response_data = $this->postData('/api/v1/cms/teacher/assignment-bank', $formData, 'json');

        $this->handleFileManagement($request, null, $response_data->data->id);

        return $this->handleTugasResponse($response_data, 'add');
    }

    public function v_edit_tugas(Request $request, $id)
    {
        $this->authorizeTeacher();

        $tugas = $this->fetchData('/api/v1/cms/teacher/assignment-bank/' . $id);
        $levels = $this->fetchLevels();

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.tugas.edit', [
            'assignment' => $tugas->data ?? [],
            'assignment_id' => $id,
            'courses' => $this->fetchCourses(),
            'levels' => $levels,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications
        ]);
    }

    public function edit_tugas(Request $request, $id)
    {
        $this->authorizeTeacher();

        $validator = $this->validateTugas($request);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareTugasData($request);

        $response_data = $this->putData('/api/v1/cms/teacher/assignment-bank/' . $id, $formData, 'json');

        $this->handleFileManagement($request, null, $response_data->data->id);

        return $this->handleTugasResponse($response_data, 'edit');
    }

    public function delete_tugas($id)
    {
        $this->authorizeTeacher();

        $response_data = $this->deleteData('/api/v1/cms/teacher/assignment-bank/' . $id);

        return $this->handleTugasResponse($response_data, 'delete');
    }

    public function share_tugas(Request $request)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|string',
            'class_level' => 'required|string',
            'due_date' => 'required|date',
            'end_time' => 'required|date_format:H:i',
            'collection_type' => 'required|array|min:1',
            'collection_type.*' => 'required|string|in:Catatan,Lampiran',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $collection_type = null;
        if (count($request->collection_type) === 2) {
            $collection_type = 'All';
        } elseif (count($request->collection_type) === 1) {
            $collection_type = $request->collection_type[0];
        };

        $learningData = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningId = collect($learningData->data)
            ->where('course.id', $request->course_id)
            ->pluck('id')
            ->first();

        if (!$learningId) {
            return redirect()->back()->withInput()->withErrors(['message' => 'Course not found.']);
        }

        $tugas_json = json_decode($request->input('tugas_json'), true);

        $apiData = [
            'learning_id' => $learningId,
            'assignment_title' => $tugas_json['assignment_title'],
            'assignment_description' => $tugas_json['assignment_description'],
            'instruction' => $tugas_json['instruction'],
            'due_date' => $request->input('due_date'),
            'end_time' => $request->input('end_time'),
            'collection_type' => $collection_type,
            'limit_submit' => $tugas_json['limit_submit'],
            'class_level' => $request->input('class_level'),
            'is_visibleGrade' => $tugas_json['is_visibleGrade'],
            'publication_status' => 'published',
            'max_attach' => $tugas_json['max_attach'],
            'resources' => array_map(function ($resource) {
                return [
                    'file_name' => $resource['file_name'],
                    'file_type' => $resource['file_type'],
                    'file_url' => $resource['file_url'],
                    'file_size' => $resource['file_size'],
                    'file_extension' => $resource['file_extension'],
                ];
            }, $tugas_json['assignment_attachments'] ?? []),
        ];

        $response_data = $this->postData('/api/v1/mobile/teacher/assignment', $apiData, 'form');

        return $this->handleTugasResponse($response_data, 'share');
    }


    public function download_tugas($id)
    {
        $resource = AssignmentAttachment::find($id);

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

    private function validateTugas(Request $request)
    {
        return Validator::make($request->all(), [
            'courses_name' => 'required|exists:courses,id',
            'assignment_title' => 'required|string|max:255',
            'assignment_description' => 'required|string',
            'instruction' => 'required|string',
            'class_level' => 'required|string|max:255',
            'due_date' => 'required|date',
            'limit_submit' => 'required|integer',
            'is_visibleGrade' => 'boolean',
            'max_attach' => 'required|integer|max:10',
        ]);
    }


    private function prepareTugasData(Request $request)
    {
        return [
            'courses_name' => $request->input('courses_name'),
            'assignment_title' => $request->input('assignment_title'),
            'assignment_description' => $request->input('assignment_description'),
            'instruction' => $request->input('instruction'),
            'class_level' => $request->input('class_level'),
            'due_date' => $request->input('due_date'),
            'limit_submit' => $request->input('limit_submit'),
            'is_visibleGrade' => $request->input('is_visibleGrade'),
            'max_attach' => $request->input('max_attach'),
        ];
    }

    private function handleFileManagement(Request $request, $tugasId, $tugasBankId)
    {

        $deletedResources = json_decode($request->input('deleted_resources'), true);

        if (!empty($deletedResources)) {
            foreach ($deletedResources as $resourceId) {
                $resource = AssignmentAttachment::find($resourceId);
                if ($resource) {

                    $filePath = str_replace('storage/public/', 'public/', $resource->file_url);

                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }

                    $resource->delete();
                }
            }
        }

        if ($request->has('resources')) {
            foreach ($request->resources as $resource) {
                if ($resource['file_type'] && $resource['file_name']) {
                    $resourceData = $this->mapResourceData($resource);
                    $uploadResult = $this->processResource($resourceData, 'assignments');

                    if ($uploadResult['error']) {
                        return $this->sendError($uploadResult['message'], null, 200);
                    }

                    AssignmentAttachment::create([
                        'id' => IdGenerator::generate(['table' => 'assignment_attachments', 'length' => 16, 'prefix' => 'ASGA-']),
                        'assignment_id' => $tugasId,
                        'assignment_bank_id' => $tugasBankId,
                        'file_name' => $resourceData['resource_name'],
                        'file_type' => $resourceData['resource_type'],
                        'file_url' => $uploadResult['path'],
                        'file_extension' => $uploadResult['extension'],
                        'file_size' => $uploadResult['size'],
                    ]);
                }
            }
        }
    }

    private function handleTugasResponse($response_data, $type)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $tugas = $this->fetchData('/api/v1/cms/teacher/assignment-bank');

        $learningTeacher = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($course_data ?? [])->whereIn('id', $learningCourseIds);

        if ($tugas && isset($tugas->data)) {
            foreach ($tugas->data as &$item) {
                $item->courses_name = $this->transformCourse($course_data, $item->courses_name);
                $item->class_level = $this->transformLevel($levels, $item->class_level);
            }
        }

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        if ($response_data->success) {
            return view('teacher.bank.tugas.index', [
                'courses' => $course_data,
                'filteredCourseDataForShare' => $filteredCourseDataForShare,
                'levels' => $levels,
                'tugas' => $tugas->data ?? [],
                'message' => $message,
                'alertClass' => $alertClass,
                'customTheme' => $customTheme,
                'unreadNotifications' => $unreadNotifications
            ]);
        } else {
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        }
    }

    private function getResponseMessage($success, $type, $apiMessage)
    {
        $operation = match ($type) {
            'add' => 'menambahkan',
            'edit' => 'mengubah',
            'delete' => 'menghapus',
            'share' => 'membagikan',
            default => '',
        };

        return $success ? "Berhasil $operation tugas." : "Gagal $operation tugas. $apiMessage";
    }

    public function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/course');
        return $response_data->data ?? [];
    }

    public function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/class');
        return $response_data->data ?? [];
    }

    public function generateCustomThemes()
    {
        $customThemeData = $this->client->get('/api/v1/mobile/cms');
        $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

        if (isset($customTheme)) {
            Config::set('app.name', $customTheme->title);
        }

        return $customTheme;
    }

    public function generateNotifications()
    {
        $responseNotification = $this->fetchData('/api/v1/mobile/teacher/notification');
        $unreadNotifications = collect($responseNotification->data ?? [])->where('is_read', 0)->count();

        return $unreadNotifications;
    }
}
