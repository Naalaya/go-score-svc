web: php -S 0.0.0.0:$PORT -t public
release: php artisan migrate --force && php artisan db:seed --force --class=SubjectSeeder
