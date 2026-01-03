<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'members';

    protected $fillable = [
        'username',
        'password_hash',
        'name',
        'phone',
        'email',
        'sponsor_id',
        'binary_parent_id',
        'binary_position',
        'self_pv',
        'qualified_from_at',
        'role',
        'status',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'self_pv' => 'decimal:2',
        'qualified_from_at' => 'datetime',
    ];

    // Sanctum에서 password 필드 이름이 password_hash이므로 오버라이드
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // 관계: 추천인 (스폰서)
    public function sponsor()
    {
        return $this->belongsTo(Member::class, 'sponsor_id');
    }

    // 관계: 바이너리 부모
    public function binaryParent()
    {
        return $this->belongsTo(Member::class, 'binary_parent_id');
    }

    // 관계: 바이너리 자식들
    public function binaryChildren()
    {
        return $this->hasMany(Member::class, 'binary_parent_id');
    }

    // 관계: PV 잔액
    public function pvBalance()
    {
        return $this->hasOne(PvBalance::class, 'member_id');
    }

    // 관계: PV 원장
    public function pvLedgers()
    {
        return $this->hasMany(PvLedger::class, 'member_id');
    }

    // 관계: 수당 내역
    public function payoutHistories()
    {
        return $this->hasMany(PayoutHistory::class, 'member_id');
    }

    // 관계: Closure (나를 descendant로 하는)
    public function ancestors()
    {
        return $this->belongsToMany(Member::class, 'binary_closure', 'descendant_id', 'ancestor_id')
            ->withPivot('depth', 'side_from_ancestor');
    }

    // 관계: Closure (나를 ancestor로 하는)
    public function descendants()
    {
        return $this->belongsToMany(Member::class, 'binary_closure', 'ancestor_id', 'descendant_id')
            ->withPivot('depth', 'side_from_ancestor');
    }

    // Helper: 자격 여부
    public function isQualified(): bool
    {
        return $this->self_pv >= 1 && $this->qualified_from_at !== null;
    }

    // Helper: 관리자 여부
    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }
}
