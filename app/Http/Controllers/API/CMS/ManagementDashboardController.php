<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Course;
use App\Models\Major;
use App\Models\Student;
use App\Models\SubClasses;
use App\Models\Teacher;
use App\Models\User;

class ManagementDashboardController extends Controller
{
    use CommonTrait;

    public function getSchoolStatistics()
    {
        $userLogin = auth()->user();

        $schoolId = $userLogin->role === 'STAFF' ? $userLogin->school_id : $userLogin->id;

        if (!$schoolId) {
            return $this->sendError('School ID tidak ditemukan untuk pengguna ini.', null, 400);
        }

        $userIds = User::where('school_id', $schoolId)->pluck('id');

        $jumlahSiswa = Student::whereIn('user_id', $userIds)->count();
        $jumlahGuru = Teacher::whereIn('user_id', $userIds)->count();
        $jumlahPelajaran = Course::where('created_by', $schoolId)->count();
        $jumlahKelas = SubClasses::where('school_id', $schoolId)->count();
        $jumlahJurusan = Major::where('school_id', $schoolId)->count();

        $statistics = [
            'jumlah_siswa' => $jumlahSiswa,
            'jumlah_guru' => $jumlahGuru,
            'jumlah_pelajaran' => $jumlahPelajaran,
            'jumlah_kelas' => $jumlahKelas,
            'jumlah_jurusan' => $jumlahJurusan
        ];

        return $this->sendResponse($statistics, 'Berhasil mengambil statistik sekolah');
    }
}
