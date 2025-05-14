<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RppController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function v_rpp(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        return $this->redirectToRppView(null, null);
    }

    public function v_rpp_detail(Request $request, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $academic_years = $this->generateAcademicYears();
        $rppResponse = $this->fetchData('/api/v1/cms/teacher/rpp/' . $id);
        $rppDraftResponse = $this->fetchData('/api/v1/cms/teacher/rpp-draft/rpp/' . $id);
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

        return view('teacher.bank.rpp.detail', [
            'rpp' => $rppResponse->data ?? [],
            'draft' => $rppDraftResponse->data ?? [],
            'me' => $me->data ?? null,
        ]);
    }

    public function v_add_rpp(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $academic_years = $this->generateAcademicYears();
        $status = ['Dalam Proses', 'Diterima', 'Ditolak', 'Dibatalkan'];
        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $learningTeacher = $this->fetchData('/api/v1/mobile/teacher/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($course_data)->whereIn('id', $learningCourseIds);


        return view('teacher.bank.rpp.add', [
            'academic_years' => $academic_years,
            'status' => $status,
            'courses' => $filteredCourseDataForShare,
            'levels' => $levels
        ]);
    }


    public function add_rpp(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $formData = $this->extractFormDataRpp($request);
        $response_data = $this->postData('/api/v1/cms/teacher/rpp', $formData, 'json');

        $message = $response_data->success ? 'RPP berhasil diajukan!' : 'Gagal mengajukan RPP: ' . $response_data->message;
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        return $this->redirectToRppView($message, $alertClass);
    }

    public function ajukan_rpp(Request $request)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = Validator::make($request->all(), [
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'rpp_bank_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $rppData = $this->fetchData('/api/v1/cms/teacher/rpp-bank/' . $request->rpp_bank_id);

        $submissionData = [
            'courses' => $rppData->data->courses,
            'class_level' => $rppData->data->class_level,
            'draft_name' => $rppData->data->draft_name,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'status' => 'Dalam Proses',
        ];

        $response = $this->postData('/api/v1/cms/teacher/rpp', $submissionData, 'json');

        if (!$response->success) {
            $message = 'Gagal mengajukan RPP: ' . $response->message;
            return $this->redirectToRppView($message, 'alert-danger');
        }

        $rppId = $response->data->id;
        $subjectMatters = $rppData->data->subject_matters ?? [];

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

    public function v_add_subject_matter(Request $request, $rpp_id, $rpp_draft_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        return view('teacher.bank.rpp.draft.subject.add', compact('rpp_id', 'rpp_draft_id'));
    }

    public function v_edit_subject_matter(Request $request, $rpp_id, $rpp_draft_id, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $dataSubject = $this->fetchData('/api/v1/cms/teacher/subject-draft/' . $id);

        return view('teacher.bank.rpp.draft.subject.edit', [
            'dataSubject' => $dataSubject->data,
            'rpp_id' => $rpp_id,
            'subject_matter_id' => $id,
            'rpp_draft_id' => $rpp_draft_id,
        ]);
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

    public function add_subject_matter(Request $request, $rpp_id, $rpp_draft_id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = $this->validateSubjectMatter($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareSubjectMatterData($request, $rpp_draft_id);
        $response_data = $this->postData('/api/v1/cms/teacher/subject-draft', $formData, 'form');

        if (!$response_data->success) {
            $message = 'Gagal menambahkan materi: ' . $response_data->message;
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        } else {
            $message = "Materi berhasil ditambahkan!";
            return $this->redirectToRppDetailView($message, 'alert-success', $rpp_id);
        }
    }

    public function edit_subject_matter(Request $request, $rpp_id, $rpp_draft_id, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $validator = $this->validateSubjectMatter($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $formData = $this->prepareSubjectMatterData($request, $rpp_draft_id);
        $response_data = $this->putData('/api/v1/cms/teacher/subject-draft/' . $id, $formData, 'form');

        if (!$response_data->success) {
            $message = 'Gagal mengubah materi: ' . $response_data->message;
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        } else {
            $message = "Materi berhasil dirubah!";
            return $this->redirectToRppDetailView($message, 'alert-success', $rpp_id);
        }
    }

    public function delete_subject_matter($rpp_id, $rpp_draft_id, $id)
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login');
        }

        $response_data = $this->deleteData('/api/v1/cms/teacher/subject-draft/' . $id);

        if (!$response_data->success) {
            $message = 'Gagal menghapus materi: ' . $response_data->message;
            return redirect()->back()->withInput()->withErrors(['message' => $message]);
        } else {
            $message = "Materi berhasil dihapus!";
            return $this->redirectToRppDetailView($message, 'alert-success', $rpp_id);
        }
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

    private function prepareSubjectMatterData(Request $request, $rpp_draft_id)
    {
        $time_allocation = $this->formatTimeAllocation($request->time_allocation);

        return [
            'title' => $request->title,
            'time_allocation' => $time_allocation,
            'learning_goals' => $request->learning_goals,
            'learning_activity' => $request->learning_activity,
            'grading' => $request->grading,
            'rpp_draft_id' => $rpp_draft_id,
        ];
    }

    private function redirectToRppView($message, $alertClass)
    {
        $academic_years = $this->generateAcademicYears();
        $status = ['Dalam Proses', 'Diterima', 'Ditolak', 'Dibatalkan'];
        $course_data = $this->fetchCourses();
        $rpp_data = $this->fetchData('/api/v1/cms/teacher/rpp');
        $rpp_bank = $this->fetchData('/api/v1/cms/teacher/rpp-bank');

        foreach ($rpp_data->data ?? [] as $rpp) {
            $course = collect($course_data)->firstWhere('id', $rpp->courses);
            $rpp->course_title = $course ? $course->courses_title : 'Unknown Course';
        }

        if ($this->isValidResponse($rpp_data)) {
            foreach ($rpp_data->data ?? [] as $rpp) {
                $rpp->academic_year = $this->transformAcademicYear($academic_years, $rpp->academic_year);
            }
        }

        return view('teacher.bank.rpp.index', [
            'academic_years' => $academic_years,
            'status' => $status,
            'courses' => $course_data,
            'rpp' => $rpp_data->data ?? [],
            'rpp_bank' => $rpp_bank->data ?? [],
            'message' => $message,
            'alertClass' => $alertClass,
        ]);
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
