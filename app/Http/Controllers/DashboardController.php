<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\SenderId;
use App\Models\Contact;
use App\Models\Campaign;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        // Calculate real metrics
        $totalMessages = Message::where('user_id', $user->id)->count();
        $deliveredCount = Message::where('user_id', $user->id)->where('status', 'delivered')->count();
        $deliveryRate = $totalMessages > 0 ? round(($deliveredCount / $totalMessages) * 100, 1) : 0;
        $totalCost = Message::where('user_id', $user->id)->sum('cost');
        
        $messagesThisMonth = Message::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $messagesLastMonth = Message::where('user_id', $user->id)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $messageGrowth = 0;
        if ($messagesLastMonth > 0) {
            $messageGrowth = (($messagesThisMonth - $messagesLastMonth) / $messagesLastMonth) * 100;
        } elseif ($messagesThisMonth > 0) {
            $messageGrowth = 100;
        }
        
        // Status Counts for Donut Chart
        $statusCounts = Message::where('user_id', $user->id)
            ->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
            
        // Ensure all keys exist
        $statusCounts = array_merge([
            'delivered' => 0,
            'failed' => 0,
            'queued' => 0,
            'scheduled' => 0
        ], $statusCounts);

        // Top Groups
        $topGroups = \App\Models\Group::where('tenant_id', $tenant->id)
            ->orderByDesc('contacts_count')
            ->take(5)
            ->get();

        // Prepare chart data (last 7 days)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('D, M d');
            $chartData[] = Message::where('user_id', $user->id)
                ->whereDate('created_at', $date->toDateString())
                ->count();
        }

        // Recent messages
        $recentMessages = Message::where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get();

        // Recent campaigns
        $recentCampaigns = Campaign::where('user_id', $user->id)
            ->latest()
            ->take(3)
            ->get();

        // Quick stats
        $approvedSenderIds = SenderId::where('user_id', $user->id)->where('status', 'approved')->count();
        $totalContacts = Contact::where('tenant_id', $tenant->id)->count();

        return view('dashboard', compact(
            'user',
            'tenant',
            'totalMessages',
            'deliveredCount',
            'deliveryRate',
            'totalCost',
            'messagesThisMonth',
            'messageGrowth',
            'statusCounts',
            'topGroups',
            'chartLabels',
            'chartData',
            'chartData',
            // 'recentMessages', // Handled by Livewire component
            'recentCampaigns',
            'approvedSenderIds',
            'totalContacts'
        ));
    }
}
