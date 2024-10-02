<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class SectionTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $section;
    public $title = 'Create Section';
    public $event = 'create-section';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $college = '';

    #[Url(history: true)]
    public $department = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clear()
    {
        $this->search = '';
        $this->college = '';
        $this->department = '';
    }

    public function setSortBy($sortByField)
    {
        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function delete(Section $section)
    {
        $section->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Section deleted successfully');
    }

    public function render()
    {
        return view('livewire.section-table', [
            'sections' => Section::with('college', 'department')
                ->search($this->search)
                ->when($this->college !== '', function ($query) {
                    $query->where('college_id', $this->college);
                })
                ->when($this->department !== '', function ($query) {
                    $query->where('department_id', $this->department);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
            'colleges' => College::all(),
            'departments' => Department::all(),
        ]);
    }

    #[On('refresh-section-table')]
    public function refreshSectionTable()
    {
        $this->section = Section::all();
    }
}
