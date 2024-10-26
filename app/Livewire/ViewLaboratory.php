<?php

namespace App\Livewire;

use App\Models\Laboratory;
use App\Models\TransactionLog;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ViewLaboratory extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $formTitle = 'Edit Laboratory';

    public $laboratory;
    public $perPage = 10;
    public $search = '';
    public $action = '';
    public $role = '';
    public $isLocked;
    public $isOccupied;

    // Form Fields
    public $name;
    public $location;
    public $type;
    public $status;
    public $editForm = false;

    public function mount(Laboratory $laboratory)
    {
        // Initialize the laboratory and set the initial lock status
        $this->laboratory = $laboratory;
        $this->isLocked = $this->laboratory->status === 'Locked';
        $this->isOccupied = $this->laboratory->status === 'Occupied';

        // Load initial form fields with current laboratory data
        $this->name = $this->laboratory->name;
        $this->location = $this->laboratory->location;
        $this->type = $this->laboratory->type;
        $this->status = $this->laboratory->status;
    }

    public function toggleLock()
    {
        // Prevent locking/unlocking if the laboratory is occupied
        if ($this->isOccupied) {
            $this->isLocked = false;
            notyf()
                ->position('x', 'right')
                ->position('y', 'top')
                ->error('Laboratory is occupied and cannot be locked.');
            return;
        }

        // Toggle lock/unlock status based on the switch
        $this->isLocked = !$this->isLocked;
        $action = $this->isLocked ? 'lock' : 'unlock';
        $userFullName = Auth::user()->full_name;

        // Update the laboratory status based on the isLocked state
        $this->laboratory->status = $this->isLocked ? 'Locked' : 'Available';
        $this->laboratory->save();

        // Notify the user
        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success("Laboratory {$this->laboratory->name} has been " . ($this->isLocked ? 'locked' : 'unlocked') . '.');

        // Log the lock/unlock action with detailed information
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => 'Laboratory',
            'model_id' => $this->laboratory->id,
            'details' => json_encode([
                'user' => $userFullName,
                'laboratory_name' => $this->laboratory->name,
                'location' => $this->laboratory->location,
                'status' => $this->laboratory->status,
                'action' => $this->isLocked ? 'Locked' : 'Unlocked',
            ]),
        ]);
    }

    public function edit()
    {
        // Enter edit mode
        $this->editForm = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|unique:laboratories,name,' . $this->laboratory->id,
            'location' => 'required',
            'type' => 'required',
        ]);

        // Capture old values for logging
        $original = $this->laboratory->only(['name', 'location', 'type', 'status']);
        $userFullName = Auth::user()->full_name;

        // Only update laboratory details without modifying lock status
        $this->laboratory->update([
            'name' => $this->name,
            'location' => $this->location,
            'type' => $this->type,
            // Keep `status` as it was unless changed in the form
            'status' => $original['status'],
        ]);

        // Log the update action, including changes
        $changes = array_diff_assoc($this->laboratory->only(['name', 'location', 'type', 'status']), $original);
        TransactionLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model' => 'Laboratory',
            'model_id' => $this->laboratory->id,
            'details' => json_encode([
                'user' => $userFullName,
                'laboratory_name' => $this->name,
                'location' => $this->location,
                'type' => $this->type,
                'status' => $this->laboratory->status,
                'changes' => $changes,
            ]),
        ]);

        notyf()
            ->position('x', 'right')
            ->position('y', 'top')
            ->success('Laboratory updated successfully');

        // Exit edit mode
        $this->editForm = false;
    }

    public function cancelEdit()
    {
        // Reset the form fields and exit edit mode
        $this->editForm = false;
        $this->name = $this->laboratory->name;
        $this->location = $this->laboratory->location;
        $this->type = $this->laboratory->type;
        $this->status = $this->laboratory->status;
    }

    public function clear()
    {
        $this->search = '';
        $this->action = '';
        $this->role = '';
    }

    public function getLogsProperty()
    {
        $search = trim(preg_replace('/\s+/', ' ', $this->search));

        $attendanceIds = $this->laboratory->schedules()->with('attendances')->get()->pluck('attendances.*.id')->flatten();

        return TransactionLog::where(function ($query) use ($attendanceIds) {
                $query->where('model', 'Attendance')
                      ->whereIn('model_id', $attendanceIds)
                      ->orWhere(function ($query) {
                          $query->where('model', 'Laboratory')
                                ->where('model_id', $this->laboratory->id);
                      });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($query) use ($search) {
                        $query->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$search}%"])
                              ->orWhere('first_name', 'like', '%' . $search . '%')
                              ->orWhere('last_name', 'like', '%' . $search . '%')
                              ->orWhere('username', 'like', '%' . $search . '%');
                    })
                    ->orWhereDate('created_at', 'like', '%' . $search . '%')
                    ->orWhereTime('created_at', 'like', '%' . $search . '%');
                });
            })
            ->when($this->action, function ($query) {
                $query->where('action', $this->action);
            })
            ->when($this->role, function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('name', $this->role);
                    });
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.view-laboratory', [
            'laboratory' => $this->laboratory,
            'logs' => $this->logs,
        ]);
    }
}
