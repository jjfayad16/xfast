jQuery(document).ready(function() {
	jQuery('#topo,#menu,#alertas, table#tit').corner('10px'); // para os cantos arredondados
	
		jQuery('#example').dataTable({                 
			"bLengthChange": false,
			"aaSorting": [[ 2, "desc" ]],
			"bJQueryUI": true,         
			"sPaginationType": "full_numbers"    
		}); 
});