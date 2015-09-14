    <script type="text/javascript">
        //<![CDATA[
        // Load the Visualization API and the timeline package.
        google.load('visualization', '1', { 'packages': ['timeline'], 'language': 'ru' });

        serviceDurationsChart = {
            chart: false,
            chartView: false,
            data: false,

            chartOptions: {
                title: 'Тривалість статусів по годинах',
                legendTextStyle: { color: '#000' },
                colors: [],
                availableColors : ['#3f0', '#ff0', '#f00', '#aaa'],
                bar: { groupWidth: '80%' },
                chartArea: {
                    left: 0,
                    top: 40,
                    height: '85%',
                    width: '100%'
                },
                /*legend: {
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
                },*/
                width: 750,
                height: 750 / 3 * 2,
                isStacked: true
            },

            addChatColumns: function () {
                this.chart.addColumn('string', 'State', 'state');
                /*this.chart.addColumn('string', 'State Label', 'state_label');*/
                this.chart.addColumn('datetime', 'Start', 'Start');
                this.chart.addColumn('datetime', 'End', 'End');
            },

            // Callback that creates and populates a data table,
            // instantiates the pie chart, passes in the data and
            // draws it.
            init: function() {
                // Create the data table.
                this.chart = new google.visualization.DataTable();

                this.addChatColumns();
            },

            setData: function(data) {
                this.data = data;
            },

            draw: function() {
                if (! this.chart) {
                    this.init();
                }

                this.chart.addRows(this.data);

                var formatter = new google.visualization.DateFormat({ pattern: 'dd.MM.yyyy HH:mm:ss' });
                formatter.format(this.chart, 1);
                formatter.format(this.chart, 2);

                this.chartView = new google.visualization.Timeline(document.getElementById('chart_div_durations'));

                {if isset($service['duration_chart']) && is_array($service['duration_chart']) && $service['duration_chart']}
                this.chartView.draw(
                        this.chart,
                        this.chartOptions
                );
                {/if}

            },

            refresh: function(data) {
                var jsonOld = $.parseJSON(this.chart.toJSON());
                jsonOld.rows = [];

                this.data = [];

                var states = data.states, i;
                this.chartOptions.colors = [];

                data = data.data;

                if (!data || !data.length) {
                    $('#chart_div_durations').hide();
                    return;
                } else {
                    $('#chart_div_durations').show();
                }

                for (i=0; i < this.chartOptions.availableColors.length; i++) {
                    if (states.indexOf(i) != -1) {
                        this.chartOptions.colors.push(this.chartOptions.availableColors[i]);
                    }
                }

                for (i=0; i < data.length; i++) {
                    this.data.push(
                            { c : [
                                { v: data[i].state_label },
                                { v: new Date(1000 * data[i].start) },
                                { v: new Date(1000 * data[i].end) }
                            ] }
                    );
                }

                jsonOld.rows = this.data;

                // Create the data table.
                this.chart = new google.visualization.DataTable(jsonOld);

                this.chartView.draw(
                        this.chart,
                        this.chartOptions
                );
            }
        };
        // colors: ['#3f0', '#ff0', '#f00', '#aaa'],

        {if in_array(0, $service['duration_chart_states'])}serviceDurationsChart.chartOptions.colors.push('#3f0');{/if}
        {if in_array(1, $service['duration_chart_states'])}serviceDurationsChart.chartOptions.colors.push('#ff0');{/if}
        {if in_array(2, $service['duration_chart_states'])}serviceDurationsChart.chartOptions.colors.push('#f00');{/if}
        {if in_array(3, $service['duration_chart_states'])}serviceDurationsChart.chartOptions.colors.push('#aaa');{/if}

        serviceDurationsChart.setData([
            {foreach $service['duration_chart'] as $values}
            ['{$values['state_label']}', new Date(1000 * {$values['start']}), new Date(1000 * {$values['end']})],
            {/foreach}
        ]);

        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(function() {
            serviceDurationsChart.draw();
        });

        $(document).ready(function() {
            $('#duration-chart-period').change(function() {
                var select = $(this).prop('disabled', true);
                $('#chart_div_durations').hide();
                $.ajax({
                    type: 'POST',
                    url: BASE_URL + 'service/timeline/id/' + serviceId,
                    data: { duration_period : $(this).val() },
                    dataType: 'json',
                    cache: false
                }).done(function(response) {
                    if ('object' == typeof(response)) {
                        if ('object' == typeof(response.error) && 'string' == typeof(response.error.message)) {
                            alert(response.error.message);
                        }

                        serviceDurationsChart.refresh(response);
                    }
                }).always(function() {
                    select.prop('disabled', false);
                });
            });
        });

        //]]>
    </script>

{if isset($service['duration_period'])}
    <label for="duration-chart-period">Період</label>
    <select name="duration_period" id="duration-chart-period">
    {foreach from=$duration_periods item=value key=label}
        <option value="{$value}" {if $service['duration_period'] eq $value} selected="selected" {/if}>{$label}</option>
    {/foreach}
        <option value="custom">Період</option>
</select>
{/if}
