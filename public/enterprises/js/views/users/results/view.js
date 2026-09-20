$(document).ready(function () {
    var customers = {
        chart: {
            id: 'sparkline3',
            type: 'area',
            fontFamily: "Plus Jakarta Sans', sans-serif",
            foreColor: '#4784d9',
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

    new ApexCharts(document.querySelector('#customers'), customers).render();
    new ApexCharts(document.querySelector('#customers1'), customers).render();
    new ApexCharts(document.querySelector('#customers2'), customers).render();
});
