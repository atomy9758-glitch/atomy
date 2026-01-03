<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Binary PV System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function api(path, options = {}) {
            const baseUrl = '/atomy/api';
            const url = baseUrl + path;
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                credentials: 'same-origin',
            };
            return fetch(url, { ...defaultOptions, ...options });
        }
    </script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('member.dashboard') }}" class="text-xl font-bold">Binary PV System</a>
            <div class="space-x-4">
                <a href="{{ route('member.dashboard') }}" class="hover:underline">대시보드</a>
                <a href="{{ route('member.payouts') }}" class="hover:underline">수당 내역</a>
                <a href="{{ route('member.ledger') }}" class="hover:underline">PV 원장</a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:underline">로그아웃</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container mx-auto mt-8 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
