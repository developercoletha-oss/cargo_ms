<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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
        $summaryTiles = [
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
        $recentShipments = $userCountry
            ? Shipment::where(function($q) use ($userCountry) {
                $q->where('origin_country', $userCountry)
                  ->orWhere('destination_country', $userCountry);
            })->latest()->take(5)->get()
            : collect();

        return view('dashboard.dashboard', compact(
            'stats',
            'summaryTiles',
            'recentShipments',
            'user'
        ));
    }
}
