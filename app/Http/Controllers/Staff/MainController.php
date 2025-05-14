<?php

namespace App\Http\Controllers\Staff;

use Exception;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Controllers\Controller;
use App\Http\Traits\StaticDataTrait;

class MainController extends Controller
{
    use ApiHelperTrait,StaticDataTrait;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
    }

    public function v_dashboard()
    {
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        try {
            $dashboardData = $this->client->get('/api/v1/cms/staff-curriculum/dashboard/statistic', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            return view('staff-curriculum.dashboard', [
                'title' => 'Dashboard',
                'cmsData' => $cmsData?->data[0] ?? [],
                'dashboardData' => json_decode($dashboardData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function v_adminDashboard()
    {
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        try {
            $dashboardData = $this->client->get('/api/v1/cms/staff-curriculum/dashboard/statistic', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            return view('staff-curriculum.dashboard', [
                'title' => 'Dashboard',
                'cmsData' => $cmsData?->data[0] ?? [],
                'dashboardData' => json_decode($dashboardData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function v_schoolExam()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $schoolExamData = $this->client->get('/api/v1/cms/staff-curriculum/school-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            $responseData = json_decode($response->getBody()->getContents());
            $userTeacherData = [];
            $userStudentData = [];

            if ($responseData->success) {
                foreach ($responseData->data as $data) {
                    if ($data->role === 'TEACHER') {
                        $userTeacherData[] = $data;
                    } elseif ($data->role === 'STUDENT') {
                        $userStudentData[] = $data;
                    }
                }
            }

            return Inertia::render('staff/school-exam', [
                'title' => 'Ujian',
                'cmsData' => $cmsData?->data[0] ?? [],
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
                'userTeacherData' => $userTeacherData,
                'userStudentData' => $userStudentData,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_studentEnrollment($id)
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $schoolExamData = $this->client->get('/api/v1/cms/staff-curriculum/school-exam/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $schoolExamDecoded = json_decode($schoolExamData->getBody()->getContents())->data ?? "";
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $responseData = json_decode($response->getBody()->getContents());
            $userStudentData = [];

            if ($responseData->success) {
                foreach ($responseData->data as $data) {
                    if ($data->role === 'STUDENT') {
                        $userStudentData[] = $data;
                    }
                }
            }

            return view('staff-curriculum.studentEnrollment', [
                'title' => 'Daftar Peserta Ujian',
                'schoolExamData' => $schoolExamDecoded,
                'userStudentData' => $userStudentData,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function registerTeacher(Request $request)
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $data = $request->only(['exam_id', 'teacher_id', 'role']);
            $response = $this->client->post("/api/v1/cms/staff-curriculum/register-exam/teacher", [
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
            return redirect('/admin/login');
        }
    }

    public function registerStudent(Request $request)
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $data = $request->only(['exam_id', 'students']);
            $response = $this->client->post("/api/v1/cms/staff-curriculum/register-exam/student", [
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
            return redirect('/admin/login');
        }
    }

    public function v_examSection()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $examSectionData = $this->client->get('/api/v1/cms/staff-curriculum/exam-section', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $schoolExamData = $this->client->get('/api/v1/cms/staff-curriculum/school-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return Inertia::render('staff/exam-section', [
                'title' => 'Bagian Ujian',
                'examSectionData' => json_decode($examSectionData->getBody()->getContents())->data ?? [],
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_question()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $questionData = $this->client->get('/api/v1/cms/staff-curriculum/question', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('staff-curriculum.question', [
                'title' => 'Soal Ujian',
                'questionData' => json_decode($questionData->getBody()->getContents())->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_createQuestion()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $examSectionData = $this->client->get('/api/v1/cms/staff-curriculum/exam-section', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $schoolExamData = $this->client->get('/api/v1/cms/staff-curriculum/school-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('staff-curriculum.questionCreate', [
                'title' => 'Tambah Soal Ujian',
                'examSectionData' => json_decode($examSectionData->getBody()->getContents())->data ?? [],
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_editQuestion($id)
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $examSectionData = $this->client->get('/api/v1/cms/staff-curriculum/exam-section', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $questionData = $this->client->get("/api/v1/cms/staff-curriculum/question/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $schoolExamData = $this->client->get('/api/v1/cms/staff-curriculum/school-exam', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('staff-curriculum.questionEdit', [
                'title' => 'Edit Soal Ujian',
                'examSectionData' => json_decode($examSectionData->getBody()->getContents())->data ?? [],
                'questionData' => json_decode($questionData->getBody()->getContents())->data ?? [],
                'schoolExamData' => json_decode($schoolExamData->getBody()->getContents())->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userTeacher()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            $response_data = json_decode($response->getBody()->getContents());
            $userTeacherData = [];

            if ($response_data->success) {
                foreach ($response_data->data as $data) {
                    if ($data->role === 'TEACHER') {
                        $userTeacherData[] = $data;
                    }
                }
            }

            return view('staff-curriculum.user-teacher.index', [
                'title' => 'Daftar Guru',
                'cmsData' => $cmsData?->data[0] ?? [],
                'userTeacherData' => $userTeacherData,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userTeacherCreate()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {

            return view('staff-curriculum.user-teacher.create', [
                'title' => 'Tambah Guru',
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userTeacherUpdate($id)
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('staff-curriculum.user-teacher.edit', [
                'title' => 'Ubah Guru',
                'user' => json_decode($response->getBody()->getContents())->data,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStudent()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            $response_data = json_decode($response->getBody()->getContents());
            $userStudentData = [];

            if ($response_data->success) {
                foreach ($response_data->data as $data) {
                    if ($data->role === 'STUDENT') {
                        $userStudentData[] = $data;
                    }
                }
            }

            return view('staff-curriculum.user-student.index', [
                'title' => 'Daftar Siswa',
                'cmsData' => $cmsData?->data[0] ?? [],
                'userStudentData' => $userStudentData,

            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStudentCreate()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $subClassData = $this->client->get('/api/v1/cms/staff-curriculum/sub-class', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('staff-curriculum.user-student.create', [
                'title' => 'Tambah Siswa',
                'subClassData' => json_decode($subClassData->getBody()->getContents())->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStudentUpdate($id)
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $subClassData = $this->client->get('/api/v1/cms/staff-curriculum/sub-class', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('staff-curriculum.user-student.edit', [
                'title' => 'Ubah Siswa',
                'user' => json_decode($response->getBody()->getContents())->data,
                'subClassData' => json_decode($subClassData->getBody()->getContents())->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStaff()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
        
            $response_data = json_decode($response->getBody()->getContents());
            $userStaffData = [];

            if ($response_data->success) {
                foreach ($response_data->data as $data) {
                    if ($data->role === 'STAFF') {
                        $userStaffData[] = $data;
                    }
                }
            }

            return view('staff-curriculum.user-staff.index', [
                'title' => 'Daftar Staff',
                'cmsData' => $cmsData?->data[0] ?? [],
                'userStaffData' => $userStaffData,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStaffCreate()
    {
        if (session('role') === 'STAFF' && session()->has('token')) {

            return view('staff-curriculum.user-staff.create', [
                'title' => 'Tambah Staff',
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStaffUpdate($id)
    {
        if (session('role') === 'STAFF' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/staff-curriculum/user/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('staff-curriculum.user-staff.edit', [
                'title' => 'Ubah Staff',
                'user' => json_decode($response->getBody()->getContents())->data,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function create(string $type, Request $request)
    {
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        if (!$request->has('choice_text') && !$request->has('files')) {
            $data = $request->all();
        } else {
            if ($request->has('choice_text')) {
                $choice_text = $request->input('choice_text');
                $is_true = $request->input('is_true');
                $choices = [];

                for ($i = 0; $i < count($choice_text); $i++) {
                    $choices[] = [
                        'choice_text' => $choice_text[$i],
                        'is_true' => !empty($is_true[$i]) ? true : false,
                    ];
                }

                $data = array_merge($request->except('choice_text', 'is_true'), ['choices' => $choices]);
            }

            if ($request->has('files')) {
                $files = $request->file('files');
                $types = $request->input('types');
                $resources = [];

                for ($i = 0; $i < count($files); $i++) {
                    $fileName = $files[$i]->getClientOriginalName();
                    $resources[] = [
                        'file_name' => pathinfo($fileName)['filename'],
                        'file_type' => $types[$i],
                        'file_url' => $files[$i],
                    ];
                }

                if ($request->has('choice_text')) {
                    $data = array_merge($data, ['resources' => $resources]);
                } else {
                    $data = array_merge($request->except('files', 'types'), ['resources' => $resources]);
                }
            }
        }

        if ($type === 'register-exam-teacher') {
            $type = 'register-exam/teacher';
        } elseif ($type === 'register-exam-student') {
            $type = 'register-exam/student';
        }

        $response = $this->client->post("/api/v1/cms/staff-curriculum/{$type}", [
            'form_params' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' . session('token'),
                'Accept' => 'application/json',
            ]
        ]);
        $response_data = json_decode($response->getBody()->getContents());

        if (!$response_data->success) {
            return back()->withErrors($response_data->message);
        }

        if ($type === 'question') {
            return to_route('staff_curriculum.soal')->with('success', 'Berhasil tambah data!');
        }

        if ($type === 'school-exam') {
            return to_route('staff_curriculum.sekolah.v_ujian_detail', ['id' => $response_data?->data?->id])->with('message', 'Berhasil tambah data!')->with('alertClass', 'alert-success');
        }

        if ($request->input('role') == 'teacher') {
            return to_route('staff_curriculum.guru')->with('success', 'Berhasil tambah data!');
        }
        if ($request->input('role') == 'student') {
            return to_route('staff_curriculum.siswa')->with('success', 'Berhasil tambah data!');
        }
        if ($request->input('role') == 'staff') {
            return to_route('staff_curriculum.staff')->with('success', 'Berhasil tambah data!');
        }

        if ($type === 'register-exam/teacher' || $type === 'register-exam/student') {
            return to_route('staff_curriculum.ujian')->with('success', 'Ujian berhasil didaftarkan!');
        }

        return back()->with('success', 'Berhasil tambah data!');
    }

    public function update(string $type, Request $request)
    {
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        if ($type === 'update-rpp-status') {
            try {
                $rppId = $request->input('rpp_id');

                $response = $this->client->put("/api/v1/cms/staff-curriculum/rpp/{$rppId}/update-status", [
                    'form_params' => [
                        'status' => $request->input('status'),
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . session('token'),
                        'Accept' => 'application/json',
                    ]
                ]);

                $response_data = json_decode($response->getBody()->getContents());

                if (!$response_data?->success) throw new Exception('Gagal memperbarui status');

                return back()->with('success', 'Berhasil memperbarui status!');
            } catch (\Throwable $e) {
                return back()->withErrors($e->getMessage());
            }
        }

        if ($type === 'update-rpp-draft-status') {
            try {
                $rppId = $request->input('rpp_id');
                $rppDraftId = $request->input('rpp_draft_id');

                $response = $this->client->put("/api/v1/cms/staff-curriculum/rpp-draft/rpp/{$rppId}/{$rppDraftId}/update-status", [
                    'form_params' => [
                        'status' => $request->input('status'),
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . session('token'),
                        'Accept' => 'application/json',
                    ]
                ]);

                $response_data = json_decode($response->getBody()->getContents());

                if (!$response_data?->success) throw new Exception('Gagal memperbarui status');

                return back()->with('success', 'Berhasil memperbarui status!');
            } catch (\Throwable $e) {
                return back()->withErrors($e->getMessage());
            }
        }

        if (!$request->has('choice_text') && !$request->has('files')) {
            $data = $request->all();
        } else {
            if ($request->has('choice_text')) {
                $choice_text = $request->input('choice_text');
                $is_true = $request->input('is_true');
                $choices = [];

                for ($i = 0; $i < count($choice_text); $i++) {
                    $choices[] = [
                        'choice_text' => $choice_text[$i],
                        'is_true' => !empty($is_true[$i]) ? true : false,
                    ];
                }

                $data = array_merge($request->except('choice_text', 'is_true'), ['choices' => $choices]);
            }

            if ($request->has('files')) {
                $files = $request->file('files');
                $types = $request->input('types');
                $resources = [];

                for ($i = 0; $i < count($files); $i++) {
                    $fileName = $files[$i]->getClientOriginalName();
                    $resources[] = [
                        'file_name' => pathinfo($fileName)['filename'],
                        'file_type' => $types[$i],
                        'file_url' => $files[$i],
                    ];
                }

                if ($request->has('choice_text')) {
                    $data = array_merge($data, ['resources' => $resources]);
                } else {
                    $data = array_merge($request->except('files', 'types'), ['resources' => $resources]);
                }
            }
        }

        $response = $this->client->put("/api/v1/cms/staff-curriculum/$type/" . $this->normalizeId($data['id']), [
            'form_params' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' . session('token'),
                'Accept' => 'application/json',
            ]
        ]);
        $response_data = json_decode($response->getBody()->getContents());

        if (!$response_data->success) {
            return back()->withErrors($response_data->message);
        }

        if ($type === 'question') {
            return to_route('staff_curriculum.soal')->with('success', 'Berhasil ubah data!');
        }

        if ($type === 'school-exam') {
            return to_route('staff_curriculum.sekolah.v_ujian')->with('message', 'Berhasil ubah data!')->with('alertClass', 'alert-success');
        }

        if ($request->input('role') == 'teacher') {
            return to_route('staff_curriculum.guru')->with('success', 'Berhasil ubah data!');
        }
        if ($request->input('role') == 'student') {
            return to_route('staff_curriculum.siswa')->with('success', 'Berhasil ubah data!');
        }
        if ($request->input('role') == 'staff') {
            return to_route('staff_curriculum.staff')->with('success', 'Berhasil ubah data!');
        }

        return back()->with('success', 'Berhasil ubah data!');
    }

    public function delete(string $type, Request $request)
    {
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        try {
            $data = $request->all();
            $response = $this->client->delete("/api/v1/cms/staff-curriculum/$type/" . $this->normalizeId($data['id']), [
                'form_params' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if (!$response_data->success) {
                return back()->withErrors($response_data->message);
            }

            return back()->with('success', 'Berhasil hapus data!');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
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
                return redirect('/admin/login');
            }

            return back();
        } else {
            return redirect('/admin/login');
        }
    }
}