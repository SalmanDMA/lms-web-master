<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class UjianController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
        $this->authorizeStaff();
    }

    public function v_ujian(Request $request)
    {
        $viewData = $this->prepareViewData('/api/v1/cms/staff-curriculum/school-exam');

        // dd($viewData);

        return view('staff-curriculum.sekolah.ujian.index', $viewData);
    }

    public function v_ujian_create()
    {
        $courses = $this->fetchCourses();
        $classes = $this->fetchLevels();
        $academicYears = $this->generateAcademicYears();

        return view('staff-curriculum.sekolah.ujian.create', [
            'title' => 'Tambah Ujian Sekolah',
            'courses' => $courses,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'examTypes' => [
                'UTS',
                'UAS',
                'PTS',
                'PTA',
                'UKK',
            ],
            'semester' => ['Genap', 'Ganjil']
        ]);
    }

    public function v_ujian_detail(Request $request, $id)
    {
        $viewData = $this->prepareViewData("/api/v1/cms/staff-curriculum/school-exam/{$id}", $id);

        return view('staff-curriculum.sekolah.ujian.edit', $viewData);
    }

    public function edit_ujian(Request $request, $id)
    {
        $validator = $this->validateUlangan($request);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $school_exam = $this->fetchData("/api/v1/cms/staff-curriculum/school-exam/{$id}")->data ?? null;

        $form_data = $this->prepareUlanganData($request);
        $form_data['title'] = $school_exam->title;
        $form_data['description'] = $school_exam->description;
        $form_data['type'] = $school_exam->type;
        $form_data['instruction'] = $school_exam->instruction;
        $form_data['course'] = $school_exam->course;
        $form_data['publication_status'] = $school_exam->publication_status;
        $form_data['class_level'] = $school_exam->class_level;
        $form_data['academic_year'] = $school_exam->academic_year;
        $form_data['semester'] = $school_exam->semester;

        $response_data = $this->putData('/api/v1/cms/staff-curriculum/school-exam/' . $id, $form_data, 'json');

        return $this->handleUlanganResponse($response_data, 'edit', $id);
    }

    public function delete_ujian($id)
    {
        $response_data = $this->deleteData('/api/v1/cms/staff-curriculum/school-exam/' . $id);

        $message = $this->getResponseMessage($response_data->success ?? false, 'delete', $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $dataToSend = $this->prepareViewData('/api/v1/cms/staff-curriculum/school-exam');

        return redirect()->route('staff_curriculum.sekolah.v_ujian')
            ->with('message', $message)
            ->with('alertClass', $alertClass)
            ->with('dataToSend', $dataToSend);
    }

    public function edit_ujian_is_active(Request $request, $id)
    {
        $request->merge(['is_active' => $request->is_active === 'on' ? 1 : 0]);

        $validator = Validator::make($request->all(), [
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $response_data = $this->putData('/api/v1/cms/staff-curriculum/school-exam/' . $id . '/update-is-active', ['is_active' => $request->is_active], 'json');

        return $this->handleUlanganResponse($response_data, 'edit', $id);
    }

    private function prepareViewData($apiUrl, $id = null)
    {
        $courseData = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $schoolExams = $this->fetchData($apiUrl)->data ?? [];

        // dd($this->fetchData($apiUrl), $apiUrl, $courseData, $levels, $schoolExams);

        if (is_array($schoolExams)) {
            foreach ($schoolExams as &$item) {
                if (is_object($item)) {
                    $item->courses_name = $this->transformCourse($courseData, $item->course);
                    $item->class_level = $this->transformLevel($levels, $item->class_level);
                }
            }
        } elseif (is_object($schoolExams)) {
            $schoolExams->courses_name = $this->transformCourse($courseData, $schoolExams->course);
            $schoolExams->class_level = $this->transformLevel($levels, $schoolExams->class_level);
        }

        $viewData = [
            'school_exams' => $schoolExams,
            'courses' => $courseData,
            'levels' => $levels,
            'role_teacher' => $schoolExams->teacher_exam ?? null,
        ];

        if ($id) {
            $viewData = array_merge($viewData, $this->prepareDetailViewData($id, $schoolExams));
        }

        return $viewData;
    }

    private function prepareDetailViewData($id, $schoolExam)
    {
        $categories = $this->fetchData('/api/v1/cms/staff-curriculum/category-question')->data ?? [];
        $questions = collect($this->fetchData('/api/v1/cms/staff-curriculum/question/school')->data ?? [])
            ->filter(fn($q) => $q->school_exam_id == $id)
            ->map(fn($q) => $this->mapQuestionCategory($q, $categories));

        $bankQuestions = $this->filterBankQuestions($schoolExam, $categories);

        $students = collect($this->fetchData('/api/v1/cms/staff-curriculum/register-exam/student/' . $id)?->data ?? []);
        $teachers = collect($this->fetchData('/api/v1/cms/staff-curriculum/register-exam/teacher/' . $id)?->data ?? []);

        $studentOptions = collect($this->fetchData('/api/v1/cms/staff-curriculum/user')?->data ?? [])->filter(function ($x) use ($students) {
            return $x->role == 'STUDENT' && !empty($x->is_student) && !$students->contains('student_id', '=', $x->is_student->id);
        });
        // dd($studentOptions, 'halo', $schoolExam);
        $studentOptions = $studentOptions->filter(function ($x) use ($schoolExam) {
            $enrollment = collect($x?->enrollment ?? []);

            return $enrollment->where('course.id', '=', $schoolExam?->course->id)
                ->where('subclass.class_id', '=', $schoolExam?->class_level['id'])->count() > 0;
        });


        $teacherOptions = collect($this->fetchData('/api/v1/cms/staff-curriculum/user')?->data ?? [])->filter(function ($x) use ($teachers) {
            return $x->role == 'TEACHER' && !empty($x->is_teacher) && !$teachers->contains('teacher_id', '=', $x->is_teacher->id);
        });

        $examSections = collect($this->fetchData('/api/v1/cms/staff-curriculum/exam-section/')->data ?? [])
            ->filter(function ($section) use ($id) {
                return $section->exam_id == $id;
            })
            ->toArray();

        return [
            'ujian_id' => $id,
            'exam' => $schoolExam,
            'courses_name' => $schoolExam->courses_name ?? [],
            'class_level' => $schoolExam->class_level ?? [],
            'class_levels' => $this->fetchLevels(),
            'question_type' => $this->generateQuestionTypes(),
            'question_category' => $categories,
            'questions' => $questions,
            'exam_sections' => $examSections,
            'bank_questions' => $bankQuestions->values()->toArray(),
            'students' => $students,
            'teachers' => $teachers,
            'studentOptions' => $studentOptions,
            'teacherOptions' => $teacherOptions,
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
        $filtered_bank_questions = collect($this->fetchData('/api/v1/cms/staff-curriculum/question-bank')->data ?? [])
            ->filter(function ($question) use ($schoolExam) {
                return $schoolExam &&
                    $question->course == (!empty($schoolExam->course) ? $schoolExam->course : null) &&
                    $question->class_level == (!empty($schoolExam->class_level) ? $schoolExam->class_level : null);
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
            'duration' => 'required|date_format:H:i',
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
            'start_time' => Carbon::parse($request->start_time)->format('Y-m-d'),
            'end_time' => Carbon::parse($request->end_time)->format('Y-m-d'),
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

    private function handleUlanganResponse($response_data, $type, $id)
    {
        $message = $this->getResponseMessage($response_data->success ?? false, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        $dataToSend = $this->prepareViewData('/api/v1/cms/staff-curriculum/school-exam');

        return redirect()->route('staff_curriculum.sekolah.v_ujian_detail', ['id' => $id])
            ->with('message', $message)->with('alertClass', $alertClass)->with('dataToSend', $dataToSend);
    }

    private function fetchCourses()
    {
        $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/course');
        return $response_data->data ?? [];
    }

    private function fetchLevels()
    {
        $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/class');
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

    public function generateAcademicYears()
    {
        $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/academic-year');
        return $response_data->data ?? [];
    }

    public function enroll_student(Request $request)
    {
        $response = $this->postData('/api/v1/cms/staff-curriculum/register-exam/student/register', [
            'exam_id' => $request->input('exam_id'),
            'students' => $request->input('students'),
        ], 'json');

        return redirect()->back()->with('message', $response?->message)->with('alertClass', $response?->success ? 'alert-success' : 'alert-danger');
    }

    public function unenroll_student(Request $request)
    {
        $response = $this->postData('/api/v1/cms/staff-curriculum/register-exam/student/unregister', [
            'exam_id' => $request->input('exam_id'),
            'students' => $request->input('students'),
        ], 'json');

        return redirect()->back()->with('message', $response?->message)->with('alertClass', $response?->success ? 'alert-success' : 'alert-danger');
    }

    public function enroll_teacher(Request $request)
    {
        $response = $this->postData('/api/v1/cms/staff-curriculum/register-exam/teacher/register', [
            'exam_id' => $request->input('exam_id'),
            'teacher_id' => $request->input('teacher_id'),
            'role' => $request->input('role'),
        ], 'json');

        return redirect()->back()->with('message', $response?->message)->with('alertClass', $response?->success ? 'alert-success' : 'alert-danger');
    }

    public function unenroll_teacher(Request $request)
    {
        $response = $this->postData('/api/v1/cms/staff-curriculum/register-exam/teacher/unregister', [
            'exam_id' => $request->input('exam_id'),
            'teacher_id' => $request->input('teacher_id'),
            'role' => $request->input('role'),
        ], 'json');

        return redirect()->back()->with('message', $response?->message)->with('alertClass', $response?->success ? 'alert-success' : 'alert-danger');
    }

    public function add_section(Request $request)
    {
        $response = $this->postData('/api/v1/cms/staff-curriculum/exam-section', [
            'exam_id' => $request->input('exam_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ], 'json');

        return redirect()->back()->with('message', $response?->message)->with('alertClass', $response?->success ? 'alert-success' : 'alert-danger');
    }

    public function detail_section($id)
    {
        $section = $this->fetchData('/api/v1/cms/staff-curriculum/exam-section/' . $id)?->data ?? [];
        $section->exam =  $this->fetchData('/api/v1/cms/staff-curriculum/school-exam/' . $section->exam_id)?->data ?? [];

        $categories = $this->fetchData('/api/v1/cms/staff-curriculum/category-question')->data ?? [];
        $questions = collect($this->fetchData('/api/v1/cms/staff-curriculum/question')->data ?? [])
            ->filter(fn($q) => $q->school_exam_id == $section->exam_id && $q->section_id == $id)
            ->map(fn($q) => $this->mapQuestionCategory($q, $categories));

        $bankQuestions = $this->filterBankQuestions($section->exam, $categories)->toArray();

        return view('staff-curriculum.sekolah.ujian.section.show', [
            'section' => $section,
            'categories' => $categories,
            'questions' => $questions,
            'questionTypes' => $this->generateQuestionTypes(),
            'questionCategories' => $categories,
            'bankQuestions' => $bankQuestions,
            'classLevels' => $this->fetchLevels(),
        ]);
    }

    public function delete_section($id)
    {
        $response = $this->deleteData('/api/v1/cms/staff-curriculum/exam-section/' . $id);
        return redirect()->back()->with('message', $response?->message)->with('alertClass', $response?->success ? 'alert-success' : 'alert-danger');
    }

    public function v_ujian_penilaian($id)
    {
        $data = $this->prepare_penilaian_data_to_send_view($id);

        return view('staff-curriculum.sekolah.ujian.penilaian.index', $data);
    }

    public function v_penilaian_student($id, $student_id)
    {
        $data = $this->prepare_penilaian_data_to_send_view($id);

        $convertStudentId = $this->convertSubClassId($student_id);

        $data['student_reports'] = $data['student_reports']->filter(function ($report) use ($convertStudentId) {
            return $report['student_id'] === $convertStudentId;
        });
        $data['student_id'] = $student_id;

        return view('staff-curriculum.sekolah.ujian.penilaian.student.index', $data);
    }

    public function v_penilaian_ulasan(Request $request, $id, $student_id, $response_id)
    {
        $data = $this->prepare_penilaian_data_to_send_view($id);

        $data['all_answers'] = collect($data['all_answers']->data ?? [])->filter(function ($answer) use ($response_id) {
            return $answer->response_id === $response_id;
        });
        $questions = collect($data['all_questions']->data ?? [])->filter(function ($question) use ($id) {
            return $question->school_exam_id === $id;
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 1;
        $currentItems = $questions->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedQuestions = new LengthAwarePaginator($currentItems, $questions->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);

        $data['all_questions'] = $paginatedQuestions;
        $data['analytics'] = collect($data['analytics']->data ?? [])->where('student_id', $this->convertSubClassId($student_id))->where('response_id', $response_id)->first();
        $data['response_id'] = $response_id;

        return view('staff-curriculum.sekolah.ujian.penilaian.student.ulasan.index', $data);
    }

    public function update_is_main(Request $request, $id, $student_id, $response_id)
    {
        $validator = Validator::make($request->all(), [
            'is_main' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }
        $url = '/api/v1/cms/staff-curriculum/school-exam/' . $id . '/update-is-main/' . $response_id;

        $response_data = $this->putData($url, [
            'is_main' => $request->is_main,
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, 'edit-nilai-akhir', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }

    public function penilaian_ulasan(Request $request, $id, $student_id, $response_id)
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

        $url = '/api/v1/cms/staff-curriculum/update-grade/' . $request->answer_id;
        $response_data = $this->putData($url, [
            'point' => $point,
            'exam_type' => 'class',
        ], 'json');

        return redirect()->back()->withInput()->with('message', $this->getResponseMessage($response_data->success, 'edit', $response_data->message))->with('alertClass', $response_data->success ? 'alert-success' : 'alert-danger');
    }

    public function prepare_penilaian_data_to_send_view($school_exam_id)
    {
        $school_exam = $this->fetchData('/api/v1/cms/staff-curriculum/school-exam/' . $school_exam_id)?->data;
        // $class = $this->fetchData('/api/v1/cms/staff-curriculum/class/' . $this->normalizeId($school_exam->class_level))?->data;
        $class = $this->fetchData('/api/v1/cms/staff-curriculum/class/' . $school_exam->class_level)?->data;
        // dd($this->fetchData('/api/v1/cms/staff-curriculum/class/' . $school_exam->class_level), 'school_exam', $school_exam);

        // $staffSubClasses = $this->fetchData('/api/v1/cms/staff-curriculum/enrollment/sub-class');
        // $filteredSubclasses = collect($staffSubClasses->data ?? [])->filter(function ($subclass) use ($school_exam) {
        //     return $subclass->course == $school_exam->course;
        // });

        // $subclasses = $this->fetchData('/api/v1/cms/staff-curriculum/sub-class');
        // $subClass = $filteredSubclasses->map(function ($filteredSubclass) use ($subclasses) {
        //     return collect($subclasses->data ?? [])->firstWhere('id', $filteredSubclass->sub_class_id);
        // })->values()->first();

        $all_response_students = $this->fetchData('/api/v1/cms/staff-curriculum/' . $school_exam_id . '/response');
        $responses = collect($all_response_students->data ?? []);

        $all_answers = $this->fetchData('/api/v1/cms/staff-curriculum/answer');
        $answers = collect($all_answers->data ?? []);
        $all_questions = $this->fetchData('/api/v1/cms/staff-curriculum/question');
        $questions = collect($all_questions->data ?? []);

        $grades = $responses->pluck('grades.*.exam')->flatten();
        $highestGrade = $grades->max();
        $lowestGrade = $grades->min();
        $averageGrade = $grades->avg();
        $totalUserResponses = $responses->unique('student_id')->count();

        $analyticData = $this->fetchData('api/v1/cms/staff-curriculum/' . $school_exam_id . '/analytic');
        $studentReports = $responses->groupBy('student_id')->map(function ($studentResponses) use ($answers, $questions) {
            $studentId = $studentResponses->first()->student_id;
            $studentName = $studentResponses->first()->student_name;
            $studentNisn = $studentResponses->first()->student_nisn;

            $grades = collect($studentResponses->pluck('grades')->flatten());
            $finalGrade = $grades->where('is_main', true)->pluck('exam')->first();

            $totalAttempts = $studentResponses->count();

            $attemptsHistory = $studentResponses->sortByDesc('created_at')->map(function ($response, $index) use ($answers, $questions) {
                $createdAt = \Carbon\Carbon::parse($response->created_at);
                $updatedAt = $response->updated_at ? \Carbon\Carbon::parse($response->updated_at) : null;

                $grades = collect($response->grades);

                $initialPoints = 0;
                $essayPoints = 0;
                $totalPointQuestion = 0;
                $isEssayGraded = true;

                $responseAnswers = $answers->where('response_id', $response->id);
                $responseQuestions = $questions->where('exam_id', $response->exam_id);

                $responseQuestions->each(function ($question) use ($responseAnswers, &$initialPoints, &$totalPointQuestion) {
                    $totalPointQuestion += $question->point;
                    if (in_array($question->question_type, ['Pilihan Ganda', 'Pilihan Ganda Complex', 'True False'])) {
                        $answer = $responseAnswers->where('question_id', $question->id)->first();
                        if ($answer) {
                            $choice = collect($question->choices)->where('id', $answer->choice_id)->first();
                            if ($choice && $choice->is_true) {
                                $initialPoints += $question->point;
                            }
                        }
                    }
                });

                $responseQuestions->where('question_type', 'Essay')->each(function ($question) use ($responseAnswers, &$essayPoints, &$isEssayGraded) {
                    $answer = $responseAnswers->where('question_id', $question->id)->first();
                    if ($answer && $answer->is_graded) {
                        $essayPoints += $question->point;
                    } else {
                        $isEssayGraded = false;
                    }
                });

                $totalPointsWithEssay = $initialPoints + $essayPoints;

                $status = $isEssayGraded ? 'Sudah Dinilai' : 'Mohon beri nilai di ulasan';
                $score = $isEssayGraded ? $grades->where('is_main', true)->pluck('school_exam')->first() : 'Esai belum dinilai';

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

            return [
                'student_id' => $studentId,
                'nisn' => $studentNisn,
                'name' => $studentName,
                'final_grade' => $finalGrade ? number_format($finalGrade, 2) : null,
                'total_attempts' => $totalAttempts,
                'attempts_history' => $attemptsHistory,
            ];
        });

        $course = $this->fetchData('/api/v1/cms/staff-curriculum/course/' . $school_exam->course->id)?->data;

        return [
            'class' => $class,
            'school_exam' => $school_exam,
            'student_reports' => $studentReports->values(),
            'highest_grade' => number_format($highestGrade, 2),
            'lowest_grade' => number_format($lowestGrade, 2),
            'average_grade' => number_format($averageGrade, 2),
            'total_user_responses' => $totalUserResponses,
            'school_exam_id' => $school_exam_id,
            'all_answers' => $all_answers,
            'all_questions' => $all_questions,
            'analytics' => $analyticData,
            'course' => $course,
        ];
    }
}
