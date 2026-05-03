<?php

return [
    'login_max_attempts' => env('AUTH_LOGIN_MAX_ATTEMPTS', 3),
    'login_lock_seconds' => env('AUTH_LOGIN_LOCK_SECONDS', 10800),
    'forgot_max_attempts' => env('AUTH_FORGOT_MAX_ATTEMPTS', 3),
    'forgot_lock_seconds' => env('AUTH_FORGOT_LOCK_SECONDS', 10800),
];
