@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pending Applications</h1>
    

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($applications->isEmpty())
        <p>No pending applications.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Course ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Application Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
                <tbody>
                    @foreach ($applications as $student)
                    <tr>
                        <td>{{ $student->studentID }}</td>
                        <td>{{ $student->courseID }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ ucfirst($student->application_status) }}</td>
                        <td>
                                <form action="{{ route('admin.applications.approve', ['student' => $student->studentID]) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <input type="email" name="email" placeholder="Student email" required class="form-control mb-1" />
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                            <!-- Button to toggle document details -->
                            <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#docs-{{ $student->studentID }}">View Documents</button>
                        </td>
                    </tr>
                    <!-- Collapsible row for document details -->
                    <tr class="collapse" id="docs-{{ $student->studentID }}">
                        <td colspan="5">
                            <strong>Uploaded Documents:</strong>
                            <ul>
                                @forelse($student->documents as $doc)
                                    <li>
                                        {{ $doc->document_type }} -
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank">View</a> -
                                        Status: {{ $doc->status }}
                                        @if ($doc->status === 'Rejected')
                                            <br><strong>Reason:</strong> {{ $doc->rejection_reason }}
                                        @endif
                                    </li>
                                @empty
                                    <li>No documents uploaded.</li>
                                @endforelse
                            </ul>

                                @if (!empty($student->missing_documents))
                                    <div class="alert alert-warning mt-2">
                                        <strong>Missing Documents:</strong>
                                        <ul>
                                            @foreach ($student->missing_documents as $doc)
                                                <li>{{ $doc }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="text-success mt-2">
                                        ✅ All required documents submitted.
                                    </div>
                                @endif

                            <form action="{{ route('admin.applications.reject', $student->studentID) }}" method="POST" class="mt-2">
                                @csrf
                                <input type="text" name="rejection_reason" placeholder="Reason for rejection" required class="form-control mb-2">
                                <input type="email" name="email" placeholder="Student email" required class="form-control mb-2">
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
        </table>
    @endif
</div>
@endsection
