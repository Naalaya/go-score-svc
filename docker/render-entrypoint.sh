#!/bin/bash
set -e

echo "🚀 Starting Go Score Service deployment on Render..."

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
until php artisan migrate:status &> /dev/null; do
    echo "Database not ready, waiting 5 seconds..."
    sleep 5
done

# Generate application key if not exists
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    export APP_KEY=$(php artisan key:generate --show --no-ansi)
fi

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Seed essential data (subjects)
echo "🌱 Seeding essential data..."
php artisan db:seed --force --class=SubjectSeeder

# Create storage link if it doesn't exist
echo "🔗 Creating storage link..."
php artisan storage:link || echo "Storage link already exists"

# Set final permissions
echo "🔐 Setting final permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Application setup complete!"
echo "🌍 Starting Apache web server..."

# Start Apache in foreground
exec apache2-foreground
