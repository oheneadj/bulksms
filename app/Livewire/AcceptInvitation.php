<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Devrabiul\ToastMagic\Facades\ToastMagic as Toaster;

#[Layout('layouts.guest')]
class AcceptInvitation extends Component
{
    public $token;
    public $invitation;
    
    public $name = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'password' => 'required|string|min:8|confirmed',
    ];

    public function mount($token)
    {
        $this->token = $token;
        $this->invitation = UserInvitation::where('token', $token)->first();

        if (!$this->invitation || $this->invitation->isExpired()) {
            abort(404, 'Invitation not found or expired.');
        }

        // If user already exists, we might want to just link them, 
        // but PRD suggests creating an account.
        if (User::where('email', $this->invitation->email)->exists()) {
             // In v1 we assume the invited person doesn't have an account yet or we use this email.
             // If they already have an account, this flow might needs refinement.
        }
    }

    public function accept()
    {
        $this->validate();

        $user = User::create([
            'tenant_id' => $this->invitation->tenant_id,
            'name' => $this->name,
            'email' => $this->invitation->email,
            'password' => Hash::make($this->password),
            'role' => $this->invitation->role,
            'status' => 'active',
            'can_topup_credits' => $this->invitation->can_topup_credits,
            'can_view_billing' => $this->invitation->can_view_billing,
            'email_verified_at' => now(),
        ]);

        $this->invitation->delete();

        Auth::login($user);

        Toaster::success('Welcome to the team!');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.accept-invitation');
    }
}
