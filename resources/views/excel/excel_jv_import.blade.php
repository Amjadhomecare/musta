@extends('keen')
@section('content')

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-4">Import General Journal Voucher</h3>

            <!-- Template Download Link -->
            <div class="alert alert-info">
                You can <a href="https://nextmetaerp.s3.eu-north-1.amazonaws.com/documents/2025/07/Rib7rZnaEMshC9gdiUKnexvwqLw4D0wVcZqKZEIk.csv" target="_blank" class="alert-link">download the CSV template here</a> before uploading.
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- CSV Format Table -->
            <div class="mb-4">
                <label class="form-label fw-bold">CSV Format Requirements</label>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped small text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>RefNumber</th>
                                <th>VoucherType</th>
                                <th>Type</th>
                                <th>PreConnectionName</th>
                                <th>MaidName</th>
                                <th>Account</th>
                                <th>Amount</th>
                                <th>InvoiceBalance</th>
                                <th>Notes</th>
                                <th>ReceiveRef</th>
                                <th>CreditNoteRef</th>
                                <th>ContractRef</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Y-m-d</td>
                                <td>0</td>
                                <td>Journal Voucher</td>
                                <td>debit / credit</td>
                                <td>null</td>
                                <td>ERP name / null</td>
                                <td>Exact ledger name</td>
                                <td>Integer</td>
                                <td>0</td>
                                <td>Any text</td>
                                <td>null</td>
                                <td>null</td>
                                <td>null</td>
                                <td>null</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- File Upload Form -->
            <form action="{{ route('import.general-journal-voucher') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="file" class="form-label fw-bold">Upload CSV File</label>
                    <div class="dropzone p-4 border border-2 border-primary rounded text-center bg-light" onclick="document.getElementById('file').click()" style="cursor: pointer;">
                        <p class="mb-1"><i class="bi bi-upload" style="font-size: 2rem;"></i></p>
                        <p class="mb-1">Click or drag your file here to upload</p>
                        <small class="text-muted">Accepted format: .csv only</small>
                        <input type="file" name="file" id="file" class="form-control d-none" accept=".csv" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Import</button>
            </form>
        </div>
    </div>
</div>

<!-- Optional Bootstrap Icons (needed for upload icon) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

@endsection
