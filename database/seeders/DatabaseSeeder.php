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
    // Only insert the Test User if email isn't already present
    \App\Models\User::firstOrCreate(
        ['email' => 'test@example.com'],     // lookup criteria
        [
          'name'     => 'Test User',
          'password' => bcrypt('secret123'),  // or whatever default you want
        ]
    );

    // Now run your course‑requirements seeder
    $this->call(\Database\Seeders\CourseRequirementsSeeder::class);
}
}
