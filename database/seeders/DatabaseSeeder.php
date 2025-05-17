<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // —––– If you still want this Test User, make it idempotent:
        \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'     => 'Test User',
                'password' => bcrypt('secret'), 
            ]
        );

        // ───▶ HERE is where you hook in your course‐requirements seeder:
        $this->call(\Database\Seeders\CourseRequirementsSeeder::class);
    }
}
