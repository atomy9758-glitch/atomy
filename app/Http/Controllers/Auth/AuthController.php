<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\ClosureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    protected $closureService;

    public function __construct(ClosureService $closureService)
    {
        $this->closureService = $closureService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm(Request $request)
    {
        // 추천인 ID가 URL 파라미터로 전달된 경우
        $sponsorId = $request->query('sponsor');
        $sponsor = null;
        
        if ($sponsorId) {
            $sponsor = Member::where('username', $sponsorId)
                ->orWhere('id', $sponsorId)
                ->first();
        }

        return view('auth.register', compact('sponsor'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:members,username|min:4|max:20|alpha_num',
            'password' => 'required|string|min:8|confirmed',
            'name' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'sponsor_username' => 'required|string|exists:members,username',
            'binary_position' => 'required|in:L,R',
        ], [
            'username.required' => '아이디를 입력해주세요.',
            'username.unique' => '이미 사용 중인 아이디입니다.',
            'username.min' => '아이디는 최소 4자 이상이어야 합니다.',
            'username.alpha_num' => '아이디는 영문과 숫자만 사용할 수 있습니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'name.required' => '이름을 입력해주세요.',
            'phone.required' => '연락처를 입력해주세요.',
            'sponsor_username.required' => '추천인 아이디를 입력해주세요.',
            'sponsor_username.exists' => '존재하지 않는 추천인 아이디입니다.',
            'binary_position.required' => '배치 위치를 선택해주세요.',
            'binary_position.in' => '배치 위치는 좌측(L) 또는 우측(R)이어야 합니다.',
        ]);

        DB::beginTransaction();

        try {
            // 추천인(스폰서) 찾기
            $sponsor = Member::where('username', $request->sponsor_username)->firstOrFail();

            // 바이너리 부모 찾기 (추천인과 동일하게 시작)
            $binaryParent = $sponsor;

            // 선택한 위치에 이미 회원이 있는지 확인
            $existingChild = Member::where('binary_parent_id', $binaryParent->id)
                ->where('binary_position', $request->binary_position)
                ->first();

            if ($existingChild) {
                DB::rollBack();
                return back()->withErrors([
                    'binary_position' => '선택한 위치에 이미 회원이 배치되어 있습니다. 다른 위치를 선택해주세요.'
                ])->withInput();
            }

            // 회원 생성
            $member = Member::create([
                'username' => $request->username,
                'password_hash' => Hash::make($request->password),
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'sponsor_id' => $sponsor->id,
                'binary_parent_id' => $binaryParent->id,
                'binary_position' => $request->binary_position,
                'self_pv' => 0,
                'role' => 'MEMBER',
                'status' => 'ACTIVE',
            ]);

            // PV Balance 초기화
            DB::table('pv_balance')->insert([
                'member_id' => $member->id,
                'left_pv' => 0,
                'right_pv' => 0,
                'left_arrear_pv' => 0,
                'right_arrear_pv' => 0,
                'cycle_no' => 0,
                'cycle_started_at' => now(),
                'updated_at' => now(),
            ]);

            // Closure 테이블 업데이트
            $this->closureService->addMember($member);

            DB::commit();

            // 자동 로그인
            auth()->login($member);

            return redirect('/me')->with('success', '회원가입이 완료되었습니다! 환영합니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => '회원가입 중 오류가 발생했습니다: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $member = Member::where('username', $request->username)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$member || !Hash::check($request->password, $member->password_hash)) {
            return back()->withErrors(['username' => '아이디 또는 비밀번호가 일치하지 않습니다.']);
        }

        // 세션 로그인
        auth()->login($member);

        // 세션 재생성 (보안)
        $request->session()->regenerate();

        // 관리자면 관리자 대시보드로, 아니면 회원 대시보드로
        if ($member->isAdmin()) {
            return redirect('/admin');
        }

        return redirect('/me');
    }

    public function apiLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $member = Member::where('username', $request->username)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$member || !Hash::check($request->password, $member->password_hash)) {
            return response()->json(['message' => '인증 실패'], 401);
        }

        $token = $member->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'member' => $member,
        ]);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return redirect()->route('login');
    }

    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '로그아웃 성공']);
    }

    public function checkUsername($username)
    {
        $member = Member::where('username', $username)->first();
        
        if ($member) {
            return response()->json([
                'exists' => true,
                'member' => [
                    'username' => $member->username,
                    'name' => $member->name,
                ]
            ]);
        }
        
        return response()->json([
            'exists' => false,
            'message' => '존재하지 않는 사용자입니다.'
        ], 404);
    }
}
