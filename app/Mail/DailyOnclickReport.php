<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyOnclickReport extends Mailable
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
        return $this->subject("Next meta ERP Daily Report - {$this->periodLabel}")
            ->markdown('emails.daily_onclick_report');
    }
}
