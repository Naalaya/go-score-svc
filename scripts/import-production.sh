#!/bin/bash

# Production Data Import Script for Render.com
echo "🚀 Starting production data import on Render..."

# Set environment
export APP_ENV=production
export APP_DEBUG=false

# Check if CSV file exists
if [[ ! -f "database/seeders/diem_thi_thpt_2024.csv" ]]; then
    echo "❌ CSV file not found!"
    exit 1
fi

# Display file info
echo "📊 CSV file size: $(du -h database/seeders/diem_thi_thpt_2024.csv | cut -f1)"
echo "📏 Total lines: $(wc -l < database/seeders/diem_thi_thpt_2024.csv)"

# Set memory limits for production environment
export PHP_MEMORY_LIMIT=512M

# Ensure database is ready
echo "🗄️  Preparing database..."
php artisan migrate --force

# Seed subjects if needed
echo "📚 Seeding subjects..."
php artisan db:seed --class=SubjectSeeder --force

# Start import with production-optimized settings
echo "🏃 Starting fast import (this may take 15-30 minutes)..."
php artisan scores:fast-import \
    --batch=1000 \
    --chunk=5000 \
    --memory-limit=512M

# Verify import
echo "🔍 Verifying import results..."
TOTAL_SCORES=$(php artisan tinker --execute="echo App\Models\Score::count();")
echo "✅ Total scores imported: $TOTAL_SCORES"

# Clear caches for better performance
echo "🧹 Clearing caches..."
php artisan config:cache
php artisan route:cache

echo "🎉 Production import completed successfully!"
echo "📈 Ready to serve $(printf "%'d" $TOTAL_SCORES) student records!"
