# Klient Backend

Laravel API for Klient — a client portal for freelancers to manage invoices, projects, and clients in one place.

## What's inside

- User auth with Sanctum (register, login, password reset)
- Invoice management with PDF export
- Multi-currency support (USD, PKR, EUR, GBP, AED, INR)
- Projects and tasks
- Client management
- File attachments for projects
- Public share links so clients can view progress without an account
- Auto-marks invoices as overdue daily

## Tech stack

- Laravel 12
- MySQL
- Laravel Sanctum
- barryvdh/laravel-dompdf for PDFs
- Deployed on Railway

## Getting started

You'll need PHP 8.2+, Composer, and MySQL installed.

    git clone https://github.com/saad-dev-1/clienthub-backend.git
    cd clienthub-backend
    composer install
    cp .env.example .env
    php artisan key:generate

Set up your database in `.env`, then:

    php artisan migrate
    php artisan serve

The API runs at `http://localhost:8000`.

## Environment setup

The important ones:

    APP_URL=http://localhost:8000
    FRONTEND_URL=http://localhost:5173

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=klient
    DB_USERNAME=root
    DB_PASSWORD=

    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=
    MAIL_PASSWORD=
    MAIL_ENCRYPTION=tls

`FRONTEND_URL` is used for password reset links, so make sure it matches where your frontend is running.

## Email setup

Password resets need a mailer. I use Gmail SMTP with an app password. Generate one from your Google Account → Security → App Passwords, then drop the 16-character password into `MAIL_PASSWORD`.

For local testing, you can also set `MAIL_MAILER=log` and the reset link will end up in `storage/logs/laravel.log`.

## Scheduler

Overdue invoices are handled by a scheduled command:

    php artisan invoices:mark-overdue

In production, add this cron entry:

    * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

## API routes

Everything lives under `/api`. Public routes handle registration, login, and password reset. Protected routes use Sanctum bearer tokens.

You can see the full list with:

    php artisan route:list

## Deploying

Push to GitHub, connect the repo on Railway, add a MySQL service, and set your environment variables. Migrations run automatically on deploy if you add the pre-deploy command.

## Project layout

    app/Http/Controllers/Api   — API controllers
    app/Models                 — Eloquent models
    app/Console/Commands       — Scheduled commands
    database/migrations        — Schema
    routes/api.php             — API routes
    resources/views            — PDF templates

## Author

Saad Ahmed
[github.com/saad-dev-1](https://github.com/saad-dev-1)
