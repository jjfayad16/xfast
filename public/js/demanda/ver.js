jQuery(document).ready(function(){

			jQuery("#btnCancelar").click(function () {
				acao('/locador');
			});
			
			jQuery('ul.tabs').each(function(){ // para o tab do editar
				// For each set of tabs, we want to keep track of
				// which tab is active and it's associated content
				var $links = jQuery(this).find('a');
				var $active;
				var	$content;
				
				// Use the first link as the initial active tab
				$active = $links.first().addClass('active');
				
				jQuery('#tab2').hide();
				jQuery(".tab1").click(function (e) {
					// switch all tabs off
					jQuery(".active").removeClass("active");
					jQuery('#tab2').hide();
					
					jQuery(this).addClass("active");
					jQuery('#tab1').show();
					
					e.preventDefault();
				}); // fecha click
				jQuery(".tab2").click(function (e) {
					// switch all tabs off
					jQuery(".active").removeClass("active");
					jQuery('#tab1').hide();
					
					jQuery(this).addClass("active");
					jQuery('#tab2').show();
					
					e.preventDefault();
				}); // fecha click
			});
			
			jQuery('#conjuge').hide();
			jQuery('#estadocivil').change(function () {
				var $estcivil = document.form_salvar.fk_id_estadocivil.value;
				if ($estcivil == 2) {
					jQuery('#conjuge').show();
				} else {
					jQuery('#conjuge').hide();
				}
			});
});