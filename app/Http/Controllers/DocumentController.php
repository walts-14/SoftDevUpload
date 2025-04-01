<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        if (!auth()->guard('student')->check()) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }
    
        $student = auth()->guard('student')->user();
        $documents = DocumentRequirement::where('studentID', $student->studentID)->get();
    
        return view('documents.index', [
            'documents' => $documents,
            'requiredDocs' => $this->requiredDocs,
            'uploadedDocs' => $documents->pluck('fileName')->toArray()
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240|mimes:pdf,jpeg,png,docx',
            'document_types' => 'required|array',
            'document_types.*' => 'string'
        ]);
    
        $user = auth()->user(); // Get the authenticated student
        $studentID = $user->studentID;
        $courseID = $user->courseID;
        $files = $request->file('files');
        $documentTypes = $request->input('document_types');
    
        foreach ($files as $index => $file) {
            $documentType = $documentTypes[$index] ?? null;
            if ($documentType) {
                // Rename file based on document type
                $extension = $file->getClientOriginalExtension();
                $newFileName = "{$documentType}.{$extension}";
                $path = $file->storeAs("documents/{$studentID}", $newFileName, 'public');
    
                // Save document details in the database
                DocumentRequirement::create([
                    'documentID' => uniqid(), // Generate unique ID
                    'studentID' => $studentID,
                    'courseID' => $courseID,
                    'fileName' => $newFileName,
                    'fileFormat' => $extension,
                    'fileSize' => $file->getSize(),
                    'documentStatus' => 'pending', // Default status
                    'removeFile' => false, // Default false
                ]);
            }
        }
    
        return redirect()->route('documents.index')->with('success', 'Files uploaded successfully.');
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
    \Mail::raw('You have 5 days to submit your missing documents.', function ($message) use ($email) {
        $message->to($email)
            ->subject('Reminder: Submit Your Missing Documents');
    });

    return response()->json(['message' => 'Reminder email scheduled successfully.']);
}

}
