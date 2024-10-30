var kfframe;
jQuery(function($){
      jQuery(document).on('click', 'input.select-imgKW', function( event ){
			var $this = $(this);
			event.preventDefault();
			var TAGESMENUEImage = wp.media.controller.Library.extend({
				defaults :  _.defaults({
						id:        'katalogportal-insert-image',
						title:      $this.data( 'uploader_title' ),
						allowLocalEdits: false,
						displaySettings: true,
						displayUserSettings: false,
						multiple : false,
						library: wp.media.query( { type: 'image' } )
				  }, wp.media.controller.Library.prototype.defaults )
			});
			kfframe = wp.media.frames.kfframe = wp.media({
			  button: {
				text: jQuery( this ).data( 'uploader_button_text' )
			  },
			  state : 'katalogportal-insert-image',
				  states : [
					  new TAGESMENUEImage()
				  ],
			  multiple: false  
			});

			kfframe.on( 'select', function() {

			  var state = kfframe.state('katalogportal-insert-image');
			  var selection = state.get('selection');
			  var display = state.display( selection.first() ).toJSON();
			  var obj_attachment = selection.first().toJSON();
			  display = wp.media.string.props( display, obj_attachment );

			  var image_field = $this.siblings('.img');
			  var imgurl = display.src;

			  image_field.val(imgurl);
			  image_field.trigger('change');
			  var image_preview_wrap = $this.siblings('.katalogportal-preview-wrap');
			  image_preview_wrap.show();
			  image_preview_wrap.find('img').attr('src',imgurl);
			});
			kfframe.open();
      });
});
