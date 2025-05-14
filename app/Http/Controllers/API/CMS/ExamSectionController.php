<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\ExamSection;
use App\Models\ExamTeacher;
use App\Models\Staff;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamSectionController extends Controller
{
    use CommonTrait;

    public function index()
    {
        $section = ExamSection::all();

        return $this->sendResponse($section, 'Daftar bagian ujian ditemukan.');
    }

    public function show($id)
    {
        $section = ExamSection::find($id);

        if (!$section) {
            return $this->sendError('Bagian ujian tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($section, 'Bagian ujian ditemukan.');
    }

    public function store(Request $request)
    {
        $userlogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:school_exams,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        if ($userlogin->role === 'STAFF') {
            $staffCurriculum = Staff::where('user_id', $userlogin->id)->first();

            if ($staffCurriculum->authority != "KURIKULUM") {
                return $this->sendError('Anda tidak memiliki akses sebagai staff kurikulum.', null, 200);
            }
        } else if ($userlogin->role === 'TEACHER') {
            $teacherPengelola = ExamTeacher::where('teacher_id', $userlogin->is_teacher->id)->first();

            if (!$teacherPengelola || $teacherPengelola->role != 'PENGELOLA') {
                return $this->sendError('Anda tidak memiliki akses sebagai pengelola ujian.', null, 200);
            }
        }

        $generateIdExamSection = IdGenerator::generate(['table' => 'exam_sections', 'length' => 16, 'prefix' => 'EXN-']);

        $section = ExamSection::create([
            'id' => $generateIdExamSection,
            'exam_id' => $request->exam_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->sendResponse($section, 'Bagian ujian berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $userlogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $section = ExamSection::find($id);

        if (!$section) {
            return $this->sendError('Bagian ujian tidak ditemukan.', null, 200);
        }

        if ($userlogin->role === 'STAFF') {
            $staffCurriculum = Staff::where('user_id', $userlogin->id)->first();

            if ($staffCurriculum->authority != "KURIKULUM") {
                return $this->sendError('Anda tidak memiliki akses sebagai staff kurikulum.', null, 200);
            }
        } else if ($userlogin->role === 'TEACHER') {
            $teacherPengelola = ExamTeacher::where('teacher_id', $userlogin->is_teacher->id)->first();

            if (!$teacherPengelola || $teacherPengelola->role != 'PENGELOLA') {
                return $this->sendError('Anda tidak memiliki akses sebagai pengelola ujian.', null, 200);
            }
        }

        $section->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->sendResponse($section, 'Bagian ujian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $section = ExamSection::find($id);

        if (!$section) {
            return $this->sendError('Bagian ujian tidak ditemukan.', null, 200);
        }

        $section->delete();

        return $this->sendResponse(null, 'Bagian ujian berhasil dihapus.');
    }
}
