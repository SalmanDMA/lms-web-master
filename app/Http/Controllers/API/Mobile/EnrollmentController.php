<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Course;
use App\Models\CourseTeacher;
use App\Models\Enrollment;
use App\Models\Learning;
use App\Models\Student;
use App\Models\SubClasses;
use App\Models\TeacherSubClasses;
use App\Models\User;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends Controller
{
    use CommonTrait;

    public function getAllSubClass()
    {
        $userLogin = auth()->user();

        if ($userLogin->role == 'TEACHER') {
            $subClasses = TeacherSubClasses::where('teacher_id', $userLogin->is_teacher->id)->get();
        } else {
            $subClasses = TeacherSubClasses::all();
        }

        return $this->sendResponse($subClasses, 'Data sub class berhasil diambil');
    }

    public function getAllStudents()
    {
        $userLogin = auth()->user();


        if ($userLogin->role == 'TEACHER') {
            $courseTeacher = CourseTeacher::where('teacher_id', $userLogin->is_teacher->id)
                ->pluck('course_id');
            $enrollments = Enrollment::whereIn('course_id', $courseTeacher)->get();
        } else {
            $enrollments = Enrollment::all();
        }

        // Ambil data student
        $studentIds = $enrollments->pluck('student_id')->unique();
        $students = Student::whereIn('id', $studentIds)->get()->keyBy('id');

        // Ambil data users
        $userIds = $students->pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $result = $enrollments->map(function ($enrollment) use ($students, $users) {
            $studentId = $enrollment->student_id;
            $student = $students->get($studentId);
            $user = $student ? $users->get($student->user_id) : null;

            return [
                'id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'enrollment_date' => $enrollment->enrollment_date,
                'student' => [
                    'id' => $enrollment->student_id,
                    'fullname' => $user ? $user->fullname : 'Unknown',
                    'nisn' => $student ? $student->nisn : 'Unknown',
                ],
            ];
        });

        return $this->sendResponse($result, 'Data enrollment berhasil diambil');
    }

    public function assignSubClass(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'course' => 'required|string|exists:courses,id',
            'sub_class_id' => 'required|string|exists:sub_class,id',
            'learning_id' => 'nullable|string|exists:learnings,id',
        ], [
            'course.required' => 'Ups! Lengkapi formulir di bawah ini',
            'sub_class_id.required' => 'Ups! Lengkapi formulir di bawah ini',
            'course.exists' => 'Ups, Course Tidak Ditemukan',
            'sub_class_id.exists' => 'Ups, Sub Class Tidak Ditemukan',
        ]);

        if ($userLogin->role != 'TEACHER' && !$request->has('teacher_id')) {
            return $this->sendError('Ups, Anda Belum Memasukkan ID Guru', null, 200);
        }

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $subClass = SubClasses::find($request->sub_class_id);
        if (!$subClass) {
            return $this->sendError('Ups, Sub Class Tidak Ditemukan', null, 200);
        }

        $existingTeacherCourse = TeacherSubClasses::where('sub_class_id', $request->sub_class_id)
            ->where('course', $request->course)
            ->exists();

        if ($existingTeacherCourse) {
            return $this->sendError('Ups, Sub Class sudah diisi oleh guru dengan course yang sama', null, 200);
        }

        $course = Course::find($request->course);
        if (!$course) {
            return $this->sendError('Ups, Course Tidak Ditemukan', null, 200);
        }

        $teacherId = null;
        if ($userLogin->role == 'TEACHER') {
            $teacherId = $userLogin->is_teacher->id;
        } else {
            $teacherId = $request->input('teacher_id');
        }

        $generatedTeacherSubClassId = IdGenerator::generate(['table' => 'teacher_sub_class', 'length' => 16, 'prefix' => 'TSC-']);
        TeacherSubClasses::create([
            'id' => $generatedTeacherSubClassId,
            'teacher_id' => $teacherId,
            'sub_class_id' => $request->sub_class_id,
            'course' => $request->course,
            'learning_id' => $request->learning_id ?? null
        ]);


        $generatedCourseTeacherId = IdGenerator::generate(['table' => 'course_teacher', 'length' => 16, 'prefix' => 'CT-']);

        $existingCourseTeacher = CourseTeacher::where('course_id', $request->course)
            ->where('teacher_id', $teacherId)
            ->exists();

        if (!$existingCourseTeacher) {
            CourseTeacher::create([
                'id' => $generatedCourseTeacherId,
                'teacher_id' => $teacherId,
                'course_id' => $request->course,
                'status' => 'Active'
            ]);
        }


        return $this->sendResponse(null, 'Berhasil menambahkan sub class');
    }

    public function updateSubClass(Request $request, $id)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'course' => 'required|string|exists:courses,id',
            'sub_class_id' => 'required|string|exists:sub_class,id',
            'teacher_id' => 'nullable|string|exists:teachers,id',
            'learning_id' => 'nullable|string|exists:learnings,id',
        ], [
            'course.required' => 'Ups! Lengkapi formulir di bawah ini',
            'sub_class_id.required' => 'Ups! Lengkapi formulir di bawah ini',
            'course.exists' => 'Ups, Course Tidak Ditemukan',
            'sub_class_id.exists' => 'Ups, Sub Class Tidak Ditemukan',
        ]);

        if ($userLogin->role != 'TEACHER' && !$request->has('teacher_id')) {
            return $this->sendError('Ups, Anda Belum Memasukkan ID Guru', null, 200);
        }

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $teacherSubClass = TeacherSubClasses::find($id);
        if (!$teacherSubClass) {
            return $this->sendError('Ups, Sub Class Tidak Ditemukan', null, 200);
        }

        $learningTeacher = Learning::find($request->learning_id ?? $teacherSubClass->learning_id);
        if (!$learningTeacher) {
            return $this->sendError('Ups, Learning Tidak Ditemukan', null, 200);
        }

        $existingTeacherCourse = TeacherSubClasses::where('sub_class_id', $request->sub_class_id)
            ->where('course', $request->course)
            ->where('id', '!=', $id)
            ->exists();

        if ($existingTeacherCourse) {
            return $this->sendError('Ups, Sub Class sudah diisi oleh guru dengan course yang sama', null, 200);
        }

        $teacherId = $userLogin->role == 'TEACHER'
            ? $userLogin->is_teacher->id
            : $request->input('teacher_id') ?? $teacherSubClass->teacher_id;

        $teacherSubClass->update([
            'teacher_id' => $teacherId,
            'sub_class_id' => $request->sub_class_id,
            'course' => $request->course,
            'learning_id' => $request->learning_id ?? $teacherSubClass->learning_id
        ]);

        $learningTeacher->update([
            'teacher_id' => $teacherId,
            'course' => $request->course
        ]);

        $existingCourseTeacher = CourseTeacher::where('course_id', $request->course)
            ->where('teacher_id', $teacherId)
            ->exists();

        if (!$existingCourseTeacher) {
            CourseTeacher::create([
                'id' => IdGenerator::generate(['table' => 'course_teacher', 'length' => 16, 'prefix' => 'CT-']),
                'teacher_id' => $teacherId,
                'course_id' => $request->course,
                'status' => 'Active',
            ]);
        }

        return $this->sendResponse(null, 'Berhasil memperbarui sub class');
    }

    public function deleteSubClass($id)
    {
        $teacherSubClass = TeacherSubClasses::find($id);

        if (!$teacherSubClass) {
            return $this->sendError('Ups, Sub Class Tidak Ditemukan', null, 200);
        }

        $learningTeacher = Learning::find($teacherSubClass->learning_id);
        if ($learningTeacher) {
            $learningTeacher->delete();
        }

        $otherTeacherSubClasses = TeacherSubClasses::where('course', $teacherSubClass->course)
            ->where('id', '!=', $teacherSubClass->id)
            ->where('teacher_id', $teacherSubClass->teacher_id)
            ->exists();

        $otherLearnings = Learning::where('course', $teacherSubClass->course)->where('teacher_id', $teacherSubClass->teacher_id)
            ->exists();

        if (!$otherTeacherSubClasses && !$otherLearnings) {
            $courseTeacher = CourseTeacher::where('teacher_id', $teacherSubClass->teacher_id)
                ->where('course_id', $teacherSubClass->course)
                ->first();

            if ($courseTeacher) {
                $courseTeacher->delete();
            }
        }

        $teacherSubClass->delete();

        return $this->sendResponse(null, 'Berhasil menghapus sub class');
    }



    public function assignStudents(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'course' => 'required|string|exists:courses,id',
            'sub_class_id' => 'required|string|exists:sub_class,id',
            'students' => 'required|array',
            'students.*.id' => 'required|string|exists:students,id',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'students.required' => 'Ups! Lengkapi formulir di bawah ini',
            'students.*.id.required' => 'Ups! Lengkapi formulir di bawah ini',
            'students.*.id.exists' => 'Ups, Siswa Tidak Ditemukan',
            'teacher_id.required_if' => 'Ups! Lengkapi formulir di bawah ini',
            'teacher_id.exists' => 'Ups, Guru Tidak Ditemukan',
            'course.exists' => 'Ups, Course Tidak Ditemukan',
            'sub_class_id.exists' => 'Ups, Sub Class Tidak Ditemukan',
        ]);

        if ($userLogin->role != 'TEACHER' && !$request->has('teacher_id')) {
            return $this->sendError('Ups, Anda Belum Memasukkan ID Guru', null, 200);
        }

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $teacherId = $userLogin->role == 'TEACHER' ? $userLogin->is_teacher->id : $request->input('teacher_id');

        $subClassId = TeacherSubClasses::where('teacher_id', $teacherId)
            ->where('course', $request->course)
            ->where('sub_class_id', $request->sub_class_id)
            ->first()->sub_class_id;

        foreach ($request->students as $studentData) {
            $studentId = $studentData['id'];
            $student = Student::find($studentId);

            if (!$student || $student->sub_class_id != $subClassId) {
                return $this->sendError('Ups, siswa tidak termasuk dalam sub class yang sesuai', null, 200);
            }

            $enrollment = Enrollment::withTrashed()
                ->where('course_id', $request->course)
                ->where('student_id', $studentId)
                ->first();

            if ($enrollment) {
                $enrollment->restore();
                $enrollment->enrollment_date = Carbon::now('Asia/Jakarta');
                $enrollment->save();
            } else {
                $id = IdGenerator::generate(['table' => 'enrollment', 'length' => 16, 'prefix' => 'ENR-']);
                Enrollment::create([
                    'id' => $id,
                    'course_id' => $request->course,
                    'student_id' => $studentId,
                    'enrollment_date' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        }

        return $this->sendResponse(null, 'Berhasil menambahkan siswa ke enrollment');
    }


    public function updateStudents(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'course' => 'required|string|exists:courses,id',
            'sub_class_id' => 'required|string|exists:sub_class,id',
            'students' => 'required|array',
            'students.*.id' => 'required|string|exists:students,id',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'students.required' => 'Ups! Lengkapi formulir di bawah ini',
            'students.*.id.required' => 'Ups! Lengkapi formulir di bawah ini',
            'students.*.id.exists' => 'Ups, Siswa Tidak Ditemukan',
            'teacher_id.required_if' => 'Ups! Lengkapi formulir di bawah ini',
            'teacher_id.exists' => 'Ups, Guru Tidak Ditemukan',
            'course.exists' => 'Ups, Course Tidak Ditemukan',
            'sub_class_id.exists' => 'Ups, Sub Class Tidak Ditemukan',
        ]);

        if ($userLogin->role != 'TEACHER' && !$request->has('teacher_id')) {
            return $this->sendError('Ups, Anda Belum Memasukkan ID Guru', null, 403);
        }

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $teacherId = null;
        if ($userLogin->role == 'TEACHER') {
            $teacherId = $userLogin->is_teacher->id;
        } else {
            $teacherId = $request->input('teacher_id');
        }

        $subClassId = TeacherSubClasses::where('teacher_id', $teacherId)
            ->where('course', $request->course)
            ->where('sub_class_id', $request->sub_class_id)
            ->first()->sub_class_id;

        foreach ($request->students as $studentData) {
            $studentId = $studentData['id'];
            $student = Student::find($studentId);
            if (!$student || $student->sub_class_id != $subClassId) {
                return $this->sendError('Ups, siswa tidak termasuk dalam sub class yang sesuai', null, 400);
            }

            $enrollment = Enrollment::where('course_id', $request->course)
                ->where('student_id', $studentId)
                ->first();

            if ($enrollment) {
                $enrollment->delete();
            } else {
                return $this->sendError('Ups, Enrollment tidak ditemukan untuk siswa ini', null, 404);
            }
        }
        return $this->sendResponse(null, 'Berhasil menghapus enrollment untuk siswa yang dipilih');
    }
}