<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class UjianController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
    }

    public function v_soal()
    {
        if (!session()->has('token') || !session('role') === 'ADMIN') {
            return redirect('/admin/login');
        }

        try {
            $questionData = $this->client->get('/api/v1/cms/admin/question', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $schoolExamData = $this->client->get('/api/v1/cms/admin/school-exam', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            return view('school.ujian.soal', [
                'title' => 'Soal Ujian',
                'questionData' => json_decode($questionData->getBody()->getContents())->data ?? [],
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function v_soalCreate()
    {
        if (!session()->has('token') || !session('role') === 'ADMIN') {
            return redirect('/admin/login');
        }

        try {
            $examSectionData = $this->client->get('/api/v1/cms/admin/exam-section', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $categoryQuestionData = $this->client->get('/api/v1/cms/admin/category-question', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $schoolExamData = $this->client->get('/api/v1/cms/admin/school-exam', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            return view('school.ujian.soal-create', [
                'title' => 'Tambah Soal Ujian',
                'examSectionData' => json_decode($examSectionData->getBody()->getContents())->data ?? [],
                'categoryQuestionData' => json_decode($categoryQuestionData->getBody()->getContents())->data ?? [],
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function v_soalUpdate($id)
    {
        if (!session()->has('token') || !session('role') === 'ADMIN') {
            return redirect('/admin/login');
        }

        try {
            $questionData = $this->client->get("/api/v1/cms/admin/question/$id", [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $examSectionData = $this->client->get('/api/v1/cms/admin/exam-section', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $categoryQuestionData = $this->client->get('/api/v1/cms/admin/category-question', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $schoolExamData = $this->client->get('/api/v1/cms/admin/school-exam', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            return view('school.ujian.soal-update', [
                'title' => 'Ubah Soal Ujian',
                'questionData' => json_decode($questionData->getBody()->getContents())->data ?? [],
                'examSectionData' => json_decode($examSectionData->getBody()->getContents())->data ?? [],
                'categoryQuestionData' => json_decode($categoryQuestionData->getBody()->getContents())->data ?? [],
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
                'questionTypeData' => ['Pilihan Ganda', 'Pilihan Ganda Complex', 'True False', 'Essay'],
                'difficultyData' => ['Mudah', 'Sedang', 'Sulit'],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}
