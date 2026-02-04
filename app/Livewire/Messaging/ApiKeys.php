<?php

namespace App\Livewire\Messaging;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class ApiKeys extends Component
{
    public $name = '';
    public $newKey = null;

    protected $rules = [
        'name' => 'required|min:3|max:50',
    ];

    public function generateKey()
    {
        if (!Auth::user()->tenant_id) {
            ToastMagic::error('Your account is not associated with a tenant. Please contact support.');
            return;
        }

        $this->validate();

        $key = 'bsms_' . Str::random(40);
        
        ApiKey::create([
            'tenant_id' => Auth::user()->tenant_id,
            'user_id' => Auth::id(),
            'name' => $this->name,
            'key' => hash('sha256', $key),
        ]);

        $this->newKey = $key;
        $this->name = '';
        
        ToastMagic::success('API Key generated successfully!');
    }

    public function revokeKey($id)
    {
        $apiKey = ApiKey::where('tenant_id', '=', Auth::user()->tenant_id)->findOrFail($id);
        $apiKey->delete();
        
        ToastMagic::success('API Key revoked.');
    }

    public function render()
    {
        return view('livewire.messaging.api-keys', [
            'apiKeys' => ApiKey::where('tenant_id', '=', Auth::user()->tenant_id)->latest()->get(),
        ]);
    }
}
