<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Notification;

class NotificationController extends Controller
{
    use CommonTrait;

    public function index()
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'STUDENT') {
            $notifications = Notification::where('student_id', $userLogin->is_student->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($userLogin->role === 'TEACHER') {
            $notifications = Notification::where('teacher_id', $userLogin->is_teacher->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        return $this->sendResponse($notifications, 'Berhasil mengambil data notifikasi');
    }

    public function show($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'STUDENT') {
            $notification = Notification::where('id', $id)
                ->where('student_id', $userLogin->is_student->id)
                ->first();
        } elseif ($userLogin->role === 'TEACHER') {
            $notification = Notification::where('id', $id)
                ->where('teacher_id', $userLogin->is_teacher->id)
                ->first();
        } else {
            return $this->sendError('Anda tidak memiliki akses.', null, 200);
        }

        if ($notification) {
            $notification->is_read = true;
            $notification->save();

            return $this->sendResponse($notification, 'Berhasil mengambil data notifikasi');
        } else {
            return $this->sendError('Notifikasi tidak ditemukan.', null, 200);
        }
    }
}
