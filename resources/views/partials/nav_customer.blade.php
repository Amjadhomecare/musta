<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid "><!-- moved slightly up with mt-2 -->
  <div id="kt_app_content_container" class="app-container" style="max-width: 100%;"> <!-- narrower width -->

    <div class="card card-flush shadow-sm mb-8">
      @php
        use App\Models\customer;
        use App\Models\categoryOne;
        use App\Models\Category4Model;
        use App\Models\registerComplaint;
        use App\Models\AsyncSubStripe;

        $activeP4              = Category4Model::customerActivep4($name);
        $activeP1              = categoryOne::customerActivep1($name);
        $customer              = customer::where('name', $name)->first();
        $complainCount         = registerComplaint::getComplainByCustomer($name);
        $customerSubscriptions = AsyncSubStripe::where('customer_erp', $name)->get();
        $blacklist = customer::where('name', $name)->first()->black_list ?? false;  
      @endphp

      @if ($customer)
        <div class="card-body">
          <div class="row g-4 align-items-start">
            {{-- ID image --}}
            <div class="col-12 col-md-auto text-center">
              @if ($customer->idImg)
                <img src="{{ $customer->idImg }}" class="img-thumbnail rounded" style="max-height:250px" alt="ID Image">
              @else
                <p class="text-muted mb-0">No ID Image Available</p>
              @endif
            </div>

            {{-- Customer details --}}
            <div class="col">
              @if ($blacklist)
                <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                  <i class="ki-outline ki-information-5 fs-2x me-3"></i>
                  <div>
                    <h5 class="mb-0 text-danger fw-bold">⚠️ BLACKLISTED CUSTOMER</h5>
                  </div>
                </div>
              @endif
              <div class="row g-4">
                <div class="col-md-6">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent">
                      <strong>Active:</strong> 
                      @if ($blacklist)
                        <span class="badge badge-danger bg-danger text-white fw-bold">BLACKLISTED - NO</span>
                      @else
                        <span class="badge badge-success bg-success text-white">Yes</span>
                      @endif
                    </li>
                    <li class="list-group-item bg-transparent"><strong >Note:</strong> <strong style="color: red;"> {{ $customer->related }}</strong></li>
                    <li class="list-group-item bg-transparent"><strong>Phone:</strong> {{ $customer->phone }}</li>
                    <li class="list-group-item bg-transparent"><strong>Secondary Phone:</strong> {{ $customer->secondaryPhone ?? 'N/A' }}</li>
                    <li class="list-group-item bg-transparent"><strong>ID Type:</strong> {{ $customer->idType ?? 'N/A' }}</li>
                    <li class="list-group-item bg-transparent"><strong>ID Number:</strong> {{ $customer->idNumber ?? 'N/A' }}</li>
                    <li class="list-group-item bg-transparent"><strong>Created by:</strong> {{ $customer->created_by ?? 'N/A' }}</li>
                    <li class="list-group-item bg-transparent"><strong>Created At:</strong> {{ $customer->created_at?->format('Y-m-d') ?? 'N/A' }}</li>
                  </ul>
                </div>

                <div class="col-md-6">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent"><strong>Nationality:</strong> {{ $customer->nationality ?? 'N/A' }}</li>
                    <li class="list-group-item bg-transparent"><strong>Customer Type:</strong> {{ $customer->customerType ?? 'N/A' }}</li>
                    <li class="list-group-item bg-transparent"><strong>Email:</strong> {{ $customer->email }}</li>
                    <li class="list-group-item bg-transparent"><strong>Address:</strong> {{ $customer->address }}</li>
                    <li class="list-group-item bg-transparent">
                      <button id="copyUrlButton" class="btn btn-outline-primary btn-sm" onclick="copyCurrentUrl()">
                        <i class="fa fa-copy me-1"></i>Copy URL
                      </button>
                    </li>
                  </ul>
                </div>

                <div class="col-md-6">
                  @if ($customerSubscriptions->count())
                    <div class="border rounded p-2 overflow-auto" style="max-height: 280px;">
                      <ul class="list-group list-group-flush small">
                        @foreach ($customerSubscriptions as $sub)
                          <li class="list-group-item bg-transparent">
                            <strong>Sub ID:</strong>
                            <a href="https://dashboard.stripe.com/subscriptions/{{ $sub->sub_id }}" target="_blank" class="link-primary">{{ $sub->sub_id }}</a><br>
                            <strong>Cus ID:</strong>
                            <a href="https://dashboard.stripe.com/customers/{{ $sub->cus_id }}" target="_blank" class="link-primary">{{ $sub->cus_id }}</a><br>
                            <strong>Status:</strong> {{ ucfirst($sub->status) }}<br>
                            <strong>Created:</strong> {{ $sub->created_date }}
                          </li>
                        @endforeach
                      </ul>
                    </div>
                  @else
                    <p class="text-muted">No Stripe subscriptions found.</p>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Navigation --}}
        <div class="card-body border-top">
<ul class="nav nav-pills justify-content-center flex-wrap gap-2">
    @foreach ([
        'customer/report/' . $name               => 'Package One ('.$activeP1.')',
        'customer/report/p4/' . $name            => 'Package Four ('.$activeP4.')',
        'page/invoices/' . $name                 => 'Invoices',
        'customer/soa/' . $name                  => 'Statement',
        'page/customer/attachment/' . $name      => 'Attachment',

    ] as $url => $label)
        <li class="nav-item">
            <a class="btn btn-light-primary btn-sm{{ request()->is($url) ? ' active' : '' }}" href="/{{ $url }}">{{ $label }}</a>
        </li>
    @endforeach

    {{-- Hide these if blacklisted --}}
    @if (!$blacklist)
        @foreach([
            'customer/make/p1/' . $name => 'Make P1',
            'customer/make/p4/' . $name => 'Make P4'
        ] as $url => $label)
            <li class="nav-item">
                <a class="btn btn-light-primary btn-sm{{ request()->is($url) ? ' active' : '' }}" href="/{{ $url }}">{{ $label }}</a>
            </li>
        @endforeach
    @endif

    @foreach([
        'installment-p4/' . $name             => 'Installment',
        'adv-customer/'.$name                 => 'Advance',
        'cus-comp/'.$name                     => 'Complain ('.$complainCount.')',
      

        'vue/sign-list?search='.$name         => 'Signature',

    ] as $url => $label)
        <li class="nav-item">
            <a class="btn btn-light-primary btn-sm{{ request()->is($url) ? ' active' : '' }}" href="/{{ $url }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>


        </div>

      @else
        <div class="alert alert-warning m-4 text-center">Customer not found.</div>
      @endif
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->
<script>

function copyCurrentUrl() {
    const currentUrl = window.location.href; 

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(currentUrl)
            .then(() => {
                alert("URL copied!");
            })
            .catch(err => {
                console.error("Failed to copy: ", err);
                alert("Failed to copy the URL.");
            });
    } else {
      
        const tempInput = document.createElement('input');
        tempInput.value = currentUrl;
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            document.execCommand('copy');
            alert("URL copied (fallback method)!");
        } catch (err) {
            console.error("Failed to copy (fallback): ", err);
            alert("Failed to copy the URL.");
        }
        document.body.removeChild(tempInput);
    }
}

</script>