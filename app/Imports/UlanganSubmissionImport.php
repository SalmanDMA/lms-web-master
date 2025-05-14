<?php

namespace App\Imports;

use App\Models\ClassExam;
use App\Models\Grade;
use App\Models\Response;
use App\Models\Student;
use App\Models\TugasSubmission;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UlanganSubmissionImport implements ToModel, WithHeadingRow
{
    protected $learning_id;
    protected $ulangan_id;

    public function __construct($learning_id, $ulangan_id)
    {
        $this->learning_id = $learning_id;
        $this->ulangan_id = $ulangan_id;
    }

    public function model(array $row)
    {
        $student = Student::where('nisn', $row['nisn'])->first();

        $response = Response::where('student_id', $student->id)
            ->where('exam_id', $this->ulangan_id)
            ->first();

        if (!$response) {
            $response_data_count = Response::count() ?? 0;
            $responseId = 'RES-' . str_pad($response_data_count + 1, 12, '0', STR_PAD_LEFT);

            $response = Response::create([
                'id' => $responseId,
                'student_id' => $student->id,
                'exam_id' => $this->ulangan_id,
                'status' => 'pengerjaan',
            ]);
        }

        $grade = Grade::where('response_id', $response->id)->first();

        if (!$grade) {
            $grade_data_count = Grade::count() ?? 0;
            $gradeId = 'GRD-' . str_pad($grade_data_count + 1, 12, '0', STR_PAD_LEFT);

            Grade::create([
                'id' => $gradeId,
                'response_id' => $response->id,
                'class_exam' => $row['nilai_ulangan'],
                'status' => $row['status'],
                'publication_status' => $row['status_publikasi'],
            ]);
        } else {
            $grade->update([
                'class_exam' => $row['nilai_ulangan'],
                'status' => $row['status'],
                'publication_status' => $row['status_publikasi'],
            ]);
        }

        return $response;
    }
}
