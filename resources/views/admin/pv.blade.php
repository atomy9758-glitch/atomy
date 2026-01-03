@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6">PV 입력</h1>
    <form action="{{ route('admin.pv.store') }}" method="POST" class="max-w-md">
        @csrf
        <div class="mb-4"><label class="block font-bold mb-2">회원 ID</label><input type="number" name="member_id" required class="border rounded px-3 py-2 w-full"></div>
        <div class="mb-4"><label class="block font-bold mb-2">PV 금액</label><input type="number" step="0.01" name="pv_amount" required class="border rounded px-3 py-2 w-full"></div>
        <div class="mb-4"><label class="block font-bold mb-2">타입</label><select name="type" required class="border rounded px-3 py-2 w-full"><option value="ORDER">ORDER</option><option value="CANCEL">CANCEL</option><option value="ADJUST">ADJUST</option></select></div>
        <div class="mb-4"><label class="block font-bold mb-2">참조번호</label><input type="text" name="ref_no" class="border rounded px-3 py-2 w-full"></div>
        <div class="mb-4"><label class="block font-bold mb-2">메모</label><textarea name="memo" class="border rounded px-3 py-2 w-full"></textarea></div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">PV 입력</button>
    </form>
</div>
@endsection
