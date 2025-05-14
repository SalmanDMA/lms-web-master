<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\User;
use App\Http\Traits\UserRegistrationTrait;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\SubClasses;
use App\Models\Teacher;
use App\Models\TeacherSubClasses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ManagementUserController extends Controller
{
    use CommonTrait, UserRegistrationTrait, StaticDataTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');
        $query = User::query();

        if ($userLogin->school_id == null) {
            $query->where('school_id', $userLogin->id);
        }

        if ($userLogin->role === 'STAFF') {
            $query->where('school_id', $userLogin->school_id);
        }

        if ($search) {
            $query->where('fullname', 'like', '%' . $search . '%');
        }

        $users = $query->get();

        $all_student_on_enrollment = Enrollment::all();
        $all_teacher_subclass = TeacherSubClasses::all();
        $all_subclass = SubClasses::all();

        /** @var \App\Models\User $user */
        foreach ($users as $user) {
            /** @property array $enrollment */

            if ($user->role === 'STUDENT') {
                $studentEnrollments = $all_student_on_enrollment->filter(function ($enrollment) use ($user, $all_teacher_subclass) {
                    return $user->is_student && $user->is_student->id == $enrollment->student_id &&
                        $all_teacher_subclass->contains('course', $enrollment->course_id);
                })->map(function ($enrollment) use ($all_teacher_subclass, $all_subclass) {
                    $teacherSubclass = $all_teacher_subclass->firstWhere('course', $enrollment->course_id);
                    $subclassDetails = $all_subclass->firstWhere('id', $teacherSubclass->sub_class_id);

                    return [
                        'subclass' => $subclassDetails,
                        'course' => $enrollment->course,
                    ];
                });

                $user->enrollment = $studentEnrollments->isNotEmpty() ? $studentEnrollments->values() : [];
            }
        }

        if ($users->isEmpty()) {
            return $this->sendError('User tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($users, 'Berhasil mengambil semua data user.');
    }


    public function store(Request $request)
    {
        $validated = $this->validateUser($request, []);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $user = $this->saveUser($validated, $request);

        $additionalData = $this->registerOrUpdateRole($request, $user);

        if ($additionalData instanceof JsonResponse) {
            return $additionalData;
        }

        return $this->sendResponse(array_merge($user->toArray(), $additionalData), 'Berhasil menambahkan data user', 201);
    }

    public function show($id)
    {
        $userLogin = auth()->user();
        $query = User::query();

        if ($userLogin->school_id == null) {
            $query->where('school_id', $userLogin->id);
        }

        if ($userLogin->role === 'STAFF') {
            $query->where('school_id', $userLogin->school_id);
        }

        $user = $query->find($id);

        if (!$user) {
            return $this->sendError('User tidak ditemukan.', null, 200);
        }

        if ($user->role === 'STUDENT') {
            $all_student_on_enrollment = Enrollment::all();
            $all_teacher_subclass = TeacherSubClasses::all();
            $all_subclass = SubClasses::all();

            $studentEnrollments = $all_student_on_enrollment->filter(function ($enrollment) use ($user, $all_teacher_subclass) {
                return $user->is_student && $user->is_student->id == $enrollment->student_id &&
                    $all_teacher_subclass->contains('course', $enrollment->course_id);
            })->map(function ($enrollment) use ($all_teacher_subclass, $all_subclass) {
                $teacherSubclass = $all_teacher_subclass->firstWhere('course', $enrollment->course_id);
                $subclassDetails = $all_subclass->firstWhere('id', $teacherSubclass->sub_class_id);

                return [
                    'subclass' => $subclassDetails,
                    'course' => $enrollment->course,
                ];
            });

            $user->enrollment = $studentEnrollments->isNotEmpty() ? $studentEnrollments->values() : [];
        }

        return $this->sendResponse($user, 'Berhasil menemukan data user.');
    }

    public function update($id, Request $request)
    {
        $userLogin = auth()->user();
        $user = User::query();

        if ($userLogin->school_id == null) {
            $user->where('school_id', $userLogin->id);
        }

        if ($userLogin->role === 'STAFF') {
            $user->where('school_id', $userLogin->school_id);
        }

        $user = $user->find($id);

        if (!$user) {
            return $this->sendError('User tidak ditemukan.', null, 200);
        }

        $validated = $this->validateUser($request, []);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $user = $this->saveUser($validated, $request, $user);

        $additionalData = $this->registerOrUpdateRole($request, $user);

        if ($additionalData instanceof JsonResponse) {
            return $additionalData;
        }

        return $this->sendResponse(array_merge($user->toArray(), $additionalData), 'Update berhasil', 200);
    }

    public function destroy($id)
    {
        $userLogin = auth()->user();
        $user = User::where('school_id', $userLogin->id)->find($id);

        if (!$user) {
            return $this->sendError('User tidak ditemukan.', null, 200);
        }

        if ($user->role === 'STUDENT') {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $student->delete();
            }
        }

        if ($user->role === 'TEACHER') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $teacher->delete();
            }
        }

        if ($user->role === 'STAFF') {
            $staff = Staff::where('user_id', $user->id)->first();
            if ($staff) {
                $staff->delete();
            }
        }

        $user->delete();
        return $this->sendResponse($user, 'User berhasil di hapus', 200);
    }

    public function list_student(Request $request)
    {
        $userLogin = auth()->user();

        if (!$userLogin) {
            return $this->sendError('User tidak terautentikasi.', null, 401);
        }

        $students = collect();
        $search = $request->query('search');

        $school_id = $userLogin->school_id ?? $userLogin->id;
        $query = User::where('school_id', $school_id)->where('role', 'STUDENT');

        if ($search) {
            $query->where('fullname', 'like', '%' . $search . '%');
        }

        $students = $query->get();
        $all_student_on_enrollment = Enrollment::all();
        $all_teacher_subclass = TeacherSubClasses::all();
        $all_subclass = SubClasses::all();

        foreach ($students as $student) {
            $studentEnrollments = $all_student_on_enrollment->filter(function ($enrollment) use ($student, $all_teacher_subclass) {
                return $student->is_student && $student->is_student->id == $enrollment->student_id &&
                    $all_teacher_subclass->contains('course', $enrollment->course_id);
            })->map(function ($enrollment) use ($all_teacher_subclass, $all_subclass) {
                $teacherSubclass = $all_teacher_subclass->firstWhere('course', $enrollment->course_id);
                $subclassDetails = $all_subclass->firstWhere('id', $teacherSubclass->sub_class_id);

                return [
                    'subclass' => $subclassDetails,
                    'course' => $enrollment->course,
                ];
            });

            $student->enrollment = $studentEnrollments->isNotEmpty() ? $studentEnrollments->values() : [];
        }

        if ($students->isEmpty()) {
            return $this->sendError('Siswa tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($students, 'Berhasil mengambil semua data siswa');
    }

    public function student_profile($id)
    {
        $convertId = $this->convertSubClassId($id);
        $student = Student::where('id', $convertId)->first();

        $all_student_on_enrollment = Enrollment::all();
        $all_teacher_subclass = TeacherSubClasses::all();
        $all_subclass = SubClasses::all();

        $studentEnrollments = $all_student_on_enrollment->filter(function ($enrollment) use ($student, $all_teacher_subclass) {
            return $student->id == $enrollment->student_id &&
                $all_teacher_subclass->contains('course', $enrollment->course_id);
        })->map(function ($enrollment) use ($all_teacher_subclass, $all_subclass) {
            $teacherSubclass = $all_teacher_subclass->firstWhere('course', $enrollment->course_id);
            $subclassDetails = $all_subclass->firstWhere('id', $teacherSubclass->sub_class_id);

            return [
                'subclass' => $subclassDetails,
                'course' => $enrollment->course,
            ];
        });

        $student->enrollment = $studentEnrollments->isNotEmpty() ? $studentEnrollments->values() : [];

        return $this->sendResponse($student, 'Berhasil menemukan data siswa');
    }

    public function list_teacher(Request $request)
    {
        $userLogin = auth()->user();
        $teachers = collect();
        $search = $request->query('search');

        // for teacher or sekolah role
        if ($userLogin->school_id != null) {
            $query = User::where('school_id', $userLogin->school_id)->where('role', 'TEACHER');
        }
        //  for admin
        else {
            $query = User::where('school_id', $userLogin->id)->where('role', 'TEACHER');
        }

        if ($search) {
            $query->where('fullname', 'like', '%' . $search . '%');
        }

        $teachers = $query->get();

        if ($teachers->isEmpty()) {
            return $this->sendError('Guru tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($teachers, 'Berhasil mengambil semua data guru');
    }

    public function teacher_profile($id)
    {
        $convertId = $this->convertSubClassId($id);
        $teacher = Teacher::where('id', $convertId)->first();
        return $this->sendResponse($teacher, 'Berhasil menemukan data guru');
    }
}
