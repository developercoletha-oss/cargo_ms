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

        $shipments = $query->latest()->paginate(15);

        return view('dashboard.shipments.index', compact('shipments', 'user'));
    }
}
