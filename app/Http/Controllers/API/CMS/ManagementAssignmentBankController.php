<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\AssignmentAttachment;
use App\Models\AssignmentBank;
use App\Models\Course;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementAssignmentBankController extends Controller
{
    use CommonTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');

        $query = AssignmentBank::query();

        if ($userLogin->role === 'TEACHER') {
            $query->where('created_by', $userLogin->is_teacher->id);
        }

        if ($search) {
            $query->where('assignment_title', 'like', '%' . $search . '%');
        }

        $assignments = $query->get();

        if ($assignments->isEmpty()) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        $assignments->load(['assignment_attachments', 'course', 'created_by']);

        return $this->sendResponse($assignments, 'Berhasil mengambil semua data assignment');
    }


    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'courses_name' => 'required|exists:courses,id',
            'assignment_title' => 'required|string|max:255',
            'assignment_description' => 'required|string',
            'instruction' => 'required|string',
            'class_level' => 'required|string|max:255',
            'due_date' => 'required|date',
            'limit_submit' => 'required|integer',
            'is_visibleGrade' => 'boolean',
            'max_attach' => 'required|integer|max:10',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable|file',
            'resources.*.file_type' => 'nullable|string',
        ], [
            'courses_name.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'instruction.required' => 'Ups, Anda Belum Melengkapi Form',
            'due_date.required' => 'Ups, Anda Belum Melengkapi Form',
            'limit_submit.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
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
                if (isset($resource['file_url'])) {
                    $validExtensions = [];

                    switch ($resource['file_type']) {
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

                    if (!empty($validExtensions) && $resource['file_type'] !== 'url' && $resource['file_type'] !== 'youtube') {
                        $extension = strtolower($resource['file_url']->getClientOriginalExtension());
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

        $courseData = $courseData->find($data['courses_name']);

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

        $generateIdAssigment = IdGenerator::generate(['table' => 'assignment_banks', 'length' => 16, 'prefix' => 'ASGB-']);


        $assignment = AssignmentBank::create([
            'id' => $generateIdAssigment,
            'courses_name' => $data['courses_name'],
            'assignment_title' => $data['assignment_title'],
            'assignment_description' => $data['assignment_description'],
            'instruction' => $data['instruction'],
            'class_level' => $data['class_level'],
            'due_date' => $data['due_date'],
            'limit_submit' => $data['limit_submit'],
            'is_visibleGrade' => $data['is_visibleGrade'] ?? false,
            'created_by' => $data['created_by'],
            'max_attach' => $data['max_attach'],
        ]);

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $uploadResult = null;
                    if ($resource['file_type'] === 'url' || $resource['file_type'] === 'youtube') {
                        $uploadResult['path'] = $resource['file_url'];
                        $uploadResult['size'] = null;
                        $uploadResult['extension'] = null;
                    } else {
                        $folder = match ($resource['file_type']) {
                            'image' => 'assignments/images',
                            'document' => 'assignments/documents',
                            'archive' => 'assignments/archives',
                            'audio' => 'assignments/audio',
                            'video' => 'assignments/videos',
                            default => 'assignments/others',
                        };

                        $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                    }

                    $generateIdAssigmentAttachment = IdGenerator::generate(['table' => 'assignment_attachments', 'length' => 16, 'prefix' => 'ASGA-']);
                    AssignmentAttachment::create([
                        'id' => $generateIdAssigmentAttachment,
                        'assignment_id' => null,
                        'assignment_bank_id' => $assignment->id,
                        'file_name' => $resource['file_name'],
                        'file_type' => $resource['file_type'],
                        'file_url' => $uploadResult['path'],
                        'file_size' => $uploadResult['size'],
                        'file_extension' => $uploadResult['extension'],
                    ]);
                }
            }
        }

        $assignment->load(['assignment_attachments', 'course', 'created_by']);
        return $this->sendResponse($assignment, 'Berhasil menambahkan data assignment', 201);
    }


    public function show($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $assignment = AssignmentBank::where('created_by', $userLogin->is_teacher->id)->find($id);
        } else {
            $assignment = AssignmentBank::find($id);
        }

        if (!$assignment) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        $assignment->load(['assignment_attachments', 'course', 'created_by']);
        return $this->sendResponse($assignment, 'Berhasil mengambil data assignment');
    }

    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $assignment = AssignmentBank::where('created_by', $userLogin->is_teacher->id)->find($id);
        } else {
            $assignment = AssignmentBank::find($id);
        }

        if (!$assignment) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'courses_name' => 'required|exists:courses,id',
            'assignment_title' => 'sometimes|required|string|max:255',
            'assignment_description' => 'sometimes|required|string',
            'instruction' => 'sometimes|required|string',
            'class_level' => 'sometimes|required|string|max:255',
            'due_date' => 'sometimes|required|date',
            'limit_submit' => 'sometimes|required|integer',
            'is_visibleGrade' => 'boolean',
            'max_attach' => 'sometimes|required|integer',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable|file',
            'resources.*.file_type' => 'nullable|string',
        ], [
            'courses_name.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'instruction.required' => 'Ups, Anda Belum Melengkapi Form',
            'due_date.required' => 'Ups, Anda Belum Melengkapi Form',
            'limit_submit.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
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
                if (isset($resource['file_url'])) {
                    $validExtensions = [];

                    switch ($resource['file_type']) {
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

                    if (!empty($validExtensions) && $resource['file_type'] !== 'url' && $resource['file_type'] !== 'youtube') {
                        $extension = strtolower($resource['file_url']->getClientOriginalExtension());
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
            $courseData = Course::where('created_by', $userLogin->id);
        }

        $courseData = $courseData->find($data['courses_name']);

        if (!$courseData) {
            return $this->sendError('Course tidak ditemukan.', null, 200);
        }

        $assignment->update([
            'courses_name' => $data['courses_name'],
            'assignment_title' => $data['assignment_title'] ?? $assignment->assignment_title,
            'assignment_description' => $data['assignment_description'] ?? $assignment->assignment_description,
            'instruction' => $data['instruction'] ?? $assignment->instruction,
            'class_level' => $data['class_level'] ?? $assignment->class_level,
            'due_date' => $data['due_date'] ?? $assignment->due_date,
            'limit_submit' => $data['limit_submit'] ?? $assignment->limit_submit,
            'is_visibleGrade' => $data['is_visibleGrade'] ?? $assignment->is_visibleGrade,
            'max_attach' => $data['max_attach'] ?? $assignment->max_attach,
        ]);



        // Handle resources
        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $existingResources = $assignment->assignment_attachments()->where('file_type', $resource['file_type'])->get();

                    foreach ($existingResources as $existingResource) {
                        $this->removeFile($existingResource->file_url);
                        $existingResource->delete();
                    }

                    foreach ($data['resources'] as $resource) {
                        if (isset($resource['file_url'])) {
                            $uploadResult = null;
                            if ($resource['file_type'] === 'url' || $resource['file_type'] === 'youtube') {
                                $uploadResult['path'] = $resource['file_url'];
                                $uploadResult['size'] = null;
                                $uploadResult['extension'] = null;
                            } else {
                                $folder = match ($resource['file_type']) {
                                    'image' => 'assignments/images',
                                    'document' => 'assignments/documents',
                                    'archive' => 'assignments/archives',
                                    'audio' => 'assignments/audio',
                                    'video' => 'assignments/videos',
                                    default => 'assignments/others',
                                };
                                $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                            }

                            $generateIdAssigmentAttachment = IdGenerator::generate(['table' => 'assignment_attachments', 'length' => 16, 'prefix' => 'ASGA-']);
                            AssignmentAttachment::create([
                                'id' => $generateIdAssigmentAttachment,
                                'assignment_id' => null,
                                'assignment_bank_id' => $assignment->id,
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
        }

        $assignment->load(['assignment_attachments', 'course', 'created_by']);
        return $this->sendResponse($assignment, 'Berhasil mengubah data assignment');
    }



    public function destroy($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $assignment = AssignmentBank::where('created_by', $userLogin->is_teacher->id)->find($id);
        } else {
            $assignment = AssignmentBank::find($id);
        }

        if (!$assignment) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        foreach ($assignment->assignment_attachments as $resource) {
            $this->removeFile($resource->file_url);
            $resource->delete();
        }

        $assignment->delete();

        return $this->sendResponse($assignment, 'Berhasil menghapus data assignment');
    }
}
