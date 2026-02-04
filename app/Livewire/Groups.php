<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Group;

#[Title('Contact Groups')]
class Groups extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingGroup = null;

    public $name = '';
    public $description = '';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:50',
            'description' => 'nullable|max:255',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function edit(Group $group)
    {
        $this->editingGroup = $group;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->showEditModal = true;
    }

    public function save()
    {
        $this->validate();

        Auth::user()->groups()->create([
            'tenant_id' => Auth::user()->tenant_id ?? 1,
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->resetForm();
        $this->showCreateModal = false;
        $this->dispatch('toastMagic', status: 'success', message: 'Group created successfully.');
    }

    public function update()
    {
        $this->validate();

        $this->editingGroup->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->resetForm();
        $this->showEditModal = false;
        $this->dispatch('toastMagic', status: 'success', message: 'Group updated successfully.');
    }

    public function delete(Group $group)
    {
        if ($group->contacts_count > 0) {
            $this->dispatch('toastMagic', status: 'error', message: 'Cannot delete group with contacts.');
            return;
        }

        $group->delete();
        $this->dispatch('toastMagic', status: 'success', message: 'Group deleted successfully.');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->editingGroup = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = Auth::user()->groups()->with('creator');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.groups', [
            'groups' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(12),
        ]);
    }
}
