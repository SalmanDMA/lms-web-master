<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Major;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementMajorController extends Controller
{
    use CommonTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');

        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $query = Major::where('school_id', $school_id);

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $majors = $query->get();

        if ($majors->isEmpty()) {
            return $this->sendError('Tidak ada data jurusan', null, 200);
        }

        return $this->sendResponse($majors, 'Berhasil mengambil data jurusan');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $generateId = IdGenerator::generate(['table' => 'majors', 'length' => 16, 'prefix' => 'MAJ-']);

        $major = Major::create([
            'id' => $generateId,
            'name' => $request->name,
            'school_id' => $school_id
        ]);

        return $this->sendResponse($major, 'Berhasil menambahkan data jurusan');
    }

    public function show($id)
    {
        $userLogin = auth()->user();
        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;
        $major = Major::where('school_id', $school_id)->find($id);

        if (!$major) {
            return $this->sendError('Jurusan tidak ditemukan', null, 200);
        }

        return $this->sendResponse($major, 'Berhasil mengambil data jurusan');
    }

    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();
        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;
        $major = Major::where('school_id', $school_id)->find($id);

        if (!$major) {
            return $this->sendError('Jurusan tidak ditemukan', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $major->name = $request->name;
        $major->save();

        return $this->sendResponse($major, 'Berhasil memperbarui data jurusan');
    }

    public function destroy($id)
    {
        $userLogin = auth()->user();
        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;
        $major = Major::where('school_id', $school_id)->find($id);

        if (!$major) {
            return $this->sendError('Jurusan tidak ditemukan', null, 200);
        }

        $major->delete();

        return $this->sendResponse($major, 'Berhasil menghapus data jurusan');
    }
}
