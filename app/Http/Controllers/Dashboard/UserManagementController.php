<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\CustomerApprovedMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        User::create($this->mapPayload($validated, true));

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('dashboard.users.show', ['user' => $user]);
    }

    public function edit(User $user): View
    {
        return view('dashboard.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $wasInactiveCustomer = ! $user->is_active && ($user->role === 'customer');
        $validated = $this->validateUser($request, $user->id);

        $user->update($this->mapPayload($validated, false));

        if ($wasInactiveCustomer && $user->is_active) {
            try {
                Mail::to($user->email)->send(new CustomerApprovedMail($user));
            } catch (\Throwable $exception) {
                // Keep approval successful even if email service fails.
            }
        }

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function validateUser(Request $request, ?int $userId = null): array
    {
        $passwordRules = $userId ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(['admin', 'hgadmin', 'manager', 'staff', 'user', 'customer'])],
            'address' => ['nullable', 'string', 'max:500'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'password' => $passwordRules,
        ]);
    }

    private function mapPayload(array $validated, bool $creating): array
    {
        $country = strtoupper((string) ($validated['country'] ?? ''));
        $timezone = $validated['timezone'] ?? $this->timezoneForCountry($country);

        $payload = [
            'name' => $validated['full_name'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'address' => $validated['address'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'country' => $country !== '' ? $country : null,
            'timezone' => $timezone,
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : false,
        ];

        if ($creating || !empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        return $payload;
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
