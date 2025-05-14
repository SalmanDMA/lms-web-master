<?php

namespace App\Http\Traits;

use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Student;
use Haruncpi\LaravelIdGenerator\IdGenerator;

trait NotifiableTrait
{
    public function notifyStudents($courseId, $subClassId, $type, $notificationTitle)
    {
        $notificationTitle = ucfirst($type) . ' Mata Pelajaran ' . $notificationTitle;
        $message = 'Anda memiliki ' . strtolower($type) . ' baru dengan mata pelajaran "' . $notificationTitle . '".';

        // Ambil semua murid dari subkelas tertentu
        $studentsFromSubClass = Student::where('sub_class_id', $subClassId)->pluck('id')->toArray();

        // Ambil semua murid yang terdaftar dalam kursus tertentu
        $studentsFromEnrollment = Enrollment::where('course_id', $courseId)->pluck('student_id')->toArray();

        // Filter untuk murid yang memiliki ID yang sama di kedua array
        $studentIds = array_intersect($studentsFromSubClass, $studentsFromEnrollment);

        foreach ($studentIds as $studentId) {
            $existingNotification = Notification::where('student_id', $studentId)
                ->where('type', $type)
                ->where('title', $notificationTitle)
                ->first();

            if ($existingNotification) {
                $existingNotification->update([
                    'message' => $message,
                    'is_read' => false,
                ]);
            } else {
                $generateIdNotification = IdGenerator::generate(['table' => 'notifications', 'length' => 16, 'prefix' => 'NOT-']);

                Notification::create([
                    'id' => $generateIdNotification,
                    'student_id' => $studentId,
                    'teacher_id' => null,
                    'type' => $type,
                    'title' => $notificationTitle,
                    'message' => $message,
                    'is_read' => false,
                ]);
            }
        }
    }

    public function notifyTeacher($teacherId, $type, $notificationTitle)
    {
        $notificationTitle = ucfirst($type) . ' Mata Pelajaran ' . $notificationTitle;
        $message = ucfirst($type) . ' baru telah dibuat dengan mata pelajaran "' . $notificationTitle . '".';

        $existingNotification = Notification::where('teacher_id', $teacherId)
            ->where('type', $type)
            ->where('title', $notificationTitle)
            ->first();

        if ($existingNotification) {
            $existingNotification->update([
                'message' => $message,
                'is_read' => false,
            ]);
        } else {
            $generateIdNotification = IdGenerator::generate(['table' => 'notifications', 'length' => 16, 'prefix' => 'NOT-']);

            Notification::create([
                'id' => $generateIdNotification,
                'teacher_id' => $teacherId,
                'student_id' => null,
                'type' => $type,
                'title' => $notificationTitle,
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }
}
