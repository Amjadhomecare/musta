<?php

namespace App\Services;

use App\Models\DirectDebit;
use App\Models\DdFollowUp;
use App\Enum\DdFollowUps;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DdFollowUpService
{
    /**
     * Maximum attempts before marking for manual follow-up
     */
    private const MAX_ATTEMPTS = 3;

    /**
     * Process DD follow-ups for rejected mandates with RR01 signature issues.
     *
     * @param bool $dryRun If true, show what would happen without saving
     * @param callable|null $logCallback Optional callback for logging (for CLI output)
     * @return array Stats: ['processed' => int, 'sms_sent' => int, 'marked_manual' => int]
     */
    public function processFollowUps(bool $dryRun = false, callable $logCallback = null): array
    {
        $log = function(string $message, string $level = 'info') use ($logCallback) {
            if ($logCallback) {
                $logCallback($message);
            }
            Log::$level("DdFollowUpService: $message");
        };

        $log('Starting Direct Debit follow-up processing...');

        // Query DirectDebits: rejected with RR01 signature issue, submitted 2+ times
        $rejectedDDs = DirectDebit::where('status', DirectDebit::STATUS_REJECTED)
            ->where('rejected_reason', 'LIKE', '%RR01%')
            ->where('rejected_reason', 'LIKE', '%Submitted%')
            ->where(function ($query) {
                // Match "2 time(s)" or more (2, 3, 4, ... times)
                $query->where('rejected_reason', 'REGEXP', '[2-9][0-9]* time')
                      ->orWhere('rejected_reason', 'LIKE', '%2 time%')
                      ->orWhere('rejected_reason', 'LIKE', '%3 time%')
                      ->orWhere('rejected_reason', 'LIKE', '%4 time%')
                      ->orWhere('rejected_reason', 'LIKE', '%5 time%');
            })
            ->with('followUp')
            ->get();

        $log("Found {$rejectedDDs->count()} rejected Direct Debits matching criteria.");

        $processed = 0;
        $smsSent = 0;
        $markedManual = 0;

        foreach ($rejectedDDs as $dd) {
            $followUp = $dd->followUp;

            // Skip if customer has already replied (status 3)
            if ($followUp && $followUp->follow_up_status === DdFollowUps::CustomerReplied->value) {
                $log("Skipping DD #{$dd->id} (ref: {$dd->ref}) - Customer already replied");
                continue;
            }

            if (!$followUp) {
                // No follow-up exists - create one with attempt 1
                if ($dryRun) {
                    $log("DRY RUN: Would create follow-up for DD #{$dd->id} (ref: {$dd->ref}) - Attempt 1");
                } else {
                    $followUp = DdFollowUp::create([
                        'dd_id' => $dd->id,
                        'attempt_number' => 1,
                        'follow_up_status' => DdFollowUps::FollowedUpSent->value,
                        'follow_up_notes' => 'Auto-created by job: RR01 rejection follow-up',
                        'message_sent' => [],
                        'created_by' => 'system',
                    ]);

                    $this->sendFollowUpSms($dd, $followUp);
                    $log("Created follow-up for DD #{$dd->id} (ref: {$dd->ref}) - Attempt 1");
                }
                $smsSent++;

            } elseif ($followUp->attempt_number < self::MAX_ATTEMPTS) {
                // Follow-up exists with attempts < 3 - increment and send SMS
                $newAttempt = $followUp->attempt_number + 1;
                
                if ($dryRun) {
                    $log("DRY RUN: Would update follow-up for DD #{$dd->id} (ref: {$dd->ref}) - Attempt {$newAttempt}");
                } else {
                    $followUp->update([
                        'attempt_number' => $newAttempt,
                        'follow_up_status' => DdFollowUps::FollowedUpSent->value,
                        'updated_by' => 'system',
                    ]);

                    $this->sendFollowUpSms($dd, $followUp);
                    $log("Updated follow-up for DD #{$dd->id} (ref: {$dd->ref}) - Attempt {$newAttempt}");
                }
                $smsSent++;

            } else {
                // Attempt >= 3 - mark for manual follow-up, do NOT send SMS
                if ($followUp->follow_up_status !== DdFollowUps::FollowUpManually->value && $followUp->attachment == null) {
                    if ($dryRun) {
                        $log("DRY RUN: Would mark DD #{$dd->id} (ref: {$dd->ref}) for manual follow-up", 'warning');
                    } else {
                        $followUp->update([
                            'follow_up_status' => DdFollowUps::FollowUpManually->value,
                            'follow_up_notes' => ($followUp->follow_up_notes ?? '') . "\nMarked for manual follow-up after {$followUp->attempt_number} attempts.",
                            'updated_by' => 'system',
                        ]);
                        $log("DD #{$dd->id} (ref: {$dd->ref}) marked for manual follow-up (attempt {$followUp->attempt_number})", 'warning');
                    }
                    $markedManual++;
                }
            }

            $processed++;
        }

        $log("Complete - {$processed} processed, {$smsSent} SMS sent, {$markedManual} marked for manual follow-up.");

        return [
            'processed' => $processed,
            'sms_sent' => $smsSent,
            'marked_manual' => $markedManual,
        ];
    }

    /**
     * Send follow-up SMS to the customer using whitelisted SMS relay
     */
    public function sendFollowUpSms(DirectDebit $dd, DdFollowUp $followUp): bool
    {
        $phone = $dd->phone;
        if (empty($phone)) {
            Log::warning('DdFollowUpService: No phone number for DD', ['dd_id' => $dd->id, 'ref' => $dd->ref]);
            return false;
        }

        // Normalize phone to 9715XXXXXXXX format
        $digits = preg_replace('/\D+/', '', (string) $phone);

        // Handle common UAE formats
        if (\Str::startsWith($digits, '009715') && strlen($digits) === 14) {
            $digits = '971' . substr($digits, 5);
        } elseif (\Str::startsWith($digits, '05') && strlen($digits) === 10) {
            $digits = '971' . substr($digits, 1);
        } elseif (\Str::startsWith($digits, '5') && strlen($digits) === 9) {
            $digits = '971' . $digits;
        }

        // Validate final format (must be 9715XXXXXXXX)
        if (!\Str::startsWith($digits, '9715') || strlen($digits) !== 12) {
            Log::warning('DdFollowUpService: Invalid UAE mobile format', [
                'dd_id' => $dd->id,
                'ref' => $dd->ref,
                'raw_phone' => $phone,
                'normalized' => $digits,
            ]);
            return false;
        }

        $signLink = "https://sign.homecaremaids.ae/external/resign-rejection/{$dd->ref}";
        $message = "HomeCare Direct Debit: Please re-sign your mandate using this link: {$signLink}";

        try {
            // Use whitelisted SMS relay API
            $relayUrl = 'https://hcnextmeta.com/api/relay/sms';
            $response = Http::timeout(15)->post($relayUrl, [
                'text' => $message,
                'number' => $digits,
            ]);

            $json = $response->json() ?? [];
            $success = (strtolower($json['Success'] ?? '') === 'true') || (bool)($json['ok'] ?? false);

            // Log the SMS in the follow-up record
            $messageSent = $followUp->message_sent ?? [];
            $messageSent[] = [
                'sent_at' => now()->toDateTimeString(),
                'phone' => $digits,
                'raw_phone' => $phone,
                'message' => $message,
                'success' => $success,
                'response' => $json,
            ];
            $followUp->update(['message_sent' => $messageSent]);

            Log::info('DdFollowUpService: SMS sent via relay', [
                'dd_id' => $dd->id,
                'ref' => $dd->ref,
                'phone' => $digits,
                'attempt' => $followUp->attempt_number,
                'success' => $success,
            ]);

            return $success;

        } catch (\Throwable $e) {
            Log::error('DdFollowUpService: SMS failed', [
                'dd_id' => $dd->id,
                'ref' => $dd->ref,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
