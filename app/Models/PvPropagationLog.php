<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvPropagationLog extends Model
{
    protected $table = 'pv_propagation_log';
    
    public $timestamps = false;
    
    protected $fillable = [
        'ledger_id',
        'buyer_id',
        'ancestor_id',
        'side',
        'applied_amount',
        'applied_to',
        'occurred_at',
    ];

    protected $casts = [
        'applied_amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    // 관계: 원장
    public function ledger()
    {
        return $this->belongsTo(PvLedger::class, 'ledger_id');
    }

    // 관계: 구매자
    public function buyer()
    {
        return $this->belongsTo(Member::class, 'buyer_id');
    }

    // 관계: 상위 회원
    public function ancestor()
    {
        return $this->belongsTo(Member::class, 'ancestor_id');
    }
}
