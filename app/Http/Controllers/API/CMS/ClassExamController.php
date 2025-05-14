<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\NotifiableTrait;
use Illuminate\Http\Request;
use App\Models\ClassExam;
use App\Models\Course;
use App\Models\ExamSetting;
use App\Models\Grade;
use App\Models\Learning;
use App\Models\Question;
use App\Models\Response;
use App\Models\TeacherSubClasses;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Hash;

class ClassExamController extends Controller
{

    use CommonTrait, NotifiableTrait;

    public function index(Request $request)
{
    $userLogin = auth()->user();
    $limit = $request->query('limit');

    if ($userLogin->role === 'STUDENT') {
        // Ambil sub_class_id siswa yang login
        $student = $userLogin->is_student;
        $subClassId = $student->sub_class_id;

        // Ambil semua learning_id yang berkaitan dengan sub_class siswa
        $learningIds = TeacherSubClasses::where('sub_class_id', $subClassId)
                        ->pluck('learning_id');

        // Filter ujian berdasarkan learning_id yang sesuai
        $query = ClassExam::whereIn('learning_id', $learningIds);
    } elseif ($userLogin->role === 'TEACHER') {
        // Ambil semua learning_id yang dimiliki oleh guru
        $learningIds = Learning::where('teacher_id', $userLogin->is_teacher->id)->pluck('id');
        $query = ClassExam::whereIn('learning_id', $learningIds);
    } else {
        // Jika bukan student atau teacher, ambil semua ujian
        $query = ClassExam::query();
    }

    $query->with(['learning', 'questions', 'exam_setting'])->orderBy('created_at', 'desc');

    if ($limit) {
        $classExams = $query->limit($limit)->get();
    } else {
        $classExams = $query->get();
    }

    if ($classExams->isEmpty()) {
        return $this->sendError('Tidak ada data class exam.', null, 200);
    }

    $examSettings = ExamSetting::whereIn('exam_id', $classExams->pluck('id')->toArray())->get();

    foreach ($classExams as $classExam) {
        $classExam->exam_setting = $examSettings->where('exam_id', $classExam->id)->first();
    }

    return $this->sendResponse($classExams, 'Berhasil mendapatkan semua class exam');
}


    public function show($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $learningIds = Learning::where('teacher_id', $userLogin->is_teacher->id)->pluck('id');
            $classExam = ClassExam::with(['learning', 'questions', 'exam_setting'])
                ->whereIn('learning_id', $learningIds)
                ->find($id);
        } else {
            $classExam = ClassExam::with(['learning', 'questions', 'exam_setting'])
                ->find($id);
        }

        if (!$classExam) {
            return $this->sendError('Tidak ada data class exam.', null, 200);
        }

        $examSetting = ExamSetting::where('exam_id', $classExam->id)->first();
        $allQuestionsByClassExam = Question::where('exam_id', $classExam->id)->get();
        $totalPoints = $allQuestionsByClassExam->sum('point') ?? 0;

        $classExam->exam_setting = $examSetting;
        $classExam->total_point_question = $totalPoints;

        return $this->sendResponse($classExam, 'Berhasil mendapatkan data class exam');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        // Validator untuk ClassExam dan ExamSetting
        $validator = Validator::make($request->all(), [
            'learning_id' => 'required|exists:learnings,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'instruction' => 'nullable|string',
            'is_active' => 'boolean',
            'status' => 'nullable|string',
            // Validator untuk ExamSetting
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'token' => 'required|string|max:255',
            'duration' => 'required|date_format:H:i',
            'repeat_chance' => 'required|integer',
            'device' => 'nullable|in:Web,Mobile,All',
            'maximum_user' => 'required|integer',
            'is_random_question' => 'boolean',
            'is_random_answer' => 'boolean',
            'is_show_score' => 'boolean',
            'is_show_result' => 'boolean',
        ], [
            'learning_id.exists' => 'Tidak ada data learning.',
            'learning_id.required' => 'Ups, Anda Belum Melengkapi Form.',
            'title.required' => 'Ups, Anda Belum Melengkapi Form.',
            'type.required' => 'Ups, Anda Belum Melengkapi Form.',
            'start_time.required' => 'Ups, Anda Belum Melengkapi Form.',
            'end_time.required' => 'Ups, Anda Belum Melengkapi Form.',
            'end_time.after' => 'Ups, Anda Belum Melengkapi Form.',
            'duration.required' => 'Ups, Anda Belum Melengkapi Form.',
            'token.required' => 'Ups, Anda Belum Melengkapi Form.',
            'maximum_user.required' => 'Ups, Anda Belum Melengkapi Form.',
            'is_active.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_random_question.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_random_answer.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_show_score.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_show_result.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $learning = Learning::where('teacher_id', $userLogin->is_teacher->id)
            ->find($request->learning_id);

        if (!$learning) {
            return $this->sendError('Tidak ada data learning.', null, 200);
        }

        $data = $request->all();
        $generateClassExamId = IdGenerator::generate(['table' => 'class_exams', 'length' => 16, 'prefix' => 'CLE-']);

        // Membuat ClassExam
        $classExamData = [
            'id' => $generateClassExamId,
            'learning_id' => $data['learning_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'instruction' => $data['instruction'],
            'is_active' => $data['is_active'],
            'status' => $data['status'],
        ];

        $classExam = ClassExam::create($classExamData);

        $generateClassExamSettingId = IdGenerator::generate(['table' => 'exam_settings', 'length' => 16, 'prefix' => 'EXS-']);

        $encryptToken = Hash::make($data['token']);

        // Membuat ExamSetting yang terkait
        $examSettingData = [
            'id' => $generateClassExamSettingId,
            'exam_id' => $generateClassExamId,
            'school_exam_id' => null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'token' => $encryptToken,
            'duration' => $data['duration'],
            'repeat_chance' => $data['repeat_chance'],
            'device' => $data['device'],
            'maximum_user' => $data['maximum_user'],
            'is_random_question' => $data['is_random_question'],
            'is_random_answer' => $data['is_random_answer'],
            'is_show_score' => $data['is_show_score'],
            'is_show_result' => $data['is_show_result'],
        ];
        $examSetting = ExamSetting::create($examSettingData);

        // Menggabungkan response dari ClassExam dan ExamSetting
        $response = $classExam->toArray();
        $response['exam_setting'] = $examSetting;

        // Notification
        $teacherId = $userLogin->role === 'TEACHER' ? $userLogin->is_teacher->id : $data['teacher_id'];
        $courseId = $learning->course;
        $teacherSubclass = TeacherSubClasses::where('teacher_id', $teacherId)->where('course', $courseId)->first();

        if ($teacherSubclass) {
            $role = $userLogin->role === 'TEACHER' ? 'TEACHER' : 'STUDENT';
            $courseTitle = Course::find($courseId)->title;

            $this->notifyStudents(
                $teacherId,
                $courseId,
                $teacherSubclass->sub_class_id,
                'ulangan',
                $role,
                $courseTitle
            );
        }

        return $this->sendResponse($response, 'Berhasil menambahkan data class exam', 201);
    }

    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();


        $learningIds = Learning::where('teacher_id', $userLogin->is_teacher->id)->pluck('id');
        $classExam = ClassExam::whereIn('learning_id', $learningIds)->find($id);

        if (!$classExam) {
            return response()->json(['message' => 'Class Exam not found'], 200);
        }

        $validator = Validator::make($request->all(), [
            'learning_id' => 'required|exists:learnings,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'instruction' => 'nullable|string',
            'is_active' => 'boolean',
            'status' => 'nullable|string',
            // Validator untuk ExamSetting
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'token' => 'nullable|string|max:255',
            'duration' => 'required|date_format:H:i',
            'repeat_chance' => 'required|integer',
            'device' => 'nullable|in:Web,Mobile,All',
            'maximum_user' => 'required|integer',
            'is_random_question' => 'boolean',
            'is_random_answer' => 'boolean',
            'is_show_score' => 'boolean',
            'is_show_result' => 'boolean',
        ], [
            'learning_id.exists' => 'Tidak ada data learning.',
            'learning_id.required' => 'Ups, Anda Belum Melengkapi Form.',
            'title.required' => 'Ups, Anda Belum Melengkapi Form.',
            'type.required' => 'Ups, Anda Belum Melengkapi Form.',
            'start_time.required' => 'Ups, Anda Belum Melengkapi Form.',
            'end_time.required' => 'Ups, Anda Belum Melengkapi Form.',
            'end_time.after' => 'Ups, Anda Belum Melengkapi Form.',
            'duration.required' => 'Ups, Anda Belum Melengkapi Form.',
            'maximum_user.required' => 'Ups, Anda Belum Melengkapi Form.',
            'is_active.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_random_question.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_random_answer.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_show_score.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
            'is_show_result.boolean' => 'Ups, tolong isi dengan angka 1 atau 0.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $classExamData = [];
        if ($request->has('learning_id')) $classExamData['learning_id'] = $request->learning_id;
        if ($request->has('title')) $classExamData['title'] = $request->title;
        if ($request->has('description')) $classExamData['description'] = $request->description;
        if ($request->has('type')) $classExamData['type'] = $request->type;
        if ($request->has('instruction')) $classExamData['instruction'] = $request->instruction;
        if ($request->has('is_active')) $classExamData['is_active'] = $request->is_active;
        if ($request->has('status')) $classExamData['status'] = $request->status;

        $originalLearningId = $classExam->learning_id;

        $classExam->update($classExamData);


        $examSetting = ExamSetting::where('exam_id', $classExam->id)->first();
        if ($examSetting) {
            $examSettingData = [];
            if ($request->has('start_time')) $examSettingData['start_time'] = $request->start_time;
            if ($request->has('end_time')) $examSettingData['end_time'] = $request->end_time;
            if ($request->has('token')) $examSettingData['token'] = Hash::make($request->token);
            if ($request->has('duration')) $examSettingData['duration'] = $request->duration;
            if ($request->has('repeat_chance')) $examSettingData['repeat_chance'] = $request->repeat_chance;
            if ($request->has('device')) $examSettingData['device'] = $request->device;
            if ($request->has('maximum_user')) $examSettingData['maximum_user'] = $request->maximum_user;
            if ($request->has('is_random_question')) $examSettingData['is_random_question'] = $request->is_random_question;
            if ($request->has('is_random_answer')) $examSettingData['is_random_answer'] = $request->is_random_answer;
            if ($request->has('is_show_score')) $examSettingData['is_show_score'] = $request->is_show_score;
            if ($request->has('is_show_result')) $examSettingData['is_show_result'] = $request->is_show_result;

            $examSetting->update($examSettingData);
        }

        $response = $classExam->toArray();
        $response['exam_setting'] = $examSetting;

        return $this->sendResponse($response, 'Berhasil mengupdate data class exam');
    }

    public function update_is_active($id, Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $learningIds = Learning::where('teacher_id', $userLogin->is_teacher->id)->pluck('id');
        $classExam = ClassExam::whereIn('learning_id', $learningIds)->find($id);

        if (!$classExam) {
            return $this->sendError('Tidak ada data class exam.', null, 200);
        }

        $classExam->is_active = $request->is_active;

        if ($request->has('is_active') && $request->is_active == 1) {
            $classExam->status = 'Active';
            $teacherId =  $userLogin->is_teacher->id;
            $learning = Learning::find($classExam->learning_id);
            $courseId = $learning->course;
            $teacherSubclass = TeacherSubClasses::where('teacher_id', $teacherId)->where('course', $courseId)->first();
            $courseTitle = Course::find($courseId)->courses_title;
            $this->notifyTeacher($teacherId, 'ulangan', $courseTitle);
            if ($teacherSubclass) {
                $this->notifyStudents($courseId, $teacherSubclass->sub_class_id, 'ulangan', $courseTitle);
            }
        } else {
            $classExam->status = 'Inactive';
        }

        $classExam->save();

        return $this->sendResponse($classExam, 'Berhasil mengupdate data class exam');
    }

    public function update_is_main($id, $response_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_main' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $response = Response::find($response_id);

        if (!$response) {
            return $this->sendError('Tidak ada data response.', null, 200);
        }

        $student_id = $response->student_id;
        $related_response_ids = Response::where('student_id', $student_id)
            ->pluck('id');

        $grades = Grade::whereIn('response_id', $related_response_ids)->get();
        foreach ($grades as $grade) {
            if ($grade->response_id == $response_id) {
                $grade->is_main = $request->is_main;
            } else {
                $grade->is_main = false;
            }
            $grade->save();
        }

        return $this->sendResponse($grades, 'Berhasil mengupdate data grade');
    }


    public function destroy($id)
    {
        $userLogin = auth()->user();

        $learningIds = Learning::where('teacher_id', $userLogin->is_teacher->id)->pluck('id');

        $classExam = ClassExam::whereIn('learning_id', $learningIds)->find($id);

        if (!$classExam) {
            return $this->sendError('Tidak ada data class exam.', null, 200);
        }

        $examSetting = ExamSetting::where('exam_id', $classExam->id)->first();
        if ($examSetting) {
            $examSetting->delete();
        }

        $classExam->delete();
        return $this->sendResponse($classExam, 'Berhasil menghapus data class exam');
    }
}