<?php

namespace App\Services;

use App\Models\Member;
use App\Models\BinaryClosure;
use Illuminate\Support\Facades\DB;

class ClosureService
{
    /**
     * 신규 회원 등록 시 Binary Closure 테이블 갱신
     * 
     * @param Member $member
     * @return void
     */
    public function insertMember(Member $member): void
    {
        DB::transaction(function () use ($member) {
            // 1. Self-link 생성 (depth=0)
            BinaryClosure::create([
                'ancestor_id' => $member->id,
                'descendant_id' => $member->id,
                'depth' => 0,
                'side_from_ancestor' => null,
            ]);

            // 2. 부모가 있으면 부모의 모든 상위 경로 복사
            if ($member->binary_parent_id) {
                $parentPaths = BinaryClosure::where('descendant_id', $member->binary_parent_id)->get();

                foreach ($parentPaths as $path) {
                    BinaryClosure::create([
                        'ancestor_id' => $path->ancestor_id,
                        'descendant_id' => $member->id,
                        'depth' => $path->depth + 1,
                        'side_from_ancestor' => ($path->depth == 0)
                            ? $member->binary_position // 직계: 내 position
                            : $path->side_from_ancestor, // 간접: 부모의 side 유지
                    ]);
                }
            }
        });
    }

    /**
     * 회원 삭제 시 Closure 정리 (자동으로 CASCADE되지만 명시적 메서드)
     * 
     * @param int $memberId
     * @return void
     */
    public function removeMember(int $memberId): void
    {
        DB::transaction(function () use ($memberId) {
            // CASCADE로 자동 삭제되지만 명시적으로 삭제 가능
            BinaryClosure::where('ancestor_id', $memberId)
                ->orWhere('descendant_id', $memberId)
                ->delete();
        });
    }

    /**
     * 특정 회원의 모든 상위(ancestors) 조회
     * 
     * @param int $memberId
     * @param bool $excludeSelf
     * @return \Illuminate\Support\Collection
     */
    public function getAncestors(int $memberId, bool $excludeSelf = true)
    {
        $query = BinaryClosure::where('descendant_id', $memberId);
        
        if ($excludeSelf) {
            $query->where('depth', '>', 0);
        }
        
        return $query->orderBy('depth', 'asc')->get();
    }

    /**
     * 특정 회원의 모든 하위(descendants) 조회
     * 
     * @param int $memberId
     * @param bool $excludeSelf
     * @return \Illuminate\Support\Collection
     */
    public function getDescendants(int $memberId, bool $excludeSelf = true)
    {
        $query = BinaryClosure::where('ancestor_id', $memberId);
        
        if ($excludeSelf) {
            $query->where('depth', '>', 0);
        }
        
        return $query->orderBy('depth', 'asc')->get();
    }
}
