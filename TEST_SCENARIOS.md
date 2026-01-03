# Binary PV System - 테스트 시나리오 (20개)

## 🧪 엣지케이스 포함 완전 테스트 시나리오

### 1. 신규 회원 등록 → Closure 자동 생성 확인
- **목적**: 회원 등록 시 binary_closure 테이블에 self-link와 상위 경로가 정확히 생성되는지 확인
- **절차**:
  1. ADMIN 계정으로 로그인
  2. Root 회원 A 생성 (부모 없음)
  3. 회원 B 생성 (부모: A, position: L)
  4. 회원 C 생성 (부모: A, position: R)
  5. binary_closure 테이블 확인
- **기대 결과**: 
  - A, B, C 각각 self-link (depth=0) 존재
  - B와 C는 A를 ancestor로 하는 레코드 존재 (depth=1, side='L'/'R')

### 2. 동일 부모 아래 동일 위치 중복 배치 시도
- **목적**: UNIQUE 제약 조건 검증
- **절차**:
  1. 회원 D 생성 (부모: A, position: L)
  2. 회원 E 생성 시도 (부모: A, position: L) - 중복!
- **기대 결과**: 회원 E 생성 실패, 에러 메시지 표시

### 3. +PV 입력 → 상위 전파 → 자격 미충족 상위는 제외
- **목적**: 자격 조건(self_pv >= 1) 검증
- **절차**:
  1. 회원 A (self_pv=0), 회원 B (자식, self_pv=10)
  2. B에게 +PV 입력 (ORDER, 10,000)
  3. A의 pv_balance 확인
- **기대 결과**: A는 자격이 없으므로 전파 안됨

### 4. self_pv < 1 → >= 1 변경 시 qualified_from_at 설정
- **목적**: 자격 획득 시점 기록
- **절차**:
  1. 회원 A (self_pv=0)
  2. A에게 +PV 입력 (ORDER, 5)
  3. A의 qualified_from_at 확인
- **기대 결과**: qualified_from_at이 현재 시각으로 설정됨

### 5. 소급 방지: 하위 PV 발생 < 상위 자격 시각
- **목적**: 과거 PV는 전파 안됨
- **절차**:
  1. 회원 A, B (B는 A 자식)
  2. B에게 +PV 입력 (occurred_at = 2024-01-01)
  3. A에게 자격 부여 (qualified_from_at = 2024-01-02)
  4. A의 pv_balance 확인
- **기대 결과**: 과거 PV는 전파 안됨

### 6. +PV → 미수 있음 → 미수부터 상계
- **목적**: +PV 시 미수 우선 상계 로직
- **절차**:
  1. 회원 A (left_arrear_pv=5000)
  2. 좌측 하위에서 +PV 7000 발생
  3. A의 pv_balance 확인
- **기대 결과**: 
  - left_arrear_pv = 0
  - left_pv = 2000

### 7. -PV → 현재PV 충분 → PV만 차감
- **목적**: -PV 시 현재 PV 충분한 경우
- **절차**:
  1. 회원 A (left_pv=10000)
  2. 좌측 하위에서 -PV 3000 발생
  3. A의 pv_balance 확인
- **기대 결과**: 
  - left_pv = 7000
  - left_arrear_pv = 0

### 8. -PV → 현재PV 부족 → PV=0, 부족분 미수 누적
- **목적**: 0 바닥 원칙 + 미수 관리
- **절차**:
  1. 회원 A (left_pv=5000)
  2. 좌측 하위에서 -PV 8000 발생
  3. A의 pv_balance 확인
- **기대 결과**: 
  - left_pv = 0
  - left_arrear_pv = 3000

### 9. 미수 있는 상태에서 매칭 시도 → 지급 안됨
- **목적**: 미수 있으면 매칭 금지
- **절차**:
  1. 회원 A (left_pv=500000, right_pv=500000, left_arrear_pv=1000)
  2. payout_history 확인
- **기대 결과**: 지급 기록 생성 안됨

### 10. 좌/우 PV >= threshold → 수당 기록 → PV 리셋
- **목적**: 매칭 수당 지급 정상 플로우
- **절차**:
  1. 회원 A (left_pv=350000, right_pv=350000, arrear=0)
  2. payout_history 확인
  3. A의 pv_balance 확인
- **기대 결과**: 
  - payout_history 생성 (cycle_no=0)
  - left_pv=0, right_pv=0
  - cycle_no=1

### 11. 동시 다발 PV 입력 → SELECT FOR UPDATE → 중복 지급 없음
- **목적**: 동시성 제어
- **절차**:
  1. 회원 A (left_pv=299000, right_pv=299000)
  2. 동시에 좌측 +2000, 우측 +2000 입력 (멀티스레드)
  3. payout_history 확인
- **기대 결과**: 수당 기록은 1개만 생성

### 12. cycle_no 증가 확인
- **목적**: 사이클 관리
- **절차**:
  1. 회원 A의 첫 번째 수당 (cycle_no=0)
  2. 다시 PV 쌓아 두 번째 수당 발생
  3. payout_history 확인
- **기대 결과**: 두 번째 수당의 cycle_no=1

### 13. 초과 PV 소멸 (wasted_pv) 계산
- **목적**: 양쪽 PV 차이 처리
- **절차**:
  1. 회원 A (left_pv=400000, right_pv=350000, threshold=300000)
  2. payout_history 확인
- **기대 결과**: 
  - wasted_pv = 150000 (400000+350000 - 300000*2)

### 14. 수당 설정 변경 → 새 버전 적용
- **목적**: 버전 관리
- **절차**:
  1. 초기 설정: threshold=300000, payout=30000
  2. 새 설정 추가: threshold=500000, payout=50000
  3. 이후 매칭은 새 설정 적용
- **기대 결과**: rule_id가 새 설정을 가리킴

### 15. 주문번호(ref_no)로 원장→전파로그→수당 추적
- **목적**: 감사 추적
- **절차**:
  1. 특정 주문번호(ref_no=ORD-12345)로 PV 입력
  2. pv_ledger, pv_propagation_log, payout_history 연결 조회
- **기대 결과**: ref_no로 전체 플로우 추적 가능

### 16. 관리자 검색: 키워드로 회원/원장/수당 통합 검색
- **목적**: 통합 검색 기능
- **절차**:
  1. 관리자 통합 검색에서 "홍길동" 입력
  2. members, pv_ledger, payout_history 결과 확인
- **기대 결과**: 3개 카테고리 모두에서 검색 결과 반환

### 17. 회원 검색: 내 원장/수당만 조회
- **목적**: 권한 분리
- **절차**:
  1. 회원 A로 로그인
  2. 내 PV 원장 조회
  3. 다른 회원 데이터 접근 시도
- **기대 결과**: 본인 데이터만 조회 가능

### 18. PV 전파 로그: applied_to='PV' / 'ARREAR' 구분
- **목적**: 전파 로그 정확성
- **절차**:
  1. +PV 발생 → 미수 상계 → 남은 금액 PV 적립
  2. pv_propagation_log 확인
- **기대 결과**: 
  - 미수 상계분: applied_to='ARREAR'
  - PV 적립분: applied_to='PV'

### 19. 관리자 감사 로그: 회원 등록/PV 조정 기록
- **목적**: 관리자 행위 기록
- **절차**:
  1. 관리자가 회원 등록
  2. 관리자가 PV 조정
  3. admin_audit 테이블 확인
- **기대 결과**: 
  - action='CREATE_MEMBER', 'ADJUST_PV' 기록
  - detail에 상세 정보 JSON 저장

### 20. API 권한: 회원은 admin API 호출 불가
- **목적**: API 보안
- **절차**:
  1. 일반 회원 토큰으로 /atomy/api/admin/members 호출
- **기대 결과**: 403 Forbidden

---

## 테스트 실행 가이드

### 수동 테스트
1. 각 시나리오를 브라우저 또는 Postman에서 실행
2. DB 상태를 MySQL 클라이언트로 직접 확인

### 자동화 테스트 (권장)
```bash
# PHPUnit 테스트 작성 (별도 구현 필요)
php artisan test
```

### 부하 테스트
```bash
# Apache Bench 예시
ab -n 1000 -c 10 https://100serolife.co.kr/atomy/api/me/balance
```

---

## 버그 발견 시 보고 양식

- **시나리오 번호**: 
- **재현 절차**: 
- **기대 결과**: 
- **실제 결과**: 
- **에러 메시지**: 
- **Laravel 로그**: storage/logs/laravel.log
