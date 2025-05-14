<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\AssignmentAttachment;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PembelajaranTugasController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_tugas(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareTugasDataToSend($learning_id);

        return view('teacher.pengajar.pembelajaran.tugas.index', $dataToSend);
    }

    public function import_tugas(Request $request, $learning_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = Validator::make($request->all(), [
            'bank_assignment_id' => 'required|exists:assignment_banks,id',
            'status' => 'required',
            'due_date' => 'required|date',
            'end_time' => 'required|date_format:H:i',
            'collection_type' => 'required',
            'publication_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $response_assignment_bank = $this->fetchData('/api/v1/cms/teacher/assignment-bank/' . $request->bank_assignment_id);

        $collection_type = null;
        if (count($request->input('collection_type')) > 1) {
            $collection_type = 'All';
        } else {
            $collection_type = $request->input('collection_type')[0];
        }

        $form_data = [
            'learning_id' => $learning_id,
            'assignment_title' => $response_assignment_bank->data->assignment_title,
            'assignment_description' => $response_assignment_bank->data->assignment_description,
            'instruction' => $response_assignment_bank->data->instruction,
            'due_date' => $request->due_date,
            'end_time' => $request->end_time,
            'collection_type' => $collection_type,
            'limit_submit' => $response_assignment_bank->data->limit_submit,
            'class_level' => $response_assignment_bank->data->class_level,
            'is_visibleGrade' => $response_assignment_bank->data->is_visibleGrade,
            'publication_status' => $request->publication_status,
            'shared_at' => $request->shared_at ?? null,
            'status' => $request->status,
            'max_attach' => $response_assignment_bank->data->max_attach,
            'resources' => array_map(function ($resource) {
                return [
                    'file_name' => $resource->file_name,
                    'file_type' => $resource->file_type,
                    'file_url' => $resource->file_url,
                    'file_size' => $resource->file_size,
                    'file_extension' => $resource->file_extension,
                ];
            }, $response_assignment_bank->data->assignment_attachments ?? []),
        ];

        $response_data = $this->postData('/api/v1/mobile/teacher/assignment', $form_data, 'json');

        return $this->handleTugasResponse($response_data, 'import', $learning_id);
    }

    public function v_add_tugas(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareTugasDataToSend($learning_id);

        return view('teacher.pengajar.pembelajaran.tugas.add', $dataToSend);
    }

    public function add_tugas(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $request->merge([
            'end_time' => Carbon::parse($request->end_time)->format('H:i'),
        ]);

        $validator = $this->validatetugas($request);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $formData = $this->prepareTugasData($request, $learning_id);


        $response_data = $this->postData('/api/v1/mobile/teacher/assignment', $formData, 'json');

        $this->handleResourceManagement($request, $response_data->data->id, null);

        return $this->handleTugasResponse($response_data, 'add', $learning_id);
    }

    public function v_edit_tugas(Request $request, $learning_id, $id)
    {
        $this->authorizeTeacher();

        $dataToSend = $this->prepareTugasDataToSend($learning_id, $id);

        return view('teacher.pengajar.pembelajaran.tugas.edit', $dataToSend);
    }

    public function edit_tugas(Request $request, $learning_id, $id)
    {
        $this->authorizeTeacher();

        $request->merge([
            'end_time' => Carbon::parse($request->end_time)->format('H:i'),
        ]);

        $validator = $this->validatetugas($request);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $formData = $this->prepareTugasData($request, $learning_id);

        $response_data = $this->putData('/api/v1/mobile/teacher/assignment/' . $id, $formData, 'json');

        $this->handleResourceManagement($request, $response_data->data->id, null);

        return $this->handleTugasResponse($response_data, 'edit', $learning_id);
    }

    public function delete_tugas($learning_id, $id)
    {
        $this->authorizeTeacher();

        $response_data = $this->deleteData('/api/v1/mobile/teacher/assignment/' . $id);

        return $this->handleTugasResponse($response_data, 'delete', $learning_id);
    }

    public function download_tugas($learning_id, $id)
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

    public function v_detail_tugas_student(Request $request, $learning_id, $id, $student_id)
    {
        $this->authorizeTeacher();

        // $studentId = $this->convertSubClassId($student_id);
        $studentId = $student_id;
        $student = $this->fetchData('/api/v1/mobile/teacher/enrollment/student');
        $filtered_student = collect($student->data ?? [])->filter(function ($item) use ($studentId) {
            return $item->student->id == $studentId;
        })->first();

        $submissions = $this->fetchData('/api/v1/mobile/teacher/submission');
        $filtered_submissions = collect($submissions->data ?? [])->filter(function ($item) use ($studentId, $id) {
            $submissionStudentId = $item->student_id;
            $submissionId = $item->assignment_id;
            return $submissionStudentId == $studentId && $submissionId == $id;
        });

        $assignment = $this->fetchData('/api/v1/mobile/teacher/assignment/' . $id);

        return view('teacher.pengajar.pembelajaran.tugas.student.index', [
            'student' => $filtered_student ?? null,
            'submissions' => $filtered_submissions ?? [],
            'learning_id' => $learning_id,
            'student_id' => $studentId,
            'assignment_id' => $id,
            'assignment' => $assignment->data ?? null,
        ]);
    }

    public function v_rubah_nilai(Request $request, $learning_id, $id, $student_id, $submission_id)
    {
        $this->authorizeTeacher();

        // $studentId = $this->convertSubClassId($student_id);
        $studentId = $student_id;
        $student = $this->fetchData('/api/v1/mobile/teacher/enrollment/student');
        $filtered_student = collect($student->data ?? [])->filter(function ($item) use ($studentId) {
            return $item->student->id == $studentId;
        })->first();

        $submission = $this->fetchData('/api/v1/mobile/teacher/submission/' . $submission_id);

        return view('teacher.pengajar.pembelajaran.tugas.student.rubah-nilai', [
            'student' => $filtered_student ?? null,
            'submission' => $submission->data ?? null,
            'learning_id' => $learning_id,
            'student_id' => $studentId,
            'assignment_id' => $id,
            'submission_id' => $submission_id,
        ]);
    }

    public function rubah_nilai(Request $request, $learning_id, $id, $student_id, $submission_id)
    {
        $this->authorizeTeacher();

        $is_null = $request->input('is_null', '0');

        $typeMessage = $is_null == '0' ? 'edit-score' : 'edit-score-null';
        if ($is_null == '0') {
            $validator = Validator::make($request->all(), [
                'knowledge' => 'required_without:skills|integer|nullable',
                'skills' => 'required_without:knowledge|integer|nullable',
            ], [
                'required_without' => 'Anda harus mengisi salah satu dari nilai Pengetahuan atau Keterampilan.',
                'integer' => 'Nilai harus berupa angka.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            $knowledge = $request->has('knowledge') ? $request->knowledge : null;
            $skills = $request->has('skills') ? $request->skills : null;
        } else {
            $knowledge = null;
            $skills = null;
        }

        $response_data = $this->putData('/api/v1/mobile/teacher/submission/' . $submission_id, [
            'knowledge' => $knowledge,
            'skills' => $skills,
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, $typeMessage, $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }

    public function simpan_nilai_utama(Request $request, $learning_id, $id, $student_id, $submission_id)
    {
        $this->authorizeTeacher();

        $response_data = $this->putData('/api/v1/mobile/teacher/submission/' . $submission_id, [
            'is_main' => 1,
        ], 'json');

        return redirect()->back()->with('message', $this->getResponseMessage($response_data->success, 'edit-score', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }

    public function feedback_send(Request $request, $learning_id, $id, $student_id, $submission_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'feedback' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        $response_data = $this->putData('/api/v1/mobile/teacher/submission/' . $submission_id, [
            'feedback' => $request->feedback,
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, 'edit-feedback', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }

    private function validatetugas(Request $request)
    {
        return Validator::make($request->all(), [
            'assignment_title' => 'required|string|max:255',
            'assignment_description' => 'required|string',
            'instruction' => 'required|string',
            'due_date' => 'required|date',
            'end_time' => 'required|date_format:H:i',
            'collection_type' => 'required',
            'limit_submit' => 'required|integer',
            'class_level' => 'required|string|max:255',
            'is_visibleGrade' => 'boolean',
            'publication_status' => 'required|string',
            'max_attach' => 'required|integer|max:10',
        ]);
    }

    private function prepareTugasData(Request $request, $learning_id)
    {
        $collection_type = null;
        if (count($request->input('collection_type')) > 1) {
            $collection_type = 'All';
        } else {
            $collection_type = $request->input('collection_type')[0];
        }

        return [
            'learning_id' => $learning_id,
            'assignment_title' => $request->assignment_title,
            'assignment_description' => $request->assignment_description,
            'instruction' => $request->instruction,
            'due_date' => $request->due_date,
            'end_time' => $request->end_time,
            'collection_type' => $collection_type,
            'limit_submit' => $request->limit_submit,
            'class_level' => $request->class_level,
            'is_visibleGrade' => $request->is_visibleGrade,
            'publication_status' => $request->publication_status,
            'max_attach' => $request->max_attach,
        ];
    }

    private function prepareTugasDataToSend($learning_id, $id = null)
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

        $assignments = $id ? $this->fetchData('/api/v1/mobile/teacher/assignment/' . $id) : $this->fetchData('/api/v1/mobile/teacher/assignment');
        $filteredAssignments = $id ? $assignments->data ?? [] : collect($assignments->data ?? [])->filter(function ($assignment) use ($learning_id) {
            return $assignment->learning_id == $learning_id;
        });

        $assignment_banks = $this->fetchData('/api/v1/cms/teacher/assignment-bank');
        $filteredBankAssignment = collect($assignment_banks->data ?? [])->filter(function ($assignment) use ($learning, $nameSubclass) {
            return $assignment->courses_name == $learning->data->course->id && $assignment->class_level == $nameSubclass->class_id;
        });

        $enrollment_students = $this->fetchData('/api/v1/mobile/teacher/enrollment/student');
        $filteredStudents = collect($enrollment_students->data ?? [])->filter(function ($student) use ($learning) {
            return $student->course_id == $learning->data->course->id;
        });

        $submissions = $this->fetchData('/api/v1/mobile/teacher/submission');
        $filteredSubmissions = collect($submissions->data ?? [])->filter(function ($submission) use ($learning_id, $id) {
            return $submission->assignment->learning_id == $learning_id && $submission->assignment_id == $id;
        });

        $filteredStudents = $filteredStudents->map(function ($student) use ($filteredSubmissions, $id) {
            $submission = $filteredSubmissions->firstWhere('student.student_id', $student->student->id);
            if ($submission) {
                $student->assignment_id = $submission->assignment_id;
                $student->status = 'sudah mengerjakan';
                $student->knowledge = $submission->grades[0]->knowledge ?? null;
                $student->skills = $submission->grades[0]->skills ?? null;
                $student->time = $submission->created_at;
                $student->is_main = $submission->grades[0]->is_main;
            } else {
                $student->assignment_id = $id;
                $student->status = 'belum mengerjakan';
                $student->knowledge = null;
                $student->skills = null;
                $student->time = null;
                $student->is_main = null;
            }

            return $student;
        });

        return [
            'learning' => $learning->data ?? null,
            'subclasses' => $nameSubclass,
            'assignments' => $filteredAssignments,
            'learning_id' => $learning_id,
            'assignment_banks' => $filteredBankAssignment ?? [],
            'student_submissions' => $filteredStudents ?? [],
        ];
    }

    private function handleResourceManagement(Request $request, $tugasId, $tugasBankId)
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

    private function handleTugasResponse($response_data, $type, $learning_id)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $dataToSend = $this->prepareTugasDataToSend($learning_id);

        return redirect()->route('teacher.pengajar.pembelajaran.v_tugas', ['learning_id' => $learning_id])
            ->with('message', $message)->with('alertClass', $alertClass)->with('dataToSend', $dataToSend);
    }

    private function getResponseMessage($success, $type, $message)
    {
        if ($success) {
            switch ($type) {
                case 'add':
                    return 'Tugas berhasil ditambahkan.';
                case 'edit':
                    return 'Tugas berhasil diperbarui.';
                case 'edit-main-value':
                    return 'Berhasil menjadikan nilai utama.';
                case 'edit-feedback':
                    return 'Berhasil menyimpan catatan.';
                case 'edit-score':
                    return 'Berhasil menyimpan nilai.';
                case 'edit-score-null':
                    return 'Berhasil menghapus nilai.';
                case 'delete':
                    return 'Tugas berhasil dihapus.';
                case 'import':
                    return 'Tugas berhasil diimport.';
                default:
                    return $message;
            }
        }

        return 'Terjadi kesalahan: ' . $message;
    }
}
