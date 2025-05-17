<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\CourseRequirement;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminController extends Controller
{

    
    // Show list of pending applications directly
        public function listApplications()
        {
            $applications = Student::with('documents')
                ->where('application_status', 'pending')
                ->get();

            foreach ($applications as $student) {
                $student->missing_documents = $this->getMissingDocuments($student);
            }

            return view('admin.applications', compact('applications'));
        }


    public function approveApplication(Student $student)
    {
        $student->application_status = 'approved';
        $student->save();

        return redirect()->route('admin.applications.list')->with('success', 'Application approved.');
    }

    public function rejectApplication(Request $request, Student $student)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        // Set student as rejected
        $student->application_status = 'rejected';
        $student->save();

        // Optionally reject each pending document and attach reason
        foreach ($student->documents as $doc) {
            if ($doc->status === 'Pending') {
                $doc->status = 'Rejected';
                $doc->rejection_reason = $request->rejection_reason;
                $doc->save();
            }
        }

        return redirect()->route('admin.applications.list')->with('success', 'Application rejected.');
    }


    public function viewApplications()
    {
        $applications = User::with('documents')->get(); // eager-load documents
        return view('admin.applications', compact('applications'));
    }

    private function getMissingDocuments($student)
    {
        if (!$student->courseID) return ['Course not assigned'];

        $requiredDocs = CourseRequirement::where('courseID', $student->courseID)
                        ->pluck('document_type')
                        ->map(fn($doc) => strtolower($doc))
                        ->toArray();

        $uploadedDocs = $student->documents->pluck('document_type')
                        ->map(fn($doc) => strtolower($doc))
                        ->toArray();

        $missing = array_diff($requiredDocs, $uploadedDocs);

        return array_map('ucwords', $missing); // Format nicely
    }



}
