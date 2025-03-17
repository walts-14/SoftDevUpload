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
    const uploadedList = document.getElementById('uploadedList');

    // Store uploaded documents in a Set for quick lookup
    let uploadedDocs = new Set(
        Array.from(uploadedList.children)
            .map(li => li.getAttribute('data-doc-type'))
    );

    // Save checked checkboxes to local storage
    function saveCheckboxState() {
        const checkedDocs = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        localStorage.setItem('checkedDocuments', JSON.stringify(checkedDocs));
    }

    // Restore previously checked checkboxes
    function restoreCheckboxState() {
        const storedDocs = JSON.parse(localStorage.getItem('checkedDocuments') || '[]');
        checkboxes.forEach(cb => {
            if (storedDocs.includes(cb.value)) {
                cb.checked = true;
                cb.dispatchEvent(new Event('change'));
            }
        });
    }

    // Function to check if all selected documents are uploaded
    function updateSubmitButton() {
        const checkedDocs = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        const missingUploads = checkedDocs.filter(doc => !uploadedDocs.has(doc));

        submitBtn.disabled = missingUploads.length > 0;
    }

    // Event listener for checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            saveCheckboxState();
            const docType = this.value;
            const slug = this.id;

            if (this.checked && !uploadedDocs.has(docType)) {
                if (!document.getElementById(`upload-${slug}`)) {
                    const uploadDiv = document.createElement('div');
                    uploadDiv.setAttribute('id', `upload-${slug}`);
                    uploadDiv.innerHTML = `
                        <label>${docType} (PDF, JPEG, PNG, DOCX, max 10MB):</label>
                        <input type="file" name="files[${slug}]" class="form-control upload-input" accept=".pdf,.jpeg,.jpg,.png,.docx">
                        <p class="text-danger error-${slug}" style="display: none;">Invalid file!</p>
                    `;
                    uploadFieldsContainer.appendChild(uploadDiv);
                }
            } else {
                document.getElementById(`upload-${slug}`)?.remove();
            }
            updateSubmitButton();
        });
    });

    // Event listener for file uploads
    uploadFieldsContainer.addEventListener('change', function (e) {
        if (e.target.classList.contains('upload-input')) {
            const file = e.target.files[0];
            const docType = e.target.name.replace('files[', '').replace(']', '');
            const errorElement = document.querySelector(`.error-${docType}`);
            const uploadField = document.getElementById(`upload-${docType}`);

            if (file) {
                const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!validTypes.includes(file.type) || file.size > 10 * 1024 * 1024) {
                    errorElement.style.display = 'block';
                    e.target.value = "";
                    uploadBtn.disabled = true;
                } else {
                    errorElement.style.display = 'none';
                    uploadBtn.disabled = false;

                    // Simulating successful upload (Replace with AJAX if necessary)
                    setTimeout(() => {
                        uploadedDocs.add(docType);
                        updateSubmitButton();
                        
                        // **REMOVE file input after upload**
                        uploadField?.remove();
                    }, 1000);
                }
            }
        }
    });

    // Initialize state
    restoreCheckboxState();
    updateSubmitButton();
});



</script>

@endsection
