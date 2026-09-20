$('.datepicker').datepicker({
    prevText: '<Ant',
    nextText: 'Sig>',
    monthNames: [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ],
    monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    changeMonth: true,
    changeYear: true,
    format: 'dd-mm-yyyy',
    minDate: '0',
});
