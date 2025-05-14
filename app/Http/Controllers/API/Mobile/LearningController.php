<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Course;
use App\Models\CourseTeacher;
use App\Models\Learning;
use App\Models\SubClasses;
use App\Models\Teacher;
use App\Models\TeacherSubClasses;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LearningController extends Controller
{
    use CommonTrait;

    public function index()
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $query = Learning::where('teacher_id', $userLogin->is_teacher->id);
        } else {
            $query = Learning::query();
        }

        $data = $query->with(['course', 'teacher', 'materials', 'class_exams'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return $this->sendError('Data learning tidak ditemukan');
        }

        $data->each(function ($learning) {
            $course = Course::find($learning->course);
            $learning->course = [
                'id' => $course->id,
                'name' => $course->courses_title,
            ];
        });

        return $this->sendResponse($data, 'Berhasil mengambil semua data learning');
    }

    public function show($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $query = Learning::where('teacher_id', $userLogin->is_teacher->id);
        } else {
            $query = Learning::query();
        }

        $data = $query->with(['course', 'teacher', 'materials', 'class_exams'])->find($id);

        if (!$data) {
            return $this->sendError('Data learning tidak ditemukan');
        }

        $course = Course::find($data->course);
        $data->course = [
            'id' => $course->id,
            'name' => $course->courses_title,
        ];

        return $this->sendResponse($data, 'Berhasil mengambil data learning');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Active,Non Active',
            'course' => 'required|exists:courses,id',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
            'course.required' => 'Ups, Anda Belum Melengkapi Form',
            'course.exists' => 'Ups, Id course tidak ditemukan',
            'teacher_id.required_if' => 'Ups, Anda Belum Memasukkan ID Guru',
            'teacher_id.exists' => 'Ups, ID Guru Tidak Ditemukan',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $teacherId = $userLogin->role === 'TEACHER' ? $userLogin->is_teacher->id : $request->teacher_id;

        $generateId = IdGenerator::generate(['table' => 'learnings', 'length' => 16, 'prefix' => 'LEA-']);
        $request->merge(['id' => $generateId, 'teacher_id' => $teacherId]);
        $learning = Learning::create($request->all());
        return $this->sendResponse($learning, 'Berhasil menambahkan data learning', 201);
    }


    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Active,Non Active',
            'course' => 'required|exists:courses,id',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
            'course.required' => 'Ups, Anda Belum Melengkapi Form',
            'course.exists' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        if ($userLogin->role === 'TEACHER') {
            $teacherId = $userLogin->is_teacher->id;
            $learning = Learning::where('teacher_id', $teacherId)->find($id);
        } else {
            $teacherId = $request->teacher_id;
            $learning = Learning::find($id);
        }

        if (!$learning) {
            return $this->sendError('Data learning tidak ditemukan');
        }

        $existingTeacherCourse = TeacherSubClasses::where('course', $request->course)
            ->get();

        if ($existingTeacherCourse) {
            return $this->sendError('Ups, Learning sudah ada pada course ini', null, 200);
        }

        $learning->update($request->all());

        return $this->sendResponse($learning, 'Berhasil memperbarui data learning');
    }

    public function destroy($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $learning = Learning::where('teacher_id', $userLogin->is_teacher->id)->find($id);
        } else {
            $learning = Learning::find($id);
        }

        if (!$learning) {
            return $this->sendError('Data learning tidak ditemukan');
        }

        $learning->delete();

        return $this->sendResponse($learning, 'Berhasil menghapus data learning');
    }
}
