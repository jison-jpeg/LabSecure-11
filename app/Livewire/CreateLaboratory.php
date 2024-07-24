<?php

namespace App\Livewire;

use App\Models\Laboratory;
use Livewire\Component;
use Livewire\Attributes\On;


class CreateLaboratory extends Component
{
    public $formTitle = 'Create Laboratory';
    public $editForm = false;
    public $laboratory;
    public $name;
    public $location;
    public $type;
    public $status;

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

        Laboratory::create([
            'name' => $this->name,
            'location' => $this->location,
            'type' => $this->type,
            'status' => $this->status,
        ]);

        $this->dispatch('refresh-laboratory-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Laboratory created successfully');
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit(Laboratory $laboratory)
    {
        $this->editForm = true;
        $this->formTitle = 'Edit Laboratory';
        $this->laboratory = $laboratory;
        $this->name = $laboratory->name;
        $this->location = $laboratory->location;
        $this->type = $laboratory->type;
        $this->status = $laboratory->status;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|unique:laboratories,name,' . $this->laboratory->id,
            'location' => 'required',
            'type' => 'required',
            'status' => 'required',
        ]);

        $this->laboratory->update([
            'name' => $this->name,
            'location' => $this->location,
            'type' => $this->type,
            'status' => $this->status,
        ]);

        notyf()
        ->position('x', 'right')
        ->position('y', 'top')
        ->success('Laboratory updated successfully');
        $this->dispatch('refresh-laboratory-table');
        $this->reset();
    }
}
