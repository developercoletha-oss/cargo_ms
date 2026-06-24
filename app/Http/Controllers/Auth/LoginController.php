<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = Str::lower(trim($credentials['email']));
        $remember = $request->boolean('remember');
        $result = DB::transaction(function () use ($credentials): array {
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$credentials['email']])
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return ['status' => 'invalid', 'user' => null];
            }

            if ($user->login_locked_until?->isFuture()) {
                return [
                    'status' => 'locked',
                    'user' => $user,
                    'locked_until' => $user->login_locked_until,
                ];
            }

            if ($user->login_locked_until?->isPast()) {
                $user->forceFill([
                    'failed_login_attempts' => 0,
                    'login_locked_until' => null,
                ])->save();
            }

            if (! $user->is_active) {
                return ['status' => 'inactive', 'user' => $user];
            }

            if (! Hash::check($credentials['password'], $user->password)) {
                $attempts = $user->failed_login_attempts + 1;
                $maxAttempts = (int) config('auth_security.login_max_attempts', 3);
                $lockedUntil = $attempts >= $maxAttempts
                    ? now()->addSeconds((int) config('auth_security.login_lock_seconds', 86400))
                    : null;

                $user->forceFill([
                    'failed_login_attempts' => $attempts,
                    'login_locked_until' => $lockedUntil,
                ])->save();

                return [
                    'status' => $lockedUntil ? 'locked' : 'invalid',
                    'user' => $user,
                    'attempts_remaining' => max(0, $maxAttempts - $attempts),
                    'locked_until' => $lockedUntil,
                ];
            }

            $user->forceFill([
                'failed_login_attempts' => 0,
                'login_locked_until' => null,
                'last_login_at' => now(),
            ])->save();

            return ['status' => 'authenticated', 'user' => $user];
        }, 3);

        if ($result['status'] === 'inactive') {
            throw ValidationException::withMessages([
                'email' => 'Your account is pending admin approval.',
            ]);
        }

        if ($result['status'] === 'locked') {
            event(new Failed('web', $result['user'], $credentials));

            throw ValidationException::withMessages([
                'email' => $this->lockoutMessage($result['locked_until']),
            ]);
        }

        if ($result['status'] === 'invalid') {
            event(new Failed('web', $result['user'], $credentials));

            $remaining = $result['attempts_remaining'] ?? null;
            $message = is_int($remaining)
                ? "These credentials do not match our records. {$remaining} login attempt(s) remaining."
                : 'These credentials do not match our records.';

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        Auth::login($result['user'], $remember);
        $request->session()->regenerate();

        $user = $request->user();

        return redirect()->intended(route('dashboard.index'))
            ->with('success', 'Welcome back, '.$request->user()->full_name.'.');
    }

    private function lockoutMessage(?CarbonInterface $lockedUntil): string
    {
        if (! $lockedUntil) {
            return 'Too many failed login attempts. Your account is locked for 24 hours.';
        }

        return 'Too many failed login attempts. Your account is locked until '
            .$lockedUntil->timezone(config('app.timezone'))->format('d M Y, H:i T').'.';
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'You have been logged out successfully.');
    }
}
