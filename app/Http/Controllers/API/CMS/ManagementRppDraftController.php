<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\RppDraft;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementRppDraftController extends Controller
{
    use CommonTrait;

    public function index_rpp(Request $request, $rpp_id)
    {
        $userlogin = auth()->user();
        $search = $request->query('search');

        $query = RppDraft::where('rpp_id', $rpp_id);

        if ($userlogin->role === 'TEACHER') {
            $query->where('teacher_id', $userlogin->is_teacher->id);
        }

        if ($search) {
            $query->where('draft_name', 'like', '%' . $search . '%');
        }

        $drafts = $query->get();

        if ($drafts->isEmpty()) {
            return $this->sendError('Data draft RPP tidak ditemukan.', [], 200);
        }

        $drafts->load(['teacher', 'subject_matters', 'rpp', 'rpp_bank']);

        return $this->sendResponse($drafts, 'Data draft RPP ditemukan.');
    }

    public function detail_rpp($rpp_id, $draft_id)
    {
        $userlogin = auth()->user();

        $draft = null;

        if ($userlogin->role === 'TEACHER') {
            $draft = RppDraft::where('rpp_id', $rpp_id)->where('teacher_id', $userlogin->is_teacher->id)->find($draft_id);
        } else {
            $draft = RppDraft::where('rpp_id', $rpp_id)->find($draft_id);
        }

        if (!$draft) {
            return $this->sendError('Detail draft RPP tidak ditemukan.', [], 404);
        }

        $draft->load(['teacher', 'subject_matters', 'rpp', 'rpp_bank']);

        return $this->sendResponse($draft, 'Detail draft RPP ditemukan.');
    }

    public function index_rpp_bank(Request $request, $rpp_bank_id)
    {
        $userlogin = auth()->user();
        $search = $request->query('search');

        $query = RppDraft::where('rpp_bank_id', $rpp_bank_id);

        if ($userlogin->role === 'TEACHER') {
            $query->where('teacher_id', $userlogin->is_teacher->id);
        }

        if ($search) {
            $query->where('draft_name', 'like', '%' . $search . '%');
        }

        $drafts = $query->get();

        if ($drafts->isEmpty()) {
            return $this->sendError('Data draft RPP tidak ditemukan.', [], 200);
        }

        $drafts->load(['teacher', 'subject_matters', 'rpp', 'rpp_bank']);

        return $this->sendResponse($drafts, 'Data draft RPP ditemukan.');
    }

    public function detail_rpp_bank($rpp_bank_id, $draft_id)
    {
        $userlogin = auth()->user();

        $query = RppDraft::where('rpp_bank_id', $rpp_bank_id);

        if ($userlogin->role === 'TEACHER') {
            $query->where('teacher_id', $userlogin->is_teacher->id);
        }

        $draft = $query->find($draft_id);

        if (!$draft) {
            return $this->sendError('Detail draft RPP tidak ditemukan.', [], 404);
        }

        $draft->load(['teacher', 'subject_matters', 'rpp', 'rpp_bank']);

        return $this->sendResponse($draft, 'Detail draft RPP ditemukan.');
    }

    public function store_rpp(Request $request, $rpp_id)
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

        $generateIdDraft = IdGenerator::generate(['table' => 'rpp_draft', 'length' => 16, 'prefix' => 'RPPD-']);

        $draft = RppDraft::create([
            'id' => $generateIdDraft,
            'teacher_id' => $teacherId,
            'courses' => $request->courses,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'rpp_id' => $rpp_id,
        ]);

        return $this->sendResponse($draft, 'Draft RPP Berhasil.');
    }

    public function store_rpp_bank(Request $request, $rpp_bank_id)
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

        $generateIdDraft = IdGenerator::generate(['table' => 'rpp_draft', 'length' => 16, 'prefix' => 'RPPD-']);

        $draft = RppDraft::create([
            'id' => $generateIdDraft,
            'teacher_id' => $teacherId,
            'courses' => $request->courses,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'rpp_bank_id' => $rpp_bank_id,
        ]);

        return $this->sendResponse($draft, 'Draft RPP Berhasil.');
    }

    public function update_rpp(Request $request, $rpp_id, $draft_id)
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

        $draft = null;

        if ($userlogin->role === 'TEACHER') {
            $draft = RppDraft::where('rpp_id', $rpp_id)->where('teacher_id', $userlogin->is_teacher->id)->find($draft_id);
        } else {
            $draft = RppDraft::where('rpp_id', $rpp_id)->find($draft_id);
        }

        if (!$draft) {
            return $this->sendError('Detail draft RPP tidak ditemukan.', [], 200);
        }

        if ($userlogin->role === 'TEACHER' && $userlogin->is_teacher->id !== $draft->teacher_id) {
            return $this->sendError('Anda tidak memiliki izin untuk mengubah draft RPP ini.', [], 200);
        }

        $draft->update([
            'teacher_id' => $teacherId,
            'courses' => $request->courses,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        return $this->sendResponse($draft, 'Berhasil memperbarui Draft RPP.');
    }

    public function update_rpp_bank(Request $request, $rpp_bank_id, $draft_id)
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

        $draft = null;

        if ($userlogin->role === 'TEACHER') {
            $draft = RppDraft::where('rpp_bank_id', $rpp_bank_id)->where('teacher_id', $userlogin->is_teacher->id)->find($draft_id);
        } else {
            $draft = RppDraft::where('rpp_bank_id', $rpp_bank_id)->find($draft_id);
        }

        if (!$draft) {
            return $this->sendError('Detail draft RPP Bank tidak ditemukan.', [], 200);
        }

        if ($userlogin->role === 'TEACHER' && $userlogin->is_teacher->id !== $draft->teacher_id) {
            return $this->sendError('Anda tidak memiliki izin untuk mengubah draft RPP Bank ini.', [], 200);
        }

        $draft->update([
            'teacher_id' => $teacherId,
            'courses' => $request->courses,
            'class_level' => $request->class_level,
            'draft_name' => $request->draft_name,
            'status' => $request->status,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
        ]);

        return $this->sendResponse($draft, 'Berhasil memperbarui Draft RPP Bank.');
    }

    public function update_rpp_status(Request $request, $rpp_id, $draft_id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }
        $draft = RppDraft::where('rpp_id', $rpp_id)->find($draft_id);

        if (!$draft) {
            return $this->sendError('Detail draft RPP tidak ditemukan.', [], 200);
        }

        $draft->update([
            'status' => $request->status,
        ]);

        return $this->sendResponse($draft, 'Berhasil memperbarui Draft RPP.');
    }

    public function update_rpp_bank_status(Request $request, $rpp_bank_id, $draft_id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $draft = RppDraft::where('rpp_bank_id', $rpp_bank_id)->find($draft_id);

        if (!$draft) {
            return $this->sendError('Detail draft RPP Bank tidak ditemukan.', [], 200);
        }

        $draft->update([
            'status' => $request->status,
        ]);

        return $this->sendResponse($draft, 'Berhasil memperbarui Draft RPP Bank.');
    }

    public function destroy_rpp($rpp_id, $draft_id)
    {
        $userlogin = auth()->user();

        $rpp = null;

        if ($userlogin->role === 'TEACHER') {
            $rpp = RppDraft::where('rpp_id', $rpp_id)->where('teacher_id', $userlogin->is_teacher->id)->find($draft_id);
        } else {
            $rpp = RppDraft::where('rpp_id', $rpp_id)->find($draft_id);
        }

        if (!$rpp) {
            return $this->sendError('Detail draft RPP tidak ditemukan.', [], 200);
        }

        if ($userlogin->role === 'TEACHER' && $userlogin->is_teacher->id !== $rpp->teacher_id) {
            return $this->sendError('Anda tidak memiliki izin untuk menghapus draft RPP ini.', [], 200);
        }

        $rpp->delete();

        return $this->sendResponse($rpp, 'Berhasil menghapus Draft RPP.');
    }

    public function destroy_rpp_bank($rpp_bank_id, $draft_id)
    {
        $userlogin = auth()->user();

        $draft = null;

        if ($userlogin->role === 'TEACHER') {
            $draft = RppDraft::where('rpp_bank_id', $rpp_bank_id)->where('teacher_id', $userlogin->is_teacher->id)->find($draft_id);
        } else {
            $draft = RppDraft::where('rpp_bank_id', $rpp_bank_id)->find($draft_id);
        }

        if (!$draft) {
            return $this->sendError('Detail draft RPP Bank tidak ditemukan.', [], 200);
        }

        if ($userlogin->role === 'TEACHER' && $userlogin->is_teacher->id !== $draft->teacher_id) {
            return $this->sendError('Anda tidak memiliki izin untuk menghapus draft RPP Bank ini.', [], 200);
        }

        $draft->delete();

        return $this->sendResponse($draft, 'Berhasil menghapus Draft RPP Bank.');
    }
}
