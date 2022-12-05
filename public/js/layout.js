jQuery(document).ready(function(){
			jQuery("#loading").bind("ajaxStart", function(){
				jQuery('#controlaEventos').unbind('click');
				jQuery(this).show();
				}).bind("ajaxStop", function(){
				jQuery(this).fadeOut(500);
				jQuery('#controlaEventos').unbind('click');
			});
});

function mensagem(valor){
        jQuery('div.mensagem').html(valor);

        jQuery('#mensagem').slideDown(1000);
        jQuery('#mensagem').delay(6000).slideUp(1000);
}

function acao(url) {
    jQuery('#conteudo').load(url);
}