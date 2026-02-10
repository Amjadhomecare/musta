<?php

namespace App\Console\Commands;

use App\Mail\MonthlyOnclickReport;
use App\Services\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class TestMonthlyTemplateCommand extends Command
{
    protected $signature = 'test:monthly-template';
    protected $description = 'Test rendering monthly onclick email template';

    public function handle(ReportService $reportService): int
    {
        $startDate = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $endDate   = CarbonImmutable::now()->subMonthNoOverflow()->endOfMonth();
        $periodLabel = $startDate->format('M Y');

        $this->info("Testing template rendering for {$periodLabel}");
        
        try {
            $data = $reportService->getOneClickReportData($startDate, $endDate);
            
            $mailable = new MonthlyOnclickReport($data, $periodLabel);
            $rendered = $mailable->render();
            
            $this->info("✅ Template rendered successfully!");
            $this->info("Rendered length: " . strlen($rendered) . " bytes");
            
            // Save to file for inspection
            $path = storage_path('logs/monthly_email_test.html');
            file_put_contents($path, $rendered);
            $this->info("Saved to: {$path}");
            
        } catch (\Throwable $e) {
            $this->error("❌ Template rendering failed!");
            $this->error("Error: " . $e->getMessage());
            $this->error("Line: " . $e->getLine());
            $this->error("File: " . $e->getFile());
            $this->line("\nStack trace:");
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
