jQuery(document).ready(function(){
    
    
    //jQuery("#conteudo").load(base+'/index');
    extrai();
    //setTimeout("extrai(2)",2000);
    //setTimeout("extrai(3)",4000);
    
    //setTimeOut(extrai2, 1000);
                     
			
});

function extrai(){
    tipoL = '#extrai1';
    var base = jQuery('#base').val();
    var idsindec = jQuery('#idsindec').val();
        jQuery.get(base+'/extracao/valida/id/'+idsindec, function( data ) {
            dados = jQuery.parseJSON(data);
            //alert(dados.valida);
            var id =  dados.id;
             if (dados.finalizado == '1') {
                jQuery(tipoL).load(base+'/extracao/concluido');
            } else {   //alert(id);
                jQuery(tipoL).load(base+'/extracao/processa/id/'+id);
            }
        });
}


function submitSindec() {
    var base = jQuery('#base').val();
    jQuery.post(base+'extracao/index2/CaptchaStr/'+jQuery('#CaptchaStr').val()+'/idbase/'+jQuery('#idbase').val())
    .success(function(data) { 
            if (data) {
                dados = jQuery.parseJSON(data);
                if (dados.erro == 'erro') {
                    mensagem('Captcha inserido incorreto!');
                } else if (dados.finalizado == '1') {
                    mensagem('Extração Finalizada!');
                } else {
                    jQuery('#idsindec').val(dados.id);
                    mensagem('Extração concluída!');
                    alert(dados.valida);
                }
                jQuery("#extrai1").load(base+'/extracao/extrair2');
            }
    }, 'json')
    .error(function() {  })
    .complete(function() { });  
}