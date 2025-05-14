<?php

namespace Database\Seeders;

use App\Http\Traits\CommonTrait;
use App\Models\Classes;
use App\Models\SubClasses;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubClassSeeder extends Seeder
{
    use CommonTrait;

    public function run(): void
    {
        $school = DB::table('schools')->first();
        $classes = Classes::all();
        $classes->each(function ($class) use ($school) {
            SubClasses::create([
                'id' => $this->getUniqueId('sub_class', 'SUB-', 16),
                'school_id' => $school->id,
                'class_id' => $class->id,
                'name' => $class->name,
            ]);
        });
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
