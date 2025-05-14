<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\QuestionCategory;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementQuestionCategoryController extends Controller
{
    use CommonTrait;

    public function index()
    {

        $categories = QuestionCategory::all();

        if ($categories->isEmpty()) {
            return $this->sendError('Category tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($categories, 'Berhasil mengambil semua data Category.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ], [
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $generateId = IdGenerator::generate(['table' => 'question_categories', 'length' => 16, 'prefix' => 'QCA-']);

        $request->merge(['id' => $generateId]);

        $category = QuestionCategory::create($request->all());

        return $this->sendResponse($category, 'Berhasil menambahkan data category', 201);
    }

    public function show($id)
    {
        $category = QuestionCategory::find($id);

        if (!$category) {
            return $this->sendError('category tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($category, 'Berhasil menemukan data category.');
    }

    public function update($id, Request $request)
    {
        $category = QuestionCategory::find($id);

        if (!$category) {
            return $this->sendError('Category tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ], [
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $category->update($request->all());
        return $this->sendResponse($category, 'Berhasil mengubah data category');
    }

    public function destroy($id)
    {
        $category = QuestionCategory::find($id);

        if (!$category) {
            return $this->sendError('Category tidak ditemukan.', null, 200);
        }

        $category->delete();
        return $this->sendResponse($category, 'Category berhasil di hapus');
    }
}
