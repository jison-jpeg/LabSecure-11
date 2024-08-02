<?php

namespace App\Livewire;

use App\Models\Section;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ClassTable extends Component
{
    use WithPagination;

    public $section;
    public $title = 'Class Table';
    public $event = 'class-table';

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $yearLevel = '';

    #[Url(history: true)]
    public $semester = '';

    #[Url(history: true)]
    public $schoolYear = '';

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
        $this->yearLevel = '';
        $this->semester = '';
        $this->schoolYear = '';
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

    public function render()
    {
        $instructorId = Auth::id();

        return view('livewire.class-table', [
            'sections' => Section::whereHas('schedules', function ($query) use ($instructorId) {
                    $query->where('instructor_id', $instructorId);
                })
                ->search($this->search)
                ->when($this->yearLevel !== '', function ($query) {
                    $query->where('year_level', $this->yearLevel);
                })
                ->when($this->semester !== '', function ($query) {
                    $query->where('semester', $this->semester);
                })
                ->when($this->schoolYear !== '', function ($query) {
                    $query->where('school_year', $this->schoolYear);
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage),
            'yearLevels' => Section::select('year_level')->distinct()->orderBy('year_level')->get(),
            'semesters' => Section::select('semester')->distinct()->orderBy('semester')->get(),
            'schoolYears' => Section::select('school_year')->distinct()->orderBy('school_year')->get(),
        ]);
    }

    #[On('refresh-class-table')]
    public function refreshClassTable()
    {
        $this->section = Section::all();
    }
}
