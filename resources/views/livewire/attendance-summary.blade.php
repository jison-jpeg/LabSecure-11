<div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Attendance Statistics</h5>

                <!-- Column Chart -->
                <div id="columnChart"></div>

                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        new ApexCharts(document.querySelector("#columnChart"), {
                            series: [{
                                name: 'Present',
                                data: @json($present)
                            }, {
                                name: 'Late',
                                data: @json($late)
                            }, {
                                name: 'Absent',
                                data: @json($absent)
                            }, {
                                name: 'Incomplete',
                                data: @json($incomplete)
                            }],
                            chart: {
                                type: 'bar',
                                height: 350
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '55%',
                                    endingShape: 'rounded'
                                },
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                show: true,
                                width: 2,
                                colors: ['transparent']
                            },
                            xaxis: {
                                categories: @json($months),
                            },
                            fill: {
                                opacity: 1
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + " total"
                                    }
                                }
                            }
                        }).render();
                    });
                </script>
                <!-- End Column Chart -->

            </div>
        </div>
    </div>
</div>
