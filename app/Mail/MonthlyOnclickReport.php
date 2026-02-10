<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyOnclickReport extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;
    public string $periodLabel; 

    public function __construct(array $payload, string $periodLabel)
    {
        $this->payload = $payload;
        $this->periodLabel = $periodLabel;
    }

    public function build()
    {
        return $this->subject("Monthly non-financial Report - {$this->periodLabel}")
            ->markdown('emails.monthly_onclick_report');
    }
}
