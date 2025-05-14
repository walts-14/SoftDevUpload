<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentRequirement;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\Course;
use App\Models\StudentDocument;
use App\Models\StudentCourse;
use App\Models\Document;

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

        // 1️⃣ Fetch all the student’s previously uploaded records
        $documents = DocumentRequirement::where('studentID', $studentId)->get();

        // 2️⃣ The full list of “required” document names
        $requiredDocs = $this->requiredDocs;

        // 3️⃣ A simple array of what the student has already uploaded
        //    (so your JS can disable/“check” those boxes)
        $uploadedDocs = $documents
            ->pluck('document_type')   // ← make sure this matches your DB column
            ->toArray();

        // 4️⃣ Pass all three into your Blade
        return view('documents.index', compact(
            'documents',
            'requiredDocs',
            'uploadedDocs'
        ));
    }



public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:pdf,jpg,png|max:5120',
        'courseID' => 'required|exists:courses,courseID',
        // …
    ]);

    // Build a new DocumentRequirement record:
    DocumentRequirement::create([
        'documentID'     => Str::uuid(),
        'studentID'      => Auth::id(),          // ← tie it to the current student
        'courseID'       => $request->courseID,
        'fileName'       => $request->file->getClientOriginalName(),
        'fileFormat'     => $request->file->getClientOriginalExtension(),
        'fileSize'       => $request->file->getSize(),
        'documentStatus' => 'pending',
        'removeFile'     => false,
    ]);

    // move the file, flash message, etc…
    return back()->with('success', 'Document uploaded!');
}

public function checkMissingDocs()
{
    $uploadedDocs = Document::pluck('document_type')->toArray();
    $missingDocs = array_diff($this->requiredDocs, $uploadedDocs);

    return response()->json(['missingDocs' => $missingDocs]);
}

    public function remove($id)
    {
        $document = DocumentRequirement::findOrFail($id);
        $document->removeFile = true; // Mark as removed
        $document->save();

        return redirect()->route('documents.index')->with('success', 'File marked as removed.');
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
