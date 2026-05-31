<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\User;
use App\Models\Shipment;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $userCountry = $user?->country;
        $userRole = $user?->role;

        // Base stats array
        $stats = [];

        // Admin sees global stats
        if ($userRole === 'admin') {
            $stats = [
                [
                    'label' => 'Total Users',
                    'value' => User::count(),
                    'icon' => 'bi-people',
                    'tone' => 'blue',
                ],
                [
                    'label' => 'Total Shipments',
                    'value' => Shipment::count(),
                    'icon' => 'bi-box-seam',
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'Active Sessions',
                    'value' => 124, // Placeholder
                    'icon' => 'bi-activity',
                    'tone' => 'slate',
                ],
                [
                    'label' => 'Pending Tasks',
                    'value' => Shipment::where('status', 'pending')->count(),
                    'icon' => 'bi-list-task',
                    'tone' => 'amber',
                ],
            ];
        } elseif ($userRole === 'customer') {
            $stats = [
                [
                    'label' => 'My Cargo',
                    'value' => Cargo::where('customer_id', $user->id)->count(),
                    'icon' => 'bi-box2-heart',
                    'tone' => 'blue',
                ],
                [
                    'label' => 'Pending',
                    'value' => Cargo::where('customer_id', $user->id)->where('status', Cargo::STATUS_PENDING)->count(),
                    'icon' => 'bi-hourglass-split',
                    'tone' => 'amber',
                ],
                [
                    'label' => 'In Transit',
                    'value' => Cargo::where('customer_id', $user->id)->where('status', Cargo::STATUS_IN_TRANSIT)->count(),
                    'icon' => 'bi-truck',
                    'tone' => 'orange',
                ],
                [
                    'label' => 'Delivered',
                    'value' => Cargo::where('customer_id', $user->id)->where('status', Cargo::STATUS_DELIVERED)->count(),
                    'icon' => 'bi-check-circle',
                    'tone' => 'green',
                ],
            ];
        } else {
            // Regular user - filtered by their country and assigned shipments
            $stats = [
                [
                    'label' => 'My Shipments',
                    'value' => $userCountry 
                        ? Shipment::where('origin_country', $userCountry)
                            ->orWhere('destination_country', $userCountry)
                            ->count()
                        : Shipment::where('sender_id', $user->id)->count(),
                    'icon' => 'bi-box-seam',
                    'tone' => 'blue',
                ],
                [
                    'label' => 'Assigned to Me',
                    'value' => Shipment::where('assigned_to', $user->id)->count(),
                    'icon' => 'bi-person-check',
                    'tone' => 'indigo',
                ],
                [
                    'label' => 'In Transit',
                    'value' => $userCountry
                        ? Shipment::where(function($q) use ($userCountry) {
                            $q->where('origin_country', $userCountry)
                              ->orWhere('destination_country', $userCountry);
                        })->where('status', 'in_transit')->count()
                        : 0,
                    'icon' => 'bi-truck',
                    'tone' => 'orange',
                ],
                [
                    'label' => 'Delivered',
                    'value' => $userCountry
                        ? Shipment::where(function($q) use ($userCountry) {
                            $q->where('origin_country', $userCountry)
                              ->orWhere('destination_country', $userCountry);
                        })->where('status', 'delivered')->count()
                        : 0,
                    'icon' => 'bi-check-circle',
                    'tone' => 'green',
                ],
            ];
        }

        // Summary tiles - also dynamic
        $latestCargo = $userRole === 'customer'
            ? Cargo::where('customer_id', $user->id)->latest()->first()
            : null;

        $summaryTiles = $userRole === 'customer' ? [
            [
                'label' => 'Latest Tracking',
                'value' => $latestCargo?->tracking_number ?? '-',
                'icon' => 'bi-upc-scan',
            ],
            [
                'label' => 'Latest Status',
                'value' => $latestCargo?->statusLabel() ?? '-',
                'icon' => 'bi-activity',
            ],
            [
                'label' => 'Origin',
                'value' => $latestCargo?->origin_city ?? '-',
                'icon' => 'bi-geo',
            ],
            [
                'label' => 'Destination',
                'value' => $latestCargo?->destination_city ?? '-',
                'icon' => 'bi-geo-alt',
            ],
        ] : [
            [
                'label' => 'Storage Usage',
                'value' => '45%',
                'icon' => 'bi-hdd',
            ],
            [
                'label' => 'API Requests',
                'value' => '1.2k',
                'icon' => 'bi-cloud-arrow-down',
            ],
            [
                'label' => 'Uptime',
                'value' => '99.9%',
                'icon' => 'bi-heart-pulse',
            ],
            [
                'label' => 'System Health',
                'value' => 'Good',
                'icon' => 'bi-shield-check',
            ],
        ];

        // Get recent shipments relevant to user
        $recentShipments = $userRole !== 'customer' && $userCountry
            ? Shipment::where(function($q) use ($userCountry) {
                $q->where('origin_country', $userCountry)
                  ->orWhere('destination_country', $userCountry);
            })->latest()->take(5)->get()
            : collect();

        $recentCargo = $userRole === 'customer'
            ? Cargo::query()
                ->with(['detail', 'transportStaff.user'])
                ->where('customer_id', $user->id)
                ->latest()
                ->take(5)
                ->get()
            : collect();

        return view('dashboard.dashboard', compact(
            'stats',
            'summaryTiles',
            'recentShipments',
            'recentCargo',
            'user'
        ));
    }
}
