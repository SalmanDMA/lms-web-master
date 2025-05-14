<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RppDraftController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_add_draft_rpp($rpp_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $responseRppDraft = $this->fetchData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id);

        if (isset($responseRppDraft->data) && !empty($responseRppDraft->data)) {
            usort($responseRppDraft->data, function ($a, $b) {
                return strtotime($b->updated_at) - strtotime($a->updated_at);
            });

            $latestDraft = $responseRppDraft->data[0];

            switch ($latestDraft->status) {
                case 'Dalam Proses':
                    $message = 'RPP terbaru anda sedang dalam proses peninjauan.';
                    $alertClass = 'alert-warning';
                    return  $this->redirectToRppDetailView($message, $alertClass, $rpp_id);
                case 'Diterima':
                    $message = 'RPP terbaru anda telah diterima.';
                    $alertClass = 'alert-success';
                    return $this->redirectToRppDetailView($message, $alertClass, $rpp_id);
                default:
                    break;
            }
        }

        $academic_years = $this->generateAcademicYears();
        $status = ['Dalam Proses', 'Diterima', 'Ditolak', 'Dibatalkan'];
        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();

        $learningTeacher = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($course_data)->whereIn('id', $learningCourseIds);

        return view('teacher.bank.rpp.draft.add', [
            'rpp_id' => $rpp_id,
            'academic_years' => $academic_years,
            'status' => $status,
            'courses' => $filteredCourseDataForShare,
            'levels' => $levels,
        ]);
    }

    public function add_draft_rpp(Request $request, $rpp_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = $this->validateRpp($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->extractFormDataRpp($request);
        $response_data = $this->postData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id, $formData, 'json');

        if ($response_data->success) {
            $message = "Draft RPP berhasil ditambahkan!";
            return $this->redirectToRppDetailView($message, 'alert-success', $rpp_id);
        } else {
            $message = "Draft RPP gagal ditambahkan!";
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        }
    }

    public function ajukan_draft_rpp(Request $request, $rpp_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $json_decode = json_decode($request->draft);

        $request->merge([
            'course' => $json_decode->courses->id,
            'level' => $json_decode->class_level->id,
            'draftName' => $json_decode->draft_name,
            'academicYear' => $json_decode->academic_year->id,
            'semester' => $json_decode->semester
        ]);

        $formData = $this->extractFormDataRpp($request);

        $response_data = $this->postData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id, $formData, 'json');

        if ($response_data->success) {
            $new_rpp_id = $response_data->data->id;

            if (isset($json_decode->subject_matters) && !empty($json_decode->subject_matters)) {
                foreach ($json_decode->subject_matters as $subject_matter) {
                    $subjectRequest = new Request([
                        'title' => $subject_matter->title,
                        'time_allocation' => $subject_matter->time_allocation,
                        'learning_goals' => $subject_matter->learning_goals,
                        'learning_activity' => $subject_matter->learning_activity,
                        'grading' => $subject_matter->grading,
                        'rpp_draft_id' => $new_rpp_id
                    ]);

                    $subjectData = $this->prepareSubjectMatterData($subjectRequest, $new_rpp_id);
                    $response_data_subject = $this->postData('/api/v1/cms/teacher/subject-draft', $subjectData, 'json');

                    if (!$response_data_subject->success) {
                        $message = "Subject matter gagal ditambahkan!";
                        return redirect()->back()->withInput()->withErrors(['message' => $message]);
                    }
                }
            }

            $message = "Draft RPP berhasil ditambahkan!";
            return $this->redirectToRppDetailView($message, 'alert-success', $rpp_id);
        } else {
            $message = "Draft RPP gagal ditambahkan!";
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        }
    }

    public function download_draft($rpp_id, $rpp_draft_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $academic_years = $this->generateAcademicYears();
        $responseRppDraft = $this->fetchData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id . '/' . $rpp_draft_id);
        $me = $this->fetchData('/api/v1/auth/profile/me');

        if ($responseRppDraft && isset($responseRppDraft->data)) {
            $responseRppDraft->data->courses = $this->transformCourse($course_data, $responseRppDraft->data->courses);
            $responseRppDraft->data->class_level = $this->transformLevel($levels, $responseRppDraft->data->class_level);
            $responseRppDraft->data->academic_year = $this->transformAcademicYear($academic_years, $responseRppDraft->data->academic_year);
        }

        $pdf = Pdf::loadView('pdf.draft-rpp', ['draft' => $responseRppDraft->data, 'user' => $me->data]);

        $namePdf = 'draft-rpp-' . '-' . $rpp_draft_id . '.pdf';
        return $pdf->stream($namePdf);
    }


    public function v_edit_draft_rpp($rpp_id, $rpp_draft_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $responseRppDraft = $this->fetchData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id . '/' . $rpp_draft_id);

        $academic_years = $this->generateAcademicYears();
        $status = ['Dalam Proses', 'Diterima', 'Ditolak', 'Dibatalkan'];
        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();

        $learningTeacher = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($course_data)->whereIn('id', $learningCourseIds);

        return view('teacher.bank.rpp.draft.edit', [
            'rpp_id' => $rpp_id,
            'rpp_draft_id' => $rpp_draft_id,
            'draft' => $responseRppDraft->data,
            'academic_years' => $academic_years,
            'status' => $status,
            'courses' => $filteredCourseDataForShare,
            'levels' => $levels,
        ]);
    }

    public function edit_draft_rpp(Request $request, $rpp_id, $rpp_draft_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = $this->validateRpp($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $response_draft_id = $this->fetchData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id . '/' . $rpp_draft_id);

        $formData = $this->extractFormDataRpp($request);
        $formData['status'] = $response_draft_id->data->status;

        $response_data = $this->putData('api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id . '/' . $rpp_draft_id, $formData, 'json');

        if ($response_data->success) {
            $message = "Draft RPP berhasil diubah!";
            return $this->redirectToRppDetailView($message, 'alert-success', $rpp_id);
        } else {
            $message = "Gagal mengubah draft RPP: " . $response_data->message;
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        }
    }

    public function batalkan_draft_rpp($rpp_id, $rpp_draft_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $responseRppDraft = $this->fetchData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id . '/' . $rpp_draft_id);

        $formData = [
            'courses' => $responseRppDraft->data->courses,
            'class_level' => $responseRppDraft->data->class_level,
            'draft_name' => $responseRppDraft->data->draft_name,
            'academic_year' => $responseRppDraft->data->academic_year,
            'semester' => $responseRppDraft->data->semester,
            'status' => 'Dibatalkan',
        ];

        $responseUpdateRppDraft = $this->putData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id . '/' . $rpp_draft_id, $formData, 'json');

        if ($responseUpdateRppDraft->success) {
            $message = "Draft RPP berhasil dibatalkan!";
            return $this->redirectToRppDetailView($message, 'alert-success', $rpp_id);
        } else {
            $message = 'Gagal membatalkan draft RPP: ' . $responseUpdateRppDraft->message;
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        }
    }

    private function redirectToRppDetailView($message = null, $alertClass = null, $rpp_id)
    {
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $academic_years = $this->generateAcademicYears();
        $rppResponse = $this->fetchData('/api/v1/cms/teacher/rpp/' . $rpp_id);
        $rppDraftResponse = $this->fetchData('/api/v1/cms/teacher/rpp-draft/rpp/' . $rpp_id);
        $me = $this->fetchData('/api/v1/auth/profile/me');

        if ($this->isValidResponse($rppResponse)) {
            $this->transformRppData($rppResponse->data, $courseData, $levels, $academic_years);
        }

        if ($this->isValidResponse($rppDraftResponse)) {
            foreach ($rppDraftResponse->data as &$draft) {
                $this->transformRppData($draft, $courseData, $levels, $academic_years);
            }

            $rppDraftResponse->data = collect($rppDraftResponse->data)
                ->sortByDesc(function ($draft) {
                    return $draft->updated_at ?: $draft->created_at;
                })
                ->values()
                ->all();
        } else {
            $rppDraftResponse = [];
        }

        return redirect()->route('teacher.pengajar.v_rpp_detail', ['id' => $rpp_id])->with('message', $message)->with('alertClass', $alertClass)->with('rpp', $responseRpp->data ?? null)->with('draft', $responseRppDraft ?? null)->with('me', $me->data ?? null);
    }

    private function transformRppData(&$rppData, $courseData, $levels, $academic_year)
    {
        $rppData->courses = $this->transformCourse($courseData, $rppData->courses);
        $rppData->class_level = $this->transformLevel($levels, $rppData->class_level);
        $rppData->academic_year = $this->transformAcademicYear($academic_year, $rppData->academic_year);
    }

    private function isValidResponse($response)
    {
        return $response && isset($response->success) && $response->success && isset($response->data);
    }

    private function extractFormDataRpp(Request $request)
    {
        return [
            'courses' => $request->course,
            'class_level' => $request->level,
            'draft_name' => $request->draftName,
            'status' => 'Dalam Proses',
            'academic_year' => $request->academicYear,
            'semester' => $request->semester
        ];
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
            'rpp_draft_id' => $rpp_id
        ];
    }


    private function validateRpp(Request $request)
    {
        return Validator::make($request->all(), [
            'course' => 'required',
            'level' => 'required',
            'draftName' => 'required',
            'academicYear' => 'required',
            'semester' => 'required',
        ]);
    }

    public function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/mobile/teacher/course');
        return $response_data->data ?? [];
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
}
