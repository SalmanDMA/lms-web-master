<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class RppBankController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_bank_rpp(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $courseData = $this->fetchCourses();
        $academicYears = $this->generateAcademicYears();
        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.rpp.bank.index', [
            'courses' => $courseData,
            'levels' => $this->fetchLevels(),
            'academic_years' => $academicYears,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function v_bank_rpp_detail($id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $endpoint = null;
        if (strpos($id, 'RPPB-') === 0) {
            $endpoint = '/api/v1/cms/teacher/rpp-bank/' . $id;
        } elseif (strpos($id, 'RPPD-') === 0) {
            $endpoint = '/api/v1/cms/teacher/rpp-draft/' . $id;
        } else {
            $endpoint = '/api/v1/cms/teacher/rpp/' . $id;
        }

        $data = $this->fetchData($endpoint);
        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.rpp.bank.detail', [
            'data' => $data->data,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function addBank_rpp(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = Validator::make($request->all(), [
            'course' => 'required|exists:courses,id',
            'level' => 'required|string',
            'draftName' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $formData = $this->extractFormData($request);
        $response_data = $this->postData('/api/v1/cms/teacher/rpp-bank', $formData, 'json');

        $message = $response_data->success ? 'RPP berhasil ditambahkan!' : 'Gagal menambahkan RPP: ' . $response_data->message;
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $data = $this->fetchData('/api/v1/cms/teacher/rpp-bank/' . $response_data->data->id);

        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.rpp.bank.detail', [
            'message' => $message,
            'alertClass' => $alertClass,
            'data' => $data->data,
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function updateBank_rpp(Request $request, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $formData = $this->extractFormData($request);
        $response_data = $this->putData('/api/v1/cms/teacher/rpp-bank/' . $id, $formData, 'json');

        $message = $response_data->success ? 'RPP berhasil dirubah!' : 'Gagal merubah RPP: ' . $response_data->message;
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        return $this->redirectToRppView($message, $alertClass);
    }

    public function deleteBank_rpp($id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $response_data = $this->deleteData('/api/v1/cms/teacher/rpp-bank/' . $id);

        $message = $response_data->success ? 'RPP berhasil dihapus!' : 'Gagal menghapus RPP: ' . $response_data->message;
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        return $this->redirectToRppView($message, $alertClass);
    }

    public function ajukanBank_rpp(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = Validator::make($request->all(), [
            'semester' => 'required|string',
            'academicYear' => 'required|string',
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $formData = $this->extractFormDataRpp($request);

        $rppData = $this->fetchData('/api/v1/cms/teacher/rpp-bank/' . $formData['id']);

        $submissionData = [
            'courses' => $rppData->data->courses,
            'class_level' => $rppData->data->class_level,
            'draft_name' => $rppData->data->draft_name,
            'academic_year' => $request->academicYear,
            'semester' => $request->semester,
            'status' => 'Dalam Proses',
        ];

        $response = $this->postData('/api/v1/cms/teacher/rpp', $submissionData, 'json');

        if (!$response->success) {
            $message = 'Gagal mengajukan RPP: ' . $response->message;
            return $this->redirectToRppView($message, 'alert-danger');
        }

        $rppId = $response->data->id;

        $subjectMatters = $rppData->data->subject_matters;

        if (!empty($subjectMatters)) {
            foreach ($subjectMatters as $subjectMatter) {
                $timeAllocation = date('H:i', strtotime($subjectMatter->time_allocation));

                $formattedSubjectMatter = [
                    'rpp_id' => $rppId,
                    'title' => $subjectMatter->title,
                    'time_allocation' => $timeAllocation,
                    'learning_goals' => $subjectMatter->learning_goals,
                    'learning_activity' => $subjectMatter->learning_activity,
                    'grading' => $subjectMatter->grading,
                ];

                $this->postData('/api/v1/cms/teacher/subject', $formattedSubjectMatter, 'json');
            }
        }

        $message = "RPP berhasil diajukan!";
        return $this->redirectToRppView($message, 'alert-success');
    }


    private function extractFormData(Request $request)
    {
        return [
            'courses' => $request->course,
            'class_level' => $request->level,
            'draft_name' => $request->draftName,
            'status' => 'Active'
        ];
    }

    private function extractFormDataRpp(Request $request)
    {
        return [
            'academic_year' => $request->academicYear,
            'semester' => $request->semester,
            'id' => $request->id,
        ];
    }

    private function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/course');
        return $response_data->success ? $response_data->data : [];
    }

    private function redirectToRppView($message, $alertClass)
    {
        $courseData = $this->fetchCourses();
        $rppData = $this->fetchData('/api/v1/cms/teacher/rpp-bank');
        $academicYears = $this->generateAcademicYears();
        $customTheme = $this->generateCustomThemes();
        $unreadNotifications = $this->generateNotifications();

        return view('teacher.bank.rpp.bank.index', [
            'message' => $message,
            'alertClass' => $alertClass,
            'courses' => $courseData,
            'academic_years' => $academicYears,
            'rpp' => $rppData,
            'levels' => $this->fetchLevels(),
            'customTheme' => $customTheme,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/class');
        return $response_data->data ?? [];
    }

    public function generateAcademicYears()
    {
        $response_data = $this->fetchData('/api/v1/cms/teacher/academic-year');
        return $response_data->data ?? [];
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
