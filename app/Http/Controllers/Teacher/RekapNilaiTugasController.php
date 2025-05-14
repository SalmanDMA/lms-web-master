<?php

namespace App\Http\Controllers\Teacher;

use App\Exports\TugasSubmissionExport;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Imports\TugasSubmissionImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class RekapNilaiTugasController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_tugas(Request $request, $learning_id)
    {
        $this->authorizeTeacher();

        $data =  $this->prepareTugasDataToSend($learning_id);

        return view('teacher.pengajar.rekap.tugas.index', $data);
    }

    public function v_detail_tugas_submission(Request $request, $learning_id, $tugas_id)
    {
        $this->authorizeTeacher();

        $data = $this->prepareTugasDataToSend($learning_id, $tugas_id);

        return view('teacher.pengajar.rekap.tugas.submission.index', $data);
    }

    public function simpan_nilai_tugas(Request $request, $learning_id, $tugas_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'knowledge.*' => 'nullable',
            'skills.*' => 'nullable',
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
                'knowledge' => $request->input("knowledge.$gradeId"),
                'skills' => $request->input("skills.$gradeId"),
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

    public function import_tugas_submission(Request $request, $learning_id, $tugas_id)
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

        Excel::import(new TugasSubmissionImport($learning_id, $tugas_id), $file);

        return redirect()->back()->with('message', 'Data berhasil diimpor')->with('alertClass', 'alert-success');
    }

    public function export_tugas_submission(Request $request, $learning_id, $tugas_id)
    {
        $this->authorizeTeacher();

        return Excel::download(new TugasSubmissionExport($tugas_id), 'rekap_nilai_tugas.xlsx');
    }

    private function prepareTugasDataToSend($learning_id, $tugas_id = null)
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

        $assignments = $tugas_id ? $this->fetchData('/api/v1/mobile/teacher/assignment/' . $tugas_id) : $this->fetchData('/api/v1/mobile/teacher/assignment');
        $filteredAssignments = $tugas_id ? $assignments->data ?? [] : collect($assignments->data ?? [])->filter(function ($assignment) use ($learning_id) {
            return $assignment->learning_id == $learning_id;
        });

        $submissions = $this->fetchData('/api/v1/mobile/teacher/submission');
        $filteredSubmissions = collect($submissions->data ?? [])->filter(function ($submission) use ($learning_id, $tugas_id) {
            return $submission->assignment->learning_id == $learning_id && $submission->assignment->id == $tugas_id;
        });

        return [
            'learning' => $learning->data ?? null,
            'subclasses' => $nameSubclass,
            'assignments' => $filteredAssignments,
            'learning_id' => $learning_id,
            'submissions' => $filteredSubmissions
        ];
    }
}
