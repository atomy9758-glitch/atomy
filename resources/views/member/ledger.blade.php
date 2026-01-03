@extends('layouts.app')
@section('title', 'PV 원장')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6">내 PV 원장</h1>
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr><th class="p-2">발생일시</th><th>타입</th><th>PV 금액</th><th>참조번호</th><th>메모</th></tr>
        </thead>
        <tbody>
            @forelse($ledgers as $l)
            <tr class="border-b">
                <td class="p-2">{{ $l->occurred_at->format('Y-m-d H:i') }}</td>
                <td><span class="px-2 py-1 rounded @if($l->type=='ORDER')bg-blue-200 @elseif($l->type=='CANCEL')bg-red-200 @else bg-gray-200 @endif">{{ $l->type }}</span></td>
                <td class="@if($l->pv_amount >= 0)text-blue-600 @else text-red-600 @endif font-bold">{{ $l->pv_amount >= 0 ? '+' : '' }}{{ number_format($l->pv_amount, 2) }}</td>
                <td>{{ $l->ref_no }}</td>
                <td>{{ $l->memo }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center p-4">PV 원장이 없습니다.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $ledgers->links() }}</div>
</div>
@endsection
