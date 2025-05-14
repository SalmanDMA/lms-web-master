<?php

use App\Http\Controllers\API\Mobile\NotificationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthViewController;
use App\Http\Controllers\School\MainController as SchoolMainController;
use App\Http\Controllers\School\SekolahController as SchoolSekolahController;
use App\Http\Controllers\School\PelajaranController as SchoolPelajaranController;
use App\Http\Controllers\School\UjianController as SchoolUjianController;
use App\Http\Controllers\Staff\SoalKategoriController as StaffSoalKategoriController;
use App\Http\Controllers\Staff\MainController as StaffMainController;
use App\Http\Controllers\Staff\SekolahController as StaffSekolahController;
use App\Http\Controllers\Staff\PelajaranController as StaffPelajaranController;
use App\Http\Controllers\Staff\KelasAjarController as StaffKelasAjarController;
use App\Http\Controllers\Staff\PertanyaanUjianController;
use App\Http\Controllers\Staff\RppController as StaffRppController;
use App\Http\Controllers\Staff\StaffAdminMainController;
use App\Http\Controllers\Staff\StaffAdminPelajaranController;
use App\Http\Controllers\Staff\StaffAdminSekolahController;
use App\Http\Controllers\Staff\StaffSoalBankController;
use App\Http\Controllers\Staff\UjianController as StaffUjianController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\Student\MainController as StudentMainController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Teacher\KelasAjarController;
use App\Http\Controllers\Teacher\MainController as TeacherMainController;
use App\Http\Controllers\Teacher\MateriBankController;
use App\Http\Controllers\Teacher\NotifikasiController;
use App\Http\Controllers\Teacher\PembelajaranController;
use App\Http\Controllers\Teacher\PembelajaranMateriController;
use App\Http\Controllers\Teacher\PembelajaranPenilaianUlanganController;
use App\Http\Controllers\Teacher\PembelajaranTugasController;
use App\Http\Controllers\Teacher\PembelajaranUlanganController;
use App\Http\Controllers\Teacher\ProfileController;
use App\Http\Controllers\Teacher\RekapNilaiController;
use App\Http\Controllers\Teacher\RekapNilaiTugasController;
use App\Http\Controllers\Teacher\RekapNilaiUlanganController;
use App\Http\Controllers\Teacher\RppBankController;
use App\Http\Controllers\Teacher\RppController;
use App\Http\Controllers\Teacher\RppDraftController;
use App\Http\Controllers\Teacher\SchoolExamAssessmentController;
use App\Http\Controllers\Teacher\SchoolExamController;
use App\Http\Controllers\Teacher\SchoolExamQuestionController;
use App\Http\Controllers\Teacher\SoalBankController;
use App\Http\Controllers\Teacher\SoalKategoriController;
use App\Http\Controllers\Teacher\SubjectMatterBankController;
use App\Http\Controllers\Teacher\TugasBankController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthViewController::class, 'v_login']);
Route::get('/dashboard', [AuthViewController::class, 'v_dashboard'])->name('dashboard');
Route::get('/login', [AuthViewController::class, 'v_login']);
Route::get('/login/{role}', [AuthViewController::class, 'v_login']);
Route::post('/login/{role}', [AuthViewController::class, 'onLogin']);
Route::get('/register', [AuthViewController::class, 'v_signup']);
Route::get('/register/{role}', [AuthViewController::class, 'v_signup']);
Route::get('/forgot-password', [AuthViewController::class, 'v_forgot']);
Route::get('/forgot-password/{role}', [AuthViewController::class, 'v_forgot']);
Route::get('/otp', [AuthViewController::class, 'v_otp']);
Route::get('/reset-password', [AuthViewController::class, 'v_resetPassword']);
Route::get('/logout', [AuthViewController::class, 'onLogout'])->name('student.logout');

Route::group(['prefix' => 'component', 'as' => 'component.'], function () {
    Route::get('accordion', function () {
        return view('mazer.components.accordion');
    })->name('accordion');
});

Route::group(['prefix' => 'student'], function () {
    Route::get('/dashboard', [StudentMainController::class, 'v_dashboard']);

    Route::get('/material', [StudentMainController::class, 'v_material']);
    Route::get('/material/{id}', [StudentMainController::class, 'v_materialDetail']);
    Route::get('/assignment', [StudentMainController::class, 'v_assignment'])->name('student.assignment');
    Route::get('/assignment/{id}', [StudentMainController::class, 'v_assignmentDetail']);
    Route::post('/assignment/submit', [StudentMainController::class, 'createSubmission'])->name('student.assignment.submit');
    Route::get('/assignment/{assignment_id}/edit/{submission_id}', [StudentMainController::class, 'v_editAssignment'])->name('student.assignment.edit');
    Route::put('/assignment/submission/{submissionId}', [StudentMainController::class, 'editSubmission'])->name('student.submission.update');
    Route::delete('/assignment/resource/{id}/delete', [StudentMainController::class, 'deleteSubmissionResource'])->name('student.assignmentresource.delete');
    Route::get('/school-exam', [StudentMainController::class, 'v_schoolExam']);
    Route::post('/token/verify', [StudentMainController::class, 'verifyTokenClassExam'])->name('token.verify');
    Route::get('/exam-detail-class/{id}', [StudentMainController::class, 'detailExamClass']);
    Route::get('/exam-detail-class/{id}/question', [StudentMainController::class, 'examQuestion']);
    Route::get('/exam-detail-school/{id}', [StudentMainController::class, 'detailExamSchool']);
    Route::get('/exam-detail-school/{id}/{schoolId}/question', [StudentMainController::class, 'examSchoolQuestion']);
    Route::get('/score', [StudentMainController::class, 'scoreList']);

    Route::get('/exam/{id}', [StudentMainController::class, 'v_exam']);
    Route::post('/exam/submit', [ExamController::class, 'submitExam']);

    Route::get('/profile', [StudentProfileController::class, 'v_profile'])->name('student.profile');
    Route::get('profile/detail', [StudentProfileController::class, 'v_profile_detail'])->name('student.profile.detail');
    Route::put('/profile', [StudentProfileController::class, 'update_profile'])->name('student.profile.update');

    Route::get('/profile/edit-password', [StudentProfileController::class, 'v_changePassword'])->name('student.change.password');
    Route::put('/profile/edit-password', [StudentProfileController::class, 'change_password'])->name('student.update.password');
});

Route::group(['prefix' => 'teacher'], function () {
    Route::get('/dashboard', [TeacherMainController::class, 'v_dashboard'])->name('teacher.dashboard');

    Route::get('/logout', [TeacherMainController::class, 'logout'])->name('teacher.logout');

    Route::group(['prefix' => 'kategori-soal'], function () {
        Route::post('/add', [SoalKategoriController::class, 'add_soal_kategori'])->name('teacher.add_soal_kategori');
        Route::put('/{id}/edit', [SoalKategoriController::class, 'edit_soal_kategori'])->name('teacher.edit_soal_kategori');
        Route::delete('/{id}/delete', [SoalKategoriController::class, 'delete_soal_kategori'])->name('teacher.delete_soal_kategori');
    });

    Route::prefix('bank')->group(function () {
        Route::group(['prefix' => 'rpp'], function () {
            Route::get('/', [RppBankController::class, 'v_bank_rpp'])->name('teacher.bank.rpp');
            Route::post('/', [RppBankController::class, 'addBank_rpp']);
            Route::post('/ajukan', [RppBankController::class, 'ajukanBank_rpp']);
            Route::put('/{id}', [RppBankController::class, 'updateBank_rpp']);
            Route::delete('/{id}', [RppBankController::class, 'deleteBank_rpp']);
        });

        Route::prefix('/rpp/{rpp_id}/subject')->group(function () {
            Route::get('/', [RppBankController::class, 'v_bank_rpp_detail'])->name('teacher.bank.v_bank_rpp_detail');
            Route::get('/add', [SubjectMatterBankController::class, 'v_add_subject_matter'])->name('teacher.bank.v_add_subject_matter');
            Route::post('/add', [SubjectMatterBankController::class, 'add_subject_matter'])->name('teacher.bank.add_subject_matter');
            Route::get('/{id}', [SubjectMatterBankController::class, 'v_subject_matter_detail'])->name('teacher.bank.v_subject_matter_detail');
            Route::get('/{id}/edit', [SubjectMatterBankController::class, 'v_edit_subject_matter'])->name('teacher.bank.v_edit_subject_matter');
            Route::put('/{id}/edit', [SubjectMatterBankController::class, 'edit_subject_matter'])->name('teacher.bank.edit_subject_matter');
            Route::delete('/{id}/delete', [SubjectMatterBankController::class, 'delete_subject_matter'])->name('teacher.bank.delete_subject_matter');
        });

        Route::group(['prefix' => 'materi'], function () {
            Route::get('/', [MateriBankController::class, 'v_bank_materi'])->name('teacher.v_bank.materi');
            Route::get('/add', [MateriBankController::class, 'v_add_materi'])->name('teacher.bank.v_add_materi');
            Route::post('/add', [MateriBankController::class, 'add_materi'])->name('teacher.bank.add_materi');
            Route::post('/share', [MateriBankController::class, 'share_materi'])->name('teacher.bank.share_materi');
            Route::get('/{id}', [MateriBankController::class, 'v_materi_detail'])->name('teacher.bank.v_materi_detail');
            Route::get('/{id}/edit', [MateriBankController::class, 'v_edit_materi'])->name('teacher.bank.v_edit_materi');
            Route::put('/{id}/edit', [MateriBankController::class, 'edit_materi'])->name('teacher.bank.edit_materi');
            Route::delete('/{id}/delete', [MateriBankController::class, 'delete_materi'])->name('teacher.bank.delete_materi');
            Route::get('/{id}/download', [MateriBankController::class, 'download_materi'])->name('teacher.bank.download_materi');
        });

        Route::group(['prefix' => 'tugas'], function () {
            Route::get('/', [TugasBankController::class, 'v_bank_tugas'])->name('teacher.v_bank.tugas');
            Route::get('/add', [TugasBankController::class, 'v_add_tugas'])->name('teacher.bank.v_add_tugas');
            Route::post('/add', [TugasBankController::class, 'add_tugas'])->name('teacher.bank.add_tugas');
            Route::post('/share', [TugasBankController::class, 'share_tugas'])->name('teacher.bank.share_tugas');
            Route::get('/{id}/edit', [TugasBankController::class, 'v_edit_tugas'])->name('teacher.bank.v_edit_tugas');
            Route::put('/{id}/edit', [TugasBankController::class, 'edit_tugas'])->name('teacher.bank.edit_tugas');
            Route::delete('/{id}/delete', [TugasBankController::class, 'delete_tugas'])->name('teacher.bank.delete_tugas');
            Route::get('/{id}/download', [TugasBankController::class, 'download_tugas'])->name('teacher.bank.download_tugas');
        });

        Route::group(['prefix' => 'soal'], function () {
            Route::get('/', [SoalBankController::class, 'v_bank_soal'])->name('teacher.v_bank.soal');
            Route::get('/add', [SoalBankController::class, 'v_add_soal'])->name('teacher.bank.v_add_soal');
            Route::post('/add', [SoalBankController::class, 'add_soal'])->name('teacher.bank.add_soal');
            Route::delete('/multi-delete', [SoalBankController::class, 'multi_delete_soal'])->name('teacher.bank.delete_multiple_soal');
            Route::post('/share', [SoalBankController::class, 'share_soal'])->name('teacher.bank.share_soal');
            Route::get('/{id}/edit', [SoalBankController::class, 'v_edit_soal'])->name('teacher.bank.v_edit_soal');
            Route::put('/{id}/edit', [SoalBankController::class, 'edit_soal'])->name('teacher.bank.edit_soal');
            Route::delete('/{id}/delete', [SoalBankController::class, 'delete_soal'])->name('teacher.bank.delete_soal');
            Route::get('/{id}/download', [SoalBankController::class, 'download_soal'])->name('teacher.bank.download_soal');
        });
    });

    Route::group(['prefix' => 'pengajar'], function () {
        Route::group(['prefix' => 'rpp'], function () {
            Route::get('/', [RppController::class, 'v_rpp'])->name('teacher.v_pengajar.rpp');
            Route::get('/add', [RppController::class, 'v_add_rpp'])->name('teacher.pengajar.v_add_rpp');
            Route::get('/{id}', [RppController::class, 'v_rpp_detail'])->name('teacher.pengajar.v_rpp_detail');
            Route::post('/', [RppController::class, 'add_rpp'])->name('teacher.pengajar.add_rpp');
            Route::post('/ajukan', [RppController::class, 'ajukan_rpp'])->name('teacher.pengajar.ajukan_rpp');

            Route::group(['prefix' => '{rpp_id}/draft'], function () {
                // draft
                Route::get('/add', [RppDraftController::class, 'v_add_draft_rpp'])->name('teacher.pengajar.v_add_draft_rpp');
                Route::post('/add', [RppDraftController::class, 'add_draft_rpp'])->name('teacher.pengajar.add_draft_rpp');
                Route::post('/ajukan', [RppDraftController::class, 'ajukan_draft_rpp'])->name('teacher.pengajar.ajukan_draft_rpp');
                Route::get('/edit/{rpp_draft_id}', [RppDraftController::class, 'v_edit_draft_rpp'])->name('teacher.pengajar.v_edit_draft_rpp');
                Route::put('/edit/{rpp_draft_id}', [RppDraftController::class, 'edit_draft_rpp'])->name('teacher.pengajar.edit_draft_rpp');
                Route::delete('/delete/{rpp_draft_id}', [RppDraftController::class, 'delete_draft_rpp'])->name('teacher.pengajar.delete_draft_rpp');
                Route::get('/download/{rpp_draft_id}', [RppDraftController::class, 'download_draft'])->name('teacher.pengajar.download_draft');
                Route::put('/batalkan/{rpp_draft_id}', [RppDraftController::class, 'batalkan_draft_rpp'])->name('teacher.pengajar.batalkan_draft_rpp');

                // subject
                Route::get('/add/{rpp_draft_id}/subject', [RppController::class, 'v_add_subject_matter'])->name('teacher.pengajar.v_add_subject_matter');
                Route::post('/add/{rpp_draft_id}/subject', [RppController::class, 'add_subject_matter'])->name('teacher.pengajar.add_subject_matter');
                Route::get('/edit/{rpp_draft_id}/subject/{id}', [RppController::class, 'v_edit_subject_matter'])->name('teacher.pengajar.v_edit_subject_matter');
                Route::put('/edit/{rpp_draft_id}/subject/{id}', [RppController::class, 'edit_subject_matter'])->name('teacher.pengajar.edit_subject_matter');
                Route::delete('/delete/{draft_id}/subject/{id}', [RppController::class, 'delete_subject_matter'])->name('teacher.pengajar.delete_subject_matter');
            });
        });

        Route::group(['prefix' => 'kelas-ajar'], function () {
            Route::get('/', [KelasAjarController::class, 'v_pengajar_kelas_ajar'])->name('teacher.v_pengajar.kelas_ajar');
            Route::post('/add', [KelasAjarController::class, 'add_kelas_ajar'])->name('teacher.pengajar.add_kelas_ajar');
            Route::get('/{course_id}/student/{sub_class_id}', [KelasAjarController::class, 'v_kelas_ajar_student'])->name('teacher.pengajar.v_kelas_ajar_student');
            Route::post('/{course_id}/student/{sub_class_id}', [KelasAjarController::class, 'enroll_student'])->name('teacher.pengajar.enroll_student');
            Route::put('/{course_id}/student/{sub_class_id}', [KelasAjarController::class, 'unenroll_student'])->name('teacher.pengajar.unenroll_student');
        });

        Route::group(['prefix' => 'pembelajaran'], function () {
            Route::get('/', [PembelajaranController::class, 'v_pembelajaran'])->name('teacher.v_pengajar.pembelajaran');

            Route::group(['prefix' => 'materi'], function () {
                Route::get('/{learning_id}', [PembelajaranMateriController::class, 'v_materi'])->name('teacher.pengajar.pembelajaran.v_materi');
                Route::get('/{learning_id}/add', [PembelajaranMateriController::class, 'v_add_materi'])->name('teacher.pengajar.pembelajaran.v_add_materi');
                Route::post('/{learning_id}/add', [PembelajaranMateriController::class, 'add_materi'])->name('teacher.pengajar.pembelajaran.add_materi');
                Route::post('/{learning_id}/import', [PembelajaranMateriController::class, 'import_materi'])->name('teacher.pengajar.pembelajaran.import_materi');
                Route::get('/{learning_id}/detail/{id}', [PembelajaranMateriController::class, 'v_edit_materi'])->name('teacher.pengajar.pembelajaran.v_materi_detail');
                Route::put('/{learning_id}/detail/{id}', [PembelajaranMateriController::class, 'edit_materi'])->name('teacher.pengajar.pembelajaran.update_materi');
                Route::delete('/{learning_id}/delete/{id}', [PembelajaranMateriController::class, 'delete_materi'])->name('teacher.pengajar.pembelajaran.delete_materi');
            });

            Route::group(['prefix' => 'tugas'], function () {
                Route::get('/{learning_id}', [PembelajaranTugasController::class, 'v_tugas'])->name('teacher.pengajar.pembelajaran.v_tugas');
                Route::get('/{learning_id}/add', [PembelajaranTugasController::class, 'v_add_tugas'])->name('teacher.pengajar.pembelajaran.v_add_tugas');
                Route::post('/{learning_id}/add', [PembelajaranTugasController::class, 'add_tugas'])->name('teacher.pengajar.pembelajaran.add_tugas');
                Route::post('/{learning_id}/import', [PembelajaranTugasController::class, 'import_tugas'])->name('teacher.pengajar.pembelajaran.import_tugas');
                Route::get('/{learning_id}/detail/{id}', [PembelajaranTugasController::class, 'v_edit_tugas'])->name('teacher.pengajar.pembelajaran.v_tugas_detail');
                Route::put('/{learning_id}/detail/{id}', [PembelajaranTugasController::class, 'edit_tugas'])->name('teacher.pengajar.pembelajaran.update_tugas');
                Route::delete('/{learning_id}/delete/{id}', [PembelajaranTugasController::class, 'delete_tugas'])->name('teacher.pengajar.pembelajaran.delete_tugas');
                Route::get('/{learning_id}/download/{id}', [PembelajaranTugasController::class, 'download_tugas'])->name('teacher.pengajar.pembelajaran.download_tugas');
                Route::get('/{learning_id}/detail/{id}/student/{student_id}', [PembelajaranTugasController::class, 'v_detail_tugas_student'])->name('teacher.pengajar.pembelajaran.v_detail_tugas_student');
                Route::put('/{learning_id}/detail/{id}/student/{student_id}/simpan-nilai-utama/{submission_id}', [PembelajaranTugasController::class, 'simpan_nilai_utama'])->name('teacher.pengajar.pembelajaran.simpan_nilai_utama');
                Route::get('/{learning_id}/detail/{id}/student/{student_id}/rubah-nilai/{submission_id}', [PembelajaranTugasController::class, 'v_rubah_nilai'])->name('teacher.pengajar.pembelajaran.rubah-nilai');
                Route::put('/{learning_id}/detail/{id}/student/{student_id}/rubah-nilai/{submission_id}', [PembelajaranTugasController::class, 'rubah_nilai'])->name('teacher.pengajar.pembelajaran.rubah_nilai');
                Route::put('/{learning_id}/detail/{id}/student/{student_id}/feedback-send/{submission_id}', [PembelajaranTugasController::class, 'feedback_send'])->name('teacher.pengajar.pembelajaran.feedback_send');
            });

            Route::group(['prefix' => 'ulangan'], function () {
                Route::get('/{learning_id}', [PembelajaranUlanganController::class, 'v_ulangan'])->name('teacher.pengajar.pembelajaran.v_ulangan');
                Route::get('/{learning_id}/add', [PembelajaranUlanganController::class, 'v_add_ulangan'])->name('teacher.pengajar.pembelajaran.v_add_ulangan');
                Route::post('/{learning_id}/add', [PembelajaranUlanganController::class, 'add_ulangan'])->name('teacher.pengajar.pembelajaran.add_ulangan');
                Route::get('/{learning_id}/detail/{ulangan_id}', [PembelajaranUlanganController::class, 'v_edit_ulangan'])->name('teacher.pengajar.pembelajaran.v_ulangan_detail');
                Route::put('/{learning_id}/detail/{ulangan_id}', [PembelajaranUlanganController::class, 'edit_ulangan'])->name('teacher.pengajar.pembelajaran.update_ulangan');
                Route::put('/{learning_id}/update-is-active/{ulangan_id}', [PembelajaranUlanganController::class, 'edit_ulangan_is_active'])->name('teacher.pengajar.pembelajaran.update_ulangan_is_active');
                Route::delete('/{learning_id}/delete/{ulangan_id}', [PembelajaranUlanganController::class, 'delete_ulangan'])->name('teacher.pengajar.pembelajaran.delete_ulangan');

                // Soal ulangan
                Route::post('/{learning_id}/soal/{ulangan_id}/import', [PembelajaranUlanganController::class, 'import_soal'])->name('teacher.pengajar.pembelajaran.import_soal');
                Route::get('/{learning_id}/soal/{ulangan_id}/add', [PembelajaranUlanganController::class, 'v_add_soal'])->name('teacher.pengajar.pembelajaran.v_add_soal');
                Route::post('/{learning_id}/soal/{ulangan_id}/add', [PembelajaranUlanganController::class, 'add_soal'])->name('teacher.pengajar.pembelajaran.add_soal');
                Route::delete('/{learning_id}/soal/{ulangan_id}/multi-delete', [PembelajaranUlanganController::class, 'multi_delete_soal'])->name('teacher.pengajar.pembelajaran.delete_multiple_soal');
                Route::post('/{learning_id}/soal/{ulangan_id}/share', [PembelajaranUlanganController::class, 'share_soal'])->name('teacher.pengajar.pembelajaran.share_soal');
                Route::get('/{learning_id}/soal/{ulangan_id}/edit/{soal_id}', [PembelajaranUlanganController::class, 'v_edit_soal'])->name('teacher.pengajar.pembelajaran.v_edit_soal');
                Route::put('/{learning_id}/soal/{ulangan_id}/edit/{soal_id}', [PembelajaranUlanganController::class, 'edit_soal'])->name('teacher.pengajar.pembelajaran.edit_soal');
                Route::delete('/{learning_id}/soal/{ulangan_id}/delete/{soal_id}', [PembelajaranUlanganController::class, 'delete_soal'])->name('teacher.pengajar.pembelajaran.delete_soal');
                Route::get('/{learning_id}/soal/{ulangan_id}/download/{soal_id}', [PembelajaranUlanganController::class, 'download_soal'])->name('teacher.pengajar.pembelajaran.download_soal');

                // Penilaian ulangan
                Route::get('/{learning_id}/penilaian/{ulangan_id}', [PembelajaranPenilaianUlanganController::class, 'v_penilaian'])->name('teacher.pengajar.pembelajaran.v_penilaian');
                Route::get('/{learning_id}/penilaian/{ulangan_id}/student/{student_id}', [PembelajaranPenilaianUlanganController::class, 'v_penilaian_student'])->name('teacher.pengajar.pembelajaran.v_penilaian_student');
                Route::put('/{learning_id}/penilaian/{ulangan_id}/student/{student_id}/simpan-nilai/{response_id}', [PembelajaranPenilaianUlanganController::class, 'update_is_main'])->name('teacher.pengajar.pembelajaran.update_is_main');
                Route::get('/{learning_id}/penilaian/{ulangan_id}/student/{student_id}/ulasan/{response_id}', [PembelajaranPenilaianUlanganController::class, 'v_penilaian_ulasan'])->name('teacher.pengajar.pembelajaran.v_penilaian_ulasan');
                Route::put('/{learning_id}/penilaian/{ulangan_id}/student/{student_id}/ulasan/{response_id}', [PembelajaranPenilaianUlanganController::class, 'penilaian_ulasan'])->name('teacher.pengajar.pembelajaran.penilaian_ulasan');
            });
        });

        Route::group(['prefix' => 'rekap-nilai'], function () {
            Route::get('/', [RekapNilaiController::class, 'v_rekap_nilai'])->name('teacher.pengajar.v_rekap_nilai');

            Route::group(['prefix' => 'tugas'], function () {
                Route::get('/{learning_id}', [RekapNilaiTugasController::class, 'v_tugas'])->name('teacher.pengajar.rekap.v_tugas');
                Route::get('/{learning_id}/detail/{tugas_id}', [RekapNilaiTugasController::class, 'v_detail_tugas_submission'])->name('teacher.pengajar.rekap.v_detail_tugas_submission');
                Route::put('/{learning_id}/detail/{tugas_id}/simpan-nilai', [RekapNilaiTugasController::class, 'simpan_nilai_tugas'])->name('teacher.pengajar.rekap.simpan_nilai_tugas');
                Route::post('/{learning_id}/detail/{tugas_id}/import', [RekapNilaiTugasController::class, 'import_tugas_submission'])->name('teacher.pengajar.rekap.import_tugas_submission');
                Route::get('/{learning_id}/detail/{tugas_id}/export', [RekapNilaiTugasController::class, 'export_tugas_submission'])->name('teacher.pengajar.rekap.export_tugas_submission');
            });

            Route::group(['prefix' => 'ulangan'], function () {
                Route::get('/{learning_id}', [RekapNilaiUlanganController::class, 'v_ulangan'])->name('teacher.pengajar.rekap.v_ulangan');
                Route::get('/{learning_id}/detail/{ulangan_id}', [RekapNilaiUlanganController::class, 'v_detail_ulangan_submission'])->name('teacher.pengajar.rekap.v_detail_ulangan_submission');
                Route::put('/{learning_id}/detail/{ulangan_id}/simpan-nilai', [RekapNilaiUlanganController::class, 'simpan_nilai_ulangan'])->name('teacher.pengajar.rekap.simpan_nilai_ulangan');
                Route::post('/{learning_id}/detail/{ulangan_id}/import', [RekapNilaiUlanganController::class, 'import_ulangan_submission'])->name('teacher.pengajar.rekap.import_ulangan_submission');
                Route::get('/{learning_id}/detail/{ulangan_id}/export', [RekapNilaiUlanganController::class, 'export_ulangan_submission'])->name('teacher.pengajar.rekap.export_ulangan_submission');
            });
        });
    });

    Route::group(['prefix' => 'sekolah'], function () {
        Route::group(['prefix' => 'ujian'], function () {
            // Ujian
            Route::get('/', [SchoolExamController::class, 'v_ujian'])->name('teacher.sekolah.v_ujian');
            Route::get('/{ujian_id}', [SchoolExamController::class, 'v_ujian_detail'])->name('teacher.sekolah.v_ujian_detail');
            Route::put('/{ujian_id}/update-is-active', [SchoolExamController::class, 'edit_ujian_is_active'])->name('teacher.sekolah.edit_ujian_is_active');
            Route::put('/{ujian_id}/edit', [SchoolExamController::class, 'edit_ujian'])->name('teacher.sekolah.edit_ujian');

            // Soal ujian
            Route::post('/{ujian_id}/soal-import', [SchoolExamQuestionController::class, 'import_soal'])->name('teacher.sekolah.import_soal');
            Route::get('/{ujian_id}/soal-add', [SchoolExamQuestionController::class, 'v_add_soal'])->name('teacher.sekolah.v_add_soal');
            Route::post('/{ujian_id}/soal-add', [SchoolExamQuestionController::class, 'add_soal'])->name('teacher.sekolah.add_soal');
            Route::delete('/{ujian_id}/soal-multi-delete', [SchoolExamQuestionController::class, 'multi_delete_soal'])->name('teacher.sekolah.delete_multiple_soal');
            Route::post('/{ujian_id}/soal-share', [SchoolExamQuestionController::class, 'share_soal'])->name('teacher.sekolah.share_soal');
            Route::get('/{ujian_id}/soal-edit/{soal_id}', [SchoolExamQuestionController::class, 'v_edit_soal'])->name('teacher.sekolah.v_edit_soal');
            Route::put('/{ujian_id}/soal-edit/{soal_id}', [SchoolExamQuestionController::class, 'edit_soal'])->name('teacher.sekolah.edit_soal');
            Route::delete('/{ujian_id}/soal-delete/{soal_id}', [SchoolExamQuestionController::class, 'delete_soal'])->name('teacher.sekolah.delete_soal');
            Route::get('/{ujian_id}/soal-download/{soal_id}', [SchoolExamQuestionController::class, 'download_soal'])->name('teacher.sekolah.download_soal');

            // Penilaian ujian
            Route::get('/{ujian_id}/penilaian', [SchoolExamAssessmentController::class, 'v_penilaian'])->name('teacher.sekolah.v_penilaian');
            Route::get('/{ujian_id}/penilaian/student/{student_id}', [SchoolExamAssessmentController::class, 'v_penilaian_student'])->name('teacher.sekolah.v_penilaian_student');
            Route::put('/{ujian_id}/penilaian/student/{student_id}/simpan-nilai/{response_id}', [SchoolExamAssessmentController::class, 'update_is_main'])->name('teacher.sekolah.update_is_main');
            Route::get('/{ujian_id}/penilaian/student/{student_id}/ulasan/{response_id}', [SchoolExamAssessmentController::class, 'v_penilaian_ulasan'])->name('teacher.sekolah.v_penilaian_ulasan');
            Route::put('/{ujian_id}/penilaian/student/{student_id}/ulasan/{response_id}', [SchoolExamAssessmentController::class, 'penilaian_ulasan'])->name('teacher.sekolah.penilaian_ulasan');
        });
    });

    Route::group(['prefix' => 'pengaturan'], function () {
        Route::get('/profile', [ProfileController::class, 'v_profile'])->name('teacher.v_pengaturan.profile');
        Route::put('/profile/general', [ProfileController::class, 'update_profile_general'])->name('teacher.pengaturan.update_profile_general');
        Route::put('/profile/password', [ProfileController::class, 'update_profile_password'])->name('teacher.pengaturan.update_profile_password');
    });

    Route::group(['prefix' => 'notifikasi'], function () {
        Route::get('/', [NotifikasiController::class, 'index'])->name('notifications.index');
        Route::post('/mark-all-read', [NotifikasiController::class, 'markAllRead'])->name('notifications.markAllRead');
    });
});

Route::group(['prefix' => 'admin'], function () {
    Route::get('/login', [AuthViewController::class, 'v_adminLogin']);
    Route::post('/login', [AuthViewController::class, 'onAdminLogin']);
    Route::get('/logout', [SchoolMainController::class, 'logout'])->name('admin.logout');

    Route::get('/dashboard', [SchoolMainController::class, 'v_dashboard'])->name('admin.dashboard');

    Route::group(['prefix' => 'sekolah'], function () {
        Route::get('/tahun-ajaran', [SchoolSekolahController::class, 'v_tahunAjaran'])->name('admin.tahun_ajaran');
        Route::get('/jurusan', [SchoolSekolahController::class, 'v_jurusan'])->name('admin.jurusan');
        Route::get('/kelas', [SchoolSekolahController::class, 'v_kelas'])->name('admin.kelas');
        Route::get('/sub-kelas', [SchoolSekolahController::class, 'v_subKelas'])->name('admin.sub_kelas');
    });

    Route::group(['prefix' => 'pelajaran'], function () {
        Route::get('/daftar-pelajaran', [SchoolPelajaranController::class, 'v_daftarPelajaran'])->name('admin.daftar_pelajaran');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('/guru', [SchoolMainController::class, 'v_userTeacher'])->name('admin.guru');
        Route::get('/guru/create', [SchoolMainController::class, 'v_userTeacherCreate'])->name('admin.guru_create');
        Route::get('/guru/{id}/update', [SchoolMainController::class, 'v_userTeacherUpdate'])->name('admin.guru_update');

        Route::get('/siswa', [SchoolMainController::class, 'v_userStudent'])->name('admin.siswa');
        Route::get('/siswa/create', [SchoolMainController::class, 'v_userStudentCreate'])->name('admin.siswa_create');
        Route::get('/siswa/{id}/update', [SchoolMainController::class, 'v_userStudentUpdate'])->name('admin.siswa_update');

        Route::get('/staff', [SchoolMainController::class, 'v_userStaff'])->name('admin.staff');
        Route::get('/staff/create', [SchoolMainController::class, 'v_userStaffCreate'])->name('admin.staff_create');
        Route::get('/staff/{id}/update', [SchoolMainController::class, 'v_userStaffUpdate'])->name('admin.staff_update');
    });

    Route::group(['prefix' => 'cms'], function () {
        Route::get('/', [SchoolMainController::class, 'v_cms'])->name('admin.cms');
        Route::post('/{id}', [SchoolMainController::class, 'update_cms'])->name('admin.update_cms');
    });

    Route::post('/{type}', [SchoolMainController::class, 'create']);
    Route::put('/{type}', [SchoolMainController::class, 'update']);
    Route::delete('/{type}', [SchoolMainController::class, 'delete']);
});

Route::group(['prefix' => 'staff-administrator'], function () {
    Route::get('/dashboard', [StaffAdminMainController::class, 'v_adminDashboard'])->name('staff_administrator.dashboard');

    Route::get('/logout', [StaffAdminMainController::class, 'logout'])->name('staff_administrator.logout');

    Route::group(['prefix' => 'sekolah'], function () {
        Route::get('/tahun-ajaran', [StaffAdminSekolahController::class, 'v_tahunAjaran'])->name('staff_administrator.tahun_ajaran');
        Route::get('/jurusan', [StaffAdminSekolahController::class, 'v_jurusan'])->name('staff_administrator.jurusan');
        Route::get('/kelas', [StaffAdminSekolahController::class, 'v_kelas'])->name('staff_administrator.kelas');
        Route::get('/sub-kelas', [StaffAdminSekolahController::class, 'v_subKelas'])->name('staff_administrator.sub_kelas');
    });

    Route::group(['prefix' => 'pelajaran'], function () {
        Route::get('/daftar-pelajaran', [StaffAdminPelajaranController::class, 'v_daftarPelajaran'])->name('staff_administrator.daftar_pelajaran');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('/guru', [StaffAdminMainController::class, 'v_userTeacher'])->name('staff_administrator.guru');
        Route::get('/guru/create', [StaffAdminMainController::class, 'v_userTeacherCreate'])->name('staff_administrator.guru_create');
        Route::get('/guru/{id}/update', [StaffAdminMainController::class, 'v_userTeacherUpdate'])->name('staff_administrator.guru_update');

        Route::get('/siswa', [StaffAdminMainController::class, 'v_userStudent'])->name('staff_administrator.siswa');
        Route::get('/siswa/create', [StaffAdminMainController::class, 'v_userStudentCreate'])->name('staff_administrator.siswa_create');
        Route::get('/siswa/{id}/update', [StaffAdminMainController::class, 'v_userStudentUpdate'])->name('staff_administrator.siswa_update');

        Route::get('/staff', [StaffAdminMainController::class, 'v_userStaff'])->name('staff_administrator.staff');
        Route::get('/staff/create', [StaffAdminMainController::class, 'v_userStaffCreate'])->name('staff_administrator.staff_create');
        Route::get('/staff/{id}/update', [StaffAdminMainController::class, 'v_userStaffUpdate'])->name('staff_administrator.staff_update');
    });

    Route::post('/{type}', [StaffAdminMainController::class, 'create']);
    Route::put('/{type}', [StaffAdminMainController::class, 'update']);
    Route::delete('/{type}', [StaffAdminMainController::class, 'delete']);
});

Route::group(['prefix' => 'staff-curriculum'], function () {
    Route::get('/dashboard', [StaffMainController::class, 'v_dashboard'])->name('staff_curriculum.dashboard');

    Route::get('/logout', [StaffMainController::class, 'logout'])->name('staff_curriculum.logout');

    Route::group(['prefix' => 'kategori-soal'], function () {
        Route::post('/add', [StaffSoalKategoriController::class, 'add_soal_kategori'])->name('staff_curriculum.add_soal_kategori');
        Route::put('/{id}/edit', [StaffSoalKategoriController::class, 'edit_soal_kategori'])->name('staff_curriculum.edit_soal_kategori');
        Route::delete('/{id}/delete', [StaffSoalKategoriController::class, 'delete_soal_kategori'])->name('staff_curriculum.delete_soal_kategori');
    });

    Route::group(['prefix' => 'kelas-mengajar'], function(){
        Route::get('/', [StaffKelasAjarController::class, 'v_kelas_ajar'])->name('staff_curriculum.kelas_mengajar');
        Route::post('/add', [StaffKelasAjarController::class, 'add_kelas_ajar'])->name('staff_curriculum.add_kelas_ajar');
        Route::get('/{course_id}/student/{sub_class_id}', [StaffKelasAjarController::class, 'v_kelas_ajar_student'])->name('staff_curriculum.v_kelas_ajar_student');
        Route::post('/{course_id}/student/{sub_class_id}', [StaffKelasAjarController::class, 'enroll_student'])->name('staff_curriculum.class.enroll_student');
        Route::put('/{course_id}/student/{sub_class_id}', [StaffKelasAjarController::class, 'unenroll_student'])->name('staff_curriculum.class.unenroll_student');
        Route::delete('/{enrollment_id}', [StaffKelasAjarController::class, 'delete_kelas_ajar'])->name('staff_curriculum.delete_kelasAjar');

    });

    Route::group(['prefix' => 'pengajuan'], function () {
        Route::get('/pengajuan-rpp', [StaffRppController::class, 'v_rpp'])->name('staff_curriculum.pengajuan_rpp');
        Route::get('/pengajuan-rpp/{id}', [StaffRppController::class, 'v_rpp_detail'])->name('staff_curriculum.pengajuan_rpp_detail');
    });

    Route::group(['prefix' => 'sekolah'], function () {
        Route::get('/tahun-ajaran', [StaffSekolahController::class, 'v_tahunAjaran'])->name('staff_curriculum.tahun_ajaran');
        Route::get('/jurusan', [StaffSekolahController::class, 'v_jurusan'])->name('staff_curriculum.jurusan');
        Route::get('/kelas', [StaffSekolahController::class, 'v_kelas'])->name('staff_curriculum.kelas');
        Route::get('/sub-kelas', [StaffSekolahController::class, 'v_subKelas'])->name('staff_curriculum.sub_kelas');
    });

    Route::group(['prefix' => 'pelajaran'], function () {
        Route::get('/daftar-pelajaran', [StaffPelajaranController::class, 'v_daftarPelajaran'])->name('staff_curriculum.daftar_pelajaran');
    });

    Route::group(['prefix' => 'ujian'], function () {
        Route::get('/', [StaffUjianController::class, 'v_ujian'])->name('staff_curriculum.sekolah.v_ujian');
        Route::get('/create', [StaffUjianController::class, 'v_ujian_create'])->name('staff_curriculum.sekolah.v_ujian_create');

        Route::post('/enroll-student', [StaffUjianController::class, 'enroll_student'])->name('staff_curriculum.enroll_student');
        Route::delete('/unenroll-student', [StaffUjianController::class, 'unenroll_student'])->name('staff_curriculum.unenroll_student');
        Route::post('/enroll-teacher', [StaffUjianController::class, 'enroll_teacher'])->name('staff_curriculum.enroll_teacher');
        Route::delete('/unenroll-teacher', [StaffUjianController::class, 'unenroll_teacher'])->name('staff_curriculum.unenroll_teacher');

        Route::post('/section', [StaffUjianController::class, 'add_section'])->name('staff_curriculum.add_section');
        Route::get('/section/{id}', [StaffUjianController::class, 'detail_section'])->name('staff_curriculum.detail_section');
        Route::delete('/section/{id}', [StaffUjianController::class, 'delete_section'])->name('staff_curriculum.delete_section');

        Route::get('/{id}', [StaffUjianController::class, 'v_ujian_detail'])->name('staff_curriculum.sekolah.v_ujian_detail');
        Route::get('/{id}/penilaian', [StaffUjianController::class, 'v_ujian_penilaian'])->name('staff_curriculum.sekolah.v_ujian_penilaian');
        Route::get('/{id}/penilaian/{student_id}', [StaffUjianController::class, 'v_penilaian_student'])->name('staff_curriculum.sekolah.v_penilaian_student');
        Route::put('/{id}/penilaian/{student_id}/simpan-nilai/{response_id}', [StaffUjianController::class, 'update_is_main'])->name('staff_curriculum.sekolah.update_is_main');
        Route::get('/{id}/penilaian/{student_id}/ulasan/{response_id}', [StaffUjianController::class, 'v_penilaian_ulasan'])->name('staff_curriculum.sekolah.v_penilaian_ulasan');
        Route::put('/{id}/penilaian/{student_id}/ulasan/{response_id}', [StaffUjianController::class, 'penilaian_ulasan'])->name('staff_curriculum.sekolah.penilaian_ulasan');

        Route::put('/{id}/update-is-active', [StaffUjianController::class, 'edit_ujian_is_active'])->name('staff_curriculum.sekolah.edit_ujian_is_active');
        Route::put('/{id}/edit', [StaffUjianController::class, 'edit_ujian'])->name('staff_curriculum.sekolah.edit_ujian');
        Route::delete('/{id}', [StaffUjianController::class, 'delete_ujian'])->name('staff_curriculum.sekolah.delete_ujian');

        Route::post('/{id}/{section_id}/soal-import', [PertanyaanUjianController::class, 'import_soal'])->name('staff_curriculum.sekolah.import_soal');
        Route::get('/{id}/{section_id}/soal-add', [PertanyaanUjianController::class, 'v_add_soal'])->name('staff_curriculum.sekolah.v_add_soal');
        Route::post('/{id}/{section_id}/soal-add', [PertanyaanUjianController::class, 'add_soal'])->name('staff_curriculum.sekolah.add_soal');
        Route::delete('/{id}/{section_id}/soal-multi-delete', [PertanyaanUjianController::class, 'multi_delete_soal'])->name('staff_curriculum.sekolah.delete_multiple_soal');
        Route::post('/{id}/{section_id}/soal-share', [PertanyaanUjianController::class, 'share_soal'])->name('staff_curriculum.sekolah.share_soal');
        Route::get('/{id}/{section_id}/soal-edit/{soal_id}', [PertanyaanUjianController::class, 'v_edit_soal'])->name('staff_curriculum.sekolah.v_edit_soal');
        Route::put('/{id}/{section_id}/soal-edit/{soal_id}', [PertanyaanUjianController::class, 'edit_soal'])->name('staff_curriculum.sekolah.edit_soal');
        Route::delete('/{id}/{section_id}/soal-delete/{soal_id}', [PertanyaanUjianController::class, 'delete_soal'])->name('staff_curriculum.sekolah.delete_soal');
        Route::get('/{id}/{section_id}/soal-download/{soal_id}', [PertanyaanUjianController::class, 'download_soal'])->name('staff_curriculum.sekolah.download_soal');
    });

    Route::group(['prefix' => 'soal'], function () {
        Route::get('/', [StaffSoalBankController::class, 'v_bank_soal'])->name('staff_curriculum.bank.v_bank.soal');
        Route::get('/add', [StaffSoalBankController::class, 'v_add_soal'])->name('staff_curriculum.bank.v_add_soal');
        Route::post('/add', [StaffSoalBankController::class, 'add_soal'])->name('staff_curriculum.bank.add_soal');
        Route::delete('/multi-delete', [StaffSoalBankController::class, 'multi_delete_soal'])->name('staff_curriculum.bank.delete_multiple_soal');
        Route::post('/share', [StaffSoalBankController::class, 'share_soal'])->name('staff_curriculum.bank.share_soal');
        Route::get('/{id}/edit', [StaffSoalBankController::class, 'v_edit_soal'])->name('staff_curriculum.bank.v_edit_soal');
        Route::put('/{id}/edit', [StaffSoalBankController::class, 'edit_soal'])->name('staff_curriculum.bank.edit_soal');
        Route::delete('/{id}/delete', [StaffSoalBankController::class, 'delete_soal'])->name('staff_curriculum.bank.delete_soal');
        Route::get('/{id}/download', [StaffSoalBankController::class, 'download_soal'])->name('staff_curriculum.bank.download_soal');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('/guru', [StaffMainController::class, 'v_userTeacher'])->name('staff_curriculum.guru');
        Route::get('/guru/create', [StaffMainController::class, 'v_userTeacherCreate'])->name('staff_curriculum.guru_create');
        Route::get('/guru/{id}/update', [StaffMainController::class, 'v_userTeacherUpdate'])->name('staff_curriculum.guru_update');

        Route::get('/siswa', [StaffMainController::class, 'v_userStudent'])->name('staff_curriculum.siswa');
        Route::get('/siswa/create', [StaffMainController::class, 'v_userStudentCreate'])->name('staff_curriculum.siswa_create');
        Route::get('/siswa/{id}/update', [StaffMainController::class, 'v_userStudentUpdate'])->name('staff_curriculum.siswa_update');

        Route::get('/staff', [StaffMainController::class, 'v_userStaff'])->name('staff_curriculum.staff');
        Route::get('/staff/create', [StaffMainController::class, 'v_userStaffCreate'])->name('staff_curriculum.staff_create');
        Route::get('/staff/{id}/update', [StaffMainController::class, 'v_userStaffUpdate'])->name('staff_curriculum.staff_update');
    });

    Route::post('/{type}', [StaffMainController::class, 'create']);
    Route::put('/{type}', [StaffMainController::class, 'update']);
    Route::delete('/{type}', [StaffMainController::class, 'delete']);
});