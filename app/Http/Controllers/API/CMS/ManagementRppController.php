<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Rpp;
use App\Models\SubjectMatter;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementRppController extends Controller
{
    use CommonTrait;

    public function index(Request $request)
    {
        $userlogin = auth()->user();
        $search = $request->query('search');

        $query = Rpp::query();

        if ($userlogin->role === 'TEACHER') {
            $query->where('teacher_id', $userlogin->is_teacher->id);
        }

        if ($search) {
            $query->where('draft_name', 'like', '%' . $search . '%');
        }

        $rpps = $query->get();

        if ($rpps->isEmpty()) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        $rpps->load(['teacher', 'rpp_draft', 'subject_matters']);

        return $this->sendResponse($rpps, 'Berhasil mengambil semua data RPP.');
    }

    public function show($id)
    {
        $userlogin = auth()->user();

        $rpp = null;

        if ($userlogin->role === 'TEACHER') {
            $rpp = Rpp::where('id', $id)->where('teacher_id', $userlogin->is_teacher->id)->first();
        } else {
            $rpp = Rpp::find($id);
        }

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        if ($userlogin->role === 'TEACHER' && $rpp->teacher_id !== $userlogin->is_teacher->id) {
            return $this->sendError('Anda tidak memiliki izin untuk mengakses data ini.', [], 200);
        }

        $rpp->load(['teacher', 'rpp_draft', 'subject_matters']);

        return $this->sendResponse($rpp, 'Berhasil mengambil data RPP.');
    }


    public function store(Request $request)
    {
        $userlogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'courses' => 'required',
            'class_level' => 'required',
            'draft_name' => 'required',
            'status' => 'required',
            'academic_year' => 'required',
            'semester' => 'required',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.exists' => 'Ups, Id teacher tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $teacherId = $userlogin->role === 'TEACHER' ? $userlogin->is_teacher->id : $request->teacher_id;

        $generateIdRpp = IdGenerator::generate(['table' => 'rpp', 'length' => 16, 'prefix' => 'RPP-']);

        $rpp = Rpp::create([
            'id' => $generateIdRpp,
            'teacher_id' => $teacherId,
            'courses' => $request->courses,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        return $this->sendResponse($rpp, 'Berhasil menambahkan data RPP.');
    }

    public function update(Request $request, $id)
    {
        $userlogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'courses' => 'required',
            'class_level' => 'required',
            'draft_name' => 'required',
            'status' => 'required',
            'academic_year' => 'required',
            'semester' => 'required',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.exists' => 'Ups, Id teacher tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $rpp = null;

        if ($userlogin->role === 'TEACHER') {
            $rpp = Rpp::where('id', $id)->where('teacher_id', $userlogin->is_teacher->id)->first();
        } else {
            $rpp = Rpp::find($id);
        }

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        if ($userlogin->role === 'TEACHER' && $rpp->teacher_id !== $userlogin->is_teacher->id) {
            return $this->sendError('Anda tidak memiliki izin untuk mengupdate data ini.', [], 200);
        }

        $teacherId = $userlogin->role === 'TEACHER' ? $userlogin->is_teacher->id : $request->teacher_id;

        $rpp->update([
            'courses' => $request->courses,
            'teacher_id' => $teacherId,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        return $this->sendResponse($rpp, 'Berhasil memperbaharui data RPP.');
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }


        $rpp = Rpp::find($id);

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        $rpp->update([
            'status' => $request->status,
        ]);

        return $this->sendResponse($rpp, 'Berhasil memperbaharui data RPP.');
    }

    public function destroy($id)
    {
        $userlogin = auth()->user();

        $rpp = null;

        if ($userlogin->role === 'TEACHER') {
            $rpp = Rpp::where('id', $id)->where('teacher_id', $userlogin->is_teacher->id)->first();
        } else {
            $rpp = Rpp::where('id', $id)->first();
        }

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        if ($userlogin->role === 'TEACHER' && $rpp->teacher_id !== $userlogin->is_teacher->id) {
            return $this->sendError('Anda tidak memiliki izin untuk menghapus data ini.', [], 200);
        }

        $rpp->delete();

        return $this->sendResponse($rpp, 'Berhasil menghapus data RPP.');
    }
}
