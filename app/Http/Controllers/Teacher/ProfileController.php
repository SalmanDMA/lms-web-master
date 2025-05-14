<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_profile()
    {
        $this->authorizeTeacher();

        $me = $this->fetchData('/api/v1/auth/profile/me');
        $religions = $this->generateReligions();

        return view('teacher.pengaturan.profile.index', [
            'user' => $me->data ?? null,
            'religions' => $religions,
        ]);
    }

    public function update_profile_general(Request $request)
    {
        $this->authorizeTeacher();
        $response = $this->fetchData('/api/v1/auth/profile/me');
        $me = $response->data;


        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $me->id,
            'religion' => 'nullable|string|max:255',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'phone' => 'required|string|unique:users,phone,' . $me->id,
            'gender' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ], [
            'email.required' => 'Ups, Anda Belum Melengkapi Form',
            'email.email' => 'Ups, Anda Belum Melengkapi Form',
            'phone.required' => 'Ups, Anda Belum Melengkapi Form',
            'email.unique' => 'Email ini sudah digunakan. Coba dengan email lain.',
            'phone.unique' => 'Nomor telepon ini sudah digunakan. Coba dengan nomor lain.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $form_data = [
            'email' => $request->email,
            'religion' => $request->religion,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'address' => $request->address,
        ];

        $image_path = $this->handleAvatar($request, $me);

        if ($image_path) {
            $form_data['image_path'] = $image_path;
        }

        $response_data = $this->putData('/api/v1/auth/profile/teacher', $form_data, 'json');

        return $this->handleProfileResponse($response_data, 'general');
    }

    public function update_profile_password(Request $request)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Ups, Anda Belum Melengkapi Form',
            'new_password.required' => 'Ups, Anda Belum Melengkapi Form',
            'new_password.confirmed' => 'Konfirmasi Password Tidak Sesuai',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $response_data = $this->putData('/api/v1/auth/reset-password', [
            'old_password' => $request->old_password,
            'new_password' => $request->new_password,
            'new_password_confirmation' => $request->new_password_confirmation,
        ], 'json');

        return $this->handleProfileResponse($response_data, 'password');
    }

    private function handleAvatar(Request $request, $user)
    {
        if ($request->hasFile('image_path')) {
            if ($user->image_path) {
                $filePath = str_replace('storage/public/', 'public/', $user->image_path);
                $this->removeFile($filePath);
            }

            $img = $this->uploadFile($request->file('image_path'), 'user');
            if ($img) {
                return $img['path'];
            }
        }
    }

    private function handleProfileResponse($response_data, $type)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $religions = $this->generateReligions();

        $me = $this->fetchData('/api/v1/auth/profile/me');

        if ($response_data->success) {
            return redirect()->route('teacher.v_pengaturan.profile')
                ->with('message', $message)
                ->with('alertClass', $alertClass)
                ->with('user', $me->data ?? null)
                ->with('religions', $religions);
        } else {
            return redirect()->back()
                ->withInput()
                ->withErrors(['message' => $message])
                ->with('alertClass', $alertClass)
                ->with('user', $me->data ?? null)
                ->with('religions', $religions);
        }
    }

    private function getResponseMessage($success, $type, $apiMessage)
    {
        $operation = match ($type) {
            'general' => 'mengubah profil',
            'password' => 'mengubah password',
            default => '',
        };

        return $success ? "Berhasil $operation." : "Gagal $operation. $apiMessage";
    }
}
