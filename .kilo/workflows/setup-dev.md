---
description: Setup CFTMS development environment
agent: general
model: anthropic/claude-sonnet
---
Setup CFTMS development environment step by step.

## Setup Process
1. Install PHP dependencies: !`composer install`
2. Copy environment file: !`cp .env.example .env`
3. Generate app key: !`php artisan key:generate`
4. Run database migrations: !`php artisan migrate`
5. Start development server: !`php artisan serve`

Verify each step completes successfully before proceeding to the next. Check output for any errors and resolve them before continuing. @file .env.example for configuration reference.