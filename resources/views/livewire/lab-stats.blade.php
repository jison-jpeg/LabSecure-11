<div>
    <div class="row g-0">
        <!-- Left Side: Radial Bar Chart -->
        <div class="col-md-6">
            <div class="card-body">
                <h5 class="card-title">Laboratory Availability</h5>

                <!-- Radial Bar Chart -->
<div id="radialBarChart"></div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const types = @json($types); // Get the lab types data from Livewire
    const labels = types.map(type => type.type); // Extract labels from types
    const series = types.map(type => (type.available / type.total) * 100); // Calculate percentage of available labs

    new ApexCharts(document.querySelector("#radialBarChart"), {
      series: series, // Percentage of available labs to total labs
      chart: {
        height: 350,
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
              formatter: function(val, opts) {
                // Show available / total labs on the chart value
                const availableLabs = types[opts.seriesIndex].available;
                const totalLabs = types[opts.seriesIndex].total;
                return `${availableLabs} / ${totalLabs}`; // Example: "5 / 7"
              },
            },
            total: {
              show: true,
              label: 'Total Labs',
              formatter: function() {
                // Display the total number of all labs
                return types.reduce((acc, type) => acc + type.total, 0); // Sum of all labs
              }
            }
          }
        }
      },
      labels: labels, // Set the labels dynamically (types)
      tooltip: {
        enabled: true,
        y: {
          formatter: function(val, opts) {
            const totalLabs = types[opts.seriesIndex].total; // Get the total labs for the hovered type
            return `${totalLabs}`; // Show "Total: 7" when hovering
          }
        }
      },
      colors: ['#00E396', '#FEB019', '#FF4560', '#775DD0'], // Custom colors for different types
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
                    @foreach ($types as $type)
                        <div class="col-6">
                            <h6>{{ $type['type'] }} Labs</h6>
                            <p class="h4">{{ $type['available'] }} / {{ $type['total'] }} Available</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
