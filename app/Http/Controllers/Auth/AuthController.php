<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
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
}
