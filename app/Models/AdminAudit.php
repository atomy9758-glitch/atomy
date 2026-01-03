<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAudit extends Model
{
    protected $table = 'admin_audit';
    
    public $timestamps = false;
    
    protected $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'detail',
        'ip_address',
    ];

    protected $casts = [
        'detail' => 'array',
        'created_at' => 'datetime',
    ];

    // 관계: 관리자
    public function admin()
    {
        return $this->belongsTo(Member::class, 'admin_id');
    }
}
