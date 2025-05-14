<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\SubClasses;
use App\Models\Teacher;
use App\Models\TeacherSubClasses;
use App\Models\User;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManagementWaliKelasController extends Controller
{
    use CommonTrait, StaticDataTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();

        $school_id = $userLogin->school_id ?? $userLogin->id;
        $wali_kelas = Teacher::whereHas('user', function ($query) use ($school_id) {
            $query->where('school_id', $school_id);
        })->where('is_wali', true)->get();

        return $this->sendResponse($wali_kelas, 'Berhasil mengambil semua data wali kelas.');
    }


    public function show($id)
    {
        $convertId = $this->convertSubClassId($id);
        $wali_kelas = Teacher::where('is_wali', true)->where('id', $convertId)->first();

        if (!$wali_kelas) {
            return $this->sendError('Data wali kelas tidak ditemukan.', null, 404);
        }

        return $this->sendResponse($wali_kelas, 'Berhasil menemukan data wali kelas.');
    }

    public function updateIsWali(Request $request, $id)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'sub_class_id' => 'required|exists:sub_class,id',
            'is_wali' => 'required|boolean',
            'course' => 'nullable|exists:courses,id',
        ], [
            'sub_class_id.required' => 'Ups! Lengkapi formulir di bawah ini',
            'course.exists' => 'Course yang dipilih tidak valid',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $validated = $validator->validated();

        $convertId = $this->convertSubClassId($id);
        $teacher = Teacher::where('id', $convertId)->first();

        if ($teacher) {
            $teacher->update(['is_wali' => $validated['is_wali']]);

            if ($validated['is_wali']) {
                SubClasses::where('id', $validated['sub_class_id'])->update(['guardian' => $teacher->id]);

                TeacherSubClasses::updateOrInsert(
                    [
                        'teacher_id' => $teacher->id,
                        'sub_class_id' => $validated['sub_class_id'],
                    ],
                    ['course' => null, 'updated_at' => now()]
                );
            } else {
                SubClasses::where('id', $validated['sub_class_id'])->update(['guardian' => null]);

                $existingTeacherCourse = TeacherSubClasses::where('sub_class_id', $request->sub_class_id)
                    ->where('course', $request->course)
                    ->exists();

                if ($existingTeacherCourse) {
                    return $this->sendError('Ups, Sub Class sudah diisi oleh guru dengan course yang sama', null, 200);
                }

                TeacherSubClasses::updateOrInsert(
                    [
                        'teacher_id' => $teacher->id,
                        'sub_class_id' => $validated['sub_class_id'],
                    ],
                    [
                        'course' => $validated['course'] ?? null,
                        'updated_at' => now()
                    ]
                );
            }
        }

        $message = $validated['is_wali'] ? 'Berhasil menetapkan wali kelas' : 'Berhasil membatalkan wali kelas';

        return $this->sendResponse($teacher, $message);
    }
}
