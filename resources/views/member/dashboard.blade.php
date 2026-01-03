@extends('layouts.app')

@section('title', '대시보드')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6">내 대시보드</h1>
    
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div class="bg-blue-100 p-4 rounded">
            <h3 class="font-bold text-lg">좌측 PV</h3>
            <p class="text-3xl">{{ number_format($balance->left_pv, 2) }}</p>
            @if($balance->left_arrear_pv > 0)
                <p class="text-red-600">미수: {{ number_format($balance->left_arrear_pv, 2) }}</p>
            @endif
        </div>
        <div class="bg-green-100 p-4 rounded">
            <h3 class="font-bold text-lg">우측 PV</h3>
            <p class="text-3xl">{{ number_format($balance->right_pv, 2) }}</p>
            @if($balance->right_arrear_pv > 0)
                <p class="text-red-600">미수: {{ number_format($balance->right_arrear_pv, 2) }}</p>
            @endif
        </div>
    </div>
    
    <div class="mb-6">
        <p class="mb-2"><strong>본인 PV:</strong> {{ number_format($member->self_pv, 2) }}</p>
        <p class="mb-2"><strong>자격 상태:</strong> 
            @if($member->isQualified())
                <span class="text-green-600 font-bold">자격 보유</span>
                ({{ $member->qualified_from_at->format('Y-m-d H:i:s') }} 부터)
            @else
                <span class="text-red-600 font-bold">자격 미보유</span>
            @endif
        </p>
        <p class="mb-2"><strong>현재 사이클:</strong> {{ $balance->cycle_no }}</p>
        @if($balance->cycle_started_at)
            <p class="mb-2"><strong>사이클 시작:</strong> {{ $balance->cycle_started_at->format('Y-m-d H:i:s') }}</p>
        @endif
    </div>
</div>
@endsection
