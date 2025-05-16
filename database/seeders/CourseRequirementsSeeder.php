<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseRequirement;

class CourseRequirementsSeeder extends Seeder
{
    public function run()
    {
        // Example: CS101 requires Form 137 and Birth Certificate
        CourseRequirement::firstOrCreate(
        ['courseID'=>'CS101','document_type'=>'Birth Certificate']
        );
        CourseRequirement::firstOrCreate(
        ['courseID'=>'CS101','document_type'=>'Form 137']
        );
       CourseRequirement::firstOrCreate([
        'courseID' => 'IT201',
        'course_name' => 'Information Technology',
        ]);

    // Now call the requirements seeder
    $this->call(CourseRequirementsSeeder::class);
}
}