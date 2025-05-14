<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\Classes;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManagementClassController extends Controller
{

    use CommonTrait, StaticDataTrait;

    public function index(Request $request)
    {
        $search = $request->query('search');

        if ($search) {
            $class = Classes::where('name', 'like', '%' . $search . '%')->get();
        } else {
            $class = Classes::all();
        }

        if ($class->isEmpty()) {
            return $this->sendError('Kelas tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($class, 'Berhasil mengambil semua data kelas.');
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

        $request->merge(['id' => $this->getUniqueId('class', 'CLA-', 16)]);

        $class = Classes::create($request->all());

        return $this->sendResponse($class, 'Berhasil menambahkan data kelas', 201);
    }

    public function show($id)
    {
        // $convertId = $this->convertSubClassId($id);

        $class = Classes::find($id);

        if (!$class) {
            return $this->sendError('Kelas tidak ditemukan.', null, 200);
        }

        return $this->sendResponse($class, 'Berhasil menemukan data kelas.');
    }

    public function update($id, Request $request)
    {
        $convertId = $this->convertSubClassId($id);

        $class = Classes::find($convertId);

        if (!$class) {
            return $this->sendError('Kelas tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ], [
            'name.required' => 'Ups, Anda Belum Melengkapi Form',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $class->update($request->all());
        return $this->sendResponse($class, 'Berhasil mengubah data kelas');
    }

    public function destroy($id)
    {
        $convertId = $this->convertSubClassId($id);

        $class = Classes::find($convertId);

        if (!$class) {
            return $this->sendError('Kelas tidak ditemukan.', null, 200);
        }

        $class->delete();
        return $this->sendResponse($class, 'Kelas berhasil di hapus');
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
