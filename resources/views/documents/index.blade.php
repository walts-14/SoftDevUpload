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
  .requirements-grid { display:flex; gap:1rem; overflow-x:auto; padding-bottom:1rem; }
  .req-card { flex:0 0 200px; background:#009688; color:#fff; border-radius:.75rem; padding:1rem; text-align:center; font-weight:500; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
  .btn-large { padding:1rem 2rem; font-size:1.25rem; border-radius:.5rem; }
  .upload-fields { margin-top:1rem; }
  .upload-list .list-group-item { border:none; border-bottom:1px solid #ddd; }
</style>

<nav class="topbar navbar navbar-expand-lg navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><img src="/images/LOGO.png" height="40"></a>
    <form class="d-flex ms-3 flex-grow-1">
      <input class="form-control me-2" type="search" placeholder="Search Courses">
      <button class="btn btn-outline-secondary" type="submit">Filter</button>
    </form>
    <ul class="navbar-nav ms-auto align-items-center">
      <li class="nav-item me-3"><a class="nav-link" href="#"><i class="bi bi-bell"></i></a></li>
      <li class="nav-item"><form action="{{ route('logout') }}" method="POST">@csrf<button class="btn btn-success">Log out</button></form></li>
    </ul>
  </div>
</nav>

<div class="d-flex">
  <nav class="sidebar d-flex flex-column nav-pills">
    <a class="nav-link" href="#">Dashboard</a>
    <a class="nav-link" href="#">Teachers</a>
    <a class="nav-link" href="#">Students & Classes</a>
    <a class="nav-link active" href="#">Documents/Requirements</a>
    <a class="nav-link" href="#">Billing</a>
    <a class="nav-link" href="#">Settings & Profile</a>
    <a class="nav-link" href="#">Exams</a>
    <a class="nav-link" href="#">Features</a>
  </nav>

  <div class="content-area">
    <h2 class="mb-3">Documents/Requirements</h2>
    <p class="text-muted">Please read the instructions carefully and upload clear photocopies:</p>

    <div class="requirements-grid mb-4">
      @foreach($requiredDocs as $doc)
        <div class="req-card">{{ $doc }}</div>
      @endforeach
    </div>

    @if($applicationStatus !== 'approved')
      <div class="d-flex gap-3 mb-4">
        <button class="btn btn-success btn-large" onclick="document.getElementById('uploadSection').scrollIntoView();">Upload Documents</button>
       <button id="seeStatusBtn" class="btn btn-outline-success btn-large">
        See Application Status
        </button>

        <!-- Application Status (hidden by default) -->
        <div id="statusSection" class="mt-4" style="display:none;">
        <h4>Application Status:
            @if ($applicationStatus === 'approved')
            <span class="badge bg-success">Approved</span>
            @elseif ($applicationStatus === 'pending')
            <span class="badge bg-warning text-dark">Pending</span>
            @elseif ($applicationStatus === 'rejected')
            <span class="badge bg-danger">Rejected</span>
            @else
            <span class="badge bg-secondary">Not Submitted</span>
            @endif
        </h4>
        </div>
      </div>

      <div id="uploadSection" class="card mb-4">
        <div class="card-body">
          <h5>Select & Upload</h5>
          @foreach($requiredDocs as $document)
            @php $slug = Str::slug($document); @endphp
            <div class="form-check mb-2">
              <input type="checkbox" class="form-check-input doc-checkbox" id="{{ $slug }}" value="{{ $document }}"
                name="selected_documents[]" {{ in_array($document, $uploadedDocs) ? 'checked' : '' }}>
              <label class="form-check-label" for="{{ $slug }}">{{ $document }}</label>
            </div>
          @endforeach

          @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
          @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

          <form id="uploadForm" action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="uploadFields" class="upload-fields"></div>
            <button type="submit" id="uploadBtn" class="btn btn-success mt-2" disabled>Upload Selected</button>
          </form>
        </div>
      </div>
    @else
      <div class="alert alert-info mb-4">Your documents have been approved and cannot be modified.</div>
    @endif

    <div class="upload-list card">
      <div class="card-body">
        <h5>Uploaded Documents</h5>
        <ul class="list-group">
          @foreach($documents as $document)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>{{ $document->document_type }} – <strong>{{ basename($document->file_path) }}</strong></span>
              <div>
                <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">View</a>
                @if($applicationStatus !== 'approved')
                <form action="{{ route('documents.remove', $document->id) }}" method="POST" class="d-inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
                @endif
              </div>
            </li>
          @endforeach
        </ul>

        <div class="mt-3">
          <button id="submitBtn" class="btn btn-success" disabled>Submit Application</button>
        </div>

        <div id="confirmationForm" class="mt-3" style="display:none;">
          <div class="alert alert-warning">
            <p>Have you uploaded all the required documents?</p>
            <button id="confirmYes" class="btn btn-success me-2">Yes</button>
            <button id="confirmNo" class="btn btn-secondary">No</button>
          </div>
        </div>
        <div id="finalMessage" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.doc-checkbox');
    const uploadFieldsContainer = document.getElementById('uploadFields');
    const uploadBtn = document.getElementById('uploadBtn');
    const submitBtn = document.getElementById('submitBtn');
    const confirmationForm = document.getElementById('confirmationForm');
    const finalMessage = document.getElementById('finalMessage');
    const confirmYes = document.getElementById('confirmYes');
    const confirmNo = document.getElementById('confirmNo');
    const uploadForm = document.getElementById('uploadForm');

    let uploadedDocs = new Set(@json($uploadedDocs));

    function updateUploadFields() {
        uploadFieldsContainer.innerHTML = '';

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const docType = checkbox.value;
                const slug = checkbox.id;

                if (!uploadedDocs.has(docType)) {
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.name = 'files[]';
                    fileInput.className = 'form-control upload-input';
                    fileInput.accept = '.pdf,.jpeg,.jpg,.png,.docx';
                    fileInput.dataset.docType = docType;

                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'document_types[]';
                    hiddenInput.value = docType;

                    const wrapper = document.createElement('div');
                    wrapper.id = `upload-${slug}`;
                    wrapper.innerHTML = `<label>${docType} (PDF, JPEG, PNG, DOCX, max 10MB):</label>`;
                    wrapper.appendChild(fileInput);
                    wrapper.appendChild(hiddenInput);

                    uploadFieldsContainer.appendChild(wrapper);
                    fileInput.addEventListener('change', handleFileUpload);
                }
            }
        });

        checkUploadStatus();
    }

    function handleFileUpload(event) {
        const file = event.target.files[0];
        const docType = event.target.dataset.docType;

        if (file) {
            const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

            if (!validTypes.includes(file.type) || file.size > 10 * 1024 * 1024) {
                alert('Invalid file. Only PDF, JPEG, PNG, and DOCX files under 10MB are allowed.');
                event.target.value = "";
                uploadedDocs.delete(docType);
            } else {
                uploadedDocs.add(docType);
            }
        } else {
            uploadedDocs.delete(docType);
        }

        checkUploadStatus();
    }

    function checkUploadStatus() {
        const selectedDocs = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        const hasCheckedDocs = selectedDocs.length > 0;
        const allUploaded = selectedDocs.every(doc => uploadedDocs.has(doc));

        uploadBtn.disabled = !hasCheckedDocs;
        submitBtn.disabled = !(hasCheckedDocs && allUploaded);
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateUploadFields);
    });

    updateUploadFields();

    submitBtn.addEventListener('click', function (event) {
        event.preventDefault();
        confirmationForm.style.display = 'block';
        finalMessage.style.display = 'none';
    });

    confirmYes.addEventListener('click', function () {
        fetch("{{ route('documents.submitApplication') }}", { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" }, body: JSON.stringify({}) })
        .then(response => response.json())
        .then(data => { showMessage(data.message, "success"); confirmationForm.style.display = 'none'; submitBtn.disabled = true; })
        .catch(error => { console.error(error); showMessage("Failed to submit application. Please try again.", "danger"); });
    });

    confirmNo.addEventListener('click', function () {
        confirmationForm.style.display = 'none';
        finalMessage.innerHTML = `<div class="mb-3"><p>Please follow up your missing requirements within 3 days.</p><label for="reminderEmail">Enter your email address to receive a reminder:</label><input type="email" id="reminderEmail" class="form-control mb-2" placeholder="example@gmail.com" required><button id="reminderDone" class="btn btn-primary">Done</button></div>`;
        finalMessage.className = 'alert alert-warning mt-3';
        finalMessage.style.display = 'block';

        document.getElementById('reminderDone').addEventListener('click', function () {
            const email = document.getElementById('reminderEmail').value;
            if (!email.includes('@')) { alert('Please enter a valid email address.'); return; }
            fetch("{{ route('documents.sendReminder') }}", { method:"POST", headers:{"Content-Type":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"}, body:JSON.stringify({ email }) })
            .then(resp => resp.json())
            .then(data => fetch("{{ route('documents.submitApplication') }}", { method:"POST", headers:{"Content-Type":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"}, body:JSON.stringify({}) }))
            .then(r2 => r2.json())
            .then(subData => { finalMessage.className='alert alert-success mt-3'; finalMessage.innerHTML=`Reminder email sent!<br><strong>${subData.message}</strong>`; submitBtn.disabled=true; })
            .catch(() => finalMessage.innerHTML='Something went wrong.');
        });
    });

    function showMessage(message, type) {
        finalMessage.className = `alert alert-${type} mt-3`;
        finalMessage.innerHTML = message;
        finalMessage.style.display = 'block';
        confirmationForm.style.display = 'none';
    }
    // === Show Application Status ===
    document.getElementById('seeStatusBtn').addEventListener('click', function() {
    const statusDiv = document.getElementById('statusSection');
    statusDiv.style.display = 'block';
    statusDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

});
</script>
@endsection
