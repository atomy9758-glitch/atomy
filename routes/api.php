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

// Nginx가 /atomy/api를 처리하므로, API는 별도 prefix 없음
// 인증 API
Route::post('/auth/login', [AuthController::class, 'apiLogin']);
Route::post('/auth/logout', [AuthController::class, 'apiLogout'])->middleware('auth:sanctum');

// 추천인 검증 API (공개)
Route::get('/members/check/{username}', [AuthController::class, 'checkUsername']);

// 회원 API (Sanctum 인증)
Route::middleware(['auth:sanctum'])->prefix('me')->group(function () {
    Route::get('/balance', [MemberDashboard::class, 'apiBalance']);
    Route::get('/payouts', [MemberPayout::class, 'index']);
    Route::get('/ledger', [MemberLedger::class, 'index']);
});

// 관리자 API (Sanctum 인증 + 관리자 권한)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminDashboard::class, 'apiStats']);
    
    // 회원 관리
    Route::get('/members', [AdminMember::class, 'apiIndex']);
    Route::post('/members', [AdminMember::class, 'store']);
    Route::get('/members/{id}', [AdminMember::class, 'show']);
    
    // PV 관리
    Route::post('/pv', [AdminPv::class, 'store']);
    Route::get('/pv/ledger', [AdminPv::class, 'ledger']);
    
    // 수당 관리
    Route::get('/payouts', [AdminPayout::class, 'index']);
    Route::get('/payouts/{id}', [AdminPayout::class, 'show']);
    
    // 설정 관리
    Route::get('/settings', [AdminSettings::class, 'index']);
    Route::post('/settings', [AdminSettings::class, 'store']);
    
    // 통합 검색
    Route::get('/search', [AdminSearch::class, 'index']);
});
