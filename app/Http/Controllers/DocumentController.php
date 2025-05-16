<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;    // ← your new model
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\Course;
use App\Models\StudentDocument;
use App\Models\StudentCourse;
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

        // Send the email (schedule it for 5 days later)
        Mail::raw('You have 5 days to submit your missing documents.', function ($message) use ($email) {
            $message->to($email)
                ->subject('Reminder: Submit Your Missing Documents');
        });

        return response()->json(['message' => 'Reminder email scheduled successfully.']);
    }
}
