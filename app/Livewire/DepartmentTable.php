<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\College;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class DepartmentTable extends Component
{
    use WithPagination;

    public $department;
    public $title = 'Create Department';
    public $event = 'create-department';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $college = '';

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

    public function delete(Department $department)
    {
        $department->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Department deleted successfully');
    }

    public function render()
    {
        return view('livewire.department-table', [
            'departments' => Department::with('college')
                ->search($this->search)
                ->when($this->college !== '', function ($query) {
                    $query->where('college_id', $this->college);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
            'colleges' => College::all(),
        ]);
    }

    #[On('refresh-department-table')]
    public function refreshDepartmentTable()
    {
        $this->department = Department::all();
    }
}
