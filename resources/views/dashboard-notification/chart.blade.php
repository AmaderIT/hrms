<div class="col-xxl-{{$card_width ?? ''}}">
    <figure class="highcharts-figure">
        <div id="container"></div>
        <p class="highcharts-description">

        </p>
    </figure>
</div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script>
    function getChartData(startDate,endDate){
        $.ajax({
            url: '{{route("dashboard-notification.get-data")}}',
            type: 'GET',
            data: {
                card_title: '{{$card_title ?? ''}}',
                card_key: '{{$card_key ?? ''}}',
                permission_key: '{{$permission_key?? ''}}',
                room: '{{$room?? ''}}',
                empID: $('#leave-calendar').attr('data-leave-emp-id'),
                start: startDate,
                end: endDate,
                type: 'chart'
            },
            success: function (res) {
                let chartDates = [];
                jQuery.each( res, function( key, val ) {
                    if(key != 0){
                        chartDates.push([key,val]);
                    }
                });
                showCharts(JSON.stringify(chartDates));
            },
            error: function (err) {
                console.log(err)
            }
        })
    }
    function showCharts(chartDates){
        var labels123 = ['0', '10', '20', '30', '40', '50', '60', '70','80','90','100'];
        Highcharts.chart('container', {
            chart: {
                type: 'column',
                /*events: {
                    load() {
                        const chart = this;
                        chart.showLoading('Loading ...');
                        setTimeout(function() {
                            chart.hideLoading();
                            chart.series[0].setData(JSON.parse(chartDates))
                        }, 700);
                    }
                }*/
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            credits:
                {
                    enabled:false,
                    href:''
                },
            exporting:
                {
                  enabled:false
                },
            xAxis: {
                type: 'category',
                labels: {
                    rotation: -75,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: ''
                },
                gridLineWidth: 0,
                allowDecimals:false,
                /*labels: {
                    formatter: function() {
                        return labels123[this.value];
                    }
                },*/
                labels: {
                    enabled: false
                },
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: '<b>{point.y:.0f}</b>'
            },
            series: [{
                name: 'Leave Calendar',
                data:JSON.parse(chartDates),
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#FFFFFF',
                    align: 'right',
                    format: '{point.y:.0f}', // one decimal
                    y: 10, // 10 pixels down from the top
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });
    }

</script>
