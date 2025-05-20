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


    public function approveApplication(Request $request, Student $student)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $student->application_status = 'approved';
        $student->save();

                        foreach ($student->documents as $doc) {
                    if ($doc->status === 'Pending') {
                        $doc->status = 'Approved';
                        $doc->save();
                    }
                }

            $this->sendEmail(
            $request->email,
            'Application Status - Approved',
            'Dear Student,<br><br>
        Congratulations! Your application to <strong>Beastlink University</strong> has been approved.<br><br>
        You may now proceed with the next steps by logging in to the Beastlink University Enrollment Management System using your student credentials.<br><br>
        If you need assistance, feel free to contact the Admissions Office.<br><br>
        Sincerely,<br>
        <strong>Beastlink University</strong><br>
        Office of Admissions'
        );

        return redirect()->route('admin.applications.list')->with('success', 'Application approved and email sent.');
    }

            public function rejectApplication(Request $request, Student $student)
            {
                $request->validate([
                    'rejection_reason' => 'required|string|max:255',
                    'email' => 'required|email'
                ]);

                $student->application_status = 'rejected';
                $student->save();

                foreach ($student->documents as $doc) {
                    if ($doc->status === 'Pending') {
                        $doc->status = 'Rejected';
                        $doc->rejection_reason = $request->rejection_reason;
                        $doc->save();
                    }
                }

                $this->sendEmail(
                $request->email,
                'Application Status - Rejected',
                'Dear Student,<br><br>
            We regret to inform you that your application to Beastlink University has been rejected due to the following reason: <strong>' . $request->rejection_reason . '</strong><br><br>
            Unfortunately, without these documents, we are unable to proceed with your enrollment.<br><br>
            If you have questions or wish to reapply in the next term, please contact the Admissions Office through the Beastlink University Enrollment Management System.<br><br>
            Sincerely,<br>
            <strong>Beastlink University</strong><br>
            Office of Admissions'
            );

                return redirect()->route('admin.applications.list')->with('success', 'Application rejected and email sent.');
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


private function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = env('PHPMAILER_EMAIL');
        $mail->Password   = env('PHPMAILER_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom(env('PHPMAILER_EMAIL'), 'Beastlink University - Admissions Office');
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        \Log::error("Email failed: " . $mail->ErrorInfo);
    }

}


}
