<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\SendMonthlyOnclickReportJob;
use App\Mail\MonthlyOnclickReport;
use App\Services\ReportService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendMonthlyOnclickReportTest extends TestCase
{
    public function test_it_dispatches_monthly_onclick_report_job()
    {
        Bus::fake();

        $this->artisan('report:onclick-monthly')
            ->assertExitCode(0)
            ->expectsOutputToContain('job dispatched to background');

        Bus::assertDispatched(SendMonthlyOnclickReportJob::class);
    }

    public function test_it_shows_data_in_dry_run()
    {
        Mail::fake();
        
        $this->artisan('report:onclick-monthly --dry-run')
            ->assertExitCode(0)
            ->expectsOutputToContain('Dry run: Fetching data');

        Mail::assertNotSent(MonthlyOnclickReport::class);
    }

    public function test_job_sends_email_successfully()
    {
        Mail::fake();
        
        // Mock the service to return dummy data
        $mockService = $this->mock(ReportService::class);
        $mockService->shouldReceive('getOneClickReportData')
            ->once()
            ->andReturn([
                'maidReturnCat1_count' => 10,
                'returnedMaid_count' => 5,
            ]);

        $job = new SendMonthlyOnclickReportJob();
        $job->handle($mockService);

        Mail::assertSent(MonthlyOnclickReport::class, function ($mail) {
            return $mail->payload['maidReturnCat1_count'] === 10 &&
                   $mail->payload['returnedMaid_count'] === 5;
        });
    }
}
