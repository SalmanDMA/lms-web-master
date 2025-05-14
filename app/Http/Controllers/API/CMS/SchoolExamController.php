<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\NotifiableTrait;
use App\Models\Course;
use App\Models\EnrollmentExam;
use App\Models\ExamSection;
use App\Models\ExamSetting;
use App\Models\ExamTeacher;
use App\Models\Grade;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Response;
use App\Models\School;
use App\Models\SchoolExam;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherSubClasses;
use App\Models\User;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SchoolExamController extends Controller
{

    use CommonTrait, NotifiableTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $limit = $request->query('limit');

        if ($userLogin->role == 'STAFF') {
            $staffCurriculum = Staff::where('user_id', $userLogin->id)->first();

            if ($staffCurriculum->authority != "KURIKULUM") {
                return $this->sendError('Anda tidak memiliki akses.', null, 200);
            }

            if (!$this->checkPremiumStatus($userLogin->school_id)) {
                return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
            }

            $schoolExams = SchoolExam::where('school_id', $userLogin->school_id)->get();
            if ($schoolExams->isEmpty()) {
                return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
            }

            // dd($schoolExams);

            $examSettings = ExamSetting::whereIn('school_exam_id', $schoolExams->pluck('id'))->get();

            foreach ($schoolExams as $schoolExam) {
                $schoolExam->examSetting = $examSettings->where('school_exam_id', $schoolExam->id)->first();
            }

            $schoolExams->load(['school', 'course', 'exam_sections', 'exam_settings', 'exam_setting', 'questions', 'responses', 'students']);

            return $this->sendResponse($schoolExams, 'Daftar ujian sekolah ditemukan.');
        }

        if ($userLogin->role == 'STUDENT') {
            $student = Student::where('user_id', $userLogin->id)->first();

            if (!$this->checkPremiumStatus($userLogin->school_id)) {
                return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
            }

            $enrollmentExams = null;

            if ($limit) {
                $enrollmentExams = EnrollmentExam::where('student_id', $student->id)->orderBy('created_at', 'desc')->limit($limit)->get();
            } else {
                $enrollmentExams = EnrollmentExam::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
            }

            if ($enrollmentExams->isEmpty()) {
                return $this->sendError('Enrollment School Exam Tidak Ditemukan.', null, 200);
            }

            $examSettings = ExamSetting::whereIn('school_exam_id', $enrollmentExams->pluck('exam_id'))->get();
            $schoolExams = SchoolExam::with(['school', 'course', 'exam_sections', 'exam_settings', 'exam_setting', 'questions', 'responses', 'students'])->whereIn('id', $enrollmentExams->pluck('exam_id'))->get();

            foreach ($enrollmentExams as $enrollmentExam) {
                $enrollmentExam->examSetting = $examSettings->where('school_exam_id', $enrollmentExam->exam_id)->first();
                $enrollmentExam->exam_id = $schoolExams->where('id', $enrollmentExam->exam_id)->first();
            }

            return $this->sendResponse($enrollmentExams, 'Enrollment School Exam Ditemukan.');
        }

        if ($userLogin->role == 'TEACHER') {
            $teacherExamIds = ExamTeacher::where('teacher_id', $userLogin->is_teacher->id)->pluck('exam_id');
            $schoolExams = SchoolExam::with(['school', 'course', 'exam_sections', 'exam_settings', 'exam_setting', 'questions', 'responses', 'students'])->whereIn('id', $teacherExamIds)->get();

            if ($schoolExams->isEmpty()) {
                return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
            }

            $examSettings = ExamSetting::whereIn('school_exam_id', $schoolExams->pluck('id'))->get();

            foreach ($schoolExams as $schoolExam) {
                $schoolExam->examSetting = $examSettings->where('school_exam_id', $schoolExam->id)->first();
            }

            $examTeachers = ExamTeacher::whereIn('exam_id', $schoolExams->pluck('id'))
                ->where('teacher_id', $userLogin->is_teacher->id)
                ->get();

            foreach ($schoolExams as $schoolExam) {
                $schoolExam->teacher_exam = $examTeachers->where('exam_id', $schoolExam->id)->first();
            }

            return $this->sendResponse($schoolExams, 'Daftar ujian sekolah ditemukan.');
        }

        return $this->sendError('Role tidak dikenal.', null, 200);
    }


    public function show($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role == 'STAFF') {
            $staffCurriculum = Staff::where('user_id', $userLogin->id)->first();

            if ($staffCurriculum->authority != "KURIKULUM") {
                return $this->sendError('Anda tidak memiliki akses.', null, 200);
            }

            if (!$this->checkPremiumStatus($userLogin->school_id)) {
                return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
            }

            $schoolExam = SchoolExam::with(['school', 'course', 'exam_sections', 'exam_settings', 'exam_setting', 'questions', 'responses', 'students'])->where('school_id', $userLogin->school_id)->find($id);

            if (!$schoolExam) {
                return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
            }

            $allQuestionsBySchoolExam = Question::where('school_exam_id', $schoolExam->id)->get();
            $totalPoints = $allQuestionsBySchoolExam->sum('point') ?? 0;

            $schoolExam->examSetting = ExamSetting::where('school_exam_id', $schoolExam->id)->first();
            $schoolExam->total_point_question = $totalPoints;

            // dd($schoolExam);

            return $this->sendResponse($schoolExam, 'Ujian sekolah ditemukan.');
        }

        if ($userLogin->role == 'STUDENT') {
            $enrollmentExam = EnrollmentExam::where('student_id', $userLogin->is_student->id)->where('id', $id)->first();

            if (!$enrollmentExam) {
                return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
            }

            if (!$this->checkPremiumStatus($userLogin->school_id)) {
                return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
            }

            $enrollmentExam->examSetting = ExamSetting::where('school_exam_id', $enrollmentExam->exam_id)->first();

            $questions = Question::where('school_exam_id', $enrollmentExam->exam_id)->get();
            $total_points = $questions->sum('point') ?? 0;

            $examSections = ExamSection::where('exam_id', $enrollmentExam->exam_id)->get();

            $examSectionData = $examSections->groupBy('name')->map(function ($section) {
                return [
                    'name' => $section->first()->name,
                    'description' => $section->first()->description,
                    'Total' => $section->count()
                ];
            })->values();

            $questionCount = [
                'Essay' => $questions->where('question_type', 'Essay')->count(),
                'Pilihan Ganda' => $questions->where('question_type', 'Pilihan Ganda')->count(),
                'Pilihan Ganda Complex' => $questions->where('question_type', 'Pilihan Ganda Complex')->count(),
                'True False' => $questions->where('question_type', 'True False')->count(),
                'Total' => $questions->count(),
            ];

            $enrollmentExam->questionCount = $questionCount;
            $enrollmentExam->examSection = $examSectionData;
            $enrollmentExam->total_point_question = $total_points;
            $enrollmentExam->exam_id = SchoolExam::where('id', $enrollmentExam->exam_id)->first();

            return $this->sendResponse($enrollmentExam, 'Ujian sekolah ditemukan.');
        }

        if ($userLogin->role == 'TEACHER') {
            $teacherExamIds = ExamTeacher::where('teacher_id', $userLogin->is_teacher->id)->pluck('exam_id');

            $schoolExam = SchoolExam::with(['school', 'course', 'exam_sections', 'exam_settings', 'exam_setting', 'questions', 'responses', 'students'])->whereIn('id', $teacherExamIds)->find($id);

            if (!$schoolExam) {
                return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
            }

            $allQuestionsBySchoolExam = Question::where('school_exam_id', $schoolExam->id)->get();
            $totalPoints = $allQuestionsBySchoolExam->sum('point') ?? 0;

            $schoolExam->examSetting = ExamSetting::where('school_exam_id', $schoolExam->id)->first();
            $schoolExam->total_point_question = $totalPoints;

            $schoolExam->teacher_exam = ExamTeacher::where('exam_id', $schoolExam->id)
                ->where('teacher_id', $userLogin->is_teacher->id)
                ->first();

            return $this->sendResponse($schoolExam, 'Ujian sekolah ditemukan.');
        }

        return $this->sendError('Role tidak dikenal.', null, 200);
    }

    public function store(Request $request)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF' && $userlogin->role != 'TEACHER') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        if ($userlogin->role == 'STAFF') {
            $staffCuriculum = Staff::where('user_id', $userlogin->id)->first();

            if ($staffCuriculum->authority != "KURIKULUM") {
                return $this->sendError('Anda tidak memiliki akses.', null, 200);
            }

            if (!$this->checkPremiumStatus($userlogin->school_id)) {
                return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
            }
        }

        if ($userlogin->role == 'TEACHER') {
            $teacherPengelola = ExamTeacher::where('exam_id', $request->exam_id)->where('role', 'PENGELOLA')->first();

            if (!$teacherPengelola) {
                return $this->sendError('Anda tidak memiliki akses.', null, 200);
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|string',
            'instruction' => 'required|string',
            'course' => 'required|string',
            'status' => 'required|string',
            'publication_status' => 'required|string',
            'class_level' => 'required|string',
            'academic_year' => 'required|string',
            'semester' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'token' => 'required|string',
            'duration' => 'required|date_format:H:i',
            'repeat_chance' => 'required|integer',
            'device' => 'nullable|in:Web,Mobile,All',
            'maximum_user' => 'required|integer',
            'is_random_question' => 'boolean',
            'is_random_answer' => 'boolean',
            'is_show_score' => 'boolean',
            'is_show_result' => 'boolean',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form.',
            'device.in' => 'Ups, Tolong pilih Web atau Mobile.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $generateIdSchoolExam = IdGenerator::generate(['table' => 'school_exams', 'length' => 16, 'prefix' => 'SCX-']);

        $schoolExam = SchoolExam::create([
            'id' => $generateIdSchoolExam,
            'school_id' => $userlogin->school_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'instruction' => $request->instruction,
            'course' => $request->course,
            'status' => $request->status,
            'publication_status' => $request->publication_status,
            'class_level' => $request->class_level,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        $generateClassExamSettingId = IdGenerator::generate(['table' => 'exam_settings', 'length' => 16, 'prefix' => 'EXS-']);

        $examSetting = ExamSetting::create([
            'id' => $generateClassExamSettingId,
            'exam_id' => null,
            'school_exam_id' => $schoolExam->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $request->duration,
            'token' => Hash::make($request->token),
            'repeat_chance' => $request->repeat_chance,
            'device' => $request->device,
            'maximum_user' => $request->maximum_user,
            'is_random_question' => $request->is_random_question,
            'is_random_answer' => $request->is_random_answer,
            'is_show_score' => $request->is_show_score,
            'is_show_result' => $request->is_show_result,
        ]);

        $schoolExam['exam_setting'] = $examSetting;

        return $this->sendResponse($schoolExam, 'School Exam Created');
    }

    public function update(Request $request, $id)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF' && $userlogin->role != 'TEACHER') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        if ($userlogin->role == 'STAFF') {
            $staffCuriculum = Staff::where('user_id', $userlogin->id)->first();

            if ($staffCuriculum->authority != "KURIKULUM") {
                return $this->sendError('Anda tidak memiliki akses.', null, 200);
            }

            if (!$this->checkPremiumStatus($userlogin->school_id)) {
                return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
            }
        }

        if ($userlogin->role == 'TEACHER') {
            $teacherPengelola = ExamTeacher::where('exam_id', $request->exam_id ?? $id)->where('role', 'PENGELOLA')->first();

            if (!$teacherPengelola) {
                return $this->sendError('Anda tidak memiliki akses.', null, 200);
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'instruction' => 'sometimes|required|string',
            'course' => 'sometimes|required|string',
            'status' => 'sometimes|required|string',
            'publication_status' => 'sometimes|required|string',
            'class_level' => 'sometimes|required|string',
            'academic_year' => 'sometimes|required|string',
            'semester' => 'sometimes|required|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date',
            'token' => 'nullable|string',
            'duration' => 'sometimes|required|date_format:H:i',
            'repeat_chance' => 'sometimes|required|integer',
            'device' => 'nullable|in:Web,Mobile,All',
            'maximum_user' => 'sometimes|required|integer',
            'is_random_question' => 'boolean',
            'is_random_answer' => 'boolean',
            'is_show_score' => 'boolean',
            'is_show_result' => 'boolean',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form.',
            'device.in' => 'Ups, Tolong pilih Web atau Mobile.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $schoolExam = SchoolExam::where('school_id', $userlogin->school_id)->find($id);

        if (!$schoolExam) {
            return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
        }

        $schoolExam->fill($request->only([
            'title',
            'description',
            'type',
            'instruction',
            'course',
            'status',
            'publication_status',
            'class_level',
            'academic_year',
            'semester'
        ]));
        $schoolExam->save();

        $examSetting = ExamSetting::where('school_exam_id', $schoolExam->id)->first();

        $tokenHash = Hash::make($request->token);

        if ($examSetting) {
            $examSetting->fill($request->only([
                'start_time',
                'end_time',
                'token',
                'duration',
                'repeat_chance',
                'device',
                'maximum_user',
                'is_random_question',
                'is_random_answer',
                'is_show_score',
                'is_show_result'
            ]));
            $examSetting->token = $tokenHash;
            $examSetting->save();
        }

        $schoolExam->examSetting = $examSetting;

        return $this->sendResponse($schoolExam, 'Ujian sekolah diperbarui.');
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

        if ($userLogin->role == 'STAFF') {
            $staffCurriculum = Staff::where('id', $userLogin->is_staff->id)->first();

            if ($staffCurriculum->authority != 'KURIKULUM') {
                return $this->sendError('Anda tidak memiliki akses.', null, 200);
            }

            if (!$this->checkPremiumStatus($userLogin->school_id)) {
                return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
            }

            $schoolExam = SchoolExam::where('school_id', $userLogin->school_id)->find($id);

            if (!$schoolExam) {
                return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
            }

            $teacherIds = ExamTeacher::where('exam_id', $id)->pluck('teacher_id');

            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::find($teacherId);
                if ($teacher) {
                    $courseTitle = Course::find($schoolExam->course)->courses_title;
                    $this->notifyTeacher($teacherId, 'ujian', $courseTitle);
                }
            }

            $teacherSubclass = TeacherSubClasses::where('teacher_id', $teacherIds->first())
                ->where('course', $schoolExam->course)
                ->first();

            if ($teacherSubclass) {
                $this->notifyStudents($schoolExam->course, $teacherSubclass->sub_class_id, 'ujian', $courseTitle);
            }
        }

        if ($userLogin->role == 'TEACHER') {
            $teacherExamIds = ExamTeacher::where('teacher_id', $userLogin->is_teacher->id)->pluck('exam_id');
            $schoolExam = SchoolExam::whereIn('id', $teacherExamIds)->find($id);

            if (!$schoolExam) {
                return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
            }

            $courseTitle = Course::find($schoolExam->course)->courses_title;
            $this->notifyTeacher($userLogin->is_teacher->id, 'ujian', $courseTitle);

            $teacherSubclass = TeacherSubClasses::where('teacher_id', $userLogin->is_teacher->id)
                ->where('course', $schoolExam->course)
                ->first();

            if ($teacherSubclass) {
                $this->notifyStudents($schoolExam->course, $teacherSubclass->sub_class_id, 'ujian', $courseTitle);
            }
        }

        $exam = SchoolExam::find($id);
        if (!$exam) {
            return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
        }

        if ($request->has('is_active') && $request->is_active == 1) {
            $exam->status = 'ACTIVE';
        } else {
            $exam->status = 'INACTIVE';
        }

        $exam->save();

        return $this->sendResponse($exam, 'Ujian sekolah diperbarui.');
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
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $staffCuriculum = Staff::where('user_id', $userlogin->id)->first();

        if ($staffCuriculum->authority != "KURIKULUM") {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        if (!$this->checkPremiumStatus($userlogin->school_id)) {
            return $this->sendError('Sekolah Anda tidak memiliki akses premium atau telah kedaluwarsa.', null, 200);
        }

        $schoolExam = SchoolExam::where('school_id', $userlogin->school_id)->find($id);

        if (!$schoolExam) {
            return $this->sendError('Ujian sekolah tidak ditemukan.', null, 200);
        }

        $schoolExam->delete();

        ExamSetting::where('school_exam_id', $schoolExam->id)->delete();

        return $this->sendResponse(null, 'Ujian sekolah dihapus.');
    }

    public function registerTeacherToExam(Request $request)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $staffCuriculum = Staff::where('user_id', $userlogin->id)->first();

        if ($staffCuriculum->authority != "KURIKULUM") {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:school_exams,id',
            'teacher_id' => 'required|exists:teachers,id',
            'role' => 'required|in:PENGELOLA,PENILAI',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $generateIdExamTeacher = IdGenerator::generate(['table' => 'exam_teachers', 'length' => 16, 'prefix' => 'ET-']);

        $examTeacher = ExamTeacher::updateOrCreate(
            ['exam_id' => $request->exam_id, 'teacher_id' => $request->teacher_id],
            ['role' => $request->role, 'id' => $generateIdExamTeacher]
        );

        $schoolExam = SchoolExam::where('id', $request->exam_id)->first();
        $course = Course::where('id', $schoolExam->course)->first();
        $courseTitle = $course->courses_title;

        // Notification
        $generateIdNotification = IdGenerator::generate(['table' => 'notifications', 'length' => 16, 'prefix' => 'NOT-']);
        Notification::create([
            'id' => $generateIdNotification,
            'teacher_id' => $request->teacher_id,
            'type' => 'ujian sekolah',
            'title' => 'Ujian sekolah ' . $courseTitle,
            'message' => 'Anda diterima sebagai ' . strtolower($request->role) . ' untuk ujian sekolah "' . $courseTitle . '".',
            'is_read' => false,
        ]);

        return $this->sendResponse($examTeacher, 'Pengajar ditambahkan.');
    }

    public function registerStudentsToExam(Request $request)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $staffCurriculum = Staff::where('user_id', $userlogin->id)->first();

        if ($staffCurriculum->authority != "KURIKULUM") {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:school_exams,id',
            'students' => 'required|array',
            'students.*.id' => 'required|exists:students,id',
            'students.*.do_exam' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $schoolExam = SchoolExam::where('id', $request->exam_id)->first();
        $course = Course::where('id', $schoolExam->course)->first();
        $courseTitle = $course->courses_title;

        $registeredStudents = [];

        foreach ($request->students as $studentData) {
            $studentId = $studentData['id'];
            $doExam = $studentData['do_exam'] ?? 0;

            $enrollmentExam = EnrollmentExam::where('exam_id', $request->exam_id)
                ->where('student_id', $studentId)
                ->first();

            if ($enrollmentExam) {
                $enrollmentExam->update([
                    'student_id' => $studentId,
                    'exam_id' => $request->exam_id,
                    'do_exam' => $doExam
                ]);
            } else {
                $generateIdEnrollmentExam = IdGenerator::generate(['table' => 'enrollment_exams', 'length' => 16, 'prefix' => 'EE-']);

                $enrollmentExam = EnrollmentExam::create([
                    'id' => $generateIdEnrollmentExam,
                    'student_id' => $studentId,
                    'exam_id' => $request->exam_id,
                    'do_exam' => $doExam,
                ]);
            }

            $registeredStudents[] = [
                'student_id' => $studentId,
                'do_exam' => $doExam,
            ];

            // Notification for each student
            $generateIdNotification = IdGenerator::generate(['table' => 'notifications', 'length' => 16, 'prefix' => 'NOT-']);
            Notification::create([
                'id' => $generateIdNotification,
                'student_id' => $studentId,
                'type' => 'Ujian Sekolah',
                'title' =>  'Ujian sekolah ' . $courseTitle,
                'message' => 'Anda telah didaftarkan untuk ujian sekolah "' . $courseTitle . '".',
                'is_read' => false,
            ]);
        }

        return $this->sendResponse($registeredStudents, 'Siswa berhasil didaftarkan ke ujian.');
    }

    public function unregisterTeacherFromExam(Request $request)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $staffCuriculum = Staff::where('user_id', $userlogin->id)->first();

        if ($staffCuriculum->authority != "KURIKULUM") {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:school_exams,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $examTeacher = ExamTeacher::where('exam_id', $request->exam_id)
            ->where('teacher_id', $request->teacher_id)
            ->first();

        if (!$examTeacher) {
            return $this->sendError('Pengajar tidak ditemukan dalam ujian ini.', null, 404);
        }

        $examTeacher->delete();

        $schoolExam = SchoolExam::where('id', $request->exam_id)->first();
        $course = Course::where('id', $schoolExam->course)->first();
        $courseTitle = $course->courses_title;

        $generateIdNotification = IdGenerator::generate(['table' => 'notifications', 'length' => 16, 'prefix' => 'NOT-']);
        Notification::create([
            'id' => $generateIdNotification,
            'teacher_id' => $request->teacher_id,
            'type' => 'ujian sekolah',
            'title' => 'Ujian sekolah ' . $courseTitle,
            'message' => 'Anda telah dicabut dari tugas ' . strtolower($examTeacher->role) . ' untuk ujian sekolah "' . $courseTitle . '".',
            'is_read' => false,
        ]);

        return $this->sendResponse(null, 'Pengajar berhasil dihapus dari ujian.');
    }

    public function unregisterStudentsFromExam(Request $request)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $staffCurriculum = Staff::where('user_id', $userlogin->id)->first();

        if ($staffCurriculum->authority != "KURIKULUM") {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:school_exams,id',
            'students' => 'required|array',
            'students.*.id' => 'required|exists:students,id',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $schoolExam = SchoolExam::where('id', $request->exam_id)->first();
        $course = Course::where('id', $schoolExam->course)->first();
        $courseTitle = $course->courses_title;

        $unregisteredStudents = [];

        foreach ($request->students as $studentData) {
            $studentId = $studentData['id'];

            $enrollmentExam = EnrollmentExam::where('exam_id', $request->exam_id)
                ->where('student_id', $studentId)
                ->first();

            if (!$enrollmentExam) {
                return $this->sendError('Siswa tidak terdaftar dalam ujian ini.', null, 404);
            }

            $enrollmentExam->delete();

            $unregisteredStudents[] = [
                'student_id' => $studentId,
            ];

            // Notification for each student
            $generateIdNotification = IdGenerator::generate(['table' => 'notifications', 'length' => 16, 'prefix' => 'NOT-']);
            Notification::create([
                'id' => $generateIdNotification,
                'student_id' => $studentId,
                'type' => 'Ujian Sekolah',
                'title' => 'Ujian sekolah ' . $courseTitle,
                'message' => 'Pendaftaran Anda untuk ujian sekolah "' . $courseTitle . '" telah dibatalkan.',
                'is_read' => false,
            ]);
        }

        return $this->sendResponse($unregisteredStudents, 'Siswa berhasil dihapus dari ujian.');
    }



    public function getDataRegisteredTeachers($id)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $staffCurriculum = Staff::where('user_id', $userlogin->id)->first();

        if ($staffCurriculum->authority != "KURIKULUM") {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $registeredTeachers = ExamTeacher::where('exam_id', $id)
            ->with(['exam'])
            ->get();

        $registeredTeachers = $registeredTeachers->map(function ($item) {
            $item->teacher = Teacher::where('id', $item->teacher_id)->first();
            if ($item->teacher) {
                $item->teacher = User::where('id', $item->teacher->user_id)->first();
            }
            return $item;
        });

        if ($registeredTeachers->isEmpty()) {
            return $this->sendError('Tidak ada guru yang terdaftar.', null, 200);
        }

        return $this->sendResponse($registeredTeachers, 'Guru yang terdaftar berhasil diambil.');
    }

    public function getDataRegisteredStudents($id)
    {
        $userlogin = auth()->user();

        if ($userlogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $staffCurriculum = Staff::where('user_id', $userlogin->id)->first();

        if ($staffCurriculum->authority != "KURIKULUM") {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $registeredStudents = EnrollmentExam::where('exam_id', $id)
            ->with(['exam'])
            ->get();

        $registeredStudents = $registeredStudents->map(function ($item) {
            $item->student = Student::where('id', $item->student_id)->first();
            if ($item->student) {
                $item->student = User::where('id', $item->student->user_id)->first();
            }
            return $item;
        });

        if ($registeredStudents->isEmpty()) {
            return $this->sendError('Tidak ada siswa yang terdaftar.', null, 200);
        }

        return $this->sendResponse($registeredStudents, 'Siswa yang terdaftar berhasil diambil.');
    }

    public function update_token($id, Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        if ($userLogin->role != 'STAFF') {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        $exam_setting = ExamSetting::where('school_exam_id', $id)->first();

        if (!$exam_setting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 404);
        }

        $hashedToken = Hash::make($request->token);

        $exam_setting->token = $hashedToken;
        $exam_setting->token_expiration = Carbon::now('Asia/Jakarta')->addMinutes(5);

        $exam_setting->save();

        return $this->sendResponse(null, 'Token ujian berhasil diperbarui.');
    }

    public function confirmation_token($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $exam_setting = ExamSetting::where('school_exam_id', $id)->first();

        if (!$exam_setting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 404);
        }

        if (!Hash::check($request->token, $exam_setting->token)) {
            return $this->sendError('Token ujian tidak sesuai.', null, 200);
        }

        if (Carbon::parse($exam_setting->token_expiration)->isPast()) {
            return $this->sendError('Token ujian telah kedaluwarsa.', null, 200);
        }

        return $this->sendResponse(null, 'Token ujian valid.');
    }


    private function checkPremiumStatus($schoolId)
    {
        $school = School::find($schoolId);

        if ($school && $school->is_premium) {
            if ($school->premium_expired_date && Carbon::parse($school->premium_expired_date)->isPast()) {
                return false;
            }
            return true;
        }

        return false;
    }
}
