jQuery("input").change(function(){
   var $input = jQuery(this);
   var href = 'javascript:acao(\'/locador/edit/id_cliente/'+$input.prop('value')+'\')';
   var href2 = 'javascript:apaga(\'/locador/excluir/id_cliente/'+$input.prop('value')+'\')';
   var href3 = 'javascript:acao(\'/locador/ver/id_cliente/'+$input.prop('value')+'\')';
   jQuery('#edit').prop("href", href);
   jQuery('#excluir').prop("href", href2);
   jQuery('#ver').prop("href", href3);
});		 
function executa(url){
	jQuery('#mensagem').slideUp();
	jQuery.post('.'+url)
	.success(function(dados) { 
		acao('/locador');
		mensagem(dados);
	})
	.error(function() {  })
	.complete(function() { });		 
};