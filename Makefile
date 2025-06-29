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
