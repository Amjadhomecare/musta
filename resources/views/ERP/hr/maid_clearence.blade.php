<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Clearance Employee</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Mobile styles for signature canvas */
    canvas {
      display: block;
    }
    
    @media (max-width: 768px) {
      #signatureModal {
        padding: 0;
        align-items: flex-start;
      }
      
      #signatureModal .max-w-4xl {
        max-width: 100vw;
        width: 100vw;
        height: 100vh;
        margin: 0;
        padding: 1rem;
        border-radius: 0;
        overflow-y: auto;
      }
      
      canvas {
        width: 100% !important;
        height: 150px !important;
        touch-action: none;
        border: 2px solid #ccc;
        margin-bottom: 0.5rem;
      }
      
      .space-y-4 > div {
        margin-bottom: 1.5rem;
      }
      
      label {
        font-size: 1.1rem;
        font-weight: bold;
      }
    }
    
    @media print {
      /* Better print layout - readable and fits one page */
      @page {
        size: A4 portrait;
        margin: 8mm;
      }
      
      html, body {
        margin: 0;
        padding: 0;
      }
      
      body {
        transform: scale(0.95);
        transform-origin: top center;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        font-size: 11px;
      }
      
      /* Hide all buttons */
      button,
      #editBtn,
      #saveBtn,
      #cancelBtn,
      #signBtn,
      .btn-clear {
        display: none !important;
      }
      
      /* Compact layout */
      .max-w-4xl {
        max-width: 100% !important;
        padding: 8px !important;
      }
      
      .p-8 {
        padding: 6px !important;
      }
      
      .p-6, .p-4 {
        padding: 4px !important;
      }
      
      /* Smaller image */
      img {
        max-width: 90px !important;
        max-height: 90px !important;
      }
      
      /* Compact spacing */
      .mt-4, .mt-6 {
        margin-top: 0.25rem !important;
      }
      
      .mb-4, .mb-6 {
        margin-bottom: 0.25rem !important;
      }
      
      .gap-4 {
        gap: 0.5rem !important;
      }
      
      /* Font sizes */
      h1 {
        font-size: 16px !important;
        margin: 4px 0 !important;
      }
      
      h2 {
        font-size: 14px !important;
        margin: 4px 0 !important;
      }
      
      h3 {
        font-size: 12px !important;
      }
      
      table {
        font-size: 10px !important;
      }
      
      /* Compact table */
      .px-2 {
        padding-left: 3px !important;
        padding-right: 3px !important;
      }
      
      .py-1 {
        padding-top: 2px !important;
        padding-bottom: 2px !important;
      }
      
      /* Signatures */
      .h-24 {
        height: 60px !important;
      }
      
      /* Avoid page breaks */
      table, .grid {
        page-break-inside: avoid !important;
      }
    }
  </style>
</head>
<body class="bg-white text-black font-sans p-8">
  <div class="max-w-4xl mx-auto border border-black p-6">
    <!-- Header -->
    <h1 class="text-center text-xl font-bold"> {{env( 'company_name') ?? ''}} </h1>
    <h2 class="text-center text-lg font-bold mt-2 border-y border-black py-1">مخالصة موظف<br/>CLEARANCE EMPLOYEE</h2>

    <!-- Number and Title -->
<div class="flex justify-between items-center mt-4 text-lg bg-white p-4 rounded-lg shadow">
  <div class="font-bold">NO: 00{{ $m->id }}</div>
  <div class="text-red-600 font-bold text-xl">Housemaid</div>
  <div> Count: {{ $m?->note }}</div>

  <div class="ml-6">
    <img src="{{ $m?->maid?->img }}" 
         alt="Maid Image" 
         class="w-40 h-40 object-cover rounded-full border-2 border-gray-300 shadow-md hover:scale-105 transition-transform duration-200" />
  </div>
</div>


    <!-- Information Table -->
    <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
      <div>
        <p><span class="font-bold">Name:</span> {{ $m?->maid_name}}</p>
        <p><span class="font-bold">Job Title:</span> DOMESTIC CLEANER</p>
        <p><span class="font-bold">Vac-Reason:</span> {{$m?->reason }}</p>
        <p><span class="font-bold">Last entry:</span> <span class="bg-yellow-200 px-1">{{ $m?->last_entry_date}}</span></p>
        <p><span class="font-bold">Date of travel:</span> <span class="bg-yellow-200 px-1">  {{ $m?->travel_date}}  </span></p>

        <p>
          <span class="font-bold">DAYS:</span>
        {{ number_format(\Carbon\Carbon::parse($m?->last_entry_date)->diffInDays($m?->created_at)) }}

        </p>



        <p><span class="font-bold">Salary :dh</span> <span class="bg-yellow-200 px-1"> {{ $m->maid?->salary}}</span></p>
        <p><span class="font-bold text-lg">Basic salary:</span>  {{ $m?->salary_dh}}</p>
      </div>
      <div>
        <p><span class="font-bold">Employee ID:</span> {{ $m->maid?->uae_id_maid }} </p>
        <p><span class="font-bold">Nationality:</span> {{ $m->maid?->nationality }}</p>
        <p><span class="font-bold">Pass No:</span>{{ $m->maid?->passport_number }} </p>
        <p><span class="font-bold">Pass.EXP:</span> {{ $m->maid?->passport_exp_date}}</p>
        <!-- <p><span class="font-bold">RP.NO:</span> 2745631</p>
        <p><span class="font-bold">RP.EXP:</span> 2025-05-24</p> -->
      </div>
    </div>

    <!-- Financial Table -->
    <table class="w-full mt-6 text-sm border border-black border-collapse">
      <thead>
        <tr class="bg-gray-100">
          <th class="border border-black px-2 py-1 text-left">Allowance</th>
          <th class="border border-black px-2 py-1 text-left">Details</th>
          <th class="border border-black px-2 py-1 text-left">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr data-field="allowance">
          <td class="border border-black px-2 py-1">
            <span class="display-mode">Leave allowance</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="allowance_label" value="Leave allowance" />
          </td>
          <td class="border border-black px-2 py-1">
            <span class="display-mode">60 days salary for 2 years</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="allowance_details" value="60 days salary for 2 years" />
          </td>
          <td class="border border-black px-2 py-1 bg-yellow-200">
            <span class="display-mode">{{$m?->allowance}}</span>
            <input type="number" step="0.01" class="edit-mode hidden border-0 w-full px-1" name="allowance" value="{{$m?->allowance}}" />
          </td>
        </tr>
        <tr data-field="ticket">
          <td class="border border-black px-2 py-1">
            <span class="display-mode">Tickets</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="ticket_label" value="Tickets" />
          </td>
          <td class="border border-black px-2 py-1">
            <span class="display-mode">On company</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="ticket_details" value="On company" />
          </td>
          <td class="border border-black px-2 py-1">
            <span class="display-mode">{{ $m?->ticket }}</span>
            <input type="number" step="0.01" class="edit-mode hidden border-0 w-full px-1" name="ticket" value="{{ $m?->ticket }}" />
          </td>
        </tr>
        @foreach($m->clearance_items_with_defaults as $index => $item)
        <tr data-index="{{ $index }}">
          <td class="border border-black px-2 py-1">
            <span class="display-mode">{{ $item['label'] }}</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="label" value="{{ $item['label'] }}" />
          </td>
          <td class="border border-black px-2 py-1">
            <span class="display-mode">{{ $item['details'] }}</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="details" value="{{ $item['details'] }}" />
          </td>
          <td class="border border-black px-2 py-1">
            <span class="display-mode">{{ $item['amount'] }}</span>
            <input type="number" step="0.01" class="edit-mode hidden border-0 w-full px-1" name="amount" value="{{ $item['amount'] }}" />
          </td>
        </tr>
        @endforeach
        <tr data-field="dedcution">
          <td class="border border-black px-2 py-1">
            <span class="display-mode">Settlements</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="dedcution_label" value="Settlements" />
          </td>
          <td class="border border-black px-2 py-1">
            <span class="display-mode">-</span>
            <input type="text" class="edit-mode hidden border-0 w-full px-1" name="dedcution_details" value="-" />
          </td>
          <td class="border border-black px-2 py-1 bg-red-500 text-white">
            <span class="display-mode">{{ $m?->dedcution  ?? 0}}</span>
            <input type="number" step="0.01" class="edit-mode hidden border-0 w-full px-1 text-black" name="dedcution" value="{{ $m?->dedcution  ?? 0}}" />
          </td>
        </tr>

          <tr>
          <td class="border border-black px-2 py-1">Remaining amount</td>
          <td class="border border-black px-2 py-1">-</td>
          <td class="border border-black px-2 py-1 bg-red-500 text-white">{{ $m?->remaining_amount  ?? 0}}</td>
        </tr>
        <tr class="font-bold bg-yellow-200">
          <td class="border border-black px-2 py-1" colspan="2">Total</td>
          <td class="border border-black px-2 py-1">
            @php
              $clearanceTotal = collect($m->clearance_items_with_defaults)->sum('amount');
              $total = ($m?->allowance ?? 0) + ($m?->ticket ?? 0) + $clearanceTotal - ($m?->dedcution ?? 0);
            @endphp
            {{ number_format($total, 2) }}
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Edit Button -->
    <div class="mt-4 flex gap-2">
      <button id="editBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Edit Clearance Items
      </button>
      <button id="saveBtn" class="hidden bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        Save Changes
      </button>
      <button id="cancelBtn" class="hidden bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
        Cancel
      </button>
    </div>

<!-- Confirmation Section -->
<p class="text-sm mt-4 leading-relaxed text-left">
  I, the undersigned <span class="font-bold">{{ $m?->maid->name }}</span>, hereby confirm that I have received all my dues as stated above under payment voucher number ( pv-00 {{ $m?->id }}). <br>
  Dated: <span class="font-bold">{{ $m?->created_at }} </span>.  I have no further claims as of this date.
</p>

<p>
Created_by : {{ $m?->created_by}}

<br>
Updated_by : {{ $m?->updated_by}} 
@if($m?->updated_at)
  <span class="text-gray-600">on {{ $m->updated_at->format('Y-m-d H:i:s') }}</span>
@endif
</p>

 
@php
  $signatures = isset($m->clearance_items['signatures']) ? $m->clearance_items['signatures'] : null;
@endphp

<div class="mt-6">
  <div class="flex justify-between items-center mb-2">
    <h3 class="text-lg font-bold">Signatures</h3>
    <button id="signBtn" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
      @if($signatures)
        Re-sign Document
      @else
        Add Signatures
      @endif
    </button>
  </div>

  <div class="grid grid-cols-3 gap-4 text-center text-sm font-bold">
    <div>
      HR. MANAGER
      <div class="border border-black h-24 mt-2 flex items-center justify-center bg-gray-50">
        @if($signatures && isset($signatures['hr_manager']))
          <img src="{{ $signatures['hr_manager'] }}" alt="HR Signature" class="max-h-full max-w-full object-contain" />
        @else
          <span class="text-gray-400 text-xs">No signature yet</span>
        @endif
      </div>
    </div>
    <div>
      EMPLOYEE SIGNATURE
      <div class="border border-black h-24 mt-2 flex items-center justify-center bg-gray-50">
       @if($signatures && isset($signatures['employee']))
          <img src="{{ $signatures['employee'] }}" alt="Employee Signature" class="max-h-full max-w-full object-contain" />
        @else
          <span class="text-gray-400 text-xs">No signature yet</span>
        @endif
      </div>
    </div>
    <div>
      G. Manager
      <div class="border border-black h-24 mt-2 flex items-center justify-center bg-gray-50">
        @if($signatures && isset($signatures['general_manager']))
          <img src="{{ $signatures['general_manager'] }}" alt="GM Signature" class="max-h-full max-w-full object-contain" />
        @else
          <span class="text-gray-400 text-xs">No signature yet</span>
        @endif
      </div>
    </div>
  </div>
  
  @if($signatures)
    <p class="text-xs text-gray-600 mt-2">
      Signed by {{ $signatures['signed_by'] ?? 'Unknown' }} on {{ isset($signatures['signed_at']) ? \Carbon\Carbon::parse($signatures['signed_at'])->format('Y-m-d H:i') : 'Unknown' }}
    </p>
  @endif
</div>

<!-- Signature Modal -->
<div id="signatureModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="z-index: 1000; display: none;">
  <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Draw Signatures</h2>
      <div class="flex gap-2">
        <button id="saveSignaturesTop" class="bg-green-500 hover:bg-green-700 text-white font-bold px-4 py-1 rounded text-sm">
          Save
        </button>
        <button id="closeModalX" class="text-gray-500 hover:text-gray-700 text-3xl font-bold leading-none">&times;</button>
      </div>
    </div>
    
    <div class="space-y-4">
      <!-- HR Manager Signature -->
      <div>
        <label class="block font-semibold mb-2">HR Manager Signature</label>
        <canvas id="hrCanvas" class="border-2 border-gray-300 rounded w-full" style="max-width: 100%; height: 200px;"></canvas>
        <button type="button" class="btn-clear mt-2 bg-gray-300 px-3 py-1 rounded text-sm" data-canvas="hrCanvas">Clear</button>
      </div>
      
      <!-- Employee Signature -->
      <div>
        <label class="block font-semibold mb-2">Employee Signature</label>
        <canvas id="empCanvas" class="border-2 border-gray-300 rounded w-full" style="max-width: 100%; height: 200px;"></canvas>
        <button type="button" class="btn-clear mt-2 bg-gray-300 px-3 py-1 rounded text-sm" data-canvas="empCanvas">Clear</button>
      </div>
      
      <!-- GM Signature -->
      <div>
        <label class="block font-semibold mb-2">General Manager Signature</label>
        <canvas id="gmCanvas" class="border-2 border-gray-300 rounded w-full" style="max-width: 100%; height: 200px;"></canvas>
        <button type="button" class="btn-clear mt-2 bg-gray-300 px-3 py-1 rounded text-sm" data-canvas="gmCanvas">Clear</button>
      </div>
    </div>
    
    <div class="flex flex-col sm:flex-row gap-3 mt-6">
      <button id="saveSignatures" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded text-lg">
        Save All Signatures
      </button>
      <button id="closeModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded text-lg">
        Cancel
      </button>
    </div>
  </div>
</div>

  </div>

  <script>
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    let editMode = false;

    editBtn.addEventListener('click', () => {
      editMode = true;
      document.querySelectorAll('.display-mode').forEach(el => el.classList.add('hidden'));
      document.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
      editBtn.classList.add('hidden');
      saveBtn.classList.remove('hidden');
      cancelBtn.classList.remove('hidden');
    });

    cancelBtn.addEventListener('click', () => {
      editMode = false;
      document.querySelectorAll('.display-mode').forEach(el => el.classList.remove('hidden'));
      document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('hidden'));
      editBtn.classList.remove('hidden');
      saveBtn.classList.add('hidden');
      cancelBtn.classList.add('hidden');
      
      // Reset values to original
      document.querySelectorAll('tr[data-index]').forEach(row => {
        const displaySpans = row.querySelectorAll('.display-mode');
        const editInputs = row.querySelectorAll('.edit-mode');
        editInputs.forEach((input, i) => {
          input.value = displaySpans[i].textContent.trim();
        });
      });
    });

    saveBtn.addEventListener('click', async () => {
      const clearanceItems = [];
      document.querySelectorAll('tr[data-index]').forEach(row => {
        const inputs = row.querySelectorAll('.edit-mode');
        clearanceItems.push({
          label: inputs[0].value,
          details: inputs[1].value,
          amount: parseFloat(inputs[2].value) || 0
        });
      });

      // Get other editable fields
      const allowanceRow = document.querySelector('tr[data-field="allowance"]');
      const ticketRow = document.querySelector('tr[data-field="ticket"]');
      const dedcutionRow = document.querySelector('tr[data-field="dedcution"]');

      const allowance = parseFloat(allowanceRow.querySelector('input[name="allowance"]').value) || 0;
      const ticket = parseFloat(ticketRow.querySelector('input[name="ticket"]').value) || 0;
      const dedcution = parseFloat(dedcutionRow.querySelector('input[name="dedcution"]').value) || 0;

      try {
        const response = await fetch('{{ route("clearance.update_items", $m->id) }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ 
            clearance_items: clearanceItems,
            allowance: allowance,
            ticket: ticket,
            dedcution: dedcution
          })
        });

        const data = await response.json();
        
        if (data.status === 'success') {
          alert('Clearance items updated successfully!');
          location.reload(); // Reload to show updated values
        } else {
          alert('Error updating clearance items');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving');
      }
    });

    // Signature Pad Functionality
    const signBtn = document.getElementById('signBtn');
    const signatureModal = document.getElementById('signatureModal');
    const closeModal = document.getElementById('closeModal');
    const saveSignatures = document.getElementById('saveSignatures');

    // Setup canvases
    const hrCanvas = document.getElementById('hrCanvas');
    const empCanvas = document.getElementById('empCanvas');
    const gmCanvas = document.getElementById('gmCanvas');

    const canvases = [hrCanvas, empCanvas, gmCanvas];
    const contexts = {};
    const isDrawing = {};

    // Function to initialize canvas
    function initializeCanvas(canvas) {
      // Get the actual displayed width
      const rect = canvas.getBoundingClientRect();
      canvas.width = rect.width || 600;
      canvas.height = 200;
      
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = 'white';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      
      contexts[canvas.id] = ctx;
      isDrawing[canvas.id] = false;

      ctx.lineWidth = 2;
      ctx.lineCap = 'round';
      ctx.strokeStyle = '#000';
    }

    // Initialize canvases
    canvases.forEach(canvas => {
      contexts[canvas.id] = canvas.getContext('2d');
      isDrawing[canvas.id] = false;

      // Mouse events
      canvas.addEventListener('mousedown', (e) => startDrawing(e, canvas));
      canvas.addEventListener('mousemove', (e) => draw(e, canvas));
      canvas.addEventListener('mouseup', () => stopDrawing(canvas));
      canvas.addEventListener('mouseout', () => stopDrawing(canvas));

      // Touch events for mobile
      canvas.addEventListener('touchstart', (e) => {
        e.preventDefault();
        const touch = e.touches[0];
        const rect = canvas.getBoundingClientRect();
        const mouseEvent = new MouseEvent('mousedown', {
          clientX: touch.clientX,
          clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
      });
      canvas.addEventListener('touchmove', (e) => {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
          clientX: touch.clientX,
          clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
      });
      canvas.addEventListener('touchend', (e) => {
        e.preventDefault();
        const mouseEvent = new MouseEvent('mouseup', {});
        canvas.dispatchEvent(mouseEvent);
      });
    });

    function startDrawing(e, canvas) {
      isDrawing[canvas.id] = true;
      const rect = canvas.getBoundingClientRect();
      const ctx = contexts[canvas.id];
      ctx.beginPath();
      ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }

    function draw(e, canvas) {
      if (!isDrawing[canvas.id]) return;
      const rect = canvas.getBoundingClientRect();
      const ctx = contexts[canvas.id];
      ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
      ctx.stroke();
    }

    function stopDrawing(canvas) {
      isDrawing[canvas.id] = false;
    }

    // Clear buttons
    document.querySelectorAll('.btn-clear').forEach(btn => {
      btn.addEventListener('click', () => {
        const canvasId = btn.getAttribute('data-canvas');
        const canvas = document.getElementById(canvasId);
        const ctx = contexts[canvasId];
        ctx.clearRect(0, 0, canvas.width, canvas.height);
      });
    });

    // Open modal
    signBtn.addEventListener('click', () => {
      signatureModal.style.display = 'flex';
      
      // Initialize canvases after modal is visible
      setTimeout(() => {
        canvases.forEach(canvas => initializeCanvas(canvas));
      }, 100);
    });

    // Close modal
    closeModal.addEventListener('click', () => {
      signatureModal.style.display = 'none';
    });
    
    // Close modal with X button
    document.getElementById('closeModalX').addEventListener('click', () => {
      signatureModal.style.display = 'none';
    });

    // Save signatures function
    async function saveSignaturesHandler() {
      // Helper function to check if canvas is blank
      function isCanvasBlank(canvas) {
        const ctx = canvas.getContext('2d');
        const pixelBuffer = new Uint32Array(
          ctx.getImageData(0, 0, canvas.width, canvas.height).data.buffer
        );
        return !pixelBuffer.some(color => color !== 0 && color !== 0xFFFFFFFF);
      }

      // Only get signatures that have been drawn
      const signatures = {};
      
      if (!isCanvasBlank(hrCanvas)) {
        signatures.hr_signature = hrCanvas.toDataURL('image/png');
      }
      
      if (!isCanvasBlank(empCanvas)) {
        signatures.employee_signature = empCanvas.toDataURL('image/png');
      }
      
      if (!isCanvasBlank(gmCanvas)) {
        signatures.gm_signature = gmCanvas.toDataURL('image/png');
      }

      // Check if at least one signature was drawn
      if (Object.keys(signatures).length === 0) {
        alert('Please draw at least one signature before saving.');
        return;
      }

      try {
        const response = await fetch('{{ route("clearance.save_signatures", $m->id) }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(signatures)
        });

        const data = await response.json();

        if (data.status === 'success') {
          alert('Signatures saved successfully!');
          location.reload();
        } else {
          alert('Error saving signatures');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while saving signatures');
      }
    }
    
    // Add click handlers for both save buttons
    saveSignatures.addEventListener('click', saveSignaturesHandler);
    document.getElementById('saveSignaturesTop').addEventListener('click', saveSignaturesHandler);
  </script>
</body>
</html>
