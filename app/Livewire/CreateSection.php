<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

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
            'school_year' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    $years = explode('-', $value);
                    $currentYear = (int)date('Y');
                    $minYear = $currentYear - 3;
                    $maxYear = $currentYear + 3;

                    if (count($years) !== 2) {
                        $fail('The school year must be in the format YYYY-YYYY.');
                    } elseif ((int)$years[0] >= (int)$years[1]) {
                        $fail('The first year must be less than the second year.');
                    } elseif ((int)$years[0] < $minYear || (int)$years[1] > $maxYear) {
                        $fail("The school year must be between $minYear and $maxYear.");
                    }
                }
            ],
        ];
    }

    public function save()
    {
        $this->validate($this->rules());

        // Combine the year level and section name
        $combinedName = $this->year_level . $this->name;

        // Create the section
        $section = Section::create([
            'name' => $combinedName,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'school_year' => $this->school_year,
        ]);

        // Create transaction log for section creation
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model' => 'Section',
            'model_id' => $section->id,
            'details' => json_encode([
                'section_name' => $combinedName,
                'college' => College::find($this->college_id)->name,
                'department' => Department::find($this->department_id)->name,
                'year_level' => $this->year_level,
                'semester' => $this->semester,
                'school_year' => $this->school_year,
            ]),
        ]);

        $this->dispatch('refresh-section-table');
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Section created successfully');
        $this->reset();
    }

    public function update()
    {
        $this->validate($this->rules());

        // Combine the year level and section name when updating
        $combinedName = $this->year_level . $this->name;

        // Update the section
        $this->section->update([
            'name' => $combinedName,
            'college_id' => $this->college_id,
            'department_id' => $this->department_id,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'school_year' => $this->school_year,
        ]);

        // Create transaction log for section update
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'Section',
            'model_id' => $this->section->id,
            'details' => json_encode([
                'section_name' => $combinedName,
                'college' => College::find($this->college_id)->name,
                'department' => Department::find($this->department_id)->name,
                'year_level' => $this->year_level,
                'semester' => $this->semester,
                'school_year' => $this->school_year,
            ]),
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Section updated successfully');
        $this->dispatch('refresh-section-table');
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
}
