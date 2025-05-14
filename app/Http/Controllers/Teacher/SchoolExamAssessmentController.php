<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class SchoolExamAssessmentController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
        $this->authorizeTeacher();
        $this->authorizeTeacherPenilai();
    }

    public function v_penilaian(Request $request, $ujian_id)
    {

        $data = $this->prepare_penilaian_data_to_send_view($ujian_id, null);

        return view('teacher.sekolah.penilaian.index', $data);
    }

    public function v_penilaian_student(Request $request, $ujian_id, $student_id)
    {

        $data = $this->prepare_penilaian_data_to_send_view($ujian_id, null);

        // $convertStudentId = $this->convertSubClassId($student_id);
        $convertStudentId = $student_id;

        $data['student_reports'] = $data['student_reports']->filter(function ($report) use ($convertStudentId) {
            return $report['student_id'] === $convertStudentId;
        });

        $data['student_id'] = $student_id;

        return view('teacher.sekolah.penilaian.student.index', $data);
    }

    public function v_penilaian_ulasan(Request $request, $ujian_id, $student_id, $response_id)
    {
        $data = $this->prepare_penilaian_data_to_send_view($ujian_id, $response_id);

        $questions = collect($data['all_questions']->data ?? [])->filter(function ($question) use ($ujian_id) {
            return $question->school_exam_id === $ujian_id;
        });

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

        // dd($data);

        return view('teacher.sekolah.penilaian.student.ulasan.index', $data);
    }

    public function update_is_main(Request $request, $ujian_id, $student_id, $response_id)
    {
        $validator = Validator::make($request->all(), [
            'is_main' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $url = '/api/v1/cms/teacher/school-exam/' . $ujian_id . '/update-is-main/' . $response_id;

        $response_data = $this->putData($url, [
            'is_main' => $request->is_main,
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, 'edit-nilai-akhir', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }

    public function penilaian_ulasan(Request $request, $ujian_id, $student_id, $response_id)
    {
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
            'exam_type' => 'school',
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, 'edit', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }


    public function prepare_penilaian_data_to_send_view($ujian_id, $response_id = null)
    {
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $schoolExams = $ujian_id ? $this->fetchData('/api/v1/cms/teacher/school-exam/' . $ujian_id)->data ?? [] : $this->fetchData('/api/v1/cms/teacher/school-exam')->data ?? [];

        if (is_array($schoolExams)) {
            foreach ($schoolExams as &$item) {
                if (is_object($item)) {
                    // $item->courses_name = $this->transformCourse($courseData, $item->course);
                    $item->courses_name = $this->transformCourse($courseData, $item->course->id);
                    $item->class_level = $this->transformLevel($levels, $item->class_level);
                }
            }
        } elseif (is_object($schoolExams)) {
            // $schoolExams->courses_name = $this->transformCourse($courseData, $schoolExams->course);
            $schoolExams->courses_name = $this->transformCourse($courseData, $schoolExams->course->id);
            $schoolExams->class_level = $this->transformLevel($levels, $schoolExams->class_level);
        }

        // Mengambil semua respons siswa
        $all_response_students = $this->fetchData('/api/v1/cms/teacher/' . $ujian_id . '/response');
        $responses = $response_id
            ? collect($all_response_students->data ?? [])->filter(function ($answer) use ($response_id) {
                return $answer->id === $response_id;
            })
            : collect($all_response_students->data ?? []);

        // Fetch data dari endpoint answer dan question
        // $all_answers = $this->fetchData('/api/v1/mobile/teacher/answer');
        $all_answers = $this->fetchData('/api/v1/mobile/teacher/answer/school');
        $answers = collect($all_answers->data ?? []);
        // dd($answers);
        $all_questions = $this->fetchData('/api/v1/cms/teacher/question/school');
        $examSections = collect($this->fetchData('/api/v1/cms/teacher/exam-section/')->data ?? [])
            ->filter(fn($section) => $section->exam_id == $ujian_id);
        foreach ($all_questions->data as &$item) {
            if (is_object($item)) {
                $item->section_id = $this->transformQuestionWithSection($examSections, $item->section_id);
            }
        }
        $questions = collect($all_questions->data ?? []);

        // Hitung nilai tertinggi, terendah, dan rata-rata dari semua respons siswa
        $grades = $responses->pluck('grades.*.exam')->flatten();
        $highestGrade = $grades->max();
        $lowestGrade = $grades->min();
        $averageGrade = $grades->avg();
        $totalUserResponses = $responses->unique('student_id')->count();

        // Analityc Data
        $analyticData = $this->fetchData('api/v1/cms/teacher/' . $ujian_id . '/analytic');

        // Kumpulkan data per siswa
        $studentReports = $responses->groupBy('student_id')->map(function ($studentResponses) use ($answers, $questions) {
            $studentId = $studentResponses->first()->student_id;
            $studentName = $studentResponses->first()->student_name;
            $studentNisn = $studentResponses->first()->student_nisn;

            // Ambil semua nilai dari semua respons
            $grades = collect($studentResponses->pluck('grades')->flatten());
            $finalGrade = $grades->where('response_id', $studentResponses->first()->id)->where('is_main', true)->pluck('exam')->first();

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
                $responseQuestions = $questions->where('school_exam_id', $response->school_exam_id);

                // Hitung initialPoints untuk question type tertentu
                $responseQuestions->each(function ($question) use ($responseAnswers, &$initialPoints, &$totalPointQuestion) {
                    $totalPointQuestion += $question->point;

                    if (in_array($question->question_type, ['Pilihan Ganda', 'Pilihan Ganda Complex', 'True False'])) {
                        $answersForQuestion = $responseAnswers->where('question_id', $question->id);

                        if ($question->question_type === 'Pilihan Ganda Complex') {
                            // Periksa apakah semua jawaban untuk pilihan ganda complex benar
                            $correctChoices = collect($question->choices)->where('is_true', true)->pluck('id');
                            $selectedChoices = $answersForQuestion->pluck('choice_id');

                            if ($selectedChoices->isEmpty()) {
                                // Tidak menjawab
                                $initialPoints += 0;
                            } else if ($selectedChoices->diff($correctChoices)->isEmpty() && $correctChoices->diff($selectedChoices)->isEmpty()) {
                                // Semua pilihan yang benar dipilih dan tidak ada yang salah
                                $initialPoints += $question->point;
                            } else if ($correctChoices->intersect($selectedChoices)->isNotEmpty()) {
                                // Beberapa jawaban benar, hitung poin proporsional
                                $correctSelected = $correctChoices->intersect($selectedChoices);
                                // Beri poin berdasarkan proporsi jawaban yang benar dipilih
                                $initialPoints += ($question->point * $correctSelected->count()) / $correctChoices->count();
                            } else {
                                // Semua salah atau pilihan yang salah dipilih
                                $initialPoints += 0;
                            }
                        } else {
                            // Logika untuk Pilihan Ganda dan True False
                            $answer = $answersForQuestion->first();
                            if ($answer) {
                                $choice = collect($question->choices)->where('id', $answer->choice_id)->first();
                                if ($choice && $choice->is_true) {
                                    $initialPoints += $question->point;
                                }
                            }
                        }
                    }
                });



                // Tambahkan essayPoints jika sudah dinilai
                $responseQuestions->where('question_type', 'Essay')->each(function ($question) use ($responseAnswers, &$essayPoints, &$isEssayGraded) {
                    $answer = $responseAnswers->where('question_id', $question->id)->first();
                    if ($answer && $answer->is_graded) {
                        $essayPoints += $question->point;
                    } else {
                        $isEssayGraded = false;
                    }
                });

                $totalPointsWithEssay = $initialPoints + $essayPoints;

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

            // dd($attemptsHistory);

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
            'class_level' => $schoolExams->class_level ?? null,
            'exams' => $schoolExams,
            'courses_name' => $schoolExams->courses_name ?? null,
            'student_reports' => $studentReports->values(),
            'highest_grade' => $highestGrade,
            'lowest_grade' => $lowestGrade,
            'average_grade' => $averageGrade,
            'total_user_responses' => $totalUserResponses,
            'ujian_id' => $ujian_id,
            'all_answers' => collect($all_answers->data)->filter(function ($answer) use ($response_id) {
                return $answer->response_id == $response_id;
            }) ?? null,
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

    private function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/course');
        return $response_data->data ?? [];
    }

    private function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/class');
        return $response_data->data ?? [];
    }
}
