<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * @throws ValidationException
     */
    public function register(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'country' => ['required', 'string', 'size:2'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $country = strtoupper($credentials['country']);

        User::create([
            'full_name' => $credentials['full_name'],
            'name' => Str::of($credentials['full_name'])->trim()->replace(' ', '.')->lower()->toString(),
            'email' => $credentials['email'],
            'phone' => $credentials['phone'],
            'company_name' => $credentials['company_name'],
            'address' => $credentials['address'],
            'country' => $country,
            'timezone' => $this->timezoneForCountry($country),
            'role' => 'customer',
            'is_active' => false,
            'password' => $credentials['password'],
        ]);

        return redirect()->route('login')
            ->with('status', 'Registration submitted successfully. Please wait for admin approval. You will receive an email once your account is activated.');
    }

    private function timezoneForCountry(string $country): ?string
    {
        return match ($country) {
            'KE' => 'Africa/Nairobi',
            'TZ' => 'Africa/Dar_es_Salaam',
            'UG' => 'Africa/Kampala',
            'RW' => 'Africa/Kigali',
            'BU' => 'Africa/Bujumbura',
            default => null,
        };
    }
}
