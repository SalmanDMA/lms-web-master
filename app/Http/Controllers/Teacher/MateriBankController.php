<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\MaterialResource;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MateriBankController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_bank_materi(Request $request)
    {
        $this->authorizeTeacher();

        $materi = $this->fetchMateriData();
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();

        $learningTeacher = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($courseData)->whereIn('id', $learningCourseIds);

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.materi.index', [
            'materi' => $materi->data ?? [],
            'courses' => $courseData,
            'levels' => $levels,
            'filteredCourseDataForShare' => $filteredCourseDataForShare,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function v_add_materi(Request $request)
    {
        $this->authorizeTeacher();
        $levels = $this->fetchLevels();

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.materi.add', [
            'courses' => $this->fetchCourses(),
            'levels' => $levels,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function add_materi(Request $request)
    {
        $this->authorizeTeacher();

        $validator = $this->validateMateri($request);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareMateriData($request);

        $response_data = $this->postData('/api/v1/cms/teacher/material-bank', $formData, 'form');

        $this->handleResourceManagement($request, null, $response_data->data->id);

        return $this->handleMateriResponse($response_data, 'add');
    }

    public function v_edit_materi(Request $request, $id)
    {
        $this->authorizeTeacher();

        $materi = $this->fetchMateriData($id);
        $levels = $this->fetchLevels();

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.materi.edit', [
            'materi' => $materi->data ?? [],
            'materi_id' => $id,
            'courses' => $this->fetchCourses(),
            'levels' => $levels,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function edit_materi(Request $request, $id)
    {
        $this->authorizeTeacher();

        $validator = $this->validateMateri($request);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareMateriData($request);

        $response_data = $this->putData('/api/v1/cms/teacher/material-bank/' . $id, $formData, 'form');

        $this->handleResourceManagement($request, null, $response_data->data->id);

        return $this->handleMateriResponse($response_data, 'edit');
    }

    public function delete_materi($id)
    {
        $this->authorizeTeacher();

        $response_data = $this->deleteData('/api/v1/cms/teacher/material-bank/' . $id);

        return $this->handleMateriResponse($response_data, 'delete');
    }

    public function share_materi(Request $request)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|string',
            'class_level' => 'required|string',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $learningData = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningId = collect($learningData->data)
            ->where('course.id', $request->course_id)
            ->pluck('id')
            ->first();


        if (!$learningId) {
            return redirect()->back()->withInput()->withErrors(['message' => 'Course not found.']);
        }

        $materiJson = json_decode($request->input('materi_json'), true);

        $apiData = [
            'learning_id' => $learningId,
            'material_title' => $materiJson['material_title'],
            'material_description' => $materiJson['material_description'],
            'class_level' => $request->input('class_level'),
            'shared_at' => now()->toDateTimeString(),
            'publication_status' => 'published',
            'status' => $request->input('status'),
            'max_file' => $materiJson['max_attach'],
            'resources' => array_map(function ($resource) {
                return [
                    'resource_name' => $resource['resource_name'],
                    'resource_type' => $resource['resource_type'],
                    'resource_url' => $resource['resource_url'],
                    'resource_size' => $resource['resource_size'],
                    'resource_extension' => $resource['resource_extension'],
                ];
            }, $materiJson['material_resources'] ?? []),
        ];

        $response_data = $this->postData('/api/v1/mobile/teacher/material', $apiData, 'form');

        return $this->handleMateriResponse($response_data, 'share');
    }

    public function download_materi($id)
    {
        $resource = MaterialResource::find($id);

        if (!$resource) {
            return redirect()->back()->withInput()->withErrors(['message' => 'Resource not found.']);
        }

        $filePath = str_replace('storage/public/', 'public/', $resource->resource_url);

        $absolutePath = storage_path('app/' . $filePath);

        if (!file_exists($absolutePath)) {
            return redirect()->back()->withInput()->withErrors(['message' => 'File not found.']);
        }

        $fileName = basename($filePath);

        return response()->download($absolutePath, $fileName);
    }

    private function validateMateri(Request $request)
    {
        return Validator::make($request->all(), [
            'course_title' => 'required|string',
            'material_title' => 'required|string',
            'material_description' => 'nullable|string',
            'class_level' => 'required|string',
            'shared_at' => 'required|date',
            'max_attach' => 'required|integer|min:1|max:10'
        ]);
    }

    private function prepareMateriData(Request $request)
    {
        return [
            'course_title' => $request->input('course_title'),
            'material_title' => $request->input('material_title'),
            'material_description' => $request->input('material_description'),
            'class_level' => $request->input('class_level'),
            'shared_at' => $request->input('shared_at'),
            'max_attach' => $request->input('max_attach'),
        ];
    }

    private function handleResourceManagement(Request $request, $materiId, $materiBankId)
    {

        $deletedResources = json_decode($request->input('deleted_resources'), true);

        if (!empty($deletedResources)) {
            foreach ($deletedResources as $resourceId) {
                $resource = MaterialResource::find($resourceId);
                if ($resource) {

                    $filePath = str_replace('storage/public/', 'public/', $resource->resource_url);

                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }

                    $resource->delete();
                }
            }
        }

        if ($request->has('resources')) {
            foreach ($request->resources as $resource) {
                if ($resource['resource_type'] && $resource['resource_name']) {
                    $resourceData = $this->mapResourceData($resource);
                    $uploadResult = $this->processResource($resourceData, 'materials');

                    if ($uploadResult['error']) {
                        return $this->sendError($uploadResult['message'], null, 200);
                    }

                    MaterialResource::create([
                        'id' => IdGenerator::generate(['table' => 'material_resources', 'length' => 16, 'prefix' => 'MATR-']),
                        'material_id' => $materiId,
                        'material_bank_id' => $materiBankId,
                        'resource_name' => $resourceData['resource_name'],
                        'resource_type' => $resourceData['resource_type'],
                        'resource_url' => $uploadResult['path'],
                        'resource_extension' => $uploadResult['extension'],
                        'resource_size' => $uploadResult['size'],
                    ]);
                }
            }
        }
    }

    private function fetchMateriData($id = null)
    {
        $url = '/api/v1/cms/teacher/material-bank';
        if ($id) {
            $url .= '/' . $id;
        }
        return $this->fetchData($url);
    }

    private function handleMateriResponse($response_data, $type)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';
        $levels = $this->fetchLevels();
        $courseData = $this->fetchCourses();

        $learningTeacher = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($courseData)->whereIn('id', $learningCourseIds);

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        if ($response_data->success) {
            return view('teacher.bank.materi.index', [
                'message' => $message,
                'alertClass' => $alertClass,
                'materi' => $this->fetchMateriData()->data,
                'courses' => $courseData,
                'levels' => $levels,
                'filteredCourseDataForShare' => $filteredCourseDataForShare,
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

        return $success ? "Berhasil $operation materi." : "Gagal $operation materi. $apiMessage";
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
