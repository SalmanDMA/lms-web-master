<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Course;
use App\Models\MaterialBank;
use App\Models\MaterialResource;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ManagementMaterialBankController extends Controller
{

    use CommonTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');

        $query = MaterialBank::query();

        if ($userLogin->role === 'TEACHER') {
            $query->where('created_by', $userLogin->is_teacher->id);
        }

        if ($search) {
            $query->where('material_title', 'like', '%' . $search . '%');
        }

        $materials = $query->get();

        if ($materials->isEmpty()) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }

        $materials->load(['material_resources', 'course', 'created_by']);

        return $this->sendResponse($materials, 'Berhasil mengambil semua data material');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'course_title' => 'required|exists:courses,id',
            'material_title' => 'required|string|max:255',
            'material_description' => 'required|string',
            'class_level' => 'required|string|max:255',
            'shared_at' => 'required|date',
            'max_attach' => 'required|integer|max:10',
            'resources.*.resource_name' => 'nullable',
            'resources.*.resource_type' => 'nullable',
            'resources.*.resource_url' => 'nullable',
        ], [
            'course_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_at.required' => 'Ups, Anda Belum Melengkapi Form',
            'max_attach.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->all();

        if (isset($data['resources']) && count($data['resources']) > $data['max_attach']) {
            return $this->sendError('Jumlah lampiran melebihi batas maksimal.', null, 200);
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['resource_url'])) {
                    $validExtensions = [];

                    switch ($resource['resource_type']) {
                        case 'image':
                            $validExtensions = ['png', 'jpeg', 'jpg'];
                            break;
                        case 'document':
                            $validExtensions = ['doc', 'docx', 'pdf'];
                            break;
                        case 'archive':
                            $validExtensions = ['rar', 'zip'];
                            break;
                        case 'audio':
                            $validExtensions = ['mp3', 'wav', 'mpeg'];
                            break;
                        case 'video':
                            $validExtensions = ['mp4', 'mkv', 'mpeg'];
                            break;
                        default:
                            $validExtensions = [];
                            break;
                    }

                    if (!empty($validExtensions) && $resource['resource_type'] !== 'url' && $resource['resource_type'] !== 'youtube') {
                        $extension = strtolower($resource['resource_url']->getClientOriginalExtension());
                        if (!in_array($extension, $validExtensions)) {
                            return $this->sendError('Jenis file tidak valid.', null, 200);
                        }
                    }
                }
            }
        }

        // for teacher or sekolah role
        if ($userLogin->school_id != null) {
            $courseData = Course::where('created_by', $userLogin->school_id);
        } else {
            // for admin
            $courseData = Course::where('created_by', $userLogin->id);
        }

        $courseData = $courseData->find($data['course_title']);

        if (!$courseData) {
            return $this->sendError('Course tidak ditemukan.', null, 200);
        }

        if ($userLogin->role === 'TEACHER') {
            $data['created_by'] = $userLogin->is_teacher->id;
        } else {
            $validator = Validator::make($request->all(), [
                'created_by' => 'required|exists:teachers,id',
            ], [
                'created_by.required' => 'Ups, Anda Belum Melengkapi Form',
            ]);

            if ($validator->fails()) {
                return $this->failsValidate($validator->errors());
            }

            $data['created_by'] = $data['created_by'];
        }

        $generateIdMaterial = IdGenerator::generate(['table' => 'material_banks', 'length' => 16, 'prefix' => 'MATB-']);

        $material = MaterialBank::create([
            'id' => $generateIdMaterial,
            'course_title' => $data['course_title'],
            'material_title' => $data['material_title'],
            'material_description' => $data['material_description'],
            'class_level' => $data['class_level'],
            'shared_at' => $data['shared_at'],
            'created_by' => $data['created_by'],
            'max_attach' => $data['max_attach'],
        ]);

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['resource_url'])) {
                    $uploadResult = null;

                    if ($resource['resource_type'] === 'url' || $resource['resource_type'] === 'youtube') {
                        $uploadResult['path'] = $resource['resource_url'];
                        $uploadResult['size'] = null;
                        $uploadResult['extension'] = null;
                    } else {
                        $folder = match ($resource['resource_type']) {
                            'image' => 'materials/images',
                            'document' => 'materials/documents',
                            'archive' => 'materials/archives',
                            'audio' => 'materials/audio',
                            'video' => 'materials/videos',
                            default => 'materials/others',
                        };
                        $uploadResult = $this->uploadFile($resource['resource_url'], $folder);
                    }

                    $generaterIdMaterialResource = IdGenerator::generate(['table' => 'material_resources', 'length' => 16, 'prefix' => 'MATR-']);
                    MaterialResource::create([
                        'id' => $generaterIdMaterialResource,
                        'material_id' => null,
                        'material_bank_id' => $material->id,
                        'resource_name' => $resource['resource_name'],
                        'resource_type' => $resource['resource_type'],
                        'resource_url' => $uploadResult['path'],
                        'resource_extension' => $uploadResult['extension'],
                        'resource_size' => $uploadResult['size'],
                    ]);
                }
            }
        }

        $material->load(['material_resources', 'course', 'created_by']);
        return $this->sendResponse($material, 'Berhasil menambahkan data material');
    }

    public function show($id)
    {

        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $material = MaterialBank::where('created_by', $userLogin->is_teacher->id)->find($id);
        } else {
            $material = MaterialBank::find($id);
        }

        if (!$material) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }

        $material->load(['material_resources', 'course', 'created_by']);
        return $this->sendResponse($material, 'Berhasil menemukan data material');
    }

    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();


        if ($userLogin->role === 'TEACHER') {
            $material = MaterialBank::where('created_by', $userLogin->is_teacher->id)->find($id);
        } else {
            $material = MaterialBank::find($id);
        }

        if (!$material) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'course_title' => 'required|exists:courses,id',
            'material_title' => 'required|string|max:255',
            'material_description' => 'required|string',
            'class_level' => 'required|string|max:255',
            'shared_at' => 'required|date',
            'max_attach' => 'required|integer|max:10',
            'resources.*.resource_name' => 'nullable',
            'resources.*.resource_type' => 'nullable',
            'resources.*.resource_url' => 'nullable',
        ], [
            'course_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_at.required' => 'Ups, Anda Belum Melengkapi Form',
            'max_attach.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->all();

        if (isset($data['resources']) && count($data['resources']) > $data['max_attach']) {
            return $this->sendError('Jumlah lampiran melebihi batas maksimal.', null, 200);
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['resource_url'])) {
                    $validExtensions = [];

                    switch ($resource['resource_type']) {
                        case 'image':
                            $validExtensions = ['png', 'jpeg', 'jpg'];
                            break;
                        case 'document':
                            $validExtensions = ['doc', 'docx', 'pdf'];
                            break;
                        case 'archive':
                            $validExtensions = ['rar', 'zip'];
                            break;
                        case 'audio':
                            $validExtensions = ['mp3', 'wav', 'mpeg'];
                            break;
                        case 'video':
                            $validExtensions = ['mp4', 'mkv', 'mpeg'];
                            break;
                        default:
                            $validExtensions = [];
                            break;
                    }

                    if (!empty($validExtensions) && $resource['resource_type'] !== 'url' && $resource['resource_type'] !== 'youtube') {
                        $extension = strtolower($resource['resource_url']->getClientOriginalExtension());
                        if (!in_array($extension, $validExtensions)) {
                            return $this->sendError('Jenis file tidak valid.', null, 200);
                        }
                    }
                }
            }
        }

        // for teacher or sekolah role
        if ($userLogin->school_id != null) {
            $courseData = Course::where('created_by', $userLogin->school_id);
        }
        //  for admin
        else {
            $courseData = Course::where('created_by', $userLogin->id);
        }

        $courseData = $courseData->find($data['course_title']);

        if (!$courseData) {
            return $this->sendError('Course tidak ditemukan.', null, 200);
        }

        if ($userLogin->role === 'TEACHER') {
            $data['created_by'] = $userLogin->is_teacher->id;
        } else {
            $validator = Validator::make($request->all(), [
                'created_by' => 'required|exists:teachers,id',
            ], [
                'created_by.required' => 'Ups, Anda Belum Melengkapi Form',
            ]);

            if ($validator->fails()) {
                return $this->failsValidate($validator->errors());
            }

            $data['created_by'] = $data['created_by'];
        }

        $material->update([
            'course_title' => $data['course_title'] ?? $material->course_title,
            'material_title' => $data['material_title'] ?? $material->material_title,
            'material_description' => $data['material_description'] ?? $material->material_description,
            'class_level' => $data['class_level'] ?? $material->class_level,
            'shared_at' => $data['shared_at'] ?? $material->shared_at,
            'max_attach' => $data['max_attach'] ?? $material->max_attach,
            'created_by' => $data['created_by'] ?? $material->created_by,
        ]);



        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['resource_url'])) {
                    $existingResources = $material->material_resources()->where('resource_type', $resource['resource_type'])->get();

                    foreach ($existingResources as $existingResource) {
                        $this->removeFile($existingResource->resource_url);
                        $existingResource->delete();
                    }

                    foreach ($data['resources'] as $resource) {
                        if (isset($resource['resource_url'])) {
                            $uploadResult = null;

                            if ($resource['resource_type'] === 'url' || $resource['resource_type'] === 'youtube') {
                                $uploadResult['path'] = $resource['resource_url'];
                                $uploadResult['size'] = null;
                                $uploadResult['extension'] = null;
                            } else {
                                $folder = match ($resource['resource_type']) {
                                    'image' => 'materials/images',
                                    'document' => 'materials/documents',
                                    'archive' => 'materials/archives',
                                    'audio' => 'materials/audio',
                                    'video' => 'materials/videos',
                                    default => 'materials/others',
                                };
                                $uploadResult = $this->uploadFile($resource['resource_url'], $folder);
                            }

                            $generaterIdMaterialResource = IdGenerator::generate(['table' => 'material_resources', 'length' => 16, 'prefix' => 'MATR-']);
                            MaterialResource::create([
                                'id' => $generaterIdMaterialResource,
                                'material_id' => null,
                                'material_bank_id' => $material->id,
                                'resource_name' => $resource['resource_name'] ?? null,
                                'resource_type' => $resource['resource_type'],
                                'resource_url' => $uploadResult['path'],
                                'resource_extension' => $uploadResult['extension'],
                                'resource_size' => $uploadResult['size'],
                            ]);
                        }
                    }
                }
            }
        }

        $material->load(['material_resources', 'course', 'created_by']);
        return $this->sendResponse($material, 'Berhasil mengubah data material');
    }


    public function destroy($id)
    {

        $userLogin = auth()->user();


        if ($userLogin->role === 'TEACHER') {
            $material = MaterialBank::where('created_by', $userLogin->is_teacher->id)->find($id);
        } else {
            $material = MaterialBank::find($id);
        }


        if (!$material) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }

        foreach ($material->material_resources as $resource) {
            $this->removeFile($resource->resource_url);
            $resource->delete();
        }

        $material->delete();

        return $this->sendResponse($material, 'Berhasil menghapus data material');
    }
}
