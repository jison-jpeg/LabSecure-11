<?php

namespace App\Livewire;

use App\Models\Laboratory;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class CreateLaboratory extends Component
{
    public $formTitle = 'Create Laboratory';
    public $editForm = false;
    public $laboratory;
    public $name;
    public $location;
    public $type;
    public $status = 'Available';  // Default status to "Available"

    public function render()
    {
        return view('livewire.create-laboratory');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'name' => 'required|unique:laboratories',
            'location' => 'required',
            'type' => 'required',
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:laboratories',
            'location' => 'required',
            'type' => 'required',
        ]);

        // Set status to 'Available' if it's null or not set
        $this->status = $this->status ?? 'Available';

        $laboratory = Laboratory::create([
            'name' => $this->name,
            'location' => $this->location,
            'type' => $this->type,
            'status' => $this->status,
        ]);

        // Log the transaction with user details
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model' => 'Laboratory',
            'model_id' => $laboratory->id,
            'details' => json_encode([
                'user' => Auth::user()->full_name,
                'laboratory_name' => $this->name,
                'location' => $this->location,
                'type' => $this->type,
                'status' => $this->status,
            ]),
        ]);

        $this->dispatch('refresh-laboratory-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Laboratory created successfully');
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->editForm = true;
        $this->formTitle = 'Edit Laboratory';
        $this->laboratory = Laboratory::findOrFail($id);
        $this->name = $this->laboratory->name;
        $this->location = $this->laboratory->location;
        $this->type = $this->laboratory->type;
        $this->status = $this->laboratory->status ?? 'Available';
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|unique:laboratories,name,' . $this->laboratory->id,
            'location' => 'required',
            'type' => 'required',
            'status' => 'required',
        ]);

        // Capture old values for logging
        $original = $this->laboratory->only(['name', 'location', 'type', 'status']);
        
        // Update laboratory details
        $this->laboratory->update([
            'name' => $this->name,
            'location' => $this->location,
            'type' => $this->type,
            'status' => $this->status ?? 'Available',
        ]);

        // Log the update action, including changes
        $changes = array_diff_assoc($this->laboratory->only(['name', 'location', 'type', 'status']), $original);
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'Laboratory',
            'model_id' => $this->laboratory->id,
            'details' => json_encode([
                'user' => Auth::user()->full_name,
                'laboratory_name' => $this->name,
                'location' => $this->location,
                'type' => $this->type,
                'status' => $this->status,
                'changes' => $changes,
            ]),
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Laboratory updated successfully');
        $this->dispatch('refresh-laboratory-table');
        $this->reset();
    }

    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset();
    }
}
