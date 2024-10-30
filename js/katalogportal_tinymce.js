(function() {
	tinymce.create('tinymce.plugins.katalogportal', {
		init : function(ed, url) {
			jQuery( '#insert_katalogportal_pdf' ).live( "click", function( e ) {
				e.preventDefault();

				ed.execCommand(
					'mceInsertContent',
					false,
					katalogportal_create_shortcode(url)
				);
				
				tb_remove();
			} );
			ed.addButton('katalogportal', {
				title : 'Katalogportal PDF Sync',
				image : url+'/../images/katalogportal_logo_small.png',
				onclick : function() {
					tb_show('Katalogportal PDF Sync', ajaxurl+'?action=katalogportal_shortcodePrinter&width=600&height=700');
				}
			});
		},
	});
	tinymce.PluginManager.add('katalogportal', tinymce.plugins.katalogportal);
})();

function katalogportal_create_shortcode(url) {
	var inputs = jQuery('#katalogportal_shortcode_generator').serializeArray();
	var shortcode = '';
	var katalogportal_userid="";
	var katalogportal_pdf="";
	var katalogportal_thumbnail = url + '/../images/katalogportal_logo.png';
	for( var a in inputs ) {
		if( inputs[a].name == "katalogportal_key") katalogportal_userid = inputs[a].value;
		if( inputs[a].name == "katalogportal_pdf_id") katalogportal_pdf = inputs[a].value;
		if( inputs[a].name == "katalogportal_thumbnail") { 
			if (inputs[a].value) katalogportal_thumbnail = inputs[a].value;
		}
		if( inputs[a].value == "" ) continue;		
	}
	shortcode += '<a class="iframe first last item" href="http://www.katalogportal.ch/book.aspx?id='+katalogportal_userid+'&kn='+katalogportal_pdf+'"><img src="'+katalogportal_thumbnail+'" style="border: 0; width: 100px; height: auto;"><br>'+katalogportal_pdf+'</a>';
	
	return shortcode;
}