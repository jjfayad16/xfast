jQuery(document).ready(function(){
			
			jQuery(function() {
					jQuery("#prazo").datepicker({
						changeMonth: true,
						changeYear: true,
						showOn: "button",
						showAnim: "slideDown",
						minDate: new Date(),
						yearRange: "2012:2015",
						dayNamesMin: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "S&aacute;b"],
						monthNamesShort: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
						buttonImage: "../public/images/calendar.gif",
						buttonImageOnly: true,
						dateFormat: "dd/mm/yy"
					})
			});
                        
            jQuery('#form3_salvar').validate({
				submitHandler: function(form){ 
                                        jQuery('#mask').hide();
                                        jQuery('.window').hide();
					var dados = jQuery(form).serialize();  
					jQuery.post('/demanda/salvar', dados)
					.success(function(dados) { 
						if (dados);
                        acao('/demanda/listar');
					}, 'json')
					.error(function() {  })
					.complete(function() { });  
	  
					return false;  
				}  
			});
			
			jQuery('#form_salvar').validate({
				submitHandler: function(form){ 
                                        jQuery('#mask').hide();
                                        jQuery('.window').hide();
					var dados = jQuery(form).serialize();  
					jQuery.post('/demanda/salvarandamento', dados)
					.success(function(dados) { 
						if (dados);
						mensagem(dados);
                        acao('/demanda/listar');
					}, 'json')
					.error(function() {  })
					.complete(function() { });  
	  
					return false;  
				}  
			});
			
		jQuery('#btnFechar').click(function() {
			jQuery('#mask').hide();
			jQuery('#modal').hide();
		});
});

jQuery(function() {
			jQuery('input[name="prazo"]').mask('99/99/9999');
			jQuery('input[name="dt_fim"]').mask('99/99/9999');
			jQuery('input[name="nu_cep"]').mask('99.999-999');
			jQuery('input[name="nu_telcomercial"]').mask('(99) 9999-9999');
			jQuery('input[name="nu_celular"]').mask('(99) 9999-9999');
			jQuery('input[name="nu_telres"]').mask('(99) 9999-9999');
});