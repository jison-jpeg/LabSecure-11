<div>
    <div class="row g-0">
        <!-- Left Side: Radial Bar Chart -->
        <div class="col-md-6">
            <div class="card-body">
                <h5 class="card-title">Laboratory</h5>

                <!-- Radial Bar Chart -->
                <div id="radialBarChart"></div>

                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const types = @json($types); // Get the lab types data
                        const labels = types.map(type => type.type); // Extract labels from types
                        const series = types.map(type => type.total); // Extract series from types

                        new ApexCharts(document.querySelector("#radialBarChart"), {
                            series: series,
                            chart: {
                                height: 325,
                                type: 'radialBar',
                                toolbar: {
                                    show: true
                                }
                            },
                            plotOptions: {
                                radialBar: {
                                    dataLabels: {
                                        name: {
                                            fontSize: '22px',
                                        },
                                        value: {
                                            fontSize: '16px',
                                        },
                                        total: {
                                            show: true,
                                            label: 'Total',
                                            formatter: function(w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            }
                                        }
                                    }
                                }
                            },
                            labels: labels, // Set the labels dynamically
                        }).render();
                    });
                </script>
                <!-- End Radial Bar Chart -->
            </div>
        </div>

        <!-- Right Side: Stats and Information -->
        <div class="col-md-6">
            <div class="card-body">
                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('laboratories') }}" class="btn btn-primary">View All</a>
                </div>

                <!-- Statistic Block 1 -->
                <div class="row mb-3 mt-5">
                    <div class="col-6 mt-md-5 mt-sm-2">
                        <h6>Total Laboratories</h6>
                        <p class="h4">{{ $totalLabs }}</p>
                    </div>
                    <div class="col-6 mt-md-5 mt-sm-2">
                        <h6>Active Users</h6>
                        <p class="h4">N/A</p> <!-- Placeholder for active users -->
                    </div>
                </div>

                <!-- Statistic Block 2 -->
                <div class="row mb-3">
                    <div class="col-6">
                        <h6>Available</h6>
                        <p class="h4">{{ $availableLabs }}</p>
                    </div>
                    <div class="col-6">
                        <h6>Occupied</h6>
                        <p class="h4">{{ $occupiedLabs }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
