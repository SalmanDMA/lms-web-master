<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\AcademicYear;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementAcademicYearController extends Controller
{
    use CommonTrait;

    public function index()
    {

        $userLogin = auth()->user();

        $school_id = $userLogin->role === 'STAFF' || $userLogin->role === 'TEACHER' ? $userLogin->school_id : $userLogin->id;

        $academic = AcademicYear::where('school_id',  $school_id)->get();

        if ($academic->isEmpty()) {
            return $this->sendError('Tahun akademik tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($academic, 'Berhasil mengambil semua data tahun akademik.');
    }

    public function store(Request $request)
    {

        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'year' => 'required|string|max:255',
            'status' => 'required|string',
        ], [
            'year.required' => 'Ups, Anda Belum Melengkapi Form',
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $generateId = IdGenerator::generate(['table' => 'academic_years', 'length' => 16, 'prefix' => 'ACY-']);

        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $request->merge(['id' => $generateId, 'school_id' => $school_id]);

        $academic = AcademicYear::create($request->all());

        return $this->sendResponse($academic, 'Berhasil menambahkan data tahun akademik', 201);
    }

    public function show($id)
    {
        $userLogin = auth()->user();

        $school_id = $userLogin->role === 'STAFF' || $userLogin->role === 'TEACHER' ? $userLogin->school_id : $userLogin->id;

        $academic = AcademicYear::where('school_id', $school_id)->find($id);

        if (!$academic) {
            return $this->sendError('Tahun akademik tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($academic, 'Berhasil menemukan data tahun akademik.');
    }

    public function update($id, Request $request)
    {
        $userLogin = auth()->user();

        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $academic = AcademicYear::where('school_id', $school_id)->find($id);

        if (!$academic) {
            return $this->sendError('Kelas tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'year' => 'required|string|max:255',
            'status' => 'required|string',
        ], [
            'year.required' => 'Ups, Anda Belum Melengkapi Form',
            'status.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $academic->update($request->all());
        return $this->sendResponse($academic, 'Berhasil mengubah data kelas');
    }

    public function destroy($id)
    {
        $userLogin = auth()->user();

        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $academic = AcademicYear::where('school_id', $school_id)->find($id);

        if (!$academic) {
            return $this->sendError('Tahun akademik tidak ditemukan.', null, 200);
        }

        $academic->delete();
        return $this->sendResponse($academic, 'Tahun akademik berhasil di hapus');
    }
}
