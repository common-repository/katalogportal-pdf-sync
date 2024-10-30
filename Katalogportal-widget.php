<?php
/**
 * Plugin Name: Katalogportal EPaper widget
 * Plugin URI: http://www.katalogportal.ch
 * Description: Insert Widget with desired EPaper uploaded into Media Library
 * Version: 1.0.0
 * Author: Mehdi Rouh
 * Author URI: http://www.colbe.ch
 * Author: Rouh Mehdi
 * Author URI: http://www.katalogportal.ch
 * Text Domain: katalogportal
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation;
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 */
 define( 'KATALOGPORTAL_KATALOG_WIDGET_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

 class EPaper_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			'epaper_widget', // Base ID
			__( 'EPaper Katalogportal', 'text_domain' ), // Name
			array( 'description' => __( 'EPaper Widget', 'text_domain' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		global $katalogportal_options;	
		$katalogportal_uid = $katalogportal_options['katalogportal_userid'];
		
		echo $args['before_widget'];
		echo '<div style="clear:both;"></div><div style="float:left; display: block; margin-right: 10px; width: 120px; text-align: center;"><a class="iframe first last item" href="http://www.katalogportal.ch/book.aspx?id='.$katalogportal_uid.'&kn='.$instance['katalogportal_pdf_id'].'">';
		echo '<img src="';
		
		if (!empty($instance['katalogportal_image_url'])) {
			echo $instance['katalogportal_image_url'].'" 
				alt="'.$instance['katalogportal_alt_text'].'" 
				title="'.$instance['katalogportal_alt_text'].'" ';
			echo 'style="'.(!empty($instance['katalogportal_image_width'])?'width:'.$instance['katalogportal_image_width'].'px; ':'').
				//		(!empty($instance['katalogportal_image_height'])?'height:'.$instance['katalogportal_image_height'].'px; ':'').
					' height: auto;" >';
		} else {
			echo katalogportal_URL.'/images/katalogportal_logo.png" ';
			
			echo 'style="'.(!empty($instance['katalogportal_image_width'])?'width:'.$instance['katalogportal_image_width'].'px; ':'250px;').
				' height: auto;" >';
		}
		
		if (!empty($instance['thekatalogname'])) {
			echo '<br/>'.$instance['thekatalogname'].'</a></div>';
		}
		echo $args['after_widget'];
	}

	public function form( $instance ) {
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
		
		?>
		<p>	
			<select name="<?php echo $this->get_field_name( 'katalogportal_pdf_id' ); ?>" id="<?php echo $this->get_field_id( 'katalogportal_pdf_id' ); ?>">
				<?php
				while ( $pdf_files->have_posts() ) : $pdf_files->the_post(); ?>
				<?php 
					$kat_url = get_post_meta( get_the_ID(), 'kat_url', true );
					if (!empty($kat_url)) { ?>
					<?php $filename = basename ( get_attached_file( get_the_ID() ) ); ?>
					<option <?php if ($instance['katalogportal_pdf_id'] == $filename) echo ' selected ' ?>	value="<?php echo $filename; ?>"><?php echo substr( get_the_title(), 0, 35 ).' ('.$filename.')'; ?></option>
				<?php } ?>
				<?php endwhile; ?>
			</select>
		</p>
		<p>
		<input name="<?php echo $this->get_field_name( 'thekatalogname' ); ?>" id="<?php echo $this->get_field_id( 'thekatalogname' ); ?>" type="text" value="<?php echo $instance['thekatalogname']; ?>" >
		</p>
		
		<?php 
		//////////		
		// Defaults.
		$instance = wp_parse_args( (array) $instance, array(
			'katalogportal_image_url'               => '',
			'katalogportal_image_width'             => '',
			'katalogportal_alt_text'                => '',
			) );
		?>

	    <div>
		      <label for="<?php echo esc_attr( $this->get_field_id( 'katalogportal_image_url' ) ); ?>"><?php _e( 'Image URL', 'katalogportal_katalog_widget' ); ?></label>:<br />
		      <input type="text" class="img widefat" name="<?php echo esc_attr( $this->get_field_name( 'katalogportal_image_url' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'katalogportal_image_url' ) ); ?>" value="<?php echo esc_url( $instance['katalogportal_image_url'] ); ?>" /><br />
		      <input type="button" class="select-imgKW button button-primary" value="<?php _e( 'Upload', 'katalogportal_katalog_widget' ); ?>" data-uploader_title="<?php _e( 'Select Image', 'katalogportal_katalog_widget' ); ?>" data-uploader_button_text="<?php _e( 'Choose Image', 'katalogportal_katalog_widget' ); ?>" style="margin-top:5px;" />

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

	    <p>
	      <label for="<?php echo esc_attr( $this->get_field_id( 'katalogportal_image_width' ) ); ?>"><?php _e( 'Image Width', 'katalogportal_katalog_widget' ); ?>:</label>
	        <input id="<?php echo esc_attr( $this->get_field_id( 'katalogportal_image_width' ) ); ?>"
	        name="<?php echo esc_attr( $this->get_field_name( 'katalogportal_image_width' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['katalogportal_image_width'] ); ?>" style="max-width:60px;"/>&nbsp;<em class="small"><?php _e( 'in pixel', 'katalogportal_katalog_widget' ); ?></em>
	    </p>

	    <p>
	      <label for="<?php echo esc_attr( $this->get_field_id( 'katalogportal_alt_text' ) ); ?>"><?php _e( 'Alt Text', 'katalogportal_katalog_widget' ); ?>:</label>
	        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'katalogportal_alt_text' ) ); ?>"
	        name="<?php echo esc_attr( $this->get_field_name( 'katalogportal_alt_text' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['katalogportal_alt_text'] ); ?>" />
	    </p>
		
		<?php
		
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance = $old_instance;

		$instance['katalogportal_pdf_id'] = ( ! empty( $new_instance['katalogportal_pdf_id'] ) ) ? strip_tags( $new_instance['katalogportal_pdf_id'] ) : '';
		
		$instance['thekatalogname'] = ( ! empty( $new_instance['thekatalogname'] ) ) ? strip_tags( $new_instance['thekatalogname'] ) : '';

		$instance['katalogportal_image_url']               = esc_url_raw( $new_instance['katalogportal_image_url'] );
		$instance['katalogportal_image_width']             = esc_attr( $new_instance['katalogportal_image_width'] );
		$instance['katalogportal_alt_text']                = sanitize_text_field( $new_instance['katalogportal_alt_text'] );
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['katalogportal_image_caption'] = $new_instance['katalogportal_image_caption'];
		} else {
			$instance['katalogportal_image_caption'] = wp_kses_post( $new_instance['katalogportal_image_caption'] );
		}
		
		return $instance;
	}
}
function katalogportal_katalog_widget_scripts( $hook ) {
	wp_enqueue_style( 'katalogportal-katalog-widget-admin', KATALOGPORTAL_KATALOG_WIDGET_URL . '/css/admin.css', array(), '1.4.0' );
	wp_enqueue_media();
	wp_enqueue_script( 'katalogportal-katalog-widget-admin', KATALOGPORTAL_KATALOG_WIDGET_URL . '/js/adminKW.js', array( 'jquery' ), '1.4.0' );
}
add_action( 'admin_enqueue_scripts', 'katalogportal_katalog_widget_scripts' );


function register_epaper_widget() {
    register_widget( 'EPaper_Widget' );
}
add_action( 'widgets_init', 'register_epaper_widget' );