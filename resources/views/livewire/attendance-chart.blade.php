<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Attendance Reports</h5>
            <!-- Attendance Chart -->
            <div id="attendanceChart"></div>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const attendanceData = @json($attendanceData);
                    const months = @json($months);

                    new ApexCharts(document.querySelector("#attendanceChart"), {
                        series: [
                            {
                                name: 'Present',
                                data: attendanceData.present || [],
                            },
                            {
                                name: 'Late',
                                data: attendanceData.late || [],
                            },
                            {
                                name: 'Absent',
                                data: attendanceData.absent || [],
                            },
                            {
                                name: 'Incomplete',
                                data: attendanceData.incomplete || [],
                            }
                        ],
                        chart: {
                            height: 350,
                            type: 'area',
                            toolbar: {
                                show: true
                            },
                        },
                        markers: {
                            size: 4
                        },
                        colors: ['#2eca6a', '#ffc107', '#dc3545', '#6c757d'],
                        fill: {
                            type: "gradient",
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.4,
                                stops: [0, 90, 100]
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        xaxis: {
                            categories: months,
                        },
                        tooltip: {
                            x: {
                                format: 'MM/yyyy'
                            },
                        }
                    }).render();
                });
            </script>
            <!-- End Attendance Chart -->
        </div>
    </div>
</div>
