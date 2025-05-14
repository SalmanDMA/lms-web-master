<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use GuzzleHttp\Client;

class RppController extends Controller
{
  use ApiHelperTrait, StaticDataTrait;

  protected $client;

  public function __construct()
  {
    $this->client = new Client([
      'base_uri' => env('API_URL')
    ]);
  }

  public function v_rpp()
  {
    $academic_years = $this->generateAcademicYears();
    $status = ['Dalam Proses', 'Diterima', 'Ditolak', 'Dibatalkan'];
    $course_data = $this->fetchCourses();
    $rpp_data = $this->fetchData('/api/v1/cms/staff-curriculum/rpp');
    $rpp_bank = $this->fetchData('/api/v1/cms/staff-curriculum/rpp-bank');

    foreach ($rpp_data->data ?? [] as $rpp) {
      $course = collect($course_data)->firstWhere('id', $rpp->courses);
      $rpp->course_title = $course ? $course->courses_title : 'Unknown Course';
    }

    if ($this->isValidResponse($rpp_data)) {
      foreach ($rpp_data->data ?? [] as $rpp) {
        $rpp->academic_year = $this->transformAcademicYear($academic_years, $rpp->academic_year);
      }
    }

    return view('staff-curriculum.rpp.index', [
      'academic_years' => $academic_years,
      'status' => $status,
      'courses' => $course_data,
      'rpp' => $rpp_data->data ?? [],
      'rpp_bank' => $rpp_bank->data ?? [],
      'message' => null,
      'alertClass' => null,
    ]);
  }

  public function v_rpp_detail($id)
  {
    if (!$this->isAuthorized('STAFF')) {
      return redirect('/login');
    }

    $courseData = $this->fetchCourses();
    $levels = $this->fetchLevels();
    $academic_years = $this->generateAcademicYears();
    $rppResponse = $this->fetchData('/api/v1/cms/staff-curriculum/rpp/' . $id);
    $rppDraftResponse = $this->fetchData('/api/v1/cms/staff-curriculum/rpp-draft/rpp/' . $id);
    $me = $this->fetchData('/api/v1/auth/profile/me');

    if ($this->isValidResponse($rppResponse)) {
      $this->transformRppData($rppResponse->data, $courseData, $levels, $academic_years);
    }

    if ($this->isValidResponse($rppDraftResponse)) {
      foreach ($rppDraftResponse->data as &$draft) {
        $this->transformRppData($draft, $courseData, $levels, $academic_years);
      }

      $rppDraftResponse->data = collect($rppDraftResponse->data)
        ->sortByDesc(function ($draft) {
          return $draft->updated_at ?: $draft->created_at;
        })
        ->values()
        ->all();
    } else {
      $rppDraftResponse = [];
    }

    return view('staff-curriculum.rpp.detail', [
      'rpp' => $rppResponse->data ?? [],
      'draft' => $rppDraftResponse->data ?? [],
      'me' => $me->data ?? null,
    ]);
  }

  private function transformRppData(&$rppData, $courseData, $levels, $academic_year)
  {
    $rppData->courses = $this->transformCourse($courseData, $rppData->courses);
    $rppData->class_level = $this->transformLevel($levels, $rppData->class_level);
    $rppData->academic_year = $this->transformAcademicYear($academic_year, $rppData->academic_year);
  }

  private function isValidResponse($response)
  {
    return $response && isset($response->success) && $response->success && isset($response->data);
  }

  public function fetchCourses()
  {
    $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/course');
    return $response_data->data ?? [];
  }

  public function fetchLevels()
  {
    $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/class');
    return $response_data->data ?? [];
  }

  public function generateAcademicYears()
  {
    $response_data = $this->fetchData('/api/v1/cms/staff-curriculum/academic-year');
    return $response_data->data ?? [];
  }
}
