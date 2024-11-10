<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;

class EditSection extends Component
{
    public $formTitle = 'Edit Section';
    public $editForm = true;
    public $section;
    public $name;
    public $college_id;
    public $department_id;
    public $year_level;
    public $semester;
    public $school_year;
    public $colleges = [];
    public $departments = [];

    protected $listeners = ['edit-mode' => 'edit'];

    public function mount(Section $section)
    {
        $this->colleges = College::all();
        $this->section = $section;
        $this->loadSectionData();
    }

    public function loadSectionData()
    {
        $this->name = $this->section->name;
        $this->college_id = $this->section->college_id;
        $this->department_id = $this->section->department_id;
        $this->year_level = $this->section->year_level;
        $this->semester = $this->section->semester;
        $this->school_year = $this->section->school_year;

        $this->updateDepartments();
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('sections', 'name')
                    ->where(function ($query) {
                        return $query->where('year_level', $this->year_level)
                                     ->where('department_id', $this->department_id);
                    })
                    ->ignore($this->section ? $this->section->id : null),
            ],
            'college_id' => 'required|exists:colleges,id',
            'department_id' => 'required|exists:departments,id',
            'year_level' => [
                'required',
                'string',
                Rule::in(['1', '2', '3', '4', '5', '6', 'irregular']),
            ],
            'semester' => 'required|string',
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

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function updatedCollegeId()
    {
        $this->department_id = null; // Reset department selection
        $this->updateDepartments(); // Update departments based on the selected college
    }

    public function updateDepartments()
    {
        $this->departments = $this->college_id 
            ? Department::where('college_id', $this->college_id)->get() 
            : [];
    }

    public function update()
{
    $this->validate();

    $this->section->update([
        'name' => $this->name,
        'college_id' => $this->college_id,
        'department_id' => $this->department_id,
        'year_level' => $this->year_level,
        'semester' => $this->semester,
        'school_year' => $this->school_year,
    ]);

    TransactionLog::create([
        'user_id' => Auth::id(),
        'action' => 'update',
        'model' => 'Section',
        'model_id' => $this->section->id,
        'details' => json_encode([
            'section_name' => $this->name,
            'college' => College::find($this->college_id)->name,
            'department' => Department::find($this->department_id)->name,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'school_year' => $this->school_year,
        ]),
    ]);

    notyf()->position('x', 'right')->position('y', 'top')->success('Section updated successfully');
    $this->dispatch('close-modal'); // Corrected line
}


    #[On('reset-modal')]
    public function close()
    {
        $this->resetErrorBag();
        $this->reset();
    }

    public function render()
    {
        return view('livewire.edit-section', [
            'colleges' => $this->colleges,
            'departments' => $this->departments,
        ]);
    }

    public function edit($id)
    {
        $this->section = Section::findOrFail($id);
        $this->loadSectionData();
    }
}
