<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight">Team Members</h1>
            <p class="text-zinc-500 font-medium mt-1">Manage your team members, roles, and permissions.</p>
        </div>
        @if(auth()->user()->isTenantAdmin())
            <button wire:click="$set('showInviteModal', true)"
                class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Invite Member
            </button>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Total Members</span>
                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4 text-indigo-brand"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-zinc-900">{{ $members->total() }}</p>
        </div>
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Pending Invites</span>
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-zinc-900">{{ count($invitations) }}</p>
        </div>
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Active Status</span>
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-zinc-900">{{ $members->where('status', 'active')->count() }}</p>
        </div>
    </div>

    <!-- Active Members Table -->
    <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
        <div class="p-6 border-b border-zinc-100">
            <h2 class="text-lg font-bold text-zinc-900 tracking-tight">Active Members</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Permissions</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($members as $member)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-indigo-brand/10 flex items-center justify-center text-indigo-brand font-bold">
                                        {{ $member->initials() }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-zinc-900 flex items-center gap-1.5">
                                            {{ $member->name }}
                                            @if($member->is_account_owner)
                                                <span
                                                    class="bg-amber-100 text-amber-700 text-[8px] font-extrabold px-1.5 py-0.5 rounded-full uppercase tracking-wider">Owner</span>
                                            @endif
                                        </div>
                                        <div class="text-zinc-500 text-xs">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if(auth()->user()->isTenantAdmin() && !$member->is_account_owner && $member->id !== auth()->id())
                                    <select wire:change="updateMemberRole({{ $member->id }}, $event.target.value)"
                                        class="bg-zinc-50 border border-zinc-200 text-zinc-900 text-xs font-medium rounded-lg block p-2 focus:border-indigo-500 focus:ring-0">
                                        <option value="user" @selected($member->role === 'user')>Member</option>
                                        <option value="tenant_admin" @selected($member->role === 'tenant_admin')>Admin</option>
                                    </select>
                                @else
                                    <span
                                        class="text-zinc-900 font-medium capitalize">{{ str_replace('_', ' ', $member->role) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox"
                                            wire:click="togglePermission({{ $member->id }}, 'can_topup_credits')"
                                            @checked($member->canTopupCredits()) @disabled(!$member->isRegularUser() || !auth()->user()->isTenantAdmin())
                                            class="w-4 h-4 text-indigo-brand bg-zinc-100 border-zinc-300 rounded focus:ring-indigo-500 focus:ring-2 transition-all">
                                        <span
                                            class="text-xs font-medium text-zinc-500 group-hover:text-zinc-900">Billing</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox"
                                            wire:click="togglePermission({{ $member->id }}, 'can_view_billing')"
                                            @checked($member->canViewBilling()) @disabled(!$member->isRegularUser() || !auth()->user()->isTenantAdmin())
                                            class="w-4 h-4 text-indigo-brand bg-zinc-100 border-zinc-300 rounded focus:ring-indigo-500 focus:ring-2 transition-all">
                                        <span
                                            class="text-xs font-medium text-zinc-500 group-hover:text-zinc-900">Reports</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 border rounded-full text-[10px] font-bold uppercase tracking-wider {{ $member->isActive() ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $member->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if(auth()->user()->isTenantAdmin() && !$member->is_account_owner && $member->id !== auth()->id())
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="toggleStatus({{ $member->id }})"
                                            class="p-2 hover:bg-zinc-100 rounded-lg text-zinc-400 hover:text-indigo-600 transition-all"
                                            title="{{ $member->isActive() ? 'Disable' : 'Enable' }}">
                                            <i data-lucide="{{ $member->isActive() ? 'user-x' : 'user-check' }}"
                                                class="w-4 h-4"></i>
                                        </button>

                                        <button wire:click="deleteUser({{ $member->id }})"
                                            wire:confirm="Are you sure you want to remove this team member? This action cannot be undone."
                                            class="p-2 hover:bg-rose-50 rounded-lg text-zinc-400 hover:text-rose-600 transition-all"
                                            title="Remove Member">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/30">
            {{ $members->links() }}
        </div>
    </div>

    @if(count($invitations) > 0)
        <!-- Pending Invitations -->
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden mt-8">
            <div class="p-6 border-b border-zinc-100">
                <h2 class="text-lg font-bold text-zinc-900 tracking-tight">Pending Invitations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Expires</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($invitations as $invitation)
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-zinc-900">{{ $invitation->email }}</td>
                                <td class="px-6 py-4 flex items-center gap-2">
                                    <span
                                        class="text-zinc-900 font-medium capitalize">{{ str_replace('_', ' ', $invitation->role) }}</span>
                                    @if($invitation->can_topup_credits || $invitation->can_view_billing)
                                        <span class="text-[8px] bg-zinc-100 text-zinc-500 px-1.5 py-0.5 rounded-full">+ perms</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="text-zinc-500 text-xs {{ $invitation->isExpired() ? 'text-rose-500 font-bold' : '' }}">
                                        {{ $invitation->expires_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if(auth()->user()->isTenantAdmin())
                                        <button wire:click="cancelInvitation({{ $invitation->id }})"
                                            class="p-2 hover:bg-rose-50 rounded-lg text-zinc-400 hover:text-rose-600 transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Invite Modal -->
    @if($showInviteModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                    <h3 class="text-xl font-extrabold text-zinc-900 tracking-tight">Invite Team Member</h3>
                    <button wire:click="$set('showInviteModal', false)"
                        class="text-zinc-400 hover:text-zinc-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="inviteMember" class="p-6 space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Email Address</label>
                        <input type="email" wire:model="invitationEmail"
                            class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-3 focus:border-indigo-500 focus:ring-0 transition-all font-medium"
                            placeholder="colleague@company.com">
                        @error('invitationEmail') <span
                            class="text-rose-600 text-[10px] font-bold uppercase tracking-wider">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Role</label>
                        <select wire:model="invitationRole"
                            class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-3 focus:border-indigo-500 focus:ring-0 transition-all font-medium italic">
                            <option value="user">Regular Member (Limited access)</option>
                            <option value="tenant_admin">Admin (Full administrative access)</option>
                        </select>
                    </div>

                    <div class="space-y-3 bg-zinc-50 p-4 rounded-xl border border-zinc-200">
                        <h4 class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-3">Additional
                            Permissions</h4>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div
                                class="flex items-center justify-center w-5 h-5 border-2 border-zinc-300 rounded group-hover:border-indigo-500 transition-all relative @if($canTopupCredits) bg-indigo-brand border-indigo-brand @endif">
                                @if($canTopupCredits) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                <input type="checkbox" wire:model="canTopupCredits"
                                    class="absolute opacity-0 cursor-pointer">
                            </div>
                            <span
                                class="text-sm font-bold text-zinc-700 group-hover:text-indigo-600 transition-colors">Top-up
                                Credits</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div
                                class="flex items-center justify-center w-5 h-5 border-2 border-zinc-300 rounded group-hover:border-indigo-500 transition-all relative @if($canViewBilling) bg-indigo-brand border-indigo-brand @endif">
                                @if($canViewBilling) <i data-lucide="check" class="w-3 h-3 text-white"></i> @endif
                                <input type="checkbox" wire:model="canViewBilling"
                                    class="absolute opacity-0 cursor-pointer">
                            </div>
                            <span class="text-sm font-bold text-zinc-700 group-hover:text-indigo-600 transition-colors">View
                                Billing Reports</span>
                        </label>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" wire:click="$set('showInviteModal', false)"
                            class="flex-1 bg-white border border-zinc-200 text-zinc-700 font-bold py-3 px-6 rounded-lg hover:bg-zinc-50 transition-all active:scale-[0.98]">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-lg shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98]">
                            Send Invite
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>