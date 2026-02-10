@extends('keen')

@section('content')
@include('partials.nav_maid')

<div class="container py-3">
    <div class="row">
        <div class="col-md-10 offset-md-1">

            {{-- Upload Attachment --}}
            <div class="card mt-2">
                <div class="card-header">
                    Upload Attachment
                </div>
                <div class="card-body">
                    <div id="message-container"></div>

                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">File</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>

                        <div class="mb-3">
                            <label for="maid_name" class="form-label">Maid Name</label>
                            <input
                                type="text"
                                class="form-control"
                                id="maid_name"
                                name="maid_name"
                                value="{{ $maid->name }}"
                                readonly
                            >
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note" rows="3" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>

            {{-- Attachments --}}
            <div class="card mt-3">
                <div class="card-header">
                    Attachments
                </div>
                <div class="card-body p-0">
                    @if(empty($maid->maidAttachment) || $maid->maidAttachment->isEmpty())
                        <div class="p-3 text-muted">No attachments found.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead >
                                    <tr>
                                        <th>File Name</th>
                                        <th>Note</th>
                                        <th>File Type</th>
                                        <th>Uploaded By</th>
                                        <th>Created At</th>
                                        <th>View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($maid->maidAttachment as $attachment)
                                        <tr>
                                            <td>{{ $attachment->file_name }}</td>
                                            <td>{{ $attachment->note }}</td>
                                            <td>{{ $attachment->file_type }}</td>
                                            <td>{{ $attachment->created_by }}</td>
                                            <td>{{ \Carbon\Carbon::parse($attachment->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ $attachment->file_path }}" target="_blank">View File</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- JS Upload --}}
<script>
document.getElementById('uploadForm').addEventListener('submit', async function(event) {
    event.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const messageContainer = document.getElementById('message-container');
    messageContainer.innerHTML = '';

    try {
        const response = await fetch('{{ url("/maids/upload-attachment") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
            }
        });

        const result = await response.json();

        if (response.ok) {
            messageContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${result.message ?? 'File uploaded successfully.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            window.location.reload();
        } else {
            messageContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${result.message ?? 'File upload failed.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        messageContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                An error occurred while uploading the file.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
    }
});
</script>
@endsection
