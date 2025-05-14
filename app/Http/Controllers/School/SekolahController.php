<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
    }

    public function v_tahunAjaran()
    {
        if (!session()->has('token') || !session('role') === 'ADMIN') {
            return redirect('/admin/login');
        }

        try {
            $academicYearData = $this->client->get('/api/v1/cms/admin/academic-year', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);

            return view('school.sekolah.tahun-ajaran', [
                'title' => 'Tahun Ajaran',
                'cmsData' => $cmsData?->data[0] ?? [],
                'academicYearData' => json_decode($academicYearData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function v_jurusan()
    {
        if (!session()->has('token') || !session('role') === 'ADMIN') {
            return redirect('/admin/login');
        }

        try {
            $majorData = $this->client->get('/api/v1/cms/admin/major', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);

            return view('school.sekolah.jurusan', [
                'title' => 'Jurusan',
                'cmsData' => $cmsData?->data[0] ?? [],
                'majorData' => json_decode($majorData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function v_kelas()
    {
        if (!session()->has('token') || !session('role') === 'ADMIN') {
            return redirect('/admin/login');
        }

        try {
            $classData = $this->client->get('/api/v1/cms/admin/class', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);

            return view('school.sekolah.kelas', [
                'title' => 'Kelas',
                'cmsData' => $cmsData?->data[0] ?? [],
                'classData' => json_decode($classData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function v_subKelas()
    {
        if (!session()->has('token') || !session('role') === 'ADMIN') {
            return redirect('/admin/login');
        }

        try {
            $classData = $this->client->get('/api/v1/cms/admin/class', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $subClassData = $this->client->get('/api/v1/cms/admin/sub-class', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/admin/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            $response = $this->fetchData('/api/v1/cms/staff-administrator/user');
            $teachers = [];

            if ($response?->success) {
                foreach (($response?->data ?? []) as $data) {
                    if ($data->role === 'TEACHER') {
                        $teachers[] = $data;
                    }
                }
            }

            return view('school.sekolah.sub-kelas', [
                'title' => 'Sub Kelas',
                'cmsData' => $cmsData?->data[0] ?? [],
                'classData' => json_decode($classData->getBody()->getContents())->data ?? [],
                'subClassData' => json_decode($subClassData->getBody()->getContents())->data ?? [],
                'teachers' => $teachers,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}