$(document).ready(function () {
    const config = $('#accounting-dashboard').data('config');

    function buildBarChartOptions(data, categories) {
        return {
            series: [
                {
                    name: 'Facturas',
                    data: data,
                },
            ],
            chart: {
                toolbar: {
                    show: false,
                },
                height: 260,
                type: 'bar',
                fontFamily: "Plus Jakarta Sans', sans-serif",
                foreColor: '#000',
            },
            colors: ['#000', '#008bce', '#000', '#4f8ac8', '#000', '#4f8ac8'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '45%',
                    distributed: true,
                    endingShape: 'rounded',
                },
            },
            dataLabels: {
                enabled: false,
            },
            legend: {
                show: false,
            },
            grid: {
                yaxis: {
                    lines: {
                        show: false,
                    },
                },
                xaxis: {
                    lines: {
                        show: false,
                    },
                },
            },
            xaxis: {
                categories: categories,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
            },
            yaxis: {
                labels: {
                    show: false,
                },
            },
            tooltip: {
                theme: 'dark',
            },
        };
    }

    const invoices = new ApexCharts(
        document.querySelector('#invoices'),
        buildBarChartOptions(config.monthValues, config.monthNames)
    );
    invoices.render();

    const orders = new ApexCharts(
        document.querySelector('#invoiceList'),
        buildBarChartOptions(config.yearValues, config.yearNames)
    );
    orders.render();
});
