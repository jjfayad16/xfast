jQuery(document).ready(function(){
	
	jQuery(function() {
		jQuery("#dt_nascimento2").datepicker({
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
	
	jQuery('#form2_salvar').validate({
		rules: {
			no_email2	:{required: true, email: true, remote: './locador/validaemail2/id_cliente/'+jQuery("#id_cliente").val()}
		},
		submitHandler: function(form){  
			var dados = jQuery(form).serialize();  
			var idcliente = jQuery("#id_cliente").val();
			jQuery.post('./locador/salvarconjuge/id_cliente/'+jQuery("#id_cliente").val(), dados)
			.success(function(dados) {
				data = jQuery.parseJSON(dados);
				if (data.idc)
				tab2('/locador/editconjuge/id_cliente/'+data.idc);
				mensagem(data.mensagem);
			}, "json")
			.error(function() {  })
			.complete(function() { });  
						 
			return false;  
		}  
	});
					
	jQuery("#nu_cpfcnpj2").rules("add", {
		required: true, cpfcnpj:true, remote: './locador/validacpfcnpj2/id_cliente/'+jQuery("#id_cliente").val()
	});
								
	jQuery('#buscacpf2').click(function() {
		if (jQuery('#nu_cpfcnpj2').valid() == '1') {
			jQuery.post('./locador/existecliente/nu_cpfcnpj2/'+jQuery('#nu_cpfcnpj2').val())
			.success(function(dados) { 
						data = jQuery.parseJSON(dados);
						mensagem(data.mensagem);
						if (data.cpf == 1)
						tab2('/locador/editconjuge/nu_cpfcnpj2/'+jQuery('#nu_cpfcnpj2').val());
			})	
		};
	});
});

jQuery(function() {
			jQuery('input[name="dt_nascimento2"]').mask('99/99/9999');
			jQuery('input[name="dt_emissao2"]').mask('99/99/9999');
			jQuery('input[name="nu_telcomercial2"]').mask('(99) 9999-9999');
			jQuery('input[name="nu_celular2"]').mask('(99) 9999-9999');
			jQuery('input[name="nu_telres2"]').mask('(99) 9999-9999');
});