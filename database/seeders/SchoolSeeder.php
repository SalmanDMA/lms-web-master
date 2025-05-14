<?php

namespace Database\Seeders;

use App\Http\Traits\CommonTrait;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolSeeder extends Seeder
{
    use CommonTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            [
                'admin_email' => 'admin@mail.com',
                'admin_password' => bcrypt('secretpass'),
                'admin_name' => 'admin',
                'admin_phone' => '081222213921',
                'admin_address' => 'Jl. Saad No.28 A, Kb. Pisang, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40112.',
                'logo' => 'http://example.com/logo1.png',
                'school_image' => 'http://example.com/school1.png',
                'structure' => 'http://example.com/structure1.png',
                'phone_number' => "081222213921",
                'email' => 'digyhomeschooling@gmail.com',
                'website' => 'https://digyhomeschooling.id',
                'name' => 'Digy Homeschooling',
                'another_name' => 'Digy Homeschooling',
                'type' => 'Swasta',
                'status' => 'Operational',
                'acreditation' => 'A',
                'vision' => 'To be the best school.',
                'mission' => 'Educate students to excel.',
                'description' => 'A great place for learning.',
                'country' => 'Indonesia',
                'province' => 'Jawa Barat',
                'city' => 'Kota Bandung',
                'district' => 'Sumur Bandung',
                'neighborhood' => 'Kb. Pisang',
                'rw' => '01',
                'latitude' => '40.712776',
                'longitude' => '-74.005974',
                'address' => '123 School St.',
                'pos' => 40112,
            ],
        ];

        foreach ($schools as &$school) {
            $school['id'] = $this->getUniqueId('schools', 'SCH-', 16);
            School::create($school);
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