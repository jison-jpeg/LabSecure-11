<div>
    <section class="section dashboard">
        <div class="row">

            <!-- Subject Overview -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subject Overview</h5>
                        <p><strong>Subject Code:</strong> {{ $subject->code }}</p>
                        <p><strong>Name:</strong> {{ $subject->name }}</p>
                        <p><strong>Description:</strong> {{ $subject->description }}</p>
                        <p><strong>Department:</strong> {{ $subject->department->name }}</p>
                    </div>
                </div>

                <!-- List of Schedules -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Schedules for {{ $subject->name }}</h5>
                        <div class="overflow-auto">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Section Code</th>
                                        <th scope="col">Instructor</th>
                                        <th scope="col">Section</th>
                                        <th scope="col">Days</th>
                                        <th scope="col">Start Time</th>
                                        <th scope="col">End Time</th>
                                        <th scope="col" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $key => $schedule)
                                        <tr>
                                            <th scope="row">{{ $key + 1 }}</th>
                                            <td>{{ $schedule->schedule_code }}</td>
                                            <td>{{ $schedule->instructor->full_name }}</td>
                                            <td>{{ $schedule->section->name }}</td>
                                            <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                                            <td class="text-center">
                                                <div class="btn-group dropstart">
                                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
                                                        <li><a href="{{ route('schedule.view', ['schedule' => $schedule->id]) }}" class="dropdown-item">View</a></li>
                                                        <li><button @click="$dispatch('edit-mode',{id:{{ $schedule->id }}})" type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#verticalycentered">Edit</button></li>
                                                        <li><button wire:click="delete({{ $schedule->id }})" wire:confirm="Are you sure you want to delete '{{ $schedule->schedule_code }}'" type="button" class="dropdown-item text-danger">Delete</button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right side columns -->
            <div class="col-lg-4 d-flex flex-column">
                <div class="card h-100">
                    @livewire('logs-widget')
                </div>
            </div>
        </div>
    </section>
</div>
