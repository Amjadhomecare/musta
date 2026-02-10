<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaidsDB extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];

    public function maidInterview()
    {
        return $this->hasMany(Interview::class, 'maid_name', 'name')
                    ->whereRaw('maid_name COLLATE utf8mb4_general_ci = name COLLATE utf8mb4_general_ci');
    }
    

    public function maidFinance()
    {
        return $this->hasMany(General_journal_voucher::class, 'maid_id', 'id');
    }

public function maidAccount()
{
    return $this->hasMany(General_journal_voucher::class, 'maid_id', 'id');
}


    public function contracts()
    {
        return $this->hasMany(Category4Model::class, 'maid', 'id');
    }

    public function p4Conts(){
        return $this->hasMany(Category4Model::class, 'maid_id');
    }

    public function p1Conts(){
        return $this->hasMany(categoryOne::class, 'maid_id',);
    }

    public function maidAttachment()
    {
        return $this->hasMany(MaidAttachment::class, 'maid_id');
    }

    public function maidDocExpiry()
    {
        return $this->hasOne(maid_doc_expiry::class,'maid_id')->withDefault([
            'labor_card_expiry' => null,
            'passport_expiry' => null,
            'visa_expiry' => null,
            'eid_expiry' => null,
        ]);;
    }
    
    

    public static function turnMaidStatusToApprove($maid)
    {
        self::where('name', $maid)->update(['maid_status' =>'approved']);
    }

    public function maidsFilter()
    {
        return $this->hasOne(maidsFilter::class, 'maid_id');
    }

    
}
