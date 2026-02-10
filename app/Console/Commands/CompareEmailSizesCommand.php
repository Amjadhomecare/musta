<?php

namespace App\Console\Commands;

use App\Mail\DailyOnclickReport;
use App\Mail\MonthlyOnclickReport;
use App\Services\ReportService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CompareEmailSizesCommand extends Command
{
    protected $signature = 'test:compare-email-sizes';
    protected $description = 'Compare sizes of daily vs monthly email templates';

    public function handle(ReportService $reportService): int
    {
        try {
            // Daily email
            $dailyStart = Carbon::today()->startOfDay();
            $dailyEnd = Carbon::today()->endOfDay();
            $dailyData = $reportService->getDailyOneClickReportData($dailyStart, $dailyEnd);
            $dailyMailable = new DailyOnclickReport($dailyData, $dailyStart->toDateString());
            $dailyRendered = $dailyMailable->render();
            $dailySize = strlen($dailyRendered);
            
            // Monthly email
            $monthlyStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
            $monthlyEnd = CarbonImmutable::now()->subMonthNoOverflow()->endOfMonth();
            $monthlyData = $reportService->getOneClickReportData($monthlyStart, $monthlyEnd);
            $monthlyMailable = new MonthlyOnclickReport($monthlyData, $monthlyStart->format('M Y'));
            $monthlyRendered = $monthlyMailable->render();
            $monthlySize = strlen($monthlyRendered);
            
            $this->info("Email Size Comparison:");
            $this->table(
                ['Email Type', 'Size (bytes)', 'Size (KB)', 'Size (MB)'],
                [
                    ['Daily', number_format($dailySize), number_format($dailySize / 1024, 2), number_format($dailySize / 1024 / 1024, 3)],
                    ['Monthly', number_format($monthlySize), number_format($monthlySize / 1024, 2), number_format($monthlySize / 1024 / 1024, 3)],
                    ['Difference', number_format($monthlySize - $dailySize), number_format(($monthlySize - $dailySize) / 1024, 2), ''],
                ]
            );
            
            $ratio = round($monthlySize / $dailySize, 2);
            $this->info("Monthly email is {$ratio}x larger than daily email");
            
            if ($monthlySize > 100 * 1024) {
                $this->warn("⚠️  Monthly email exceeds 100KB - this might cause delivery issues!");
            }
            
        } catch (\Throwable $e) {
            $this->error("❌ Failed: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
