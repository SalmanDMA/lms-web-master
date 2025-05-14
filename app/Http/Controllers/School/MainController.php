<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        if (!session('role') === 'ADMIN' && !session()->has('token')) {
            return redirect('/admin/login');
        }

        try {
            $dashboardData = $this->client->get('/api/v1/cms/admin/dashboard/statistic', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            

            return view('school.dashboard', [
                'title' => 'Dashboard',
                'cmsData' => $cmsData?->data[0] ?? [],
                'dashboardData' => json_decode($dashboardData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    // public function v_tahunAjaran()
    // {
    //     if (!session()->has('token') || !session('role') === 'ADMIN') {
    //         return redirect('/admin/login');
    //     }

    //     try {
    //         $academicYearData = $this->client->get('/api/v1/cms/admin/academic-year', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . session('token'),
    //                 'Accept' => 'application/json',
    //             ]
    //         ]);

    //         $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
    //             'headers' => [['Authorization' => 'Bearer ' . session('token')]]
    //         ]);

    //         return view('school.sekolah.tahun-ajaran', [
    //             'title' => 'Tahun Ajaran',
    //             'cmsData' => $cmsData?->data[0] ?? [],
    //             'academicYearData' => json_decode($academicYearData->getBody()->getContents())->data ?? [],
    //         ]);
    //     } catch (\Exception $e) {
    //         return back()->withErrors($e->getMessage());
    //     }
    // }

    // public function v_kelas()
    // {
    //     if (!session()->has('token') || !session('role') === 'ADMIN') {
    //         return redirect('/admin/login');
    //     }

    //     try {
    //         $classData = $this->client->get('/api/v1/cms/admin/class', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . session('token'),
    //                 'Accept' => 'application/json',
    //             ]
    //         ]);

    //         $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
    //             'headers' => [['Authorization' => 'Bearer ' . session('token')]]
    //         ]);

    //         dd($cmsData);
    //         return view('school.sekolah.kelas', [
    //             'title' => 'Kelas',
    //             'cmsData' => $cmsData?->data[0] ?? [],
    //             'classData' => json_decode($classData->getBody()->getContents())->data ?? [],
    //         ]);
    //     } catch (\Exception $e) {
    //         return back()->withErrors($e->getMessage());
    //     }
    // }

    // public function v_course()
    // {
    //     if (session('role') === 'ADMIN' && session()->has('token')) {
    //         $courseData = $this->client->get('/api/v1/cms/admin/course', [
    //             'headers' => ['Authorization' => 'Bearer ' . session('token')]
    //         ]);

    //         return Inertia::render('school/course', [
    //             'title' => 'Pelajaran',
    //             'courseData' => json_decode($courseData->getBody()->getContents())->data ?? [],
    //         ]);
    //     } else {
    //         return redirect('/admin/login');
    //     }
    // }

    // public function v_jurusan()
    // {
    //     if (!session()->has('token') || !session('role') === 'ADMIN') {
    //         return redirect('/admin/login');
    //     }

    //     try {
    //         $majorData = $this->client->get('/api/v1/cms/admin/major', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . session('token'),
    //                 'Accept' => 'application/json',
    //             ]
    //         ]);

    //         return view('school.sekolah.jurusan', [
    //             'title' => 'Jurusan',
    //             'majorData' => json_decode($majorData->getBody()->getContents())->data ?? [],
    //         ]);
    //     } catch (\Exception $e) {
    //         return back()->withErrors($e->getMessage());
    //     }
    // }

    // public function v_subKelas()
    // {
    //     if (!session()->has('token') || !session('role') === 'ADMIN') {
    //         return redirect('/admin/login');
    //     }

    //     try {
    //         $classData = $this->client->get('/api/v1/cms/admin/class', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . session('token'),
    //                 'Accept' => 'application/json',
    //             ]
    //         ]);
    //         $subClassData = $this->client->get('/api/v1/cms/admin/sub-class', [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . session('token'),
    //                 'Accept' => 'application/json',
    //             ]
    //         ]);

    //         return view('school.sekolah.sub-kelas', [
    //             'title' => 'Sub Kelas',
    //             'classData' => json_decode($classData->getBody()->getContents())->data ?? [],
    //             'subClassData' => json_decode($subClassData->getBody()->getContents())->data ?? [],
    //         ]);
    //     } catch (\Exception $e) {
    //         return back()->withErrors($e->getMessage());
    //     }
    // }

    public function v_userTeacher()
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/admin/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
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

            return view('school.user-teacher.index', [
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
        if (session('role') === 'ADMIN' && session()->has('token')) {

            return view('school.user-teacher.create', [
                'title' => 'Tambah Guru',
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userTeacherUpdate($id)
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/admin/user/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('school.user-teacher.edit', [
                'title' => 'Ubah Guru',
                'user' => json_decode($response->getBody()->getContents())->data,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStudent()
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/admin/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
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

            return view('school.user-student.index', [
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
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $subClassData = $this->fetchData('/api/v1/cms/admin/sub-class');
            $classData = $this->fetchData('/api/v1/cms/admin/class');
            $academicYearData = $this->fetchData('/api/v1/cms/admin/academic-year');

            return view('school.user-student.create', [
                'title' => 'Tambah Siswa',
                'classData' => $classData?->data ?? [],
                'subClassData' => $subClassData?->data ?? [],
                'academicYearData' => $academicYearData?->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStudentUpdate($id)
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/admin/user/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $subClassData = $this->fetchData('/api/v1/cms/admin/sub-class');
            $classData = $this->fetchData('/api/v1/cms/admin/class');
            $academicYearData = $this->fetchData('/api/v1/cms/admin/academic-year');

            return view('school.user-student.edit', [
                'title' => 'Ubah Siswa',
                'user' => json_decode($response->getBody()->getContents())->data,
                'classData' => $classData?->data ?? [],
                'subClassData' => $subClassData?->data ?? [],
                'academicYearData' => $academicYearData?->data ?? [],
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStaff()
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/admin/user', [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
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

            return view('school.user-staff.index', [
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
        if (session('role') === 'ADMIN' && session()->has('token')) {

            return view('school.user-staff.create', [
                'title' => 'Tambah Staff',
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function v_userStaffUpdate($id)
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $response = $this->client->get('/api/v1/cms/admin/user/' . $id, [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);

            return view('school.user-staff.edit', [
                'title' => 'Ubah Staff',
                'user' => json_decode($response->getBody()->getContents())->data,
            ]);
        } else {
            return redirect('/admin/login');
        }
    }

    public function create(string $type, Request $request)
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $data = $request->all();
            $response = $this->client->post("/api/v1/cms/admin/{$type}", [
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

            if ($request->input('role') == 'teacher') {
                return to_route('admin.guru')->with('success', 'Berhasil tambah data!');
            }
            if ($request->input('role') == 'student') {
                return to_route('admin.siswa')->with('success', 'Berhasil tambah data!');
            }
            if ($request->input('role') == 'staff') {
                return to_route('admin.staff')->with('success', 'Berhasil tambah data!');
            }

            return back()->with('success', 'Berhasil tambah data!');
        } else {
            return redirect('/admin/login');
        }
    }

    public function update(string $type, Request $request)
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $data = $request->all();
            $response = $this->client->put("/api/v1/cms/admin/$type/" . $this->normalizeId($data['id']), [
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

            if ($request->input('role') == 'teacher') {
                return to_route('admin.guru')->with('success', 'Berhasil ubah data!');
            }
            if ($request->input('role') == 'student') {
                return to_route('admin.siswa')->with('success', 'Berhasil ubah data!');
            }
            if ($request->input('role') == 'staff') {
                return to_route('admin.staff')->with('success', 'Berhasil ubah data!');
            }

            return back()->with('success', 'Berhasil memperbarui status!');
        } else {
            return redirect('/admin/login');
        }
    }

    public function delete(string $type, Request $request)
    {
        if (session('role') === 'ADMIN' && session()->has('token')) {
            $data = $request->all();
            $response = $this->client->delete("/api/v1/cms/admin/{$type}/" . $this->normalizeId($data['id']), [
                'headers' => ['Authorization' => 'Bearer ' . session('token')]
            ]);
            $response_data = json_decode($response->getBody()->getContents());

            if (!$response_data->success) {
                return back()->withErrors([
                    'title' => 'Hapus Data Gagal!',
                    'message' => $response_data->message,
                ]);
            }

            return back()->with('success', 'Berhasil hapus data!');
        } else {
            return redirect('/admin/login');
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

    public function v_cms()
    {
        $cms = $this->fetchData('/api/v1/cms/admin/cms')?->data ?? [];
        $cms = !empty($cms[0]) ? $cms[0] : [];

        return view('school.cms.index', [
            'title' => 'CMS',
            'cms' => $cms
        ]);
    }

    public function update_cms(Request $request, $id)
    {
        $request->merge([
            '_method' => 'PUT',
        ]);
        $splash_logo = $request->file('splash_logo');
        $login_image_student = $request->file('login_image_student');
        $login_image_teacher = $request->file('login_image_teacher');
        $logo = $request->file('logo');
        $logo_thumbnail = $request->file('logo_thumbnail');

        $http = Http::baseUrl(env('API_URL'))->withHeaders([
            'Authorization' => 'Bearer ' . session('token')
        ]);

        if ($splash_logo) {
            $http->attach('splash_logo', $splash_logo->getContent(), $splash_logo->getClientOriginalName());
        }
        if ($login_image_student) {
            $http->attach('login_image_student', $login_image_student->getContent(), $login_image_student->getClientOriginalName());
        }
        if ($login_image_teacher) {
            $http->attach('login_image_teacher', $login_image_teacher->getContent(), $login_image_teacher->getClientOriginalName());
        }
        if ($logo) {
            $http->attach('logo', $logo->getContent(), $logo->getClientOriginalName());
        }
        if ($logo_thumbnail) {
            $http->attach('logo_thumbnail', $logo_thumbnail->getContent(), $logo_thumbnail->getClientOriginalName());
        }

        $response = $http->post('/api/v1/cms/admin/cms/' . $id, $request->except(['splash_logo', 'login_image_student', 'login_image_teacher', 'logo', 'logo_thumbnail']));

        $response = json_decode($response->body());

        if (!$response?->success) {
            return back()->withErrors($response?->message);
        }

        return back()->with('success', $response?->message);
    }
}