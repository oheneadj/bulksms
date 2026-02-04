<?php

namespace App\Livewire\Messaging;

use App\Models\MessageTemplate;
use Livewire\Component;
use Livewire\WithPagination;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class Templates extends Component
{
    use WithPagination;

    public $name = '';
    public $body = '';
    public $editingTemplateId = null;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|min:3|max:50',
        'body' => 'required|string|min:10|max:1000',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingTemplateId) {
            MessageTemplate::query()->findOrFail($this->editingTemplateId)->update([
                'name' => $this->name,
                'body' => $this->body,
            ]);
            ToastMagic::success(__('Template updated successfully.'));
        } else {
            MessageTemplate::create([
                'tenant_id' => auth()->user()->tenant_id ?? 1,
                'name' => $this->name,
                'body' => $this->body,
            ]);
            ToastMagic::success(__('Template created successfully.'));
        }

        $this->reset(['name', 'body', 'editingTemplateId']);
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $template = MessageTemplate::findOrFail($id);
        $this->editingTemplateId = $template->id;
        $this->name = $template->name;
        $this->body = $template->body;
        
        $this->dispatch('open-modal', name: 'template-modal');
    }

    public function delete($id)
    {
        MessageTemplate::query()->findOrFail($id)->delete();
        ToastMagic::success(__('Template deleted successfully.'));
    }

    public function resetForm()
    {
        $this->reset(['name', 'body', 'editingTemplateId']);
        $this->resetErrorBag();
    }

    public function cancel()
    {
        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.messaging.templates', [
            'templates' => MessageTemplate::query()
                ->where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ]);
    }
}
