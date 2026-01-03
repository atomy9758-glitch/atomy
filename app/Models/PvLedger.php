<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvLedger extends Model
{
    protected $table = 'pv_ledger';
    
    public $timestamps = false;
    
    protected $fillable = [
        'member_id',
        'pv_amount',
        'occurred_at',
        'type',
        'ref_no',
        'memo',
    ];

    protected $casts = [
        'pv_amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    // 관계: 회원
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    // 관계: 전파 로그
    public function propagationLogs()
    {
        return $this->hasMany(PvPropagationLog::class, 'ledger_id');
    }
}
