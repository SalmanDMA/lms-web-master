<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiHelperTrait;
use App\Http\Traits\StaticDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SoalKategoriController extends Controller
{
    use ApiHelperTrait, StaticDataTrait;

    public function __construct()
    {
        $this->initializeApiHelper();
    }

    public function add_soal_kategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'newCategoryName' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        $data = [
            'name' => $request->newCategoryName,
        ];

        $response_data = $this->postData('/api/v1/cms/staff-curriculum/category-question', $data, 'json');

        return $this->handle_response($response_data, 'add');
    }

    public function edit_soal_kategori(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'editCategoryName' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        $data = [
            'name' => $request->editCategoryName,
        ];

        $response_data = $this->putData('/api/v1/cms/staff-curriculum/category-question/' . $id, $data, 'json');

        return $this->handle_response($response_data, 'edit');
    }

    public function delete_soal_kategori($id)
    {
        $response_data = $this->deleteData('/api/v1/cms/staff-curriculum/category-question/' . $id);

        return $this->handle_response($response_data, 'delete');
    }

    private function handle_response($response_data, $type)
    {
        $course_data = $this->fetchCourses();
        $levels = $this->fetchLevels();
        $question_type = $this->generateQuestionTypes();
        $question_category = $this->fetchData('/api/v1/cms/staff-curriculum/category-question');

        $learningTeacher = $this->fetchData('/api/v1/cms/staff-curriculum/learning');
        $learningCourseIds = collect($learningTeacher->data ?? [])->pluck('course.id');
        $filteredCourseDataForShare = collect($course_data)->whereIn('id', $learningCourseIds);

        $message = $this->getResponseMessage($response_data->success, $type, $response_data->message);
        $alertClass = $response_data->success ? 'alert-success' : 'alert-danger';

        return redirect()->back()->with('message', $message)
            ->with('alertClass', $alertClass)
            ->with('courses', $filteredCourseDataForShare)
            ->with('levels', $levels)
            ->with('question_type', $question_type)
            ->with('question_category', $question_category->data ?? []);
    }

    private function getResponseMessage($success, $type, $apiMessage)
    {
        $operation = match ($type) {
            'add' => 'menambahkan',
            'edit' => 'mengubah',
            'delete' => 'menghapus',
            default => '',
        };

        return $success ? "Berhasil $operation kategori soal." : "Gagal $operation kategori soal. $apiMessage";
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
}
