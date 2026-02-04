<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\UserInvitation;
use App\Mail\UserInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Devrabiul\ToastMagic\Facades\ToastMagic as Toaster;

class TeamMembers extends Component
{
    use WithPagination;

    public $invitationEmail = '';
    public $invitationRole = 'user';
    public $canTopupCredits = false;
    public $canViewBilling = false;
    
    public $showInviteModal = false;

    protected $rules = [
        'invitationEmail' => 'required|email',
        'invitationRole' => 'required|in:user,tenant_admin',
    ];

    public function inviteMember()
    {
        $this->validate();

        $user = auth()->user();
        $tenant = $user->tenant;

        // Check if user already exists in this tenant
        if (User::where('tenant_id', '=', $tenant->id)->where('email', '=', $this->invitationEmail)->exists()) {
            Toaster::error('User is already a member of this team.');
            return;
        }

        // Check if invitation already exists
        if (UserInvitation::where('tenant_id', '=', $tenant->id)->where('email', '=', $this->invitationEmail)->exists()) {
            Toaster::error('An invitation has already been sent to this email.');
            return;
        }

        $invitation = UserInvitation::create([
            'tenant_id' => $tenant->id,
            'invited_by_user_id' => $user->id,
            'email' => $this->invitationEmail,
            'role' => $this->invitationRole,
            'token' => Str::random(40),
            'can_topup_credits' => $this->canTopupCredits,
            'can_view_billing' => $this->canViewBilling,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($this->invitationEmail)->send(new UserInvitationMail($invitation));

        $this->reset(['invitationEmail', 'invitationRole', 'canTopupCredits', 'canViewBilling', 'showInviteModal']);
        Toaster::success('Invitation sent successfully!');
    }

    public function toggleStatus($userId)
    {
        $member = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($userId);

        if ($member->is_account_owner) {
            Toaster::error('You cannot disable the account owner.');
            return;
        }

        if ($member->id === auth()->id()) {
            Toaster::error('You cannot disable yourself.');
            return;
        }

        $member->update([
            'status' => $member->status === 'active' ? 'disabled' : 'active',
        ]);

        Toaster::success('User status updated successfully.');
    }

    public function updateMemberRole($userId, $role)
    {
        $member = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($userId);

        if ($member->is_account_owner) {
            Toaster::error('You cannot change the role of the account owner.');
            return;
        }

        $member->update(['role' => $role]);
        Toaster::success('Member role updated.');
    }

    public function togglePermission($userId, $permission)
    {
        $member = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($userId);
        
        $member->update([
            $permission => !$member->$permission,
        ]);

        Toaster::success('Permission updated.');
    }

    public function cancelInvitation($invitationId)
    {
        $invitation = UserInvitation::where('tenant_id', auth()->user()->tenant_id)->findOrFail($invitationId);
        $invitation->delete();
        Toaster::success('Invitation cancelled.');
    }

    public function deleteUser($userId)
    {
        $member = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($userId);

        if ($member->is_account_owner) {
            Toaster::error('You cannot delete the account owner.');
            return;
        }

        if ($member->id === auth()->id()) {
            Toaster::error('You cannot delete yourself.');
            return;
        }

        $member->delete();
        Toaster::success('Team member removed successfully.');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        return view('livewire.team-members', [
            'members' => User::where('tenant_id', $tenantId)->paginate(10),
            'invitations' => UserInvitation::where('tenant_id', $tenantId)->get(),
        ]);
    }
}
