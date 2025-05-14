<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Support\Facades\Validator;


class KelasAjarController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_kelas_ajar()
    {
        $this->authorizeStaff();

        $courseData = $this->fetchCourses();
        $classData = $this->fetchLevels();
        $teacherSubClass = $this->fetchData('/api/v1/cms/staff-curriculum/enrollment/sub-class');
        $allSubClass = $this->fetchData('/api/v1/cms/staff-curriculum/sub-class');
        $userTeacherData = $this->fetchTeachers();
        // dd($userTeacherData);

        $enrollmentData = [];
        if (isset($teacherSubClass->data) && isset($allSubClass->data) && !empty($courseData) && !empty($userTeacherData)) {
            $enrollmentData = $this->mapEnrollmentData($teacherSubClass->data, $allSubClass->data, $courseData, $userTeacherData);
        }
        // dd($enrollmentData);


        // dd($enrollmentData);

        return view('staff-curriculum.kelas-mengajar.index', [
            'courses' => $courseData,
            'levels' => $classData,
            'enrollmentData' => $enrollmentData,
            'teachers' => $userTeacherData,
            'allSubClass' => $allSubClass,
        ]);
    }

    public function add_kelas_ajar(Request $request)
    {
        $this->authorizeStaff();


        $validator = Validator::make($request->all(), [
            'course' => 'required|string|exists:courses,id',
            'sub_class_id' => 'required|string|exists:sub_class,id',
            'teacher_id' => 'required|string|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formDataCreateLearning = [
            'course' => $request->course,
            'teacher_id' => $request->teacher_id,
            'status' => 'Active',
        ];

        $responseCreateLearning = $this->postData('/api/v1/cms/staff-curriculum/learning', $formDataCreateLearning, 'json');

        $isCreateLearningSuccess = $responseCreateLearning->success;


        if (!$isCreateLearningSuccess) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseCreateLearning->message]);
        }

        $formDataAssignSubclass = [
            'sub_class_id' => $request->sub_class_id,
            'teacher_id' => $request->teacher_id,
            'course' => $request->course,
            'learning_id' => $responseCreateLearning->data->id,
        ];

        $responseAssignSubclass = $this->postData('/api/v1/cms/staff-curriculum/enrollment/sub-class', $formDataAssignSubclass, 'json');

        $isAssignSubclassSuccess = $responseAssignSubclass->success;

        if (!$isAssignSubclassSuccess) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseAssignSubclass->message]);
        }



        if ($isCreateLearningSuccess && $isAssignSubclassSuccess) {
            return $this->handleResponse($responseAssignSubclass, 'add');
        } else {
            return redirect()->back()->withInput()->withErrors(['message' => 'Something went wrong!!!']);
        }
    }

    public function v_kelas_ajar_student($course_id, $sub_class_id)
    {
        $this->authorizeStaff();

        // Ambil semua data dari API terkait
        $courseData = $this->fetchCourses();
        $allSubClass = $this->fetchData('/api/v1/cms/staff-curriculum/sub-class');
        $studentData = $this->fetchStudents();
        $teacherData = $this->fetchTeachers();
        $allStudentOnEnrollment = $this->fetchData('/api/v1/cms/staff-curriculum/enrollment/student');

        // Konversi subclass id dari parameter ke format yang sesuai
        // $convertSubclass = $this->convertSubClassId($sub_class_id);

        // dd($convertSubclass, $sub_class_id);

        // Filter subclass berdasarkan ID
        $filteredSubClass = collect($allSubClass->data ?? [])->firstWhere('id', $sub_class_id);

        // Filter course berdasarkan ID
        $filteredCourse = collect($courseData)->firstWhere('id', $course_id);

        // Filter student berdasarkan subclass
        $filteredStudent = collect($studentData ?? [])->where('is_student.sub_class_id', $sub_class_id);

        // Filter teacher yang mengajar subclass ini (menghasilkan 1 data, bukan array)
        $filteredTeacher = collect($teacherData ?? [])->first(function ($teacher) use ($sub_class_id) {
            return collect($teacher->is_teacher->sub_classes ?? [])->contains('id', $sub_class_id);
        });

        // Ambil semua siswa yang telah di-enroll pada course dan subkelas tertentu
        $filteredAllStudentOnEnrollment = collect($allStudentOnEnrollment->data ?? [])
            ->where('course_id', $course_id)
            ->pluck('student.id')
            ->filter() // Pastikan hanya nilai yang valid yang diambil
            ->toArray();

        // Ambil semua siswa yang telah di-enroll
        $getAllStudentOnEnrollment = collect($studentData ?? [])
            ->whereIn('is_student.id', $filteredAllStudentOnEnrollment)
            ->where('is_student.sub_class_id', $sub_class_id); // Perbaikan di sini, dari sub_class.id ke sub_class_id

        // Dapatkan siswa yang belum di-enroll
        $getAllStudentNotEnrollment = collect($filteredStudent ?? [])
            ->whereNotIn('is_student.id', $filteredAllStudentOnEnrollment); // Ganti diffKeys dengan whereNotIn

        return view('staff-curriculum.kelas-mengajar.student', [
            'filteredSubClass' => $filteredSubClass,
            'filteredCourse' => $filteredCourse,
            'filteredStudent' => $getAllStudentNotEnrollment,
            'filteredTeacher' => $filteredTeacher,
            'allStudentOnEnrollment' => $getAllStudentOnEnrollment,
            'sub_class_id' => $sub_class_id,
            'course_id' => $course_id,
        ]);
    }


    public function enroll_student(Request $request, $course_id, $sub_class_id)
    {
        $this->authorizeStaff();

        $validator = Validator::make($request->all(), [
            'students' => 'required|array',
            'students.*' => 'exists:students,id',
            'teacher_id' => 'required|string|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // $convertSubclass = $this->convertSubClassId($sub_class_id);

        $formDataEnrollStudent = [
            'sub_class_id' => $sub_class_id,
            'course' => $course_id,
            'students' => $request->students,
            'teacher_id' => $request->teacher_id,
        ];

        $responseEnrollStudent = $this->postData('/api/v1/cms/staff-curriculum/enrollment/student', $formDataEnrollStudent, 'json');
        // dd($responseEnrollStudent);
        if (!$responseEnrollStudent->success) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseEnrollStudent->message]);
        }

        return $this->reloadStudentView($course_id, $sub_class_id, $sub_class_id, 'alert-success', $responseEnrollStudent->message);
    }

    public function unenroll_student(Request $request, $course_id, $sub_class_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'students' => 'required|array',
            'students.*' => 'exists:students,id',
            'teacher_id' => 'required|string|exists:teachers,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // $convertSubclass = $this->convertSubClassId($sub_class_id);

        $formDataEnrollStudent = [
            'sub_class_id' => $sub_class_id,
            'course' => $course_id,
            'students' => $request->students,
            'teacher_id' => $request->teacher_id,
        ];

        $responseEnrollStudent = $this->putData('/api/v1/cms/staff-curriculum/enrollment/student/update', $formDataEnrollStudent, 'json');

        if (!$responseEnrollStudent->success) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseEnrollStudent->message]);
        }

        return $this->reloadStudentView($course_id, $sub_class_id, $sub_class_id, 'alert-success', $responseEnrollStudent->message);
    }

    public function delete_kelas_ajar($id)
    {
        $this->authorizeStaff();

        $responseDelete = $this->deleteData('/api/v1/cms/staff-curriculum/enrollment/sub-class/' . $id);

        // dd($id);
        if (!$responseDelete->success) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseDelete->message]);
        }

        return $this->reloadIndexView('alert-success', $responseDelete->message);
    }

    private function mapEnrollmentData($teacherSubClassData, $allSubClassData, $courseData, $userTeacherData)
    {
        return collect($teacherSubClassData)->map(function ($enrollment) use ($allSubClassData, $courseData, $userTeacherData) {
            // dd($userTeacherData);
            $subClass = collect($allSubClassData)->firstWhere('id', $enrollment->sub_class_id);
            $course = collect($courseData)->firstWhere('id', $enrollment->course);
            $teacher = collect($userTeacherData)
                ->where('is_teacher.id', $enrollment->teacher_id)
                ->first();
            // dd($teacher);

            return (object) [
                'enrollment_id' => $enrollment->id,
                'teacher_id' => $enrollment->teacher_id,
                'teacher_name' => $teacher->fullname ?? null,
                'sub_class_id' => $enrollment->sub_class_id,
                'course_id' => $enrollment->course,
                'sub_class_name' => $subClass->name ?? null,
                'class_id' => $subClass->class->id ?? null,
                'class_name' => $subClass->class->name ?? null,
                'course_name' => $course->courses_title ?? null,
            ];
        });
    }

    private function reloadIndexView($alertClass, $message)
    {
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $teacherSubClass = $this->fetchData('/api/v1/cms/staff-curriculum/enrollment/sub-class');
        $allSubClass = $this->fetchData('/api/v1/cms/staff-curriculum/sub-class');
        $userTeacherData = $this->fetchTeachers();


        $enrollmentData = [];
        if (isset($teacherSubClass->data) && isset($allSubClass->data) && !empty($courseData) && !empty($userTeacherData)) {
            $enrollmentData = $this->mapEnrollmentData($teacherSubClass->data, $allSubClass->data, $courseData, $userTeacherData);
        }


        return redirect()->route('staff_curriculum.kelas_mengajar')
            ->with('message', $message)
            ->with('alertClass', $alertClass)
            ->with('courses', $courseData)
            ->with('teachers', $userTeacherData)
            ->with('levels', $levels)
            ->with('enrollmentData', $enrollmentData)
            ->with('allSubClass', $allSubClass);
    }

    private function reloadStudentView($course_id, $convertSubclass, $sub_class_id, $alertClass, $message)
    {
        $courseData = $this->fetchCourses();
        $allSubClass = $this->fetchData('/api/v1/cms/staff-curriculum/sub-class');
        $studentData = $this->fetchStudents();
        $teacherData = $this->fetchTeachers();
        $allStudentOnEnrollment = $this->fetchData('/api/v1/cms/staff-curriculum/enrollment/student');

        // Konversi subclass id dari parameter ke format yang sesuai
        // $convertSubclass = $this->convertSubClassId($sub_class_id);

        // Filter subclass berdasarkan ID
        $filteredSubClass = collect($allSubClass->data ?? [])->firstWhere('id', $convertSubclass);

        // Filter course berdasarkan ID
        $filteredCourse = collect($courseData)->firstWhere('id', $course_id);

        // Filter student berdasarkan subclass
        $filteredStudent = collect($studentData ?? [])->where('is_student.sub_class_id', $convertSubclass);

        // Filter teacher yang mengajar subclass ini (menghasilkan 1 data, bukan array)
        $filteredTeacher = collect($teacherData ?? [])->first(function ($teacher) use ($convertSubclass) {
            return collect($teacher->is_teacher->sub_classes ?? [])->contains('id', $convertSubclass);
        });

        // Ambil semua siswa yang telah di-enroll pada course dan subkelas tertentu
        $filteredAllStudentOnEnrollment = collect($allStudentOnEnrollment->data ?? [])
            ->where('course_id', $course_id)
            ->pluck('student.id')
            ->filter() // Pastikan hanya nilai yang valid yang diambil
            ->toArray();

        // Ambil semua siswa yang telah di-enroll
        $getAllStudentOnEnrollment = collect($studentData ?? [])
            ->whereIn('is_student.id', $filteredAllStudentOnEnrollment)
            ->where('is_student.sub_class_id', $convertSubclass); // Perbaikan di sini, dari sub_class.id ke sub_class_id

        // Dapatkan siswa yang belum di-enroll
        $getAllStudentNotEnrollment = collect($filteredStudent ?? [])
            ->whereNotIn('is_student.id', $filteredAllStudentOnEnrollment); // Ganti diffKeys dengan whereNotIn

        return view('staff-curriculum.kelas-mengajar.student', [
            'filteredSubClass' => $filteredSubClass,
            'filteredCourse' => $filteredCourse,
            'filteredStudent' => $getAllStudentNotEnrollment,
            'filteredTeacher' => $filteredTeacher,
            'allStudentOnEnrollment' => $getAllStudentOnEnrollment,
            'sub_class_id' => $sub_class_id,
            'course_id' => $course_id,
        ]);
    }

    private function handleResponse($response_data, $type)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        if ($response_data->success) {
            return $this->reloadIndexView($alertClass, $message);
        } else {
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        }
    }

    private function getResponseMessage($success, $type, $message = null)
    {
        if ($success) {
            switch ($type) {
                case 'add':
                    return 'Kelas ajar berhasil ditambahkan.';
                case 'enroll':
                    return 'Siswa berhasil di-enroll ke kelas ajar.';
                case 'unenroll':
                    return 'Siswa berhasil di-unenroll dari kelas ajar.';
                default:
                    return 'Operasi berhasil.';
            }
        } else {
            return $message ?? 'Operasi gagal. Silakan coba lagi.';
        }
    }


    public function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/course');
        return $response_data->data ?? [];
    }

    public function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/class');
        return $response_data->data ?? [];
    }

    public function fetchTeachers()
    {
        $allUser = $this->fetchData('/api/v1/cms/staff-curriculum/user');

        $userTeacherData = [];

        foreach ($allUser->data as $data) {
            if ($data->role === 'TEACHER') {
                $userTeacherData[] = $data;
            }
        }

        return $userTeacherData;
    }

    public function fetchStudents()
    {
        $allUser = $this->fetchData('/api/v1/cms/staff-curriculum/user');

        $userStudentData = [];

        foreach ($allUser->data as $data) {
            if ($data->role === 'STUDENT') {
                $userStudentData[] = $data;
            }
        }

        return $userStudentData;
    }
}
