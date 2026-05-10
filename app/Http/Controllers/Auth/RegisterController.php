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
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        User::create([
            'full_name' => $credentials['full_name'],
            'name' => Str::of($credentials['full_name'])->trim()->replace(' ', '.')->lower()->toString(),
            'email' => $credentials['email'],
            'phone' => $credentials['phone'],
            'company_name' => $credentials['company_name'],
            'address' => $credentials['address'],
            'country' => 'TZ',
            'timezone' => 'Africa/Dar_es_Salaam',
            'role' => 'customer',
            'is_active' => false,
            'password' => $credentials['password'],
        ]);

        return redirect()->route('login')
            ->with('status', 'Registration submitted successfully. Please wait for admin approval. You will receive an email once your account is activated.');
    }

}
