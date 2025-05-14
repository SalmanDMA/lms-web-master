<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolExamController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
        $this->authorizeTeacher();
    }

    public function v_ujian(Request $request)
    {
        $viewData = $this->prepareViewData('/api/v1/cms/teacher/school-exam');
        return view('teacher.sekolah.ujian.index', $viewData);
    }

    public function v_ujian_detail(Request $request, $ujian_id)
    {
        $viewData = $this->prepareViewData("/api/v1/cms/teacher/school-exam/{$ujian_id}", $ujian_id);
        return view('teacher.sekolah.ujian.edit', $viewData);
    }

    public function edit_ujian(Request $request, $ujian_id)
    {
        $validator = $this->validateUlangan($request);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $school_exam = $this->fetchData("/api/v1/cms/teacher/school-exam/{$ujian_id}")->data ?? null;

        $form_data = $this->prepareUlanganData($request);
        $form_data['title'] = $school_exam->title;
        $form_data['description'] = $school_exam->description;
        $form_data['type'] = $school_exam->type;
        $form_data['instruction'] = $school_exam->instruction;
        $form_data['course'] = $school_exam->course->id;
        $form_data['publication_status'] = $school_exam->publication_status;
        $form_data['class_level'] = $school_exam->class_level;
        $form_data['academic_year'] = $school_exam->academic_year;
        $form_data['semester'] = $school_exam->semester;

        $response_data = $this->putData('/api/v1/cms/teacher/school-exam/' . $ujian_id, $form_data, 'json');

        return $this->handleUlanganResponse($response_data, 'edit', $ujian_id);
    }

    public function edit_ujian_is_active(Request $request, $ujian_id)
    {
        $request->merge(['is_active' => $request->is_active === 'on' ? 1 : 0]);

        $validator = Validator::make($request->all(), [
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $response_data = $this->putData('/api/v1/cms/teacher/school-exam/' . $ujian_id . '/update-is-active', ['is_active' => $request->is_active], 'json');

        return $this->handleUlanganResponse($response_data, 'edit', $ujian_id);
    }

    private function prepareViewData($apiUrl, $ujian_id = null)
    {
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $schoolExams = $this->fetchData($apiUrl)->data ?? [];

        // dd($schoolExams);

        if (is_array($schoolExams)) {
            foreach ($schoolExams as &$item) {
                // dd($item, $courseData);
                if (is_object($item)) {
                    $item->courses_name = $this->transformCourse($courseData, $item->course->id);
                    // dd($item);
                    $item->class_level = $this->transformLevel($levels, $item->class_level);
                }
            }
        } elseif (is_object($schoolExams)) {
            $schoolExams->courses_name = $this->transformCourse($courseData, $schoolExams->course->id);
            $schoolExams->class_level = $this->transformLevel($levels, $schoolExams->class_level);
        }

        // dd($schoolExams);

        $viewData = [
            'school_exams' => $schoolExams,
            'courses' => $courseData,
            'levels' => $levels,
            'role_teacher' => $schoolExams->teacher_exam ?? null,
        ];

        if ($ujian_id) {
            $viewData = array_merge($viewData, $this->prepareDetailViewData($ujian_id, $schoolExams));
        }

        return $viewData;
    }

    private function prepareDetailViewData($ujian_id, $schoolExam)
    {
        $categories = $this->fetchData('/api/v1/cms/teacher/category-question')->data ?? [];
        $questions = collect($this->fetchData('/api/v1/cms/teacher/question/school')->data ?? [])
            ->filter(fn($q) => $q->school_exam_id == $ujian_id)
            ->map(fn($q) => $this->mapQuestionCategory($q, $categories));

        $examSections = collect($this->fetchData('/api/v1/cms/teacher/exam-section/')->data ?? [])
            ->filter(fn($section) => $section->exam_id == $ujian_id);

        $bankQuestions = $this->filterBankQuestions($schoolExam, $categories);

        return [
            'ujian_id' => $ujian_id,
            'exams' => $schoolExam,
            'courses_name' => $schoolExam->courses_name ?? [],
            'class_level' => $schoolExam->class_level ?? [],
            'class_levels' => $this->fetchLevels(),
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $categories,
            'questions' => $questions,
            'exam_sections' => $examSections,
            'bank_questions' => $bankQuestions->values()->toArray(),
        ];
    }

    private function mapQuestionCategory($question, $categories)
    {
        $category = collect($categories)->firstWhere('id', $question->category_id);
        $question->category_name = $category->name ?? 'Unknown';
        return $question;
    }

    private function filterBankQuestions($schoolExam, $categories)
    {
        $categoryCollection = collect($categories);
        $filtered_bank_questions = collect($this->fetchData('/api/v1/cms/teacher/question-bank')->data ?? [])
            ->filter(function ($question) use ($schoolExam) {
                return $schoolExam &&
                    $question->course == $schoolExam->courses_name['id'] &&
                    $question->class_level == $schoolExam->class_level['id'];
            });

        return $filtered_bank_questions->map(function ($question) use ($categoryCollection) {
            $category = $categoryCollection->firstWhere('id', $question->category_id);
            $question->category_name = $category ? $category->name : 'Unknown';
            return $question;
        });
    }

    private function validateUlangan(Request $request)
    {
        $request->merge([
            'is_random_question' => $request->boolean('is_random_question'),
            'is_random_answer' => $request->boolean('is_random_answer'),
            'is_show_score' => $request->boolean('is_show_score'),
            'is_show_result' => $request->boolean('is_show_result'),
        ]);

        return Validator::make($request->all(), [
            'status' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'token' => 'nullable|string|max:255',
            'duration' => 'required|date_format:H:i:s',
            'repeat_chance' => 'required|integer',
            'device' => 'nullable|in:Web,Mobile,All',
            'maximum_user' => 'required|integer',
            'is_random_question' => 'boolean',
            'is_random_answer' => 'boolean',
            'is_show_score' => 'boolean',
            'is_show_result' => 'boolean',
        ]);
    }

    private function prepareUlanganData(Request $request)
    {
        return [
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'instruction' => $request->instruction,
            'course' => $request->course,
            'publication_status' => $request->publication_status,
            'class_level' => $request->class_level,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'status' => 'INACTIVE',
            'start_time' => Carbon::parse($request->start_time)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('Y-m-d H:i:s'),
            'token' => $request->token,
            'duration' => Carbon::parse($request->duration)->format('H:i'),
            'repeat_chance' => $request->repeat_chance,
            'device' => $request->device,
            'maximum_user' => $request->maximum_user,
            'is_active' => $request->is_active,
            'is_random_question' => $request->is_random_question,
            'is_random_answer' => $request->is_random_answer,
            'is_show_score' => $request->is_show_score,
            'is_show_result' => $request->is_show_result,
        ];
    }

    private function handleUlanganResponse($response_data, $type, $ujian_id)
    {
        $message = $this->getResponseMessage($response_data->success ?? false, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $dataToSend = $this->prepareViewData('/api/v1/cms/teacher/school-exam');

        return redirect()->route('teacher.sekolah.v_ujian_detail', ['ujian_id' => $ujian_id])
            ->with('message', $message)->with('alertClass', $alertClass)->with('dataToSend', $dataToSend);
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

    private function getResponseMessage($success, $type, $message)
    {

        if ($success) {
            switch ($type) {
                case 'add':
                    return 'Data berhasil ditambahkan.';
                case 'edit':
                    return 'Data berhasil diperbarui.';
                case 'delete':
                    return 'Data berhasil dihapus.';
                case 'import':
                    return 'Data berhasil diimport.';
                default:
                    return $message;
            }
        }

        return 'Terjadi kesalahan: ' . $message;
    }
}
