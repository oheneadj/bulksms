<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class GroupDetails extends Component
{
    use WithPagination;

    public Group $group;

    #[Url]
    public $search = '';

    public $showAddModal = false;
    public $searchContactsToAdd = '';
    public $selectedContacts = [];

    public function mount(Group $group)
    {
        // Ensure user owns the group
        if ($group->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }
        $this->group = $group;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function removeContact($contactId)
    {
        $contact = Contact::where('id', $contactId)
            ->where('tenant_id', Auth::user()->tenant_id)
            ->first();

        if ($contact && $contact->group_id == $this->group->id) {
            $contact->update(['group_id' => null]);
            $this->group->decrement('contacts_count');
            $this->dispatch('contact-removed');
        }
    }

    public function addContacts()
    {
        if (!empty($this->selectedContacts)) {
            $count = count($this->selectedContacts);
            
            Contact::whereIn('id', $this->selectedContacts)
                ->where('tenant_id', Auth::user()->tenant_id)
                ->update(['group_id' => $this->group->id]);
            
            $this->group->increment('contacts_count', $count);
            
            $this->reset(['selectedContacts', 'showAddModal', 'searchContactsToAdd']);
            $this->dispatch('contacts-added');
        }
    }

    public function render()
    {
        $contacts = $this->group->contacts()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('phone', 'ilike', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Contacts available to add (not already in group)
        $availableContacts = [];
        if ($this->showAddModal) {
            $availableContacts = Contact::where('tenant_id', Auth::user()->tenant_id)
                ->whereDoesntHave('groups', function ($q) {
                    $q->where('group_id', $this->group->id);
                })
                ->when($this->searchContactsToAdd, function ($query) {
                    $query->where(function ($q) {
                        $q->where('name', 'ilike', '%' . $this->searchContactsToAdd . '%')
                          ->orWhere('phone', 'ilike', '%' . $this->searchContactsToAdd . '%');
                    });
                })
                ->limit(20)
                ->get();
        }

        return view('livewire.group-details', [
            'contacts' => $contacts,
            'availableContacts' => $availableContacts
        ]);
    }
}
