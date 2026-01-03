<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Member\DashboardController as MemberDashboard;
use App\Http\Controllers\Member\PayoutController as MemberPayout;
use App\Http\Controllers\Member\LedgerController as MemberLedger;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\MemberController as AdminMember;
use App\Http\Controllers\Admin\PvController as AdminPv;
use App\Http\Controllers\Admin\PayoutController as AdminPayout;
use App\Http\Controllers\Admin\SettingsController as AdminSettings;
use App\Http\Controllers\Admin\SearchController as AdminSearch;

// Nginx가 이미 /atomy를 처리하므로, prefix 없이 라우트 정의

// 루트 경로는 로그인으로 리다이렉트
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect('/admin');
        }
        return redirect('/me');
    }
    return redirect('/login');
})->name('home');

// 인증
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 회원가입
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// 회원 영역 (로그인 필요)
Route::middleware(['member'])->prefix('me')->group(function () {
    Route::get('/', [MemberDashboard::class, 'index'])->name('member.dashboard');
    Route::get('/payouts', [MemberPayout::class, 'index'])->name('member.payouts');
    Route::get('/ledger', [MemberLedger::class, 'index'])->name('member.ledger');
});

// 관리자 영역 (관리자 권한 필요)
Route::middleware(['member', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboard::class, 'index'])->name('admin.dashboard');
    
    // 회원 관리
    Route::get('/members', [AdminMember::class, 'index'])->name('admin.members.index');
    Route::post('/members', [AdminMember::class, 'store'])->name('admin.members.store');
    
    // PV 관리
    Route::get('/pv', [AdminPv::class, 'index'])->name('admin.pv.index');
    Route::post('/pv', [AdminPv::class, 'store'])->name('admin.pv.store');
    
    // 수당 관리
    Route::get('/payouts', [AdminPayout::class, 'index'])->name('admin.payouts.index');
    
    // 설정 관리
    Route::get('/settings', [AdminSettings::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [AdminSettings::class, 'store'])->name('admin.settings.store');
    
    // 통합 검색
    Route::get('/search', [AdminSearch::class, 'index'])->name('admin.search');
});
