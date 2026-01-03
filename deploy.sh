#!/bin/bash
set -e

echo "🚀 Binary PV System 배포 시작..."

# 1. Composer install
echo "📦 Composer 의존성 설치 중..."
composer install --no-dev --optimize-autoloader

# 2. 환경 변수 체크
if [ ! -f .env ]; then
    echo "📝 .env 파일 생성 중..."
    cp .env.example .env
    php artisan key:generate
    echo "⚠️  .env 파일을 수정하여 DB 설정을 완료하세요!"
fi

# 3. 캐시 클리어
echo "🗑️  캐시 클리어 중..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. 마이그레이션 + 시딩
echo "🗄️  데이터베이스 마이그레이션 중..."
php artisan migrate --force

echo "🌱 초기 데이터 시딩 중..."
php artisan db:seed --class=InitialPayoutSettingSeeder --force
php artisan db:seed --class=InitialAdminSeeder --force

# 5. 캐시 최적화
echo "⚡ 캐시 최적화 중..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. 권한 설정
echo "🔐 권한 설정 중..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R nginx:nginx storage bootstrap/cache 2>/dev/null || echo "⚠️  권한 설정 수동 필요"

echo "✅ 배포 완료!"
echo ""
echo "📌 다음 단계:"
echo "1. .env 파일에서 DB 설정 확인"
echo "2. Nginx 설정 파일 적용: sudo cp nginx/100serolife_subdir.conf /etc/nginx/sites-available/100serolife.conf"
echo "3. Nginx 재시작: sudo systemctl restart nginx"
echo "4. 초기 관리자 계정: admin / admin1234!"
