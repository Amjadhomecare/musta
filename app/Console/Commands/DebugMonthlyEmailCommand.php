<?php

namespace App\Console\Commands;

use App\Mail\MonthlyOnclickReport;
use App\Services\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class DebugMonthlyEmailCommand extends Command
{
    protected $signature = 'debug:monthly-email {email?}';
    protected $description = 'Debug monthly email sending with verbose SMTP logging';

    public function handle(ReportService $reportService): int
    {
        $email = $this->argument('email') ?? 'ameeram4@gmail.com';
        
        $startDate = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $endDate   = CarbonImmutable::now()->subMonthNoOverflow()->endOfMonth();
        $periodLabel = $startDate->format('M Y');

        $this->info("=== Monthly Email Debug ===");
        $this->info("Period: {$periodLabel}");
        $this->info("Recipient: {$email}");
        $this->line("");
        
        try {
            // Fetch data
            $this->info("1. Fetching data...");
            $data = $reportService->getOneClickReportData($startDate, $endDate);
            $this->info("   ✓ Data fetched: " . count($data) . " keys");
            
            // Create mailable
            $this->info("2. Creating mailable...");
            $mailable = new MonthlyOnclickReport($data, $periodLabel);
            $this->info("   ✓ Mailable created");
            
            // Render template
            $this->info("3. Rendering template...");
            $rendered = $mailable->render();
            $size = strlen($rendered);
            $this->info("   ✓ Template rendered: " . number_format($size) . " bytes (" . number_format($size/1024, 2) . " KB)");
            
            // Get subject
            $built = $mailable->build();
            $subject = $built->subject ?? 'No subject';
            $this->info("   Subject: {$subject}");
            
            // Send email
            $this->info("4. Sending email via SMTP...");
            
            try {
                Mail::to($email)->send($mailable);
                $this->info("   ✓ Mail::send() completed successfully");
                $this->line("");
                $this->info("=== Email Sent Successfully ===");
                $this->info("Check your email inbox AND spam folder for:");
                $this->info("  Subject: {$subject}");
                $this->info("  To: {$email}");
                $this->info("  Size: " . number_format($size/1024, 2) . " KB");
                $this->line("");
                $this->warn("If you don't see it in your inbox, check:");
                $this->warn("  1. Spam/Junk folder");
                $this->warn("  2. Promotions tab (Gmail)");
                $this->warn("  3. All Mail folder");
                $this->warn("  4. Search for subject: \"Monthly non-financial Report\"");
            } catch (\Throwable $e) {
                $this->error("   ✗ Mail::send() threw exception!");
                $this->error("   Error: " . $e->getMessage());
                $this->error("   File: " . $e->getFile() . ":" . $e->getLine());
                throw $e;
            }
            
        } catch (\Throwable $e) {
            $this->error("❌ Fatal error: " . $e->getMessage());
            $this->error("Line: " . $e->getLine());
            $this->error("File: " . $e->getFile());
            $this->line("\nStack trace:");
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
