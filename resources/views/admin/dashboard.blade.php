@extends('layouts.admin')
@section('title', '관리자 대시보드')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6">관리자 대시보드</h1>
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-blue-100 p-4 rounded"><h3 class="font-bold">전체 회원</h3><p class="text-3xl">{{ $stats['total_members'] }}</p></div>
        <div class="bg-green-100 p-4 rounded"><h3 class="font-bold">활성 회원</h3><p class="text-3xl">{{ $stats['active_members'] }}</p></div>
        <div class="bg-yellow-100 p-4 rounded"><h3 class="font-bold">오늘 수당 건수</h3><p class="text-3xl">{{ $stats['total_payouts_today'] }}</p></div>
        <div class="bg-red-100 p-4 rounded"><h3 class="font-bold">오늘 수당 합계</h3><p class="text-2xl">{{ number_format($stats['total_payout_amount_today'], 0) }}원</p></div>
    </div>
</div>
@endsection
