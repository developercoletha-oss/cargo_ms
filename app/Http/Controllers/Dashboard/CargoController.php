<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\TransportStaff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CargoController extends Controller
{
    private const TANZANIA_AREAS = [
        'Arusha',
        'Dar es Salaam',
        'Dodoma',
        'Geita',
        'Iringa',
        'Kagera',
        'Katavi',
        'Kigoma',
        'Kilimanjaro',
        'Lindi',
        'Manyara',
        'Mara',
        'Mbeya',
        'Morogoro',
        'Mtwara',
        'Mwanza',
        'Njombe',
        'Pemba',
        'Pwani',
        'Rukwa',
        'Ruvuma',
        'Shinyanga',
        'Simiyu',
        'Singida',
        'Songwe',
        'Tabora',
        'Tanga',
        'Zanzibar',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $search = (string) $request->query('search', '');

        User::query()
            ->where('role', 'staff')
            ->get(['id'])
            ->each(function (User $staffUser) {
                TransportStaff::firstOrCreate(
                    ['user_id' => $staffUser->id],
                    ['staff_code' => 'TSF-' . str_pad((string) $staffUser->id, 4, '0', STR_PAD_LEFT)]
                );
            });

        $query = Cargo::query()
            ->with(['customer', 'detail', 'transportStaff.user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('origin_city', 'like', "%{$search}%")
                        ->orWhere('destination_city', 'like', "%{$search}%")
                        ->orWhere('origin_country', 'like', "%{$search}%")
                        ->orWhere('destination_country', 'like', "%{$search}%");
                });
            });

        if ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        } elseif ($user->role === 'staff') {
            $query->whereHas('transportStaff', function ($staffQ) use ($user) {
                $staffQ->where('user_id', $user->id);
            });
        }

        $cargoes = $query->latest()->paginate(12)->withQueryString();

        $transportStaff = TransportStaff::query()
            ->with('user')
            ->where('is_active', true)
            ->orderBy('staff_code')
            ->get();

        $view = $user->role === 'customer'
            ? 'customer.cargo.index'
            : 'staff.cargo.index';

        return view($view, [
            'cargoes' => $cargoes,
            'search' => $search,
            'transportStaff' => $transportStaff,
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer') {
            abort(403, 'Only customers can create cargo.');
        }

        $validated = $this->validateCargo($request);

        $cargo = Cargo::create([
            'customer_id' => $user->id,
            'origin_country' => 'TZ',
            'origin_city' => $validated['origin_city'],
            'origin_address' => $validated['origin_address'] ?? null,
            'destination_country' => 'TZ',
            'destination_city' => $validated['destination_city'],
            'destination_address' => $validated['destination_address'] ?? null,
            'pickup_date' => $validated['pickup_date'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
            'status' => 'pending',
        ]);

        $cargo->detail()->create($this->detailPayload($validated));

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo created successfully.');
    }

    public function update(Request $request, Cargo $cargo): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer' || $cargo->customer_id !== $user->id) {
            abort(403, 'You can only edit your own cargo.');
        }
        if ($cargo->status !== 'pending') {
            return back()->with('error', 'Approved/disapproved cargo cannot be edited.');
        }

        $validated = $this->validateCargo($request);

        $cargo->update([
            'origin_country' => 'TZ',
            'origin_city' => $validated['origin_city'],
            'origin_address' => $validated['origin_address'] ?? null,
            'destination_country' => 'TZ',
            'destination_city' => $validated['destination_city'],
            'destination_address' => $validated['destination_address'] ?? null,
            'pickup_date' => $validated['pickup_date'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
        ]);

        $cargo->detail()->updateOrCreate([], $this->detailPayload($validated));

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo updated successfully.');
    }

    public function destroy(Request $request, Cargo $cargo): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer' || $cargo->customer_id !== $user->id) {
            abort(403, 'You can only delete your own cargo.');
        }
        if ($cargo->status !== 'pending') {
            return back()->with('error', 'Approved/disapproved cargo cannot be deleted.');
        }

        $cargo->delete();

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo deleted successfully.');
    }

    public function approve(Request $request, Cargo $cargo): RedirectResponse
    {
        $this->ensureReviewer($request->user());

        if ($cargo->status === 'approved') {
            return back()->with('info', 'Cargo is already approved.');
        }

        $cargo->update([
            'status' => 'approved',
            'approval_note' => $request->input('approval_note'),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'disapproved_by' => null,
            'disapproved_at' => null,
        ]);

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo approved successfully.');
    }

    public function disapprove(Request $request, Cargo $cargo): RedirectResponse
    {
        $this->ensureReviewer($request->user());

        if ($cargo->status === 'disapproved') {
            return back()->with('info', 'Cargo is already disapproved.');
        }

        $cargo->update([
            'status' => 'disapproved',
            'approval_note' => $request->input('approval_note'),
            'disapproved_by' => $request->user()->id,
            'disapproved_at' => now(),
            'transport_staff_id' => null,
        ]);

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo disapproved successfully.');
    }

    public function assign(Request $request, Cargo $cargo): RedirectResponse
    {
        $this->ensureReviewer($request->user());

        if ($cargo->status !== 'approved') {
            return back()->with('error', 'Only approved cargo can be assigned.');
        }

        $validated = $request->validate([
            'transport_staff_id' => ['required', Rule::exists('transport_staff', 'id')],
        ]);

        $cargo->update([
            'transport_staff_id' => (int) $validated['transport_staff_id'],
        ]);

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo assigned to transport officer.');
    }

    private function validateCargo(Request $request): array
    {
        return $request->validate([
            'origin_city' => ['required', 'string', 'max:120', Rule::in(self::TANZANIA_AREAS)],
            'origin_address' => ['nullable', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'max:120', Rule::in(self::TANZANIA_AREAS)],
            'destination_address' => ['nullable', 'string', 'max:255'],
            'pickup_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:pickup_date'],
            'description' => ['required', 'string', 'max:255'],
            'cargo_type' => ['nullable', 'string', 'max:100'],
            'weight_kg' => ['required', 'numeric', 'min:0.01'],
            'volume_cbm' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'package_count' => ['required', 'integer', 'min:1'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'is_fragile' => ['nullable', 'boolean'],
            'is_hazardous' => ['nullable', 'boolean'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
        ], [
            'origin_city.in' => 'Origin area must be a valid Tanzania area.',
            'destination_city.in' => 'Destination area must be a valid Tanzania area.',
        ]);
    }

    private function detailPayload(array $validated): array
    {
        return [
            'description' => $validated['description'],
            'cargo_type' => $validated['cargo_type'] ?? null,
            'weight_kg' => $validated['weight_kg'],
            'volume_cbm' => $validated['volume_cbm'] ?? null,
            'quantity' => $validated['quantity'],
            'package_count' => $validated['package_count'],
            'estimated_value' => $validated['estimated_value'] ?? null,
            'is_fragile' => (bool) ($validated['is_fragile'] ?? false),
            'is_hazardous' => (bool) ($validated['is_hazardous'] ?? false),
            'special_instructions' => $validated['special_instructions'] ?? null,
        ];
    }

    private function ensureReviewer(User $user): void
    {
        if (! in_array($user->role, ['admin', 'hgadmin', 'manager', 'staff'], true)) {
            abort(403, 'Only admin/manager/staff can review cargo.');
        }
    }
}
