# Binary PV 시스템 - MySQL 회원 관리 가이드

## 🔐 MySQL 접속

```bash
# 터미널에서 접속
mysql -u atomy_user -patomy_password_2026 binary_pv

# 또는 비밀번호 프롬프트
mysql -u atomy_user -p binary_pv
# Password: atomy_password_2026
```

## 📊 1. 회원 조회

### 전체 회원 목록
```sql
SELECT 
    id,
    username,
    name,
    phone,
    email,
    sponsor_id,
    binary_parent_id,
    binary_position,
    role,
    status,
    created_at
FROM members
ORDER BY id;
```

### 특정 회원 상세 정보
```sql
SELECT 
    m.*,
    s.username AS sponsor_username,
    s.name AS sponsor_name,
    p.username AS parent_username,
    p.name AS parent_name
FROM members m
LEFT JOIN members s ON m.sponsor_id = s.id
LEFT JOIN members p ON m.binary_parent_id = p.id
WHERE m.username = 'test001';
```

### 회원 검색 (이름 또는 아이디)
```sql
SELECT id, username, name, phone, status
FROM members
WHERE name LIKE '%테스트%' 
   OR username LIKE '%test%'
ORDER BY created_at DESC;
```

### 활성 회원만 조회
```sql
SELECT id, username, name, status
FROM members
WHERE status = 'ACTIVE'
ORDER BY created_at DESC;
```

## 👥 2. 조직도 조회

### 특정 회원의 직계 하위 (자녀)
```sql
SELECT 
    id,
    username,
    name,
    binary_position AS position,
    status
FROM members
WHERE binary_parent_id = 1
ORDER BY binary_position;
```

### 특정 회원의 모든 하위 조직 (Closure 테이블 활용)
```sql
SELECT 
    bc.depth,
    bc.side_from_ancestor AS side,
    m.id,
    m.username,
    m.name,
    m.binary_position
FROM binary_closure bc
JOIN members m ON bc.descendant_id = m.id
WHERE bc.ancestor_id = 1
  AND bc.depth > 0
ORDER BY bc.depth, bc.side_from_ancestor, m.binary_position;
```

### 특정 회원의 상위 라인 (스폰서 라인)
```sql
WITH RECURSIVE sponsor_tree AS (
    SELECT id, username, name, sponsor_id, 0 AS level
    FROM members
    WHERE id = 3
    
    UNION ALL
    
    SELECT m.id, m.username, m.name, m.sponsor_id, st.level + 1
    FROM members m
    JOIN sponsor_tree st ON m.id = st.sponsor_id
)
SELECT level, id, username, name
FROM sponsor_tree
ORDER BY level;
```

### 바이너리 트리 구조 (좌/우 하위)
```sql
-- 좌측 하위
SELECT 
    m.id,
    m.username,
    m.name,
    m.status
FROM binary_closure bc
JOIN members m ON bc.descendant_id = m.id
WHERE bc.ancestor_id = 1
  AND bc.side_from_ancestor = 'L'
  AND bc.depth > 0
ORDER BY bc.depth;

-- 우측 하위
SELECT 
    m.id,
    m.username,
    m.name,
    m.status
FROM binary_closure bc
JOIN members m ON bc.descendant_id = m.id
WHERE bc.ancestor_id = 1
  AND bc.side_from_ancestor = 'R'
  AND bc.depth > 0
ORDER BY bc.depth;
```

## ➕ 3. 회원 추가

### 기본 회원 추가 (추천인: admin, 배치: 우측)
```sql
-- 1. 회원 생성
INSERT INTO members (
    username, 
    password_hash, 
    name, 
    phone, 
    email,
    sponsor_id,
    binary_parent_id,
    binary_position,
    self_pv,
    role,
    status,
    created_at,
    updated_at
) VALUES (
    'test002',                                      -- 아이디
    '$2y$12$LQv3c1yYqBYYCQkQ6qBPWe7E5qYiPPPPPPPPPPPP', -- 비밀번호 (bcrypt)
    '테스트회원2',                                   -- 이름
    '010-2222-3333',                                -- 연락처
    'test002@test.com',                             -- 이메일
    1,                                              -- 추천인 ID (admin)
    1,                                              -- 바이너리 부모 ID
    'R',                                            -- 배치 위치 (L/R)
    0,                                              -- 초기 PV
    'MEMBER',                                       -- 역할
    'ACTIVE',                                       -- 상태
    NOW(),
    NOW()
);

-- 2. 방금 생성된 회원의 ID 확인
SELECT LAST_INSERT_ID() AS new_member_id;

-- 3. PV Balance 초기화 (new_member_id를 실제 ID로 변경)
INSERT INTO pv_balance (
    member_id,
    left_pv,
    right_pv,
    left_arrear_pv,
    right_arrear_pv,
    cycle_no,
    cycle_started_at,
    updated_at
) VALUES (
    LAST_INSERT_ID(),  -- 위에서 생성된 회원 ID
    0,
    0,
    0,
    0,
    0,
    NOW(),
    NOW()
);

-- 4. Binary Closure 생성 (new_member_id를 실제 ID로 변경)
SET @new_member_id = LAST_INSERT_ID();
SET @parent_id = 1;
SET @position = 'R';

-- 자기 자신 링크
INSERT INTO binary_closure (ancestor_id, descendant_id, depth, side_from_ancestor)
VALUES (@new_member_id, @new_member_id, 0, NULL);

-- 부모의 모든 상위 경로 복사
INSERT INTO binary_closure (ancestor_id, descendant_id, depth, side_from_ancestor)
SELECT 
    bc.ancestor_id,
    @new_member_id,
    bc.depth + 1,
    CASE 
        WHEN bc.depth = 0 THEN @position
        ELSE bc.side_from_ancestor
    END
FROM binary_closure bc
WHERE bc.descendant_id = @parent_id;
```

### 비밀번호 해시 생성 (PHP 필요)
```bash
# 터미널에서 실행
cd /var/www/atomy/webapp
php -r "echo password_hash('원하는비밀번호', PASSWORD_BCRYPT) . PHP_EOL;"
```

## ✏️ 4. 회원 수정

### 기본 정보 수정
```sql
UPDATE members
SET 
    name = '수정된이름',
    phone = '010-9999-8888',
    email = 'new@email.com',
    updated_at = NOW()
WHERE username = 'test001';
```

### 비밀번호 변경
```sql
-- 먼저 PHP로 해시 생성 후 (위 방법 참고)
UPDATE members
SET 
    password_hash = '$2y$12$NEW_HASHED_PASSWORD_HERE',
    updated_at = NOW()
WHERE username = 'test001';
```

### 회원 상태 변경
```sql
-- 활성화
UPDATE members
SET status = 'ACTIVE', updated_at = NOW()
WHERE username = 'test001';

-- 비활성화
UPDATE members
SET status = 'INACTIVE', updated_at = NOW()
WHERE username = 'test001';

-- 정지
UPDATE members
SET status = 'SUSPENDED', updated_at = NOW()
WHERE username = 'test001';
```

### 역할 변경 (일반 회원 → 관리자)
```sql
UPDATE members
SET role = 'ADMIN', updated_at = NOW()
WHERE username = 'test001';

-- 관리자 → 일반 회원
UPDATE members
SET role = 'MEMBER', updated_at = NOW()
WHERE username = 'test001';
```

## 🗑️ 5. 회원 삭제 (주의!)

### 회원 삭제 전 확인사항
```sql
-- 하위 조직 확인
SELECT COUNT(*) AS child_count
FROM members
WHERE binary_parent_id = 3;

-- PV 잔액 확인
SELECT left_pv, right_pv, left_arrear_pv, right_arrear_pv
FROM pv_balance
WHERE member_id = 3;

-- 수당 내역 확인
SELECT COUNT(*) AS payout_count
FROM payout_history
WHERE member_id = 3;
```

### 회원 삭제 (CASCADE로 자동 삭제)
```sql
-- ⚠️ 경고: 되돌릴 수 없습니다!
-- 하위 조직이 있는 회원은 삭제하면 안 됩니다!

-- 안전한 삭제 (하위가 없는 경우만)
DELETE FROM members
WHERE id = 3
  AND NOT EXISTS (
      SELECT 1 FROM members WHERE binary_parent_id = 3
  );
```

### 회원 비활성화 (권장)
```sql
-- 삭제 대신 비활성화를 권장
UPDATE members
SET 
    status = 'INACTIVE',
    updated_at = NOW()
WHERE id = 3;
```

## 💰 6. PV 관리

### 회원 PV 잔액 조회
```sql
SELECT 
    m.id,
    m.username,
    m.name,
    pb.left_pv,
    pb.right_pv,
    pb.left_arrear_pv,
    pb.right_arrear_pv,
    pb.cycle_no,
    (pb.left_pv + pb.right_pv) AS total_pv,
    LEAST(pb.left_pv, pb.right_pv) AS matchable_pv
FROM members m
JOIN pv_balance pb ON m.id = pb.member_id
WHERE m.username = 'test001';
```

### PV 원장 조회
```sql
SELECT 
    pl.id,
    pl.member_id,
    m.username,
    m.name,
    pl.pv_amount,
    pl.type,
    pl.ref_no,
    pl.memo,
    pl.occurred_at
FROM pv_ledger pl
JOIN members m ON pl.member_id = m.id
WHERE m.username = 'test001'
ORDER BY pl.occurred_at DESC
LIMIT 20;
```

### PV 수동 추가 (관리자용)
```sql
-- PV 원장에 기록
INSERT INTO pv_ledger (
    member_id,
    pv_amount,
    occurred_at,
    type,
    ref_no,
    memo
) VALUES (
    3,                      -- 회원 ID
    10000,                  -- PV 금액
    NOW(),                  -- 발생 시간
    'PURCHASE',             -- 유형
    'MANUAL-2026-001',      -- 참조 번호
    '관리자 수동 추가'       -- 메모
);

-- ⚠️ 주의: PV 전파는 Laravel PvService를 통해 처리해야 합니다!
-- 직접 pv_balance를 수정하지 마세요!
```

## 📈 7. 통계 조회

### 전체 회원 통계
```sql
SELECT 
    COUNT(*) AS total_members,
    SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) AS active_members,
    SUM(CASE WHEN status = 'INACTIVE' THEN 1 ELSE 0 END) AS inactive_members,
    SUM(CASE WHEN role = 'ADMIN' THEN 1 ELSE 0 END) AS admin_count
FROM members;
```

### 오늘 가입한 회원
```sql
SELECT id, username, name, created_at
FROM members
WHERE DATE(created_at) = CURDATE()
ORDER BY created_at DESC;
```

### 월별 가입 통계
```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') AS month,
    COUNT(*) AS new_members
FROM members
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month DESC;
```

### 조직도 깊이별 회원 수
```sql
SELECT 
    bc.depth,
    COUNT(DISTINCT bc.descendant_id) AS member_count
FROM binary_closure bc
WHERE bc.ancestor_id = 1
GROUP BY bc.depth
ORDER BY bc.depth;
```

### 좌/우 조직 통계
```sql
SELECT 
    bc.side_from_ancestor AS side,
    COUNT(DISTINCT bc.descendant_id) AS member_count
FROM binary_closure bc
WHERE bc.ancestor_id = 1
  AND bc.depth > 0
GROUP BY bc.side_from_ancestor;
```

## 🔍 8. 문제 해결

### Binary Closure 일관성 검사
```sql
-- 자기 자신 링크가 없는 회원
SELECT m.id, m.username, m.name
FROM members m
LEFT JOIN binary_closure bc ON m.id = bc.descendant_id AND bc.depth = 0
WHERE bc.ancestor_id IS NULL;

-- Closure는 있지만 members에 없는 경우
SELECT DISTINCT bc.descendant_id
FROM binary_closure bc
LEFT JOIN members m ON bc.descendant_id = m.id
WHERE m.id IS NULL;
```

### PV Balance 없는 회원 찾기
```sql
SELECT m.id, m.username, m.name
FROM members m
LEFT JOIN pv_balance pb ON m.id = pb.member_id
WHERE pb.member_id IS NULL;
```

### 배치 위치 중복 확인
```sql
SELECT 
    binary_parent_id,
    binary_position,
    COUNT(*) AS count
FROM members
WHERE binary_parent_id IS NOT NULL
GROUP BY binary_parent_id, binary_position
HAVING COUNT(*) > 1;
```

## 🛠️ 9. 유용한 프로시저

### 회원 완전 등록 프로시저
```sql
DELIMITER //

CREATE PROCEDURE add_member(
    IN p_username VARCHAR(50),
    IN p_password_hash VARCHAR(255),
    IN p_name VARCHAR(50),
    IN p_phone VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_sponsor_id INT,
    IN p_binary_parent_id INT,
    IN p_binary_position CHAR(1)
)
BEGIN
    DECLARE v_new_id INT;
    
    START TRANSACTION;
    
    -- 1. 회원 생성
    INSERT INTO members (
        username, password_hash, name, phone, email,
        sponsor_id, binary_parent_id, binary_position,
        self_pv, role, status, created_at, updated_at
    ) VALUES (
        p_username, p_password_hash, p_name, p_phone, p_email,
        p_sponsor_id, p_binary_parent_id, p_binary_position,
        0, 'MEMBER', 'ACTIVE', NOW(), NOW()
    );
    
    SET v_new_id = LAST_INSERT_ID();
    
    -- 2. PV Balance 초기화
    INSERT INTO pv_balance (
        member_id, left_pv, right_pv, left_arrear_pv, right_arrear_pv,
        cycle_no, cycle_started_at, updated_at
    ) VALUES (
        v_new_id, 0, 0, 0, 0, 0, NOW(), NOW()
    );
    
    -- 3. Binary Closure - 자기 자신
    INSERT INTO binary_closure (ancestor_id, descendant_id, depth, side_from_ancestor)
    VALUES (v_new_id, v_new_id, 0, NULL);
    
    -- 4. Binary Closure - 부모 경로 복사
    INSERT INTO binary_closure (ancestor_id, descendant_id, depth, side_from_ancestor)
    SELECT 
        bc.ancestor_id,
        v_new_id,
        bc.depth + 1,
        CASE 
            WHEN bc.depth = 0 THEN p_binary_position
            ELSE bc.side_from_ancestor
        END
    FROM binary_closure bc
    WHERE bc.descendant_id = p_binary_parent_id;
    
    COMMIT;
    
    SELECT v_new_id AS new_member_id;
END //

DELIMITER ;
```

### 프로시저 사용 예시
```sql
-- 비밀번호 해시는 미리 생성해야 함
CALL add_member(
    'test003',                                          -- username
    '$2y$12$YOUR_BCRYPT_HASH_HERE',                   -- password_hash
    '테스트회원3',                                      -- name
    '010-3333-4444',                                   -- phone
    'test003@test.com',                                -- email
    1,                                                 -- sponsor_id
    1,                                                 -- binary_parent_id
    'R'                                                -- binary_position
);
```

## 📝 10. 백업 및 복원

### 특정 회원 데이터 백업
```sql
-- 백업 파일로 내보내기
mysqldump -u atomy_user -patomy_password_2026 binary_pv \
  --where="id IN (SELECT descendant_id FROM binary_closure WHERE ancestor_id = 1)" \
  members binary_closure pv_balance pv_ledger > member_backup.sql
```

### 전체 데이터베이스 백업
```bash
mysqldump -u atomy_user -patomy_password_2026 binary_pv > binary_pv_backup_$(date +%Y%m%d).sql
```

---

## ⚠️ 중요 주의사항

1. **PV 전파는 직접 수정하지 마세요**
   - Laravel의 `PvService`를 통해서만 처리해야 합니다
   - 직접 `pv_balance` 수정 시 전파가 누락될 수 있습니다

2. **하위 조직이 있는 회원은 삭제하지 마세요**
   - 조직 구조가 깨집니다
   - 대신 `status = 'INACTIVE'`로 변경하세요

3. **Binary Closure는 수동으로 수정하지 마세요**
   - 잘못된 수정은 전체 트리 구조를 파괴합니다
   - Laravel의 `ClosureService`를 사용하세요

4. **백업은 정기적으로**
   - 매일 자동 백업 설정을 권장합니다

5. **프로덕션 환경에서는**
   - 직접 SQL 실행보다는 Laravel Artisan 명령어 사용을 권장합니다
   - 관리자 페이지를 통한 관리를 권장합니다

---

**문서 작성일**: 2026-01-03  
**데이터베이스**: binary_pv  
**사용자**: atomy_user
