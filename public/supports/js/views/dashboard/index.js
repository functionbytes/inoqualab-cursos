$(document).ready(function () {

    var config = $('#dashboard-content').data('config') || {};

    // =====================================
    // Customers
    // =====================================
    var customers = {
        chart: {
            id: "sparkline3",
            type: "area",
            fontFamily: "Plus Jakarta Sans', sans-serif",
            foreColor: "#008bce",
            height: 60,
            sparkline: {
                enabled: true,
            },
            group: "sparklines",
        },
        series: [
            {
                name: "Clientes",
                color: "#000",
                data: [30, 25, 35, 20, 30, 40],
            },
        ],
        stroke: {
            curve: "smooth",
            width: 2,
        },
        fill: {
            type: "gradient",
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
            theme: "dark",
            fixed: {
                enabled: true,
                position: "right",
            },
            x: {
                show: false,
            },
        },
    };
    new ApexCharts(document.querySelector('.customers'), customers).render();
    new ApexCharts(document.querySelector('.customers1'), customers).render();
    new ApexCharts(document.querySelector('.customers2'), customers).render();
    new ApexCharts(document.querySelector('.customers3'), customers).render();
    new ApexCharts(document.querySelector('.customers4'), customers).render();
    new ApexCharts(document.querySelector('.customers5'), customers).render();
    new ApexCharts(document.querySelector('.customers6'), customers).render();

    // =====================================
    // Profit
    // =====================================
    var chart = {
        series: [
            {
                name: "Ganancias este mes",
                data: config.numberEarnings,
            },
            {
                name: "Gasto este mes",
                data: config.numberEarnings,
            },
        ],
        chart: {
            toolbar: {
                show: false,
            },
            type: "bar",
            fontFamily: "Plus Jakarta Sans', sans-serif",
            foreColor: "#008bce",
            height: 320,
            stacked: true,
        },
        colors: ["#000", "#008bce"],
        plotOptions: {
            bar: {
                horizontal: false,
                barHeight: "60%",
                columnWidth: "20%",
                borderRadius: [6],
                borderRadiusApplication: 'end',
                borderRadiusWhenStacked: 'all'
            },
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        grid: {
            borderColor: "rgba(0,0,0,0.1)",
            strokeDashArray: 3,
            xaxis: {
                lines: {
                    show: false,
                },
            },
        },
        yaxis: {
            min: -5,
            max: 5,
            title: {},
        },
        xaxis: {
            axisBorder: {
                show: false,
            },
            categories: config.monthEarnings,
        },
        // Nota: sobrescribe el "yaxis" anterior (comportamiento original conservado).
        yaxis: {
            tickAmount: 4,
        },
        tooltip: {
            theme: "dark",
        },
    };

    new ApexCharts(document.querySelector("#charts"), chart).render();

    var order = {
        series: [
            {
                name: "Ordenes",
                data: config.viewOrders,
            },
        ],
        chart: {
            toolbar: {
                show: false,
            },
            height: 260,
            type: "bar",
            fontFamily: "Plus Jakarta Sans', sans-serif",
            foreColor: "#000",
        },
        colors: ["#000", "#008bce", "#000", "#4f8ac8", "#000", "#4f8ac8"],
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: "45%",
                distributed: true,
                endingShape: "rounded",
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
            categories: [["Enero"], ["Febrero"], ["Marzo"], ["Abril"], ["Mayo"], ["Junio"], ["Julio"], ["Agosto"], ["Seoptiembre"], ["Octubre"], ["Noviembre"], ["Diciembre"]],
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
            theme: "dark",
        },
    };

    new ApexCharts(document.querySelector("#orders"), order).render();
});
