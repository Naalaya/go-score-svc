db:
	php artisan migrate:refresh --seed

test:
	php artisan test

docs:
	php artisan l5-swagger:generate

git:
	git pull origin main

queue:
	php artisan queue:work --queue=high,default --sleep=5

import-fast:
	php artisan scores:fast-import --batch=500 --chunk=2500 --memory-limit=1G

import-production:
	@echo "Starting production data import..."
	php artisan migrate:fresh --force
	php artisan db:seed --class=SubjectSeeder --force
	php artisan scores:fast-import --batch=300 --chunk=1500 --memory-limit=1G
	@echo "Production import completed!"

import-safe:
	@echo "Starting SAFE import for limited memory environments..."
	php artisan migrate:fresh --force
	php artisan db:seed --class=SubjectSeeder --force
	php artisan scores:fast-import --batch=200 --chunk=1000 --memory-limit=512M
	@echo "Safe import completed!"

import-micro:
	@echo "Starting MICRO import for 128MB-256MB memory environments..."
	php artisan migrate:fresh --force
	php artisan db:seed --class=SubjectSeeder --force
	php artisan scores:micro-import --batch=25 --memory-limit=256M
	@echo "Micro import completed!"

deploy-with-data:
	git pull origin main
	composer install --no-dev --optimize-autoloader
	php artisan migrate --force
	php artisan db:seed --class=SubjectSeeder --force
	php artisan scores:fast-import --batch=250 --chunk=1250 --memory-limit=768M
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
