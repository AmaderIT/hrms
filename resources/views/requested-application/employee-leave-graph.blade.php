<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<figure class="highcharts-figure">
    <div id="container"></div>
</figure>


<script>
    let chart = Highcharts.chart('container', {
        xAxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },

        series: null,
        exporting: {
            enabled: false
        },
        chart: {
            type: 'column',
            animation: Highcharts.svg, // don't animate in old IE
        },


        title: {
            text: 'Employee Leave Graph'
        },
        credits: {
            enabled: false
        },
    });

    function getEmployeeLeaveGraphData() {
        $.ajax({
            url: '{{route('apply-for-leave.get-employee-leave-graph')}}',
            data: {
                user_id: $('#user_id').val()
            },
            success: function (res) {
                $.each(res.data, function (x, y) {
                    chart.addSeries(
                        {
                            name: y.name, data: y.data,
                        }
                    )
                })
            },
            error: function (err) {
                console.log(err)
            }
        })
    }


</script>
