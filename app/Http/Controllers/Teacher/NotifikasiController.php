<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotifikasiController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function index()
    {
        $response = $this->fetchData('/api/v1/mobile/teacher/notification');

        $unreadNotifications = collect($response)->where('is_read', 0)->count();

        return view('teacher.notifikasi.index', [
            'unreadNotifications' => $unreadNotifications,
            'notifications' => $response->data ?? [],
        ]);
    }

    public function markAllRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $ids = $request->input('ids');

        if (is_string($ids)) {
            $decodedIds = json_decode($ids, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $ids = $decodedIds;
            } else {
                $ids = explode(',', trim($ids, '[]"'));
            }
        }

        if (!is_array($ids)) {
            return redirect()->back()->withErrors(['ids' => 'ID harus berupa array'])->withInput();
        }

        $success = true;
        $errors = [];

        foreach ($ids as $id) {
            if (!empty($id)) {
                try {
                    $response = $this->fetchData('/api/v1/mobile/teacher/notification/' . $id);

                    if (!$response || !$response->success) {
                        $success = false;
                        $errors[] = "Gagal memperbarui notifikasi dengan ID: $id";
                    }
                } catch (\Exception $e) {
                    $success = false;
                    $errors[] = "Terjadi kesalahan saat memproses notifikasi dengan ID: $id. Error: " . $e->getMessage();
                }
            }
        }

        if ($success) {
            return redirect()->route('notifications.index')
                ->with('message', 'Berhasil mengubah status notifikasi.')
                ->with('alertClass', 'alert-success');
        } else {
            return redirect()->route('notifications.index')
                ->withErrors($errors);
        }
    }
}
