jQuery(document).ready(function(){
		jQuery('ul.tabs').each(function(){ // para o tab do editar
				// For each set of tabs, we want to keep track of
				// which tab is active and it's associated content
				var $active, $links = jQuery(this).find('a');
	
				// Use the first link as the initial active tab
				$active = $links.first().addClass('active');
		});
});