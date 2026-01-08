$(document).ready(function() {	
	var empr = document.getElementById('empr').value;
	var ejer = document.getElementById('ejer').value;
	var mes = document.getElementById('mes').value;
	var cuenta = document.getElementById('cuenta').value;
	var table = $('#datatable-keytable').DataTable( {
		keys: {
			focus: ':eq(0)'
    	},
		"stateSave": false,
		"searching": true,
		"pageLength": 10,
		"bDeferRender": true,	
		"sPaginationType": "full_numbers",
		"ajax": {
			"url": "busca_asto.php?empr="+empr+"&ejer="+ejer+"&mes="+mes+"&cuenta="+cuenta,
	    	"type": "POST"
		},					
		"columns": [
			{ "data": "asto_cod_modu" },
			{ "data": "asto_fec_asto" },
			{ "data": "asto_cod_asto" },
			{ "data": "asto_ben_asto" },
			{ "data": "asto_det_asto" },
			{ "data": "asto_vat_asto" },
			{ "data": "detalle"}
		],
		
		"oLanguage": {
            "sProcessing":     "Procesando...",
		    "sLengthMenu": 'Mostrar <select>'+ 
		        '<option value="10">10</option>'+
		        '<option value="60">60</option>'+
		        '<option value="90">90</option>'+
		        '<option value="120">120</option>'+
		        '<option value="150">150</option>'+
		        '<option value="-1">Todo</option>'+
		        '</select> registros',    
		    "sZeroRecords":    "No se encontraron resultados",
		    "sEmptyTable":     "Ningún dato disponible en esta tabla",
		    "sInfo":           "Mostrando del (_START_ al _END_) de un total de _TOTAL_ registros",
		    "sInfoEmpty":      "Mostrando del 0 al 0 de un total de 0 registros",
		    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
		    "sInfoPostFix":    "",
		    "sSearch":         "Filtrar:",
		    "sUrl":            "",
		    "sInfoThousands":  ",",
		    "sLoadingRecords": "Por favor espere - cargando...",
		    "oPaginate": {
		        "sFirst":    "Primero",
		        "sLast":     "Último",
		        "sNext":     "Siguiente",
		        "sPrevious": "Anterior"
		    },
		    "oAria": {
		        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
		        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
		    }
        }
	});

});