#!/bin/bash
set -e

echo "Starting Go Score Service deployment on Render..."

# Create SQLite database directory and file
echo "Setting up SQLite database..."
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

# Set proper permissions for SQLite
echo "Setting SQLite permissions..."
chmod 664 /var/www/html/database/database.sqlite
chmod 755 /var/www/html/database
chown -R www-data:www-data /var/www/html/database

# Verify database file was created successfully
if [ -f "/var/www/html/database/database.sqlite" ]; then
    echo "SQLite database file created successfully"
    ls -la /var/www/html/database/database.sqlite
else
    echo "Failed to create SQLite database file"
    exit 1
fi

# Generate application key if not exists
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    export APP_KEY=$(php artisan key:generate --show --no-ansi)
fi

# Check if database is accessible (quick SQLite test)
echo "🔍 Testing SQLite database access..."
if php artisan migrate:status &> /dev/null; then
    echo "Database is accessible"

    echo "Running database migrations..."
    php artisan migrate:refresh --seed
else
    echo "ℹDatabase is new, will run migrations"

    echo "Running database migrations..."
    php artisan migrate --force

    echo "Seeding essential data..."
    php artisan db:seed --force

fi

# Create storage link if it doesn't exist
echo "Creating storage link..."
php artisan storage:link || echo "Storage link already exists"

# Set final permissions
echo "Setting final permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache
chmod 755 /var/www/html/database
chmod 664 /var/www/html/database/database.sqlite

echo "Application setup complete!"
echo "Starting Apache web server..."

# Start Apache in foreground
exec apache2-foreground
