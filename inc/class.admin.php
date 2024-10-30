<?php
class katalogportal_Admin {
	function katalogportal_Admin() {
		global $pagenow;
		add_filter( 'attachment_fields_to_edit', array( &$this, 'insertkatalogportalButton' ), 10, 2 );
		add_filter( 'add_attachment', array( &$this, 'add_filter_upload_image' ), 10, 2);
		
		add_action( 'admin_menu', array( &$this, 'addPluginMenu' ) );
		add_action( 'admin_init', array( &$this, 'init' ) );
		// Add the tinyMCE button
		add_action( 'admin_init', array( &$this, 'katalogportaladdButtons' ) );
		add_action( 'wp_ajax_katalogportal_shortcodePrinter', array( &$this, 'katalogportal_popup_shortcode' ) );

		add_action('delete_attachment', array( &$this, 'attachment_manipulation') );

		add_filter('manage_media_columns', array( &$this, 'add_media_columns'));
		add_action('manage_media_custom_column', array( &$this, 'mte_custom_media_column_content'), 10, 2);
	}
	
	function add_media_columns( $columns ) {
		$columns['katalogportal'] = 'EPaper';
		return $columns;
	}
	
	function mte_custom_media_column_content( $column_name, $id ) {
		switch ( $column_name ) {
			case 'katalogportal' :		
				$kat_url = get_post_meta( get_the_ID(), 'kat_url', true );
				if (!empty($kat_url)) { 						
					wp_enqueue_script( 'tagesmenue-colorbox-script', katalogportal_URL .'/js/colorbox/jquery.colorbox-min.js', array('jquery'), '1.6.4' );
					wp_enqueue_style('tagesmenue-colorbox-style', katalogportal_URL .'/js/colorbox/css/colorbox.css', array(), '1.6.4', 'all');
					wp_enqueue_script( 'katalogportal-js', katalogportal_URL .'/js/katalogportal.js', array('jquery'), '1.0.0' );
					wp_add_inline_script( 'katalogportal-js', 
						'	jQuery(document).ready(function(){' .
						'		jQuery(".iframe").colorbox({iframe:true, width:"90%", height:"90%"});' .						
						'	});'	
					);					
					echo '<a class="iframe" href="'.$kat_url.'" target="_blank" style="border: 0;">';
					echo '<img src="'.katalogportal_URL.'/images/katalogportal_logo.png" style="width: 80px; height: auto;">';
					echo '</a>';	
				}
			break;
		}
	}	
	
	function attachment_manipulation($id)
	{
		global $katalogportal_options;		
		$kat_url = get_post_meta( get_the_ID(), 'kat_url', true );
		if (!empty($kat_url)) { 
			$client = '';
			$params = '';	
			$file = wp_get_attachment_url( $id);
			$att_guid_arr = explode('/', $file);
			$thepdf = $att_guid_arr[count($att_guid_arr)-1];			
			$thepdf_arr = explode('.', $thepdf);
			if ($thepdf_arr[1] == 'pdf') {
				try {
					$client = new SoapClient("http://www.katalogportal.ch/WebServiceKatalogPortal.asmx?WSDL");
					$params = array( 'user_name'  => $katalogportal_options['katalogportal_username'], 
									'thePDF'=> $thepdf,
									'key'=>$katalogportal_options['katalogportal_key'],
									);
					$result = $client->deleteKatalogWP($params);
				} catch (Exception $e){
					 echo 'Caught exception: ',  $e->getMessage(), "\n"; exit;
				}			
			}
		}
	}
	
	function init() {
		wp_enqueue_script( 'jquery' );
	}

	function addPluginMenu() {
		add_options_page( __('Options for Katalogportal PDF Sync', 'katalogportal'), __('Katalogportal PDF Sync', 'katalogportal'), 'manage_options', 'katalogportal-options', array( &$this, 'displayOptions' ) );
	}

	function displayOptions() {
		global $katalogportal_options;
		if ( isset($_POST['save']) ) {
			check_admin_referer( 'katalogportal-update-options' );
			$new_options = array();

			// Update existing
			foreach( (array) $_POST['katalogportal'] as $key => $value ) {
				$new_options[$key] = stripslashes($value);
			}

			update_option( 'katalogportal_options', $new_options );
			$katalogportal_options = get_option ( 'katalogportal_options' );
		}

		if (isset($_POST['save']) ) {
			echo '<div class="message updated"><p>'.__('Options updated!', 'katalogportal').'</p></div>';
		}

		if ( $katalogportal_options == false ) {
			$katalogportal_options = array();
		}
		?>
		<div class="wrap" id="katalogportal_options" >
			<h2><?php _e('Katalogportal PDF Sync', 'katalogportal'); ?></h2>

			<form method="post" action="#">
				<table class="form-table describe media-upload-form">

					<tr><td colspan="2"><h3><?php _e('Configuration', 'katalogportal'); ?></h3></td></tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="katalogportal[katalogportal_username]"><span class="alignleft"><?php _e('Katalogportal username', 'katalogportal'); ?></span></label></th>
						<td><input id="katalogportal[katalogportal_username]" type="text" style="width: 360px;" class="text" name="katalogportal[katalogportal_username]" value="<?php echo isset( $katalogportal_options['katalogportal_username'] ) ? esc_attr( $katalogportal_options['katalogportal_username'] ) : '' ; ?>" /></a>
						</td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="katalogportal[katalogportal_key]"><span class="alignleft"><?php _e('Katalogportal key', 'katalogportal'); ?></span></label></th>
						<td><input id="katalogportal[katalogportal_key]" type="text" style="width: 360px;" class="text" name="katalogportal[katalogportal_key]" value="<?php echo isset( $katalogportal_options['katalogportal_key'] ) ? esc_attr( $katalogportal_options['katalogportal_key'] ) : '' ; ?>" /></a>
						</td>
					</tr>

					<tr valign="top" class="field">
						<th class="label" scope="row"><label for="katalogportal[katalogportal_userid]"><span class="alignleft"><?php _e('Katalogportal Userid', 'katalogportal'); ?></span></label></th>
						<td><input id="katalogportal[katalogportal_userid]" type="text" style="width: 360px;" class="text" name="katalogportal[katalogportal_userid]" value="<?php echo isset( $katalogportal_options['katalogportal_userid'] ) ? esc_attr( $katalogportal_options['katalogportal_userid'] ) : '' ; ?>" /></a>
						</td>
					</tr>
					
					<tr>
						<td>
							<p class="submit">
								<?php wp_nonce_field( 'katalogportal-update-options'); ?>
								<input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes', 'katalogportal') ?>" />
							</p>
						</td>

				</table>
			</form>
		</div>
		<?php
	}

	function add_filter_upload_image($attachment_id){
		$attachment_meta = wp_prepare_attachment_for_js($attachment_id); 
	}

	
	function insertkatalogportalButton( $form_fields, $attachment ) {
		global $wp_version, $katalogportal_options;

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			if ( !isset( $form_fields ) || empty( $form_fields ) || !isset( $attachment ) || empty( $attachment ) )
				return $form_fields;
		}

		$file = wp_get_attachment_url( $attachment->ID );

		// Only add the extra button if the attachment is a PDF file
		if ( $attachment->post_mime_type != 'application/pdf' )
			return $form_fields;

		// Allow plugin to stop the auto-insertion
		$check = apply_filters( 'insert-katalogportal-button', true, $attachment, $form_fields );
		if ( $check !== true )
			return $form_fields;

		// Check on post meta if the PDF has already been uploaded on Katalogportal
		$katalogportal_pdf_id = get_post_meta( $attachment->ID, 'katalogportal_pdf_id', true );
		$kat_url =  get_post_meta( $attachment->ID, 'kat_url', true ); 
		
		$client = '';
		$params = '';
		if (empty($katalogportal_pdf_id)) {
			$upload_dir = wp_upload_dir();
			$thepdf =  get_post_meta( $attachment->ID, '_wp_attached_file', true ); 
			$thepdf_arr=explode( substr($upload_dir['subdir'],1).'/', $thepdf);
			$thepdf=$thepdf_arr[1];
			
			$att_guid_arr = explode('/', $attachment->guid);
			$thepdf = $att_guid_arr[count($att_guid_arr)-1];
			$kat_url = "";
			try {
					$the_url = $upload_dir['url'];
					$client = new SoapClient("http://www.katalogportal.ch/WebServiceKatalogPortal.asmx?WSDL");
					$params = array( 'user_name'  => $katalogportal_options['katalogportal_username'], 
									'url' => $the_url. '/',
									'thePDF'=> $thepdf,
									'key'=>$katalogportal_options['katalogportal_key'],
									);
					$result = $client->getKatalogWP($params);
					$kat_url = $result->getKatalogWPResult->string[2];
					update_post_meta(  $attachment->ID, 'kat_url', $kat_url );
			} catch (Exception $e){
				 echo 'Caught exception: ',  $e->getMessage(), "\n"; exit;
			}
		}			
		update_post_meta(  $attachment->ID, 'katalogportal_pdf_id', $attachment->ID );				
		$form_fields['kat_url'] = array(
			'show_in_edit' => true,
			'label'        => __( 'Katalog URL', 'katalogportal' ),
			'value'        => $kat_url 
		);
		return $form_fields;
	}

	/*
	 * The content of the javascript popin for the PDF insertion
	 */
	function katalogportal_popup_shortcode(){
		global $katalogportal_options, $wp_styles;

		$pdf_files = new WP_Query( array(
			'post_type'      => 'attachment',
			'posts_per_page' => 100,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => 'katalogportal_pdf_id',
					'value'   => '',
					'compare' => '!='
				)
			)
		) );

		 if ( !$pdf_files->have_posts() ) : ?>
			<p><strong><?php _e("You don't have set your settings correctly, please check Settings -> Katalogportal PDF Sync .", 'katalogportal'); ?></strong></p>
		<?php else : ?>
			<h3 class="media-title"><?php _e('Insert Flipbook', 'katalogportal'); ?></h3>
			<form name="katalogportal_shortcode_generator" id="katalogportal_shortcode_generator" >
				<div id="media-items">
					<div class="media-item media-blank">

						<table class="describe" style="width:100%; "><tbody>
							<tr valign="top" class="field">
								<td>
									<input name="katalogportal_key" type="hidden" id="katalogportal_key" value="<?php echo $katalogportal_options['katalogportal_userid']; ?>">
								</td>
							</tr>
							<tr valign="top" class="field">
								<th class="label" scope="row"><label for="katalogportal_layout"><?php _e('Select EPaper', 'katalogportal'); ?></th>
								<td>
									<select name="katalogportal_pdf_id" id="katalogportal_pdf_id">
										<?php
										while ( $pdf_files->have_posts() ) : $pdf_files->the_post(); ?>
										<?php 
											$kat_url = get_post_meta( get_the_ID(), 'kat_url', true );
											if (!empty($kat_url)) { 
												$filename = basename ( get_attached_file( get_the_ID() ) ); 
											?>
											<option value="<?php echo $filename; ?>"><?php echo substr( get_the_title(), 0, 35 ).' ('.$filename.')'; ?></option>
										<?php } ?>
										<?php endwhile; ?>
									</select>
								</td>
							</tr>
							
							<tr>
								<th>Thumbnail</th>
								<td>
									<?php
											$instance = wp_parse_args( (array) $instance, array(
												'katalogportal_image_url'               => '',
												'katalogportal_image_width'             => '',
												'katalogportal_alt_text'                => '',
												) );
											//echo '<script>var tb = document.getElementById("TB_ajaxContent");tb.setAttribute("style", "");</script>';
												
									?>

											<div>
												  <input type="text" class="img widefat" name="katalogportal_thumbnail" id="katalogportal_thumbnail" value="<?php echo esc_url( $instance['katalogportal_image_url'] ); ?>" /><br />
												  <input type="button" class="select-imgK button button-primary" value="<?php _e( 'Upload', 'katalogportal_katalog_widget' ); ?>" data-uploader_title="<?php _e( 'Select Image', 'katalogportal_katalog_widget' ); ?>" data-uploader_button_text="<?php _e( 'Choose Image', 'katalogportal_katalog_widget' ); ?>" style="margin-top:5px;" />

													<?php
													$full_image_url = '';
													if ( ! empty( $instance['katalogportal_image_url'] ) ) {
														$full_image_url = $instance['katalogportal_image_url'];
													}
													$wrap_style = '';
													if ( empty( $full_image_url ) ) {
														$wrap_style = ' style="display:none;" ';
													}
													?>
												  <div class="katalogportal-preview-wrap" <?php echo $wrap_style; ?>>
													<img src="<?php echo esc_url( $full_image_url ); ?>" alt="<?php _e( 'Preview', 'katalogportal_katalog_widget' ); ?>" style="max-width: 100%;"  />
												  </div><!-- .katalogportal-preview-wrap -->

											</div>
								</td>
							</tr>
							<tr valign="top" class="field">
								<td>
									<input name="insert_katalogportal_pdf" type="submit" class="button-primary" id="insert_katalogportal_pdf" tabindex="5" accesskey="p" value="<?php _e('Insert', 'katalogportal') ?>">
								</td>
							</tr>
						</tbody></table>
					</div>
				</div>
			</form>
		<?php endif; ?>
		<?php exit();
	}

	function katalogportaladdButtons() {
		global $katalogportal_options;
		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return false;

		if (get_user_option('rich_editing') == 'true') {			
			add_filter('mce_external_plugins', array (&$this,'katalogportaladdScriptTinymce' ) );
			add_filter('mce_buttons', array (&$this,'registerTheButton' ) );
		}
	}

	function registerTheButton($buttons) {
		array_push($buttons, "|", "katalogportal");
		return $buttons;
	}

	function katalogportaladdScriptTinymce($plugin_array) {
		$plugin_array['katalogportal'] = katalogportal_URL . '/js/katalogportal_tinymce.js';
		return $plugin_array;
	}
}
