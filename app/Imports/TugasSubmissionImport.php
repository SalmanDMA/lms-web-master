<?php

namespace App\Imports;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Submission;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TugasSubmissionImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected $learning_id;
    protected $tugas_id;

    public function __construct($learning_id, $tugas_id)
    {
        $this->learning_id = $learning_id;
        $this->tugas_id = $tugas_id;
    }

    public function model(array $row)
    {
        $student = Student::where('nisn', $row[0])->first();

        $submittedAt = Date::excelToDateTimeObject($row[7])->format('Y-m-d H:i:s');

        $submission = Submission::where('student_id', $student->id)
            ->where('assignment_id', $this->tugas_id)
            ->first();

        if (!$submission) {
            $submission_data_count = Submission::count() ?? 0;
            $submissionId = 'SMS-' . str_pad($submission_data_count + 1, 12, '0', STR_PAD_LEFT);

            $submission = Submission::create([
                'id' => $submissionId,
                'student_id' => $student->id,
                'assignment_id' => $this->tugas_id,
                'submission_content' => $row[5],
                'submission_note' => $row[6],
                'submitted_at' => $submittedAt,
                'feedback' => $row[8],
            ]);
        } else {
            $submission->update([
                'submission_content' => $row[5],
                'submission_note' => $row[6],
                'submitted_at' => $submittedAt,
                'feedback' => $row[8],
            ]);
        }

        $grade = Grade::where('submission_id', $submission->id)->first();

        if (!$grade) {
            $grade_data_count = Grade::count() ?? 0;
            $gradeId = 'GRD-' . str_pad($grade_data_count + 1, 12, '0', STR_PAD_LEFT);

            Grade::create([
                'id' => $gradeId,
                'submission_id' => $submission->id,
                'knowledge' => $row[1],
                'skills' => $row[2],
                'status' => $row[3],
                'publication_status' => $row[4],
            ]);
        } else {
            // Update grade jika sudah ada
            $grade->update([
                'knowledge' => $row[1],
                'skills' => $row[2],
                'status' => $row[3],
                'publication_status' => $row[4],
            ]);
        }

        return $submission;
    }
}
