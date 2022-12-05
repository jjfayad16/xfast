jQuery(document).ready(function(){
			
			if (document.form_salvar.fk_id_estadocivil.value != 2) {
				jQuery('#conjuge').hide();
			}
			if (document.form_salvar.id_cliente.value == '') {
				jQuery('#imovel').hide();
			}
			jQuery('#estadocivil').change(function () {
				var $estcivil = document.form_salvar.fk_id_estadocivil.value;
				if ($estcivil == 2) {
					jQuery('#conjuge').show();
				} else {
					jQuery('#conjuge').hide();
				}
			});
			
			jQuery(function() {
					jQuery("#dt_nascimento,#dt_emissao").datepicker({
						changeMonth: true,
						changeYear: true,
						showOn: "button",
						showAnim: "slideDown",
						yearRange: "1920:2012",
						dayNamesMin: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "S&aacute;b"],
						monthNamesShort: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
						buttonImage: "./public/images/calendar.gif",
						buttonImageOnly: true,
						dateFormat: "dd/mm/yy"
					})
			});
			
			jQuery('#form_salvar').validate({
				rules: {
					no_email	:{required: true, email: true, remote: './locador/validaemail/id_cliente/'+jQuery('#id_cliente').val()}
				},
				submitHandler: function(form){  
					var dados = jQuery(form).serialize();  
					jQuery.post('./locador/salvar', dados)
					.success(function(dados) { 
						data = jQuery.parseJSON(dados);
						mensagem(data.mensagem);
						if (data.idc) {
							jQuery('#id_cliente').val(data.idc);
							jQuery('#imovel').show();
						}
					})
					.error(function() {  })
					.complete(function() { });  
	  
					return false;  
				}  
			}); 
		
		jQuery("#nu_cpfcnpj").rules("add", {
			required: true, cpfcnpj: true, remote: './locador/validacpfcnpj/id_cliente/'+jQuery('#id_cliente').val()
		});
			
		jQuery('#buscacpf').click(function() {
			if (jQuery('#nu_cpfcnpj').valid() == '1') {
				jQuery.post('./locador/existecliente/nu_cpfcnpj2/'+jQuery('#nu_cpfcnpj').val())
				.success(function(dados) { 
							data = jQuery.parseJSON(dados);
							if (data.cpf == 1)	tab1('/locador/editlocador/nu_cpfcnpj/'+jQuery('#nu_cpfcnpj').val());
							mensagem(data.mensagem);
				})	
			};
		});
								
});

jQuery(function() {
			jQuery('input[name="dt_nascimento"]').mask('99/99/9999');
			jQuery('input[name="dt_emissao"]').mask('99/99/9999');
			jQuery('input[name="nu_cep"]').mask('99.999-999');
			jQuery('input[name="nu_telcomercial"]').mask('(99) 9999-9999');
			jQuery('input[name="nu_celular"]').mask('(99) 9999-9999');
			jQuery('input[name="nu_telres"]').mask('(99) 9999-9999');
});