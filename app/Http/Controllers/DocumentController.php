<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;    // ← your new model
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\Course;
use App\Models\StudentDocument;
use App\Models\StudentCourse;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\CourseRequirement;


class DocumentController extends Controller
{
    private $requiredDocs = [
        'Application Form',
        'Birth Certificate',
        "Student's Voter ID/Certificate",
        "Guardian's Voter ID/Certificate",
        'Grade 11 Grade Card',
        'Grade 12 Grade Card',
        'School ID or a Valid ID',
    ];

   public function index()
    {
        $student   = Auth::user();
        $courseID  = $student->courseID;

        // 1️⃣ all docs required for this course
        $required  = CourseRequirement::where('courseID', $courseID)
                                     ->pluck('document_type')
                                     ->toArray();

        // 2️⃣ what this student already uploaded
        $uploaded  = Document::where('user_id', $student->studentID)
                             ->pluck('document_type')
                             ->toArray();

        return view('documents.index', [
            'requiredDocs' => $required,
            'uploadedDocs' => $uploaded,
            'documents'   => Document::where('user_id', $student->studentID)->get(),
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files'            => 'required|array',
            'files.*'          => 'file|mimes:pdf,jpg,png,docx|max:10240',
            'document_types'   => 'required|array',
            'document_types.*' => 'string',
        ]);

        $studentId = Auth::id();

        foreach ($request->file('files') as $idx => $file) {
            $docType = $request->document_types[$idx] ?? 'Unknown';
            $path    = $file->store("documents/{$studentId}", 'public');

            Document::create([
                'user_id'       => $studentId,
                'document_type' => $docType,
                'file_path'     => $path,
                'status'        => 'Pending',
            ]);
        }

        return back()->with('success', 'Documents uploaded!');
    }



    public function checkMissingDocs()
    {
        $uploadedDocs = Document::pluck('document_type')->toArray();
        $missingDocs = array_diff($this->requiredDocs, $uploadedDocs);

        return response()->json(['missingDocs' => $missingDocs]);
    }

   public function remove($id)
{
    $doc = Document::findOrFail($id);

    // 1️⃣ delete the file from disk
    Storage::disk('public')->delete($doc->file_path);

    // 2️⃣ delete the DB record
    $doc->delete();

    return redirect()
           ->route('documents.index')
           ->with('success', 'Document removed successfully.');
}


public function sendReminder(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $email = $request->email;

    // Setup PHPMailer
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Or your mail server
        $mail->SMTPAuth   = true;
        $mail->Username = env('PHPMAILER_EMAIL');
        $mail->Password = env('PHPMAILER_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom(env('PHPMAILER_EMAIL'), 'Beastlink University - Admissions Office');
        $mail->addAddress($email);     // Student email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reminder to Submit Missing Documents';
        $mail->Body    = '
        <p>Dear Student,</p>

        <p>
        We hope this message finds you well. This is a gentle reminder that our records indicate you have not yet completed the submission of all required documents for your enrollment. In order to proceed with the validation and processing of your application, we kindly ask that you submit the remaining documents within the next <strong>three (3) days</strong>.
        </p>

        <p>
        Timely submission of these documents is essential to avoid delays in the enrollment process and to ensure your eligibility for the upcoming academic term. Please log in to the <strong>Beastlink University Enrollment Management System</strong> using your student credentials to review which documents are still pending and to upload them accordingly.
        </p>

        <p>
        Should you encounter any difficulties or require assistance with your submission, feel free to reach out to the Admissions Office through the contact information provided in the system.
        </p>

        <p>
        We appreciate your attention to this matter and look forward to receiving your complete documentation soon.
        </p>

        <p>Sincerely,<br>
        <strong>Beastlink University</strong><br>
        Office of Admissions</p>
        ';

        $mail->send();

        return response()->json(['message' => 'Reminder email sent successfully.']);
    } catch (Exception $e) {
        \Log::error('PHPMailer Error: ' . $mail->ErrorInfo);
        return response()->json(['error' => 'Failed to send reminder.'], 500);
    }

}

    public function submitApplication(Request $request)
    {
        $student = auth()->user();

        // Optional: validate email if user typed it
        $request->validate([
            'email' => 'nullable|email'
        ]);

        if ($request->email && $request->email !== $student->email) {
            $student->email = $request->email;
        }

        // Update application status
        $student->application_status = 'pending';
        $student->save();

        return response()->json([
            'message' => 'Application submitted successfully and is pending admin review.'
        ]);
    }


}
