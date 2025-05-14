<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\SubjectMatter;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectMatterRppDraftController extends Controller
{
    use CommonTrait;

    public function index($rpp_draft_id)
    {
        $subjectMatters = SubjectMatter::where('rpp_draft_id', $rpp_draft_id)->get();

        if ($subjectMatters->isEmpty()) {
            return $this->sendError('Data subject matter tidak ditemukan untuk RPP ini.', [], 200);
        }

        return $this->sendResponse($subjectMatters, 'Berhasil mengambil semua data subject matter untuk RPP.');
    }

    public function show($id)
    {
        $subjectMatter = SubjectMatter::find($id);

        if (!$subjectMatter) {
            return $this->sendError('Data subject matter tidak ditemukan.', [], 200);
        }

        return $this->sendResponse($subjectMatter, 'Berhasil mengambil data subject matter.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rpp_draft_id' => 'required|exists:rpp_draft,id',
            'title' => 'required',
            'time_allocation' => 'required|date_format:H:i',
            'learning_goals' => 'required',
            'learning_activity' => 'required',
            'grading' => 'required',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $generateIdSubjectMatter = IdGenerator::generate(['table' => 'subject_matters', 'length' => 16, 'prefix' => 'SUBM-']);

        $subjectMatter = SubjectMatter::create([
            'id' => $generateIdSubjectMatter,
            'rpp_draft_id' => $request->rpp_draft_id,
            'title' => $request->title,
            'time_allocation' => $request->time_allocation,
            'learning_goals' => $request->learning_goals,
            'learning_activity' => $request->learning_activity,
            'grading' => $request->grading,
        ]);

        return $this->sendResponse($subjectMatter, 'Berhasil menambahkan data subject matter untuk RPP.');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'time_allocation' => 'required|date_format:H:i',
            'learning_goals' => 'required',
            'learning_activity' => 'required',
            'grading' => 'required',
        ], [
            'required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $subjectMatter = SubjectMatter::where('id', $id)->first();

        if (!$subjectMatter) {
            return $this->sendError('Data subject matter tidak ditemukan untuk RPP ini.', [], 200);
        }

        $subjectMatter->update([
            'title' => $request->title,
            'time_allocation' => $request->time_allocation,
            'learning_goals' => $request->learning_goals,
            'learning_activity' => $request->learning_activity,
            'grading' => $request->grading,
        ]);

        return $this->sendResponse($subjectMatter, 'Berhasil mengupdate data subject matter untuk RPP.');
    }

    public function destroy($id)
    {
        $subjectMatter = SubjectMatter::where('id', $id)->first();

        if (!$subjectMatter) {
            return $this->sendError('Data subject matter tidak ditemukan untuk RPP ini.', [], 200);
        }

        $subjectMatter->delete();

        return $this->sendResponse($subjectMatter, 'Berhasil menghapus data subject matter untuk RPP.');
    }
}
