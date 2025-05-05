# ivplflmnt

## How to run

- Install dependencies `composer install`
- Start a mysql/mariadb database or use the provided `docker compose up`
- Seed the db `php artisan migrate --seed`
- Start the laravel dev server `php artisan serve`
- Open `http://127.0.0.1:8000/ivpl`
- (Generate app key if needed)
- (Create a user ?) `php artisan make:filament-user`

## E-mail

You can use the mailcatcher app on `http://127.0.0.1:1080/`
