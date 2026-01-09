<!-- Lancer le serveur adress local -->

php artisan serve --host=0.0.0.0 --port=8000

.env

php artisan key:generate

php artisan migrate

php artisan permissions:create-app-role
