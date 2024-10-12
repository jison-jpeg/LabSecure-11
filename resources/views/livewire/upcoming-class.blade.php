<div class="row">
    <!-- If there is an ongoing class -->
    @if ($ongoingClass)
        <div class="{{ $upcomingClass ? 'col-12 col-md-6' : 'col-12' }} ">
            <div class="card  card-info position-relative">
                <div class="card-body text-white">
                    <h5 class="card-title fs-3">On-going Class</h5>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <h6>Subject</h6>
                            <p class="text-truncate">{{ $ongoingClass->subject->name }}</p>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6>Instructor</h6>
                            <p class="text-truncate">{{ $ongoingClass->instructor->full_name }}</p>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6>Time</h6>
                            <p class="text-truncate">{{ Carbon\Carbon::parse($ongoingClass->start_time)->format('h:i A') }} - 
                                {{ Carbon\Carbon::parse($ongoingClass->end_time)->format('h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- If there is an upcoming class, it will take the right column -->
        @if ($upcomingClass)
            <div class="col-12 col-md-6">
                <div class="card card-info position-relative">
                    <div class="card-body text-white">
                        <h5 class="card-title fs-3">Up-next</h5>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Subject</h6>
                                <p class="text-truncate">{{ $upcomingClass->subject->name }}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <h6>Instructor</h6>
                                <p class="text-truncate">{{ $upcomingClass->instructor->full_name }}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <h6>Time</h6>
                                <p class="text-truncate">{{ Carbon\Carbon::parse($upcomingClass->start_time)->format('h:i A') }} - 
                                    {{ Carbon\Carbon::parse($upcomingClass->end_time)->format('h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- If there is no ongoing class, but there's an upcoming class, show the upcoming class in full width -->
    @if (!$ongoingClass && $upcomingClass)
        <div class="col-12">
            <div class="card card-info position-relative">
                <div class="card-body text-white">
                    <h5 class="card-title fs-3">Up-coming Class</h5>
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <h6>Subject</h6>
                            <p class="text-truncate">{{ $upcomingClass->subject->name }}</p>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6>Instructor</h6>
                            <p class="text-truncate">{{ $upcomingClass->instructor->full_name }}</p>
                        </div>
                        <div class="col-12 col-md-4">
                            <h6>Time</h6>
                            <p class="text-truncate">{{ Carbon\Carbon::parse($upcomingClass->start_time)->format('h:i A') }} - 
                                {{ Carbon\Carbon::parse($upcomingClass->end_time)->format('h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- No upcoming or ongoing class -->
    @if (!$ongoingClass && !$upcomingClass)
        <div class="col-12">
            <div class="card card-info position-relative">
                <div class="card-body text-white">
                    <h5 class="card-title fs-3">No Upcoming Classes</h5>
                    <p class="text-truncate">You have no upcoming classes scheduled at the moment.</p>
                </div>
            </div>
        </div>
    @endif
</div>
