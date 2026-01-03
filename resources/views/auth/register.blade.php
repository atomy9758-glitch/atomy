<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - Binary PV System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Binary PV System</h1>
                <p class="text-gray-600">회원가입</p>
            </div>

            @if($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">오류가 발생했습니다</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-6">
                @csrf

                <!-- 추천인 정보 -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-blue-800 mb-3">📌 추천인 정보</h3>
                    @if($sponsor)
                        <div class="space-y-2 text-sm text-blue-700">
                            <p><strong>추천인:</strong> {{ $sponsor->name }} ({{ $sponsor->username }})</p>
                            <input type="hidden" name="sponsor_username" value="{{ $sponsor->username }}">
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                추천인 아이디 <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="sponsor_username" id="sponsor_username" value="{{ old('sponsor_username') }}"
                                    placeholder="추천인의 아이디를 입력하세요"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <div id="sponsor_check_icon" class="absolute right-3 top-2.5 hidden">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div id="sponsor_info" class="mt-2 text-sm"></div>
                            <p class="mt-1 text-xs text-gray-500">추천인의 아이디를 정확히 입력해주세요.</p>
                        </div>
                    @endif
                </div>

                <!-- 기본 정보 -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">기본 정보</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- 아이디 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                아이디 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="username" value="{{ old('username') }}"
                                placeholder="영문, 숫자 4-20자"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required minlength="4" maxlength="20" pattern="[a-zA-Z0-9]+">
                            <p class="mt-1 text-xs text-gray-500">영문과 숫자만 사용 가능합니다.</p>
                        </div>

                        <!-- 이름 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                이름 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="실명을 입력하세요"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required maxlength="50">
                        </div>

                        <!-- 비밀번호 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                비밀번호 <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password"
                                placeholder="8자 이상 입력"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required minlength="8">
                            <p class="mt-1 text-xs text-gray-500">최소 8자 이상 입력해주세요.</p>
                        </div>

                        <!-- 비밀번호 확인 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                비밀번호 확인 <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation"
                                placeholder="비밀번호 재입력"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required minlength="8">
                        </div>

                        <!-- 연락처 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                연락처 <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                placeholder="010-1234-5678"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required maxlength="20">
                        </div>

                        <!-- 이메일 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                이메일 (선택)
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="example@email.com"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                maxlength="100">
                        </div>
                    </div>
                </div>

                <!-- 바이너리 배치 위치 선택 -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">배치 위치 선택</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-yellow-800">
                            <strong>⚠️ 중요:</strong> 배치 위치는 한 번 선택하면 변경할 수 없습니다. 신중히 선택해주세요.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="radio" name="binary_position" value="L" 
                                {{ old('binary_position') == 'L' ? 'checked' : '' }}
                                class="sr-only peer" required>
                            <div class="text-center peer-checked:text-blue-600">
                                <div class="text-4xl mb-2">👈</div>
                                <div class="font-semibold text-lg mb-1">좌측 (L)</div>
                                <div class="text-sm text-gray-600">Left Position</div>
                            </div>
                            <div class="absolute inset-0 border-4 border-blue-500 rounded-lg hidden peer-checked:block"></div>
                        </label>

                        <label class="relative border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="radio" name="binary_position" value="R" 
                                {{ old('binary_position') == 'R' ? 'checked' : '' }}
                                class="sr-only peer" required>
                            <div class="text-center peer-checked:text-blue-600">
                                <div class="text-4xl mb-2">👉</div>
                                <div class="font-semibold text-lg mb-1">우측 (R)</div>
                                <div class="text-sm text-gray-600">Right Position</div>
                            </div>
                            <div class="absolute inset-0 border-4 border-blue-500 rounded-lg hidden peer-checked:block"></div>
                        </label>
                    </div>
                </div>

                <!-- 이용약관 동의 -->
                <div class="border-t pt-6">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" required
                            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="text-sm text-gray-700">
                            <span class="text-red-500">*</span>
                            <a href="#" class="text-blue-600 hover:underline">이용약관</a> 및 
                            <a href="#" class="text-blue-600 hover:underline">개인정보처리방침</a>에 동의합니다.
                        </span>
                    </label>
                </div>

                <!-- 버튼 -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition-colors">
                        회원가입
                    </button>
                    <a href="{{ route('login') }}"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg text-center transition-colors">
                        로그인으로 돌아가기
                    </a>
                </div>
            </form>

            <!-- 안내사항 -->
            <div class="mt-8 pt-6 border-t">
                <h4 class="font-semibold text-gray-800 mb-3">📢 회원가입 안내</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li>• 회원가입은 <strong class="text-blue-600">무료</strong>이며, 별도의 가입비나 연회비가 없습니다.</li>
                    <li>• 추천인 아이디는 가입 후 변경할 수 없으니 정확히 입력해주세요.</li>
                    <li>• 배치 위치(좌/우)는 한 번 선택하면 변경할 수 없습니다.</li>
                    <li>• 모든 필수 항목(*)은 반드시 입력해야 합니다.</li>
                    <li>• 가입 후 즉시 로그인되어 회원 대시보드로 이동합니다.</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // 추천인 아이디 실시간 검증
        @if(!$sponsor)
        const sponsorInput = document.getElementById('sponsor_username');
        const sponsorInfo = document.getElementById('sponsor_info');
        const sponsorIcon = document.getElementById('sponsor_check_icon');
        let debounceTimer;

        sponsorInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const username = this.value.trim();
            
            if (username.length < 3) {
                sponsorInfo.innerHTML = '';
                sponsorIcon.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/atomy/api/members/check/${username}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            sponsorInfo.innerHTML = `<span class="text-green-600">✓ ${data.member.name} (${data.member.username})</span>`;
                            sponsorIcon.classList.remove('hidden');
                        } else {
                            sponsorInfo.innerHTML = '<span class="text-red-600">✗ 존재하지 않는 추천인입니다.</span>';
                            sponsorIcon.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        sponsorInfo.innerHTML = '<span class="text-red-600">✗ 확인 중 오류가 발생했습니다.</span>';
                        sponsorIcon.classList.add('hidden');
                    });
            }, 500);
        });
        @endif

        // 비밀번호 확인 실시간 검증
        const password = document.querySelector('input[name="password"]');
        const passwordConfirm = document.querySelector('input[name="password_confirmation"]');
        
        passwordConfirm.addEventListener('input', function() {
            if (this.value && this.value !== password.value) {
                this.setCustomValidity('비밀번호가 일치하지 않습니다.');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
