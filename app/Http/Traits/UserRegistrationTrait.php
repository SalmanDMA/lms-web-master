<?php

namespace App\Http\Traits;

use App\Http\Traits\CommonTrait;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

trait UserRegistrationTrait
{
    use CommonTrait;

    public function validateUser($request, $additionalRules = [])
    {
        if (empty($request->all())) {
            return $this->sendError('Ups! Tolong isi formulir di bawah ini', null, 200);
        }

        $userLogin = auth()->user();

        $rules = [
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($request->id ?? 'NULL') . ',id',
            'phone' => 'required|unique:users,phone,' . ($request->id ?? 'NULL') . ',id',
            'password' => 'nullable|string|confirmed',
        ];

        $messageErrors = [
            'fullname.required' => 'Ups! Lengkapi formulir di bawah ini',
            'email.required' => 'Ups! Lengkapi formulir di bawah ini',
            'phone.required' => 'Ups! Lengkapi formulir di bawah ini',
            'email.unique' => 'Anda sudah memiliki akun. Coba masuk',
            'phone.unique' => 'Anda sudah memiliki akun. Coba masuk',
            'password.confirmed' => 'Ups! Lengkapi formulir di bawah ini',
        ];

        $rules = array_merge($rules, $additionalRules);

        if ($userLogin->school_id == null) {
            $request->merge(['school_id' => $userLogin->id]);
        }

        if ($userLogin->role === 'STAFF') {
            $request->merge(['school_id' => $userLogin->school_id]);
        }

        if ($request->has('image_path')) {
            $request->merge(['image_path' => $request->file('image_path')]);
        }

        $validator = Validator::make($request->all(), $rules, $messageErrors);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        return $request->all();
    }

    public function saveUser($validated, $request, $user = null)
    {
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['role'])) {
            $validated['role'] = strtoupper($validated['role']);
        }

        if ($request->hasFile('image_path')) {
            if ($user && $user->image_path) {
                $this->removeFile($user->image_path);
            }

            $img = $this->uploadFile($request->file('image_path'), 'user');
            if ($img) {
                $validated['image_path'] = $img['path'];
            }
        }

        if ($user) {
            $user->update($validated);
        } else {
            $validated['id'] = $this->getUniqueId('users', 'USE-', 16);
            $user = User::create($validated);
        }

        return $user;
    }

    public function registerOrUpdateRole($request, $user)
    {
        $additionalData = [];

        switch ($request->role) {
            case 'student':
                $additionalData = $this->registerOrUpdateStudent($request, $user->id);
                break;
            case 'teacher':
                $additionalData = $this->registerOrUpdateTeacher($request, $user->id);
                break;
            case 'staff':
                $additionalData = $this->registerOrUpdateStaff($request, $user->id);
                break;
        }

        if ($additionalData instanceof JsonResponse) {
            return $additionalData;
        }

        return array_merge($request->all(), $additionalData);
    }

    public function registerOrUpdateStudent($request, $userId)
    {
        $student = Student::where('user_id', $userId)->first();
        $studentId = $student ? $student->id : 'NULL';

        $validator = Validator::make($request->all(), [
            'sub_class_id' => 'required|exists:sub_class,id',
            'nisn' => 'required|unique:students,nisn,' . $studentId . ',id'
        ], [
            'nisn.unique' => 'NISN sudah terdaftar',
            'nisn.required' => 'Ups! Lengkapi formulir di bawah ini',
            'sub_class_id.required' => 'Ups! Lengkapi formulir di bawah ini',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->only(['sub_class_id', 'nisn', 'major', 'type', 'year']);
        if (!$student) {
            $studentId = $this->getUniqueId('students', 'STU-', 16);
            $data['id'] = $studentId;
            $data['user_id'] = $userId;
        }

        Student::updateOrCreate(['user_id' => $userId], $data);

        return $data;
    }

    public function registerOrUpdateTeacher($request, $userId)
    {
        $teacher = Teacher::where('user_id', $userId)->first();
        $teacherId = $teacher ? $teacher->id : 'NULL';

        $validator = Validator::make($request->all(), [
            'nip' => 'required|unique:teachers,nip,' . $teacherId . ',id',
            'is_wali' => 'boolean'
        ], [
            'nip.unique' => 'NIP sudah terdaftar',
            'nip.required' => 'Ups! Lengkapi formulir di bawah ini',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->only(['nip', 'is_wali']);
        if (!$teacher) {
            $teacherId = $this->getUniqueId('teachers', 'TEA-', 16);
            $data['id'] = $teacherId;
            $data['user_id'] = $userId;
        }

        Teacher::updateOrCreate(['user_id' => $userId], $data);

        return $data;
    }

    public function registerOrUpdateStaff($request, $userId)
    {
        $staff = Staff::where('user_id', $userId)->first();
        $staffId = $staff ? $staff->id : 'NULL';

        $validator = Validator::make($request->all(), [
            'placement' => 'required',
            'nip' => 'required|unique:staffs,nip,' . $staffId . ',id',
            'authority' => 'required|in:ADMIN,KURIKULUM'
        ], [
            'nip.unique' => 'NIP sudah terdaftar',
            'nip.required' => 'Ups! NIP tidak boleh kosong',
            'placement.required' => 'Ups! placement tidak boleh kosong',
            'authority.required' => 'Ups! authority tidak boleh kosong',
            'authority.in' => 'Ups! authority harus ADMIN atau KURIKULUM',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->only(['placement', 'nip', 'authority']);
        if (!$staff) {
            $staffId = $this->getUniqueId('staffs', 'STA-', 16);
            $data['id'] = $staffId;
            $data['user_id'] = $userId;
            $data['authority'] = strtoupper($request->authority);
        }

        Staff::updateOrCreate(['user_id' => $userId], $data);

        return $data;
    }

    public function getUniqueId($table, $prefix, $length)
    {
        $newId = $this->generateUniqueId($table, $prefix, $length);

        while (DB::table($table)->where('id', $newId)->exists()) {
            $newId = $this->generateUniqueId($table, $prefix, $length);
        }

        return $newId;
    }

    public function generateUniqueId($table, $prefix, $length)
    {
        $latestId = DB::table($table)
            ->where('id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('id');

        if ($latestId) {
            $lastNumberPart = substr($latestId, strlen($prefix));
            $parts = explode('/', $lastNumberPart);
            $lastNumber = intval($parts[0]);
            $newNumber = $lastNumber + 1;

            $newId = $prefix . str_pad($newNumber, $length - strlen($prefix), '0', STR_PAD_LEFT);

            if ($table !== 'users' && count($parts) > 1) {
                $newId .= '/' . $parts[1];
            }
        } else {
            $newId = $prefix . str_pad('1', $length - strlen($prefix), '0', STR_PAD_LEFT);
        }

        return $newId;
    }
}
