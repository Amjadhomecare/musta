<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\DirectDebit;
use App\Models\DdFollowUp;
use App\Services\DdFollowUpService;
use App\Enum\DdFollowUps;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DdFollowUpServiceTest extends TestCase
{
    use RefreshDatabase;

    private DdFollowUpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DdFollowUpService();
    }

    /** @test */
    public function it_creates_follow_up_for_matching_rejected_dd()
    {
        Http::fake([
            'https://hcnextmeta.com/api/relay/sms' => Http::response(['Success' => 'true'], 200),
        ]);

        // Create a DirectDebit matching criteria:
        // Status REJECTED, reason has RR01, Submitted, and "2 time(s)"
        $dd = DirectDebit::unguarded(function () {
            return DirectDebit::create([
                'ref' => 'REF123',
                'status' => DirectDebit::STATUS_REJECTED,
                'rejected_reason' => 'RR01 - Submitted 2 times rejection',
                'phone' => '971501234567',
                'email' => 'test@example.com',
            ]);
        });

        // Run service
        $result = $this->service->processFollowUps();

        // Assertions
        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(1, $result['sms_sent']);
        $this->assertEquals(0, $result['marked_manual']);

        $this->assertDatabaseHas('dd_follow_ups', [
            'dd_id' => $dd->id,
            'attempt_number' => 1,
            'follow_up_status' => DdFollowUps::FollowedUpSent->value,
        ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://hcnextmeta.com/api/relay/sms' &&
                   str_contains($request['text'], 'REF123') &&
                   $request['number'] === '971501234567';
        });
    }

    /** @test */
    public function it_increments_attempt_for_existing_follow_up_under_limit()
    {
        Http::fake([
            'https://hcnextmeta.com/api/relay/sms' => Http::response(['Success' => 'true'], 200),
        ]);

        $dd = DirectDebit::unguarded(function () {
            return DirectDebit::create([
                'ref' => 'REF124',
                'status' => DirectDebit::STATUS_REJECTED,
                'rejected_reason' => 'RR01 - Submitted 2 times',
                'phone' => '971501234567',
            ]);
        });

        $followUp = DdFollowUp::create([
            'dd_id' => $dd->id,
            'attempt_number' => 1,
            'follow_up_status' => DdFollowUps::FollowedUpSent->value,
            'created_by' => 'system',
        ]);

        $result = $this->service->processFollowUps();

        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(1, $result['sms_sent']);

        $this->assertDatabaseHas('dd_follow_ups', [
            'id' => $followUp->id,
            'attempt_number' => 2,
            'follow_up_status' => DdFollowUps::FollowedUpSent->value,
        ]);
    }

    /** @test */
    public function it_marks_as_manual_cleanup_after_max_attempts()
    {
        Http::fake(); // No SMS should be sent

        $dd = DirectDebit::unguarded(function () {
            return DirectDebit::create([
                'ref' => 'REF125',
                'status' => DirectDebit::STATUS_REJECTED,
                'rejected_reason' => 'RR01 - Submitted 3 times',
                'phone' => '971501234567',
            ]);
        });

        $followUp = DdFollowUp::create([
            'dd_id' => $dd->id,
            'attempt_number' => 3, // Already at max (assuming MAX_ATTEMPTS is 3)
            'follow_up_status' => DdFollowUps::FollowedUpSent->value,
            'created_by' => 'system',
        ]);

        $result = $this->service->processFollowUps();

        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(0, $result['sms_sent']);
        $this->assertEquals(1, $result['marked_manual']);

        $this->assertDatabaseHas('dd_follow_ups', [
            'id' => $followUp->id,
            'follow_up_status' => DdFollowUps::FollowUpManually->value,
        ]);
        
        // Ensure note was appended
        $followUp->refresh();
        $this->assertStringContainsString('Marked for manual follow-up', $followUp->follow_up_notes);
        
        Http::assertNothingSent();
    }

    /** @test */
    public function it_ignores_non_matching_direct_debits()
    {
        // 1. Wrong Status
        DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'IGNORE1',
            'status' => DirectDebit::STATUS_ACCEPTED, // Not Rejected
            'rejected_reason' => 'RR01 - Submitted 2 times',
        ]));

        // 2. Wrong Reason (No RR01)
        DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'IGNORE2',
            'status' => DirectDebit::STATUS_REJECTED,
            'rejected_reason' => 'Other - Submitted 2 times',
        ]));

        // 3. Wrong Reason (No "Submitted")
        DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'IGNORE3',
            'status' => DirectDebit::STATUS_REJECTED,
            'rejected_reason' => 'RR01 - Failed 2 times', // "Submitted" missing
        ]));

        // 4. Wrong Reason (Less than 2 times - e.g. "1 time")
        DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'IGNORE4',
            'status' => DirectDebit::STATUS_REJECTED,
            'rejected_reason' => 'RR01 - Submitted 1 time',
        ]));

        $result = $this->service->processFollowUps();

        $this->assertEquals(0, $result['processed']);
        $this->assertEquals(0, $result['sms_sent']);
    }

    /** @test */
    public function it_normalizes_phone_numbers_correctly_for_sms()
    {
        Http::fake([
            'https://hcnextmeta.com/api/relay/sms' => Http::response(['Success' => 'true'], 200),
        ]);

        // Case 1: 0501234567 -> 971501234567
        $dd1 = DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'PHONE1', 'status' => DirectDebit::STATUS_REJECTED, 
            'rejected_reason' => 'RR01 - Submitted 2 times', 'phone' => '0501234567'
        ]));

        // Case 2: 501234567 -> 971501234567
        $dd2 = DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'PHONE2', 'status' => DirectDebit::STATUS_REJECTED, 
            'rejected_reason' => 'RR01 - Submitted 2 times', 'phone' => '501234567'
        ]));
        
        // Case 3: 00971501234567 -> 971501234567
        $dd3 = DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'PHONE3', 'status' => DirectDebit::STATUS_REJECTED, 
            'rejected_reason' => 'RR01 - Submitted 2 times', 'phone' => '00971501234567'
        ]));
        
        $this->service->processFollowUps();
        
        Http::assertSent(function ($request) {
            return $request['number'] === '971501234567';
        });
    }

    /** @test */
    public function it_handles_sms_failure_gracefully()
    {
        Http::fake([
            'https://hcnextmeta.com/api/relay/sms' => Http::response(['Success' => 'false'], 200),
        ]);

        $dd = DirectDebit::unguarded(fn() => DirectDebit::create([
            'ref' => 'FAIL1',
            'status' => DirectDebit::STATUS_REJECTED,
            'rejected_reason' => 'RR01 - Submitted 2 times',
            'phone' => '0501234567',
        ]));

        $this->service->processFollowUps();
        
        $this->assertDatabaseHas('dd_follow_ups', [
            'dd_id' => $dd->id,
            'attempt_number' => 1,
        ]);

        $followUp = DdFollowUp::where('dd_id', $dd->id)->first();
        $this->assertCount(1, $followUp->message_sent);
        $this->assertFalse($followUp->message_sent[0]['success']);
    }
}
