jQuery("input").change(function(){
   var $input = jQuery(this);
   var href = 'javascript:acao(\'/usuario/edit/id_usuario/'+$input.prop('value')+'\')';
   var href2 = 'javascript:apaga(\'/usuario/excluir/id_usuario/'+$input.prop('value')+'\')';
   var href3 = 'javascript:acao(\'/usuario/ver/id_usuario/'+$input.prop('value')+'\')';
   jQuery('#edit').prop("href", href);
   jQuery('#excluir').prop("href", href2);
   jQuery('#ver').prop("href", href3);
});		 
function executa(url){
	jQuery('#mensagem').slideUp();
	jQuery.post('.'+url)
	.success(function(dados) { 
		acao('/usuario');
		mensagem(dados);
	})
	.error(function() {  })
	.complete(function() { });		 
};