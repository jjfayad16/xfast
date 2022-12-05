jQuery(document).ready(function(){
    var base = jQuery('#base').val();
    function valida(){
        var idsindec = jQuery('#idsindec').val();
        jQuery.get(base+'/index/valida/id/'+idsindec, function( data ) {
            dados = jQuery.parseJSON(data);
            //alert(dados.valida);
            var id =  dados.id;
                //alert(id);
            jQuery("#conteudo").load(base+'/index/index/id/'+id);
        });
    }
    //jQuery("#conteudo").load(base+'/index');
    valida();
                     
			
});

function submitSindec() {
    var base = jQuery('#base').val();
    jQuery.post(base+'/index/index2/CaptchaStr/'+jQuery('#CaptchaStr').val()+'/idbase/'+jQuery('#idbase').val())
    .success(function(data) { 
            if (data) {
                dados = jQuery.parseJSON(data);
                if (dados.erro == 'erro') {
                    mensagem('Captcha inserido incorreto!');
                } else if (dados.finalizado == '1') {
                    mensagem('Extração Finalizada!');
                    exit;
                } else {
                    jQuery('#idsindec').val(dados.id);
                    mensagem('Extração concluída!');
                }
                jQuery("#conteudo").load(base+'/index/extrair2');
            }
    }, 'json')
    .error(function() {  })
    .complete(function() { });  
}