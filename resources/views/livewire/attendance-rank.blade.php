<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Instructor Attendance Rankings</h5>

            <!-- Ranks Chart -->
            <div id="attendanceRankChart"></div>

            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const attendanceData = @json($attendanceData);

                    // Generate a distinct color for each instructor
                    const colors = [
                        '#1E90FF', '#32CD32', '#FF6347', '#FFD700', '#6A5ACD', '#FF69B4', '#8A2BE2', '#00CED1',
                        '#FF4500', '#DA70D6', '#2E8B57', '#B8860B', '#CD5C5C', '#F08080', '#20B2AA'
                    ];

                    const seriesData = attendanceData.map((instructor, index) => ({
                        name: `${instructor.first_name} ${instructor.last_name}`,
                        data: [
                            instructor.present_count,
                            instructor.late_count,
                            instructor.absent_count,
                            instructor.incomplete_count
                        ],
                        color: colors[index % colors.length] // Assign distinct colors
                    }));

                    new ApexCharts(document.querySelector("#attendanceRankChart"), {
                        series: seriesData,
                        chart: {
                            height: 350,
                            type: 'bar',
                            toolbar: {
                                show: false
                            },
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
                            categories: ['Present', 'Late', 'Absent', 'Incomplete'],
                        },
                        fill: {
                            opacity: 1
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return val + " records";
                                }
                            }
                        },
                        colors: seriesData.map(data => data.color) // Assign dynamic colors
                    }).render();
                });
            </script>
        </div>
    </div>
</div>
