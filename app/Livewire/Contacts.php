<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Models\Group;

#[Title('Contacts')]
class Contacts extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $filterGroup = '';
    public $filterMonth = '';
    public $filterStatus = '';
    public $selectedContacts = [];
    public $selectAll = false;
    public $showFilters = false;

    public $csvFile;
    public $showImportModal = false;
    public $showContactModal = false;
    public $editingContact = null;

    // Form fields
    public $title = ''; 
    public $first_name = '';
    public $surname = '';
    public $phone = '';
    public $email = '';
    public $dob = '';
    public $group_id = '';

    protected function rules()
    {
        return [
            'csvFile' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'title' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:50',
            'surname' => 'nullable|string|max:50',
            'phone' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
            'email' => 'nullable|email|max:255',
            'dob' => 'nullable|date',
            'group_id' => 'nullable|exists:groups,id',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showContactModal = true;
    }

    public function edit(Contact $contact)
    {
        $this->editingContact = $contact;
        $this->title = $contact->title;
        $this->first_name = $contact->first_name;
        $this->surname = $contact->surname;
        $this->phone = $contact->phone;
        $this->email = $contact->email;
        $this->dob = $contact->dob ? $contact->dob->format('Y-m-d') : '';
        $this->group_id = $contact->group_id;
        $this->showContactModal = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:50',
            'surname' => 'nullable|string|max:50',
            'phone' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
            'email' => 'nullable|email|max:255',
            'dob' => 'nullable|date',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        Auth::user()->contacts()->create([
            'tenant_id' => Auth::user()->tenant_id ?? 1,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'email' => $this->email,
            'dob' => $this->dob ?: null,
            'group_id' => $this->group_id ?: null,
        ]);

        if ($this->group_id) {
            Group::where('id', '=', $this->group_id)->increment('contacts_count');
        }

        $this->resetForm();
        $this->showContactModal = false;
        $this->dispatch('toastMagic', status: 'success', message: 'Contact created successfully.');
    }

    public function update()
    {
        $this->validate([
            'title' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:50',
            'surname' => 'nullable|string|max:50',
            'phone' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
            'email' => 'nullable|email|max:255',
            'dob' => 'nullable|date',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $oldGroupId = $this->editingContact->group_id;
        $newGroupId = $this->group_id ?: null;

        $this->editingContact->update([
            'title' => $this->title,
            'first_name' => $this->first_name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'email' => $this->email,
            'dob' => $this->dob ?: null,
            'group_id' => $newGroupId,
        ]);

        // Update counts if group changed
        if ($oldGroupId != $newGroupId) {
            if ($oldGroupId) Group::where('id', '=', $oldGroupId)->decrement('contacts_count');
            if ($newGroupId) Group::where('id', '=', $newGroupId)->increment('contacts_count');
        }

        $this->resetForm();
        $this->showContactModal = false;
        $this->dispatch('toastMagic', status: 'success', message: 'Contact updated successfully.');
    }

    public function delete(Contact $contact)
    {
        if ($contact->group_id) {
            Group::where('id', '=', $contact->group_id)->decrement('contacts_count');
        }
        
        $contact->delete();
        $this->dispatch('toastMagic', status: 'success', message: 'Contact deleted successfully.');
    }

    public function unsubscribe(Contact $contact)
    {
        $contact->update([
            'is_unsubscribed' => true,
            'unsubscribed_at' => now(),
        ]);
        $this->dispatch('toastMagic', status: 'success', message: 'Contact unsubscribed.');
    }

    public function reactivate(Contact $contact)
    {
        $contact->update([
            'is_unsubscribed' => false,
            'unsubscribed_at' => null,
        ]);
        $this->dispatch('toastMagic', status: 'success', message: 'Contact reactivated.');
    }

    public function resetForm()
    {
        $this->title = '';
        $this->first_name = '';
        $this->surname = '';
        $this->phone = '';
        $this->email = '';
        $this->dob = '';
        $this->group_id = '';
        $this->editingContact = null;
        $this->resetErrorBag();
    }

    public function messageContact($contactId)
    {
        session()->flash('selected_contacts', [$contactId]);
        return redirect()->route('messaging.send');
    }

    public function bulkMessage()
    {
        if (empty($this->selectedContacts)) return;
        session()->flash('selected_contacts', $this->selectedContacts);
        return redirect()->route('messaging.send');
    }

    public function bulkMoveToGroup($groupId)
    {
        if (empty($this->selectedContacts)) return;
        
        $contacts = Auth::user()->contacts()->whereIn('id', $this->selectedContacts)->get();
        foreach ($contacts as $contact) {
            $oldGroupId = $contact->group_id;
            $contact->update(['group_id' => $groupId ?: null]);
            
            if ($oldGroupId != $groupId) {
                if ($oldGroupId) Group::where('id', '=', $oldGroupId)->decrement('contacts_count');
                if ($groupId) Group::where('id', '=', $groupId)->increment('contacts_count');
            }
        }

        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->dispatch('toastMagic', status: 'success', message: 'Contacts moved successfully.');
    }

    public function bulkExport()
    {
        if (empty($this->selectedContacts)) return;

        $contacts = Auth::user()->contacts()
            ->with('group')
            ->whereIn('id', $this->selectedContacts)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="selected_contacts_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($contacts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Title', 'First Name', 'Surname', 'Phone', 'Email', 'Group', 'DOB']);

            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->title,
                    $contact->first_name,
                    $contact->surname,
                    $contact->phone,
                    $contact->email,
                    $contact->group?->name ?? 'None',
                    $contact->dob?->format('Y-m-d') ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterGroup', 'filterMonth', 'filterStatus']);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedContacts = Auth::user()->contacts()
                ->when($this->search, function($q) {
                    $q->where(fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%'));
                })
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedContacts = [];
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selectedContacts)) return;

        Contact::whereIn('id', $this->selectedContacts)
            ->where('created_by_user_id', Auth::id())
            ->delete();

        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->dispatch('toastMagic', status: 'success', message: 'Selected contacts deleted.');
    }

    public function import()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $path = $this->csvFile->store('imports');
        
        \App\Jobs\ImportContactsJob::dispatch(
            $path, 
            Auth::id(), 
            Auth::user()->tenant_id ?? 1
        );

        $this->reset(['csvFile', 'showImportModal']);
        $this->dispatch('toastMagic', status: 'success', message: 'Import started in the background. Your contacts will appear shortly.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['title', 'first_name', 'surname', 'phone', 'email', 'dob', 'group']);
            fputcsv($file, ['Mr', 'John', 'Doe', '+233241234567', 'john@example.com', '1990-01-01', 'Customers']);
            fputcsv($file, ['Ms', 'Jane', 'Smith', '+233201234567', 'jane@example.com', '1995-05-15', 'Staff']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $query = Auth::user()->contacts()
            ->with('group')
            ->when($this->search, function($q) {
                $q->where(fn($sq) => $sq->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('surname', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterGroup, fn($q) => $q->where('group_id', $this->filterGroup))
            ->when($this->filterStatus === 'unsubscribed', fn($q) => $q->where('is_unsubscribed', true))
            ->when($this->filterStatus === 'active', fn($q) => $q->where('is_unsubscribed', false))
            ->when($this->filterMonth, function($q) {
                $q->whereRaw("strftime('%m', dob) = ?", [str_pad($this->filterMonth, 2, '0', STR_PAD_LEFT)]);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.contacts', [
            'contacts' => $query->paginate(10),
            'allGroups' => Auth::user()->groups,
        ]);
    }
}
