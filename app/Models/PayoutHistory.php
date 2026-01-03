<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutHistory extends Model
{
    protected $table = 'payout_history';
    
    public $timestamps = false;
    
    protected $fillable = [
        'member_id',
        'cycle_no',
        'paid_at',
        'threshold_pv',
        'payout_amount',
        'left_pv_snapshot',
        'right_pv_snapshot',
        'wasted_pv',
        'status',
        'rule_id',
        'note',
        'search_text',
    ];

    protected $casts = [
        'cycle_no' => 'integer',
        'paid_at' => 'datetime',
        'threshold_pv' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'left_pv_snapshot' => 'decimal:2',
        'right_pv_snapshot' => 'decimal:2',
        'wasted_pv' => 'decimal:2',
    ];

    // 관계: 회원
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    // 관계: 적용된 설정
    public function rule()
    {
        return $this->belongsTo(PayoutSetting::class, 'rule_id');
    }
}
