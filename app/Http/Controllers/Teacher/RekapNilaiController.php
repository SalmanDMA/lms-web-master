<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RekapNilaiController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_rekap_nilai(Request $request)
    {
        $this->authorizeTeacher();

        $learnings = $this->fetchData('/api/v1/mobile/teacher/learning');
        $allSubclass = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $teacherSubclasses = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');
        $assignments = $this->fetchData('/api/v1/mobile/teacher/assignment');
        $class_exams = $this->fetchData('/api/v1/cms/teacher/class-exam');
        $userLogin = $this->fetchData('/api/v1/auth/profile/me');

        // dd($learnings);

        $learningCourseIds = array_map(function ($learning) {
            return $learning->course->id ?? null;
        }, $learnings->data ?? []);

        $filteredTeacherSubclassesByLearning = array_filter(
            $teacherSubclasses->data ?? [],
            fn($subclass) => in_array($subclass->course, $learningCourseIds)
        );

        $uniqueCourses = [];
        $seenCourseIds = [];
        foreach ($learnings->data ?? [] as $learning) {
            if (!isset($seenCourseIds[$learning->course->id])) {
                $uniqueCourses[] = [
                    'id' => $learning->id,
                    'teacher_id' => $learning->teacher_id,
                    'course' => [
                        'id' => $learning->course->id,
                        // 'name' => $learning->course->name,
                        'name' => $learning->course->courses_title,
                    ],
                    'status' => $learning->status,
                    'created_at' => $learning->created_at,
                    'updated_at' => $learning->updated_at,
                    'deleted_at' => $learning->deleted_at,
                ];
                $seenCourseIds[$learning->course->id] = true;
            }
        }

        $mappedTeacherSubclasses = array_map(function ($subclass) use ($learnings, $assignments, $class_exams, $userLogin, $allSubclass) {
            $learningId = null;
            $courseName = null;
            $courseId = null;

            foreach ($learnings->data ?? [] as $learning) {
                if ($learning->course->id == $subclass->course && $learning->id == $subclass->learning_id) {
                    $learningId = $learning->id;
                    $courseName = $learning->course->courses_title;
                    $courseId = $learning->course->id;
                    break;
                }
            }

            $subclassDetail = null;
            foreach ($allSubclass->data ?? [] as $allSubclassItem) {
                if ($allSubclassItem->id == $subclass->sub_class_id) {
                    $subclassDetail = $allSubclassItem;
                    break;
                }
            }

            $class_name = $subclassDetail->class->name ?? 'Unknown Class';
            $subclassName = $subclassDetail->name ?? 'No Subclass Name';

            return [
                'class_name' => $class_name,
                'subclass_id' => $subclass->sub_class_id,
                'learning_id' => $learningId,
                'course_name' => $courseName,
                'course_id' => $courseId,
                'teacher' => $userLogin->data->fullname ?? 'No Teacher',
                'teacher_profile' => isset($userLogin->data->image_path)
                    ? (Storage::exists(str_replace('storage/public/', 'public/', $userLogin->data->image_path))
                        ? str_replace('/storage/storage/public', 'storage/public', Storage::url($userLogin->data->image_path))
                        : null)
                    : null,
                'subclassName' => $subclassName,
                'assignments' => array_filter(
                    $assignments->data ?? [],
                    fn($assignment) => $assignment->learning_id == $learningId
                ),
                'class_exams' => array_filter(
                    $class_exams->data ?? [],
                    fn($classExam) => $classExam->learning_id == $learningId
                ),
            ];
        }, $filteredTeacherSubclassesByLearning);

        return view('teacher.pengajar.rekap.index', [
            'courses' => $uniqueCourses ?? [],
            'filteredTeacherSubclassesByLearning' => $mappedTeacherSubclasses,
        ]);
    }
}
