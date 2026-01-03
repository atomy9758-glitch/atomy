@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6">수당 설정 관리</h1>
    <form action="{{ route('admin.settings.store') }}" method="POST" class="max-w-md mb-6">
        @csrf
        <div class="mb-4"><label class="block font-bold mb-2">기준 PV (threshold_pv)</label><input type="number" step="0.01" name="threshold_pv" required class="border rounded px-3 py-2 w-full"></div>
        <div class="mb-4"><label class="block font-bold mb-2">지급 금액 (payout_amount)</label><input type="number" step="0.01" name="payout_amount" required class="border rounded px-3 py-2 w-full"></div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">새 설정 추가</button>
    </form>
    <h2 class="text-xl font-bold mb-4">설정 이력</h2>
    <table class="w-full"><thead class="bg-gray-200"><tr><th class="p-2">ID</th><th>기준 PV</th><th>지급 금액</th><th>적용 시작</th></tr></thead>
    <tbody>@foreach($settings as $s)<tr class="border-b"><td class="p-2">{{$s->id}}</td><td>{{number_format($s->threshold_pv, 2)}}</td><td>{{number_format($s->payout_amount, 0)}}원</td><td>{{$s->effective_from->format('Y-m-d H:i')}}</td></tr>@endforeach</tbody></table>
</div>
@endsection
