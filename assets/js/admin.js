let alm_modal = ( show = true ) => {
	if(show) {
		jQuery('#autoload-manager-modal').show();
	}
	else {
		jQuery('#autoload-manager-modal').hide();
	}
}

jQuery(function($){
	
	$('#autoload-manager_report-copy').click(function(e) {
		e.preventDefault();
		$('#autoload-manager_tools-report').select();

		try {
			if( document.execCommand('copy') ){
				$(this).html('<span class="dashicons dashicons-saved"></span>');
			}
		} catch (err) {
			console.log('Oops, unable to copy!');
		}
	});
})