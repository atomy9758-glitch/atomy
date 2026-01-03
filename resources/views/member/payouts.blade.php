@extends('layouts.app')
@section('title', '수당 내역')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6">내 수당 내역</h1>
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2">사이클</th><th>지급일</th><th>좌측PV</th><th>우측PV</th><th>소멸PV</th><th>지급액</th><th>상태</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payouts as $p)
            <tr class="border-b">
                <td class="p-2">{{ $p->cycle_no }}</td>
                <td>{{ $p->paid_at->format('Y-m-d H:i') }}</td>
                <td>{{ number_format($p->left_pv_snapshot, 2) }}</td>
                <td>{{ number_format($p->right_pv_snapshot, 2) }}</td>
                <td>{{ number_format($p->wasted_pv, 2) }}</td>
                <td class="font-bold">{{ number_format($p->payout_amount, 0) }}원</td>
                <td><span class="px-2 py-1 bg-yellow-200 rounded">{{ $p->status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center p-4">수당 내역이 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $payouts->links() }}</div>
</div>
@endsection
