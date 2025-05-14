<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembelajaranController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_pembelajaran(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        // Fetch data from API
        $learnings = $this->fetchData('/api/v1/mobile/teacher/learning');
        $allSubclass = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $teacherSubclasses = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');
        $teachers = $this->fetchData('/api/v1/cms/teacher/user/teacher');
        $materials = $this->fetchData('/api/v1/mobile/teacher/material');
        $assignments = $this->fetchData('/api/v1/mobile/teacher/assignment');
        $class_exams = $this->fetchData('/api/v1/cms/teacher/class-exam');
        // dd($learnings);

        // dd($teacherSubclasses, $learnings);

        // Store course IDs and subclass IDs for the logged-in teacher
        $learningCourseIds = array_map(fn($learning) => $learning->course->id, $learnings->data ?? []);
        $loggedInTeacherSubclasses = array_map(fn($subclass) => $subclass->sub_class_id, $teacherSubclasses->data ?? []);
        $loggedInTeacherCourseIds = array_map(fn($subclass) => $subclass->course, $teacherSubclasses->data ?? []);

        // dd($learningCourseIds);

        // Create mappings for subclass information
        $subclassIdToName = [];
        $subclassIdToClassName = [];
        $subclassIdToGuardianId = [];

        foreach ($allSubclass->data as $subclass) {
            $subclassIdToName[$subclass->id] = $subclass->name;
            $subclassIdToClassName[$subclass->id] = $subclass->class->name;
            $subclassIdToGuardianId[$subclass->id] = $subclass->guardian; // Teacher ID of the guardian
        }

        // Separate subclasses into those with and without guardians
        $subclassesWithGuardians = [];
        $subclassesWithoutGuardians = [];

        foreach ($teacherSubclasses->data as $subclass) {
            if (in_array($subclass->course, $learningCourseIds)) {
                $guardianId = $subclassIdToGuardianId[$subclass->sub_class_id] ?? null;

                if ($guardianId) {
                    $subclassesWithGuardians[] = $subclass;
                } else {
                    $subclassesWithoutGuardians[] = $subclass;
                }
            }
        }

        // dd($subclassesWithGuardians, $subclassesWithoutGuardians);

        // Initialize grouped data
        $groupedData = [];

        // Process subclasses with guardians
        foreach ($subclassesWithGuardians as $subclass) {
            $guardianId = $subclassIdToGuardianId[$subclass->sub_class_id] ?? null;
            $waliKelasName = null;
            $waliKelasImage = null;
            $teacherObject = null;

            if ($guardianId) {
                foreach ($teachers->data as $teacher) {
                    if ($teacher->is_teacher->id == $guardianId) {
                        $waliKelasName = $teacher->fullname;
                        $teacherObject = $teacher;
                        if ($teacher->image_path) {
                            $filePath = str_replace('storage/public/', 'public/', $teacher->image_path);
                            $waliKelasImage = Storage::exists($filePath) ? $filePath : null;
                        }
                        break;
                    }
                }
            }

            $subclassName = $subclassIdToName[$subclass->sub_class_id] ?? null;
            $className = $subclassIdToClassName[$subclass->sub_class_id] ?? null;
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

            $groupKey = $subclassName . ' - ' . $className . ' - ' . $waliKelasName . ' - ' . $courseName;

            if (!isset($groupedData[$waliKelasName][$groupKey])) {
                $groupedData[$waliKelasName][$groupKey] = [
                    'class_name' => $className,
                    'subclass_id' => $subclass->sub_class_id,
                    'learning_id' => $learningId,
                    'course_name' => $courseName,
                    'course_id' => $courseId,
                    'teacher' => $teacherObject,
                    'waliKelasImage' => $waliKelasImage,
                    'waliKelasName' => $waliKelasName,
                    'subclassName' => $subclassName,
                    'materials' => [],
                    'assignments' => [],
                    'class_exams' => [],
                ];
            }

            // Add materials, assignments, and exams for this subclass
            $groupedData[$waliKelasName][$groupKey]['materials'] = array_merge(
                $groupedData[$waliKelasName][$groupKey]['materials'],
                array_filter($materials->data ?? [], fn($material) => $material->learning_id == $learningId)
            );

            $groupedData[$waliKelasName][$groupKey]['assignments'] = array_merge(
                $groupedData[$waliKelasName][$groupKey]['assignments'],
                array_filter($assignments->data ?? [], fn($assignment) => $assignment->learning_id == $learningId)
            );

            $groupedData[$waliKelasName][$groupKey]['class_exams'] = array_merge(
                $groupedData[$waliKelasName][$groupKey]['class_exams'],
                array_filter($class_exams->data ?? [], fn($classExam) => $classExam->learning_id == $learningId)
            );
        }

        // Process subclasses without guardians
        // dd($subclassesWithoutGuardians, $learnings->data);
        foreach ($subclassesWithoutGuardians as $subclass) {
            $subclassName = $subclassIdToName[$subclass->sub_class_id] ?? 'Unknown Subclass';
            $className = $subclassIdToClassName[$subclass->sub_class_id] ?? 'Unknown Class';

            $groupKey = $subclassName . ' - ' . $className . ' - Unknown Teacher - ' . $subclass->course;

            if (!isset($groupedData['Unknown'][$groupKey])) {
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

                $groupedData['Unknown'][$groupKey] = [
                    'class_name' => $className,
                    'subclass_id' => $subclass->sub_class_id,
                    'learning_id' => $learningId,
                    'course_name' => $courseName,
                    'course_id' => $courseId,
                    'teacher' => 'Unknown Teacher',
                    'waliKelasImage' => null,
                    'waliKelasName' => 'No Wali Kelas',
                    'subclassName' => $subclassName,
                    'materials' => [],
                    'assignments' => [],
                    'class_exams' => [],
                ];
            }

            // Add materials, assignments, and exams for this subclass
            $groupedData['Unknown'][$groupKey]['materials'] = array_merge(
                $groupedData['Unknown'][$groupKey]['materials'],
                array_filter($materials->data ?? [], fn($material) => $material->learning_id == $learningId)
            );

            $groupedData['Unknown'][$groupKey]['assignments'] = array_merge(
                $groupedData['Unknown'][$groupKey]['assignments'],
                array_filter($assignments->data ?? [], fn($assignment) => $assignment->learning_id == $learningId)
            );

            $groupedData['Unknown'][$groupKey]['class_exams'] = array_merge(
                $groupedData['Unknown'][$groupKey]['class_exams'],
                array_filter($class_exams->data ?? [], fn($classExam) => $classExam->learning_id == $learningId)
            );
        }

        $uniqueCourses = [];
        $seenCourseIds = [];

        foreach ($learnings->data ?? [] as $learning) {
            if (!isset($seenCourseIds[$learning->course->id])) {
                $uniqueCourses[] = [
                    'id' => $learning->id,
                    'teacher_id' => $learning->teacher_id,
                    'course' => [
                        'id' => $learning->course->id,
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
        // dd($groupedData);

        return view('teacher.pengajar.pembelajaran.index', [
            'groupedData' => $groupedData,
            'courses' => $uniqueCourses ?? [],
        ]);
    }
}
