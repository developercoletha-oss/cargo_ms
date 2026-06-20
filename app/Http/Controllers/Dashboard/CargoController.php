<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\CargoLocationUpdate;
use App\Models\SystemNotification;
use App\Models\TransportStaff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

    private const AREA_COORDINATES = [
        'Arusha' => [-3.3869, 36.6830],
        'Dar es Salaam' => [-6.7924, 39.2083],
        'Dodoma' => [-6.1630, 35.7516],
        'Geita' => [-2.8725, 32.2320],
        'Iringa' => [-7.7708, 35.6923],
        'Kagera' => [-1.9403, 31.1820],
        'Katavi' => [-6.3670, 31.0409],
        'Kigoma' => [-4.8824, 29.6615],
        'Kilimanjaro' => [-3.0674, 37.3556],
        'Lindi' => [-9.9969, 39.7144],
        'Manyara' => [-4.3150, 36.9541],
        'Mara' => [-1.7754, 34.1532],
        'Mbeya' => [-8.9094, 33.4608],
        'Morogoro' => [-6.8278, 37.6591],
        'Mtwara' => [-10.2676, 40.1833],
        'Mwanza' => [-2.5164, 32.9175],
        'Njombe' => [-9.3492, 34.7718],
        'Pemba' => [-5.2050, 39.7756],
        'Pwani' => [-7.3238, 38.8205],
        'Rukwa' => [-7.9667, 31.6167],
        'Ruvuma' => [-10.6879, 35.6501],
        'Shinyanga' => [-3.6619, 33.4231],
        'Simiyu' => [-2.8309, 34.1532],
        'Singida' => [-4.8163, 34.7436],
        'Songwe' => [-8.2726, 32.9308],
        'Tabora' => [-5.0342, 32.8084],
        'Tanga' => [-5.0889, 39.1023],
        'Zanzibar' => [-6.1659, 39.2026],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $search = (string) $request->query('search', '');

        $this->ensureTransporterStaffRecords();

        $query = Cargo::query()
            ->with(['customer', 'detail', 'transportStaff.user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('origin_city', 'like', "%{$search}%")
                        ->orWhere('tracking_number', 'like', "%{$search}%")
                        ->orWhere('destination_city', 'like', "%{$search}%")
                        ->orWhere('origin_country', 'like', "%{$search}%")
                        ->orWhere('destination_country', 'like', "%{$search}%");
                });
            });

        if ($user->role === 'customer') {
            $query->where('customer_id', $user->id);
        } elseif ($user->role === 'transporter') {
            $query->whereHas('transportStaff', function ($staffQ) use ($user) {
                $staffQ->where('user_id', $user->id);
            });
        }

        $cargoes = $query->latest()->paginate(12)->withQueryString();

        $transportStaff = TransportStaff::query()
            ->with('user')
            ->where('is_active', true)
            ->whereHas('user', function ($q) {
                $q->where('role', 'transporter');
            })
            ->orderBy('staff_code')
            ->get();

        $customers = User::query()
            ->where('role', 'customer')
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'name', 'full_name', 'email']);

        $view = $user->role === 'customer'
            ? 'customer.cargo.index'
            : 'staff.cargo.index';

        return view($view, [
            'cargoes' => $cargoes,
            'search' => $search,
            'transportStaff' => $transportStaff,
            'customers' => $customers,
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'store_keeper') {
            abort(403, 'Only store keeper can register cargo.');
        }

        $validated = $this->validateCargo($request);
        $customerId = (int) ($request->input('customer_id') ?: $user->id);

        if (! User::where('id', $customerId)->where('role', 'customer')->exists()) {
            return back()->withInput()->with('error', 'Store keeper must register cargo for a valid customer.');
        }

        $cargo = Cargo::create([
            'customer_id' => $customerId,
            'origin_country' => 'TZ',
            'origin_city' => $validated['origin_city'],
            'origin_address' => $validated['origin_address'] ?? null,
            'destination_country' => 'TZ',
            'destination_city' => $validated['destination_city'],
            'destination_address' => $validated['destination_address'] ?? null,
            'pickup_date' => $validated['pickup_date'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
            'status' => Cargo::STATUS_PENDING,
        ]);

        $cargo->detail()->create($this->detailPayload($validated));
        $this->notifyCustomerTrackingNumber($cargo);

        return redirect()->route('dashboard.cargo.index')->with('success', "Cargo created successfully. Tracking number: {$cargo->tracking_number}");
    }

    public function update(Request $request, Cargo $cargo): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer' || $cargo->customer_id !== $user->id) {
            abort(403, 'You can only edit your own cargo.');
        }
        if ($cargo->status !== Cargo::STATUS_PENDING) {
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
        if ($cargo->status !== Cargo::STATUS_PENDING) {
            return back()->with('error', 'Approved/disapproved cargo cannot be deleted.');
        }

        $cargo->delete();

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo deleted successfully.');
    }

    public function approve(Request $request, Cargo $cargo): RedirectResponse
    {
        $this->ensureManager($request->user());

        if ($cargo->status !== Cargo::STATUS_PENDING) {
            return back()->with('error', 'Only pending cargo can be approved.');
        }

        if ($cargo->status === Cargo::STATUS_APPROVED) {
            return back()->with('info', 'Cargo is already approved.');
        }

        $cargo->update([
            'status' => Cargo::STATUS_APPROVED,
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
        $this->ensureManager($request->user());

        if ($cargo->status !== Cargo::STATUS_PENDING) {
            return back()->with('error', 'Only pending cargo can be disapproved.');
        }

        if ($cargo->status === Cargo::STATUS_DISAPPROVED) {
            return back()->with('info', 'Cargo is already disapproved.');
        }

        $cargo->update([
            'status' => Cargo::STATUS_DISAPPROVED,
            'approval_note' => $request->input('approval_note'),
            'disapproved_by' => $request->user()->id,
            'disapproved_at' => now(),
            'transport_staff_id' => null,
        ]);

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo disapproved successfully.');
    }

    public function assign(Request $request, Cargo $cargo): RedirectResponse
    {
        $this->ensureTransportAssignmentManager($request->user());

        if ($cargo->status !== Cargo::STATUS_APPROVED) {
            return back()->with('error', 'Only approved cargo can be assigned.');
        }

        $validated = $request->validate([
            'transport_staff_id' => ['required', Rule::exists('transport_staff', 'id')],
            'pickup_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:pickup_date'],
        ]);

        $transportStaff = TransportStaff::with('user')->findOrFail((int) $validated['transport_staff_id']);
        if ($transportStaff->user?->role !== 'transporter') {
            return back()->with('error', 'Selected staff is not a transporter.');
        }

        $cargo->update([
            'transport_staff_id' => (int) $validated['transport_staff_id'],
            'pickup_date' => $validated['pickup_date'] ?? $cargo->pickup_date,
            'delivery_date' => $validated['delivery_date'] ?? $cargo->delivery_date,
            'signed_by_transporter' => null,
            'signed_at' => null,
            'handover_confirmed_by' => null,
            'handover_confirmed_at' => null,
        ]);

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo assigned to transporter successfully.');
    }

    public function sign(Request $request, Cargo $cargo): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'transporter') {
            abort(403, 'Only transporter can sign assigned cargo.');
        }

        $transportStaff = TransportStaff::query()->where('user_id', $user->id)->first();
        if (! $transportStaff || (int) $cargo->transport_staff_id !== (int) $transportStaff->id) {
            return back()->with('error', 'You can only sign cargo assigned to you.');
        }

        if ($cargo->status !== Cargo::STATUS_APPROVED) {
            return back()->with('error', 'Only approved cargo can be signed.');
        }

        if ($cargo->signed_at) {
            return back()->with('info', 'Cargo is already signed by transporter.');
        }

        $cargo->update([
            'signed_by_transporter' => $user->id,
            'signed_at' => now(),
            'handover_confirmed_by' => null,
            'handover_confirmed_at' => null,
        ]);

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo signed successfully.');
    }

    public function confirmHandover(Request $request, Cargo $cargo): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'store_keeper') {
            abort(403, 'Only store keeper can confirm warehouse handover.');
        }

        if (! $cargo->transport_staff_id || ! $cargo->signed_at) {
            return back()->with('error', 'Transporter must sign cargo before handover confirmation.');
        }

        if ($cargo->handover_confirmed_at) {
            return back()->with('info', 'Handover is already confirmed by store keeper.');
        }

        $cargo->update([
            'status' => Cargo::STATUS_IN_TRANSIT,
            ...$this->locationPayload($cargo->origin_city),
            'handover_confirmed_by' => $user->id,
            'handover_confirmed_at' => now(),
        ]);
        $this->recordLocationUpdate($cargo, $user->id, $cargo->origin_city, (float) $cargo->current_location_lat, (float) $cargo->current_location_lng, 'handover');

        return redirect()->route('dashboard.cargo.index')->with('success', 'Warehouse handover confirmed. Cargo is now in transit.');
    }

    public function liveLocation(Request $request, Cargo $cargo): JsonResponse
    {
        $this->ensureAssignedTransporter($request->user(), $cargo);

        if ($cargo->status !== Cargo::STATUS_IN_TRANSIT) {
            return response()->json([
                'message' => 'Only cargo in transit can receive live GPS updates.',
            ], 422);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $cargo->update([
            'current_location_city' => null,
            'current_location_lat' => $validated['latitude'],
            'current_location_lng' => $validated['longitude'],
            'current_location_updated_at' => now(),
        ]);
        $this->recordLocationUpdate($cargo, $request->user()->id, null, (float) $validated['latitude'], (float) $validated['longitude'], 'gps');

        return response()->json([
            'message' => 'Live location updated.',
            'updated_at' => optional($cargo->current_location_updated_at)->format('d M Y H:i'),
        ]);
    }

    public function markArrived(Request $request, Cargo $cargo): RedirectResponse
    {
        $this->ensureAssignedTransporter($request->user(), $cargo);

        if ($cargo->status !== Cargo::STATUS_IN_TRANSIT) {
            return back()->with('error', 'Only cargo in transit can be marked as arrived.');
        }

        $cargo->update([
            'status' => Cargo::STATUS_ARRIVED,
            ...$this->locationPayload($cargo->destination_city),
        ]);
        $this->recordLocationUpdate($cargo, $request->user()->id, $cargo->destination_city, (float) $cargo->current_location_lat, (float) $cargo->current_location_lng, 'arrived');

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo marked as arrived.');
    }

    public function markDelivered(Request $request, Cargo $cargo): RedirectResponse
    {
        $this->ensureAssignedTransporter($request->user(), $cargo);

        if ($cargo->status !== Cargo::STATUS_ARRIVED) {
            return back()->with('error', 'Only arrived cargo can be marked as delivered.');
        }

        $cargo->update([
            'status' => Cargo::STATUS_DELIVERED,
            ...$this->locationPayload($cargo->destination_city),
            'delivery_date' => $cargo->delivery_date ?: now()->toDateString(),
        ]);
        $this->recordLocationUpdate($cargo, $request->user()->id, $cargo->destination_city, (float) $cargo->current_location_lat, (float) $cargo->current_location_lng, 'delivered');

        return redirect()->route('dashboard.cargo.index')->with('success', 'Cargo marked as delivered.');
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

    private function ensureManager(User $user): void
    {
        if (! in_array($user->role, ['admin', 'manager'], true)) {
            abort(403, 'Only manager or admin can review cargo.');
        }
    }

    private function ensureTransportAssignmentManager(User $user): void
    {
        if ($user->role !== 'manager') {
            abort(403, 'Only manager can assign transporter.');
        }
    }

    private function ensureAssignedTransporter(User $user, Cargo $cargo): void
    {
        if ($user->role !== 'transporter') {
            abort(403, 'Only transporter can update cargo movement.');
        }

        $transportStaff = TransportStaff::query()->where('user_id', $user->id)->first();
        if (! $transportStaff || (int) $cargo->transport_staff_id !== (int) $transportStaff->id) {
            abort(403, 'You can only update cargo assigned to you.');
        }
    }

    private function ensureTransporterStaffRecords(): void
    {
        $transporterIds = User::query()
            ->where('role', 'transporter')
            ->pluck('id');

        if ($transporterIds->isEmpty()) {
            return;
        }

        $existingStaffUserIds = TransportStaff::query()
            ->whereIn('user_id', $transporterIds)
            ->pluck('user_id');

        $timestamp = now();
        $missingStaffRows = $transporterIds
            ->diff($existingStaffUserIds)
            ->map(fn (int $userId) => [
                'user_id' => $userId,
                'staff_code' => 'TSF-'.str_pad((string) $userId, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])
            ->values()
            ->all();

        if ($missingStaffRows === []) {
            return;
        }

        TransportStaff::query()->insertOrIgnore($missingStaffRows);
    }

    private function notifyCustomerTrackingNumber(Cargo $cargo): void
    {
        SystemNotification::create([
            'user_id' => $cargo->customer_id,
            'title' => 'Cargo tracking number generated',
            'message' => "Your cargo has been registered successfully. Tracking number: {$cargo->tracking_number}. Use it to track your cargo movement.",
            'status' => 'unread',
            'metadata' => [
                'cargo_id' => $cargo->id,
                'tracking_number' => $cargo->tracking_number,
                'tracking_url' => route('tracking.show', ['tracking_number' => $cargo->tracking_number]),
            ],
        ]);
    }

    private function locationPayload(string $city): array
    {
        $coordinates = self::AREA_COORDINATES[$city] ?? [null, null];

        return [
            'current_location_city' => $city,
            'current_location_lat' => $coordinates[0],
            'current_location_lng' => $coordinates[1],
            'current_location_updated_at' => now(),
        ];
    }

    private function recordLocationUpdate(Cargo $cargo, ?int $reportedBy, ?string $locationName, ?float $latitude, ?float $longitude, string $source): void
    {
        if ($latitude === null || $longitude === null) {
            return;
        }

        CargoLocationUpdate::create([
            'cargo_id' => $cargo->id,
            'reported_by' => $reportedBy,
            'location_name' => $locationName,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'source' => $source,
            'recorded_at' => now(),
        ]);
    }
}
