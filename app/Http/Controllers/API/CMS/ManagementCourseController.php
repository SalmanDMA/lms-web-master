<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Course;
use App\Models\User;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementCourseController extends Controller
{

    use CommonTrait;

    public function index(Request $request)
    {
        $search = $request->query('search');
        $courses = collect();
        $userLogin = auth()->user();


        // for teacher or sekolah role
        if ($userLogin->school_id != null) {
            $query = Course::where('created_by', $userLogin->school_id);
        }
        //  for admin
        else {
            $query = Course::where('created_by', $userLogin->id);
        }

        if ($search) {
            $query->where('courses_title', 'like', '%' . $search . '%');
        }

        $courses = $query->get();

        if ($courses->isEmpty()) {
            return $this->sendError('Course tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($courses, 'Berhasil mengambil semua data course');
    }

    public function show($id)
    {
        $userLogin = auth()->user();
        $created_by = null;

        if ($userLogin->school_id != null) {
            $created_by = $userLogin->school_id;
        } else {
            $created_by = $userLogin->id;
        }

        $course = Course::where('created_by', $created_by)->find($id);

        if (!$course) {
            return $this->sendError('Course tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($course, 'Berhasil menemukan data course');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'courses_title' => 'required',
            'courses_description' => 'required',
            'type' => 'required',
            'course_code' => 'required',
            'curriculum' => 'nullable',
        ], [
            'courses_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'courses_description.required' => 'Ups, Anda Belum Melengkapi Form',
            'type.required' => 'Ups, Anda Belum Melengkapi Form',
            'course_code.required' => 'Ups, Anda Belum Melengkapi Form'
        ]);

        $created_by = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $request->merge(['created_by' =>  $created_by]);

        if ($request->type != 'Lainnya') {
            $validator = Validator::make(
                $request->all(),
                [
                    'curriculum' => 'required',
                ],
                [
                    'curriculum.required' => 'Ups, Anda Belum Melengkapi Form',
                ]
            );
        } else {
            $request->merge(['curriculum' => $request->curriculum]);
        }

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $generateId = IdGenerator::generate(['table' => 'courses', 'length' => 16, 'prefix' => 'COU-']);

        $request->merge(['id' => $generateId]);

        $course = Course::create($request->all());

        return $this->sendResponse($course, 'Berhasil menambahkan data course', 201);
    }

    public function update(Request $request, $id)
    {

        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'courses_title' => 'required',
            'course_code' => 'required',
        ], [
            'courses_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'course_code.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $created_by = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $course = Course::where('created_by',  $created_by)->find($id);

        if (!$course) {
            return $this->sendError('Course tidak ditemukan.', null, 200);
        }

        $course->update($request->all());

        return $this->sendResponse($course, 'Berhasil mengubah data course', 200);
    }

    public function destroy($id)
    {

        $userLogin = auth()->user();

        $created_by = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $course = Course::where('created_by', $created_by)->find($id);

        if (!$course) {
            return $this->sendError('Course tidak ditemukan.', null, 200);
        }

        $course->delete();
        return $this->sendResponse($course, 'Berhasil menghapus data course');
    }
}
