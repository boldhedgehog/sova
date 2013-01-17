{if isset($service['daily_chart']) && is_array($service['daily_chart']) && $service['daily_chart']}
<script type="text/javascript">
    //<![CDATA[
    // Load the Visualization API and the piechart package.
    google.load('visualization', '1.0', { 'packages' : ['corechart'], 'language' : 'ua' });

    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart);

    // Callback that creates and populates a data table,
    // instantiates the pie chart, passes in the data and
    // draws it.
    function drawChart() {
        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('number', 'Година', 'hour');
        data.addColumn('number', 'OK', 'OK');
        data.addColumn('number', 'WARNING', 'WARNING');
        data.addColumn('number', 'CRITICAL', 'CRITICAL');
        data.addColumn('number', 'UKNOWN', 'UKNOWN');
        //data.addColumn('number', 'Всього', 'total');

        data.addRows([
            {foreach $service['daily_chart'] as $values}
                [{implode(',', $values)}],
            {/foreach}
        ]);

        var formatter = new google.visualization.NumberFormat({ pattern: '00' });
        formatter.format(data, 0);

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(
                data,
                {
                    title: 'Статусы по годинах',
                    colors: ['#3f0', '#ff0', '#f00', '#aaa'],
                    legendTextStyle: { color:'#000' },
                    bar: { groupWidth: '80%' },
                    chartArea: {
                        left: 0,
                        top: 40,
                        height: '85%',
                        width: '100%'
                    },
                    legend: {
                        position: 'in',
                        alignment: 'center'
                    },
                    hAxis: {
                        title: '',
                        titleTextStyle: { color: '#5c5c5c' },
                        titlePosition: 'in',
                        format: '00',
                        gridlines: { count: 24 }
                    },
                    vAxis: {
                        logScale: false,
                        textPosition: 'in'
                    },
                    width: 750,
                    height: 750/3*2,
                    isStacked: true
                }
        );
    }
    //]]>
</script>
{/if}
