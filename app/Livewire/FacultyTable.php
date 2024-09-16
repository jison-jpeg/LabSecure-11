<?php

namespace App\Livewire;

use App\Exports\FacultyExport;
use App\Models\College;
use App\Models\Department;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class FacultyTable extends Component
{
    use WithPagination;

    public $user;
    public $title = 'Create Faculty';
    public $event = 'create-faculty';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;
    
    #[Url(history: true)]
    public $college = '';

    #[Url(history: true)]
    public $department = '';

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

    public function delete(User $user)
    {
        $user->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Faculty deleted successfully');
    }

    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new FacultyExport($this->search, $this->college, $this->department), 'faculty.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new FacultyExport($this->search, $this->college, $this->department), 'faculty.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                break;
        }
    }
    
    public function render()
    {
        return view('livewire.faculty-table', [
            'users' => User::where('role_id', 2) // Assuming role_id 2 is for faculty
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

    #[On('refresh-faculty-table')]
    public function refreshFacultyTable()
    {
        $this->user = User::all();
    }
}
