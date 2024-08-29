<div>
    <div class="row g-0">
        <!-- Left Side: Pie Chart -->
        <div class="col-md-6">
            <div class="card-body">
                <h5 class="card-title">Laboratory</h5>

                <!-- Radial Bar Chart -->
                <div id="radialBarChart"></div>

                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        new ApexCharts(document.querySelector("#radialBarChart"), {
                            series: [44, 55, 67, 83],
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
                                                // By default this function returns the average of all series. The below is just an example to show the use of custom formatter function
                                                return 249
                                            }
                                        }
                                    }
                                }
                            },
                            labels: ['Apples', 'Oranges', 'Bananas', 'Berries'],
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
                        <p class="h4">10</p>
                    </div>
                    <div class="col-6 mt-md-5 mt-sm-2">
                        <h6>Active Users</h6>
                        <p class="h4">1001</p>
                    </div>
                </div>

                <!-- Statistic Block 2 -->
                <div class="row mb-3">
                    <div class="col-6">
                        <h6>Available</h6>
                        <p class="h4">5</p>
                    </div>
                    <div class="col-6">
                        <h6>Unavailable</h6>
                        <p class="h4">5</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
