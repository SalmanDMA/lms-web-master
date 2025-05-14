<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class SubjectMatterBankController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_subject_matter_detail(Request $request, $rpp_id, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $dataSubject = $this->fetchSubjectMatterData($id);
        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.rpp.subject.detail', [
            'dataSubject' => $dataSubject->data,
            'rpp_id' => $rpp_id,
            'subject_matter_id' => $id,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function v_add_subject_matter(Request $request, $rpp_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.rpp.subject.add', [
            'rpp_id' => $rpp_id,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function add_subject_matter(Request $request, $rpp_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = $this->validateSubjectMatter($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareSubjectMatterData($request, $rpp_id);
        $response_data = $this->postData('/api/v1/cms/teacher/subject-bank', $formData, 'form');

        return $this->handleResponse($response_data, $rpp_id, 'add');
    }

    public function v_edit_subject_matter(Request $request, $rpp_id, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $dataSubject = $this->fetchSubjectMatterData($id);
        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.rpp.subject.edit', [
            'dataSubject' => $dataSubject->data,
            'rpp_id' => $rpp_id,
            'subject_matter_id' => $id,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function edit_subject_matter(Request $request, $rpp_id, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = $this->validateSubjectMatter($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareSubjectMatterData($request, $rpp_id);
        $response_data = $this->putData('/api/v1/cms/teacher/subject-bank/' . $id, $formData, 'form');

        return $this->handleResponse($response_data, $rpp_id, 'edit');
    }

    public function delete_subject_matter($rpp_id, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $response_data = $this->deleteData('/api/v1/cms/teacher/subject-bank/' . $id);

        return $this->handleResponse($response_data, $rpp_id, 'delete');
    }

    private function validateSubjectMatter(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => 'required|string',
            'time_allocation' => 'required|string|date_format:H:i',
            'learning_goals' => 'required|string',
            'learning_activity' => 'required|string',
            'grading' => 'required|string',
        ]);
    }

    private function prepareSubjectMatterData(Request $request, $rpp_id)
    {
        $time_allocation = $this->formatTimeAllocation($request->time_allocation);

        return [
            'title' => $request->title,
            'time_allocation' => $time_allocation,
            'learning_goals' => $request->learning_goals,
            'learning_activity' => $request->learning_activity,
            'grading' => $request->grading,
            'rpp_bank_id' => $rpp_id
        ];
    }

    private function fetchSubjectMatterData($id)
    {
        return $this->fetchData('/api/v1/cms/teacher/subject-bank/' . $id);
    }

    private function handleResponse($response_data, $rpp_id, $type)
    {
        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        if ($response_data->success) {
            $dataRpp = $this->fetchData('/api/v1/cms/teacher/rpp-bank/' . $rpp_id);
            $message = '';
            $alertClass = 'alert-success';

            switch ($type) {
                case 'add':
                    $message = 'Materi pokok berhasil ditambahkan!';
                    break;
                case 'edit':
                    $message = 'Materi pokok berhasil dirubah!';
                    break;
                case 'delete':
                    $message = 'Materi pokok berhasil dihapus!';
                    break;
                default:
                    $message = 'Operasi berhasil!';
                    break;
            }

            return view('teacher.bank.rpp.bank.detail', [
                'message' => $message,
                'alertClass' => $alertClass,
                'data' => $dataRpp->data,
                'customTheme' => $customTheme,
                'unreadNotifications' => $unreadNotifications,
            ]);
        } else {
            $message = 'Gagal melakukan operasi: ' . $response_data->message;
            $alertClass = 'alert-danger';

            switch ($type) {
                case 'add':
                    return view('teacher.bank.rpp.subject.add', [
                        'message' => $message,
                        'alertClass' => $alertClass,
                        'rpp_id' => $rpp_id,
                        'customTheme' => $customTheme,
                        'unreadNotifications' => $unreadNotifications,
                    ]);
                case 'edit':
                    return view('teacher.bank.rpp.subject.edit', [
                        'message' => $message,
                        'alertClass' => $alertClass,
                        'rpp_id' => $rpp_id,
                        'subject_matter_id' => $response_data->id ?? null,
                        'customTheme' => $customTheme,
                        'unreadNotifications' => $unreadNotifications,
                    ]);
                case 'delete':
                    return view('teacher.bank.rpp.bank.detail', [
                        'message' => $message,
                        'alertClass' => $alertClass,
                        'data' => $this->fetchData('/api/v1/cms/teacher/rpp-bank/' . $rpp_id)->data,
                        'customTheme' => $customTheme,
                        'unreadNotifications' => $unreadNotifications,
                    ]);
                default:
                    return view('teacher.bank.rpp.bank.index', [
                        'message' => $message,
                        'alertClass' => $alertClass,
                        'customTheme' => $customTheme,
                        'unreadNotifications' => $unreadNotifications,
                    ]);
            }
        }
    }

    public function generateCustomThemes()
    {
        $customThemeData = $this->client->get('/api/v1/mobile/cms');
        $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

        if (isset($customTheme)) {
            Config::set('app.name', $customTheme->title);
        }

        return $customTheme;
    }

    public function generateNotifications()
    {
        $responseNotification = $this->fetchData('/api/v1/mobile/teacher/notification');
        $unreadNotifications = collect($responseNotification->data ?? [])->where('is_read', 0)->count();

        return $unreadNotifications;
    }
}
