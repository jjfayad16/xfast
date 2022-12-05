	jQuery(document).ready(function() {
		jQuery('#topo,#menu,#alertas, table#tit').corner('3px'); // para os cantos arredondados
	
		jQuery('#example').dataTable({
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
			"bLengthChange": false,
			"aaSorting": [[ 1, "asc" ]],
			"bJQueryUI": true,         
			"sPaginationType": "full_numbers"    
		}); 
	});
	function apaga(url){
	jQuery('div.mensagem').html('<p>Confirma exclus&atilde;o do item selecionado: <a href=\'javascript:executa(\"'+url+'\")\' id=\'confirmaExclusao\'>SIM</a> ou <a href=\'javascript:cancela()\' id=\'naoExcluir\'>N&Atilde;O</a>?</p>');
	jQuery('#mensagem').slideDown(1000);
        };
        
        function existecpf(url){
        jQuery('div.mensagem').html('<p>CPF/CNPJ j&aacute; cadastrado em outro perfil, carregar dados do cliente: <a href=\'javascript:carregacpfcliente(\"'+url+'\")\' id=\'confirmaExclusao\'>SIM</a> ou <a href=\'javascript:cancela()\' id=\'naoExcluir\'>N&Atilde;O</a>?</p>');
	jQuery('#mensagem').slideDown(1000);
        };