$(document).ready(function () {
    const config = $('#accounting-dashboard').data('config');

    const customers = {
        chart: {
            id: 'sparkline3',
            type: 'area',
            fontFamily: "Plus Jakarta Sans', sans-serif",
            foreColor: '#008bce',
            height: 60,
            sparkline: {
                enabled: true,
            },
            group: 'sparklines',
        },
        series: [
            {
                name: 'Clientes',
                color: '#000',
                data: [30, 25, 35, 20, 30, 40],
            },
        ],
        stroke: {
            curve: 'smooth',
            width: 2,
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 0,
                inverseColors: false,
                opacityFrom: 0.12,
                opacityTo: 0,
                stops: [20, 180],
            },
        },
        markers: {
            size: 0,
        },
        tooltip: {
            theme: 'dark',
            fixed: {
                enabled: true,
                position: 'right',
            },
            x: {
                show: false,
            },
        },
    };

    new ApexCharts(document.querySelector('#customers1'), customers).render();
    new ApexCharts(document.querySelector('#customers2'), customers).render();
    new ApexCharts(document.querySelector('#customers3'), customers).render();
    new ApexCharts(document.querySelector('#customers4'), customers).render();

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
