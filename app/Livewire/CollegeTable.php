<?php

namespace App\Livewire;

use App\Exports\CollegeExport;
use App\Models\College;
use App\Models\TransactionLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class CollegeTable extends Component
{
    use WithPagination;

    public $college;
    public $title = 'Create College';
    public $event = 'create-college';

    #[Url(history: true)]
    public $search = '';

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

    public function delete(College $college)
    {
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model' => 'College',
            'model_id' => $college->id,
            'details' => json_encode([
                'college_name' => $college->name,
                'user' => Auth::user()->full_name,
                'username' => Auth::user()->username,
            ]),
        ]);
        $college->delete();
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('College deleted successfully');
    }

    public function exportAs($format)
    {
        switch ($format) {
            case 'csv':
                return Excel::download(new CollegeExport(), 'College and Departments.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'excel':
                return Excel::download(new CollegeExport(), 'College and Departments.xlsx');
            case 'pdf':
                // You can implement a PDF export logic if needed.
                break;
        }
    }

    public function render()
    {
        return view('livewire.college-table', [
            'colleges' => College::search($this->search)
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }

    #[On('refresh-college-table')]
    public function refreshCollegeTable()
    {
        $this->college = College::all();
    }
}
