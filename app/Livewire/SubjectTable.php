<?php

namespace App\Livewire;

use App\Models\Subject;
use App\Models\College;
use App\Models\Department;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class SubjectTable extends Component
{
    use WithPagination;

    public $title = 'Subjects';
    public $event = 'refresh-subject-table';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    public $subject;

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

    public function delete(Subject $subject)
    {
        $subject->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Subject deleted successfully');
    }

    public function render()
    {
        return view('livewire.subject-table', [
            'subjects' => Subject::with(['college', 'department'])
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

    #[On('refresh-subject-table')]
    public function refreshSubjectTable()
    {
        $this->subject = Subject::all();

    }
}
