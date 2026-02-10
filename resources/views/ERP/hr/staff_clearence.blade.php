<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Clearance Employee</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-black font-sans p-8">
  <div class="max-w-4xl mx-auto border border-black p-6">
    <!-- Header -->
    <h1 class="text-center text-xl font-bold"> {{env( 'company_name') ?? ''}} </h1>
    <h2 class="text-center text-lg font-bold mt-2 border-y border-black py-1">مخالصة موظف<br/>CLEARANCE EMPLOYEE</h2>

    <!-- Number and Title -->
    <div class="flex justify-between items-center mt-4 text-lg">
      <div class="font-bold">NO:  00{{ $m->id }}</div>
      <div class="text-red-600 font-bold text-xl"> {{ $m?->job_title }} </div>
      <div> Leave Salary Count: {{ $m?->note}}</div>
    </div>

    <!-- Information Table -->
    <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
      <div>
        <p><span class="font-bold">Name:</span> {{ $m?->maid_name}}</p>
        <p><span class="font-bold">Job Title:</span>{{ $m?->job_title }}</p>
        <p><span class="font-bold">Vac-Reason:</span> {{$m?->reason }}</p>
        <p><span class="font-bold">Last entry:</span> <span class="bg-yellow-200 px-1">{{ $m?->last_entry_date}}</span></p>
        <p><span class="font-bold">Date of travel:</span> <span class="bg-yellow-200 px-1">  {{ $m?->travel_date}}  </span></p>

        <p>
          <span class="font-bold">DAYS:</span>
        {{ number_format(\Carbon\Carbon::parse($m?->last_entry_date)->diffInDays($m?->created_at)) }}

        </p>



        <p><span class="font-bold">Salary :dh</span> <span class="bg-yellow-200 px-1"> {{ $m->salary_dh}} </span></p>
        <p><span class="font-bold text-lg">Basic salary:</span> {{ $m->basic_salary}}</p>
      </div>
      <div>
        <p><span class="font-bold">Employee ID:</span> {{ $m->emirate_id }} </p>
        <p><span class="font-bold">Nationality:</span> {{ $m?->nationality }}</p>
        <p><span class="font-bold">Pass No:</span>{{ $m?->pp }} </p>
        <p><span class="font-bold">Pass.EXP:</span> {{ $m?->pp_expire}}</p>
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
        <tr>
          <td class="border border-black px-2 py-1">Leave allowance</td>
          <td class="border border-black px-2 py-1">60 days salary for 2 years</td>
          <td class="border border-black px-2 py-1 bg-yellow-200"> {{$m?->allowance}} </td>
        </tr>
        <tr>
          <td class="border border-black px-2 py-1">Tickets</td>
          <td class="border border-black px-2 py-1">On company</td>
          <td class="border border-black px-2 py-1">0</td>
        </tr>
        <tr>
          <td class="border border-black px-2 py-1">Salaries</td>
          <td class="border border-black px-2 py-1">14 days</td>
          <td class="border border-black px-2 py-1">0</td>
        </tr>
        <tr>
          <td class="border border-black px-2 py-1">End of service</td>
          <td class="border border-black px-2 py-1">21 days for each YEAR</td>
          <td class="border border-black px-2 py-1">0</td>
        </tr>
        <tr>
          <td class="border border-black px-2 py-1">Other</td>
          <td class="border border-black px-2 py-1">-</td>
          <td class="border border-black px-2 py-1">0</td>
        </tr>
        <tr>
          <td class="border border-black px-2 py-1">Settlements</td>
          <td class="border border-black px-2 py-1">-</td>
          <td class="border border-black px-2 py-1 bg-red-500 text-white">{{ $m?->dedcution  ?? 0}}</td>
        </tr>

          <tr>
          <td class="border border-black px-2 py-1">Remaining amount</td>
          <td class="border border-black px-2 py-1">-</td>
          <td class="border border-black px-2 py-1 bg-red-500 text-white">{{ $m?->remaining_amount  ?? 0}}</td>
        </tr>
        <tr class="font-bold bg-yellow-200">
          <td class="border border-black px-2 py-1" colspan="2">Total</td>
          <td class="border border-black px-2 py-1">{{ $m?->allowance - $m?->dedcution   }}</td>
        </tr>
      </tbody>
    </table>

<!-- Confirmation Section -->
<p class="text-sm mt-4 leading-relaxed text-left">
  I, the undersigned <span class="font-bold">{{ $m?->maid_name }}</span>, hereby confirm that I have received all my dues as stated above under payment voucher number ( pv-00 {{ $m?->id }}). <br>
  Dated: <span class="font-bold">{{ $m?->created_at }}</span>. I have no further claims as of this date.
</p>


<p>
Created_by : {{ $m?->created_by}}

<br>
  Updated_by : {{ $m?->updated_by}}

</p>


 
<!-- Signatures -->
<div class="grid grid-cols-3 gap-4 text-center mt-8 text-sm font-bold">
  <div>
    HR. MANAGER
    <div class="border border-black h-24 mt-2"></div>
  </div>
  <div>
    EMPLOYEE SIGNATURE
    <div class="border border-black h-24 mt-2"></div>
  </div>
  <div>
    G. Manager
    <div class="border border-black h-24 mt-2"></div>
  </div>
</div>

  </div>
</body>
</html>
