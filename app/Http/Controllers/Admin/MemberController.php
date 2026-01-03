<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\AdminAudit;
use App\Services\ClosureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    protected $closureService;

    public function __construct(ClosureService $closureService)
    {
        $this->closureService = $closureService;
    }

    public function index(Request $request)
    {
        $query = Member::where('role', 'MEMBER');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.members', compact('members'));
    }

    public function apiIndex(Request $request)
    {
        $query = Member::where('role', 'MEMBER');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:members,username',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'binary_parent_id' => 'nullable|exists:members,id',
            'binary_position' => 'nullable|in:L,R',
        ]);

        \DB::transaction(function () use ($request) {
            $member = Member::create([
                'username' => $request->username,
                'password_hash' => Hash::make($request->password),
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'binary_parent_id' => $request->binary_parent_id,
                'binary_position' => $request->binary_position,
                'role' => 'MEMBER',
                'status' => 'ACTIVE',
            ]);

            // Closure 생성
            $this->closureService->insertMember($member);

            // Audit 로그
            AdminAudit::create([
                'admin_id' => auth()->id(),
                'action' => 'CREATE_MEMBER',
                'target_type' => 'Member',
                'target_id' => $member->id,
                'detail' => ['username' => $member->username, 'name' => $member->name],
                'ip_address' => request()->ip(),
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => '회원 등록 완료'], 201);
        }

        return redirect()->route('admin.members.index')->with('success', '회원 등록 완료');
    }

    public function show($id)
    {
        $member = Member::with(['pvBalance', 'pvLedgers', 'payoutHistories'])->findOrFail($id);
        return response()->json($member);
    }
}
