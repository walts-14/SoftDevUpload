@extends('layouts.app')

@section('content')
<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
  body { background-color: #E8F6EF; padding-top: 70px; }
  .topbar { position: fixed; top:0; left:0; right:0; background:#fff; border-bottom:1px solid #ddd; z-index:1030; }
  .sidebar { position: fixed; top:70px; left:0; width:220px; height:calc(100vh-70px); background:#004D40; padding-top:1rem; }
  .sidebar .nav-link { color:#B2DFDB; margin:.5rem 0; padding:.75rem 1rem; border-radius:.25rem; }
  .sidebar .nav-link.active { background:#00796B; color:#fff; }
  .content-area { margin-left:220px; padding:2rem; }
  .status-badge { text-transform: capitalize; }
  .table-responsive { background:#fff; border-radius:.5rem; padding:1rem; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
  .collapse-row td { background:#F9F9F9; }
</style>

{{-- Topbar --}}
<nav class="topbar navbar navbar-expand-lg navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><img src="/images/LOGO.png" height="40" alt="BeastLink"></a>
    <form class="d-flex ms-3 flex-grow-1">
      <input class="form-control me-2" type="search" placeholder="Search Students">
    </form>
    <ul class="navbar-nav ms-auto align-items-center">
      <li class="nav-item me-3"><a class="nav-link" href="#"><i class="bi bi-bell"></i></a></li>
      <li class="nav-item"><form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-success">Log out</button></form></li>
    </ul>
  </div>
</nav>

<div class="d-flex">
  {{-- Sidebar --}}
  <nav class="sidebar d-flex flex-column nav-pills">
    <a class="nav-link" href="#">Dashboard</a>
    <a class="nav-link" href="#">Teachers</a>
    <a class="nav-link" href="#">Students & Classes</a>
    <a class="nav-link active" href="#">Pending Applications</a>
    <a class="nav-link" href="#">Settings</a>
  </nav>

  {{-- Main Content --}}
  <div class="content-area">
    <h2 class="mb-4">Pending Applications</h2>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($applications->isEmpty())
      <div class="alert alert-info">No pending applications.</div>
    @else
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>Student ID</th>
              <th>Course ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
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
                <td><span class="badge bg-warning status-badge">{{ $student->application_status }}</span></td>
                <td>
                  <div class="d-flex gap-2">
                    <form action="{{ route('admin.applications.approve', $student->studentID) }}" method="POST" class="d-flex">
                      @csrf
                      <input type="email" name="email" placeholder="Email" required class="form-control form-control-sm me-1" />
                      <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                    <button class="btn btn-info btn-sm" data-bs-toggle="collapse" data-bs-target="#docs-{{ $student->studentID }}">
                      View Docs
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="collapse collapse-row" id="docs-{{ $student->studentID }}">
                <td colspan="6">
                  <div class="mb-3">
                    <strong>Uploaded Documents:</strong>
                    <ul>
                      @forelse($student->documents as $doc)
                        <li>
                          {{ $doc->document_type }} –
                          <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank">View</a> –
                          <span class="text-capitalize">Status: {{ $doc->status }}</span>
                          @if($doc->status==='Rejected')<br><strong>Reason:</strong> {{ $doc->rejection_reason }}@endif
                        </li>
                      @empty
                        <li>No documents uploaded.</li>
                      @endforelse
                    </ul>
                  </div>

                  @if(!empty($student->missing_documents))
                    <div class="alert alert-warning">
                      <strong>Missing Documents:</strong>
                      <ul class="mb-0">
                        @foreach($student->missing_documents as $md)
                          <li>{{ $md }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @else
                    <div class="text-success mb-3">✅ All required documents submitted.</div>
                  @endif

                  <form action="{{ route('admin.applications.reject', $student->studentID) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-5">
                      <input type="text" name="rejection_reason" placeholder="Rejection reason" required class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-4">
                      <input type="email" name="email" placeholder="Email" required class="form-control form-control-sm" />
                    </div>
                    <div class="col-md-3">
                      <button class="btn btn-danger btn-sm w-100">Reject</button>
                    </div>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
