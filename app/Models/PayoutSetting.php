<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutSetting extends Model
{
    protected $table = 'payout_settings';
    
    public $timestamps = false;
    
    protected $fillable = [
        'threshold_pv',
        'payout_amount',
        'effective_from',
        'created_by',
    ];

    protected $casts = [
        'threshold_pv' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'effective_from' => 'datetime',
    ];

    // 관계: 생성자
    public function creator()
    {
        return $this->belongsTo(Member::class, 'created_by');
    }

    // 관계: 이 설정으로 지급된 수당들
    public function payoutHistories()
    {
        return $this->hasMany(PayoutHistory::class, 'rule_id');
    }
}
