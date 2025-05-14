<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Answer;
use App\Models\Choices;
use App\Models\ClassExam;
use App\Models\EnrollmentExam;
use App\Models\ExamSetting;
use App\Models\ExamTeacher;
use App\Models\Grade;
use App\Models\Learning;
use App\Models\Question;
use App\Models\Response;
use App\Models\School;
use App\Models\SchoolExam;
use App\Models\User;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
    use CommonTrait;

    public function verifyTokenExam(Request $request)
    {
        $userLogin = auth()->user();

        if ($userLogin->role !== 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengakses ini.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'exam_id' => 'nullable|exists:class_exams,id',
            'school_exam_id' => 'nullable|exists:school_exams,id',
            'status' => 'required|string',
        ], [
            'token.required' => 'Ups, Anda Belum Melengkapi Form',
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
            'exam_id.exists' => 'Ups, Id Exam Tidak Valid',
            'school_exam_id.exists' => 'Ups, Id School Exam Tidak Valid',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $exam = null;

        if ($request->exam_id) {
            $exam = ExamSetting::where('exam_id', $request->exam_id)->first();
        } else if ($request->school_exam_id) {
            $exam = ExamSetting::where('school_exam_id', $request->school_exam_id)->first();
        } else {
            return $this->sendError('Ups, Id Exam Tidak Valid', null, 200);
        }

        if (!$exam) {
            return $this->sendError('Exam tidak ditemukan.', null, 200);
        }

        if (!Hash::check($request->token, $exam->token)) {
            return $this->sendError('Token tidak valid.', null, 200);
        }

        if ($request->exam_id) {
            $existingResponse = Response::where('student_id', $userLogin->is_student->id)
                ->where('exam_id', $request->exam_id)
                ->where('status', 'pengerjaan')
                ->latest()
                ->first();
        } else if ($request->school_exam_id) {
            $existingResponse = Response::where('student_id', $userLogin->is_student->id)
                ->where('school_exam_id', $request->school_exam_id)
                ->where('status', 'pengerjaan')
                ->latest()
                ->first();
        }

        if (isset($existingResponse)) {
            return $this->sendError('Anda masih dalam tahap pengerjaan.', null, 200);
        }

        $generateResponseId = IdGenerator::generate([
            'table' => 'responses',
            'length' => 16,
            'prefix' => 'RES-'
        ]);

        $response = Response::create([
            'id' => $generateResponseId,
            'student_id' => $userLogin->is_student->id,
            'exam_id' => $request->exam_id,
            'school_exam_id' => $request->school_exam_id,
            'status' => $request->status,
        ]);

        $examSettingByStudent = ExamSetting::where('exam_id', $response->exam_id)->first()
            ?? ExamSetting::where('school_exam_id', $response->school_exam_id)->first();

        if ($examSettingByStudent->repeat_chance > 0) {
            return $this->sendResponse($response, 'Token valid.');
        } else {
            return $this->sendError('Anda telah mencapai batas percobaan.', null, 200);
        }
    }


    public function index_all_response_student($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role !== 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengakses ini.', null, 200);
        }

        $response = Response::where('student_id', $userLogin->is_student->id)
            ->where('exam_id', $id)
            ->get();

        if ($response->isEmpty()) {
            return $this->sendError('Data response tidak ditemukan.', null, 200);
        }

        $response->load('class_exam');

        $examSetting = ExamSetting::where('exam_id', $id)->first();

        if (!$examSetting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 404);
        }

        if ($examSetting->is_show_score) {
            $response->load('grades');
        }

        if ($examSetting->is_show_result) {
            return $this->sendResponse($response, 'Berhasil mengambil semua data response.');
        } else {
            return $this->sendResponse(null, 'Berhasil mengambil semua data response.');
        }
    }

    public function index_all_response($id)
    {
        $userLogin = auth()->user();

        $responses = Response::where('exam_id', $id)->get();

        if ($responses->isEmpty()) {
            $responses = Response::where('school_exam_id', $id)->get();
        }

        if ($responses->isEmpty()) {
            return $this->sendError('Data response tidak ditemukan.', null, 200);
        }

        $studentIds = $responses->pluck('student_id')->unique();
        $users = User::with('is_student')->get();

        $students = $users->filter(function ($user) use ($studentIds) {
            return $studentIds->contains(optional($user->is_student)->id);
        })->keyBy(function ($user) {
            return optional($user->is_student)->id;
        });

        foreach ($responses as $response) {
            $student = $students->get($response->student_id);
            $response->student_name = $student ? $student->fullname : 'Nama tidak ditemukan';
            $response->student_nisn = $student ? $student->is_student->nisn : 'NIS tidak ditemukan';
        }

        $response->load('class_exam');
        $response->load('school_exam');
        $responses->load('grades');


        return $this->sendResponse($responses, 'Berhasil mengambil semua data response.');
    }


    public function index_all_enrollment_school_exam_student($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role !== 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengakses ini.', null, 200);
        }

        $response = Response::where('student_id', $userLogin->is_student->id)->where('school_exam_id', $id)->get();


        if ($response->isEmpty()) {
            return $this->sendError('Data response tidak ditemukan.', null, 200);
        }

        $response->load('school_exam');

        $examSetting = ExamSetting::where('school_exam_id', $id)->first();

        if (!$examSetting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 404);
        }

        if ($examSetting->is_show_score) {
            $response->load('grades');
        }

        if ($examSetting->is_show_result) {
            return $this->sendResponse($response, 'Berhasil mengambil semua data response.');
        } else {
            return $this->sendResponse(null, 'Berhasil mengambil semua data response.');
        }
    }

    public function index_all_enrollment_school_exam($id)
    {
        $userLogin = auth()->user();

        $response = Response::where('school_exam_id', $id)->get();


        if ($response->isEmpty()) {
            return $this->sendError('Data response tidak ditemukan.', null, 200);
        }

        $response->load('school_exam');

        $examSetting = ExamSetting::where('school_exam_id', $id)->first();

        if (!$examSetting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 404);
        }

        $response->load('grades');

        return $this->sendResponse($response, 'Berhasil mengambil semua data response.');
    }

    public function analytic_class_exam_student($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role !== 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengakses ini.', null, 200);
        }

        $student = $userLogin->is_student;

        $responses = Response::where('student_id', $student->id)
            ->where('exam_id', $id)
            ->get();

        if ($responses->isEmpty()) {
            return $this->sendError('Response tidak ditemukan.', null, 200);
        }

        // Mengambil pertanyaan dan pilihan
        $questions = Question::where('exam_id', $id)->get();
        $choices = Choices::whereIn('question_id', $questions->pluck('id'))->get();

        // Mengambil pengaturan ujian
        $examSetting = ExamSetting::where('exam_id', $id)->first();

        if (!$examSetting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 200);
        }

        // Hitung durasi ujian dalam detik
        $startTime = strtotime($examSetting->start_time);
        $endTime = strtotime($examSetting->end_time);
        $examDuration = $endTime - $startTime;
        $currentTime = time();
        $timeRemaining = $endTime - $currentTime;

        // Untuk setiap response, hitung analisisnya
        $analysisList = [];

        foreach ($responses as $response) {
            // Menghitung poin total yang diperoleh untuk semua jenis pertanyaan
            $totalPoints = $questions->sum('point');
            $obtainedPoints = 0;

            // Mengambil jawaban siswa
            $answers = Answer::where('response_id', $response->id)->get();

            $responseId = $response->id;
            $filledAnswers = $answers->unique('question_id')->count();
            $correctAnswers = 0;
            $incorrectAnswers = 0;
            $unanswered = $questions->count() - $filledAnswers;

            foreach ($questions as $question) {
                $answer = $answers->where('question_id', $question->id)->first();
                if ($answer) {
                    if ($question->question_type == 'Pilihan Ganda' || $question->question_type == 'True False') {
                        $choice = $choices->find($answer->choice_id);
                        if ($choice && $choice->is_true) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Pilihan Ganda Complex') {
                        $selectedChoices = $choices->where('question_id', $question->id)->whereIn('id', $answers->pluck('choice_id'));
                        $correctChoiceCount = $choices->where('question_id', $question->id)->where('is_true', true)->count();
                        $correctSelectedCount = $selectedChoices->where('is_true', true)->count();

                        if ($correctChoiceCount == $correctSelectedCount && $correctSelectedCount > 0) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Essay') {
                        $cleanedContent = trim(strip_tags($answer->answer));
                        if ($cleanedContent === '') {
                            $unanswered++;
                            $filledAnswers--;
                        }
                    }
                }
            }

            $questionCount = [
                'Essay' => $questions->where('question_type', 'Essay')->count(),
                'Pilihan Ganda' => $questions->where('question_type', 'Pilihan Ganda')->count(),
                'Pilihan Ganda Complex' => $questions->where('question_type', 'Pilihan Ganda Complex')->count(),
                'True False' => $questions->where('question_type', 'True False')->count(),
                'Total' => $questions->count(),
            ];

            // Mengambil grade
            $grade = Grade::where('response_id', $response->id)->first();

            $analysisList[] = [
                'response_id' => $responseId,
                'student_name' => $userLogin->fullname,
                'start_time' => $examSetting->start_time,
                'time_remaining' => $timeRemaining,
                'end_time' => $examSetting->end_time,
                'exam_duration' => $examDuration,
                'total_points' => $totalPoints,
                'obtained_points' => $obtainedPoints,
                'question_count' => $questionCount,
                'answers' => [
                    'filled' => $filledAnswers,
                    'unanswered' => $unanswered,
                    'correct' => $correctAnswers,
                    'incorrect' => $incorrectAnswers,
                ],
                'grade' => $grade ? $grade->class_exam : 0
            ];
        }

        return $this->sendResponse($analysisList, 'Analisis ujian kelas berhasil diambil.');
    }

    public function analytic_class_exam_all_students($id)
    {
        $userLogin = auth()->user();

        $responses = Response::where('exam_id', $id)->get();

        if ($responses->isEmpty()) {
            $responses = Response::where('school_exam_id', $id)->get();
        }

        if ($responses->isEmpty()) {
            return $this->sendError('Response tidak ditemukan.', null, 200);
        }

        $questions = Question::where('exam_id', $id)->get();

        if ($questions->isEmpty()) {
            $questions = Question::where('school_exam_id', $id)->get();
        }

        if ($questions->isEmpty()) {
            return $this->sendError('Pertanyaan tidak ditemukan.', null, 200);
        }

        // Mengambil semua pilihan terkait dengan pertanyaan tersebut
        $choices = Choices::whereIn('question_id', $questions->pluck('id'))->get();

        // Mengambil setting ujian
        $examSetting = ExamSetting::where('exam_id', $id)->first();

        if (!$examSetting) {
            $examSetting = ExamSetting::where('school_exam_id', $id)->first();
        }

        if (!$examSetting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 200);
        }

        // Hitung durasi ujian dalam detik
        $startTime = strtotime($examSetting->start_time);
        $endTime = strtotime($examSetting->end_time);
        $examDuration = $endTime - $startTime;

        // Hitung waktu yang tersisa atau yang sudah berlalu
        $currentTime = time();
        $timeRemaining = $endTime - $currentTime;

        $studentsAnalysis = [];

        foreach ($responses as $response) {
            $student = $response->student;
            $answers = Answer::where('response_id', $response->id)->get();

            // Menghitung jumlah jawaban yang diisi berdasarkan question_id unik
            $filledQuestions = $answers->unique('question_id')->count();
            $correctAnswers = 0;
            $incorrectAnswers = 0;
            $obtainedPoints = 0;
            $totalPoint = $questions->sum('point');

            // Inisialisasi unanswered berdasarkan total pertanyaan
            $unanswered = $questions->count() - $filledQuestions;

            foreach ($questions as $question) {
                $answer = $answers->where('question_id', $question->id)->first();
                if ($answer) {
                    if ($question->question_type == 'Pilihan Ganda' || $question->question_type == 'True False') {
                        $choice = $choices->find($answer->choice_id);
                        if ($choice && $choice->is_true) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Pilihan Ganda Complex') {
                        $selectedChoices = $choices->where('question_id', $question->id)->whereIn('id', $answers->pluck('choice_id'));
                        $correctChoiceCount = $choices->where('question_id', $question->id)->where('is_true', true)->count();
                        $correctSelectedCount = $selectedChoices->where('is_true', true)->count();

                        if ($correctChoiceCount == $correctSelectedCount && $correctSelectedCount > 0) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Essay') {
                        $cleanedContent = trim(strip_tags($answer->answer));
                        if ($cleanedContent === '') {
                            $unanswered++;
                            $filledQuestions--;
                        }
                    }
                }
            }

            $questionCount = [
                'Essay' => $questions->where('question_type', 'Essay')->count(),
                'Pilihan Ganda' => $questions->where('question_type', 'Pilihan Ganda')->count(),
                'Pilihan Ganda Complex' => $questions->where('question_type', 'Pilihan Ganda Complex')->count(),
                'True False' => $questions->where('question_type', 'True False')->count(),
                'Total' => $questions->count(),
            ];

            // Mengambil grade
            $grade = Grade::where('response_id', $response->id)->first();

            $user = User::find($student->user_id);

            $studentAnalysis = [
                'response_id' => $response->id,
                'student_id' => $student->id,
                'student_name' => $user->fullname,
                'start_time' => $examSetting->start_time,
                'time_remaining' => $timeRemaining,
                'end_time' => $examSetting->end_time,
                'exam_duration' => $examDuration,
                'total_points' => $totalPoint,
                'obtained_points' => $obtainedPoints,
                'question_count' => $questionCount,
                'answers' => [
                    'filled' => $filledQuestions,
                    'unanswered' => $unanswered,
                    'correct' => $correctAnswers,
                    'incorrect' => $incorrectAnswers,
                ],
                'grade' => $grade ? $grade->class_exam : 0
            ];

            $studentsAnalysis[] = $studentAnalysis;
        }

        return $this->sendResponse($studentsAnalysis, 'Analisis ujian kelas berhasil diambil.');
    }

    public function analytic_school_exam_student($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role !== 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengakses ini.', null, 200);
        }

        $student = $userLogin->is_student;

        $responses = Response::where('student_id', $student->id)
            ->where('school_exam_id', $id)
            ->where('status', 'pengumpulan')
            ->get();

        if ($responses->isEmpty()) {
            return $this->sendError('Response tidak ditemukan.', null, 200);
        }

        // Mengambil pertanyaan dan pilihan
        $questions = Question::where('school_exam_id', $id)->get();
        $choices = Choices::whereIn('question_id', $questions->pluck('id'))->get();

        // Mengambil exam settings
        $examSetting = ExamSetting::where('school_exam_id', $id)->first();

        if (!$examSetting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 200);
        }

        // Hitung durasi ujian dalam detik
        $startTime = strtotime($examSetting->start_time);
        $endTime = strtotime($examSetting->end_time);
        $examDuration = $endTime - $startTime;

        $analysis = [];

        foreach ($responses as $response) {
            // Mengambil EnrollmentExam untuk mengambil created_at dan updated_at
            $enrollmentExam = EnrollmentExam::where('student_id', $student->id)
                ->where('exam_id', $id)
                ->first();

            if (!$enrollmentExam) {
                continue; // Skip if enrollment exam is not found
            }

            // Menghitung poin total yang diperoleh untuk semua jenis pertanyaan
            $totalPoints = $questions->sum('point');
            $obtainedPoints = 0;
            $responseId = $response->id;

            // Mengambil jawaban siswa
            $answers = Answer::where('response_id', $response->id)->get();

            $filledQuestions = $answers->unique('question_id')->count();
            $correctAnswers = 0;
            $incorrectAnswers = 0;
            $unanswered = $questions->count() - $filledQuestions;

            foreach ($questions as $question) {
                $answer = $answers->where('question_id', $question->id)->first();
                if ($answer) {
                    if ($question->question_type == 'Pilihan Ganda' || $question->question_type == 'True False') {
                        $choice = $choices->find($answer->choice_id);
                        if ($choice && $choice->is_true) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Pilihan Ganda Complex') {
                        // Logika untuk Pilihan Ganda Complex (anggap multiple correct answers)
                        $selectedChoices = $choices->where('question_id', $question->id)->whereIn('id', $answers->pluck('choice_id'));
                        $correctChoiceCount = $choices->where('question_id', $question->id)->where('is_true', true)->count();
                        $correctSelectedCount = $selectedChoices->where('is_true', true)->count();

                        if ($correctChoiceCount == $correctSelectedCount && $correctSelectedCount > 0) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Essay') {
                        $cleanedContent = trim(strip_tags($answer->answer));
                        if ($cleanedContent === '') {
                            $unanswered++;
                            $filledQuestions--;
                        }
                    }
                }
            }

            $questionCount = [
                'Essay' => $questions->where('question_type', 'Essay')->count(),
                'Pilihan Ganda' => $questions->where('question_type', 'Pilihan Ganda')->count(),
                'Pilihan Ganda Complex' => $questions->where('question_type', 'Pilihan Ganda Complex')->count(),
                'True False' => $questions->where('question_type', 'True False')->count(),
                'Total' => $questions->count(),
            ];

            // Mengambil grade
            $grade = Grade::where('response_id', $response->id)->first();

            // Hitung waktu yang tersisa atau yang sudah berlalu
            $currentTime = time();
            $timeRemaining = $endTime - $currentTime;

            $analysis[] = [
                'response_id' => $responseId,
                'student_name' => $userLogin->fullname,
                'start_time' => $examSetting->start_time,
                'time_remaining' => $timeRemaining,
                'end_time' => $examSetting->end_time,
                'exam_duration' => $examDuration,
                'total_points' => $totalPoints,
                'obtained_points' => $obtainedPoints,
                'question_count' => $questionCount,
                'answers' => [
                    'filled' => $filledQuestions,
                    'unanswered' => $unanswered,
                    'correct' => $correctAnswers,
                    'incorrect' => $incorrectAnswers,
                ],
                'grade' => $grade ? $grade->exam : 0
            ];
        }

        return $this->sendResponse($analysis, 'Analisis ujian sekolah berhasil diambil.');
    }

    public function analytic_school_exam_all_students($examId)
    {
        $userLogin = auth()->user();

        // Mengambil semua response untuk ujian sekolah tertentu
        $responses = Response::where('school_exam_id', $examId)->get();

        if ($responses->isEmpty()) {
            return $this->sendError('Response tidak ditemukan.', null, 200);
        }

        // Mengambil semua pertanyaan untuk ujian tersebut
        $questions = Question::where('school_exam_id', $examId)->get();

        // Mengambil semua pilihan terkait dengan pertanyaan tersebut
        $choices = Choices::whereIn('question_id', $questions->pluck('id'))->get();

        // Mengambil setting ujian
        $examSetting = ExamSetting::where('school_exam_id', $examId)->first();

        if (!$examSetting) {
            return $this->sendError('Pengaturan ujian tidak ditemukan.', null, 200);
        }

        // Hitung durasi ujian dalam detik
        $startTime = strtotime($examSetting->start_time);
        $endTime = strtotime($examSetting->end_time);
        $examDuration = $endTime - $startTime;

        // Hitung waktu yang tersisa atau yang sudah berlalu
        $currentTime = time();
        $timeRemaining = $endTime - $currentTime;

        $studentsAnalysis = [];

        foreach ($responses as $response) {
            $student = $response->student;
            $answers = Answer::where('response_id', $response->id)->get();

            // Menghitung jumlah jawaban yang diisi berdasarkan question_id unik
            $filledQuestions = $answers->unique('question_id')->count();
            $correctAnswers = 0;
            $incorrectAnswers = 0;
            $obtainedPoints = 0;
            $totalPoints = $questions->sum('point');
            $unanswered = $questions->count() - $filledQuestions;

            foreach ($questions as $question) {
                $answer = $answers->where('question_id', $question->id)->first();
                if ($answer) {
                    if ($question->question_type == 'Pilihan Ganda' || $question->question_type == 'True False') {
                        $choice = $choices->find($answer->choice_id);
                        if ($choice && $choice->is_true) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Pilihan Ganda Complex') {
                        // Logika untuk Pilihan Ganda Complex (anggap multiple correct answers)
                        $selectedChoices = $choices->where('question_id', $question->id)->whereIn('id', $answers->pluck('choice_id'));
                        $correctChoiceCount = $choices->where('question_id', $question->id)->where('is_true', true)->count();
                        $correctSelectedCount = $selectedChoices->where('is_true', true)->count();

                        if ($correctChoiceCount == $correctSelectedCount && $correctSelectedCount > 0) {
                            $correctAnswers++;
                            $obtainedPoints += $question->point;
                        } else {
                            $incorrectAnswers++;
                        }
                    } elseif ($question->question_type == 'Essay') {
                        $cleanedContent = trim(strip_tags($answer->answer));
                        if ($cleanedContent === '') {
                            $unanswered++;
                            $filledQuestions--;
                        }
                    }
                }
            }

            $questionCount = [
                'Essay' => $questions->where('question_type', 'Essay')->count(),
                'Pilihan Ganda' => $questions->where('question_type', 'Pilihan Ganda')->count(),
                'Pilihan Ganda Complex' => $questions->where('question_type', 'Pilihan Ganda Complex')->count(),
                'True False' => $questions->where('question_type', 'True False')->count(),
                'Total' => $questions->count(),
            ];

            // Mengambil grade
            $grade = Grade::where('response_id', $response->id)->first();

            $user = User::find($student->user_id);

            $studentAnalysis = [
                'student_name' => $user->fullname,
                'start_time' => $examSetting->start_time,
                'time_remaining' => $timeRemaining,
                'end_time' => $examSetting->end_time,
                'exam_duration' => $examDuration,
                'total_points' => $totalPoints,
                'obtained_points' => $obtainedPoints,
                'question_count' => $questionCount,
                'answers' => [
                    'filled' => $filledQuestions,
                    'unanswered' => $unanswered,
                    'correct' => $correctAnswers,
                    'incorrect' => $incorrectAnswers,
                ],
                'grade' => $grade ? $grade->exam : 0
            ];

            $studentsAnalysis[] = $studentAnalysis;
        }

        return $this->sendResponse($studentsAnalysis, 'Analisis ujian sekolah berhasil diambil.');
    }

    public function score_list_student(Request $request)
    {
        $user_login = auth()->user();
        $filterBy = $request->query('search');

        $gradesQuery = Grade::query();

        if ($filterBy === 'class-exam') {
            $gradesQuery->with(['response', 'response.class_exam', 'response.class_exam.exam_setting', 'response.class_exam.learning', 'response.class_exam.learning.course'])
                ->whereHas('response', function ($query) use ($user_login) {
                    $query->where('student_id', $user_login->is_student->id);
                })
                ->whereNotNull('class_exam')
                ->whereNull('exam')
                ->where('is_main', true);
        } elseif ($filterBy === 'school-exam') {
            $gradesQuery->with(['response', 'response.school_exam', 'response.school_exam.exam_setting', 'response.school_exam.course'])
                ->whereHas('response', function ($query) use ($user_login) {
                    $query->where('student_id', $user_login->is_student->id);
                })
                ->whereNotNull('exam')
                ->whereNull('class_exam')
                ->where('is_main', true);
        } elseif ($filterBy === 'submission') {
            $gradesQuery->with(['submission', 'submission.assignment', 'submission.assignment.learning', 'submission.assignment.learning.course'])
                ->whereHas('submission', function ($query) use ($user_login) {
                    $query->where('student_id', $user_login->is_student->id);
                })
                ->whereNull('class_exam')
                ->whereNull('exam')
                ->where('is_main', true);
        } else {
            $gradesQuery->with([
                'response',
                'response.class_exam',
                'response.class_exam.exam_setting',
                'response.class_exam.learning',
                'response.class_exam.learning.course',
                'response.school_exam',
                'response.school_exam.exam_setting',
                'response.school_exam.course',
                'submission',
                'submission.assignment',
                'submission.assignment.learning',
                'submission.assignment.learning.course'
            ])
                ->where(function ($query) use ($user_login) {
                    $query->whereHas('response', function ($q) use ($user_login) {
                        $q->where('student_id', $user_login->is_student->id);
                    })->orWhereHas('submission', function ($q) use ($user_login) {
                        $q->where('student_id', $user_login->is_student->id);
                    });
                })
                ->where('is_main', true);
        }

        if ($filterBy === 'class-exam') {
            $gradesQuery->select(['id', 'class_exam', 'response_id', 'graded_at', 'is_main', 'status', 'publication_status']);
        } elseif ($filterBy === 'school-exam') {
            $gradesQuery->select(['id', 'response_id', 'exam', 'graded_at', 'is_main', 'status', 'publication_status']);
        } elseif ($filterBy === 'submission') {
            $gradesQuery->select(['id', 'submission_id', 'knowledge', 'skills', 'graded_at', 'is_main', 'status', 'publication_status']);
        }

        $filteredGrades = $gradesQuery->get();

        return $this->sendResponse($filteredGrades, 'Berhasil mengambil daftar nilai.');
    }


    public function storeResponseStudent(Request $request)
    {
        $userLogin = auth()->user();

        // Cek apakah user login adalah siswa
        if ($userLogin->role !== 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengakses ini.', null, 200);
        }

        // Validasi request input
        $validator = Validator::make($request->all(), [
            'exam_id' => 'nullable|exists:class_exams,id',
            'school_exam_id' => 'nullable|exists:school_exams,id',
            'status' => 'required|string',
        ], [
            'exam_id.exists' => 'Ups, Id Exam Tidak Valid',
            'school_exam_id.exists' => 'Ups, Id School Exam Tidak Valid',
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $response = null;

        if ($request->exam_id) {
            $response = Response::where('student_id', $userLogin->is_student->id)
                ->where('exam_id', $request->exam_id)
                ->where('status', 'pengerjaan')
                ->first();
        }

        if ($request->school_exam_id) {
            $response = Response::where('student_id', $userLogin->is_student->id)
                ->where('school_exam_id', $request->school_exam_id)
                ->where('status', 'pengerjaan')
                ->first();
        }

        if (!$response) {
            return $this->sendError('Anda sudah mengerjakan ujian ini.', null, 200);
        }

        if ($request->school_exam_id) {
            $enrollmentExam = EnrollmentExam::where('student_id', $userLogin->is_student->id)
                ->where('exam_id', $request->school_exam_id)->first();
            if ($enrollmentExam) {
                $enrollmentExam->increment('do_exam');
            }
        }

        $examSettingByStudent = null;

        if ($request->exam_id) {
            $examSettingByStudent = ExamSetting::where('exam_id', $request->exam_id)->first();
        }

        if ($request->school_exam_id) {
            $examSettingByStudent = ExamSetting::where('school_exam_id', $request->school_exam_id)->first();
        }

        $submittedAt = Carbon::now('Asia/Jakarta');
        $endTime = Carbon::parse($examSettingByStudent->end_time, 'Asia/Jakarta');
        $endTimeWithTolerance = $endTime->copy()->addMinutes(15);

        if ($submittedAt->greaterThan($endTimeWithTolerance)) {
            return $this->sendError('Anda tidak dapat mengirim submission yang melebihi deadline.', null, 200);
        }

        if ($examSettingByStudent->repeat_chance > 0) {
            $examSettingByStudent->decrement('repeat_chance');
        } else {
            return $this->sendError('Anda telah mencapai batas percobaan.', null, 200);
        }

        $response->update(['status' => $request->status]);

        return $this->sendResponse($response, 'Berhasil menyimpan jawaban.');
    }

    public function index()
    {
        $userLogin = auth()->user();

        // Jika pengguna adalah siswa
        if ($userLogin->role === 'STUDENT') {
            $student = $userLogin->is_student;
            $responseAnswerIds = Response::where('student_id', $student->id)->pluck('id');

            $allAnswer = Answer::whereIn('response_id', $responseAnswerIds)->get();

            if ($allAnswer->isEmpty()) {
                return $this->sendError('Jawaban tidak ditemukan.', null, 200);
            }

            return $this->sendResponse($allAnswer, 'Jawaban ditemukan.');
        }

        $allAnswer = Answer::all();

        if ($allAnswer->isEmpty()) {
            return $this->sendError('Jawaban tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($allAnswer, 'Jawaban ditemukan.');
    }

    public function indexTeacherClassExam()
    {
        $userLogin = auth()->user();
        if ($userLogin->role !== 'TEACHER') {
            return $this->sendError('Unauthorized.', null, 403);
        }

        $teacher = $userLogin->is_teacher;
        $learningIds = Learning::where('teacher_id', $teacher->id)->pluck('id');
        $examIds = ClassExam::whereIn('learning_id', $learningIds)->pluck('id');

        $responseAnswerIds = Response::whereIn('exam_id', $examIds)->pluck('id');
        $allAnswer = Answer::whereIn('response_id', $responseAnswerIds)->get();

        if ($allAnswer->isEmpty()) {
            return $this->sendError('Jawaban Class Exam tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($allAnswer, 'Jawaban Class Exam ditemukan.');
    }

    public function indexTeacherSchoolExam()
    {
        $userLogin = auth()->user();
        if ($userLogin->role !== 'TEACHER') {
            return $this->sendError('Unauthorized.', null, 403);
        }

        $teacher = $userLogin->is_teacher;
        $examTeachers = ExamTeacher::where('teacher_id', $teacher->id)->pluck('exam_id');
        $schoolExamIds = SchoolExam::whereIn('id', $examTeachers)->pluck('id');

        $responseAnswerIds = Response::whereIn('school_exam_id', $schoolExamIds)->pluck('id');
        $allAnswer = Answer::whereIn('response_id', $responseAnswerIds)->get();

        if ($allAnswer->isEmpty()) {
            return $this->sendError('Jawaban School Exam tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($allAnswer, 'Jawaban School Exam ditemukan.');
    }

    public function show($id)
    {
        $userLogin = auth()->user();
        $answer = null;

        if ($userLogin->role === 'STUDENT') {
            $student = $userLogin->is_student;
            $responseAnswerIds = Response::where('student_id', $student->id)->pluck('id');

            $answer = Answer::whereIn('response_id', $responseAnswerIds)->find($id);
        }

        if ($userLogin->role === 'TEACHER') {
            $teacher = $userLogin->is_teacher;
            $learningIds = Learning::where('teacher_id', $teacher->id)->pluck('id');
            $examIds = ClassExam::whereIn('learning_id', $learningIds)->pluck('id');
            $responseAnswerIds = Response::whereIn('exam_id', $examIds)->pluck('id');

            $answer = Answer::whereIn('response_id', $responseAnswerIds)->find($id);
        }

        // Untuk peran lainnya, mengambil semua jawaban
        $answer = Answer::find($id);

        if (!$answer) {
            return $this->sendError('Jawaban tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($answer, 'Jawaban ditemukan.');
    }


    public function store(Request $request)
    {
        $userLogin = auth()->user();

        if ($userLogin->role != 'STUDENT') {
            return $this->sendError('Anda tidak dapat mengirim jawaban.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'response_id' => 'required|exists:responses,id',
            'exam_id' => 'nullable|exists:class_exams,id',
            'school_exam_id' => 'nullable|exists:school_exams,id',
            'answers' => 'nullable|array',
            'answers.*.question_id' => 'nullable|exists:questions,id',
            'answers.*.answer_text' => 'nullable',
            'answers.*.choice_id' => 'nullable|exists:choices,id',
        ], [
            'exam_id.exists' => 'Ups, Id Exam Tidak Valid',
            'school_exam_id.exists' => 'Ups, Id School Exam Tidak Valid',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $examId = $request->exam_id;
        $schoolExamId = $request->school_exam_id;

        if ($request->filled('answers') && count($request->answers) > 0) {
            foreach ($request->answers as $answer) {
                $question = Question::where('id', $answer['question_id'])
                    ->where(function ($query) use ($examId, $schoolExamId) {
                        $query->where('exam_id', $examId)
                            ->orWhere('school_exam_id', $schoolExamId);
                    })->first();

                if (!$question) {
                    continue;
                }

                $response = Response::where('student_id', $userLogin->is_student->id)
                    ->where(function ($query) use ($examId, $schoolExamId) {
                        $query->where('exam_id', $examId)
                            ->orWhere('school_exam_id', $schoolExamId);
                    })->where('id', $request->response_id)->first();

                if (!$response) {
                    continue;
                }

                $existingAnswer = null;
                if ($question->question_type === 'Essay') {
                    $existingAnswer = Answer::where('response_id', $response->id)
                        ->where('question_id', $answer['question_id'])
                        ->first();
                } else {
                    $existingAnswer = Answer::where('response_id', $response->id)
                        ->where('question_id', $answer['question_id'])
                        ->where('choice_id', $answer['choice_id'])
                        ->first();
                }

                $grade = Grade::where('response_id', $response->id)->first();

                if ($question->question_type === 'Essay') {
                    $this->handleEssayAnswer($answer, $existingAnswer, $response, $question, $grade, $examId, $schoolExamId);
                } else if ($question->question_type === 'Pilihan Ganda Complex') {
                    $this->handleComplexChoiceAnswer($answer, $existingAnswer, $response, $question, $grade, $examId, $schoolExamId);
                } else {
                    $this->handleChoiceAnswer($answer, $existingAnswer, $response, $question, $grade, $examId, $schoolExamId);
                }
            }
        } else {
            $grade = Grade::where('response_id', $request->response_id)->first();
            if (!$grade) {
                $generateGradeId = IdGenerator::generate(['table' => 'grades', 'length' => 16, 'prefix' => 'GRA-']);

                if ($examId) {
                    Grade::create([
                        'id' => $generateGradeId,
                        'response_id' => $request->response_id,
                        'class_exam' => 0,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                } elseif ($schoolExamId) {
                    Grade::create([
                        'id' => $generateGradeId,
                        'response_id' => $request->response_id,
                        'exam' => 0,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                } else {
                    Grade::create([
                        'id' => $generateGradeId,
                        'response_id' => $request->response_id,
                        'class_exam' => 0,
                        'exam' => 0,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            } else {
                if ($examId) {
                    $grade->update([
                        'class_exam' => $grade->class_exam + 0,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                } else {
                    $grade->update([
                        'exam' => $grade->exam + 0,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            }
        }

        return $this->sendResponse(null, 'Jawaban Berhasil Disimpan.');
    }

    private function handleEssayAnswer($answer, $existingAnswer, $response, $question, $grade, $examId, $schoolExamId)
    {
        if ($existingAnswer) {
            $existingAnswer->update([
                'answer_text' => $answer['answer_text'],
                'is_graded' => false,
            ]);
        } else {
            $generateAnswerId = IdGenerator::generate(['table' => 'answers', 'length' => 16, 'prefix' => 'ANS-']);
            Answer::create([
                'id' => $generateAnswerId,
                'choice_id' => null,
                'response_id' => $response->id,
                'question_id' => $answer['question_id'],
                'answer_text' => $answer['answer_text'],
                'is_graded' => false,
            ]);
        }

        if ($grade) {
            if ($examId) {
                $grade->update([
                    'class_exam' => $grade->class_exam + 0,
                    'graded_at' => Carbon::now('Asia/Jakarta'),
                ]);
            } elseif ($schoolExamId) {
                $grade->update([
                    'exam' => $grade->exam + 0,
                    'graded_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } else {
            $generateGradeId = IdGenerator::generate(['table' => 'grades', 'length' => 16, 'prefix' => 'GRA-']);
            Grade::create([
                'id' => $generateGradeId,
                'response_id' => $response->id,
                'class_exam' => $examId ? 0 : null,
                'exam' => $schoolExamId ? 0 : null,
                'graded_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }
    }

    private function handleChoiceAnswer($answer, $existingAnswer, $response, $question, $grade, $examId, $schoolExamId)
    {
        $questionChoice = Choices::find($answer['choice_id']);

        if (!$questionChoice) {
            return $this->sendError('Choice tidak ditemukan.', null, 200);
        }

        $isCorrect = $questionChoice->is_true && $questionChoice->choice_text === $answer['answer_text'];
        $point = $isCorrect ? $question->point : 0;

        if ($existingAnswer) {
            $previousChoice = Choices::find($existingAnswer->choice_id);
            $previousIsCorrect = $previousChoice ? $previousChoice->is_true && $previousChoice->choice_text === $existingAnswer->answer_text : false;
            $previousPoint = $previousIsCorrect ? $question->point : 0;

            $existingAnswer->update([
                'choice_id' => $questionChoice->id,
                'answer_text' => $answer['answer_text'],
                'is_graded' => true,
            ]);

            if ($grade) {
                if ($examId) {
                    $grade->update([
                        'class_exam' => $grade->class_exam - $previousPoint + $point,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                } elseif ($schoolExamId) {
                    $grade->update([
                        'exam' => $grade->exam - $previousPoint + $point,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            } else {
                $generateGradeId = IdGenerator::generate(['table' => 'grades', 'length' => 16, 'prefix' => 'GRA-']);
                Grade::create([
                    'id' => $generateGradeId,
                    'response_id' => $response->id,
                    'class_exam' => $examId ? $point : null,
                    'exam' => $schoolExamId ? $point : null,
                    'graded_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        } else {
            $generateAnswerId = IdGenerator::generate(['table' => 'answers', 'length' => 16, 'prefix' => 'ANS-']);
            Answer::create([
                'id' => $generateAnswerId,
                'choice_id' => $questionChoice->id,
                'response_id' => $response->id,
                'question_id' => $answer['question_id'],
                'answer_text' => $answer['answer_text'],
                'is_graded' => true,
            ]);

            if ($grade) {
                if ($examId) {
                    $grade->update([
                        'class_exam' => $grade->class_exam + $point,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                } elseif ($schoolExamId) {
                    $grade->update([
                        'exam' => $grade->exam + $point,
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            } else {
                $generateGradeId = IdGenerator::generate(['table' => 'grades', 'length' => 16, 'prefix' => 'GRA-']);
                Grade::create([
                    'id' => $generateGradeId,
                    'response_id' => $response->id,
                    'class_exam' => $examId ? $point : null,
                    'exam' => $schoolExamId ? $point : null,
                    'graded_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        }
    }

    private function handleComplexChoiceAnswer($answer, $existingAnswer, $response, $question, $grade, $examId, $schoolExamId)
    {
        // Periksa apakah 'choice_id' ada dan valid
        if (!isset($answer['choice_id']) || empty($answer['choice_id'])) {
            return $this->sendError('Choice ID tidak ditemukan dalam jawaban.', null, 200);
        }

        // Dapatkan semua pilihan untuk pertanyaan ini
        $questionChoices = Choices::where('question_id', $answer['question_id'])->get();

        // Hitung total poin yang dapat diperoleh untuk pertanyaan ini
        $totalPointsPossible = $question->point;

        // Dapatkan pilihan yang dipilih oleh pengguna
        $selectedChoiceId = $answer['choice_id'];

        // Dapatkan pilihan yang benar dari pertanyaan ini berdasarkan teks jawaban
        $correctChoices = $questionChoices->filter(function ($choice) use ($answer) {
            return $choice->is_true && $choice->id == $answer['choice_id'];
        });

        // Hitung jumlah pilihan benar yang ada pada pertanyaan ini
        $correctChoiceCount = $questionChoices->where('is_true', true)->count();

        // Jika tidak ada pilihan benar atau jumlah pilihan benar adalah nol, hindari pembagian oleh nol
        if ($correctChoiceCount == 0) {
            return $this->sendError('Tidak ada pilihan benar untuk pertanyaan ini.', null, 200);
        }

        // Hitung total poin yang diperoleh berdasarkan pilihan yang benar yang dipilih oleh pengguna
        $totalPointsEarned = $totalPointsPossible / $correctChoiceCount * $correctChoices->count();

        // Jika jawaban sebelumnya sudah ada
        if ($existingAnswer) {
            if ($existingAnswer->choice_id !== $selectedChoiceId) {
                $existingAnswer->update([
                    'choice_id' => $selectedChoiceId,
                    'answer_text' => $answer['answer_text'],
                    'is_graded' => true,
                ]);

                if ($grade) {
                    if ($examId) {
                        $totalGrade = $grade->class_exam + $totalPointsEarned;

                        if ($totalGrade > $totalPointsPossible) {
                            $totalGrade = $totalPointsPossible;
                        }

                        $grade->update([
                            'class_exam' => round($totalGrade),
                            'graded_at' => Carbon::now('Asia/Jakarta'),
                        ]);
                    } elseif ($schoolExamId) {
                        $totalGrade = $grade->exam + $totalPointsEarned;

                        if ($totalGrade > $totalPointsPossible) {
                            $totalGrade = $totalPointsPossible;
                        }

                        $grade->update([
                            'exam' => round($totalGrade),
                            'graded_at' => Carbon::now('Asia/Jakarta'),
                        ]);
                    }
                }
            }
        } else {
            $generateAnswerId = IdGenerator::generate(['table' => 'answers', 'length' => 16, 'prefix' => 'ANS-']);
            Answer::create([
                'id' => $generateAnswerId,
                'response_id' => $response->id,
                'question_id' => $answer['question_id'],
                'answer_text' => $answer['answer_text'],
                'choice_id' => $selectedChoiceId,
                'is_graded' => true,
            ]);

            if ($grade) {
                if ($examId) {
                    $grade->update([
                        'class_exam' => $grade->class_exam + round($totalPointsEarned),
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                } elseif ($schoolExamId) {
                    $grade->update([
                        'exam' => $grade->exam + round($totalPointsEarned),
                        'graded_at' => Carbon::now('Asia/Jakarta'),
                    ]);
                }
            } else {
                $generateGradeId = IdGenerator::generate(['table' => 'grades', 'length' => 16, 'prefix' => 'GRA-']);
                Grade::create([
                    'id' => $generateGradeId,
                    'response_id' => $response->id,
                    'class_exam' => $examId ? round($totalPointsEarned) : null,
                    'exam' => $schoolExamId ? round($totalPointsEarned) : null,
                    'graded_at' => Carbon::now('Asia/Jakarta'),
                ]);
            }
        }
    }

    public function update_grade_for_essay(Request $request, $id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role !== 'TEACHER') {
            return $this->sendError('Anda tidak memiliki izin untuk melakukan tindakan ini.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'point' => 'required|numeric|min:0',
            'exam_type' => 'required|in:school,class',
        ], [
            'point.required' => 'Ups, Anda Belum Melengkapi Form',
            'point.numeric' => 'Nilai harus berupa angka',
            'point.min' => 'Nilai tidak boleh kurang dari 0',
            'exam_type.required' => 'Tipe ujian harus diisi.',
            'exam_type.in' => 'Tipe ujian tidak valid.',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $examType = $request->input('exam_type');
        $teacher = $userLogin->is_teacher;

        if ($examType === 'class') {
            $learningIds = Learning::where('teacher_id', $teacher->id)->pluck('id');
            $examIds = ClassExam::whereIn('learning_id', $learningIds)->pluck('id');
            $responseAnswerIds = Response::whereIn('exam_id', $examIds)->pluck('id');
        } elseif ($examType === 'school') {
            $teacherPenilai = ExamTeacher::where('teacher_id', $teacher->id)->first();
            if (!$teacherPenilai || $teacherPenilai->role !== "PENILAI") {
                return $this->sendError('Anda tidak memiliki izin untuk melakukan tindakan ini.', null, 200);
            }
            $responseAnswerIds = Response::whereIn('school_exam_id', $teacherPenilai->pluck('exam_id'))->pluck('id');
        } else {
            return $this->sendError('Tipe ujian tidak valid.', null, 200);
        }

        if ($responseAnswerIds->isEmpty()) {
            return $this->sendError('Jawaban tidak ditemukan.', null, 200);
        }

        $answer = Answer::whereIn('response_id', $responseAnswerIds)->find($id);

        if (!$answer) {
            return $this->sendError('Jawaban tidak ditemukan.', null, 200);
        }

        $questionTypeEssay = Question::where('id', $answer->question_id)->where('question_type', 'Essay')->exists();

        if (!$questionTypeEssay) {
            return $this->sendError('Pertanyaan tidak ditemukan atau bukan tipe Essay.', null, 200);
        }

        $grade = Grade::where('response_id', $answer->response_id)->first();

        if (!$grade) {
            return $this->sendError('Grade tidak ditemukan.', null, 200);
        }

        $response = Response::find($answer->response_id);
        if ($examType === 'class' && $response->exam_id) {
            $grade->update([
                'class_exam' => $grade->class_exam + $request->point,
                'graded_at' => Carbon::now('Asia/Jakarta'),
            ]);
        } elseif ($examType === 'school' && $response->school_exam_id) {
            $grade->update([
                'exam' => $grade->exam + $request->point,
                'graded_at' => Carbon::now('Asia/Jakarta'),
            ]);
        } else {
            return $this->sendError('Jenis ujian tidak dapat ditentukan atau tidak cocok.', null, 200);
        }

        $answer->update([
            'is_graded' => true,
        ]);

        return $this->sendResponse(null, 'Nilai berhasil diperbarui.');
    }

    public function update_rekap_nilai(Request $request, $id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role !== 'TEACHER') {
            return $this->sendError('Anda tidak memiliki izin untuk melakukan tindakan ini.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'knowledge' => 'nullable',
            'skills' => 'nullable',
            'publication_status' => 'required',
        ], [
            'publication_status.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $grade = Grade::find($id);

        if (!$grade) {
            return $this->sendError('Grade tidak ditemukan.', null, 200);
        }

        $grade->update([
            'knowledge' => $request->knowledge ?? $grade->knowledge,
            'skills' => $request->skills ?? $grade->skills,
            'class_exam' => $request->exam ?? $grade->class_exam,
            'publication_status' => $request->publication_status,
            'status' => "Tersimpan",
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        return $this->sendResponse($grade, 'Nilai berhasil diperbarui.');
    }
}
