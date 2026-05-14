<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * Display a listing of shipments filtered by user's country/role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userCountry = $user?->country;
        $userRole = $user?->role;

        $query = Shipment::query();

        // Filter shipments based on user's country and role
        if ($userRole !== 'admin' && $userRole !== 'hgadmin') {
            if ($userCountry) {
                $query->where(function($q) use ($userCountry) {
                    $q->where('origin_country', $userCountry)
                      ->orWhere('destination_country', $userCountry);
                });
            } else {
                $query->where('sender_id', $user->id);
            }
        }

        $shipments = $query->with('assignedUser')->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('staff.shipments.partials.rows', compact('shipments'))->render(),
                'has_more' => $shipments->hasMorePages(),
                'next_page' => $shipments->currentPage() + 1,
            ]);
        }

        return view('staff.shipments.index', compact('shipments', 'user'));
    }
}
