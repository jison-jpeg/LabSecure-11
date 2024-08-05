<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Validator;

class CreateSection extends Component
{
    public $formTitle = 'Create Section';
    public $editForm = false;
    public $section;
    public $name;
    public $college_id;
    public $department_id;
    public $year_level;
    public $semester;
    public $school_year;

    public function render()
    {
        return view('livewire.create-section', [
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'college_id' => 'required',
            'department_id' => 'required',
            'year_level' => 'required',
            'semester' => 'required',
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/', function ($attribute, $value, $fail) {
                $years = explode('-', $value);
                if (count($years) !== 2 || (int)$years[0] <= 0 || (int)$years[1] <= 0 || (int)$years[0] >= (int)$years[1] || (int)$years[0] < 1900 || (int)$years[1] > 2100) {
                    $fail('The school year is not valid.');
                }
            }],
        ];
    }

    public function save()
    {
        $this->validate($this->rules());

        Section::create([
            'name' => $this->name,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'school_year' => $this->school_year,
        ]);

        $this->dispatch('refresh-section-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Section created successfully');
        $this->reset();
    }

    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset();
    }

    #[On('edit-mode')]
    public function edit($id)
    {
        $this->formTitle = 'Edit Section';
        $this->editForm = true;
        $this->section = Section::findOrFail($id);
        $this->name = $this->section->name;
        $this->college_id = $this->section->college_id;
        $this->department_id = $this->section->department_id;
        $this->year_level = $this->section->year_level;
        $this->semester = $this->section->semester;
        $this->school_year = $this->section->school_year;
    }

    public function update()
    {
        $this->validate($this->rules());

        $this->section->update([
            'name' => $this->name,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'school_year' => $this->school_year,
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Section updated successfully');
        $this->dispatch('refresh-section-table');
        $this->reset();
    }
}
