@extends('layouts.app')

@section('content')
<div class="container">

    <h2>View List of Document Requirements</h2>
    <p>Please read the instructions carefully and make sure to upload a clear photocopy of the following documents:</p>
    <ul>
        @foreach ($requiredDocs as $document)
            <li>{{ $document }}</li>
        @endforeach
    </ul>

    <h2>Document Submission</h2>
    <p>Please check the documents you want to upload and ensure all required files are uploaded before submitting.</p>

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

    <h3 class="mt-4">Uploaded Documents:</h3>
    <ul id="uploadedList">
        @foreach($documents as $document)
            <li id="doc-{{ $document->id }}" data-doc-type="{{ $document->document_type }}">
                {{ $document->document_type }} - <strong>{{ basename($document->file_path) }}</strong> -
                <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank">View</a>
                <form action="{{ route('documents.remove', $document->id) }}" method="POST" class="d-inline remove-form">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.doc-checkbox');
    const uploadFieldsContainer = document.getElementById('uploadFields');
    const uploadBtn = document.getElementById('uploadBtn');
    const submitBtn = document.getElementById('submitBtn');

    // Initialize uploadedDocs from the server
    let uploadedDocs = new Set(@json($uploadedDocs));

    function updateUploadFields() {
        uploadFieldsContainer.innerHTML = '';

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const docType = checkbox.value;
                const slug = checkbox.id;

                if (!uploadedDocs.has(docType)) {
                    // Create file input
                    const fileInput = document.createElement('input');
                    fileInput.setAttribute('type', 'file');
                    fileInput.setAttribute('name', `files[]`);
                    fileInput.setAttribute('class', 'form-control upload-input');
                    fileInput.setAttribute('accept', '.pdf,.jpeg,.jpg,.png,.docx');
                    fileInput.dataset.docType = docType;

                    // Create hidden input for document type
                    const hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', `document_types[]`);
                    hiddenInput.value = docType;

                    // Wrap elements in a div
                    const wrapper = document.createElement('div');
                    wrapper.setAttribute('id', `upload-${slug}`);
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
        // Removed the uploadClicked condition so that submit is enabled as soon as all selected docs are uploaded.
        submitBtn.disabled = !(hasCheckedDocs && allUploaded);
    }

    // When a checkbox is toggled, update file inputs accordingly
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateUploadFields);
    });

    // Initialize file inputs on load (in case some checkboxes are pre-checked)
    updateUploadFields();
});
</script>

<script>
document.getElementById('submitBtn').addEventListener('click', function(event) {
    event.preventDefault();
    
    if (!confirm("Are you sure you want to submit your documents?")) {
        return;
    }

   /* fetch("{{ route('documents.checkMissingDocs') }}")
        .then(response => response.json())
        .then(data => {
            let missingDocs = data.missingDocs;
            if (!confirm("Have you uploaded all the required documents?")) {
                let userEmail = prompt(`You are missing: ${missingDocs.join(", ")}. Please enter your email to receive a reminder within 5 days:`);

                if (userEmail) {
                    fetch("{{ route('documents.sendReminder') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ email: userEmail, missingDocs: missingDocs })
                    }).then(() => alert("Reminder set. You must submit missing documents within 5 days."));
                }
            } else {
                document.getElementById('uploadForm').submit();
            }
        });  */
}); 
</script>


@endsection
