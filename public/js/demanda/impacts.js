jQuery(document).ready(function(){
			
			jQuery('.example').dataTable({
				"oLanguage": {
					"sProcessing":   "Processando...",
					"sLengthMenu":   "Mostrar _MENU_ registros",
					"sZeroRecords":  "N&atilde;o foram encontrados resultados",
					"sInfo":         "Mostrando _START_ de _END_. Total de _TOTAL_ registros",
					"sInfoEmpty":    "Mostrando 0 de 0 registros",
					"sInfoFiltered": "(filtrado de _MAX_ registros no total)",
					"sInfoPostFix":  "",
					"sSearch":       "Buscar:",
					"sUrl":          "",
					"oPaginate": {
						"sFirst":    "Primeiro",
						"sPrevious": "Anterior",
						"sNext":     "Seguinte",
						"sLast":     "&Uacute;ltimo"
					}
				},
				"iDisplayLength": 5,
				"bLengthChange": false,
				"aaSorting": [[ 3, "desc" ]],
				"bJQueryUI": true,         
				"sPaginationType": "full_numbers"    
			}); 
		
});