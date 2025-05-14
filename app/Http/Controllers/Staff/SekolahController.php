<?php

namespace App\Http\Controllers\Staff;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Controllers\Controller;

class SekolahController extends Controller
{
    use ApiHelperTrait;
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('API_URL')
        ]);
    }

    public function v_tahunAjaran()
    {
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        try {
            $academicYearData = $this->client->get('/api/v1/cms/staff-curriculum/academic-year', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);

            return view('staff-curriculum.sekolah.tahun-ajaran', [
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
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        try {
            $majorData = $this->client->get('/api/v1/cms/staff-curriculum/major', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            return view('staff-curriculum.sekolah.jurusan', [
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
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        try {
            $classData = $this->client->get('/api/v1/cms/staff-curriculum/class', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            return view('staff-curriculum.sekolah.kelas', [
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
        if (!session()->has('token') || !session('role') === 'STAFF') {
            return redirect('/admin/login');
        }

        try {
            $classData = $this->client->get('/api/v1/cms/staff-curriculum/class', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);
            $subClassData = $this->client->get('/api/v1/cms/staff-curriculum/sub-class', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);

            return view('staff-curriculum.sekolah.sub-kelas', [
                'title' => 'Sub Kelas',
                'classData' => json_decode($classData->getBody()->getContents())->data ?? [],
                'cmsData' => $cmsData?->data[0] ?? [],
                'subClassData' => json_decode($subClassData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}