<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

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
        $documents = Document::all();
        $uploadedDocs = $documents->pluck('document_type')->toArray();

        return view('documents.index', [
            'documents' => $documents,
            'requiredDocs' => $this->requiredDocs,
            'uploadedDocs' => $uploadedDocs
        ]);
    }

    public function upload(Request $request)
{
    $request->validate([
        'files' => 'required|array',
        'files.*' => 'file|max:10240|mimes:pdf,jpeg,png,docx',
    ], [
        'files.required' => 'You must upload at least one document.',
        'files.*.max' => 'File must not exceed 10MB.',
        'files.*.mimes' => 'Only PDF, JPEG, PNG, and DOCX files are allowed.',
    ]);

    foreach ($request->file('files') as $documentType => $file) {
        $existingDoc = Document::where('document_type', $documentType)->first();
        if ($existingDoc) {
            Storage::disk('public')->delete($existingDoc->file_path);
            $existingDoc->delete();
        }

        $path = $file->store('documents', 'public');

        Document::create([
            'document_type' => $documentType,
            'file_path' => $path,
        ]);
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
        $document = Document::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'File removed successfully.');
    }
}
