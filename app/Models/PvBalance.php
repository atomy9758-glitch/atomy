<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvBalance extends Model
{
    protected $table = 'pv_balance';
    
    protected $primaryKey = 'member_id';
    public $incrementing = false;
    public $timestamps = false; // updated_at만 사용
    
    protected $fillable = [
        'member_id',
        'left_pv',
        'right_pv',
        'left_arrear_pv',
        'right_arrear_pv',
        'cycle_no',
        'cycle_started_at',
    ];

    protected $casts = [
        'left_pv' => 'decimal:2',
        'right_pv' => 'decimal:2',
        'left_arrear_pv' => 'decimal:2',
        'right_arrear_pv' => 'decimal:2',
        'cycle_no' => 'integer',
        'cycle_started_at' => 'datetime',
    ];

    // 관계: 회원
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    // Helper: 미수 있는지 확인
    public function hasArrear(): bool
    {
        return $this->left_arrear_pv > 0 || $this->right_arrear_pv > 0;
    }
}
