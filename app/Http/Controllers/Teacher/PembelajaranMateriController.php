<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\MaterialResource;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PembelajaranMateriController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_materi(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareMateriDataToSend($learning_id);

        return view('teacher.pengajar.pembelajaran.materi.index', $dataToSend);
    }

    public function import_materi(Request $request, $learning_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = Validator::make($request->all(), [
            'bank_materi_id' => 'required|exists:material_banks,id',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $response_material_bank = $this->fetchData('/api/v1/cms/teacher/material-bank/' . $request->bank_materi_id);

        $shared_at = null;
        $publication_status = null;

        if ($request->input('status') == 'Active') {
            $shared_at = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
            $publication_status = 'Publikasikan Sekarang';
        } else {
            $shared_at = Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d H:i:s');
            $publication_status = 'Tidak Publikasikan';
        }

        $form_data = [
            'learning_id' => $learning_id,
            'material_title' => $response_material_bank->data->material_title,
            'material_description' => $response_material_bank->data->material_description,
            'class_level' => $response_material_bank->data->class_level,
            'shared_at' => $shared_at,
            'publication_status' => $publication_status,
            'status' => $request->status,
            'max_file' => $response_material_bank->data->max_attach,
            'resources' => array_map(function ($resource) {
                return [
                    'resource_name' => $resource->resource_name,
                    'resource_type' => $resource->resource_type,
                    'resource_url' => $resource->resource_url,
                    'resource_size' => $resource->resource_size,
                    'resource_extension' => $resource->resource_extension,
                ];
            }, $response_material_bank->data->material_resources ?? []),
        ];

        $response_data = $this->postData('/api/v1/mobile/teacher/material', $form_data, 'json');

        return $this->handleMateriResponse($response_data, 'import', $learning_id);
    }

    public function v_add_materi(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareMateriDataToSend($learning_id);

        return view('teacher.pengajar.pembelajaran.materi.add', $dataToSend);
    }

    public function add_materi(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $validator = $this->validateMateri($request);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareMateriData($request, $learning_id);

        $response_data = $this->postData('/api/v1/mobile/teacher/material', $formData, 'json');

        $this->handleResourceManagement($request, $response_data->data->id, null);

        return $this->handleMateriResponse($response_data, 'add', $learning_id);
    }

    public function v_edit_materi(Request $request, $learning_id, $id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareMateriDataToSend($learning_id, $id);

        return view('teacher.pengajar.pembelajaran.materi.edit', $dataToSend);
    }

    public function edit_materi(Request $request, $learning_id, $id)
    {
        $this->authorizeTeacher();

        $validator = $this->validateMateri($request);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareMateriData($request, $learning_id);

        $response_data = $this->putData('/api/v1/mobile/teacher/material/' . $id, $formData, 'json');

        $this->handleResourceManagement($request, $response_data->data->id, null);

        return $this->handleMateriResponse($response_data, 'edit', $learning_id);
    }

    public function delete_materi($learning_id, $id)
    {
        $this->authorizeTeacher();

        $response_data = $this->deleteData('/api/v1/mobile/teacher/material/' . $id);

        return $this->handleMateriResponse($response_data, 'delete', $learning_id);
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
            'material_title' => 'required|string|max:255',
            'material_description' => 'required|string',
            'class_level' => 'required|string|max:255',
            'publication_status' => 'required|string',
            'max_file' => 'required|integer|min:1|max:10',
        ]);
    }

    private function prepareMateriData(Request $request, $learning_id)
    {
        $shared_at = null;
        $status = null;

        if ($request->input('shared_at') && $request->input('publication_status') == 'Jadwalkan') {
            $shared_at = Carbon::parse($request->input('shared_at'))->format('Y-m-d H:i:s');
            $status = 'Active';
        } else if ($request->input('publication_status') == 'Publikasikan Sekarang') {
            $shared_at = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
            $status = 'Active';
        } else {
            $shared_at = Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d H:i:s');
            $status = 'Inactive';
        }

        return [
            'learning_id' => $learning_id,
            'material_title' => $request->input('material_title'),
            'material_description' => $request->input('material_description'),
            'class_level' => $request->input('class_level'),
            'shared_at' => $shared_at,
            'publication_status' => $request->input('publication_status'),
            'status' => $status,
            'max_file' => $request->input('max_file'),
        ];
    }

    private function prepareMateriDataToSend($learning_id, $id = null)
    {
        $learning = $this->fetchData('/api/v1/mobile/teacher/learning/' . $learning_id);
        $teacherSubclasses = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');
        $filteredSubclasses = collect($teacherSubclasses->data ?? [])->filter(function ($subclass) use ($learning) {
            return $subclass->learning_id == $learning->data->id;
        });

        $subclasses = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $nameSubclass = $filteredSubclasses->map(function ($filteredSubclass) use ($subclasses) {
            return collect($subclasses->data ?? [])->firstWhere('id', $filteredSubclass->sub_class_id);
        })->values()->first();

        $materials = $id ? $this->fetchData('/api/v1/mobile/teacher/material/' . $id) : $this->fetchData('/api/v1/mobile/teacher/material');
        $filteredMaterial = $id ? $materials->data : collect($materials->data ?? [])->filter(function ($material) use ($learning_id) {
            return $material->learning_id == $learning_id;
        });

        $material_banks = $this->fetchData('/api/v1/cms/teacher/material-bank');
        $filteredBankMaterial = collect($material_banks->data ?? [])->filter(function ($material) use ($learning, $nameSubclass) {
            return $material->course_title == $learning->data->course->id && $material->class_level == $nameSubclass->class_id;
        });

        return [
            'learning' => $learning->data,
            'subclasses' => $nameSubclass,
            'materials' => $filteredMaterial,
            'learning_id' => $learning_id,
            'material_banks' => $filteredBankMaterial ?? [],
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

    private function handleMateriResponse($response_data, $type, $learning_id)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $dataToSend = $this->prepareMateriDataToSend($learning_id);

        return redirect()->route('teacher.pengajar.pembelajaran.v_materi', ['learning_id' => $learning_id])
            ->with('message', $message)->with('alertClass', $alertClass)->with('dataToSend', $dataToSend);
    }

    private function getResponseMessage($success, $type, $message)
    {
        if ($success) {
            switch ($type) {
                case 'add':
                    return 'Materi berhasil ditambahkan.';
                case 'edit':
                    return 'Materi berhasil diperbarui.';
                case 'delete':
                    return 'Materi berhasil dihapus.';
                case 'import':
                    return 'Materi berhasil diimport.';
                default:
                    return $message;
            }
        }

        return 'Terjadi kesalahan: ' . $message;
    }
}
