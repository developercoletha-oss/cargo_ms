<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CargoTrackingController extends Controller
{
    public function __invoke(Request $request): View
    {
        $trackingNumber = strtoupper(trim((string) $request->query('tracking_number', '')));
        $cargo = null;
        $searched = $trackingNumber !== '';

        if ($searched) {
            $cargo = Cargo::query()
                ->with(['customer', 'detail', 'transportStaff.user'])
                ->where('tracking_number', $trackingNumber)
                ->first();
        }

        return view('tracking.show', [
            'trackingNumber' => $trackingNumber,
            'cargo' => $cargo,
            'searched' => $searched,
        ]);
    }
}
