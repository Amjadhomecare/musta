<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Blacklist Approval</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .approval-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .customer-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .btn-approve {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 25px;
            transition: transform 0.2s;
        }
        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .warning-text {
            color: #dc3545;
            font-size: 14px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="approval-card">
        <div class="card-header-custom">
            <h2 class="mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Blacklist Approval
            </h2>
        </div>
        
        <div class="card-body p-4">
            <p class="text-center mb-4">
                You are about to blacklist the following customer. Please review the information carefully before proceeding.
            </p>

            <div class="customer-info">
                <div class="info-row">
                    <span class="info-label">Customer Name:</span>
                    <span class="info-value">{{ $customer->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $customer->phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">ID Number:</span>
                    <span class="info-value">{{ $customer->idNumber ?? 'N/A' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Note:</span>
                    <span class="info-value">{{ $customer->note ?? 'N/A' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Related:</span>
                    <span class="info-value">{{ $customer->related ?? 'N/A' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $customer->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer Type:</span>
                    <span class="info-value">{{ $customer->cusomerType ?? 'N/A' }}</span>
                </div>
            </div>

            <form action="{{ route('blacklist.process', $customer->id) }}" method="POST" class="text-center">
                @csrf
                <button type="submit" class="btn btn-danger btn-approve">
                    <i class="bi bi-check-circle"></i> Confirm Blacklist
                </button>
                
                <p class="warning-text">
                    <i class="bi bi-info-circle"></i> This action will mark the customer as blacklisted in the system.
                </p>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>
