jQuery(document).ready(function(){
			
			jQuery('#example2').dataTable({
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
				"iDisplayLength": 3,
				"bLengthChange": false,
				"aaSorting": [[ 4, "desc" ],[3, "asc"]],
				"bJQueryUI": true,         
				"sPaginationType": "full_numbers"    
			}); 
			
				jQuery('#example3').dataTable({
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
					"iDisplayLength": 3,
					"bLengthChange": false,
					"aaSorting": [[ 4, "desc" ],[3, "asc"]],
					"bJQueryUI": true,         
					"sPaginationType": "full_numbers"    
				}).makeEditable({
                       	sUpdateURL: "/demanda/alteraprioridade",
						"aoColumns": [
                    				null,null,
									{
                						indicator: 'Alterando titulo...',
                						tooltip: 'Clique para alterar!',
                						onblur: 'cancel',
                						submit: 'Ok',
                						loadtype: 'POST',
                						sUpdateURL: "/demanda/alteratitulo"
                    				},
									{
                						indicator: 'Alterando prioridade...',
                						tooltip: 'Clique para alterar!',
                						type: 'select',
                						onblur: 'cancel',
                						submit: 'Ok',
                						data: "{'':'Selecione...','1':'1 - Alta', '2':'2 - M&eacute;dia', '3':'3 - Baixa'}",
                						loadtype: 'POST',
                						sUpdateURL: "/demanda/alteraprioridade"
                    				},
									{
                						indicator: 'Alterando data...',
                						tooltip: 'Clique para alterar!',
                						type: 'datepicker',
                						onblur: 'cancel',
                						submit: 'Ok',
                						loadtype: 'POST',
                						sUpdateURL: "/demanda/alteradata"
                    				},null
									]
				}); 
				
		jQuery('#btnFechar').click(function() {
				jQuery('#mask').hide();
				jQuery('#modal').hide();
		});
});