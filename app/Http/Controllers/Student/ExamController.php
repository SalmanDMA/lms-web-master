<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
    }

    function submitExam(Request $request)
    {
        // Response id that were created prior to starting the exam.
        // API will determine whether its a regular exam or school exam
        // using their respective id.
        $request->merge([
            'response_id' => session('response_id'),
            'exam_id' => session('tmp_exam_id'),
            'school_exam_id' => session('tmp_school_exam_id'),
        ]);

        // Validation rules required by API
        $validated = $request->validate([
            'response_id' => 'required|exists:responses,id',
            'exam_id' => 'nullable|exists:class_exams,id',
            'school_exam_id' => 'nullable|exists:school_exams,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_text' => 'required',
            'answers.*.choice_id' => 'nullable|exists:choices,id'
        ], [
            'exam_id.exists' => 'Ups, Id Exam Tidak Valid',
            'school_exam_id.exists' => 'Ups, Id School Exam Tidak Valid',
            'answers.*.question_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'answers.*.answer_text.required' => 'Ups, Anda Belum Melengkapi Form',
            'answers.*.question_id.exists' => 'Ups, Id Question Tidak Valid',
            'answers.*.choice_id.exists' => 'Ups, Id Choice Tidak Valid',
        ]);

        if (session('role') !== 'STUDENT' && !session()->has('token')) {
            return Inertia::location('/login');
        }

        $response = $this->client->post('/api/v1/mobile/student/answer', [
            'form_params' => $validated,
            'headers' => ['Authorization' => 'Bearer ' . session('token')]
        ]);

        $response_data = json_decode($response->getBody()->getContents());

        if (!$response_data->success) {
            return back()->withErrors([
                'title' => 'Terjadi kesalahan saat menyimpan jawaban',
                'message' => $response_data->message,
            ]);
        }

        // Flush the current exam information
        session()->forget([
            'exam_id',
            'tmp_exam_id',
            'tmp_school_exam_id',
            'response_id',
        ]);

        return redirect('/student/school-exam')->with('flash_message', 'Selamat anda telah menyelesaikan ulangan');
    }
}
