<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Submission;
use App\Models\SubmissionAttachment;
use App\Models\User;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{

    use CommonTrait;

    public function index()
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'STUDENT') {
            $student = $userLogin->is_student;

            $submissions = Submission::with(['assignment', 'submission_attachments'])
                ->where('student_id', $student->id)
                ->get();

            if ($submissions->isEmpty()) {
                return $this->sendError('Tidak ada data submission.', null, 200);
            }

            foreach ($submissions as $submission) {
                if ($submission->assignment->is_visibleGrade) {
                    $submission->load('grades');
                }
            }

            return $this->sendResponse($submissions, 'Berhasil mengambil semua data submission');
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id');

            if ($learningIds->isEmpty()) {
                return $this->sendError('Tidak ada data submission untuk teacher ini.', null, 200);
            }

            $assignments = Assignment::whereIn('learning_id', $learningIds)->pluck('id');

            if ($assignments->isEmpty()) {
                return $this->sendError('Tidak ada data submission untuk teacher ini.', null, 200);
            }

            $submissions = Submission::with(['assignment', 'grades', 'submission_attachments'])
                ->whereIn('assignment_id', $assignments)
                ->get();

            $submissions->each(function ($submission) {
                $student = Student::where('id', $submission->student_id)->first();
                $user = User::where('id', $student->user_id)->first();

                $submission->student = [
                    'student_id' => $student->id,
                    'user_id' => $user->id,
                    'name' => $user->fullname,
                    'nisn' => $student->nisn,
                ];
            });

            if ($submissions->isEmpty()) {
                return $this->sendError('Tidak ada data submission.', null, 200);
            }

            return $this->sendResponse($submissions, 'Berhasil mengambil semua data submission');
        }

        // For admin roles
        $submissions = Submission::with(['assignment', 'grades', 'submission_attachments'])->get();

        if ($submissions->isEmpty()) {
            return $this->sendError('Tidak ada data submission.', null, 200);
        }

        return $this->sendResponse($submissions, 'Berhasil mengambil semua data submission');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        if ($userLogin->role != 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengirim submission.', null, 200);
        }

        $assignment = Assignment::find($request->assignment_id);

        if (!$assignment) {
            return $this->sendError('Assignment tidak ditemukan.', null, 200);
        }

        $rules = [
            'assignment_id' => 'required|exists:assignments,id',
            'submission_content' => 'required|string|max:255',
            'submission_note' => 'nullable|string',
        ];

        if ($assignment->collection_type === 'Lampiran' || $assignment->collection_type === 'All') {
            $rules['resources.*.file_name'] = 'required|string';
            $rules['resources.*.file_url'] = 'required|file';
            $rules['resources.*.file_type'] = 'required|string';
        } else {
            $rules['resources.*.file_name'] = 'nullable|string';
            $rules['resources.*.file_url'] = 'nullable|file';
            $rules['resources.*.file_type'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules, [
            'assigment_id.exists' => 'Ups, Id Assignment Tidak Valid',
            'assignment_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'submission_content.required' => 'Submission harus diisi.',
            'resources.*.file_name.required' => 'Nama file harus diisi',
            'resources.*.file_url.required' => 'URL file harus diisi',
            'resources.*.file_type.required' => 'Jenis file harus diisi',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $validated = $validator->validated();
        $validated['student_id'] = $userLogin->is_student->id;

        $submittedAt = Carbon::now('Asia/Jakarta');
        $dueDate = Carbon::parse($assignment->due_date, 'Asia/Jakarta');
        $endTime = Carbon::parse($assignment->end_time, 'Asia/Jakarta');

        $endTimeWithTolerance = $endTime->copy()->addMinutes(15);
        $dueDateTimeWithTolerance = Carbon::create(
            $dueDate->year,
            $dueDate->month,
            $dueDate->day,
            $endTimeWithTolerance->hour,
            $endTimeWithTolerance->minute,
            $endTimeWithTolerance->second,
            'Asia/Jakarta'
        );

        if ($submittedAt->greaterThan($dueDateTimeWithTolerance)) {
            return $this->sendError('Anda tidak dapat mengirim submission yang melebihi deadline.', null, 200);
        }

        if ($assignment->limit_submit <= 0) {
            return $this->sendError('Anda tidak dapat mengirim submission lebih dari limit.', null, 200);
        }

        $assignment->decrement('limit_submit');
        $assignment->save();
        $validated['submitted_at'] = $submittedAt;
        $validated['id'] = IdGenerator::generate(['table' => 'submissions', 'length' => 16, 'prefix' => 'SMS-']);

        $submission = Submission::create($validated);

        if (isset($validated['resources'])) {
            foreach ($validated['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $folder = match ($resource['file_type']) {
                        'document' => 'submissions/documents',
                        'image' => 'submissions/images',
                        'video' => 'submissions/videos',
                        default => 'submissions/others',
                    };
                    $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                    $generateIdSubmissionAttachment = IdGenerator::generate(['table' => 'submission_attachments', 'length' => 16, 'prefix' => 'SMSA-']);
                    SubmissionAttachment::create([
                        'id' => $generateIdSubmissionAttachment,
                        'submission_id' => $submission->id,
                        'file_name' => $resource['file_name'],
                        'file_type' => $resource['file_type'],
                        'file_url' => $uploadResult['path'],
                        'file_size' => $uploadResult['size'],
                        'file_extension' => $uploadResult['extension'],
                    ]);
                }
            }
        }

        $generateIdGrade = IdGenerator::generate(['table' => 'grades', 'length' => 16, 'prefix' => 'GRA-']);
        Grade::create([
            'id' => $generateIdGrade,
            'submission_id' => $submission->id,
            'knowledge' => null,
            'skills' => null,
            'graded_at' => null
        ]);

        return $this->sendResponse($submission, 'Berhasil menambahkan data submission', 201);
    }


    public function show($id)
    {
        $userLogin = auth()->user();
        $submission = Submission::with(['assignment', 'grades', 'submission_attachments'])->find($id);

        if (!$submission) {
            return $this->sendError('Tidak ada data submission.', null, 200);
        }

        if ($userLogin->role === 'STUDENT' && $userLogin->is_student->id !== $submission->student_id) {
            return $this->sendError('Anda tidak memiliki izin untuk melihat submission ini.', null, 200);
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id');
            $assignmentIds = Assignment::whereIn('learning_id', $learningIds)->pluck('id');

            if ($assignmentIds->isEmpty()) {
                return $this->sendError('Tidak ada data submission untuk teacher ini.', null, 200);
            }

            if (!in_array($submission->assignment_id, $assignmentIds->toArray())) {
                return $this->sendError('Anda tidak mempunyai akses untuk melihat submission ini.', null, 200);
            }
        }

        return $this->sendResponse($submission, 'Berhasil mengambil data submission');
    }


    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();
        $submission = Submission::with(['assignment', 'grades', 'submission_attachments'])->find($id);

        if (!$submission) {
            return $this->sendError('Submission tidak ditemukan.', null, 200);
        }

        if ($userLogin->role === 'STUDENT' && $userLogin->is_student->id !== $submission->student_id) {
            return $this->sendError('Anda tidak memiliki izin untuk mengubah submission ini.', null, 200);
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = $teacher->learnings()->pluck('id');
            $assignmentIds = Assignment::whereIn('learning_id', $learningIds)->pluck('id');

            if ($assignmentIds->isEmpty() || !in_array($submission->assignment_id, $assignmentIds->toArray())) {
                return $this->sendError('Anda tidak mempunyai akses untuk mengubah submission ini.', null, 200);
            }
        }

        $assignment = Assignment::find($submission->assignment_id);

        $rules = [];

        if ($userLogin->role === 'STUDENT') {
            $rules = [
                'submission_content' => 'required|string|max:255',
                'submission_note' => 'nullable|string',
            ];
            if ($assignment->collection_type === 'Lampiran' || $assignment->collection_type === 'All') {
                $rules['resources.*.file_name'] = 'required|string';
                $rules['resources.*.file_url'] = 'required|file';
                $rules['resources.*.file_type'] = 'required|string';
            } else {
                $rules['resources.*.file_name'] = 'nullable|string';
                $rules['resources.*.file_url'] = 'nullable|file';
                $rules['resources.*.file_type'] = 'nullable|string';
            }
        }

        $validator = Validator::make($request->all(), $rules, [
            'submission_content.required' => 'Ups, Anda Belum Melengkapi Form',
            'resources.*.file_name.required' => 'Nama file harus diisi',
            'resources.*.file_url.required' => 'URL file harus diisi',
            'resources.*.file_type.required' => 'Jenis file harus diisi',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $submittedAt = Carbon::now('Asia/Jakarta');
        $dueDate = Carbon::parse($assignment->due_date, 'Asia/Jakarta');
        $endTime = Carbon::parse($assignment->end_time, 'Asia/Jakarta');

        $endTimeWithTolerance = $endTime->copy()->addMinutes(15);
        $dueDateTimeWithTolerance = Carbon::create(
            $dueDate->year,
            $dueDate->month,
            $dueDate->day,
            $endTimeWithTolerance->hour,
            $endTimeWithTolerance->minute,
            $endTimeWithTolerance->second,
            'Asia/Jakarta'
        );

        if ($submittedAt->greaterThan($dueDateTimeWithTolerance)) {
            return $this->sendError('Anda tidak dapat mengirim submission yang melebihi deadline.', null, 200);
        }

        if ($assignment->limit_submit <= 0) {
            return $this->sendError('Anda tidak dapat mengirim submission lebih dari limit.', null, 200);
        }

        $validated = $request->all();
        $validated['submitted_at'] = $submittedAt;

        $submission->update($validated);

        if (isset($validated['resources'])) {
            foreach ($validated['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $existingResources = $submission->submission_attachments()->where('file_type', $resource['file_type'])->get();

                    foreach ($existingResources as $existingResource) {
                        $this->removeFile($existingResource->file_url);
                        $existingResource->delete();
                    }

                    foreach ($validated['resources'] as $resource) {
                        if (isset($resource['file_url'])) {
                            $folder = match ($resource['file_type']) {
                                'document' => 'submissions/documents',
                                'image' => 'submissions/images',
                                'video' => 'submissions/videos',
                                default => 'submissions/others',
                            };
                            $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                            $generateIdSubmissionAttachment = IdGenerator::generate(['table' => 'submission_attachments', 'length' => 16, 'prefix' => 'SMSA-']);
                            SubmissionAttachment::create([
                                'id' => $generateIdSubmissionAttachment,
                                'submission_id' => $submission->id,
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

        if ($userLogin->role !== 'STUDENT') {
            $grade = Grade::where('submission_id', $submission->id)->first();
            if ($grade) {
                if ($request->has('knowledge') || $request->has('skills')) {
                    $grade->update([
                        'knowledge' => $request->input('knowledge', $grade->knowledge),
                        'skills' => $request->input('skills', $grade->skills),
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }

                if ($request->has('is_main')) {
                    $grade->update([
                        'is_main' => $request->input('is_main') == 1 ? 1 : 0,
                    ]);
                }
            }
        }

        $assignment->save();

        return $this->sendResponse($submission, 'Berhasil mengubah data submission');
    }


    public function destroy($id)
    {
        $userLogin = auth()->user();

        $submission = Submission::with(['assignment', 'grades', 'submission_attachments'])->find($id);

        if (!$submission) {
            return $this->sendError('Tidak ada data submission.', null, 200);
        }

        if ($userLogin->role === 'STUDENT' && $userLogin->is_student->id !== $submission->student_id) {
            return $this->sendError('Anda tidak memiliki izin untuk menghapus submission ini.', null, 200);
        }

        $submission->delete();

        return $this->sendResponse($submission, 'Submission berhasil dihapus.');
    }
}
