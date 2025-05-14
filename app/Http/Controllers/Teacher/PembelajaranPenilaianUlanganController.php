<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class PembelajaranPenilaianUlanganController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_penilaian(Request $request, $learning_id, $ulangan_id)
    {
        $this->authorizeTeacher();

        $data = $this->prepare_penilaian_data_to_send_view($learning_id, $ulangan_id);

        return view('teacher.pengajar.pembelajaran.ulangan.penilaian.index', $data);
    }

    public function v_penilaian_student(Request $request, $learning_id, $ulangan_id, $student_id)
    {
        $this->authorizeTeacher();

        $data = $this->prepare_penilaian_data_to_send_view($learning_id, $ulangan_id);

        // $convertStudentId = $this->convertSubClassId($student_id);
        $convertStudentId = $student_id;

        $data['student_reports'] = $data['student_reports']->filter(function ($report) use ($convertStudentId) {
            return $report['student_id'] === $convertStudentId;
        });

        $data['student_id'] = $student_id;

        return view('teacher.pengajar.pembelajaran.ulangan.penilaian.student.index', $data);
    }

    public function v_penilaian_ulasan(Request $request, $learning_id, $ulangan_id, $student_id, $response_id)
    {
        $this->authorizeTeacher();

        $data = $this->prepare_penilaian_data_to_send_view($learning_id, $ulangan_id);

        $data['all_answers'] = collect($data['all_answers']->data ?? [])->filter(function ($answer) use ($response_id) {
            return $answer->response_id === $response_id;
        });

        $questions = collect($data['all_questions']->data ?? [])->filter(function ($question) use ($ulangan_id) {
            return $question->exam_id === $ulangan_id;
        });

        // dd($data['all_questions'], $questions);

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 1;
        $currentItems = $questions->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedQuestions = new LengthAwarePaginator($currentItems, $questions->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);

        $data['all_questions'] = $paginatedQuestions;

        // $data['analytics'] = collect($data['analytics']->data ?? [])->where('student_id', $this->convertSubClassId($student_id))->where('response_id', $response_id)->first();
        $data['analytics'] = collect($data['analytics']->data ?? [])->where('student_id', $student_id)->where('response_id', $response_id)->first();

        $data['response_id'] = $response_id;

        return view('teacher.pengajar.pembelajaran.ulangan.penilaian.student.ulasan.index', $data);
    }

    public function update_is_main(Request $request, $learning_id, $ulangan_id, $student_id, $response_id)
    {
        $this->authorizeTeacher();

        $validator = Validator::make($request->all(), [
            'is_main' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $url = '/api/v1/cms/teacher/class-exam/' . $ulangan_id . '/update-is-main/' . $response_id;

        $response_data = $this->putData($url, [
            'is_main' => $request->is_main,
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, 'edit-nilai-akhir', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }

    public function penilaian_ulasan(Request $request, $learning_id, $ulangan_id, $student_id, $response_id)
    {
        $this->authorizeTeacher();

        $grades = collect($request->all())->filter(function ($value, $key) {
            return strpos($key, 'grade_') === 0;
        });
        $point = $grades->first();

        $validator = Validator::make([
            'point' => $point,
            'answer_id' => $request->answer_id,
        ], [
            'point' => 'required|numeric',
            'answer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $url = '/api/v1/mobile/teacher/update-grade/' . $request->answer_id;
        $response_data = $this->putData($url, [
            'point' => $point,
            'exam_type' => 'class',
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, 'edit', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }


    public function prepare_penilaian_data_to_send_view($learning_id, $ulangan_id)
    {
        // Mengambil data pembelajaran
        $learning = $this->fetchData('/api/v1/mobile/teacher/learning/' . $learning_id);

        // Mengambil data subkelas yang diampu oleh guru
        $teacherSubclasses = $this->fetchData('/api/v1/mobile/teacher/enrollment/sub-class');

        // Filter subkelas berdasarkan course
        $filteredSubclasses = collect($teacherSubclasses->data ?? [])->filter(function ($subclass) use ($learning) {
            return $subclass->course == optional($learning->data)->course->id;
        });

        // Mengambil data subkelas
        $subclasses = $this->fetchData('/api/v1/mobile/teacher/sub-class');
        $nameSubclass = $filteredSubclasses->map(function ($filteredSubclass) use ($subclasses) {
            return collect($subclasses->data ?? [])->firstWhere('id', $filteredSubclass->sub_class_id);
        })->values()->first();

        // Mengambil data ujian kelas
        $class_exams = $ulangan_id ? $this->fetchData('/api/v1/cms/teacher/class-exam/' . $ulangan_id) : $this->fetchData('/api/v1/cms/teacher/class-exam');
        $filtered_class_exams = $ulangan_id ? ($class_exams->data ?? null) : collect($class_exams->data ?? [])->filter(function ($class_exam) use ($learning) {
            return $class_exam->learning_id == optional($learning->data)->id;
        });

        // Mengambil semua respons siswa
        $all_response_students = $this->fetchData('/api/v1/cms/teacher/' . $ulangan_id . '/response');
        $responses = collect($all_response_students->data ?? []);

        // Fetch data dari endpoint answer dan question
        // $all_answers = $this->fetchData('/api/v1/mobile/teacher/answer');
        $all_answers = $this->fetchData('/api/v1/mobile/teacher/answer/class');
        $answers = collect($all_answers->data ?? []);
        $all_questions = $this->fetchData('/api/v1/cms/teacher/question/class');
        $questions = collect($all_questions->data ?? []);

        // Hitung nilai tertinggi, terendah, dan rata-rata dari semua respons siswa
        $grades = $responses->pluck('grades.*.class_exam')->flatten();
        $highestGrade = $grades->max();
        $lowestGrade = $grades->min();
        $averageGrade = $grades->avg();
        $totalUserResponses = $responses->unique('student_id')->count();

        // Analityc Data
        $analyticData = $this->fetchData('api/v1/cms/teacher/' . $ulangan_id . '/analytic');

        // Kumpulkan data per siswa
        $studentReports = $responses->groupBy('student_id')->map(function ($studentResponses) use ($answers, $questions) {
            $studentId = $studentResponses->first()->student_id;
            $studentName = $studentResponses->first()->student_name;
            $studentNisn = $studentResponses->first()->student_nisn;

            // Ambil semua nilai dari semua respons
            $grades = collect($studentResponses->pluck('grades')->flatten());
            $finalGrade = $grades->where('is_main', true)->pluck('class_exam')->first();

            // Hitung total usaha/attempt untuk setiap siswa
            $totalAttempts = $studentResponses->count();

            // Kumpulkan riwayat pengerjaan
            $attemptsHistory = $studentResponses->sortByDesc('created_at')->map(function ($response, $index) use ($answers, $questions) {
                $createdAt = \Carbon\Carbon::parse($response->created_at);
                $updatedAt = $response->updated_at ? \Carbon\Carbon::parse($response->updated_at) : null;

                $grades = collect($response->grades);

                // Dapatkan initialPoints dan totalPoints
                $initialPoints = 0;
                $essayPoints = 0;
                $totalPointQuestion = 0;
                $isEssayGraded = true;

                $responseAnswers = $answers->where('response_id', $response->id);
                $responseQuestions = $questions->where('exam_id', $response->exam_id);

                // Hitung initialPoints untuk question type tertentu
                $responseQuestions->each(function ($question) use ($responseAnswers, &$initialPoints, &$totalPointQuestion) {
                    $totalPointQuestion += $question->point;
                    if (in_array($question->question_type, ['Pilihan Ganda', 'Pilihan Ganda Complex', 'True False'])) {
                        $answer = $responseAnswers->where('question_id', $question->id)->first();
                        if ($answer) {
                            $choice = collect($question->choices)->where('id', $answer->choice_id)->first();
                            if ($choice && $choice->is_true) {
                                $initialPoints += $question->point;
                            }
                        }
                    }
                });

                // Tambahkan essayPoints jika sudah dinilai
                $responseQuestions->where('question_type', 'Essay')->each(function ($question) use ($grades, $responseAnswers, &$essayPoints, &$isEssayGraded) {
                    $answer = $responseAnswers->where('question_id', $question->id)->first();
                    if ($answer && $answer->is_graded) {
                        $essayPoints += $grades[0]->class_exam;
                    } else {
                        $isEssayGraded = false;
                    }
                });

                $totalPointsWithEssay = $essayPoints;

                // Tentukan status
                $status = $isEssayGraded ? 'Sudah Dinilai' : 'Mohon beri nilai di ulasan';
                $score = $isEssayGraded ? $grades->where('is_main', true)->pluck('class_exam')->first() : 'Esai belum dinilai';

                return [
                    'order' => $index + 1,
                    'response_id' => $response->id,
                    'start_time' => $createdAt->format('d M Y H:i') . ' WIB',
                    'end_time' => $updatedAt ? $updatedAt->format('d M Y H:i') . ' WIB' : 'Tak diketahui',
                    'score' => $score,
                    'status' => $status,
                    'initial_points' => $initialPoints,
                    'is_essay_graded' => $isEssayGraded,
                    'total_points_with_essay' => $totalPointsWithEssay,
                    'total_point_question' => $totalPointQuestion,
                ];
            });

            return [
                'student_id' => $studentId,
                'nisn' => $studentNisn,
                'name' => $studentName,
                'final_grade' => $finalGrade ?? null,
                'total_attempts' => $totalAttempts,
                'attempts_history' => $attemptsHistory,
            ];
        });

        return [
            'learning' => $learning->data ?? null,
            'subclasses' => $nameSubclass,
            'class_exams' => $filtered_class_exams,
            'student_reports' => $studentReports->values(),
            'highest_grade' => $highestGrade,
            'lowest_grade' => $lowestGrade,
            'average_grade' => $averageGrade,
            'total_user_responses' => $totalUserResponses,
            'learning_id' => $learning_id,
            'ulangan_id' => $ulangan_id,
            'all_answers' => $all_answers,
            'all_questions' => $all_questions,
            'analytics' => $analyticData,
        ];
    }

    private function getResponseMessage($success, $type, $message)
    {

        if ($success) {
            switch ($type) {
                case 'add':
                    return 'Penilaian berhasil ditambahkan.';
                case 'edit':
                    return 'Penilaian berhasil diperbarui.';
                case 'edit-nilai-akhir':
                    return 'Berhasil menyimpan nilai akhir.';
                case 'delete':
                    return 'Penilaian berhasil dihapus.';
                case 'import':
                    return 'Penilaian berhasil diimport.';
                default:
                    return $message;
            }
        }

        return 'Terjadi kesalahan: ' . $message;
    }
}
