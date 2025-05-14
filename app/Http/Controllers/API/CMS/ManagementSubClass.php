<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\SubClasses;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManagementSubClass extends Controller
{
    use CommonTrait, StaticDataTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');

        $query = SubClasses::query();

        if ($userLogin) {
            $school_id = $userLogin->school_id ?? $userLogin->id;
            $query->where('school_id', $school_id);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $class = $query->get();

        if ($class->isEmpty()) {
            return $this->sendError('Sub class tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($class, 'Berhasil mengambil semua data sub class.');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'class_id' => 'required|exists:class,id',
            'guardian' => 'nullable',
        ], [
            'class_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $request->merge(['id' => $this->getUniqueId('sub_class', 'SUB-', 16)]);

        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $class = SubClasses::create([
            'id' => $request->id,
            'name' => $request->name,
            'class_id' => $request->class_id,
            'school_id' => $school_id,
            'guardian' => $request->guardian ?? null,
        ]);

        return $this->sendResponse($class, 'Berhasil menambahkan data sub class', 201);
    }

    public function show($id)
    {
        $userLogin = auth()->user();

        $query = SubClasses::query();

        $convertId = $this->convertSubClassId($id);

        if ($userLogin) {
            $school_id = $userLogin->school_id ?? $userLogin->id;
            $query->where('school_id', $school_id);
        }

        $class = $query->where('id', $convertId)->first();

        if (!$class) {
            return $this->sendError('Sub class tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($class, 'Berhasil menemukan data sub class.');
    }

    public function update($id, Request $request)
    {
        $userLogin = auth()->user();

        $school_id = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        $convertId = $this->convertSubClassId($id);

        $class = SubClasses::where('school_id', $school_id)->find($convertId);

        if (!$class) {
            return $this->sendError('Sub class tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'class_id' => 'required|exists:class,id',
            'guardian' => 'nullable',
        ], [
            'class_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $class->update($request->all());
        $class->load('class');
        return $this->sendResponse($class, 'Berhasil mengubah data sub class');
    }

    public function destroy($id)
    {
        $convertId = $this->convertSubClassId($id);

        $class = SubClasses::find($convertId);

        if (!$class) {
            return $this->sendError('Sub class tidak ditemukan.', null, 200);
        }

        $class->delete();
        return $this->sendResponse($class, 'Sub class berhasil di hapus');
    }

    public function getUniqueId($table, $prefix, $length)
    {
        $newId = $this->generateUniqueId($table, $prefix, $length);

        while (DB::table($table)->where('id', $newId)->exists()) {
            $newId = $this->generateUniqueId($table, $prefix, $length);
        }

        return $newId;
    }

    public function generateUniqueId($table, $prefix, $length)
    {
        $latestId = DB::table($table)
            ->where('id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('id');

        if ($latestId) {
            $lastNumberPart = substr($latestId, strlen($prefix));
            $parts = explode('/', $lastNumberPart);
            $lastNumber = intval($parts[0]);
            $newNumber = $lastNumber + 1;

            $newId = $prefix . str_pad($newNumber, $length - strlen($prefix), '0', STR_PAD_LEFT);

            if ($table !== 'users' && count($parts) > 1) {
                $newId .= '/' . $parts[1];
            }
        } else {
            $newId = $prefix . str_pad('1', $length - strlen($prefix), '0', STR_PAD_LEFT);
        }

        return $newId;
    }
}
