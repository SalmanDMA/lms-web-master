<?php

namespace App\Http\Controllers\Teacher;

use App\Exports\UlanganSubmissionExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Imports\UlanganSubmissionImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class RekapNilaiUlanganController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_ulangan(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $data =  $this->prepareUlanganDataToSend($learning_id);

        return view('teacher.pengajar.rekap.ulangan.index', $data);
    }

    public function v_detail_ulangan_submission(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $data = $this->prepareUlanganDataToSend($learning_id, $ulangan_id);

        $response_student = $this->fetchData('/api/v1/cms/teacher/' . $ulangan_id . '/response');
        $response_student_data = collect($response_student->data ?? []);

        $grouped_by_student = $response_student_data->groupBy('student_id');

        $latest_responses = $grouped_by_student->map(function ($responses) {
            $latestResponse = $responses->sortBy('created_at')->first();
            $mainGrade = collect($latestResponse->grades)->firstWhere('is_main', true);
            if (!$mainGrade) {
                $latestResponseWithMainGrade = $responses->first(function ($response) {
                    return collect($response->grades)->firstWhere('is_main', true);
                });

                return $latestResponseWithMainGrade;
            }
            return $latestResponse;
        })->values();

        $data['response_student'] = $latest_responses;

        return view('teacher.pengajar.rekap.ulangan.submission.index', $data);
    }


    public function simpan_nilai_ulangan(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'exam.*' => 'nullable',
            'publication_status.*' => 'required',
        ], [
            'publication_status.*.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $grades = [];
        $success = true;
        $errors = [];

        foreach ($request->input('publication_status', []) as $gradeId => $publication) {
            $grades[$gradeId] = [
                'exam' => $request->input("exam.$gradeId"),
                'publication_status' => $publication,
            ];
        }

        foreach ($grades as $gradeId => $grade) {
            if (!empty($gradeId)) {
                try {
                    $response = $this->putData('/api/v1/mobile/teacher/update-rekap/' . $gradeId, $grade, 'json');

                    if (!$response || !$response->success) {
                        $success = false;
                        $errors[] = "Gagal memperbarui nilai dengan ID: $gradeId";
                    }
                } catch (\Exception $e) {
                    $success = false;
                    $errors[] = "Terjadi kesalahan saat memproses nilai dengan ID: $gradeId. Error: " . $e->getMessage();
                }
            }
        }

        if ($success) {
            return redirect()->back()->with('message', 'Nilai berhasil diperbarui')->with('alertClass', 'alert-success');
        } else {
            return redirect()->back()->withErrors($errors)->with('alertClass', 'alert-danger');
        }
    }

    public function import_ulangan_submission(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $file = $request->file('file');

        if (!in_array($file->getClientOriginalExtension(), ['xlsx', 'xls'])) {
            return redirect()->back()->withErrors(['file' => 'Format file tidak valid.'])->withInput();
        }

        Excel::import(new UlanganSubmissionImport($learning_id, $ulangan_id), $file);

        return redirect()->back()->with('message', 'Data berhasil diimpor')->with('alertClass', 'alert-success');
    }

    public function export_ulangan_submission(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        return Excel::download(new UlanganSubmissionExport($ulangan_id), 'rekap_nilai_ulangan.xlsx');
    }

    private function prepareUlanganDataToSend($learning_id, $ulangan_id = null)
    {
        $learning = $this->fetchData('/api/v1/mobile/teacher/learning/' . $learning_id);
        $teacherSubclasses = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');

        $filteredSubclasses = collect($teacherSubclasses->data ?? [])->filter(function ($subclass) use ($learning) {
            return $subclass->course == $learning->data->course->id;
        });

        $subclasses = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $nameSubclass = $filteredSubclasses->map(function ($filteredSubclass) use ($subclasses) {
            return collect($subclasses->data ?? [])->firstWhere('id', $filteredSubclass->sub_class_id);
        })->values()->first();

        $class_exams = $ulangan_id ? $this->fetchData('/api/v1/cms/teacher/class-exam/' . $ulangan_id) : $this->fetchData('/api/v1/cms/teacher/class-exam');
        $filtered_class_exams = $ulangan_id ? $class_exams->data ?? [] : collect($class_exams->data ?? [])->filter(function ($class_exam) use ($learning) {
            return $class_exam->learning_id == $learning->data->id;
        })->values();

        return [
            'learning' => $learning->data ?? null,
            'subclasses' => $nameSubclass,
            'class_exam' => $filtered_class_exams,
            'learning_id' => $learning_id,
        ];
    }
}
