# CI/CD Pipeline

## Jobs

**tests** — runs on PHP 8.3 and 8.4 in parallel.
Boots a MySQL 8 service (`training_test` DB), installs dependencies, generates an APP_KEY,
runs `php artisan migrate` (which loads `database/schema/mysql-schema.sql` then pending migrations),
and executes the full PHPUnit suite via `php artisan test`.

**lint** — runs on PHP 8.3 only.
Installs dependencies and runs `vendor/bin/pint --test` to enforce the `laravel` preset
defined in `pint.json`. Fails the build on any style violation without modifying files.

## Triggers

Both jobs run on every push and every pull request, for all branches.

## Running tests locally

Requirements: MySQL running with a `training_test` database and a `.env.testing` file.

```bash
# Create the test database once
mysql -u root -e "CREATE DATABASE IF NOT EXISTS training_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Copy and configure .env.testing
cp .env.example .env.testing
# Set DB_DATABASE=training_test and adjust credentials in .env.testing

# Run the full suite
php artisan test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage (requires Xdebug or PCOV)
php artisan test --coverage
```

## Style check locally

```bash
# Check without modifying files (mirrors CI)
vendor/bin/pint --test

# Fix violations automatically
vendor/bin/pint
```
