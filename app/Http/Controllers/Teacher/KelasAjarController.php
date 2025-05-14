<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KelasAjarController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_pengajar_kelas_ajar(Request $request)
    {
        $this->authorizeTeacher();

        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $teacherSubClass = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');
        $allSubClass = $this->fetchData('/api/v1/mobile/teacher/sub-class');

        $enrollmentData = [];
        if (isset($teacherSubClass->data) && isset($allSubClass->data) && !empty($courseData)) {
            $enrollmentData = $this->mapEnrollmentData($teacherSubClass->data, $allSubClass->data, $courseData);
        }
        // dd($teacherSubClass->data);
        return view('teacher.pengajar.kelas-ajar.index', [
            'courses' => $courseData,
            'levels' => $levels,
            'enrollmentData' => $enrollmentData,
            'allSubClass' => $allSubClass,
        ]);
    }

    public function add_kelas_ajar(Request $request)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'course' => 'required|string|exists:courses,id',
            'sub_class_id' => 'required|string|exists:sub_class,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formDataCreateLearning = [
            'course' => $request->course,
            'status' => 'Active',
        ];

        $responseCreateLearning = $this->postData('/api/v1/mobile/teacher/learning', $formDataCreateLearning, 'json');

        $isCreateLearningSuccess = $responseCreateLearning->success;

        if (!$isCreateLearningSuccess) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseCreateLearning->message]);
        }

        $formDataAssignSubclass = [
            'sub_class_id' => $request->sub_class_id,
            'course' => $request->course,
            'learning_id' => $responseCreateLearning->data->id,
        ];

        $responseAssignSubclass = $this->postData('/api/v1/mobile/teacher/enrollment/sub-class', $formDataAssignSubclass, 'json');

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
        $this->authorizeTeacher();

        $courseData = $this->fetchCourses();
        $allSubClass = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $allStudent = $this->fetchData('/api/v1/cms/teacher/user/student');
        $allStudentOnEnrollment = $this->fetchData('/api/v1/mobile/teacher/enrollment/student');

        // $convertSubclass = $this->convertSubClassId($sub_class_id);
        $filteredSubClass = collect($allSubClass->data ?? [])->firstWhere('id', $sub_class_id);
        $filteredCourse = collect($courseData)->firstWhere('id', $course_id);
        $filteredStudent = collect($allStudent->data ?? [])->where('is_student.sub_class_id', $sub_class_id);

        $filteredAllStudentOnEnrollment = collect($allStudentOnEnrollment->data ?? [])
            ->where('course_id', $course_id)
            ->pluck('student.id')
            ->toArray();

        $getAllStudentOnEnrollment = collect($allStudent->data ?? [])
            ->whereIn('is_student.id', $filteredAllStudentOnEnrollment)->where('is_student.sub_class_id', $sub_class_id);

        $getAllStudentNotEnrollment = collect($filteredStudent ?? [])
            ->diffKeys($getAllStudentOnEnrollment);

        return view('teacher.pengajar.kelas-ajar.student', [
            'filteredSubClass' => $filteredSubClass,
            'filteredCourse' => $filteredCourse,
            'filteredStudent' => $getAllStudentNotEnrollment,
            'allStudentOnEnrollment' => $getAllStudentOnEnrollment,
            'sub_class_id' => $sub_class_id,
            'course_id' => $course_id,
        ]);
    }

    public function enroll_student(Request $request, $course_id, $sub_class_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'students' => 'required|array',
            'students.*' => 'exists:students,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $convertSubclass = $this->convertSubClassId($sub_class_id);

        $formDataEnrollStudent = [
            'sub_class_id' => $convertSubclass,
            'course' => $course_id,
            'students' => $request->students,
        ];

        $responseEnrollStudent = $this->postData('/api/v1/mobile/teacher/enrollment/student', $formDataEnrollStudent, 'json');

        if (!$responseEnrollStudent->success) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseEnrollStudent->message]);
        }

        return $this->reloadStudentView($course_id, $convertSubclass, $sub_class_id, 'alert-success', $responseEnrollStudent->message);
    }

    public function unenroll_student(Request $request, $course_id, $sub_class_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'students' => 'required|array',
            'students.*' => 'exists:students,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $convertSubclass = $this->convertSubClassId($sub_class_id);

        $formDataEnrollStudent = [
            'sub_class_id' => $convertSubclass,
            'course' => $course_id,
            'students' => $request->students,
        ];

        $responseEnrollStudent = $this->putData('/api/v1/mobile/teacher/enrollment/student/update', $formDataEnrollStudent, 'json');

        if (!$responseEnrollStudent->success) {
            return redirect()->back()->withInput()->withErrors(['message' => $responseEnrollStudent->message]);
        }

        return $this->reloadStudentView($course_id, $convertSubclass, $sub_class_id, 'alert-success', $responseEnrollStudent->message);
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

    private function reloadIndexView($alertClass, $message)
    {
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $teacherSubClass = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');
        $allSubClass = $this->fetchData('/api/v1/mobile/teacher/sub-class');

        $enrollmentData = [];
        if (isset($teacherSubClass->data) && isset($allSubClass->data) && !empty($courseData)) {
            $enrollmentData = $this->mapEnrollmentData($teacherSubClass->data, $allSubClass->data, $courseData);
        }

        return redirect()->route('teacher.v_pengajar.kelas_ajar')
            ->with('message', $message)
            ->with('alertClass', $alertClass)
            ->with('courses', $courseData)
            ->with('levels', $levels)
            ->with('enrollmentData', $enrollmentData)
            ->with('allSubClass', $allSubClass);
    }

    private function reloadStudentView($course_id, $convertSubclass, $sub_class_id, $alertClass, $message)
    {
        $courseData = $this->fetchCourses();
        $allSubClass = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $allStudent = $this->fetchData('/api/v1/cms/teacher/user/student');
        $allStudentOnEnrollment = $this->fetchData('/api/v1/mobile/teacher/enrollment/student');

        $filteredSubClass = collect($allSubClass->data ?? [])->firstWhere('id', $convertSubclass);
        $filteredCourse = collect($courseData)->firstWhere('id', $course_id);
        $filteredStudent = collect($allStudent->data ?? [])->where('is_student.sub_class_id', $convertSubclass);

        $filteredAllStudentOnEnrollment = collect($allStudentOnEnrollment->data ?? [])
            ->where('course_id', $course_id)
            ->pluck('student.id')
            ->toArray();

        $getAllStudentOnEnrollment = collect($allStudent->data ?? [])
            ->whereIn('is_student.id', $filteredAllStudentOnEnrollment)->where('is_student.sub_class_id', $convertSubclass);

        $getAllStudentNotEnrollment = collect($filteredStudent ?? [])
            ->diffKeys($getAllStudentOnEnrollment);

        return redirect()->route('teacher.pengajar.v_kelas_ajar_student', ['course_id' => $course_id, 'sub_class_id' => $sub_class_id])
            ->with('message', $message)
            ->with('alertClass', $alertClass)
            ->with('filteredSubClass', $filteredSubClass)
            ->with('filteredCourse', $filteredCourse)
            ->with('filteredStudent', $getAllStudentNotEnrollment)->with('allStudentOnEnrollment', $getAllStudentOnEnrollment)
            ->with('sub_class_id', $sub_class_id)
            ->with('course_id', $course_id);
    }

    private function mapEnrollmentData($teacherSubClassData, $allSubClassData, $courseData)
    {
        return collect($teacherSubClassData)->map(function ($enrollment) use ($allSubClassData, $courseData) {
            $subClass = collect($allSubClassData)->firstWhere('id', $enrollment->sub_class_id);
            $course = collect($courseData)->firstWhere('id', $enrollment->course);

            return (object) [
                'enrollment_id' => $enrollment->id,
                'teacher_id' => $enrollment->teacher_id,
                'sub_class_id' => $enrollment->sub_class_id,
                'course_id' => $enrollment->course,
                'sub_class_name' => $subClass->name ?? null,
                'class_id' => $subClass->class->id ?? null,
                'class_name' => $subClass->class->name ?? null,
                'course_name' => $course->courses_title ?? null,
            ];
        });
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
        $response_data = $this->fetchData('/api/v1/mobile/teacher/course');
        return $response_data->data ?? [];
    }

    public function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/class');
        return $response_data->data ?? [];
    }
}
