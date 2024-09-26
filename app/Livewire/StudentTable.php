<?php

namespace App\Livewire;

use App\Exports\StudentExport;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Section;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class StudentTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $title = 'Create Student';
    public $event = 'create-student';

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
            ->success('Student deleted successfully');
    }

    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new StudentExport($this->search, $this->college, $this->department), 'students.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new StudentExport($this->search, $this->college, $this->department), 'students.xlsx');
            case 'pdf':
                // Implement PDF export if needed
                break;
        }
    }

    public function render()
    {
        return view('livewire.student-table', [
            'users' => User::with(['college', 'department'])
                ->where('role_id', 3) // Assuming role_id 3 is for students
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
            'sections' => Section::all(),
        ]);
    }

    #[On('refresh-student-table')]
    public function refreshStudentTable()
    {
        $this->user = User::all();
    }
}
