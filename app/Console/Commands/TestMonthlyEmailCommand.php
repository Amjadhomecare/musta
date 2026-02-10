<?php

namespace App\Console\Commands;

use App\Mail\MonthlyOnclickReport;
use App\Services\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMonthlyEmailCommand extends Command
{
    protected $signature = 'test:monthly-email {email?}';
    protected $description = 'Test sending monthly onclick email';

    public function handle(ReportService $reportService): int
    {
        $email = $this->argument('email') ?? 'ameeram4@gmail.com';
        
        $startDate = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $endDate   = CarbonImmutable::now()->subMonthNoOverflow()->endOfMonth();
        $periodLabel = $startDate->format('M Y');

        $this->info("Fetching data for {$startDate->toDateString()} to {$endDate->toDateString()}");
        
        try {
            $data = $reportService->getOneClickReportData($startDate, $endDate);
            
            $this->info("Data fetched successfully. Keys: " . implode(', ', array_keys($data)));
            $this->info("Sending to: {$email}");
            
            Mail::to($email)->send(new MonthlyOnclickReport($data, $periodLabel));
            
            $this->info("✅ Email sent successfully!");
            
            Log::info("Test monthly email sent", [
                'to' => $email,
                'period' => $periodLabel,
            ]);
            
        } catch (\Throwable $e) {
            $this->error("❌ Failed: " . $e->getMessage());
            $this->error("Line: " . $e->getLine());
            $this->error("File: " . $e->getFile());
            Log::error('Test monthly email failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
