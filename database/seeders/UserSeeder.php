<?php

namespace Database\Seeders;

use App\Http\Traits\CommonTrait;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Staff;
use App\Models\SubClasses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    use CommonTrait;

    public function run(): void
    {
        $roles = ['STUDENT', 'TEACHER', 'STAFF'];
        $authorityOptions = ['ADMIN', 'KURIKULUM'];
        $school = DB::table('schools')->first();
        $subClasses = SubClasses::all();

        foreach ($roles as $role) {
            for ($i = 0; $i < 5; $i++) {
                $user = User::create([
                    'id' => $this->getUniqueId('users', 'USE-', 16),
                    'school_id' => $school->id,
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'status' => 'Active',
                    'fullname' => fake()->name(),
                    'phone' => fake()->phoneNumber(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'religion' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
                    'address' => fake()->address(),
                    'role' => $role,
                    'image_path' => null,
                    'is_premium' => fake()->boolean(),
                    'fcm_token' => Str::random(32),
                ]);

                if ($role === 'STUDENT') {
                    $subClass = $subClasses->random();
                    Student::create([
                        'id' => $this->getUniqueId('students', 'STU-', 16),
                        'user_id' => $user->id,
                        'sub_class_id' => $subClass->id,
                        'nisn' => fake()->numerify('############'),
                        'major' => fake()->word(),
                        'type' => 'Regular',
                        'year' => now()->year,
                    ]);
                }

                if ($role === 'TEACHER') {
                    Teacher::create([
                        'id' => $this->getUniqueId('teachers', 'TEA-', 16),
                        'user_id' => $user->id,
                        'nip' => fake()->numerify('##########'),
                        'is_wali' => fake()->boolean(),
                    ]);
                }

                if ($role === 'STAFF') {
                    Staff::create([
                        'id' => $this->getUniqueId('staffs', 'STA-', 16),
                        'user_id' => $user->id,
                        'nip' => fake()->numerify('##########'),
                        'placement' => fake()->city(),
                        'authority' => fake()->randomElement($authorityOptions),
                    ]);
                }
            }
        }
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

        $numberLength = $length - strlen($prefix);

        if ($latestId) {
            $lastNumberPart = substr($latestId, strlen($prefix));
            $parts = explode('/', $lastNumberPart);
            $lastNumber = intval($parts[0]);
            $newNumber = $lastNumber + 1;

            $newId = $prefix . str_pad($newNumber, $numberLength, '0', STR_PAD_LEFT);

            if ($table !== 'users' && count($parts) > 1) {
                $newId .= '/' . $parts[1];
            }
        } else {
            $newId = $prefix . str_pad('1', $numberLength, '0', STR_PAD_LEFT);
        }

        return $newId;
    }
}
