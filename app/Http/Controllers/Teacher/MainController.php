<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
    }

    public function v_dashboard()
    {
        try {
            $token = session('token');
            $role = session('role');

            if (!$token || $role !== 'TEACHER') {
                return redirect('/login');
            }

            $responseQuestionsBank = $this->client->get('/api/v1/cms/teacher/question-bank', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $questionBanksResponse = json_decode($responseQuestionsBank->getBody()->getContents(), true);
            $questionBanks = $questionBanksResponse['data'] ?? [];
            $questionBankCount = count($questionBanks);

            $responseAssignments = $this->client->get('/api/v1/cms/teacher/assignment-bank', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $assignmentBanksResponse = json_decode($responseAssignments->getBody()->getContents(), true);
            $assignmentBanks = $assignmentBanksResponse['data'] ?? [];
            $assignmentBankCount = count($assignmentBanks);

            $responseAssignmentsActive = $this->client->get('/api/v1/mobile/teacher/assignment', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $assignmentsActiveResponse = json_decode($responseAssignmentsActive->getBody()->getContents(), true);
            $assignmentsActive = $assignmentsActiveResponse['data'] ?? [];
            $assignmentsActiveCount = count($assignmentsActive);

            $responseClassExam = $this->client->get('/api/v1/cms/teacher/class-exam', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $classExamResponse = json_decode($responseClassExam->getBody()->getContents(), true);
            $classExam = $classExamResponse['data'] ?? [];
            $classExamCount = count($classExam);

            $responseNotification = $this->fetchData('/api/v1/mobile/teacher/notification');
            $unreadNotifications = collect($responseNotification->data ?? [])->where('is_read', 0)->count();

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('teacher.dashboard', [
                'assignmentBankCount' => $assignmentBankCount,
                'questionBankCount' => $questionBankCount,
                'assignmentsActiveCount' => $assignmentsActiveCount,
                'classExamCount' => $classExamCount,
                'unreadNotifications' => $unreadNotifications,
                'customTheme' => $customTheme,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch data from API: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        if (session()->has('token')) {
            $response = $this->client->get('/api/v1/auth/logout', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if ($response_data->success) {
                session()->flush();
                return redirect('/login/teacher');
            }

            return back();
        } else {
            return redirect('/login');
        }
    }
}