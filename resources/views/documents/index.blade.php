@extends('layouts.app')

@section('content')
<div class="container">

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

    <h2>View List of Document Requirements</h2>
    <p>Please read the instructions carefully and make sure to upload a clear photocopy of the following documents:</p>
    <ul>
        @foreach ($requiredDocs as $document)
            <li>{{ $document }}</li>
        @endforeach
    </ul>

    <h2>Document Submission</h2>
    <p>Please check the documents you want to upload and ensure all required files are uploaded before submitting.</p>

    @if ($applicationStatus !== 'approved')
        <h3>Select Documents You Can Upload:</h3>
        @foreach($requiredDocs as $document)
            @php $slug = Str::slug($document); @endphp
            <div class="form-check">
                <input type="checkbox" class="form-check-input doc-checkbox" id="{{ $slug }}" name="selected_documents[]" value="{{ $document }}" 
                {{ in_array($document, $uploadedDocs) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $slug }}">{{ $document }}</label>
            </div>
        @endforeach

        <h3 class="mt-3">Upload Files</h3>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="uploadForm" action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="uploadFields"></div>
            <button type="submit" id="uploadBtn" class="btn btn-success mt-2" disabled>Upload</button>
        </form>
    @else
        <div class="alert alert-info mt-3">
            Your documents have been approved. You cannot upload or modify your documents anymore.
        </div>
    @endif

    <h3 class="mt-4">Uploaded Documents:</h3>
    <ul id="uploadedList">
        @foreach($documents as $document)
            <li id="doc-{{ $document->id }}" data-doc-type="{{ $document->document_type }}">
                {{ $document->document_type }} - <strong>{{ basename($document->file_path) }}</strong> -
                <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank">View</a>
                <form action="{{ route('documents.remove', $document->id) }}" method="POST" class="d-inline remove-form"
                    @if ($applicationStatus = 'approved') style="display:none;" @endif>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                </form>

            </li>
        @endforeach
    </ul>

    <p id="submissionStatus" class="mt-3"></p>
    <button type="submit" id="submitBtn" class="btn btn-primary mt-2" disabled>Submit</button>
</div>

<!-- Confirmation form (hidden by default) -->
<div id="confirmationForm" class="mt-4" style="display: none;">
    <h4>Have you uploaded all the required documents?</h4>
    <button type="button" id="confirmYes" class="btn btn-success">Yes</button>
    <button type="button" id="confirmNo" class="btn btn-warning">No</button>
</div>

<!-- Message display -->
<div id="finalMessage" class="alert alert-info mt-3" style="display: none;"></div>


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

    // === Confirmation Logic ===
    submitBtn.addEventListener('click', function (event) {
        event.preventDefault();
        confirmationForm.style.display = 'block';
        finalMessage.style.display = 'none';
    });

    confirmYes.addEventListener('click', function () {
        fetch("{{ route('documents.submitApplication') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, "success");
            // Optionally hide the confirmation form
            confirmationForm.style.display = 'none';
            // Optionally disable submit button to prevent resubmission
            submitBtn.disabled = true;
        })
        .catch(error => {
            console.error(error);
            showMessage("Failed to submit application. Please try again.", "danger");
        });
    });

    confirmNo.addEventListener('click', function () {
        // Hide the confirmation form
    confirmationForm.style.display = 'none';

    // Show email prompt for reminder
    finalMessage.innerHTML = `
        <div class="mb-3">
            <p>Please follow up your missing requirements within 3 days.</p>
            <label for="reminderEmail">Enter your email address to receive a reminder:</label>
            <input type="email" id="reminderEmail" class="form-control mb-2" placeholder="example@gmail.com" required>
            <button id="reminderDone" class="btn btn-primary">Done</button>
        </div>
    `;
    finalMessage.className = 'alert alert-warning mt-3';
    finalMessage.style.display = 'block';

    // Add event listener to the "Done" button
    document.getElementById('reminderDone').addEventListener('click', function () {
        const email = document.getElementById('reminderEmail').value;

        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address.');
            return;
        }

        fetch("{{ route('documents.sendReminder') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
    // After sending reminder, also submit the application
    fetch("{{ route('documents.submitApplication') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(submitData => {
        finalMessage.className = 'alert alert-success mt-3';
        finalMessage.innerHTML = `
            Reminder email sent successfully. Please check your inbox.<br>
            <strong>${submitData.message}</strong>
        `;
        confirmationForm.style.display = 'none';
        submitBtn.disabled = true;
    })
    .catch(error => {
        console.error(error);
        finalMessage.className = 'alert alert-danger mt-3';
        finalMessage.innerHTML = 'Application reminder sent, but submission failed. Please try again.';
    });
})
    });
    
    });

    function showMessage(message, type) {
        finalMessage.className = `alert alert-${type} mt-3`;
        finalMessage.innerHTML = message;
        finalMessage.style.display = 'block';
        confirmationForm.style.display = 'none';
    }
});
</script>

@endsection
