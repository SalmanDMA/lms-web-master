<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\NotifiableTrait;
use App\Models\Learning;
use App\Models\Material;
use App\Models\MaterialResource;

use App\Models\TeacherSubClasses;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    use CommonTrait, NotifiableTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');
        $limit = $request->query('limit');
    
        $query = Material::query();
    
        if ($userLogin->role === 'STUDENT') {
            // Ambil sub_class_id siswa yang login
            $student = $userLogin->is_student;
            $subClassId = $student->sub_class_id;
    
            // Ambil semua learning_id yang berkaitan dengan sub_class siswa
            $learningIds = TeacherSubClasses::where('sub_class_id', $subClassId)
                            ->pluck('learning_id');
    
            // Filter materi berdasarkan learning_id yang sesuai
            $query->whereIn('learning_id', $learningIds);
        } elseif ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id');
            $query->whereIn('learning_id', $learningIds);
        }
    
        if ($search) {
            $query->where('material_title', 'like', '%' . $search . '%');
        }
    
        $query->orderBy('created_at', 'desc');
    
        $materials = $limit ? $query->limit($limit)->get() : $query->get();
    
        if ($materials->isEmpty()) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }
    
        $materials->load(['material_resources', 'learning']);
    
        return $this->sendResponse($materials, 'Berhasil mengambil semua data material');
    }
    


    public function store(Request $request)
    {

        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'learning_id' => 'required|exists:learnings,id',
            'material_title' => 'required|string|max:255',
            'material_description' => 'required|string',
            'class_level' => 'required|string|max:255',
            'shared_at' => 'required|date',
            'publication_status' => 'required|string',
            'status' => 'required|string',
            'max_file' => 'required|integer|min:1|max:100',
            'resources.*.resource_name' => 'nullable|string',
            'resources.*.resource_type' => 'nullable|string',
            'resources.*.resource_url' => 'nullable',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'learning_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_at.required' => 'Ups, Anda Belum Melengkapi Form',
            'publication_status.required' => 'Ups, Anda Belum Melengkapi Form',
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
            'max_file.required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->all();

        if (isset($data['resources']) && count($data['resources']) > $data['max_file']) {
            return $this->sendError('Jumlah lampiran melebihi batas maksimal.', null, 200);
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['resource_url'])) {
                    if ($resource['resource_url'] instanceof UploadedFile) {
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
        }


        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id')->toArray();

            if (!in_array($data['learning_id'], $learningIds)) {
                return $this->sendError('Anda tidak mempunyai akses untuk mengakses learning ini', null, 200);
            }
        }

        $generateIdMaterial = IdGenerator::generate(['table' => 'materials', 'length' => 16, 'prefix' => 'MAT-']);

        $material = Material::create([
            'id' => $generateIdMaterial,
            'learning_id' => $data['learning_id'],
            'material_title' => $data['material_title'],
            'material_description' => $data['material_description'],
            'class_level' => $data['class_level'],
            'shared_at' => $data['shared_at'],
            'publication_status' => $data['publication_status'],
            'status' => $data['status'],
            'max_file' => $data['max_file'],
        ]);

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['resource_url'])) {
                    $uploadResult = null;

                    if ($resource['resource_url'] instanceof UploadedFile) {

                        $folder = match ($resource['resource_type']) {
                            'image' => 'materials/images',
                            'document' => 'materials/documents',
                            'archive' => 'materials/archives',
                            'audio' => 'materials/audio',
                            'video' => 'materials/videos',
                            default => 'materials/others',
                        };

                        $uploadResult = $this->uploadFile($resource['resource_url'], $folder);
                    } else {
                        $uploadResult['path'] = $resource['resource_url'];
                        $uploadResult['size'] = $resource['resource_size'] ?? null;
                        $uploadResult['extension'] = $resource['resource_extension'] ?? null;
                    }

                    $generateIdMaterialResource = IdGenerator::generate(['table' => 'material_resources', 'length' => 16, 'prefix' => 'MATR-']);
                    MaterialResource::create([
                        'id' => $generateIdMaterialResource,
                        'material_id' => $material->id,
                        'material_bank_id' => null,
                        'resource_name' => $resource['resource_name'] ?? null,
                        'resource_type' => $resource['resource_type'],
                        'resource_url' => $uploadResult['path'],
                        'resource_extension' => $uploadResult['extension'],
                        'resource_size' => $uploadResult['size'],
                    ]);
                }
            }
        }


        // Notification
        $teacherId = $userLogin->role === 'TEACHER' ? $userLogin->is_teacher->id : $data['teacher_id'];
        $learning = Learning::find($data['learning_id']);
        $courseId = $learning->course;
        $teacherSubclass = TeacherSubClasses::where('teacher_id', $teacherId)->where('course', $courseId)->first();
        $this->notifyTeacher($teacherId, 'material', $material->material_title);
        if ($teacherSubclass) {
            $this->notifyStudents($courseId, $teacherSubclass->sub_class_id, 'material', $material->material_title);
        }

        $material->load(['material_resources', 'learning']);
        return $this->sendResponse($material, 'Berhasil menambahkan data material');
    }

    public function show($id)
    {
        $userLogin = auth()->user();

        $query = Material::query();

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id');
            $query->whereIn('learning_id', $learningIds);
        }

        $material = $query->find($id);

        if (!$material) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }

        $material->load(['material_resources', 'learning']);
        return $this->sendResponse($material, 'Berhasil menemukan data material');
    }

    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'learning_id' => 'required|exists:learnings,id',
            'material_title' => 'required|string|max:255',
            'material_description' => 'required|string',
            'class_level' => 'required|string|max:255',
            'shared_at' => 'required|date',
            'publication_status' => 'required|string',
            'status' => 'required|string',
            'max_file' => 'required|integer|min:1|max:100',
            'resources.*.resource_name' => 'nullable|string',
            'resources.*.resource_type' => 'nullable|string',
            'resources.*.resource_url' => 'nullable',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'learning_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'material_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_at.required' => 'Ups, Anda Belum Melengkapi Form',
            'publication_status.required' => 'Ups, Anda Belum Melengkapi Form',
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
            'max_file.required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $material = Material::find($id);

        if (!$material) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }

        $data = $request->all();

        if (isset($data['resources']) && count($data['resources']) > $data['max_file']) {
            return $this->sendError('Jumlah lampiran melebihi batas maksimal.', null, 200);
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['resource_url'])) {
                    if ($resource['resource_url'] instanceof UploadedFile) {
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
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id')->toArray();

            if (!in_array($data['learning_id'], $learningIds)) {
                return $this->sendError('Anda tidak mempunyai akses untuk mengakses learning ini', null, 200);
            }
        }

        $originalLearningId = $material->learning_id;

        $material->update([
            'learning_id' => $data['learning_id'] ?? $material->learning_id,
            'material_title' => $data['material_title'] ?? $material->material_title,
            'material_description' => $data['material_description'] ?? $material->material_description,
            'class_level' => $data['class_level'] ?? $material->class_level,
            'shared_at' => $data['shared_at'] ?? $material->shared_at,
            'publication_status' => $data['publication_status'] ?? $material->publication_status,
            'status' => $data['status'] ?? $material->status,
            'max_file' => $data['max_file'] ?? $material->max_file,
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

                            if ($resource['resource_url'] instanceof UploadedFile) {

                                $folder = match ($resource['resource_type']) {
                                    'image' => 'materials/images',
                                    'document' => 'materials/documents',
                                    'archive' => 'materials/archives',
                                    'audio' => 'materials/audio',
                                    'video' => 'materials/videos',
                                    default => 'materials/others',
                                };

                                $uploadResult = $this->uploadFile($resource['resource_url'], $folder);
                            } else {
                                $uploadResult['path'] = $resource['resource_url'];
                                $uploadResult['size'] = $resource['resource_size'] ?? null;
                                $uploadResult['extension'] = $resource['resource_extension'] ?? null;
                            }

                            $generateIdMaterialResource = IdGenerator::generate(['table' => 'material_resources', 'length' => 16, 'prefix' => 'MATR-']);
                            MaterialResource::create([
                                'id' => $generateIdMaterialResource,
                                'material_id' => $material->id,
                                'material_bank_id' => null,
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

        // Update notification if learning_id has changed
        if ($originalLearningId !== $material->learning_id) {
            $teacherId = $userLogin->role === 'TEACHER' ? $userLogin->is_teacher->id : $data['teacher_id'];
            $learning = Learning::find($material->learning_id);
            $courseId = $learning->course;
            $teacherSubclass = TeacherSubClasses::where('teacher_id', $teacherId)->where('course', $courseId)->first();
            $this->notifyTeacher($teacherId, 'material', $material->material_title);
            if ($teacherSubclass) {
                $this->notifyStudents($courseId, $teacherSubclass->sub_class_id, 'material', $material->material_title);
            }
        }

        $material->load(['material_resources', 'learning']);
        return $this->sendResponse($material, 'Berhasil mengubah data material');
    }

    public function destroy($id)
    {

        $userLogin = auth()->user();

        $material = Material::find($id);

        if (!$material) {
            return $this->sendError('Material tidak ditemukan.', null, 200);
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id')->toArray();

            if (!in_array($material->learning_id, $learningIds)) {
                return $this->sendError('Anda tidak mempunyai akses untuk mengakses learning ini', [], 200);
            }
        }

        foreach ($material->material_resources as $resource) {
            $this->removeFile($resource->resource_url);
            $resource->delete();
        }

        $material->delete();

        return $this->sendResponse($material, 'Berhasil menghapus data material');
    }
}