<?php

add_action( 'admin_menu', 'nemus_slider_settings_menu' );
function nemus_slider_settings_menu() {
	add_submenu_page( 'edit.php?post_type=nemus_slider', __('Nemus Slider Settings','nemus_slider'), __('Settings','nemus_slider'), 'manage_options', 'nemus_slider_settings', 'nemus_slider_settings' );

	//call register settings function
	add_action( 'admin_init', 'nemus_slider_register_settings' );

}

function nemus_slider_register_settings() {
	//register our settings
	register_setting( 'nemus-slider-options', 'nemus_slider_instagram_client_id' );
	register_setting( 'nemus-slider-options', 'nemus_slider_instagram_token' );
	register_setting( 'nemus-slider-options', 'nemus_slider_instagram_user_id' );
	register_setting( 'nemus-slider-options', 'nemus_slider_flickr_api_key' );
}

function nemus_slider_settings() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.','nemus_slider' ) );
	}
	?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php _e('Nemus Slider Settings','nemus_slider'); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'nemus-slider-options' ); ?>
			<h3 class="title"><?php _e('Instagram Settings','nemus_slider'); ?></h3>
			<p><?php _e('You need to set a couple of options to start using the Instagram function.','nemus_slider'); ?></p>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('Client ID','nemus_slider'); ?></label>
						</th>
						<td>
							<input name="nemus_slider_instagram_client_id" type="text" value="<?php echo get_option('nemus_slider_instagram_client_id'); ?>" class="regular-text" />
							<p class="description"><?php _e("The only thing you'll need to get going is a valid client id from Instagram's API. You can easily register for one on Instagram's website:","nemus_slider"); ?> <a href="http://instagram.com/developer/" target="_blank">http://instagram.com/developer/</a></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('oAuth Token','nemus_slider'); ?></label>
						</th>
						<td>
							<input name="nemus_slider_instagram_token" type="text" value="<?php echo get_option('nemus_slider_instagram_token'); ?>" class="regular-text" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('User ID','nemus_slider'); ?></label>
						</th>
						<td>
							<input name="nemus_slider_instagram_user_id" type="text" value="<?php echo get_option('nemus_slider_instagram_user_id'); ?>" class="regular-text" />
							<p class="description"><?php _e("If you want to get images from a specific user, you will need a valid oAuth token. Don't know your user id and your oAuth token?","nemus_slider"); ?> <a href="http://visztpeter.me/instagram-access-token-request/" target="_blank"><?php _e('Click here','nemus_slider'); ?></a> <?php _e('to get one.','nemus_slider'); ?></p>
	                	</td>
	                </tr>
				</tbody>
			</table>
			<h3 class="title"><?php _e('Flickr Settings','nemus_slider'); ?></h3>
			<p><?php _e('You need a valid API Key from Flickr to use flickr images as a source for slides.','nemus_slider'); ?></p>
			<table class="form-table">
				<tbody>
				   	<tr valign="top">
						<th scope="row" class="titledesc">
							<label for=""><?php _e('API Key','nemus_slider'); ?></label>
						</th>
						<td>
							<input name="nemus_slider_flickr_api_key" type="text" value="<?php echo get_option('nemus_slider_flickr_api_key'); ?>" class="regular-text" />
							<p class="description"><?php _e("The only thing you'll need to get going is a API Key from Flickr's. You can easily register for one on Flickr's website:",'nemus_slider'); ?> <a href="http://www.flickr.com/services/apps/create/apply/" target="_blank">http://www.flickr.com/services/apps/create/apply/</a></p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','nemus_slider'); ?>"></p>
		</form>
	</div>
	
	<?php

}
?>