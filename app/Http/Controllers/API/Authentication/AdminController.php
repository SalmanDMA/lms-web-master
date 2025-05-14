<?php

namespace App\Http\Controllers\API\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\PersonalAccessToken;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use CommonTrait;

    public function updateProfileAdmin(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required|unique:users,phone,' . $userLogin->id,
            'address' => 'nullable',
        ], [
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
            'phone.required' => 'Ups, Anda Belum Melengkapi Form',
            'phone.unique' => 'Nomor telepon ini sudah digunakan. Coba dengan nomor lain.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $admin = School::where('id', $userLogin->id)->first();
        if (!$admin) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        $admin->admin_name = $request->name;
        $admin->admin_phone = $request->phone;
        $admin->admin_address = $request->address;
        $admin->save();

        return $this->sendResponse($admin, 'Update Profil Admin Berhasil');
    }

    public function updateProfileSchool(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|unique:schools,phone_number,' . $userLogin->id,
            'email' => 'required|email|unique:schools,email,' . $userLogin->id,
            'address' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'school_image' => 'nullable||image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'structure' => 'nullable||image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'website' => 'nullable|string|max:255',
            'another_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'acreditation' => 'nullable',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'description' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'rw' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'pos' => 'nullable|integer',
        ], [
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
            'phone_number.required' => 'Ups, Anda Belum Melengkapi Form',
            'phone_number.unique' => 'Nomor telepon ini sudah digunakan. Coba dengan nomor lain.',
            'email.required' => 'Ups, Anda Belum Melengkapi Form',
            'email.email' => 'Ups, Anda Belum Melengkapi Form',
            'email.unique' => 'Email ini sudah digunakan. Coba dengan email lain.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $school = School::where('id', $userLogin->id)->first();
        if (!$school) {
            return $this->sendError('Ups, Akun Ini Belum Terdaftar', null, 200);
        }

        $logoUpdated = $request->hasFile('logo');
        $schoolImageUpdated = $request->hasFile('school_image');
        $stuctureUpdated = $request->hasFile('structure');

        $fields = [
            'name',
            'phone_number',
            'email',
            'address',
            'website',
            'another_name',
            'type',
            'status',
            'acreditation',
            'vision',
            'mission',
            'description',
            'country',
            'province',
            'city',
            'district',
            'neighborhood',
            'rw',
            'latitude',
            'longitude',
            'address',
            'pos'
        ];


        foreach ($fields as $field) {
            if ($request->has($field)) {
                $school->$field = $request->input($field);
            }
        }

        if ($logoUpdated) {
            if ($school->logo) {
                $this->removeFile($school->logo);
            }

            $logoPath = $this->uploadFile($request->file('logo'), 'schools');
            if ($logoPath) {
                $school->logo = $logoPath['path'];
            }
        }

        if ($schoolImageUpdated) {
            if ($school->school_image) {
                $this->removeFile($school->school_image);
            }
            $schoolImagePath = $this->uploadFile($request->file('school_image'), 'schools');
            if ($schoolImagePath) {
                $school->school_image = $schoolImagePath['path'];
            }
        }

        if ($stuctureUpdated) {
            if ($school->structure) {
                $this->removeFile($school->structure);
            }
            $structurePath = $this->uploadFile($request->file('structure'), 'schools');
            if ($structurePath) {
                $school->structure = $structurePath['path'];
            }
        }

        $school->save();

        return $this->sendResponse($school, 'Update Profil Sekolah Berhasil');
    }
}
