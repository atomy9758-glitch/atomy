@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold mb-6">회원 관리</h1>
    <button onclick="alert('회원 등록 모달 (API 연동 필요)')" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">회원 등록</button>
    <table class="w-full"><thead class="bg-gray-200"><tr><th class="p-2">ID</th><th>아이디</th><th>이름</th><th>전화번호</th><th>상태</th><th>등록일</th></tr></thead>
    <tbody>@foreach($members as $m)<tr class="border-b"><td class="p-2">{{$m->id}}</td><td>{{$m->username}}</td><td>{{$m->name}}</td><td>{{$m->phone}}</td><td>{{$m->status}}</td><td>{{$m->created_at->format('Y-m-d')}}</td></tr>@endforeach</tbody></table>
    <div class="mt-4">{{ $members->links() }}</div>
</div>
@endsection
