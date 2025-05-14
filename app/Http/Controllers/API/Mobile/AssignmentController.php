<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\NotifiableTrait;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\Grade;
use App\Models\Learning;
use App\Models\Submission;
use App\Models\TeacherSubClasses;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    use CommonTrait, NotifiableTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');
        $limit = $request->query('limit');
    
        $query = Assignment::query();
    
        if ($userLogin->role === 'STUDENT') {
            // Ambil sub_class_id siswa yang login
            $student = $userLogin->is_student;
            $subClassId = $student->sub_class_id;
    
            // Ambil semua learning_id yang berkaitan dengan sub_class siswa
            $learningIds = TeacherSubClasses::where('sub_class_id', $subClassId)
                            ->pluck('learning_id');
    
            // Filter assignment berdasarkan learning_id yang sesuai
            $query->whereIn('learning_id', $learningIds);
        } elseif ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id');
            $query->whereIn('learning_id', $learningIds);
        }
    
        if ($search) {
            $query->where('assignment_title', 'like', '%' . $search . '%');
        }
    
        $query->orderBy('created_at', 'desc');
    
        $assignments = $limit ? $query->limit($limit)->get() : $query->get();
    
        if ($assignments->isEmpty()) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }
    
        $assignments->load(['assignment_attachments', 'learning', 'submissions']);
    
        return $this->sendResponse($assignments, 'Berhasil mengambil semua data assignment');
    }
    


    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'learning_id' => 'required|exists:learnings,id',
            'assignment_title' => 'required|string|max:255',
            'assignment_description' => 'required|string',
            'instruction' => 'required|string',
            'due_date' => 'required|date',
            'end_time' => 'required|date_format:H:i',
            'collection_type' => 'required|in:Catatan,Lampiran,All',
            'limit_submit' => 'required|integer',
            'class_level' => 'required|string|max:255',
            'is_visibleGrade' => 'boolean',
            'publication_status' => 'required|string',
            'max_attach' => 'required|integer|max:10',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable',
            'resources.*.file_type' => 'nullable|string',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'learning_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'instruction.required' => 'Ups, Anda Belum Melengkapi Form',
            'due_date.required' => 'Ups, Anda Belum Melengkapi Form',
            'end_time.required' => 'Ups, Anda Belum Melengkapi Form',
            'collection_type.required' => 'Ups, Anda Belum Melengkapi Form',
            'limit_submit.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'max_attach.required' => 'Ups, Anda Belum Melengkapi Form',
            'publication_status.required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
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
                    if ($resource['file_url'] instanceof UploadedFile) {
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
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id')->toArray();

            if (!in_array($data['learning_id'], $learningIds)) {
                return $this->sendError('Anda tidak mempunyai akses untuk mengakses learning ini.', null, 200);
            }
        }

        $generateIdAssigment = IdGenerator::generate(['table' => 'assignments', 'length' => 16, 'prefix' => 'ASG-']);

        $assignment = Assignment::create([
            'id' => $generateIdAssigment,
            'learning_id' => $data['learning_id'],
            'assignment_title' => $data['assignment_title'],
            'assignment_description' => $data['assignment_description'],
            'instruction' => $data['instruction'],
            'due_date' => $data['due_date'],
            'end_time' => $data['end_time'],
            'collection_type' => $data['collection_type'],
            'limit_submit' => $data['limit_submit'],
            'class_level' => $data['class_level'],
            'is_visibleGrade' => $data['is_visibleGrade'] ?? false,
            'publication_status' => $data['publication_status'],
            'max_attach' => $data['max_attach'],
        ]);

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $uploadResult = null;

                    if ($resource['file_url'] instanceof UploadedFile) {
                        $folder = match ($resource['file_type']) {
                            'image' => 'assignments/images',
                            'document' => 'assignments/documents',
                            'archive' => 'assignments/archives',
                            'audio' => 'assignments/audio',
                            'video' => 'assignments/videos',
                            default => 'assignments/others',
                        };

                        $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                    } else {
                        $uploadResult['path'] = $resource['file_url'];
                        $uploadResult['size'] = $resource['file_size'] ?? null;
                        $uploadResult['extension'] = $resource['file_extension'] ?? null;
                    }

                    $generateIdAssigmentAttachment = IdGenerator::generate(['table' => 'assignment_attachments', 'length' => 16, 'prefix' => 'ASGA-']);
                    AssignmentAttachment::create([
                        'id' => $generateIdAssigmentAttachment,
                        'assignment_id' => $assignment->id,
                        'assignment_bank_id' => null,
                        'file_name' => $resource['file_name'],
                        'file_type' => $resource['file_type'],
                        'file_url' => $uploadResult['path'],
                        'file_size' => $uploadResult['size'],
                        'file_extension' => $uploadResult['extension'],
                    ]);
                }
            }
        }

        // Notification
        $teacherId = $userLogin->role === 'TEACHER' ? $userLogin->is_teacher->id : $data['teacher_id'];
        $learning = Learning::find($data['learning_id']);
        $courseId = $learning->course;
        $teacherSubclass = TeacherSubClasses::where('teacher_id', $teacherId)->where('course', $courseId)->first();
        $this->notifyTeacher($teacherId, 'assignment', $assignment->assignment_title);
        if ($teacherSubclass) {
            $this->notifyStudents($courseId, $teacherSubclass->sub_class_id, 'assignment', $assignment->assignment_title);
        }


        $assignment->load(['assignment_attachments', 'learning', 'submissions']);
        return $this->sendResponse($assignment, 'Berhasil menambahkan data assignment', 201);
    }


    public function show($id)
    {
        $userLogin = auth()->user();

        $query = Assignment::query();

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id');
            $query->whereIn('learning_id', $learningIds);
        }

        $assignment = $query->find($id);

        if (!$assignment) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        $assignment->load(['assignment_attachments', 'learning', 'submissions']);
        return $this->sendResponse($assignment, 'Berhasil mengambil data assignment');
    }

    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'learning_id' => 'required|exists:learnings,id',
            'assignment_title' => 'required|string|max:255',
            'assignment_description' => 'required|string',
            'instruction' => 'required|string',
            'due_date' => 'required|date',
            'end_time' => 'required|date_format:H:i',
            'collection_type' => 'required|in:Catatan,Lampiran,All',
            'limit_submit' => 'required|integer',
            'class_level' => 'required|string|max:255',
            'is_visibleGrade' => 'boolean',
            'publication_status' => 'required|string',
            'max_attach' => 'required|integer|max:10',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable|file',
            'resources.*.file_type' => 'nullable|string',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'learning_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'assignment_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'instruction.required' => 'Ups, Anda Belum Melengkapi Form',
            'due_date.required' => 'Ups, Anda Belum Melengkapi Form',
            'end_time.required' => 'Ups, Anda Belum Melengkapi Form',
            'collection_type.required' => 'Ups, Anda Belum Melengkapi Form',
            'limit_submit.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'max_attach.required' => 'Ups, Anda Belum Melengkapi Form',
            'publication_status.required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $assignment = Assignment::find($id);

        if (!$assignment) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        $data = $request->all();

        if (isset($data['resources']) && count($data['resources']) > $data['max_attach']) {
            return $this->sendError('Jumlah lampiran melebihi batas maksimal.', null, 200);
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    if ($resource['file_url'] instanceof UploadedFile) {
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
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id')->toArray();

            if (!in_array($data['learning_id'], $learningIds)) {
                return $this->sendError('Anda tidak mempunyai akses untuk mengakses learning ini.', null, 200);
            }
        }

        $originalLearningId = $assignment->learning_id;

        $assignment->update([
            'learning_id' => $data['learning_id'],
            'assignment_title' => $data['assignment_title'],
            'assignment_description' => $data['assignment_description'],
            'instruction' => $data['instruction'],
            'due_date' => $data['due_date'],
            'end_time' => $data['end_time'],
            'collection_type' => $data['collection_type'],
            'limit_submit' => $data['limit_submit'],
            'class_level' => $data['class_level'],
            'is_visibleGrade' => $data['is_visibleGrade'] ?? false,
            'publication_status' => $data['publication_status'],
            'max_attach' => $data['max_attach'],
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

                            if ($resource['file_url'] instanceof UploadedFile) {
                                $folder = match ($resource['file_type']) {
                                    'image' => 'assignments/images',
                                    'document' => 'assignments/documents',
                                    'archive' => 'assignments/archives',
                                    'audio' => 'assignments/audio',
                                    'video' => 'assignments/videos',
                                    default => 'assignments/others',
                                };
                                $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                            } else {
                                $uploadResult['path'] = $resource['file_url'];
                                $uploadResult['size'] = $resource['file_size'] ?? null;
                                $uploadResult['extension'] = $resource['file_extension'] ?? null;
                            }

                            $generateIdAssigmentAttachment = IdGenerator::generate(['table' => 'assignment_attachments', 'length' => 16, 'prefix' => 'ASGA-']);
                            AssignmentAttachment::create([
                                'id' => $generateIdAssigmentAttachment,
                                'assignment_id' => $assignment->id,
                                'assignment_bank_id' => null,
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

        // Update notification if learning_id has changed
        if ($originalLearningId !== $assignment->learning_id) {
            $teacherId = $userLogin->role === 'TEACHER' ? $userLogin->is_teacher->id : $data['teacher_id'];
            $learning = Learning::find($assignment->learning_id);
            $courseId = $learning->course;
            $teacherSubclass = TeacherSubClasses::where('teacher_id', $teacherId)->where('course', $courseId)->first();
            $this->notifyTeacher($teacherId, 'assignment', $assignment->assignment_title);
            if ($teacherSubclass) {
                $this->notifyStudents($courseId, $teacherSubclass->sub_class_id, 'assignment', $assignment->assignment_title);
            }
        }

        $assignment->load(['assignment_attachments', 'learning', 'submissions']);
        return $this->sendResponse($assignment, 'Berhasil mengubah data assignment');
    }

    public function destroy($id)
    {
        $userLogin = auth()->user();
        $assignment = Assignment::find($id);

        if (!$assignment) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id')->toArray();

            if (!in_array($assignment->learning_id, $learningIds)) {
                return $this->sendError('Anda tidak mempunyai akses untuk mengakses learning ini.', null, 200);
            }
        }

        foreach ($assignment->assignment_attachments as $resource) {
            $this->removeFile($resource->file_url);
            $resource->delete();
        }

        $submissions = Submission::where('assignment_id', $assignment->id)->get();
        foreach ($submissions as $submission) {
            Grade::where('submission_id', $submission->id)->delete();
            $submission->delete();
        }

        $assignment->delete();

        return $this->sendResponse($assignment, 'Berhasil menghapus data assignment');
    }
}