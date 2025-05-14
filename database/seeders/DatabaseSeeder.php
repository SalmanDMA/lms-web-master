<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            SchoolSeeder::class,
            ClassSeeder::class,
            SubClassSeeder::class,
            UserSeeder::class,
        ]);

        $this->call([
            SettingSeeder::class,
        ]);
    }
}
