<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Jobs\ProcessDdFollowUpsJob;
use App\Jobs\ResetBookedMaidsJob;
use App\Jobs\RunDailyRecursionsJob;
use App\Jobs\SendComparativeTrial3EndsJob;
use App\Jobs\SendDailyOnclickReportJob;
use App\Jobs\SendIncomeStatement3MonthsJob;
use App\Jobs\SendMonthlyOnclickReportJob;
use Illuminate\Support\Facades\Log;

class JobController extends Controller
{
    /**
     * Dispatch ProcessDdFollowUpsJob to the queue
     */
    public function processDdFollowUps(): JsonResponse
    {
        ProcessDdFollowUpsJob::dispatch();
        Log::info('📤 ProcessDdFollowUpsJob dispatched via API');
        
        return response()->json([
            'success' => true,
            'message' => 'ProcessDdFollowUpsJob has been queued successfully',
            'job' => 'ProcessDdFollowUpsJob'
        ]);
    }

    /**
     * Dispatch ResetBookedMaidsJob to the queue
     */
    public function resetBookedMaids(): JsonResponse
    {
        ResetBookedMaidsJob::dispatch();
        Log::info('📤 ResetBookedMaidsJob dispatched via API');
        
        return response()->json([
            'success' => true,
            'message' => 'ResetBookedMaidsJob has been queued successfully',
            'job' => 'ResetBookedMaidsJob'
        ]);
    }

    /**
     * Dispatch RunDailyRecursionsJob to the queue
     */
    public function runDailyRecursions(): JsonResponse
    {
        RunDailyRecursionsJob::dispatch();
        Log::info('📤 RunDailyRecursionsJob dispatched via API');
        
        return response()->json([
            'success' => true,
            'message' => 'RunDailyRecursionsJob has been queued successfully',
            'job' => 'RunDailyRecursionsJob'
        ]);
    }

    /**
     * Dispatch SendComparativeTrial3EndsJob to the queue
     */
    public function sendComparativeTrial(): JsonResponse
    {
        SendComparativeTrial3EndsJob::dispatch();
        Log::info('📤 SendComparativeTrial3EndsJob dispatched via API');
        
        return response()->json([
            'success' => true,
            'message' => 'SendComparativeTrial3EndsJob has been queued successfully',
            'job' => 'SendComparativeTrial3EndsJob'
        ]);
    }

    /**
     * Dispatch SendDailyOnclickReportJob to the queue
     */
    public function sendDailyOnclickReport(): JsonResponse
    {
        SendDailyOnclickReportJob::dispatch();
        Log::info('📤 SendDailyOnclickReportJob dispatched via API');
        
        return response()->json([
            'success' => true,
            'message' => 'SendDailyOnclickReportJob has been queued successfully',
            'job' => 'SendDailyOnclickReportJob'
        ]);
    }

    /**
     * Dispatch SendIncomeStatement3MonthsJob to the queue
     */
    public function sendIncomeStatement(): JsonResponse
    {
        SendIncomeStatement3MonthsJob::dispatch();
        Log::info('📤 SendIncomeStatement3MonthsJob dispatched via API');
        
        return response()->json([
            'success' => true,
            'message' => 'SendIncomeStatement3MonthsJob has been queued successfully',
            'job' => 'SendIncomeStatement3MonthsJob'
        ]);
    }

    /**
     * Dispatch SendMonthlyOnclickReportJob to the queue
     */
    public function sendMonthlyOnclickReport(): JsonResponse
    {
        SendMonthlyOnclickReportJob::dispatch();
        Log::info('📤 SendMonthlyOnclickReportJob dispatched via API');
        
        return response()->json([
            'success' => true,
            'message' => 'SendMonthlyOnclickReportJob has been queued successfully',
            'job' => 'SendMonthlyOnclickReportJob'
        ]);
    }

    /**
     * List all available jobs
     */
    public function listJobs(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'jobs' => [
                ['name' => 'ProcessDdFollowUpsJob', 'endpoint' => '/api/jobs/process-dd-followups'],
                ['name' => 'ResetBookedMaidsJob', 'endpoint' => '/api/jobs/reset-booked-maids'],
                ['name' => 'RunDailyRecursionsJob', 'endpoint' => '/api/jobs/run-daily-recursions'],
                ['name' => 'SendComparativeTrial3EndsJob', 'endpoint' => '/api/jobs/send-comparative-trial'],
                ['name' => 'SendDailyOnclickReportJob', 'endpoint' => '/api/jobs/send-daily-onclick-report'],
                ['name' => 'SendIncomeStatement3MonthsJob', 'endpoint' => '/api/jobs/send-income-statement'],
                ['name' => 'SendMonthlyOnclickReportJob', 'endpoint' => '/api/jobs/send-monthly-onclick-report'],
            ]
        ]);
    }
}
