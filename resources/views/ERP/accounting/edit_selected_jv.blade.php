{{--@extends('admin_dashboard')--}}
{{--@section('admin')--}}

{{--<style>--}}
{{--    /* Layout and General Styling */--}}
{{--    body {--}}
{{--        font-family: 'Segoe UI', Arial, sans-serif;--}}
{{--        line-height: 1.6;--}}
{{--        color: #333;--}}
{{--        background: #f4f4f4;--}}
{{--    }--}}

{{--    .voucher-container {--}}
{{--        width: 90%;--}}
{{--        max-width: 1200px; /* Increased width */--}}
{{--        margin: 40px auto;--}}
{{--        padding: 20px;--}}
{{--        border: none;--}}
{{--        background-color: #fff;--}}
{{--        box-shadow: 0 5px 15px rgba(0,0,0,0.2);--}}
{{--        border-radius: 8px; /* Rounded corners */--}}
{{--    }--}}

{{--    /* Header Styling */--}}
{{--    .voucher-header {--}}
{{--        text-align: center;--}}
{{--        margin-bottom: 40px;--}}
{{--        color: #444;--}}
{{--    }--}}

{{--    .voucher-header h2 {--}}
{{--        margin: 0;--}}
{{--        color: #007bff; /* Bright blue color */--}}
{{--        font-weight: bold;--}}
{{--        font-size: 1.5em;--}}
{{--    }--}}

{{--    /* Table Styling */--}}
{{--    .voucher-table {--}}
{{--        width: 100%;--}}
{{--        border-collapse: collapse;--}}
{{--        margin-bottom: 30px;--}}
{{--    }--}}

{{--    .voucher-table th, .voucher-table td {--}}
{{--        text-align: left;--}}
{{--        padding: 12px; /* Increased padding */--}}
{{--        border: 1px solid #ddd;--}}
{{--    }--}}

{{--    .voucher-table th {--}}
{{--        background-color: #007bff; /* Bright blue color */--}}
{{--        color: #fff;--}}
{{--    }--}}

{{--    /* Signature Styling */--}}
{{--    .voucher-signature {--}}
{{--        text-align: right;--}}
{{--        margin-top: 40px;--}}
{{--        font-style: italic;--}}
{{--    }--}}

{{--    .signature-space {--}}
{{--        border-bottom: 1px solid #000;--}}
{{--        width: 200px;--}}
{{--        display: inline-block;--}}
{{--    }--}}

{{--    /* Footer Styling */--}}
{{--    .footer {--}}
{{--        text-align: center;--}}
{{--        margin-top: 20px;--}}
{{--        font-size: 0.8em;--}}
{{--        color: #666;--}}
{{--    }--}}

{{--    /* Button Styling */--}}
{{--    .print-button {--}}
{{--        display: block;--}}
{{--        width: 200px;--}}
{{--        margin: 20px auto;--}}
{{--        padding: 10px;--}}
{{--        background-color: #28a745; /* Green color */--}}
{{--        color: white;--}}
{{--        border: none;--}}
{{--        border-radius: 5px;--}}
{{--        cursor: pointer;--}}
{{--        text-align: center;--}}
{{--        text-decoration: none;--}}
{{--        font-weight: bold;--}}
{{--    }--}}

{{--    /* Input Styles */--}}
{{--    input {--}}
{{--        width: 100%;--}}
{{--        padding: 8px;--}}
{{--        margin: 5px 0 15px 0;--}}
{{--        border: 1px solid #ccc;--}}
{{--        border-radius: 4px;--}}
{{--        box-sizing: border-box; /* Ensures padding doesn't affect width */--}}
{{--    }--}}
{{--</style>--}}

{{--<form action="{{ route('updateSelectedJournalEntryGroupByRefNumberAction') }}" method="POST">--}}
{{--    @csrf--}}

{{--    <div class="voucher-container">--}}
{{--        <div class="voucher-header">--}}
{{--            <h2>Edit Voucher</h2>--}}
{{--            <input readonly type="text" name="voucher_type" value="{{$details_jv[0]->voucher_type}}">--}}
{{--            <input readonly type="text" name="refNumber" value="{{$details_jv[0]->refNumber}}">--}}
{{--            <input type="date" name="date" value="{{$details_jv[0]->date}}">--}}
{{--        </div>--}}

{{--        <table class="voucher-table">--}}
{{--            <thead>--}}
{{--                <tr>--}}
{{--                    <th>ID</th>--}}
{{--                    <th>Type</th>--}}
{{--                    <th>Account</th>--}}
{{--                    <th>Amount</th>--}}
{{--                    <th>Notes</th>--}}
{{--                    <th>Created At</th>--}}
{{--                    <th>Updated At</th>--}}
{{--                </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--                @foreach ($details_jv as $index => $transaction)--}}
{{--                    <tr>--}}
{{--                        <td><input readonly type="text" name="transactions[{{$index}}][id]" value="{{ $transaction->id }}"></td>--}}

{{--                        <td>--}}
{{--                            <select  class="form-select" name="transactions[{{$index}}][type]" >--}}
{{--                              <option value="{{ $transaction->type }}"> {{ $transaction->type }} </option>--}}
{{--                              <option value="debit">debit   <option>--}}
{{--                              <option value="credit">credit <option>--}}
{{--                           </select>--}}
{{--                        </td>--}}

{{--                        <td>--}}
{{--                            <select class="form-select" data-toggle="select2" name="transactions[{{$index}}][account]">--}}
{{--                            <option value="{{$transaction->account}}" >--}}
{{--                                       {{$transaction->account}}--}}
{{--                            </option>--}}
{{--                                   @foreach ($all_ledger_name as $key )--}}
{{--                                   <option value="{{$key->ledger}}" >--}}
{{--                                       {{$key->ledger}}--}}
{{--                                    </option>--}}
{{--                                   @endforeach--}}
{{--                            </select>--}}
{{--                        </td>--}}

{{--                        <td><input type="number" name="transactions[{{$index}}][amount]" value="{{ $transaction->amount }}"></td>--}}
{{--                        <td><input type="text" name="transactions[{{$index}}][notes]" value="{{ $transaction->notes }}"></td>--}}
{{--                        <td>{{ $transaction->created_at }}</td>--}}
{{--                        <td>{{ $transaction->updated_at }}</td>--}}
{{--                    </tr>--}}
{{--                @endforeach--}}
{{--            </tbody>--}}
{{--        </table>--}}

{{--              <!-- Totals -->--}}
{{--              <div class="row mb-3">--}}
{{--                    <div class="col">--}}
{{--                        <div class="fw-bold">Total Debit: <span id="totalDebit" step="0.01"  class="text-danger">0.00</span></div>--}}
{{--                    </div>--}}
{{--                    <div class="col">--}}
{{--                        <div class="fw-bold">Total Credit: <span id="totalCredit" step="0.01" class="text-success">0.00</span></div>--}}
{{--                    </div>--}}
{{--                </div>--}}


{{--        <div class="footer">--}}
{{--            <p>&copy; {{ date('Y') }} Homecare.</p>--}}
{{--        </div>--}}

{{--        <!-- Submit Button -->--}}
{{--        <input  type="submit" value="Submit" class="btn btn-success">--}}
{{--    </div>--}}
{{--</form>--}}

{{--<script>--}}

{{--document.addEventListener('DOMContentLoaded', function() {--}}
{{--    function updateTotals() {--}}
{{--        let totalDebit = 0, totalCredit = 0;--}}

{{--        Array.from(document.querySelectorAll('.voucher-table tbody tr')).forEach(row => {--}}
{{--            let type = row.querySelector('[name^="transactions"][name$="[type]"]').value;--}}
{{--            let amount = parseFloat(row.querySelector('[name^="transactions"][name$="[amount]"]').value) || 0;--}}

{{--            if (type.toLowerCase() === 'debit') {--}}
{{--                totalDebit += amount;--}}
{{--            } else if (type.toLowerCase() === 'credit') {--}}
{{--                totalCredit += amount;--}}
{{--            }--}}
{{--        });--}}

{{--        document.getElementById("totalDebit").textContent = totalDebit.toFixed(2)--}}
{{--        document.getElementById("totalCredit").textContent = totalCredit.toFixed(2)--}}

{{--        // Adjusted logic for enabling the submit button--}}
{{--        let isBalanced = Math.abs(totalDebit - totalCredit) < 0.01;--}}
{{--        let submitButton = document.querySelector('input[type="submit"]');--}}
{{--        submitButton.disabled = !isBalanced;--}}
{{--    }--}}

{{--    document.querySelector('.voucher-table').addEventListener('input', updateTotals);--}}

{{--    updateTotals();--}}
{{--});--}}




{{--</script>--}}
{{--@endsection--}}
