# Binary PV System 배포 가이드

## 환경 요구사항

- Ubuntu 20.04+ (카페24 VPS)
- Nginx
- PHP 8.1+ (php-fpm 포함)
- MySQL 8.0+
- Composer

## 배포 절차

### 1. 서버 기본 설정

```bash
# PHP 및 필수 패키지 설치
sudo apt update
sudo apt install -y nginx php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip unzip

# Composer 설치
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. MySQL 데이터베이스 생성

```bash
# MySQL 접속
mysql -u root -p

# 데이터베이스 생성
CREATE DATABASE binary_pv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 사용자 생성 및 권한 부여 (선택사항)
CREATE USER 'binary_pv_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON binary_pv.* TO 'binary_pv_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Laravel 프로젝트 배포

```bash
cd /var/www/atomy/webapp

# .env 파일 설정
cp .env.example .env
nano .env

# DB 설정 수정:
# DB_DATABASE=binary_pv
# DB_USERNAME=root (또는 생성한 사용자)
# DB_PASSWORD=your_password

# 배포 스크립트 실행
chmod +x deploy.sh
./deploy.sh
```

### 4. Nginx 설정

```bash
# Nginx 설정 파일 복사
sudo cp /var/www/atomy/webapp/nginx/100serolife_subdir.conf /etc/nginx/sites-available/100serolife.conf

# 심볼릭 링크 생성
sudo ln -s /etc/nginx/sites-available/100serolife.conf /etc/nginx/sites-enabled/

# 기존 default 설정 비활성화 (필요 시)
# sudo rm /etc/nginx/sites-enabled/default

# Nginx 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl restart nginx
```

### 5. HTTPS 설정 (Certbot)

```bash
# Certbot 설치
sudo apt install -y certbot python3-certbot-nginx

# SSL 인증서 발급
sudo certbot --nginx -d 100serolife.co.kr -d www.100serolife.co.kr

# 자동 갱신 테스트
sudo certbot renew --dry-run
```

### 6. 초기 로그인

- **URL**: https://100serolife.co.kr/atomy/login
- **관리자 계정**: 
  - 아이디: `admin`
  - 비밀번호: `admin1234!`

⚠️ **반드시 초기 로그인 후 비밀번호를 변경하세요!**

## 트러블슈팅

### 권한 문제

```bash
cd /var/www/atomy/webapp
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### PHP-FPM 소켓 경로 확인

```bash
# PHP-FPM 소켓 위치 확인
ls -l /var/run/php/

# Nginx 설정에서 fastcgi_pass 경로가 일치하는지 확인
# 예: unix:/var/run/php/php8.1-fpm.sock
```

### Laravel 로그 확인

```bash
tail -f /var/www/atomy/webapp/storage/logs/laravel.log
```

### Nginx 로그 확인

```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

## 업데이트 절차

```bash
cd /var/www/atomy/webapp
git pull origin main  # 최신 코드 가져오기
./deploy.sh  # 배포 스크립트 재실행
```

## 주의사항

1. `/atomy` 경로는 서브디렉토리로 작동하며, 기존 사이트(`/var/www/100serolife.co.kr/public`)는 그대로 유지됩니다.
2. 모든 Laravel 라우트는 `/atomy` prefix를 가집니다.
3. API 라우트는 `/atomy/api` prefix를 가집니다.
4. 프로덕션 환경에서는 `.env`의 `APP_DEBUG=false`로 설정하세요.
5. 정기적으로 데이터베이스 백업을 수행하세요.

## 백업 스크립트 예시

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p binary_pv > /backup/binary_pv_$DATE.sql
```

## 문의

시스템 운영 중 문제가 발생하면 `storage/logs/laravel.log`를 확인하고, 필요 시 개발팀에 문의하세요.
