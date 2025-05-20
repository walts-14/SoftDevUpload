<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseRequirement;

class CourseRequirementsSeeder extends Seeder
{
    public function run()
    {
        //
        // 1️⃣ Make sure the courses themselves exist:
        //
      

        //
        // 2️⃣ Define CS101 requirements:
        //
        $csDocs = [
            'Application Form',
            'Birth Certificate',
            'High School Transcript',
            'Recommendation Letter',
            '1x1 ID Picture',
        ];
        foreach ($csDocs as $docType) {
            CourseRequirement::firstOrCreate([
                'courseID'      => 'CS101',
                'document_type' => $docType,
            ]);
        }

        //
        // 3️⃣ Define IT201 requirements:
        //
        $itDocs = [
            'Application Form',
            'Birth Certificate',
            'Grade 11 Grade Card',
            'Grade 12 Grade Card',
            'Portfolio of Projects',
        ];
        foreach ($itDocs as $docType) {
            CourseRequirement::firstOrCreate([
                'courseID'      => 'IT201',
                'document_type' => $docType,
            ]);
        }

        //
        // 4️⃣ (Optional) Add other courses below…
        //
                $itDocs = [
            'Birth Certificate',
            'SF10',
            'Report Card',
            'Application Form',
            'Form 137',
        ];
        foreach ($itDocs as $docType) {
            CourseRequirement::firstOrCreate([
                'courseID'      => 'IS301',
                'document_type' => $docType,
            ]);
        }
    }
}