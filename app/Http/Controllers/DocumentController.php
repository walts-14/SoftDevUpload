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
    $studentId    = Auth::id();

    // Pull directly from your `documents` table via the new model:
    $documents = Document::where('user_id', $studentId)->get();

    $requiredDocs = $this->requiredDocs;
    $uploadedDocs = $documents->pluck('document_type')->toArray();

    return view('documents.index', compact(
        'documents',
        'requiredDocs',
        'uploadedDocs'
    ));
}


    public function upload(Request $request)
{
    $request->validate([
        'files'            => 'required|array',
        'files.*'          => 'file|mimes:pdf,jpg,png,docx|max:10240',
        'document_types'   => 'required|array',
        'document_types.*' => 'string',
        // 'courseID'         => 'required|exists:courses,courseID',
    ]);

    $studentId = Auth::id();
    $courseId  = $request->courseID;

    foreach ($request->file('files') as $idx => $uploadedFile) {
        $docType = $request->input('document_types')[$idx] ?? 'Unknown';

        // store the file under storage/app/public/documents/{studentId}/
        $path = $uploadedFile->store(
            "documents/{$studentId}",
            'public'
        );

        // insert into the `documents` table
        Document::create([
            'user_id'        => $studentId,
            'document_type'  => $docType,
            'file_path'      => $path,
            'status'         => 'Pending',
            'rejection_reason' => null,
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
