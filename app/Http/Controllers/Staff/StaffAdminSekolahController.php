<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class StaffAdminSekolahController extends Controller
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
    if (!session()->has('token') || !session('role') === 'STAFF') {
      return redirect('/admin/login');
    }

    try {
      $academicYearData = $this->client->get('/api/v1/cms/staff-administrator/academic-year', [
        'headers' => [
          'Authorization' => 'Bearer ' . session('token'),
          'Accept' => 'application/json',
        ]
      ]);

      return view('staff-admin.sekolah.tahun-ajaran', [
        'title' => 'Tahun Ajaran',
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
      $majorData = $this->client->get('/api/v1/cms/staff-administrator/major', [
        'headers' => [
          'Authorization' => 'Bearer ' . session('token'),
          'Accept' => 'application/json',
        ]
      ]);

      return view('staff-admin.sekolah.jurusan', [
        'title' => 'Jurusan',
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
      $classData = $this->client->get('/api/v1/cms/staff-administrator/class', [
        'headers' => [
          'Authorization' => 'Bearer ' . session('token'),
          'Accept' => 'application/json',
        ]
      ]);

      return view('staff-admin.sekolah.kelas', [
        'title' => 'Kelas',
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
      $classData = $this->client->get('/api/v1/cms/staff-administrator/class', [
        'headers' => [
          'Authorization' => 'Bearer ' . session('token'),
          'Accept' => 'application/json',
        ]
      ]);
      $subClassData = $this->client->get('/api/v1/cms/staff-administrator/sub-class', [
        'headers' => [
          'Authorization' => 'Bearer ' . session('token'),
          'Accept' => 'application/json',
        ]
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

      return view('staff-admin.sekolah.sub-kelas', [
        'title' => 'Sub Kelas',
        'classData' => json_decode($classData->getBody()->getContents())->data ?? [],
        'subClassData' => json_decode($subClassData->getBody()->getContents())->data ?? [],
        'teachers' => $teachers,
      ]);
    } catch (\Exception $e) {
      return back()->withErrors($e->getMessage());
    }
  }
}
