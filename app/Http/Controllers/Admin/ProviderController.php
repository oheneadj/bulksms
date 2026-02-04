<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsProvider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = SmsProvider::orderBy('priority', 'desc')->get();
        return view('admin.providers.index', compact('providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|in:twilio,mnotify',
            'priority' => 'required|integer',
            'config_key' => 'nullable|string',
            'config_value' => 'nullable|string',
            // Simplified config input for now, ideally dynamic fields based on provider
            'sid' => 'nullable|required_if:provider,twilio|string',
            'token' => 'nullable|required_if:provider,twilio|string',
            'from' => 'nullable|required_if:provider,twilio|string',
            'key' => 'nullable|required_if:provider,mnotify|string',
            'sender_id' => 'nullable|required_if:provider,mnotify|string',
        ]);

        $config = [];
        if ($validated['provider'] === 'twilio') {
            $config = [
                'sid' => $request->sid,
                'token' => $request->token,
                'from' => $request->from,
            ];
        } elseif ($validated['provider'] === 'mnotify') {
            $config = [
                'key' => $request->key,
                'sender_id' => $request->sender_id,
            ];
        }

        SmsProvider::create([
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'priority' => $validated['priority'],
            'config' => $config,
            'is_active' => true,
        ]);

        return back()->with('success', 'Provider added successfully.');
    }

    public function toggle(SmsProvider $provider)
    {
        $provider->update(['is_active' => !$provider->is_active]);
        return back()->with('success', 'Provider status updated.');
    }

    public function destroy(SmsProvider $provider)
    {
        $provider->delete();
        return back()->with('success', 'Provider deleted.');
    }
}
