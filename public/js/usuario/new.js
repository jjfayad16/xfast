jQuery(document).ready(function(){
		jQuery('ul.tabs').each(function(){ // para o tab do editar
				// For each set of tabs, we want to keep track of
				// which tab is active and it's associated content
				var $active, $content, $links = jQuery(this).find('a');
	
				// Use the first link as the initial active tab
				$active = $links.first().addClass('active');
		});   
        jQuery('#form_salvar').validate({
			rules: {
				no_login:{required: true, remote: './usuario/validalogin'},
				no_email:{required: true, email: true, remote: './usuario/validaemail'}
			},
			submitHandler: function(form){  
                jQuery('input#submit').attr("disabled", true);
				var dados = jQuery(form).serialize();  
                jQuery.post('./usuario/salvar', dados)
				.success(function(dados) { 
					acao('/usuario');
					mensagem(dados);
				})
				.error(function() {  })
				.complete(function() { });  
  
                return false;  
            }  
        });  
});