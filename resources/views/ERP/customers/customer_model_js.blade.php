<!-- Add New Customer CV Modal -->
<div id="customer-form-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer CV</h5>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="customerForm" enctype="multipart/form-data" class="px-3">
                    @csrf
                    <div class="row">
                        <!-- Column 1 -->
                        <div class="col-md-6">

                        <div class="mb-3">
                            <label for="eidImgOcr" class="form-label">Extract from Emirates ID Image</label>
                            <input type="file" class="form-control" id="eidImgOcr" accept="image/*">
                            <small class="form-text text-muted">Upload Emirates ID to auto-fill Name, ID Number, and Nationality.</small>
                        </div>
                        <textarea id="ocrDebug" style="display:none;" class="form-control mb-2" rows="3"></textarea>

                            <!-- Name Field -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" pattern="[A-Za-z\s.,!?]*" title="Only English letters, spaces, and basic punctuation are allowed." class="form-control" id="name" name="name" required>
                            </div>

                                             <!-- Phone Field -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>

                               <!-- Secondary Phone Field -->
                             <div class="mb-3">
                                <label for="secondaryPhone" class="form-label">Secondary Phone</label>
                                <input type="text" class="form-control" id="secondaryPhone" name="secondaryPhone" >
                            </div>

                                <!-- ID Type Field -->
                        <div class="mb-3">
                                <label for="idType" class="form-label">ID Type</label>
                                <input type="text" class="form-control" id="idType" name="idType" >
                            </div>
                      


                            <!-- Related Field -->
                            <div class="mb-3">
                                <label for="related" class="form-label">Related</label>
                                <input type="text" class="form-control" id="related" name="related">
                            </div>

                            <!-- Note Field -->
                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <input type="text" class="form-control" id="note" name="note">
                            </div>

           
                         
                        
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">

                            <!-- ID Number Field -->
                            <div class="mb-3">
                                <label for="idNumber" class="form-label">ID Number</label>
                                <input type="text" class="form-control" id="idNumber" name="idNumber">
                            </div>

                            <!-- Nationality Field -->
                            <div class="mb-3">
                                <label for="nationality" class="form-label">Nationality</label>
                                <input type="text" class="form-control" id="nationality" name="nationality">
                            </div>

                            <!-- Customer Type Field -->
                            <div class="mb-3">
                                <label for="cusomerType" class="form-label">Customer Type</label>
                                <input type="text" class="form-control" id="cusomerType" name="cusomerType">
                            </div>

                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>

                            <!-- Address Field -->
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address">
                            </div>

                            <!-- ID Image Field -->
                            <div class="mb-3">
                                <label for="idImg" class="form-label">ID Image</label>
                                <input type="file" class="form-control" id="idImg" name="idImg" >
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="mb-3 text-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<!-- edit customer modal -->
<div id="edit_customer_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer Details</h5>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="customerFormEdit" class="px-3" enctype="multipart/form-data">
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="row">
                        <input type="hidden" name="edit_cus_id" id="edit_cus_id" class="form-control mb-3">

                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit_cus_name">Name :</label>
                                <input  type="text" class="form-control" id="edit_cus_name" name="edit_cus_name">
                            </div>


                            <div class="form-group mb-3">
                                <label for="edit_cus_related">Related :</label>
                                <input type="text" class="form-control" id="edit_cus_related" name="edit_cus_related" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_note">Note :</label>
                                <input type="text" class="form-control" id="edit_cus_note" name="edit_cus_note" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_nationality">Nationality :</label>
                                <input type="text" class="form-control" id="edit_cus_nationality" name="edit_cus_nationality" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_type">Customer Type :</label>
                                <input type="text" class="form-control" id="edit_cus_type" name="edit_cus_type" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_address">Address :</label>
                                <input type="text" class="form-control" id="edit_cus_address" name="edit_cus_address" required>
                            </div>
                   
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">

                            <div class="form-group mb-3">
                                <label for="edit_cus_phone">Phone :</label>
                                <input type="text" class="form-control" id="edit_cus_phone" name="edit_cus_phone" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_sPhone">Secondary Phone :</label>
                                <input type="text" class="form-control" id="edit_cus_sPhone" name="edit_cus_sPhone" >
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_ID_type">ID Type :</label>
                                <input type="text" class="form-control" id="edit_cus_ID_type" name="edit_cus_ID_type" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_ID_num">ID Number :</label>
                                <input type="text" class="form-control" id="edit_cus_ID_num" name="edit_cus_ID_num" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_cus_email">Email :</label>
                                <input type="text" class="form-control" id="edit_cus_email" name="edit_cus_email" required>
                            </div>


                            <!-- ID Image Display Field -->
                            <div class="mb-3">
                                <label for="edit_cus_ID_img" class="form-label">ID Image :</label>
                                <img id="current_cus_ID_img" src="" class="img-fluid mb-2" alt="ID Image" style="max-width: 50px;"> <!-- Add this line -->
                                <input type="file" class="form-control" id="edit_cus_ID_input" name="edit_cus_ID_img">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Send Blacklist SMS Checkbox -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="send_blacklist_sms" name="send_blacklist_sms" value="1">
                        <label class="form-check-label" for="send_blacklist_sms">
                            Send Blacklist SMS to The Managment
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary submit-edit-customer">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>




@section('scriptForCategory4contracts')
@endsection

@push('scripts')


<script>
document
  .getElementById('eidImgOcr')
  .addEventListener('change', async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    // ----- build multipart/form-data -----
    const fd = new FormData();
    fd.append('image', file);

    // 👉 add the CSRF token expected by /ocr/azure
    const csrf = document
                  .querySelector('meta[name="csrf-token"]')
                  .getAttribute('content');
    fd.append('_token', csrf);                 // you could also send as header

    // ----- UI feedback -----
    const debug = document.getElementById('ocrDebug');
    debug.style.display = 'block';
    debug.value = 'Uploading image please wait....… ';

    try {
      const res = await fetch('/ocr/azure', { method: 'POST', body: fd });
      if (!res.ok) throw new Error('HTTP ' + res.status);

      const { rawText, data } = await res.json();
      debug.value = rawText || '(no text)';

      if (data.name)        document.getElementById('name').value        = data.name;
      if (data.id)          document.getElementById('idNumber').value    = data.id;
      if (data.nationality) document.getElementById('nationality').value = data.nationality;

    } catch (err) {
      console.error(err);
      debug.value = 'OCR failed – see console.';
    }
  });
</script>
@endpush
