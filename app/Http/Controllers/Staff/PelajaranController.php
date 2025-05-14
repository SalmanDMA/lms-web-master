<?php

namespace App\Http\Controllers\Staff;

use GuzzleHttp\Client;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Controllers\Controller;

class PelajaranController extends Controller
{
    use ApiHelperTrait;

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
            $courseData = $this->client->get('/api/v1/cms/staff-curriculum/course', [
                'headers' => [
                    'Authorization' => 'Bearer ' . session('token'),
                    'Accept' => 'application/json',
                ]
            ]);
            $cmsData = $this->fetchData('/api/v1/cms/staff-curriculum/cms', [
                'headers' => [['Authorization' => 'Bearer ' . session('token')]]
            ]);

            return view('staff-curriculum.pelajaran.daftar-pelajaran', [
                'title' => 'Daftar Pelajaran',
                'cmsData' => $cmsData?->data[0] ?? [],
                'courseData' => json_decode($courseData->getBody()->getContents())->data ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}