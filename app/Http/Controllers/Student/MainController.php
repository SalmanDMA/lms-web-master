<?php

namespace App\Http\Controllers\Student;

use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use App\Http\Controllers\Controller;

use App\Models\SubmissionAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use DateTime;


class MainController extends Controller
{
    protected $client;
    use ApiHelperTrait, StaticDataTrait;
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
        $this->initializeApiHelper();
    }

    public function v_dashboard()
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $materialsData = $this->client->get('/api/v1/mobile/student/material', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
                'query' => ['limit' => 3]
            ]);
            $assignments = $this->client->get('/api/v1/mobile/student/assignment', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
                'query' => ['limit' => 3]
            ]);
            $school_exams = $this->client->get('/api/v1/mobile/student/school-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
                'query' => ['limit' => 3]
            ]);
            $class_exams = $this->client->get('/api/v1/mobile/student/class-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
                'query' => ['limit' => 3]
            ]);
            $courses = $this->client->get('/api/v1/mobile/student/course', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('student.pages.dashboard', [
                "materials" => json_decode($materialsData->getBody()->getContents())->data ?? [],
                "assignments" => json_decode($assignments->getBody()->getContents())->data ?? [],
                "courses" => json_decode($courses->getBody()->getContents())->data ?? [],
                "school_exams" => json_decode($school_exams->getBody()->getContents())->data ?? [],
                "class_exams" => json_decode($class_exams->getBody()->getContents())->data ?? [],
                "customTheme" => $customTheme,
            ]);
        } else {
            return redirect('/login');
        }
    }

    public function v_material()
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $materials = $this->fetchData('/api/v1/mobile/student/material', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
            ]);

            $courses = $this->fetchData('/api/v1/mobile/student/course', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
            ]);

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            $materialsData = [];
            if (!empty($materials->data)) {
                $materialsData = $materials->data;
            }

            return view('student.pages.material.index', [
                'title' => 'Materi',
                'materials' => $materialsData,
                'courses' => $courses->data,
                'customTheme' => $customTheme,
            ]);
        } else {
            return Inertia::location('/login');
        }
    }

    public function v_materialDetail($id)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $materialData     = $this->fetchData('/api/v1/mobile/student/material/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $courses = $this->fetchData('/api/v1/mobile/student/course', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
            ]);
            if (isset($materialData->data)) {
                $materialCourse = $materialData->data->learning->course;
                $course = $this->fetchData('/api/v1/mobile/student/course/' . $materialCourse, [
                    'headers' => ['Authorization' => 'Bearer ' . session('token')]
                ]);

                $customThemeData = $this->client->get('/api/v1/mobile/cms');
                $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

                if (isset($customTheme)) {
                    Config::set('app.name', $customTheme->title);
                }

                return view('student.pages.material.detail', [
                    'title' => 'Detail Materi',
                    'materialData' => $materialData->data,
                    'course' => $course->data,
                    'customTheme' => $customTheme,
                ]);
            }
        } else {
            return Inertia::location('/login');
        }
    }

    public function v_assignment()
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $assignments = $this->client->get('/api/v1/mobile/student/assignment', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $courses = $this->client->get('/api/v1/mobile/student/course', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('student.pages.assignment.index', [
                'title' => 'Tugas',
                'assignments' => json_decode($assignments->getBody()->getContents())->data ?? [],
                'courses' => json_decode($courses->getBody()->getContents())->data ?? [],
                'customTheme' => $customTheme,
            ]);
        } else {
            return Inertia::location('/login');
        }
    }

    public function v_assignmentDetail($id)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $assignmentData = $this->client->get('/api/v1/mobile/student/assignment/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $submissionData = $this->fetchData('/api/v1/mobile/student/submission', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
            ]);
            if (isset($submissionData->data)) {
                $filteredSubmissionData = $submissionData->data = array_filter($submissionData->data, function ($submission) use ($id) {
                    return $submission?->assignment_id == $id;
                });
            }




            $assignment = json_decode($assignmentData->getBody()->getContents());
            if (isset($assignment->data)) {
                $assignmentCourse = $assignment->data->learning->course;
                $course = $this->fetchData('/api/v1/mobile/student/course/' . $assignmentCourse, [
                    'headers' => ['Authorization' => 'Bearer ' . session('token')]
                ]);

                $customThemeData = $this->client->get('/api/v1/mobile/cms');
                $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

                if (isset($customTheme)) {
                    Config::set('app.name', $customTheme->title);
                }

                return view('student.pages.assignment.detail', [
                    'title' => 'Tugas Detail',
                    'assignment' => $assignment->data ?? "",
                    'course' => $course->data ?? "",
                    'submissions' => $filteredSubmissionData ?? [],
                    'customTheme' => $customTheme,
                ]);
            }
        }
    }

    public function createSubmission(Request $request)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {

            if ($request->submission_content) {
                $validator = Validator::make($request->all(), [
                    'submission_content' => 'string',
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator);
                }
            }

            $formData = [
                "assignment_id" => $request->assignment_id,
                "submission_content" => $request->submission_content
            ];

            $response_data = $this->postData('/api/v1/mobile/student/submission', $formData, 'json');


            $this->handleResourceManagement($request, $response_data->data->id, null);

            return $this->handleTugasResponse($response_data, 'submit', $request->assignment_id);
        };
    }

    public function editSubmission(Request $request, $submissionId)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {

            if ($request->submission_content) {
                $validator = Validator::make($request->all(), [
                    'submission_content' => 'string',
                    'submission_note' => 'string'
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator);
                }
            }

            $formData = [
                "assignment_id" => $request->assignment_id,
                "submission_content" => $request->submission_content,
                "submission_note" => $request->submission_note
            ];

            $response_data = $this->putData('/api/v1/mobile/student/submission/' . $submissionId, $formData, 'json');



            $this->handleResourceManagement($request, $response_data->data->id, null);

            return $this->handleTugasResponse($response_data, 'submit', $request->assignment_id);
        };
    }

    public function deleteSubmissionResource(Request $request, $id)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        $attachment = SubmissionAttachment::find($id);
        if (!$attachment) {

            return redirect()->back()->withErrors('Attachment not found.');
        }
        if (session('role') === 'STUDENT' && session()->has('token')) {

            $response_data = $this->deleteData('/api/v1/mobile/student/attachment/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')],
            ]);

            $fileUrl = str_replace('storage/public/', 'public/', $attachment->file_url);
            if (Storage::exists($fileUrl)) {
                Storage::delete($fileUrl);
            }

            $attachment->delete();
        } else {
            $response_data = ['status' => 'error', 'message' => 'Unauthorized action.'];
        }


        return $this->handleTugasResponse($response_data, 'delete-attachment', $request->assignment_id);
    }

    public function v_editAssignment($assignmentId, $submissionId)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        $assignmentData = $this->client->get('/api/v1/mobile/student/assignment/' . $assignmentId, [
            'headers' => ['Authorization' => 'Bearer ' . session('token')]
        ]);

        $submissionData = $this->fetchData('/api/v1/mobile/student/submission/' . $submissionId, [
            'headers' => ['Authorization' => 'Bearer ' . session('token')],
        ]);

        $assignment = json_decode($assignmentData->getBody()->getContents());
        if (isset($assignment->data)) {
            $assignmentCourse = $assignment->data->learning->course;
            $course = $this->fetchData('/api/v1/mobile/student/course/' . $assignmentCourse, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            return view('student.pages.assignment.edit', [
                'title' => 'Tugas Edit',
                'assignment' => $assignment->data ?? "",
                'course' => $course->data ?? "",
                'submission' => $submissionData->data ?? ""
            ]);
        }
    }

    public function v_schoolExam()
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $schoolExamData = $this->client->get('/api/v1/mobile/student/school-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $classExamData = $this->client->get('/api/v1/mobile/student/class-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $coursesData = $this->client->get('/api/v1/mobile/student/course', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $coursesArray = json_decode($coursesData->getBody()->getContents(), true)['data'] ?? [];
            $coursesMapSchool = [];
            foreach ($coursesArray as $course) {
                $coursesMapSchool[$course['id']] = $course['courses_title'];
            }

            $schoolExamArray = json_decode($schoolExamData->getBody()->getContents(), true)['data'] ?? [];
            // dd($schoolExamArray);


            foreach ($schoolExamArray as &$schoolarray) {
                $courseId = $schoolarray['exam_id']['course']['id'];


                if (isset($coursesMapSchool[$courseId])) {
                    $schoolarray['courses_title'] = $coursesMapSchool[$courseId];
                } else {
                    $schoolarray['courses_title'] = 'Unknown Course';
                }
            }
            //Buat Classs

            $coursesMap = [];
            foreach ($coursesArray as $course) {
                $coursesMap[$course['id']] = $course['courses_title'];
            }

            $classExamArray = json_decode($classExamData->getBody()->getContents(), true)['data'] ?? [];


            foreach ($classExamArray as &$classExam) {
                $courseId = $classExam['learning']['course'];

                if (isset($coursesMap[$courseId])) {
                    $classExam['courses_title'] = $coursesMap[$courseId];
                } else {
                    $classExam['courses_title'] = 'Unknown Course';
                }
            }

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('student.pages.Exam.exam_dashboard', [
                'menu' => 'ujian',
                'schoolExamData' => $schoolExamArray,
                'classExamData' => $classExamArray,
                'availableCourse' => $coursesMap,
                'customTheme' => $customTheme,
            ]);

            // return Inertia::render('student/school-exam', [
            //     'title' => 'Ujian',
            //     'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
            // ]);
        } else {
            return redirect('/login');
        }
    }

    public function verifyTokenClassExam(Request $request)
    {
        $id = "";
        if ($request->class_id !== null) {
            $id = $request->class_id;
        } else if ($request->school_id !== null) {
            $id = $request->school_id;
        }
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {

            $bodyPostReq = [];
            $type = "classExam";
            if ($request->school_id !== null) {
                $type = "schoolExam";
                $bodyPostReq = [
                    [
                        'name' => 'token',
                        'contents' => $request->inputToken,
                    ],
                    [
                        'name' => 'school_exam_id',
                        'contents' => $id,
                    ],
                    [
                        'name' => 'status',
                        'contents' => 'pengerjaan',
                    ]
                ];
            } else if ($request->class_id !== null) {
                $bodyPostReq = [
                    [
                        'name'     => 'token',
                        'contents' => $request->inputToken,
                    ],
                    [
                        'name'     => 'exam_id',
                        'contents' => $id,
                    ],
                    [
                        'name'     => 'status',
                        'contents' => 'pengerjaan',
                    ]
                ];
            }

            $response = $this->client->post('api/v1/mobile/student/verify-token-exam', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token')
                ],
                'multipart' => $bodyPostReq
            ]);
            $responseData = json_decode($response->getBody()->getContents());
            if ($responseData->success && $responseData->message == 'Token valid.') {
                $redirectUrl = $type === 'classExam'
                    ? '/student/exam-detail-class/' . $id . '/question'
                    : '/student/exam-detail-school/' . $id . '/' . $request->id . '/question';

                return redirect($redirectUrl);
            } else {
                return back()->withErrors($responseData->message);
            }
        } else {
            return redirect('/login');
        }
    }

    private function formatDateTime($datetime)
    {
        $date = new DateTime($datetime);

        $timeFormat = $date->format('H:i') . ' WIB';

        $dayNames = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        $dayOfWeek = $dayNames[$date->format('l')];

        $monthNames = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];
        $dateFormat = $date->format('d') . ' ' . $monthNames[$date->format('F')] . ' ' . $date->format('Y');

        return $timeFormat . ' | ' . $dayOfWeek . ', ' . $dateFormat;
    }

    public function detailExamClass($id)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $ClassData = $this->client->get('/api/v1/mobile/student/class-exam/' . $id . '', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $ClassDataJson = json_decode($ClassData->getBody()->getContents())->data ?? [];

            if ($ClassDataJson !== []) {
                $ClassDataJson->exam_setting->start_time_formatted = $this->formatDateTime($ClassDataJson->exam_setting->start_time);
                $ClassDataJson->exam_setting->end_time_formatted = $this->formatDateTime($ClassDataJson->exam_setting->end_time);

                $endTime = new DateTime($ClassDataJson->exam_setting->end_time);
                $today = new DateTime('today');

                if ($today > $endTime && $ClassDataJson->exam_setting->maximum_user === 0) {
                    $ClassDataJson->disabled_exam = true;
                } else {
                    $ClassDataJson->disabled_exam = false;
                }

                $total_point = 0;

                $schoolExamResponse = $this->client->get("/api/v1/mobile/student/{$id}/question", [
                    'headers' => ['Authorization' => 'Bearer ' . session('token')]
                ]);
                $schoolExamData = json_decode($schoolExamResponse->getBody()->getContents())->data ?? [];

                if ($schoolExamData !== []) {
                    foreach ($schoolExamData as $exam) {
                        $total_point += $exam->point;
                    }
                }

                $riwayatClassExam = $this->client->get("/api/v1/mobile/student/{$id}/response", [
                    'headers' => ['Authorization' => 'Bearer ' . session('token')]
                ]);
                $riwayatClassExamJson = json_decode($riwayatClassExam->getBody()->getContents())->data ?? [];

                $ClassDataJson->exam_setting->total_point = $total_point;
            }

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('student.pages.Exam.exam_detail', [
                'menu' => 'ujian',
                'ClassData' => $ClassDataJson,
                'riwayatPengerjaan' => $riwayatClassExamJson,
                'customTheme' => $customTheme,
            ]);
        } else {
            return redirect('/login');
        }
    }

    public function examQuestion($id)
    {
        if (session('role') === 'STUDENT' && session()->has('token')) {
            $headers = ['Authorization' => 'Bearer ' . session('token')];

            $classExamResponse = $this->client->get('/api/v1/mobile/student/class-exam/' . $id, [
                'headers' => $headers
            ]);
            $classExamData = json_decode($classExamResponse->getBody()->getContents())->data ?? [];

            $questionResponse = $this->client->get("/api/v1/mobile/student/{$id}/question", [
                'headers' => $headers
            ]);

            $questionData = json_decode($questionResponse->getBody()->getContents())->data ?? [];

            $questionData = collect($questionData)->groupBy('question_type')->toArray();

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('student.pages.Exam.questions.exam_question', [
                'title' => 'Ujian',
                'classExamData' => $classExamData,
                'questionData' => $questionData,
                'studentName' => session('user')->fullname,
                'customTheme' => $customTheme,
            ]);
        } else {
            return redirect('/login');
        }
    }

    public function detailExamSchool($id)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $SchoolExamData = $this->client->get('/api/v1/mobile/student/school-exam/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $SchoolExamDataJson = json_decode($SchoolExamData->getBody()->getContents())->data ?? [];

            if ($SchoolExamDataJson !== []) {
                $SchoolExamDataJson->examSetting->start_time_formatted = $this->formatDateTime($SchoolExamDataJson->examSetting->start_time);
                $SchoolExamDataJson->examSetting->end_time_formatted = $this->formatDateTime($SchoolExamDataJson->examSetting->end_time);

                $endTime = new DateTime($SchoolExamDataJson->examSetting->end_time);
                $today = new DateTime('today');

                if ($today > $endTime && $SchoolExamDataJson->examSetting->maximum_user === 0) {
                    $SchoolExamDataJson->disabled_exam = true;
                } else {
                    $SchoolExamDataJson->disabled_exam = false;
                }

                $total_point = 0;

                $schoolExamResponse = $this->client->get("/api/v1/mobile/student/{$SchoolExamDataJson->exam_id->id}/question", [
                    'headers' => ['Authorization' => 'Bearer ' . session('token')]
                ]);
                $schoolExamData = json_decode($schoolExamResponse->getBody()->getContents())->data ?? [];

                if ($schoolExamData !== []) {
                    foreach ($schoolExamData as $exam) {
                        $total_point += $exam->point;
                    }
                }

                $riwayatSchoolExam = $this->client->get("/api/v1/mobile/student/{$SchoolExamDataJson->exam_id->id}/response-exam", [
                    'headers' => ['Authorization' => 'Bearer ' . session('token')]
                ]);
                $riwayatSchoolExamJson = json_decode($riwayatSchoolExam->getBody()->getContents())->data ?? [];

                $SchoolExamDataJson->examSetting->total_point = $total_point;
            }

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('student.pages.Exam.exam_detail', [
                'menu' => 'ujian',
                'SchoolData' => $SchoolExamDataJson,
                'riwayatPengerjaan' => $riwayatSchoolExamJson,
                'customTheme' => $customTheme,
            ]);
        } else {
            return redirect('/login');
        }
    }

    public function examSchoolQuestion($id, $schoolId)
    {
        if (session('role') === 'STUDENT' && session()->has('token')) {
            $headers = ['Authorization' => 'Bearer ' . session('token')];

            $schoolExamResponse = $this->client->get('/api/v1/mobile/student/school-exam/' . $schoolId, [
                'headers' => $headers
            ]);

            $schoolExamData = json_decode($schoolExamResponse->getBody()->getContents())->data ?? [];


            $questionResponse = $this->client->get("/api/v1/mobile/student/{$id}/question", [
                'headers' => $headers
            ]);

            $questionData = json_decode($questionResponse->getBody()->getContents())->data ?? [];

            $questionData = collect($questionData)->groupBy(function ($question) {
                return $question->section_id->id ?? null;
            })->toArray();

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            return view('student.pages.Exam.questions.exam_question', [
                'title' => 'Ujian',
                'schoolExamData' => $schoolExamData,
                'questionData' => $questionData,
                'studentName' => session('user')->fullname,
                'customTheme' => $customTheme,
            ]);
        } else {
            return redirect('/login');
        }
    }

    private function formatDateScore($dateTime)
    {
        $date = new DateTime($dateTime);

        $daysNames = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];

        $dayOfWeek = $daysNames[$date->format('l')];
        $dateFormatted = $date->format('d') . ' ' . $date->format('m') . ' ' . $date->format('Y');
        return $dayOfWeek . ', ' . $dateFormatted;
    }

    public function scoreList()
    {
        if (session('role') === 'STUDENT' && session()->has('token')) {
            $headers = ['Authorization' => 'Bearer ' . session('token')];

            $scoreListResponse = $this->client->get('/api/v1/mobile/student/score-list', [
                'headers' => $headers
            ]);

            $scoreListData = json_decode($scoreListResponse->getBody()->getContents())->data ?? [];

            foreach ($scoreListData as &$score) {
                $score->formatted_graded_at = $this->formatDateScore($score->graded_at);
            }

            $coursesData = $this->client->get('/api/v1/mobile/student/course', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            $coursesArray = json_decode($coursesData->getBody()->getContents(), true)['data'] ?? [];
            $coursesMap = [];

            foreach ($coursesArray as $course) {
                $coursesMap[] = $course['courses_title'];
            }

            $customThemeData = $this->client->get('/api/v1/mobile/cms');
            $customTheme = json_decode($customThemeData->getBody()->getContents())->data ?? null;

            if (isset($customTheme)) {
                Config::set('app.name', $customTheme->title);
            }

            $typeNilaiUjian = [
                'submission',
                'exam',
                'class-exam'
            ];

            return view('student.pages.nilai.index', [
                'scoreData' => $scoreListData,
                'customTheme' => $customTheme,
                'typeNilaiUjian' => $typeNilaiUjian,
                'coursesType' => $coursesMap,
            ]);
        } else {
            return redirect('/login');
        }
    }

    public function exam(string $id, Request $request)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $data          = $request->all();
            $response      = $this->client->post('/api/v1/mobile/student/verify-token-exam', [
                'form_params' => $data,
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if (!$response_data->success) {
                return back()->withErrors([
                    'title' => 'Token salah!',
                    'message' => $response_data->message,
                ]);
            } else {
                session([
                    'id' => $id,
                    'exam_id' => $data['exam_id'] ?? $data['school_exam_id'],
                    'tmp_exam_id' => $response_data->data->exam_id ?? null,
                    'tmp_school_exam_id' => $response_data->data->school_exam_id ?? null,
                    'response_id' => $response_data->data->id,
                ]);

                return redirect('/student/exam/' . session('exam_id'));
            }

            return back();
        } else {
            return Inertia::location('/login');
        }
    }

    public function v_exam($id)
    {
        if (session('role') === 'STUDENT' && session()->has('token')) {
            $schoolExamData = $this->client->get('/api/v1/mobile/student/school-exam/' . session('id'), [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $questionData   = $this->client->get("/api/v1/mobile/student/{$id}/question", [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return Inertia::render('student/exam', [
                'title' => 'Ujian',
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
                'questionData' => json_decode($questionData->getBody()->getContents())->data ?? [],
            ]);
        } else {
            return Inertia::location('/login');
        }
    }

    public function create(string $type, Request $request)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $data          = $request->all();
            $response      = $this->client->post("/api/v1/mobile/student/{$type}", [
                'form_params' => $data,
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if (!$response_data->success) {
                return back()->withErrors([
                    'title' => 'Tambah Data Gagal!',
                    'message' => $response_data->message,
                ]);
            }

            return back();
        } else {
            return Inertia::location('/login');
        }
    }

    public function update(string $type, Request $request)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $data          = $request->all();
            $response      = $this->client->put("/api/v1/mobile/student/$type/" . $data['id'], [
                'form_params' => $data,
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if (!$response_data->success) {
                return back()->withErrors([
                    'title' => 'Ubah Data Gagal!',
                    'message' => $response_data->message,
                ]);
            }

            return back();
        } else {
            return Inertia::location('/login');
        }
    }

    public function delete(string $type, Request $request)
    {
        if (session()->has('exam_id')) {
            return redirect('/student/exam/' . session('exam_id'));
        }

        if (session('role') === 'STUDENT' && session()->has('token')) {
            $data          = $request->all();
            $response      = $this->client->delete("/api/v1/mobile/student/{$type}/" . $data['id'], [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if (!$response_data->success) {
                return back()->withErrors([
                    'title' => 'Hapus Data Gagal!',
                    'message' => $response_data->message,
                ]);
            }

            return back();
        } else {
            return Inertia::location('/login');
        }
    }

    public function logout()
    {
        if (session()->has('token')) {
            $response      = $this->client->get('/api/v1/auth/logout', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if ($response_data->success) {
                session()->flush();
                return Inertia::location('/login');
            }

            return back();
        } else {
            return Inertia::location('/login');
        }
    }

    private function handleTugasResponse($response_data, $type, $assignmentId)
    {
        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';


        return redirect()->back()->with('message', $message)->with('alertClass', $alertClass);
    }
    private function handleResourceManagement(Request $request, $tugasId)
    {
        // dd($request->resources);

        $deletedResources = json_decode($request->input('deleted_resources'), true);

        if (!empty($deletedResources)) {
            foreach ($deletedResources as $resourceId) {
                $resource = SubmissionAttachment::find($resourceId);
                if ($resource) {

                    $filePath = str_replace('storage/public/', 'public/', $resource->file_url);

                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }

                    $resource->delete();
                }
            }
        }

        if ($request->has('resources')) {
            foreach ($request->resources as $resource) {

                if ($resource['file_type'] && $resource['file_name']) {
                    $resourceData = $this->mapResourceData($resource);
                    $uploadResult = $this->processResource($resourceData, 'submissions');

                    if ($uploadResult['error']) {
                        return $this->sendError($uploadResult['message'], null, 200);
                    }


                    SubmissionAttachment::create([
                        'id' => IdGenerator::generate(['table' => 'submission_attachments', 'length' => 16, 'prefix' => 'SMSA-']),
                        'submission_id' => $tugasId,
                        'file_name' => $resourceData['resource_name'],
                        'file_type' => $resourceData['resource_type'],
                        'file_url' => $uploadResult['path'],
                        'file_extension' => $uploadResult['extension'],
                        'file_size' => $uploadResult['size'],
                    ]);
                }
            }
        }
    }

    private function getResponseMessage($success, $type, $message)
    {
        if ($success) {
            switch ($type) {
                case 'submit':
                    return 'Tugas berhasil dikumpulkan.';
                case 'edit':
                    return 'Tugas berhasil diperbarui.';
                case 'edit-main-value':
                    return 'Berhasil menjadikan nilai utama.';
                case 'edit-feedback':
                    return 'Berhasil menyimpan catatan.';
                case 'edit-score':
                    return 'Berhasil menyimpan nilai.';
                case 'edit-score-null':
                    return 'Berhasil menghapus nilai.';
                case 'delete':
                    return 'Tugas berhasil dihapus.';
                case 'delete-attachment':
                    return 'Lampiran berhasil dihapus.';
                case 'import':
                    return 'Tugas berhasil diimport.';
                default:
                    return $message;
            }
        }

        return 'Terjadi kesalahan: ' . $message;
    }
}
