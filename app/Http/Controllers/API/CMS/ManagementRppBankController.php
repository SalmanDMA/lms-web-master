<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\RppBank;
use App\Models\SubjectMatter;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementRppBankController extends Controller
{
    use CommonTrait;

    public function index(Request $request)
    {
        $userlogin = auth()->user();
        $search = $request->query('search');

        if ($userlogin->role === 'TEACHER') {
            $rpps = RppBank::where('teacher_id', $userlogin->is_teacher->id)->get();
            return $this->sendResponse($rpps, 'Berhasil mengambil semua data rpp.');
        }

        if ($search) {
            $rpps = RppBank::where('draft_name', 'like', '%' . $search . '%')->get();
            return $this->sendResponse($rpps, 'Berhasil mengambil semua data rpp.');
        }

        $rpps = RppBank::all();

        if ($rpps->isEmpty()) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        $rpps->load(['teacher', 'rpp_draft', 'subject_matters']);

        return $this->sendResponse($rpps, 'Berhasil mengambil semua data rpp bank.');
    }

    public function show($id)
    {
        $userlogin = auth()->user();

        if ($userlogin->role === 'TEACHER') {
            $rpp = RppBank::where('id', $id)->where('teacher_id', $userlogin->is_teacher->id)->first();
        } else {
            $rpp = RppBank::where('id', $id)->first();
        }

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        $rpp->load(['teacher', 'rpp_draft', 'subject_matters']);

        return $this->sendResponse($rpp, 'Berhasil mengambil data RPP bank');
    }

    public function store(Request $request)
    {
        $userlogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'courses' => 'required',
            'class_level' => 'required',
            'draft_name' => 'required',
            'status' => 'required',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.exists' => 'Ups, Id teacher tidak ditemukan',
        ]);

        if ($userlogin->role === 'TEACHER') {
            $request->merge(['teacher_id' => $userlogin->is_teacher->id]);
        }

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $generateIdRppBank = IdGenerator::generate(['table' => 'rpp_bank', 'length' => 16, 'prefix' => 'RPPB-']);

        $rpp = RppBank::create([
            'id' => $generateIdRppBank,
            'teacher_id' => $request->teacher_id,
            'courses' => $request->courses,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
        ]);

        return $this->sendResponse($rpp, 'Berhasil menambahkan data rpp bank');
    }

    public function update(Request $request, $id)
    {
        $userlogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'courses' => 'required',
            'class_level' => 'required',
            'draft_name' => 'required',
            'status' => 'required',
            'teacher_id' => 'required_if:userLogin.role,!=,TEACHER|exists:teachers,id',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.required_if' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.exists' => 'Ups, Id teacher tidak ditemukan',
        ]);

        if ($userlogin->role === 'TEACHER') {
            $request->merge(['teacher_id' => $userlogin->is_teacher->id]);
        }

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        if ($userlogin->role === 'TEACHER') {
            $rpp = RppBank::where('id', $id)->where('teacher_id', $userlogin->is_teacher->id)->first();
        } else {
            $rpp = RppBank::where('id', $id)->first();
        }

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        $rpp->update([
            'courses' => $request->courses,
            'teacher_id' => $request->teacher_id,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
        ]);

        return $this->sendResponse($rpp, 'Berhasil mengupdate data RPP bank');
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

        $rpp = RppBank::where('id', $id)->first();

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        $rpp->update([
            'status' => $request->status,
        ]);

        return $this->sendResponse($rpp, 'Berhasil mengupdate data RPP bank');
    }

    public function destroy($id)
    {
        $userlogin = auth()->user();

        if ($userlogin->role === 'TEACHER') {
            $rpp = RppBank::where('id', $id)->where('teacher_id', $userlogin->is_teacher->id)->first();
        } else {
            $rpp = RppBank::where('id', $id)->first();
        }

        if (!$rpp) {
            return $this->sendError('Data RPP tidak ditemukan.', [], 200);
        }

        $rpp->delete();

        return $this->sendResponse($rpp, 'Berhasil menghapus data RPP bank');
    }
}
