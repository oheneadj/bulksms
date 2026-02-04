<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Message;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Overview Stats
        $totalTenants = Tenant::query()->count();
        $totalMessages = Message::query()->count();
        $totalTransactions = Transaction::query()->where('type', 'deposit')->sum('amount');
        
        // 2. Recent Activity (30 Days)
        $endDate = now();
        $startDate = now()->subDays(29);
        
        // Daily Message Volume
        $dailyMessages = Message::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Daily Revenue (Credits Deposited)
        $dailyRevenue = Transaction::query()
            ->where('type', 'deposit')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        // Fill missing dates
        $chartData = [];
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $chartData['labels'][] = $startDate->copy()->addDays($i)->format('M d');
            $chartData['messages'][] = $dailyMessages[$date] ?? 0;
            $chartData['revenue'][] = $dailyRevenue[$date] ?? 0;
        }

        // 3. Low Balance Tenants (Credits < 50)
        $lowBalanceTenants = Tenant::query()
            ->where('sms_credits', '<', 50)
            ->orderBy('sms_credits', 'asc')
            ->take(5)
            ->get();

        // 4. Recent Tenants
        $recentTenants = Tenant::query()->latest('created_at')->take(5)->get();

        // 5. Provider Balances
        $smsService = app(\App\Services\SmsService::class);
        $providerBalances = $smsService->getProviderBalances();

        return view('admin.dashboard', compact(
            'totalTenants', 
            'totalMessages', 
            'totalTransactions', 
            'recentTenants',
            'lowBalanceTenants',
            'chartData',
            'providerBalances'
        ));
    }
}
