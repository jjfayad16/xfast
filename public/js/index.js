jQuery(document).ready(function(){
    var base = jQuery('#base').val();
    var $i = 1;   
    function valida(){
        jQuery.get(base+'/index/valida/id/1', function( data ) {
            dados = jQuery.parseJSON(data);
            //alert(dados.valida);
            if (dados.valida == 0) {
                //alert(dados.id);
                setTimeout(valida, 10000);
            } else {
                jQuery("#conteudo").load(base+'/index/index/id/'+dados.id);
            }
        });
    }
    jQuery("#conteudo").load(base+'/index');
    valida($i);
                     
			
});