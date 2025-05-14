<?php

namespace App\Console\Commands;

use App\Models\School;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SyncData extends Command
{
    protected $signature = 'presensi:sync-data';

    protected $description = 'Sync data periodically';

    public function __construct()
    {
        parent::__construct();
    }

    function generateUniqueId($table, $prefix, $length)
    {
        $latestId = DB::table($table)
            ->where('id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('id');

        if ($latestId) {
            $lastNumberPart = substr($latestId, strlen($prefix));
            $lastNumber = intval(strtok($lastNumberPart, '/'));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, $length - strlen($prefix), '0', STR_PAD_LEFT);
    }

    private function getUniqueId($table, $prefix, $length)
    {
        $newId = $this->generateUniqueId($table, $prefix, $length);

        while (DB::table($table)->where('id', $newId)->exists()) {
            $newId = $this->generateUniqueId($table, $prefix, $length);
        }

        return $newId;
    }


    public function handle()
    {
        $presensiBaseUrl = 'https://be-absen.bonbon-tech.com/api/v1';
        $presensiLoginEndpoint = $presensiBaseUrl . '/cms/auth/login';
        $presensiEmail = 'admin@gmail.com';
        $presensiPassword = 'secretpass';
        $userListEndpointAdmin = $presensiBaseUrl . '/cms/admin/profile';
        $userListEndpointStudent = $presensiBaseUrl . '/cms/user/student';
        $userListEndpointTeacher = $presensiBaseUrl . '/cms/user/teacher';
        $userListEndpointStaff = $presensiBaseUrl . '/cms/user/staff';
        $userListEndpointSubclass = $presensiBaseUrl . '/cms/sub-class';
        $userListEndpointClass = $presensiBaseUrl . '/cms/class';

        if (!Cache::has('presensi_token')) {
            $this->login($presensiLoginEndpoint, $presensiEmail, $presensiPassword);
        }

        $this->syncAdminData($userListEndpointAdmin);
        $this->syncData('class', $userListEndpointClass);
        $this->syncData('subclass', $userListEndpointSubclass);
        $this->syncData('student', $userListEndpointStudent);
        $this->syncData('teacher', $userListEndpointTeacher);
        $this->syncData('staff', $userListEndpointStaff);

        $this->info('Data synchronization completed.');
    }

    private function login($loginEndpoint, $email, $password)
    {
        $response = Http::post($loginEndpoint, [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $token = $responseData['data']['token'];
            Cache::put('presensi_token', $token, now()->addHours(1));
            $this->info('Admin logged in successfully.');
        } else {
            $this->error('Failed to login.');
        }
    }

    private function syncAdminData($endpoint)
    {
        $token = Cache::get('presensi_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get($endpoint);

        if ($response->successful()) {
            $data = $response->json()['data'];

            $this->insertAdmin($data);

            $this->info('Admin data synchronized successfully.');
        } else {
            $this->error('Failed to synchronize admin data.');
        }
    }

    private function syncData($type, $endpoint)
    {
        $token = Cache::get('presensi_token');
        $currentPage = 1;
        $perPage = 10;
        $lastPage = 1;

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($endpoint, [
                'page' => $currentPage,
                'per_page' => $perPage,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'];

                switch ($type) {
                    case 'student':
                        $this->insertStudent($data);
                        break;
                    case 'teacher':
                        $this->insertTeacher($data);
                        break;
                    case 'staff':
                        $this->insertStaff($data);
                        break;
                    case 'subclass':
                        $this->insertSubclass($data);
                        break;
                    case 'class':
                        $this->insertClass($data);
                        break;
                }

                $this->info(ucfirst($type) . ' data from page ' . $currentPage . ' synchronized successfully.');

                if (isset($data['last_page'])) {
                    $lastPage = $data['last_page'];
                } else if (isset($data['total_pages'])) {
                    $lastPage = $data['total_pages'];
                } else {
                    $this->error('Could not determine last page.');
                    break;
                }

                $currentPage++;
            } else {
                $this->error('Failed to synchronize ' . $type . ' data from page ' . $currentPage . '.');
                break;
            }
        } while ($currentPage <= $lastPage);
    }

    private function insertStudent($data)
    {
        try {
            foreach ($data['list'] as $item) {
                $schoolIds = School::pluck('id')->first();
                $actualSubclassId = $item['student']['sub_class_id'];
                $validSubclassId = DB::table('sub_class')->where('id', 'like', '%/' . $actualSubclassId)->value('id');

                $user = DB::table('users')->where('email', $item['email'])->first();

                if ($user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'school_id' => $schoolIds,
                        'fullname' => $item['fullname'],
                        'password' => $this->getPassword($item['id']),
                        'phone' => $item['phone'],
                        'religion' => $item['religion'],
                        'address' => $item['address'],
                        'role' => $item['role'],
                        'image_path' => $item['image_path'],
                    ]);
                    $userId = $user->id;
                } else {
                    $newUserId = $this->getUniqueId('users', 'USE-', 16);
                    DB::table('users')->insert([
                        'id' => $newUserId,
                        'school_id' => $schoolIds,
                        'email' => $item['email'],
                        'password' => $this->getPassword($item['id']),
                        'fullname' => $item['fullname'],
                        'phone' => $item['phone'],
                        'religion' => $item['religion'],
                        'address' => $item['address'],
                        'role' => $item['role'],
                        'image_path' => $item['image_path'],
                    ]);
                    $userId = $newUserId;
                }

                $student = DB::table('students')->where('user_id', $userId)->first();

                if ($student) {
                    DB::table('students')->where('id', $student->id)->update([
                        'sub_class_id' => $validSubclassId,
                        'nisn' => $item['student']['nisn'],
                        'major' => $item['student']['major'],
                        'type' => $item['student']['type'],
                        'year' => $item['student']['year'],
                    ]);
                } else {
                    $newStudentId = $this->getUniqueId('students', 'STU-', 16);
                    DB::table('students')->insert([
                        'id' => $newStudentId . '/' . $item['id'],
                        'user_id' => $userId,
                        'sub_class_id' => $validSubclassId,
                        'nisn' => $item['student']['nisn'],
                        'major' => $item['student']['major'],
                        'type' => $item['student']['type'],
                        'year' => $item['student']['year'],
                    ]);
                }
            }

            $this->info('Student data inserted or updated successfully.');
        } catch (\Exception $e) {
            $this->error('Error inserting or updating student data: ' . $e->getMessage());
        }
    }

    private function insertTeacher($data)
    {
        try {
            foreach ($data['list'] as $item) {
                $schoolIds = School::pluck('id')->first();

                $user = DB::table('users')->where('email', $item['email'])->first();

                if ($user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'school_id' => $schoolIds,
                        'fullname' => $item['fullname'],
                        'password' => $this->getPassword($item['id']),
                        'phone' => $item['phone'],
                        'religion' => $item['religion'],
                        'address' => $item['address'],
                        'role' => $item['role'],
                        'image_path' => $item['image_path'],
                    ]);
                    $userId = $user->id;
                } else {
                    $newUserId = $this->getUniqueId('users', 'USE-', 16);
                    DB::table('users')->insert([
                        'id' => $newUserId,
                        'school_id' => $schoolIds,
                        'email' => $item['email'],
                        'password' => $this->getPassword($item['id']),
                        'fullname' => $item['fullname'],
                        'phone' => $item['phone'],
                        'religion' => $item['religion'],
                        'address' => $item['address'],
                        'role' => $item['role'],
                        'image_path' => $item['image_path'],
                    ]);
                    $userId = $newUserId;
                }

                $teacher = DB::table('teachers')->where('user_id', $userId)->first();

                if ($teacher) {
                    DB::table('teachers')->where('id', $teacher->id)->update([
                        'nip' => $item['teacher']['nip'],
                        'is_wali' => false,
                    ]);
                    $teacherId = $teacher->id;
                } else {
                    $newTeacherId = $this->getUniqueId('teachers', 'TEA-', 16);
                    DB::table('teachers')->insert([
                        'id' => $newTeacherId . '/' . $item['id'],
                        'user_id' => $userId,
                        'nip' => $item['teacher']['nip'],
                        'is_wali' => false,
                    ]);
                    $teacherId = $newTeacherId . '/' . $item['id'];
                }

                $courseName = $item['teacher']['course'];
                $course = DB::table('courses')->where('courses_title', $courseName)->first();

                if ($course) {
                    $courseId = $course->id;
                } else {
                    $newCourseId = $this->getUniqueId('courses', 'COU-', 16);
                    DB::table('courses')->insert([
                        'id' => $newCourseId,
                        'courses_title' => $courseName,
                        'created_by' => $schoolIds,
                    ]);
                    $courseId = $newCourseId;
                }

                $newTeacherSubclassId = $this->getUniqueId('teacher_sub_class', 'TSC-', 16);
                $actualSubclassId = $item['teacher']['sub_class_id'];
                $validSubclassId = DB::table('sub_class')->where('id', 'like', '%/' . $actualSubclassId)->value('id');

                if (!$validSubclassId) {
                    $this->info('Subclass ID not found for teacher: ' . $item['id']);
                    continue;
                }

                $existingTeacherSubClass = DB::table('teacher_sub_class')
                    ->where('teacher_id', $teacherId)
                    ->where('course', $courseId)
                    ->where('sub_class_id', $validSubclassId)
                    ->first();

                if (!$existingTeacherSubClass) {
                    DB::table('teacher_sub_class')->insert([
                        'id' => $newTeacherSubclassId,
                        'teacher_id' => $teacherId,
                        'sub_class_id' => $validSubclassId,
                        'course' => $courseId,
                    ]);
                }

                $newLearningId = $this->getUniqueId('learnings', 'LEA-', 16);
                $existingLearning = DB::table('learnings')
                    ->where('teacher_id', $teacherId)
                    ->where('course', $courseId)
                    ->first();

                if (!$existingLearning) {
                    DB::table('learnings')->insert([
                        'id' => $newLearningId,
                        'teacher_id' => $teacherId,
                        'course' => $courseId,
                        'status' => 'Active',
                    ]);
                } else {
                    DB::table('learnings')->where('id', $existingLearning->id)->update([
                        'status' => 'Active',
                        'course' => $courseId,
                        'teacher_id' => $teacherId,
                    ]);
                }

                $newCourseTeacher = $this->getUniqueId('course_teacher', 'CT-', 16);
                $existingCourseTeacher = DB::table('course_teacher')
                    ->where('teacher_id', $teacherId)
                    ->where('course_id', $courseId)
                    ->first();

                if (!$existingCourseTeacher) {
                    DB::table('course_teacher')->insert([
                        'id' => $newCourseTeacher,
                        'teacher_id' => $teacherId,
                        'course_id' => $courseId,
                        'status' => 'Active',
                    ]);
                }

                $this->info('Teacher data inserted or updated successfully.');
            }
        } catch (\Exception $e) {
            $this->error('Error inserting or updating teacher data: ' . $e->getMessage());
        }
    }

    private function insertStaff($data)
    {
        try {
            foreach ($data['list'] as $item) {
                $schoolIds = School::pluck('id')->first();

                $user = DB::table('users')->where('email', $item['email'])->first();

                if ($user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'school_id' => $schoolIds,
                        'fullname' => $item['fullname'],
                        'password' => $this->getPassword($item['id']),
                        'phone' => $item['phone'],
                        'religion' => $item['religion'],
                        'address' => $item['address'],
                        'role' => $item['role'],
                        'image_path' => $item['image_path'],
                    ]);
                    $userId = $user->id;
                } else {
                    $generateIdUser = IdGenerator::generate(['table' => 'users', 'length' => 16, 'prefix' => 'USE-']);
                    DB::table('users')->insert([
                        'id' => $generateIdUser,
                        'school_id' => $schoolIds,
                        'email' => $item['email'],
                        'password' => $this->getPassword($item['id']),
                        'fullname' => $item['fullname'],
                        'phone' => $item['phone'],
                        'religion' => $item['religion'],
                        'address' => $item['address'],
                        'role' => $item['role'],
                        'image_path' => $item['image_path'],
                    ]);
                    $userId = $generateIdUser;
                }

                $staff = DB::table('staffs')->where('user_id', $userId)->first();

                if ($staff) {
                    DB::table('staffs')->where('id', $staff->id)->update([
                        'nip' => $item['staff']['nip'],
                        'authority' => 'ADMIN',
                        'placement' => $item['staff']['placement'],
                    ]);
                } else {
                    $newStaffId = $this->getUniqueId('staffs', 'STA-', 16);
                    DB::table('staffs')->insert([
                        'id' => $newStaffId . '/' . $item['id'],
                        'user_id' => $userId,
                        'nip' => $item['staff']['nip'],
                        'authority' => 'ADMIN',
                        'placement' => $item['staff']['placement'],
                    ]);
                }
            }

            $this->info('Staff data inserted or updated successfully.');
        } catch (\Exception $e) {
            $this->error('Error inserting or updating staff data: ' . $e->getMessage());
        }
    }

    private function insertSubclass($data)
    {
        try {
            foreach ($data['data'] as $item) {
                $schoolIds = School::pluck('id')->first();

                $actualClassId = $item['class']['id'];
                $validClassId = DB::table('class')->where('id', 'like', '%/' . $actualClassId)->value('id');

                $actualSubclassId = $item['id'];
                $validSubclassId = DB::table('sub_class')->where('id', 'like', '%/' . $actualSubclassId)->where('class_id', $validClassId)->value('id');

                if ($validSubclassId) {
                    DB::table('sub_class')->where('id', $validSubclassId)->update([
                        'name' => $item['name'],
                    ]);
                } else {
                    $newSubclassId = $this->getUniqueId('sub_class', 'SUB-', 16);
                    DB::table('sub_class')->insert([
                        'id' => $newSubclassId . '/' . $item['id'],
                        'class_id' => $validClassId,
                        'name' => $item['name'],
                        'school_id' => $schoolIds,
                    ]);
                }
            }

            $this->info('Subclass data inserted or updated successfully.');
        } catch (\Exception $e) {
            $this->error('Error inserting or updating subclass data: ' . $e->getMessage());
        }
    }

    private function insertClass($data)
    {
        try {
            foreach ($data['data'] as $item) {
                $actualClassId = $item['id'];

                $class = DB::table('class')
                    ->where('id', 'like', '%/' . $actualClassId)
                    ->first();

                if ($class) {
                    DB::table('class')->where('id', $class->id)->update([
                        'name' => $item['name'],
                    ]);
                } else {
                    $newClassId = $this->getUniqueId('class', 'CLA-', 16);
                    DB::table('class')->insert([
                        'id' => $newClassId . '/' . $actualClassId,
                        'name' => $item['name'],
                    ]);
                }
            }

            $this->info('Class data inserted or updated successfully.');
        } catch (\Exception $e) {
            $this->error('Error inserting or updating class data: ' . $e->getMessage());
        }
    }

    private function insertAdmin($data)
    {
        try {
            $admin = DB::table('schools')->where('admin_email', $data['email'])->first();

            if ($admin) {
                DB::table('schools')->where('id', $admin->id)->update([
                    'admin_name' => $data['fullname'],
                    'admin_phone' => $data['phone'],
                    'admin_address' => $data['address'],
                ]);
            } else {
                $newAdminId = $this->getUniqueId('schools', 'SCH-', 16);
                DB::table('schools')->insert([
                    'id' => $newAdminId . '/' . $data['id'],
                    'admin_email' => $data['email'],
                    'admin_password' => $data['password'] ?? bcrypt('secretpass'),
                    'admin_name' => $data['fullname'],
                    'admin_phone' => $data['phone'],
                    'admin_address' => $data['address'],
                    'logo' => 'http://example.com/logo3.png',
                    'school_image' => 'http://example.com/school3.png',
                    'structure' => 'http://example.com/structure3.png',
                    'phone_number' => '1122334455',
                    'email' => 'school3@example.com',
                    'website' => 'http://example.com',
                    'name' => 'School Three',
                    'another_name' => 'Three School',
                    'type' => 'Public',
                    'status' => 'Operational',
                    'acreditation' => 'C',
                    'vision' => 'Fostering academic excellence.',
                    'mission' => 'Empower students to achieve their best.',
                    'description' => 'An innovative public school.',
                    'country' => 'Country Three',
                    'province' => 'Province Three',
                    'city' => 'City Three',
                    'district' => 'District Three',
                    'neighborhood' => 'Neighborhood Three',
                    'rw' => '03',
                    'latitude' => '51.507351',
                    'longitude' => '-0.127758',
                    'address' => '789 School St.',
                    'pos' => 67890,
                ]);
            }

            $this->info('Admin data inserted or updated successfully.');
        } catch (\Exception $e) {
            $this->error('Error inserting or updating admin data: ' . $e->getMessage());
        }
    }

    private function getPassword($idUser)
    {
        $presensiBaseUrl = 'https://be-absen.bonbon-tech.com/api/v1';
        $userDetailEndpoint = $presensiBaseUrl . '/cms/user/' . $idUser;
        $token = Cache::get('presensi_token');
        $retryCount = 0;
        $maxRetries = 10;

        while ($retryCount < $maxRetries) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($userDetailEndpoint);

            if ($response->successful()) {
                $data = $response->json()['data'];
                return $data['password'] ?? bcrypt('password');
            } elseif ($response->status() == 429) {
                $this->error('Rate limit exceeded. Retrying...');
                $retryCount++;
                sleep(5);
            } else {
                $this->error('Failed to get password: ' . $response->body());
                break;
            }
        }

        return null;
    }
}
