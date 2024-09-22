<div wire:poll.10s>
    <div class="filter">
        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
                <h6>Filter</h6>
            </li>
            <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'today')">Today</a></li>
            <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'month')">This Month</a></li>
            <li><a class="dropdown-item" href="#" wire:click.prevent="$set('filter', 'year')">This Year</a></li>
            <li><a class="dropdown-item text-primary" href="{{route ('transaction-logs')}}">View All</a></li>
        </ul>
    </div>

    <div class="card-body">
        <h5 class="card-title">Recent Activity <span>| {{ ucfirst($filter) }}</span></h5>

        <div class="activity">
            @foreach ($this->logs as $log)
                <div class="activity-item d-flex">
                    <div class="activite-label">{{ $log->created_at->diffForHumans(null, null, true) }}</div>
                    <i class='bi bi-circle-fill activity-badge 
                        @if ($log->action == 'check_in') text-info
                        @elseif ($log->action == 'check_out') text-secondary
                        @elseif ($log->action == 'update') text-warning
                        @elseif ($log->action == 'create') text-success
                        @else text-danger @endif align-self-start'></i>
                    <div class="activity-content">
                        {{ $log->readable_details }}
                    </div>
                </div><!-- End activity item-->
            @endforeach
        </div>

        @if($this->logs->isEmpty())
            <div class="text-center mt-4">
                <p>No recent activity.</p>
            </div>
        @endif
    </div>
</div>
