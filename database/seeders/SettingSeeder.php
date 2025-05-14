<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Setting;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolIds = School::pluck('id')->take(3);

        $data = [
            [
                'splash_logo' => 'https://example.com/splash_logo1.png',
                'splash_title' => 'Welcome to School 1',
                'login_image_student' => 'https://example.com/login_student1.png',
                'login_image_teacher' => 'https://example.com/login_teacher1.png',
                'title' => 'School 1 Title',
                'logo' => 'https://example.com/logo1.png',
                'logo_thumbnail' => 'https://example.com/logo_thumbnail1.png',
                'primary_color' => '#FF5733',
                'secondary_color' => '#33FF57',
                'accent_color' => '#3357FF',
                'white_color' => '#FFFFFF',
                'black_color' => '#000000'
            ],
            [
                'splash_logo' => 'https://example.com/splash_logo2.png',
                'splash_title' => 'Welcome to School 2',
                'login_image_student' => 'https://example.com/login_student2.png',
                'login_image_teacher' => 'https://example.com/login_teacher2.png',
                'title' => 'School 2 Title',
                'logo' => 'https://example.com/logo2.png',
                'logo_thumbnail' => 'https://example.com/logo_thumbnail2.png',
                'primary_color' => '#FF5733',
                'secondary_color' => '#33FF57',
                'accent_color' => '#3357FF',
                'white_color' => '#FFFFFF',
                'black_color' => '#000000'
            ],
            [
                'splash_logo' => 'https://example.com/splash_logo3.png',
                'splash_title' => 'Welcome to School 3',
                'login_image_student' => 'https://example.com/login_student3.png',
                'login_image_teacher' => 'https://example.com/login_teacher3.png',
                'title' => 'School 3 Title',
                'logo' => 'https://example.com/logo3.png',
                'logo_thumbnail' => 'https://example.com/logo_thumbnail3.png',
                'primary_color' => '#FF5733',
                'secondary_color' => '#33FF57',
                'accent_color' => '#3357FF',
                'white_color' => '#FFFFFF',
                'black_color' => '#000000'
            ]
        ];

        $lastSettingId = Setting::max('id') ?? 'CMS-0000000000000';
        $lastSettingIdNumber = (int) str_replace('CMS-', '', $lastSettingId);

        foreach ($schoolIds as $index => $schoolId) {
            $lastSettingIdNumber++;
            Setting::create([
                'id' => 'CMS-' . str_pad($lastSettingIdNumber + 1, 12, '0', STR_PAD_LEFT),
                'school_id' => $schoolId,
                'splash_logo' => $data[$index]['splash_logo'],
                'splash_title' => $data[$index]['splash_title'],
                'login_image_student' => $data[$index]['login_image_student'],
                'login_image_teacher' => $data[$index]['login_image_teacher'],
                'title' => $data[$index]['title'],
                'logo' => $data[$index]['logo'],
                'logo_thumbnail' => $data[$index]['logo_thumbnail'],
                'primary_color' => $data[$index]['primary_color'],
                'secondary_color' => $data[$index]['secondary_color'],
                'accent_color' => $data[$index]['accent_color'],
                'white_color' => $data[$index]['white_color'],
                'black_color' => $data[$index]['black_color'],
            ]);
        }
    }
}
