<?php

namespace App\Http\Controllers\API\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use App\Http\Traits\UserRegistrationTrait;
use App\Models\ExamTeacher;
use App\Models\Major;
use App\Models\PersonalAccessToken;
use App\Models\School;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use CommonTrait, UserRegistrationTrait;

    public function notAuthorized(Request $request)
    {
        // $token = $request->bearerToken();

        // if ($token) {
        //     $tokenModel = PersonalAccessToken::findToken($token);

        //     if ($tokenModel) {
        //         if ($tokenModel->expires_at && Carbon::now()->gt($tokenModel->expires_at)) {
        //             return $this->sendError('Token ini telah kedaluwarsa, silakan login kembali', null, 200);
        //         }
        //     }
        // }

        return $this->sendError('Anda tidak diizinkan untuk mengakses ini', null, 200);
    }

    public function register(Request $request)
    {
        $validated = $this->validateUser($request, []);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $user = $this->saveUser($validated, $request);

        $this->registerOrUpdateRole($request, $user);

        return $this->sendResponse($user, 'Pendaftaran berhasil! Silakan masuk untuk melanjutkan', 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'email' => 'sometimes|required_without_all:nisn,nip|email',
            'password' => 'required|string',
            'nisn' => 'sometimes|required_without_all:email,nip|string',
            'nip' => 'sometimes|required_without_all:email,nisn|string',
        ], [
            'email.required_without_all' => 'Ups! Anda Belum Memasukkan Email, NISN, atau NIP.',
            'password.required' => 'Ups! Anda Belum Memasukkan Kata Sandi.',
            'nisn.required_without_all' => 'Ups! Anda Belum Memasukkan NISN, Email, atau NIP.',
            'nip.required_without_all' => 'Ups! Anda Belum Memasukkan NIP, Email, atau NISN.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $credentials = $request->only('email', 'password');
        $user = null;
        $role_teacher = null;
        $role = null;
        $abilities = [];

        if (isset($request->nisn)) {
            $student = Student::where('nisn', $request->nisn)->first();
            if ($student) {
                $user = $student->user;
                $role = 'STUDENT';
                $abilities = ['student'];
            }
        } elseif (isset($request->nip)) {
            $staffOrTeacher = Staff::where('nip', $request->nip)->first() ?? Teacher::where('nip', $request->nip)->first();
            if ($staffOrTeacher) {
                $user = User::where('id', $staffOrTeacher->user_id)->first();
                $role = $staffOrTeacher instanceof Staff ? 'STAFF' : 'TEACHER';

                if ($staffOrTeacher instanceof Staff) {
                    if ($staffOrTeacher->authority == 'ADMIN') {
                        $abilities = ['staff_administrator'];
                    } elseif ($staffOrTeacher->authority == 'KURIKULUM') {
                        $abilities = ['staff_curriculum'];
                    }
                } else {
                    $abilities = ['teacher'];
                    $role_teacher = ExamTeacher::where('teacher_id', $staffOrTeacher->id)->first();
                }
            }
        } elseif (isset($credentials['email'])) {
            $user = User::where('email', $credentials['email'])->first();
            if ($user) {
                if ($user->role === 'STUDENT') {
                    $role = 'STUDENT';
                    $abilities = ['student'];
                } elseif ($user->role === 'STAFF') {
                    $role = 'STAFF';
                    $abilities = $user->is_staff->authority == 'ADMIN' ? ['staff_administrator'] : ['staff_curriculum'];
                } elseif ($user->role === 'TEACHER') {
                    $role = 'TEACHER';
                    $abilities = ['teacher'];
                    $teacher = Teacher::where('user_id', $user->id)->first();
                    $role_teacher = ExamTeacher::where('teacher_id', $teacher->id)->first();
                }
            } else {
                $user = School::where('admin_email', $credentials['email'])->first();
                if ($user) {
                    $role = 'ADMIN';
                    $abilities = ['admin', 'staff_administrator', 'student', 'teacher', 'staff_curriculum'];
                }
            }
        }

        if (!$user) {
            return $this->sendError('Ups, Identitas Tidak Ditemukan. Coba Lagi Ya', null, 200);
        }

        $passwordCheck = $role === 'ADMIN'
            ? isset($user->admin_password) && Hash::check($credentials['password'], $user->admin_password)
            : isset($user->password) && Hash::check($credentials['password'], $user->password);

        if (!$passwordCheck) {
            return $this->sendError('Ups, Kata Sandi Salah. Coba Lagi Ya', null, 200);
        }

        if ($role === null || ($role !== 'ADMIN' && $user->role !== $role)) {
            return $this->sendError('Ups! Anda Tidak Memiliki Akses Untuk Halaman Ini', null, 200);
        }

        if ($role !== 'ADMIN') {
            $user->is_premium_school = School::where('id', $user->school_id)->first()->is_premium;
        }

        if ($role === 'TEACHER') {
            $user->role_teacher = $role_teacher ? $role_teacher->role : null;
        }

        $tokenResult = $user->createToken('auth_token', $abilities);
        $token = $tokenResult->plainTextToken;

        return $this->sendResponse(['token' => $token, 'user' => $user], 'Login berhasil.');
    }


    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Ups, Form Ini Belum Terisi',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        $otp = rand(100000, 999999);
        PasswordReset::updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($otp), 'created_at' => now()]
        );

        Mail::to($request->email)->send(new SendOtpMail($otp));

        return $this->sendResponse(null, 'OTP sudah dikirim ke ' . $request->email . '. Silakan cek email Anda.');
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ], [
            'email.required' => 'Ups! Kode verifikasi belum diisi',
            'otp.required' => 'Ups! Kode verifikasi belum diisi',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $passwordReset = PasswordReset::where('email', $request->email)->first();
        if (!$passwordReset || !Hash::check($request->otp, $passwordReset->token)) {
            return $this->sendError('Ups! Kode verifikasi tidak cocok', null, 200);
        }

        return $this->sendResponse(null, 'OTP benar');
    }

    public function forgotPassword(Request $request)
    {

        if (empty($request->all())) {
            return $this->sendError('Ups, Form Ini Belum Terisi', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'new_password' => 'required',
        ], [
            'emaiil.required' => 'Ups, Anda Belum Melengkapi Form',
            'new_password.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        $passwordReset = PasswordReset::where('email', $request->email)->first();
        if (!$passwordReset) {
            return $this->sendError('Verifikasi OTP terlebih dahulu', null, 200);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        PasswordReset::where('email', $request->email)->delete();

        return $this->sendResponse(null, 'Reset kata sandi berhasil! Silakan masuk untuk melanjutkan');
    }

    public function updateProfileStaff(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'phone' => 'required|unique:users,phone,' . $userLogin->id,
            'address' => 'nullable',
        ], [
            'fullname.required' => 'Ups, Anda Belum Melengkapi Form',
            'phone.required' => 'Ups, Anda Belum Melengkapi Form',
            'phone.unique' => 'Nomor telepon ini sudah terdaftar',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $staff = User::where('id', $userLogin->id)->first();
        if (!$staff) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        $staff->fullname = $request->fullname;
        $staff->phone = $request->phone;
        $staff->address = $request->address;
        $staff->save();

        return $this->sendResponse($staff, 'Update Profil Staff Berhasil');
    }


    public function updateProfileTeacher(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $userLogin->id,
            'religion' => 'nullable|string|max:255',
            'image_path' => 'nullable',
            'phone' => 'required|string|unique:users,phone,' . $userLogin->id,
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
            return $this->failsValidate($validator->errors());
        }

        $teacher = User::where('id', $userLogin->id)->first();
        if (!$teacher) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        $teacher->email = $request->email;
        $teacher->phone = $request->phone;
        $teacher->religion = $request->religion;
        $teacher->gender = $request->gender;
        $teacher->address = $request->address;

        if ($request->hasFile('image_path')) {
            if ($teacher->image_path) {
                $this->removeFile($teacher->image_path);
            }

            $img = $this->uploadFile($request->file('image_path'), 'user');
            if ($img) {
                $teacher->image_path = $img['path'];
            }
        } else if ($request->filled('image_path')) {
            $teacher->image_path = $request->image_path;
        }

        $teacher->save();

        return $this->sendResponse($teacher, 'Update Profil Guru Berhasil');
    }


    public function updateProfileStudent(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'fullname' => 'nullable|string',
            'nisn' => 'nullable|string',
            'phone' => 'nullable|string|unique:users,phone,' . $userLogin->id,
            'email' => 'nullable|email|unique:users,email,' . $userLogin->id,
            'religion' => 'nullable|string',
            'address' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $user = User::find($userLogin->id);
        if (!$user) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        $student = Student::where('user_id', $userLogin->id)->first();
        if (!$student) {
            return $this->sendError('Data Siswa Tidak Ditemukan', null, 200);
        }

        $user->fullname = $request->fullname ?? $user->fullname;
        $user->phone = $request->phone ?? $user->phone;
        $user->email = $request->email ?? $user->email;
        $user->religion = $request->religion ?? $user->religion;
        $user->address = $request->address ?? $user->address;

        if ($request->hasFile('image_path')) {
            if ($user->image_path) {
                $this->removeFile($user->image_path);
            }

            $img = $this->uploadFile($request->file('image_path'), 'user');
            if ($img) {
                $user->image_path = $img['path'];
            }
        }

        $user->save();

        $student->nisn = $request->nisn ?? $student->nisn;
        $student->save();

        return $this->sendResponse([
            'user' => $user,
            'student' => $student,
        ], 'Update Profil Siswa Berhasil');
    }

    public function resetPassword(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Ups, Anda Belum Melengkapi Form',
            'new_password.required' => 'Ups, Anda Belum Melengkapi Form',
            'new_password.confirmed' => 'Konfirmasi Password Tidak Sesuai',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $user = User::where('id', $userLogin->id)->first();
        if (!$user) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->sendError('Password lama tidak sesuai', null, 200);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->sendResponse(null, 'Reset kata sandi berhasil! Silakan masuk untuk melanjutkan');
    }

    public function myProfile()
    {
        $user = auth()->user();

        $school = School::where('id', $user->school_id)->first();
        $user->school = $school;

        return $this->sendResponse($user, 'Profilku');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Logout berhasil');
    }
}
