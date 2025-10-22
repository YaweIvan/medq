@echo off
echo ========================================
echo MedQ Quiz Platform Setup
echo ========================================
echo.

echo Installing Composer dependencies...
composer install
echo.

echo Running database migrations...
php artisan migrate
echo.

echo Seeding database with sample data...
php artisan db:seed
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Default Admin Login:
echo Email: admin@medq.com
echo Password: password
echo.
echo Default Quizzer Login:
echo Email: john@example.com
echo Password: password
echo.
echo To start the application, run:
echo php artisan serve
echo.
echo Then visit: http://localhost:8000
echo ========================================
pause