<?php

namespace App\Livewire;

use App\Models\College;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Validator;
use Flasher\Notyf\Prime\NotyfInterface;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;

class CreateCollege extends Component
{
    public $formTitle = 'Create College';
    public $editForm = false;
    public $college;
    public $name;
    public $description; // Added description field

    public function render()
    {
        return view('livewire.create-college');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'name' => 'required|unique:colleges,name,' . ($this->college->id ?? 'NULL'),
            'description' => 'nullable|string|max:1000'
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:colleges,name,' . ($this->college->id ?? 'NULL'),
            'description' => 'nullable|string|max:1000'
        ]);

        $college = College::create([
            'name' => $this->name,
            'description' => $this->description, // Save description
        ]);

        // Log the creation of the college
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model' => 'College',
            'model_id' => $college->id,
            'details' => json_encode([
                'college_name' => $college->name,
                'description' => $college->description, // Include description in log
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);

        $this->dispatch('refresh-college-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('College created successfully');
        $this->reset(['name', 'description']); // Reset description
    }

    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset(['name', 'description']); // Reset description
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit College';
        $this->editForm = true;
        $this->college = College::findOrFail($id);
        $this->name = $this->college->name;
        $this->description = $this->college->description; // Load description for editing
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|unique:colleges,name,' . $this->college->id,
            'description' => 'nullable|string|max:1000'
        ]);

        $this->college->update([
            'name' => $this->name,
            'description' => $this->description, // Update description
        ]);

        // Log the update of the college
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'College',
            'model_id' => $this->college->id,
            'details' => json_encode([
                'college_name' => $this->college->name,
                'description' => $this->college->description, // Include description in log
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('College updated successfully');
        $this->dispatch('refresh-college-table');
        $this->reset(['name', 'description']); // Reset description
    }
}
