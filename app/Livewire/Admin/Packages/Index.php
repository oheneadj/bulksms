<?php

namespace App\Livewire\Admin\Packages;

use App\Models\CreditPackage;
use App\Models\SystemCredit;
use Livewire\Component;
use Livewire\WithPagination;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class Index extends Component
{
    use WithPagination;

    // Package Form
    public $name = '';
    public $credits = 0;
    public $unit_price = 0;
    public $price = 0; // Calculated
    public $description = '';
    public $is_active = true;
    public $editingPackageId = null;
    public $showPackageModal = false;

    // Inventory Form
    public $showRestockModal = false;
    public $restockAmount = 0;

    protected $rules = [
        'name' => 'required|string|min:3|max:100',
        'credits' => 'required|integer|min:1',
        'unit_price' => 'required|numeric|min:0.0001',
        'description' => 'nullable|string|max:500',
        'is_active' => 'boolean',
    ];

    public function updated($propertyName)
    {
        if ($propertyName === 'credits' || $propertyName === 'unit_price') {
            $this->price = (float)$this->credits * (float)$this->unit_price;
        }
    }

    // Package Methods
    public function create()
    {
        $this->resetForm();
        $this->showPackageModal = true;
    }

    public function edit(CreditPackage $package)
    {
        $this->editingPackageId = $package->id;
        $this->name = $package->name;
        $this->credits = $package->credits;
        $this->unit_price = $package->unit_price;
        $this->price = $package->price;
        $this->description = $package->description;
        $this->is_active = $package->is_active;
        $this->showPackageModal = true;
    }

    public function savePackage()
    {
        $this->validate();

        $price = (float)$this->credits * (float)$this->unit_price;

        $data = [
            'name' => $this->name,
            'credits' => $this->credits,
            'unit_price' => $this->unit_price,
            'price' => $price,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->editingPackageId) {
            CreditPackage::find($this->editingPackageId)->update($data);
            ToastMagic::success('Package updated successfully.');
        } else {
            CreditPackage::create($data);
            ToastMagic::success('Package created successfully.');
        }

        $this->showPackageModal = false;
        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function deletePackage($id)
    {
        CreditPackage::findOrFail($id)->delete();
        ToastMagic::success('Package deleted.');
    }

    public function resetForm()
    {
        $this->reset(['name', 'credits', 'unit_price', 'price', 'description', 'is_active', 'editingPackageId']);
    }

    // Inventory Methods
    public function openRestockModal()
    {
        $this->restockAmount = 0;
        $this->showRestockModal = true;
    }

    public function restockInventory()
    {
        $this->validate([
            'restockAmount' => 'required|integer|min:1',
        ]);

        $inventory = SystemCredit::firstOrCreate([]);
        $inventory->increment('balance', $this->restockAmount);
        $inventory->increment('total_purchased', $this->restockAmount);

        ToastMagic::success("Inventory restocked by {$this->restockAmount} credits.");
        $this->showRestockModal = false;
        $this->restockAmount = 0;
    }

    public function render()
    {
        $inventory = SystemCredit::firstOrCreate([], [
            'balance' => 0,
            'total_purchased' => 0,
            'total_sold' => 0
        ]);

        return view('livewire.admin.packages.index', [
            'packages' => CreditPackage::latest()->paginate(10),
            'inventory' => $inventory,
        ])->layout('layouts.admin');
    }
}
