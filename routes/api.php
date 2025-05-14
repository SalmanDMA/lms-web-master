<?php

use App\Http\Controllers\API\Authentication\AdminController;
use App\Http\Controllers\API\Authentication\AuthController;
use App\Http\Controllers\API\CMS\AnswerController;
use App\Http\Controllers\API\CMS\CMSController;
use App\Http\Controllers\API\CMS\ManagementAcademicYearController;
use App\Http\Controllers\API\CMS\ManagementAssignmentBankController;
use App\Http\Controllers\API\CMS\ManagementClassController;
use App\Http\Controllers\API\CMS\ManagementCourseController;
use App\Http\Controllers\API\CMS\ManagementDashboardController;
use App\Http\Controllers\API\CMS\ManagementMajorController;
use App\Http\Controllers\API\CMS\ManagementMaterialBankController;
use App\Http\Controllers\API\CMS\ManagementBankQuestionController;
use App\Http\Controllers\API\CMS\ManagementQuestionCategoryController;
use App\Http\Controllers\API\CMS\ManagementSubClass;
use App\Http\Controllers\API\CMS\ManagementUserController;
use App\Http\Controllers\API\Mobile\AssignmentController;
use App\Http\Controllers\API\Mobile\EnrollmentController;
use App\Http\Controllers\API\Mobile\LearningController;
use App\Http\Controllers\API\Mobile\SubmissionController;
use App\Http\Controllers\API\Mobile\MaterialController;
use App\Http\Controllers\API\CMS\ClassExamController;
use App\Http\Controllers\API\CMS\ExamSectionController;
use App\Http\Controllers\API\CMS\ManagementRppBankController;
use App\Http\Controllers\API\CMS\ManagementRppController;
use App\Http\Controllers\API\CMS\ManagementRppDraftController;
use App\Http\Controllers\API\CMS\ManagementWaliKelasController;
use App\Http\Controllers\API\CMS\PremiumController;
use App\Http\Controllers\API\CMS\QuestionController;
use App\Http\Controllers\API\CMS\SchoolExamController;
use App\Http\Controllers\API\CMS\SubjectMatterRppBankController;
use App\Http\Controllers\API\CMS\SubjectMatterRppController;
use App\Http\Controllers\API\CMS\SubjectMatterRppDraftController;
use App\Http\Controllers\API\Mobile\AttachmentController;
use App\Http\Controllers\API\Mobile\NotificationController;
use App\Models\Answer;
use App\Models\SchoolExam;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['prefix' => 'v1', 'middleware' => 'throttle:rate'], function () {

    Route::get('not-auth', [AuthController::class, 'notAuthorized'])->name('login');

    Route::group(['prefix' => 'auth'], function () {

        Route::post('login', [AuthController::class, 'login']);

        Route::group(['prefix' => 'forgot-password'], function () {
            Route::post('/', [AuthController::class, 'forgotPassword']);
            Route::post('/send-otp', [AuthController::class, 'sendOtp']);
            Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        });

        Route::group(['prefix' => 'register'], function () {

            Route::post('/teacher', [AuthController::class, 'register']);
            Route::post('/student', [AuthController::class, 'register']);
        });



        Route::group(['prefix' => 'profile'], function () {

            Route::middleware(['auth:sanctum'])->group(function () {
                Route::get('/me', [AuthController::class, 'myProfile']);
            });

            Route::middleware(['auth:sanctum', 'ability:student'])->group(function () {
                Route::put('/student', [AuthController::class, 'updateProfileStudent']);
            });

            Route::middleware(['auth:sanctum', 'ability:teacher'])->group(function () {
                Route::put('/teacher', [AuthController::class, 'updateProfileTeacher']);
            });

            Route::middleware(['auth:sanctum', 'ability:staff_curriculum'])->group(function () {
                Route::put('/staff-curriculum', [AuthController::class, 'updateProfileStaff']);
            });

            Route::middleware(['auth:sanctum', 'ability:staff_administrator'])->group(function () {
                Route::put('/staff-administrator', [AuthController::class, 'updateProfileStaff']);
            });

            Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {
                Route::put('/admin', [AdminController::class, 'updateProfileAdmin']);
                Route::put('/school', [AdminController::class, 'updateProfileSchool']);
            });
        });

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/logout', [AuthController::class, 'logout']);
            Route::put('/reset-password', [AuthController::class, 'resetPassword']);
        });
    });

    Route::group(['prefix' => 'mobile'], function () {

        Route::group(['prefix' => 'cms'], function () {
            Route::get('/', [CMSController::class, 'index_not_login']);
        });

        // route yang hanya bisa di akses oleh murid dan admin
        Route::middleware(['auth:sanctum', 'ability:student'])->group(function () {

            Route::group(['prefix' => 'student'], function () {

                // Question
                Route::get('/{id}/question', [QuestionController::class, 'index_student_exam']);

                // Response
                Route::get('/{id}/response', [AnswerController::class, 'index_all_response_student']);

                // Response Exam
                Route::get('/{id}/response-exam', [AnswerController::class, 'index_all_enrollment_school_exam_student']);

                // Analitic
                Route::get('/{id}/analytic', [AnswerController::class, 'analytic_class_exam_student']);

                // Analitic Exam
                Route::get('/{id}/analytic-exam', [AnswerController::class, 'analytic_school_exam_student']);

                Route::get('/score-list', [AnswerController::class, 'score_list_student']);

                Route::group(['prefix' => 'class'], function () {
                    Route::get('/', [ManagementClassController::class, 'index']);
                    Route::get('/{id}', [ManagementClassController::class, 'show']);
                });

                Route::group(['prefix' => 'sub-class'], function () {
                    Route::get('/', [ManagementSubClass::class, 'index']);
                    Route::get('/{id}', [ManagementSubClass::class, 'show']);
                });

                Route::group(['prefix' => 'course'], function () {
                    Route::get('/', [ManagementCourseController::class, 'index']);
                    Route::get('/{id}', [ManagementCourseController::class, 'show']);
                });

                Route::group(['prefix' => 'material'], function () {

                    Route::get('/', [MaterialController::class, 'index']);
                    Route::get('/{id}', [MaterialController::class, 'show']);
                });

                Route::group(['prefix' => 'assignment'], function () {

                    Route::get('/', [AssignmentController::class, 'index']);
                    Route::get('/{id}', [AssignmentController::class, 'show']);
                });

                Route::group(['prefix' => 'submission'], function () {

                    Route::get('/', [SubmissionController::class, 'index']);
                    Route::get('/{id}', [SubmissionController::class, 'show']);
                    Route::post('/', [SubmissionController::class, 'store']);
                    Route::put('/{id}', [SubmissionController::class, 'update']);
                    Route::delete('/{id}', [SubmissionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'class-exam'], function () {
                    Route::get('/', [ClassExamController::class, 'index']);
                    Route::get('/{id}', [ClassExamController::class, 'show']);
                });

                Route::group(['prefix' => 'school-exam'], function () {
                    Route::get('/', [SchoolExamController::class, 'index']);
                    Route::get('/{id}', [SchoolExamController::class, 'show']);
                    Route::post('/{id}/confirmation-token', [SchoolExamController::class, 'confirmation_token']);
                });

                Route::group(['prefix' => 'verify-token-exam'], function () {
                    Route::post('/', [AnswerController::class, 'verifyTokenExam']);
                });

                Route::group(['prefix' => 'answer'], function () {
                    Route::get('/', [AnswerController::class, 'index']);
                    Route::get('/{id}', [AnswerController::class, 'show']);
                    Route::post('/', [AnswerController::class, 'store'])->name('student.answer.submit');
                });

                Route::group(['prefix' => 'response'], function () {
                    Route::post('/', [AnswerController::class, 'storeResponseStudent'])->name('student.response.answer');
                });

                Route::group(['prefix' => 'notification'], function () {
                    Route::get('/', [NotificationController::class, 'index']);
                    Route::get('/{id}', [NotificationController::class, 'show']);
                });

                Route::group(['prefix' => 'attachment'], function () {
                    Route::delete('/{id}', [AttachmentController::class, 'destroy']);
                });
            });
        });

        // route yang hanya dapat di akses oleh guru dan admin
        Route::middleware(['auth:sanctum', 'ability:teacher'])->group(function () {

            Route::group(['prefix' => 'teacher'], function () {

                Route::group(['prefix' => 'class'], function () {
                    Route::get('/', [ManagementClassController::class, 'index']);
                    Route::get('/{id}', [ManagementClassController::class, 'show']);
                });

                Route::group(['prefix' => 'sub-class'], function () {
                    Route::get('/', [ManagementSubClass::class, 'index']);
                    Route::get('/{id}', [ManagementSubClass::class, 'show']);
                });

                Route::group(['prefix' => 'course'], function () {
                    Route::get('/', [ManagementCourseController::class, 'index']);
                    Route::get('/{id}', [ManagementCourseController::class, 'show']);
                });

                Route::group(['prefix' => 'submission'], function () {
                    Route::get('/', [SubmissionController::class, 'index']);
                    Route::get('/{id}', [SubmissionController::class, 'show']);
                    Route::put('/{id}', [SubmissionController::class, 'update']);
                });

                Route::group(['prefix' => 'learning'], function () {
                    Route::get('/', [LearningController::class, 'index']);
                    Route::get('/{id}', [LearningController::class, 'show']);
                    Route::post('/', [LearningController::class, 'store']);
                    Route::put('/{id}', [LearningController::class, 'update']);
                    Route::delete('/{id}', [LearningController::class, 'destroy']);
                });

                Route::group(['prefix' => 'material'], function () {
                    Route::get('/', [MaterialController::class, 'index']);
                    Route::get('/{id}', [MaterialController::class, 'show']);
                    Route::post('/', [MaterialController::class, 'store']);
                    Route::put('/{id}', [MaterialController::class, 'update']);
                    Route::delete('/{id}', [MaterialController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment'], function () {
                    Route::get('/', [AssignmentController::class, 'index']);
                    Route::get('/{id}', [AssignmentController::class, 'show']);
                    Route::post('/', [AssignmentController::class, 'store']);
                    Route::put('/{id}', [AssignmentController::class, 'update']);
                    Route::delete('/{id}', [AssignmentController::class, 'destroy']);
                });

                Route::group(['prefix' => 'user'], function () {
                    Route::get('/student', [ManagementUserController::class, 'list_student']);
                    Route::get('/student/{id}', [ManagementUserController::class, 'student_profile']);
                    Route::get('/teacher', [ManagementUserController::class, 'list_teacher']);
                    Route::get('/teacher/{id}', [ManagementUserController::class, 'teacher_profile']);
                });

                Route::group(['prefix' => 'enrollment'], function () {
                    Route::group(['prefix' => 'sub-class'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllSubClass']);
                        Route::post('/', [EnrollmentController::class, 'assignSubClass']);
                    });
                    Route::group(['prefix' => 'student'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllStudents']);
                        Route::post('/', [EnrollmentController::class, 'assignStudents']);
                        Route::put('/update', [EnrollmentController::class, 'updateStudents']);
                    });
                });

                Route::group(['prefix' => 'answer'], function () {
                    Route::get('/school', [AnswerController::class, 'indexTeacherSchoolExam']);
                    Route::get('/class', [AnswerController::class, 'indexTeacherClassExam']);
                    Route::get('/{id}', [AnswerController::class, 'show']);
                });

                Route::group(['prefix' => 'update-grade'], function () {
                    Route::put('/{id}', [AnswerController::class, 'update_grade_for_essay']);
                });

                Route::group(['prefix' => 'update-rekap'], function () {
                    Route::put('/{id}', [AnswerController::class, 'update_rekap_nilai']);
                });

                Route::group(['prefix' => 'notification'], function () {
                    Route::get('/', [NotificationController::class, 'index']);
                    Route::get('/{id}', [NotificationController::class, 'show']);
                });
            });
        });
    });

    Route::group(['prefix' => 'cms'], function () {

        // route yang bisa di akses guru dan admin
        Route::middleware(['auth:sanctum', 'ability:teacher'])->group(function () {

            Route::group(['prefix' => 'teacher'], function () {

                // Response
                Route::get('/{id}/response', [AnswerController::class, 'index_all_response']);

                // Analitic
                Route::get('/{id}/analytic', [AnswerController::class, 'analytic_class_exam_all_students']);

                Route::group(['prefix' => 'academic-year'], function () {
                    Route::get('/', [ManagementAcademicYearController::class, 'index']);
                    Route::get('/{id}', [ManagementAcademicYearController::class, 'show']);
                });

                Route::group(['prefix' => 'user'], function () {
                    Route::get('/student', [ManagementUserController::class, 'list_student']);
                    Route::get('/student/{id}', [ManagementUserController::class, 'student_profile']);
                    Route::get('/teacher', [ManagementUserController::class, 'list_teacher']);
                    Route::get('/teacher/{id}', [ManagementUserController::class, 'teacher_profile']);
                });

                Route::group(['prefix' => 'material-bank'], function () {
                    Route::get('/', [ManagementMaterialBankController::class, 'index']);
                    Route::get('/{id}', [ManagementMaterialBankController::class, 'show']);
                    Route::post('/', [ManagementMaterialBankController::class, 'store']);
                    Route::put('/{id}', [ManagementMaterialBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementMaterialBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment-bank'], function () {
                    Route::get('/', [ManagementAssignmentBankController::class, 'index']);
                    Route::get('/{id}', [ManagementAssignmentBankController::class, 'show']);
                    Route::post('/', [ManagementAssignmentBankController::class, 'store']);
                    Route::put('/{id}', [ManagementAssignmentBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementAssignmentBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'category-question'], function () {
                    Route::get('/', [ManagementQuestionCategoryController::class, 'index']);
                    Route::get('/{id}', [ManagementQuestionCategoryController::class, 'show']);
                    Route::post('/', [ManagementQuestionCategoryController::class, 'store']);
                    Route::put('/{id}', [ManagementQuestionCategoryController::class, 'update']);
                    Route::delete('/{id}', [ManagementQuestionCategoryController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question'], function () {
                    Route::get('/class', [QuestionController::class, 'index_teacher_by_class_exam']);
                    Route::get('/school', [QuestionController::class, 'index_teacher_by_school_exam']);
                    Route::get('/{id}', [QuestionController::class, 'show']);
                    Route::post('/', [QuestionController::class, 'store']);
                    Route::put('/{id}', [QuestionController::class, 'update']);
                    Route::delete('/{id}', [QuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question-bank'], function () {
                    Route::get('/', [ManagementBankQuestionController::class, 'index']);
                    Route::get('/{id}', [ManagementBankQuestionController::class, 'show']);
                    Route::post('/', [ManagementBankQuestionController::class, 'store']);
                    Route::put('/{id}', [ManagementBankQuestionController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementBankQuestionController::class, 'update_status']);
                    Route::delete('/{id}', [ManagementBankQuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'exam-section'], function () {
                    Route::get('/', [ExamSectionController::class, 'index']);
                    Route::get('/{id}', [ExamSectionController::class, 'show']);
                    Route::post('/', [ExamSectionController::class, 'store']);
                    Route::put('/{id}', [ExamSectionController::class, 'update']);
                    Route::delete('/{id}', [ExamSectionController::class, 'destroy']);
                });


                Route::group(['prefix' => 'class-exam'], function () {
                    Route::get('/', [ClassExamController::class, 'index']);
                    Route::get('/{id}', [ClassExamController::class, 'show']);
                    Route::post('/', [ClassExamController::class, 'store']);
                    Route::put('/{id}', [ClassExamController::class, 'update']);
                    Route::delete('/{id}', [ClassExamController::class, 'destroy']);
                    Route::put('/{id}/update-is-active', [ClassExamController::class, 'update_is_active']);
                    Route::put('/{id}/update-is-main/{response_id}', [ClassExamController::class, 'update_is_main']);
                });

                Route::group(['prefix' => 'school-exam'], function () {
                    Route::get('/', [SchoolExamController::class, 'index']);
                    Route::get('/{id}', [SchoolExamController::class, 'show']);
                    Route::put('/{id}/update-is-active', [SchoolExamController::class, 'update_is_active']);
                    Route::put('/{id}/update-is-main/{response_id}', [SchoolExamController::class, 'update_is_main']);
                    Route::put('/{id}', [SchoolExamController::class, 'update']);
                });

                Route::group(['prefix' => 'rpp'], function () {
                    Route::get('/', [ManagementRppController::class, 'index']);
                    Route::get('/{id}', [ManagementRppController::class, 'show']);
                    Route::post('/', [ManagementRppController::class, 'store']);
                    Route::put('/{id}', [ManagementRppController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-bank'], function () {
                    Route::get('/', [ManagementRppBankController::class, 'index']);
                    Route::get('/{id}', [ManagementRppBankController::class, 'show']);
                    Route::post('/', [ManagementRppBankController::class, 'store']);
                    Route::put('/{id}', [ManagementRppBankController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppBankController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-draft'], function () {
                    Route::group(['prefix' => 'rpp/{rpp_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp']);
                    });

                    Route::group(['prefix' => 'rpp-bank/{rpp_bank_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp_bank']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp_bank']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp_bank']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp_bank']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_bank_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp_bank']);
                    });
                });

                Route::group(['prefix' => 'subject'], function () {
                    Route::get('/rpp/{rpp_id}', [SubjectMatterRppController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppController::class, 'show']);
                    Route::post('/', [SubjectMatterRppController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-bank'], function () {
                    Route::get('/rpp/{rpp_bank_id}', [SubjectMatterRppBankController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppBankController::class, 'show']);
                    Route::post('/', [SubjectMatterRppBankController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppBankController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-draft'], function () {
                    Route::get('/rpp/{rpp_draft_id}', [SubjectMatterRppDraftController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppDraftController::class, 'show']);
                    Route::post('/', [SubjectMatterRppDraftController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppDraftController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppDraftController::class, 'destroy']);
                });
            });
        });

        // route yang bisa di akses staff curriculum dan admin
        Route::middleware(['auth:sanctum', 'ability:staff_curriculum'])->group(function () {
            Route::group(['prefix' => 'staff-curriculum'], function () {

                // Response
                Route::get('/{id}/response', [AnswerController::class, 'index_all_response']);

                // Analitic
                Route::get('/{id}/analytic', [AnswerController::class, 'analytic_class_exam_all_students']);

                Route::group(['prefix' => 'dashboard'], function () {
                    Route::get('/statistic', [ManagementDashboardController::class, 'getSchoolStatistics']);
                });

                Route::group(['prefix' => 'user'], function () {
                    Route::get('/', [ManagementUserController::class, 'index']);
                    Route::get('/{id}', [ManagementUserController::class, 'show']);
                    Route::post('/', [ManagementUserController::class, 'store']);
                    Route::put('/{id}', [ManagementUserController::class, 'update']);
                    Route::delete('/{id}', [ManagementUserController::class, 'destroy']);
                });

                Route::group(['prefix' => 'class'], function () {
                    Route::get('/', [ManagementClassController::class, 'index']);
                    Route::get('/{id}', [ManagementClassController::class, 'show']);
                    Route::post('/', [ManagementClassController::class, 'store']);
                    Route::put('/{id}', [ManagementClassController::class, 'update']);
                    Route::delete('/{id}', [ManagementClassController::class, 'destroy']);
                });

                Route::group(['prefix' => 'sub-class'], function () {
                    Route::get('/', [ManagementSubClass::class, 'index']);
                    Route::get('/{id}', [ManagementSubClass::class, 'show']);
                    Route::post('/', [ManagementSubClass::class, 'store']);
                    Route::put('/{id}', [ManagementSubClass::class, 'update']);
                    Route::delete('/{id}', [ManagementSubClass::class, 'destroy']);
                });

                Route::group(['prefix' => 'course'], function () {
                    Route::get('/', [ManagementCourseController::class, 'index']);
                    Route::get('/{id}', [ManagementCourseController::class, 'show']);
                    Route::post('/', [ManagementCourseController::class, 'store']);
                    Route::put('/{id}', [ManagementCourseController::class, 'update']);
                    Route::delete('/{id}', [ManagementCourseController::class, 'destroy']);
                });

                Route::group(['prefix' => 'learning'], function () {
                    Route::get('/', [LearningController::class, 'index']);
                    Route::get('/{id}', [LearningController::class, 'show']);
                    Route::post('/', [LearningController::class, 'store']);
                    Route::put('/{id}', [LearningController::class, 'update']);
                    Route::delete('/{id}', [LearningController::class, 'destroy']);
                });

                Route::group(['prefix' => 'material'], function () {
                    Route::get('/', [MaterialController::class, 'index']);
                    Route::get('/{id}', [MaterialController::class, 'show']);
                    Route::post('/', [MaterialController::class, 'store']);
                    Route::put('/{id}', [MaterialController::class, 'update']);
                    Route::delete('/{id}', [MaterialController::class, 'destroy']);
                });

                Route::group(['prefix' => 'material-bank'], function () {
                    Route::get('/', [ManagementMaterialBankController::class, 'index']);
                    Route::get('/{id}', [ManagementMaterialBankController::class, 'show']);
                    Route::post('/', [ManagementMaterialBankController::class, 'store']);
                    Route::put('/{id}', [ManagementMaterialBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementMaterialBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment'], function () {
                    Route::get('/', [AssignmentController::class, 'index']);
                    Route::get('/{id}', [AssignmentController::class, 'show']);
                    Route::post('/', [AssignmentController::class, 'store']);
                    Route::put('/{id}', [AssignmentController::class, 'update']);
                    Route::delete('/{id}', [AssignmentController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment-bank'], function () {
                    Route::get('/', [ManagementAssignmentBankController::class, 'index']);
                    Route::get('/{id}', [ManagementAssignmentBankController::class, 'show']);
                    Route::post('/', [ManagementAssignmentBankController::class, 'store']);
                    Route::put('/{id}', [ManagementAssignmentBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementAssignmentBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'submission'], function () {
                    Route::get('/', [SubmissionController::class, 'index']);
                    Route::get('/{id}', [SubmissionController::class, 'show']);
                    Route::put('/{id}', [SubmissionController::class, 'update']);
                });

                Route::group(['prefix' => 'major'], function () {
                    Route::get('/', [ManagementMajorController::class, 'index']);
                    Route::get('/{id}', [ManagementMajorController::class, 'show']);
                    Route::post('/', [ManagementMajorController::class, 'store']);
                    Route::put('/{id}', [ManagementMajorController::class, 'update']);
                    Route::delete('/{id}', [ManagementMajorController::class, 'destroy']);
                });

                Route::group(['prefix' => 'academic-year'], function () {
                    Route::get('/', [ManagementAcademicYearController::class, 'index']);
                    Route::get('/{id}', [ManagementAcademicYearController::class, 'show']);
                    Route::post('/', [ManagementAcademicYearController::class, 'store']);
                    Route::put('/{id}', [ManagementAcademicYearController::class, 'update']);
                    Route::delete('/{id}', [ManagementAcademicYearController::class, 'destroy']);
                });

                Route::group(['prefix' => 'cms'], function () {
                    Route::get('/', [CMSController::class, 'index']);
                    Route::get('/{id}', [CMSController::class, 'show']);
                    Route::post('/', [CMSController::class, 'store']);
                    Route::put('/{id}', [CMSController::class, 'update']);
                    Route::delete('/{id}', [CMSController::class, 'destroy']);
                });

                Route::group(['prefix' => 'enrollment'], function () {
                    Route::group(['prefix' => 'sub-class'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllSubClass']);
                        Route::post('/', [EnrollmentController::class, 'assignSubClass']);
                        Route::put('/{id}', [EnrollmentController::class, 'updateSubClass']);
                        Route::delete('/{id}', [EnrollmentController::class, 'deleteSubClass']);
                    });
                    Route::group(['prefix' => 'student'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllStudents']);
                        Route::post('/', [EnrollmentController::class, 'assignStudents']);
                        Route::put('/update', [EnrollmentController::class, 'updateStudents']);
                    });
                });

                Route::group(['prefix' => 'rpp'], function () {
                    Route::get('/', [ManagementRppController::class, 'index']);
                    Route::get('/{id}', [ManagementRppController::class, 'show']);
                    Route::post('/', [ManagementRppController::class, 'store']);
                    Route::put('/{id}', [ManagementRppController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-bank'], function () {
                    Route::get('/', [ManagementRppBankController::class, 'index']);
                    Route::get('/{id}', [ManagementRppBankController::class, 'show']);
                    Route::post('/', [ManagementRppBankController::class, 'store']);
                    Route::put('/{id}', [ManagementRppBankController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppBankController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-draft'], function () {
                    Route::group(['prefix' => 'rpp/{rpp_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp']);
                    });

                    Route::group(['prefix' => 'rpp-bank/{rpp_bank_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp_bank']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp_bank']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp_bank']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp_bank']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_bank_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp_bank']);
                    });
                });

                Route::group(['prefix' => 'subject'], function () {
                    Route::get('/rpp/{rpp_id}', [SubjectMatterRppController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppController::class, 'show']);
                    Route::post('/', [SubjectMatterRppController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-bank'], function () {
                    Route::get('/rpp/{rpp_bank_id}', [SubjectMatterRppBankController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppBankController::class, 'show']);
                    Route::post('/', [SubjectMatterRppBankController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppBankController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-draft'], function () {
                    Route::get('/rpp/{rpp_draft_id}', [SubjectMatterRppDraftController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppDraftController::class, 'show']);
                    Route::post('/', [SubjectMatterRppDraftController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppDraftController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppDraftController::class, 'destroy']);
                });

                Route::group(['prefix' => 'category-question'], function () {
                    Route::get('/', [ManagementQuestionCategoryController::class, 'index']);
                    Route::get('/{id}', [ManagementQuestionCategoryController::class, 'show']);
                    Route::post('/', [ManagementQuestionCategoryController::class, 'store']);
                    Route::put('/{id}', [ManagementQuestionCategoryController::class, 'update']);
                    Route::delete('/{id}', [ManagementQuestionCategoryController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question-bank'], function () {
                    Route::get('/', [ManagementBankQuestionController::class, 'index']);
                    Route::get('/{id}', [ManagementBankQuestionController::class, 'show']);
                    Route::post('/', [ManagementBankQuestionController::class, 'store']);
                    Route::put('/{id}', [ManagementBankQuestionController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementBankQuestionController::class, 'update_status']);
                    Route::delete('/{id}', [ManagementBankQuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question'], function () {
                    Route::get('/', [QuestionController::class, 'index_admin']);
                    Route::get('/{id}', [QuestionController::class, 'show']);
                    Route::post('/', [QuestionController::class, 'store']);
                    Route::put('/{id}', [QuestionController::class, 'update']);
                    Route::delete('/{id}', [QuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'exam-section'], function () {
                    Route::get('/', [ExamSectionController::class, 'index']);
                    Route::get('/{id}', [ExamSectionController::class, 'show']);
                    Route::post('/', [ExamSectionController::class, 'store']);
                    Route::put('/{id}', [ExamSectionController::class, 'update']);
                    Route::delete('/{id}', [ExamSectionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'school-exam'], function () {
                    Route::get('/', [SchoolExamController::class, 'index']);
                    Route::get('/{id}', [SchoolExamController::class, 'show']);
                    Route::post('/', [SchoolExamController::class, 'store']);
                    Route::put('/{id}/update-is-active', [SchoolExamController::class, 'update_is_active']);
                    Route::put('/{id}/update-token', [SchoolExamController::class, 'update_token']);
                    Route::put('/{id}/update-is-main/{response_id}', [SchoolExamController::class, 'update_is_main']);
                    Route::put('/{id}', [SchoolExamController::class, 'update']);
                    Route::delete('/{id}', [SchoolExamController::class, 'destroy']);
                });

                Route::group(['prefix' => 'register-exam'], function () {
                    Route::get('/teacher/{id}', [SchoolExamController::class, 'getDataRegisteredTeachers']);
                    Route::get('/student/{id}', [SchoolExamController::class, 'getDataRegisteredStudents']);
                    Route::post('/teacher/register', [SchoolExamController::class, 'registerTeacherToExam']);
                    Route::post('/student/register', [SchoolExamController::class, 'registerStudentsToExam']);
                    Route::post('/teacher/unregister', [SchoolExamController::class, 'unregisterTeacherFromExam']);
                    Route::post('/student/unregister', [SchoolExamController::class, 'unregisterStudentsFromExam']);
                });

                Route::group(['prefix' => 'guardian'], function () {
                    Route::get('/', [ManagementWaliKelasController::class, 'index']);
                    Route::get('/{id}', [ManagementWaliKelasController::class, 'show']);
                    Route::put('/{id}', [ManagementWaliKelasController::class, 'updateIsWali']);
                });
            });
        });

        // route yang bisa di akses staff admin dan admin
        Route::middleware(['auth:sanctum', 'ability:staff_administrator'])->group(function () {
            Route::group(['prefix' => 'staff-administrator'], function () {
                Route::group(['prefix' => 'dashboard'], function () {
                    Route::get('/statistic', [ManagementDashboardController::class, 'getSchoolStatistics']);
                });

                Route::group(['prefix' => 'user'], function () {
                    Route::get('/', [ManagementUserController::class, 'index']);
                    Route::get('/{id}', [ManagementUserController::class, 'show']);
                    Route::post('/', [ManagementUserController::class, 'store']);
                    Route::put('/{id}', [ManagementUserController::class, 'update']);
                    Route::delete('/{id}', [ManagementUserController::class, 'destroy']);
                });

                Route::group(['prefix' => 'class'], function () {
                    Route::get('/', [ManagementClassController::class, 'index']);
                    Route::get('/{id}', [ManagementClassController::class, 'show']);
                    Route::post('/', [ManagementClassController::class, 'store']);
                    Route::put('/{id}', [ManagementClassController::class, 'update']);
                    Route::delete('/{id}', [ManagementClassController::class, 'destroy']);
                });

                Route::group(['prefix' => 'sub-class'], function () {
                    Route::get('/', [ManagementSubClass::class, 'index']);
                    Route::get('/{id}', [ManagementSubClass::class, 'show']);
                    Route::post('/', [ManagementSubClass::class, 'store']);
                    Route::put('/{id}', [ManagementSubClass::class, 'update']);
                    Route::delete('/{id}', [ManagementSubClass::class, 'destroy']);
                });

                Route::group(['prefix' => 'course'], function () {
                    Route::get('/', [ManagementCourseController::class, 'index']);
                    Route::get('/{id}', [ManagementCourseController::class, 'show']);
                    Route::post('/', [ManagementCourseController::class, 'store']);
                    Route::put('/{id}', [ManagementCourseController::class, 'update']);
                    Route::delete('/{id}', [ManagementCourseController::class, 'destroy']);
                });

                Route::group(['prefix' => 'learning'], function () {
                    Route::get('/', [LearningController::class, 'index']);
                    Route::get('/{id}', [LearningController::class, 'show']);
                    Route::post('/', [LearningController::class, 'store']);
                    Route::put('/{id}', [LearningController::class, 'update']);
                    Route::delete('/{id}', [LearningController::class, 'destroy']);
                });

                Route::group(['prefix' => 'material'], function () {
                    Route::get('/', [MaterialController::class, 'index']);
                    Route::get('/{id}', [MaterialController::class, 'show']);
                    Route::post('/', [MaterialController::class, 'store']);
                    Route::put('/{id}', [MaterialController::class, 'update']);
                    Route::delete('/{id}', [MaterialController::class, 'destroy']);
                });

                Route::group(['prefix' => 'material-bank'], function () {
                    Route::get('/', [ManagementMaterialBankController::class, 'index']);
                    Route::get('/{id}', [ManagementMaterialBankController::class, 'show']);
                    Route::post('/', [ManagementMaterialBankController::class, 'store']);
                    Route::put('/{id}', [ManagementMaterialBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementMaterialBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment'], function () {
                    Route::get('/', [AssignmentController::class, 'index']);
                    Route::get('/{id}', [AssignmentController::class, 'show']);
                    Route::post('/', [AssignmentController::class, 'store']);
                    Route::put('/{id}', [AssignmentController::class, 'update']);
                    Route::delete('/{id}', [AssignmentController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment-bank'], function () {
                    Route::get('/', [ManagementAssignmentBankController::class, 'index']);
                    Route::get('/{id}', [ManagementAssignmentBankController::class, 'show']);
                    Route::post('/', [ManagementAssignmentBankController::class, 'store']);
                    Route::put('/{id}', [ManagementAssignmentBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementAssignmentBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'submission'], function () {
                    Route::get('/', [SubmissionController::class, 'index']);
                    Route::get('/{id}', [SubmissionController::class, 'show']);
                    Route::put('/{id}', [SubmissionController::class, 'update']);
                });

                Route::group(['prefix' => 'major'], function () {
                    Route::get('/', [ManagementMajorController::class, 'index']);
                    Route::get('/{id}', [ManagementMajorController::class, 'show']);
                    Route::post('/', [ManagementMajorController::class, 'store']);
                    Route::put('/{id}', [ManagementMajorController::class, 'update']);
                    Route::delete('/{id}', [ManagementMajorController::class, 'destroy']);
                });

                Route::group(['prefix' => 'academic-year'], function () {
                    Route::get('/', [ManagementAcademicYearController::class, 'index']);
                    Route::get('/{id}', [ManagementAcademicYearController::class, 'show']);
                    Route::post('/', [ManagementAcademicYearController::class, 'store']);
                    Route::put('/{id}', [ManagementAcademicYearController::class, 'update']);
                    Route::delete('/{id}', [ManagementAcademicYearController::class, 'destroy']);
                });

                Route::group(['prefix' => 'cms'], function () {
                    Route::get('/', [CMSController::class, 'index']);
                    Route::get('/{id}', [CMSController::class, 'show']);
                    Route::post('/', [CMSController::class, 'store']);
                    Route::put('/{id}', [CMSController::class, 'update']);
                    Route::delete('/{id}', [CMSController::class, 'destroy']);
                });

                Route::group(['prefix' => 'enrollment'], function () {
                    Route::group(['prefix' => 'sub-class'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllSubClass']);
                        Route::post('/', [EnrollmentController::class, 'assignSubClass']);
                        Route::put('/{id}', [EnrollmentController::class, 'updateSubClass']);
                        Route::delete('/{id}', [EnrollmentController::class, 'deleteSubClass']);
                    });
                    Route::group(['prefix' => 'student'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllStudents']);
                        Route::post('/', [EnrollmentController::class, 'assignStudents']);
                        Route::put('/update', [EnrollmentController::class, 'updateStudents']);
                    });
                });

                Route::group(['prefix' => 'rpp'], function () {
                    Route::get('/', [ManagementRppController::class, 'index']);
                    Route::get('/{id}', [ManagementRppController::class, 'show']);
                    Route::post('/', [ManagementRppController::class, 'store']);
                    Route::put('/{id}', [ManagementRppController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-bank'], function () {
                    Route::get('/', [ManagementRppBankController::class, 'index']);
                    Route::get('/{id}', [ManagementRppBankController::class, 'show']);
                    Route::post('/', [ManagementRppBankController::class, 'store']);
                    Route::put('/{id}', [ManagementRppBankController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppBankController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-draft'], function () {
                    Route::group(['prefix' => 'rpp/{rpp_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp']);
                    });

                    Route::group(['prefix' => 'rpp-bank/{rpp_bank_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp_bank']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp_bank']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp_bank']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp_bank']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_bank_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp_bank']);
                    });
                });

                Route::group(['prefix' => 'subject'], function () {
                    Route::get('/rpp/{rpp_id}', [SubjectMatterRppController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppController::class, 'show']);
                    Route::post('/', [SubjectMatterRppController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-bank'], function () {
                    Route::get('/rpp/{rpp_bank_id}', [SubjectMatterRppBankController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppBankController::class, 'show']);
                    Route::post('/', [SubjectMatterRppBankController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppBankController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-draft'], function () {
                    Route::get('/rpp/{rpp_draft_id}', [SubjectMatterRppDraftController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppDraftController::class, 'show']);
                    Route::post('/', [SubjectMatterRppDraftController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppDraftController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppDraftController::class, 'destroy']);
                });

                Route::group(['prefix' => 'school-exam'], function () {
                    Route::put('/{id}/update-token', [SchoolExamController::class, 'update_token']);
                });

                Route::group(['prefix' => 'category-question'], function () {
                    Route::get('/', [ManagementQuestionCategoryController::class, 'index']);
                    Route::get('/{id}', [ManagementQuestionCategoryController::class, 'show']);
                    Route::post('/', [ManagementQuestionCategoryController::class, 'store']);
                    Route::put('/{id}', [ManagementQuestionCategoryController::class, 'update']);
                    Route::delete('/{id}', [ManagementQuestionCategoryController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question-bank'], function () {
                    Route::get('/', [ManagementBankQuestionController::class, 'index']);
                    Route::get('/{id}', [ManagementBankQuestionController::class, 'show']);
                    Route::post('/', [ManagementBankQuestionController::class, 'store']);
                    Route::put('/{id}', [ManagementBankQuestionController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementBankQuestionController::class, 'update_status']);
                    Route::delete('/{id}', [ManagementBankQuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question'], function () {
                    Route::get('/', [QuestionController::class, 'index_admin']);
                    Route::get('/{id}', [QuestionController::class, 'show']);
                    Route::post('/', [QuestionController::class, 'store']);
                    Route::put('/{id}', [QuestionController::class, 'update']);
                    Route::delete('/{id}', [QuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'guardian'], function () {
                    Route::get('/', [ManagementWaliKelasController::class, 'index']);
                    Route::get('/{id}', [ManagementWaliKelasController::class, 'show']);
                    Route::put('/{id}', [ManagementWaliKelasController::class, 'updateIsWali']);
                });
            });
        });

        // route yang hanya dapat di akses oleh admin
        Route::middleware(['auth:sanctum', 'ability:admin'])->group(function () {

            Route::group(['prefix' => 'admin'], function () {

                Route::group(['prefix' => 'user'], function () {
                    Route::get('/', [ManagementUserController::class, 'index']);
                    Route::get('/{id}', [ManagementUserController::class, 'show']);
                    Route::post('/', [ManagementUserController::class, 'store']);
                    Route::put('/{id}', [ManagementUserController::class, 'update']);
                    Route::delete('/{id}', [ManagementUserController::class, 'destroy']);
                });

                Route::group(['prefix' => 'class'], function () {
                    Route::get('/', [ManagementClassController::class, 'index']);
                    Route::get('/{id}', [ManagementClassController::class, 'show']);
                    Route::post('/', [ManagementClassController::class, 'store']);
                    Route::put('/{id}', [ManagementClassController::class, 'update']);
                    Route::delete('/{id}', [ManagementClassController::class, 'destroy']);
                });

                Route::group(['prefix' => 'sub-class'], function () {
                    Route::get('/', [ManagementSubClass::class, 'index']);
                    Route::get('/{id}', [ManagementSubClass::class, 'show']);
                    Route::post('/', [ManagementSubClass::class, 'store']);
                    Route::put('/{id}', [ManagementSubClass::class, 'update']);
                    Route::delete('/{id}', [ManagementSubClass::class, 'destroy']);
                });

                Route::group(['prefix' => 'course'], function () {
                    Route::get('/', [ManagementCourseController::class, 'index']);
                    Route::get('/{id}', [ManagementCourseController::class, 'show']);
                    Route::post('/', [ManagementCourseController::class, 'store']);
                    Route::put('/{id}', [ManagementCourseController::class, 'update']);
                    Route::delete('/{id}', [ManagementCourseController::class, 'destroy']);
                });

                Route::group(['prefix' => 'learning'], function () {
                    Route::get('/', [LearningController::class, 'index']);
                    Route::get('/{id}', [LearningController::class, 'show']);
                    Route::post('/', [LearningController::class, 'store']);
                    Route::put('/{id}', [LearningController::class, 'update']);
                    Route::delete('/{id}', [LearningController::class, 'destroy']);
                });

                Route::group(['prefix' => 'material'], function () {
                    Route::get('/', [MaterialController::class, 'index']);
                    Route::get('/{id}', [MaterialController::class, 'show']);
                    Route::post('/', [MaterialController::class, 'store']);
                    Route::put('/{id}', [MaterialController::class, 'update']);
                    Route::delete('/{id}', [MaterialController::class, 'destroy']);
                });

                Route::group(['prefix' => 'material-bank'], function () {
                    Route::get('/', [ManagementMaterialBankController::class, 'index']);
                    Route::get('/{id}', [ManagementMaterialBankController::class, 'show']);
                    Route::post('/', [ManagementMaterialBankController::class, 'store']);
                    Route::put('/{id}', [ManagementMaterialBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementMaterialBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment'], function () {
                    Route::get('/', [AssignmentController::class, 'index']);
                    Route::get('/{id}', [AssignmentController::class, 'show']);
                    Route::post('/', [AssignmentController::class, 'store']);
                    Route::put('/{id}', [AssignmentController::class, 'update']);
                    Route::delete('/{id}', [AssignmentController::class, 'destroy']);
                });

                Route::group(['prefix' => 'assignment-bank'], function () {
                    Route::get('/', [ManagementAssignmentBankController::class, 'index']);
                    Route::get('/{id}', [ManagementAssignmentBankController::class, 'show']);
                    Route::post('/', [ManagementAssignmentBankController::class, 'store']);
                    Route::put('/{id}', [ManagementAssignmentBankController::class, 'update']);
                    Route::delete('/{id}', [ManagementAssignmentBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'submission'], function () {
                    Route::get('/', [SubmissionController::class, 'index']);
                    Route::get('/{id}', [SubmissionController::class, 'show']);
                    Route::put('/{id}', [SubmissionController::class, 'update']);
                });

                Route::group(['prefix' => 'dashboard'], function () {
                    Route::get('/statistic', [ManagementDashboardController::class, 'getSchoolStatistics']);
                });

                Route::group(['prefix' => 'major'], function () {
                    Route::get('/', [ManagementMajorController::class, 'index']);
                    Route::get('/{id}', [ManagementMajorController::class, 'show']);
                    Route::post('/', [ManagementMajorController::class, 'store']);
                    Route::put('/{id}', [ManagementMajorController::class, 'update']);
                    Route::delete('/{id}', [ManagementMajorController::class, 'destroy']);
                });

                Route::group(['prefix' => 'academic-year'], function () {
                    Route::get('/', [ManagementAcademicYearController::class, 'index']);
                    Route::get('/{id}', [ManagementAcademicYearController::class, 'show']);
                    Route::post('/', [ManagementAcademicYearController::class, 'store']);
                    Route::put('/{id}', [ManagementAcademicYearController::class, 'update']);
                    Route::delete('/{id}', [ManagementAcademicYearController::class, 'destroy']);
                });

                Route::group(['prefix' => 'cms'], function () {
                    Route::get('/', [CMSController::class, 'index']);
                    Route::get('/{id}', [CMSController::class, 'show']);
                    Route::post('/', [CMSController::class, 'store']);
                    Route::put('/{id}', [CMSController::class, 'update']);
                    Route::delete('/{id}', [CMSController::class, 'destroy']);
                });

                Route::group(['prefix' => 'enrollment'], function () {
                    Route::group(['prefix' => 'sub-class'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllSubClass']);
                        Route::post('/', [EnrollmentController::class, 'assignSubClass']);
                        Route::put('/{id}', [EnrollmentController::class, 'updateSubClass']);
                        Route::delete('/{id}', [EnrollmentController::class, 'deleteSubClass']);
                    });
                    Route::group(['prefix' => 'student'], function () {
                        Route::get('/', [EnrollmentController::class, 'getAllStudents']);
                        Route::post('/', [EnrollmentController::class, 'assignStudents']);
                        Route::put('/update', [EnrollmentController::class, 'updateStudents']);
                    });
                });

                Route::group(['prefix' => 'rpp'], function () {
                    Route::get('/', [ManagementRppController::class, 'index']);
                    Route::get('/{id}', [ManagementRppController::class, 'show']);
                    Route::post('/', [ManagementRppController::class, 'store']);
                    Route::put('/{id}', [ManagementRppController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-bank'], function () {
                    Route::get('/', [ManagementRppBankController::class, 'index']);
                    Route::get('/{id}', [ManagementRppBankController::class, 'show']);
                    Route::post('/', [ManagementRppBankController::class, 'store']);
                    Route::put('/{id}', [ManagementRppBankController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementRppBankController::class, 'updateStatus']);
                    Route::delete('/{id}', [ManagementRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'rpp-draft'], function () {
                    Route::group(['prefix' => 'rpp/{rpp_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp']);
                    });

                    Route::group(['prefix' => 'rpp-bank/{rpp_bank_id}'], function () {
                        Route::get('/', [ManagementRppDraftController::class, 'index_rpp_bank']);
                        Route::get('/{draft_id}', [ManagementRppDraftController::class, 'detail_rpp_bank']);
                        Route::post('/', [ManagementRppDraftController::class, 'store_rpp_bank']);
                        Route::put('/{draft_id}', [ManagementRppDraftController::class, 'update_rpp_bank']);
                        Route::put('/{draft_id}/update-status', [ManagementRppDraftController::class, 'update_rpp_bank_status']);
                        Route::delete('/{draft_id}', [ManagementRppDraftController::class, 'destroy_rpp_bank']);
                    });
                });

                Route::group(['prefix' => 'subject'], function () {
                    Route::get('/rpp/{rpp_id}', [SubjectMatterRppController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppController::class, 'show']);
                    Route::post('/', [SubjectMatterRppController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-bank'], function () {
                    Route::get('/rpp/{rpp_bank_id}', [SubjectMatterRppBankController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppBankController::class, 'show']);
                    Route::post('/', [SubjectMatterRppBankController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppBankController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppBankController::class, 'destroy']);
                });

                Route::group(['prefix' => 'subject-draft'], function () {
                    Route::get('/rpp/{rpp_draft_id}', [SubjectMatterRppDraftController::class, 'index']);
                    Route::get('/{id}', [SubjectMatterRppDraftController::class, 'show']);
                    Route::post('/', [SubjectMatterRppDraftController::class, 'store']);
                    Route::put('/{id}', [SubjectMatterRppDraftController::class, 'update']);
                    Route::delete('/{id}', [SubjectMatterRppDraftController::class, 'destroy']);
                });

                Route::group(['prefix' => 'category-question'], function () {
                    Route::get('/', [ManagementQuestionCategoryController::class, 'index']);
                    Route::get('/{id}', [ManagementQuestionCategoryController::class, 'show']);
                    Route::post('/', [ManagementQuestionCategoryController::class, 'store']);
                    Route::put('/{id}', [ManagementQuestionCategoryController::class, 'update']);
                    Route::delete('/{id}', [ManagementQuestionCategoryController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question-bank'], function () {
                    Route::get('/', [ManagementBankQuestionController::class, 'index']);
                    Route::get('/{id}', [ManagementBankQuestionController::class, 'show']);
                    Route::post('/', [ManagementBankQuestionController::class, 'store']);
                    Route::put('/{id}', [ManagementBankQuestionController::class, 'update']);
                    Route::put('/{id}/update-status', [ManagementBankQuestionController::class, 'update_status']);
                    Route::delete('/{id}', [ManagementBankQuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'question'], function () {
                    Route::get('/', [QuestionController::class, 'index_admin']);
                    Route::get('/{id}', [QuestionController::class, 'show']);
                    Route::post('/', [QuestionController::class, 'store']);
                    Route::put('/{id}', [QuestionController::class, 'update']);
                    Route::delete('/{id}', [QuestionController::class, 'destroy']);
                });

                Route::group(['prefix' => 'guardian'], function () {
                    Route::get('/', [ManagementWaliKelasController::class, 'index']);
                    Route::get('/{id}', [ManagementWaliKelasController::class, 'show']);
                    Route::put('/{id}', [ManagementWaliKelasController::class, 'updateIsWali']);
                });

                Route::group(['prefix' => 'premium'], function () {
                    Route::get('/{id}/make', [PremiumController::class, 'makePremium']);
                    Route::get('/{id}/revoke', [PremiumController::class, 'revokePremium']);
                });
            });
        });
    });
});
