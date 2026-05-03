<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            [
                'label' => 'Total Users',
                'value' => User::count(),
                'icon' => 'bi-people',
                'tone' => 'blue',
            ],
            [
                'label' => 'Active Sessions',
                'value' => 124, // Placeholder
                'icon' => 'bi-activity',
                'tone' => 'slate',
            ],
            [
                'label' => 'New Messages',
                'value' => 12, // Placeholder
                'icon' => 'bi-envelope',
                'tone' => 'teal',
            ],
            [
                'label' => 'Pending Tasks',
                'value' => 5, // Placeholder
                'icon' => 'bi-list-task',
                'tone' => 'amber',
            ],
        ];

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
                'label' => 'uptime',
                'value' => '99.9%',
                'icon' => 'bi-heart-pulse',
            ],
            [
                'label' => 'System Health',
                'value' => 'Good',
                'icon' => 'bi-shield-check',
            ],
        ];

        return view('dashboard.dashboard', compact(
            'stats',
            'summaryTiles'
        ));
    }
}
