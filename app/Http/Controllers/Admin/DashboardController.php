<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
    ) {}

    public function __invoke(Request $request): View
    {
        $range = $request->string('range', 'monthly')->toString();

        $summary = $this->analytics->summary($range);
        $series = $this->analytics->series($range);

        return view('admin.dashboard', compact('summary', 'series', 'range'));
    }
}
