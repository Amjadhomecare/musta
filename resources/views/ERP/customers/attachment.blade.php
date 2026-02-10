@extends('keen')
@section('content')

@include('partials.nav_customer')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <!--begin::Content container-->
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Attachments card ───────── --}}
    <div class="card card-flush shadow-sm">

      {{-- Header --}}
      <div class="card-header">
        <h4 class="card-title mb-0 flex-grow-1 text-center"
            id="customer-name"
            data-name="{{ $name }}">
          Attachment: {{ $name }}
        </h4>
      </div>

      {{-- Body --}}
      <div class="card-body">

        {{-- Upload form --}}
        <div class="border rounded p-4 mb-5">
          <h6 class="fw-semibold mb-3">Upload Attachment</h6>

          <div id="message-container"></div>

          <form id="uploadForm" class="row g-3">
            @csrf

            <div class="col-12 col-md-6">
              <label class="form-label">File</label>
              <input type="file" class="form-control form-control-sm form-control-solid"
                     id="file" name="file" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Customer&nbsp;Name</label>
              <input type="text" class="form-control form-control-sm form-control-solid"
                     id="maid_name" name="customer_name" value="{{ $name }}" readonly>
            </div>

            <div class="col-12">
              <label class="form-label">Note</label>
              <textarea id="note" name="note" rows="3"
                        class="form-control form-control-sm form-control-solid"
                        placeholder="Enter note" required></textarea>
            </div>

            <div class="col-12 text-end pt-2">
              <button type="submit" class="btn btn-primary btn-sm">
                Upload
              </button>
            </div>
          </form>
        </div>

        {{-- Attachments table --}}
        <div class="table-responsive">
          <table id="customer_atthach"
                 class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Customer&nbsp;Name</th>
                <th>Note</th>
                <th>File&nbsp;Path</th>
                <th>Created&nbsp;At</th>
                <th>Created&nbsp;By</th>
              </tr>
            </thead>
          </table>
        </div>

      </div><!-- /card-body -->
    </div>
    {{-- ───────── /Attachments card ───────── --}}

  </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->

        
<script>
document.getElementById('uploadForm').addEventListener('submit', async function(event) {
    event.preventDefault(); 

    let formData = new FormData(this); 

    try {
        let response = await fetch('{{ url("/customer/upload-attachment") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        });

        let result = await response.json();

        let messageContainer = document.getElementById('message-container');
        messageContainer.innerHTML = '';

        if (response.ok) {
            // Success message
            messageContainer.innerHTML = 
                `<div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${result.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            location.reload();

        } else {
            // Failure message
            messageContainer.innerHTML = 
                `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${result.message || 'File upload failed. Please try again.'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('message-container').innerHTML = 
            `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                An error occurred while uploading the file.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
    }
});

</script>



@endsection

@push('scripts')
    @vite('resources/js/customers/customer_attach.js')
@endpush
