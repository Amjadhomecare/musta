<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaidClearence extends Model
{
    protected $guarded = [];

    protected $casts = [
        'clearance_items' => 'array',
    ];

    protected $appends = ['clearance_items_with_defaults'];

    public function getClearanceItemsWithDefaultsAttribute()
    {
        // If clearance_items exists, filter out non-numeric keys (like 'signatures')
        if ($this->clearance_items && is_array($this->clearance_items)) {
            $items = [];
            foreach ($this->clearance_items as $key => $value) {
                // Only include numeric keys (actual clearance items)
                if (is_numeric($key) && is_array($value)) {
                    $items[] = $value;
                }
            }
            
            // If we found items, return them
            if (!empty($items)) {
                return $items;
            }
        }
        
        // Otherwise return defaults
        return [
            [
                'label' => 'Salaries',
                'details' => $this->salary_details ?? '14 days',
                'amount' => $this->salary_dh ?? 0
            ],
            [
                'label' => 'End of service',
                'details' => $this->end_of_service_details ?? '21 days for each YEAR',
                'amount' => $this->end_of_service_dh ?? 0
            ],
            [
                'label' => 'Other',
                'details' => $this->other_details ?? '-',
                'amount' => $this->other_dh ?? 0
            ]
        ];
    }

    public function maid()
        {
            return $this->hasOne(MaidsDB::class, 'name', 'maid_name');
        }
}
