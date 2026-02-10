<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSurviMaid extends Model
{
      protected $fillable = [
        'maid_id',
        'customer_id',
        'satisfied',
        'perf_cleaning',
        'perf_cooking',
        'perf_childcare',
        'perf_communication',
        'note',
    ];

    protected $casts = [
        'satisfied'          => 'integer',
        'perf_cleaning'      => 'integer',
        'perf_cooking'       => 'integer',
        'perf_childcare'     => 'integer',
        'perf_communication' => 'integer',
    ];

    // Maps for labels (handy in Blade)
    public const SATISFACTION_MAP = [
        5 => 'Very Satisfied (راضي جداً)',
        4 => 'Satisfied (راضي)',
        3 => 'Neutral (وسط)',
        2 => 'Dissatisfied (غير راضي)',
        1 => 'Very Dissatisfied (غير راضي مطلقاً)',
    ];

    public const PERF_MAP = [
        5 => 'Excellent (ممتاز)',
        4 => 'Good (جيد)',
        3 => 'Fair (مقبول)',
        2 => 'Poor (سيء)',
        0 => 'Not Applicable (لا يوجد)',
    ];

    public function maid()
    {

        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getSatisfiedLabelAttribute(): ?string
    {
        return self::SATISFACTION_MAP[$this->satisfied] ?? null;
    }

    public function getPerfCleaningLabelAttribute(): ?string
    {
        return self::PERF_MAP[$this->perf_cleaning] ?? null;
    }

    public function getPerfCookingLabelAttribute(): ?string
    {
        return self::PERF_MAP[$this->perf_cooking] ?? null;
    }

    public function getPerfChildcareLabelAttribute(): ?string
    {
        return self::PERF_MAP[$this->perf_childcare] ?? null;
    }

    public function getPerfCommunicationLabelAttribute(): ?string
    {
        return self::PERF_MAP[$this->perf_communication] ?? null;
    }
}
