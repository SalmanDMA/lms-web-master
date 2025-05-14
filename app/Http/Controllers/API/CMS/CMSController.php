<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Setting;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CMSController extends Controller
{
    use CommonTrait;

    public function index()
    {
        $userLogin = auth()->user();

        $school_id = isset($userLogin->school_id) ? $userLogin->school_id : $userLogin->id;

        $cms = Setting::where('school_id', $school_id)->get();

        if ($cms->isEmpty()) {
            return $this->sendError('Setting tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($cms, 'Berhasil mengambil semua data setting.');
    }

    public function index_not_login()
    {
        $cms = Setting::first();

        if ($cms === null) {
            return $this->sendError('Setting tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($cms, 'Berhasil mengambil semua data setting.');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $school_id = isset($userLogin->school_id) ? $userLogin->school_id : $userLogin->id;

        $validator = Validator::make($request->all(), [
            'splash_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'splash_title' => 'required|string|max:255',
            'login_image_student' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'login_image_teacher' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'logo_thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            'primary_color' => 'required|string|max:255',
            'secondary_color' => 'required|string|max:255',
            'accent_color' => 'required|string|max:255',
            'white_color' => 'required|string|max:255',
            'black_color' => 'required|string|max:255',
        ], [
            'splash_logo.required' => 'Ups, Anda Belum Melengkapi Form',
            'splash_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'login_image_student.required' => 'Ups, Anda Belum Melengkapi Form',
            'login_image_teacher.required' => 'Ups, Anda Belum Melengkapi Form',
            'title.required' => 'Ups, Anda Belum Melengkapi Form',
            'logo.required' => 'Ups, Anda Belum Melengkapi Form',
            'logo_thumbnail.required' => 'Ups, Anda Belum Melengkapi Form',
            'primary_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'secondary_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'accent_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'white_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'black_color.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->all();

        $data['splash_logo'] = $this->uploadFile($request->file('splash_logo'), 'settings')['path'];
        $data['login_image_student'] = $this->uploadFile($request->file('login_image_student'), 'settings')['path'];
        $data['login_image_teacher'] = $this->uploadFile($request->file('login_image_teacher'), 'settings')['path'];
        $data['logo'] = $this->uploadFile($request->file('logo'), 'settings')['path'];
        $data['logo_thumbnail'] = $this->uploadFile($request->file('logo_thumbnail'), 'settings')['path'];

        $generateId = IdGenerator::generate(['table' => 'settings', 'length' => 16, 'prefix' => 'CMS-']);

        $data['id'] = $generateId;
        $data['school_id'] =  $school_id;

        $cms = Setting::create($data);

        return $this->sendResponse($cms, 'Berhasil menambahkan data setting', 201);
    }


    public function show($id)
    {
        $userLogin = auth()->user();
        $school_id = isset($userLogin->school_id) ? $userLogin->school_id : $userLogin->id;
        $academic = Setting::where('school_id',  $school_id)->find($id);

        if (!$academic) {
            return $this->sendError('Setting tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($academic, 'Berhasil menemukan data setting.');
    }

    public function update($id, Request $request)
    {
        $userLogin = auth()->user();
        $school_id = isset($userLogin->school_id) ? $userLogin->school_id : $userLogin->id;
        $cms = Setting::where('school_id',  $school_id)->find($id);

        if (!$cms) {
            return $this->sendError('Setting tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'splash_logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'splash_title' => 'required|string|max:255',
            'login_image_student' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'login_image_teacher' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required|string|max:255',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'logo_thumbnail' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primary_color' => 'required|string|max:255',
            'secondary_color' => 'required|string|max:255',
            'accent_color' => 'required|string|max:255',
            'white_color' => 'required|string|max:255',
            'black_color' => 'required|string|max:255',
        ], [
            'splash_logo.required' => 'Ups, Anda Belum Melengkapi Form',
            'splash_title.required' => 'Ups, Anda Belum Melengkapi Form',
            'login_image_student.required' => 'Ups, Anda Belum Melengkapi Form',
            'login_image_teacher.required' => 'Ups, Anda Belum Melengkapi Form',
            'title.required' => 'Ups, Anda Belum Melengkapi Form',
            'logo.required' => 'Ups, Anda Belum Melengkapi Form',
            'logo_thumbnail.required' => 'Ups, Anda Belum Melengkapi Form',
            'primary_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'secondary_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'accent_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'white_color.required' => 'Ups, Anda Belum Melengkapi Form',
            'black_color.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->all();

        if ($request->hasFile('splash_logo')) {
            $this->removeFile($cms->splash_logo);
            $data['splash_logo'] = $this->uploadFile($request->file('splash_logo'), 'settings')['path'];
        }
        if ($request->hasFile('login_image_student')) {
            $this->removeFile($cms->login_image_student);
            $data['login_image_student'] = $this->uploadFile($request->file('login_image_student'), 'settings')['path'];
        }
        if ($request->hasFile('login_image_teacher')) {
            $this->removeFile($cms->login_image_teacher);
            $data['login_image_teacher'] = $this->uploadFile($request->file('login_image_teacher'), 'settings')['path'];
        }
        if ($request->hasFile('logo')) {
            $this->removeFile($cms->logo);
            $data['logo'] = $this->uploadFile($request->file('logo'), 'settings')['path'];
        }
        if ($request->hasFile('logo_thumbnail')) {
            $this->removeFile($cms->logo_thumbnail);
            $data['logo_thumbnail'] = $this->uploadFile($request->file('logo_thumbnail'), 'settings')['path'];
        }

        $cms->update($data);

        return $this->sendResponse($cms, 'Berhasil mengubah data setting');
    }


    public function destroy($id)
    {
        $userLogin = auth()->user();
        $school_id = isset($userLogin->school_id) ? $userLogin->school_id : $userLogin->id;
        $cms = Setting::where('school_id',  $school_id)->find($id);

        if (!$cms) {
            return $this->sendError('Setting tidak ditemukan.', null, 200);
        }

        if ($cms->splash_logo) {
            $this->removeFile($cms->splash_logo);
        }
        if ($cms->login_image_student) {
            $this->removeFile($cms->login_image_student);
        }
        if ($cms->login_image_teacher) {
            $this->removeFile($cms->login_image_teacher);
        }
        if ($cms->logo) {
            $this->removeFile($cms->logo);
        }
        if ($cms->logo_thumbnail) {
            $this->removeFile($cms->logo_thumbnail);
        }

        $cms->delete();

        return $this->sendResponse($cms, 'Setting berhasil dihapus');
    }
}
