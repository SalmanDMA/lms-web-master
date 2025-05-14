<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class StaffAdminPelajaranController extends Controller
{
  protected $client;

  public function __construct()
  {
    $this->client = new Client([
      'base_uri' => env('API_URL')
    ]);
  }

  public function v_daftarPelajaran()
  {
    if (!session()->has('token') || !session('role') === 'STAFF') {
      return redirect('/admin/login');
    }

    try {
      $courseData = $this->client->get('/api/v1/cms/staff-administrator/course', [
        'headers' => [
          'Authorization' => 'Bearer ' . session('token'),
          'Accept' => 'application/json',
        ]
      ]);

      return view('staff-admin.pelajaran.daftar-pelajaran', [
        'title' => 'Daftar Pelajaran',
        'courseData' => json_decode($courseData->getBody()->getContents())->data ?? [],
      ]);
    } catch (\Exception $e) {
      return back()->withErrors($e->getMessage());
    }
  }
}
