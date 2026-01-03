@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-lg shadow p-6"><h1 class="text-2xl font-bold mb-6">통합 검색</h1><form method="GET" class="mb-6"><input type="text" name="keyword" value="{{ $keyword ?? '' }}" placeholder="이름, 아이디, 주문번호, 메모 검색..." class="border rounded px-3 py-2 w-96"><button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">검색</button></form><div>검색 결과 표시 (API 연동)</div></div>
@endsection
