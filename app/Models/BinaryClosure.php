<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinaryClosure extends Model
{
    protected $table = 'binary_closure';
    
    public $timestamps = false;
    public $incrementing = false;
    
    protected $fillable = [
        'ancestor_id',
        'descendant_id',
        'depth',
        'side_from_ancestor',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    // 관계: 상위 회원
    public function ancestor()
    {
        return $this->belongsTo(Member::class, 'ancestor_id');
    }

    // 관계: 하위 회원
    public function descendant()
    {
        return $this->belongsTo(Member::class, 'descendant_id');
    }
}
