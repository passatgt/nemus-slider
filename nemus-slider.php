<?php

/*
Plugin Name: Nemus Slider
Plugin URI: http://visztpeter.me
Description: Beautiful and simple to use WordPress slideshow plugin
Version: 1.2.2
Author: Viszt Péter
Author URI: http://visztpeter.me
License: GPLv2 or later
*/

//Define some useful constants
if (!defined('NEMUS_SLIDER_IN_THEME')) {
	define('NEMUS_SLIDER_DIR', plugin_dir_path(__FILE__));
	define('NEMUS_SLIDER_URL', plugin_dir_url(__FILE__));
} else {
	//If its included within a theme
	define('NEMUS_SLIDER_DIR', get_template_directory().NEMUS_SLIDER_IN_THEME);
	define('NEMUS_SLIDER_URL', get_template_directory_uri().NEMUS_SLIDER_IN_THEME);
}
define('NEMUS_SLIDER_VERSION', '1.2.2');
if (!defined('NEMUS_SLIDER_VERSION_KEY')) define('NEMUS_SLIDER_VERSION_KEY', 'nemus_slider_version');
if (!defined('NEMUS_SLIDER_VERSION_NUM')) define('NEMUS_SLIDER_VERSION_NUM', '1.2.2');
add_option(NEMUS_SLIDER_VERSION_KEY, NEMUS_SLIDER_VERSION_NUM);


//Extra button in the plugins list
add_filter('plugin_action_links', 'nemus_slider_plugin_action_links', 10, 2);
function nemus_slider_plugin_action_links($links, $file) {
	static $this_plugin;

	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);

	if ($file == $this_plugin) {
		
		$settings_link = '<a href="' . site_url() . '/wp-admin/edit.php?post_type=nemus_slider">'.__('Manage sliders','nemus_slider').'</a>';

		array_unshift($links, $settings_link);
	}
	return $links;
}

//Register the Nemus Slider Post Type to manage the sliders
add_action('init', 'register_nemus_slider_post_type');
function register_nemus_slider_post_type() {

	//Post type registration
	register_post_type('nemus_slider', array(
		'labels' => array(
			'name' => __('Sliders','nemus_slider'),
		    'singular_name' => __('Slider','nemus_slider'),
		    'add_new_item' => __('Add New Slider','nemus_slider'),
		    'edit_item' => __('Edit Slider','nemus_slider'),
		    'new_item' => __('New Slider','nemus_slider'),
		    'all_items' => __('All Sliders','nemus_slider'),
		    'view_item' => __('View Slider','nemus_slider'),
		    'search_items' => __('Search Sliders','nemus_slider'),
		    'not_found' =>  __('No sliders found','nemus_slider'),
		    'not_found_in_trash' => __('No sliders found in the trash','nemus_slider')
		),
		'public' => false,
		'show_ui' => true,
		'publicly_queryable' => false,
		'supports' => array('title'),
		'capability_type' => array("nemus-slider", "nemus-sliders"),
		'map_meta_cap' => true
	));
	
	//Translation support
	load_plugin_textdomain('nemus_slider', false, NEMUS_SLIDER_DIR . '/lang' );

}

//Capabilities
function nemus_slider_add_caps_to_admin() {
	$caps = array(
		'read',
		'read_nemus-slider',
		'read_private_nemus-sliders',
		'edit_nemus-sliders',
		'edit_private_nemus-sliders',
		'edit_published_nemus-sliders',
		'edit_others_nemus-sliders',
		'publish_nemus-sliders',
		'delete_nemus-sliders',
		'delete_private_nemus-sliders',
		'delete_published_nemus-sliders',
		'delete_others_nemus-sliders',
	);
	$roles = array(
		get_role( 'administrator' ),
		get_role( 'editor' ),
	);
	foreach ($roles as $role) {
		foreach ($caps as $cap) {
			$role->add_cap( $cap );
		}
	}
}
add_action( 'after_setup_theme', 'nemus_slider_add_caps_to_admin' );

//Nemus Slider admin CSS & JS
function nemus_slider_admin_js_css() {
    wp_enqueue_style('nemus_slider_admin_css', NEMUS_SLIDER_URL.'assets/admin/admin.css');
    wp_enqueue_script('nemus_slider_admin_js', NEMUS_SLIDER_URL.'assets/admin/admin.js');
    wp_enqueue_style('nemus_slider_chosen_css', NEMUS_SLIDER_URL.'assets/admin/chosen/chosen.css');
    wp_enqueue_script('nemus_slider_chosen_js', NEMUS_SLIDER_URL.'assets/admin/chosen/chosen.jquery.min.js');
}
add_action('admin_init', 'nemus_slider_admin_js_css');

//Nemus Slider custom metabox
add_action('admin_init', 'register_nemus_slider_slide_box');
add_action('save_post', 'save_nemus_slider_slide_box',10,2);

function register_nemus_slider_slide_box() {
	add_meta_box('nemus_slider_slides', __('Slider Options','nemus_slider'), 'nemus_slider_slide_box_callback', 'nemus_slider', 'normal', 'low');
}

function nemus_slider_slide_box_callback() {
	global $post;
	$custom = get_post_custom($post->ID);
	$slide_data = '';
	if (isset($custom['slide_data'][0])) $slide_data = $custom['slide_data'][0];
	
	wp_nonce_field( basename( __FILE__ ), 'nemus_slider_slide_nonce' );
	
	if ( ! did_action( 'wp_enqueue_media' ) ) wp_enqueue_media();

	?>
	
	<div id="slides" style="display:none;">
		<?php if($slide_data): $slide_data = unserialize($slide_data); foreach($slide_data as $key => $slide): ?>
			<?php if(!isset($slide['autoslide'])): ?>
			<div class="slide">
				<header>
					<span><em>#<?php echo $key+1; ?></em> <?php _e('slide','nemus_slider'); ?></span>
					<nav>
						<a href="#" class="nemusicon-docs duplicate"><?php _e('duplicate','nemus_slider'); ?></a>
						<a href="#" class="nemusicon-minus-circled delete"><?php _e('delete','nemus_slider'); ?></a>
					</nav>
				</header>
				<div class="body">
					<div class="left">
						<?php
						$bg = '';
						if ($slide['image']!='') {
							$image = $slide['image'];
							if (is_numeric($image)) $image = wp_get_attachment_url($slide['image']);
							$bg = 'style="background-image:url('.$image.')"';
						} 
						?>
						<div class="image<?php if($slide['image']!=''):?> hasimg<?php endif; ?>" <?php echo $bg; ?>>
							<a href="#" class="nemusicon-upload-cloud upload"><?php _e('Upload Image','nemus_slider'); ?></a>
							<a href="#" class="upload_video <?php if($slide['video']): ?>selected nemusicon-cancel-circled<?php endif; ?>" title="<?php _e('Youtube or Vimeo link','nemus_slider'); ?>">
								<?php if($slide['video']): ?>
									<?php _e('Video selected','nemus_slider'); ?>
								<?php else: ?>
									<?php _e('or video','nemus_slider'); ?>
								<?php endif; ?>
							</a>
							<input type="text" class="image-link" value="<?php echo $slide['image']; ?>" name="slide_data[<?php echo $key; ?>][image]" />
							<input type="text" class="video-link" value="<?php echo $slide['video']; ?>" name="slide_data[<?php echo $key; ?>][video]" />							
						</div>
					</div>
					<div class="right">
						<div class="row caption">
							<div class="field">
								<label><?php _e('Image Caption','nemus_slider'); ?> <em><?php _e('you can use html code','nemus_slider'); ?></em></label>
								<textarea class="caption" name="slide_data[<?php echo $key; ?>][caption]"><?php echo $slide['caption']; ?></textarea>
							</div>
							<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
								<a href="tl" <?php if($slide['caption_position']=='caption tl' || $slide['caption_position']==''):?>class="active"<?php endif; ?> title="<?php _e('Top Left','nemus_slider'); ?>"></a>
								<a href="tc" <?php if($slide['caption_position']=='caption tc'):?>class="active"<?php endif; ?> title="<?php _e('Top Center','nemus_slider'); ?>"></a>
								<a href="tr" <?php if($slide['caption_position']=='caption tr'):?>class="active"<?php endif; ?> title="<?php _e('Top Right','nemus_slider'); ?>"></a>
								<a href="cl" <?php if($slide['caption_position']=='caption cl'):?>class="active"<?php endif; ?> title="<?php _e('Center Left','nemus_slider'); ?>"></a>
								<a href="cc" <?php if($slide['caption_position']=='caption cc'):?>class="active"<?php endif; ?> title="<?php _e('Center','nemus_slider'); ?>"></a>
								<a href="cr" <?php if($slide['caption_position']=='caption cr'):?>class="active"<?php endif; ?> title="<?php _e('Center Right','nemus_slider'); ?>"></a>
								<a href="bl" <?php if($slide['caption_position']=='caption bl'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
								<a href="bc" <?php if($slide['caption_position']=='caption bc'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
								<a href="br" <?php if($slide['caption_position']=='caption br'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
								<input type="hidden" class="caption_position" name="slide_data[<?php echo $key; ?>][caption_position]" value="<?php echo $slide['caption_position']; ?>" />
							</span>
							<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
								<a href="<?php echo $slide['animation_position']; ?>" class="nemusicon-down"></a>
								<input type="hidden" class="animation_position" name="slide_data[<?php echo $key; ?>][animation_position]" value="<?php echo $slide['animation_position']; ?>" />
							</span>
						</div>
						<div class="row">
							<div class="field">
								<label><?php _e('Image Link','nemus_slider'); ?> <em>e.g. http://google.com</em></label>
								<input class="slide-link" name="slide_data[<?php echo $key; ?>][link]" type="text" value="<?php echo $slide['link']; ?>" />
							</div>
							<a href="#" class="nemusicon-popup link_target" title="<?php _e('Open in new window','nemus_slider'); ?>"></a>							
							<input class="link_target" type="hidden" name="slide_data[<?php echo $key; ?>][link_target]" value="<?php echo $slide['link_target']; ?>" />
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php else: ?>
			<div class="slide slide-auto">
				<header>
					<span class="nemusicon-rocket"><?php _e('Automated slides','nemus_slider'); ?></span>
					<nav>
						<a href="#" class="nemusicon-docs duplicate"><?php _e('duplicate','nemus_slider'); ?></a>
						<a href="#" class="nemusicon-minus-circled delete"><?php _e('delete','nemus_slider'); ?></a>
					</nav>
				</header>
				<div class="body">
					<div class="auto-slide-type">
						<span>
							<a href="#autoslide-tab-<?php echo $key; ?>-posts" data-type="posts" class="nemusicon-pin<?php if($slide['auto_slide']['type'] == '' || $slide['auto_slide']['type'] == 'posts'): ?> active<?php endif; ?>"><?php _e('Posts','nemus_slider'); ?></a>
							<a href="#autoslide-tab-<?php echo $key; ?>-flickr" data-type="flickr" class="nemusicon-flickr-circled<?php if($slide['auto_slide']['type'] == 'flickr'): ?> active<?php endif; ?>"><?php _e('Flickr','nemus_slider'); ?></a>
							<a href="#autoslide-tab-<?php echo $key; ?>-instagram" data-type="instagram" class="nemusicon-instagram<?php if($slide['auto_slide']['type'] == 'instagram'): ?> active<?php endif; ?>"><?php _e('Instagram','nemus_slider'); ?></a>
							<a href="#autoslide-tab-<?php echo $key; ?>-attached" data-type="attached" class="nemusicon-attach<?php if($slide['auto_slide']['type'] == 'attached'): ?> active<?php endif; ?>"><?php _e('Attached Photos','nemus_slider'); ?></a>
						</span>
						<input type="hidden" class="field-autoslide_type" name="slide_data[<?php echo $key; ?>][auto_slide][type]" value="<?php echo $slide['auto_slide']['type']; ?>" />
					</div>
				
					<div class="auto-slide-type-tab"<?php if($slide['auto_slide']['type'] == '' || $slide['auto_slide']['type'] == 'posts'): ?> style="display:block;"<?php endif; ?>>
						<div class="auto-slide-form">
							<p>
								<label><?php _e('Post type','nemus_slider'); ?></label>
								<select name="slide_data[<?php echo $key; ?>][auto_slide][post_type]" class="post_type nemus-chosen">
									<?php 
									$post_types=get_post_types(array('public' => true),'objects'); 
									foreach ($post_types as $post_type ):
									?>
									<option value="<?php echo $post_type->name; ?>"<?php if($slide['auto_slide']['post_type']==$post_type->name):?> selected="selected"<?php endif; ?>>
										<?php echo $post_type->label; ?>
									</option>
									<?php endforeach; ?>
								</select>
							</p>
							<p>
								<label><?php _e('Category','nemus_slider'); ?></label>
								<?php wp_dropdown_categories(array(
									'show_count' => true,
									'name' => 'slide_data['.$key.'][auto_slide][category]',
									'hierarchical' => true,
									'show_option_all' => __('From all category','nemus_slider'),
									'selected' => $slide['auto_slide']['category'],
									'class' => 'nemus-chosen'
								)); ?>
							</p>
							<p>
								<label><?php _e('Order by','nemus_slider'); ?></label>
								<select name="slide_data[<?php echo $key; ?>][auto_slide][orderby]" class="orderby nemus-chosen">
									<option value="title"<?php if($slide['auto_slide']['orderby']=='title'):?> selected="selected"<?php endif; ?>><?php _e('Name','nemus_slider'); ?></option>
									<option value="date"<?php if($slide['auto_slide']['orderby']=='date'):?> selected="selected"<?php endif; ?>><?php _e('Date','nemus_slider'); ?></option>
									<option value="comment_count"<?php if($slide['auto_slide']['orderby']=='comment_count'):?> selected="selected"<?php endif; ?>><?php _e('Comments','nemus_slider'); ?></option>
									<option value="rand"<?php if($slide['auto_slide']['orderby']=='rand'):?> selected="selected"<?php endif; ?>><?php _e('Random','nemus_slider'); ?></option>
								</select>
							</p>
							<p>
								<label><?php _e('Order','nemus_slider'); ?></label>
								<select name="slide_data[<?php echo $key; ?>][auto_slide][order]" class="order nemus-chosen">
									<option value="ASC"<?php if($slide['auto_slide']['order']=='ASC'):?> selected="selected"<?php endif; ?>><?php _e('Ascending','nemus_slider'); ?></option>
									<option value="DESC"<?php if($slide['auto_slide']['order']=='DESC'):?> selected="selected"<?php endif; ?>><?php _e('Descending','nemus_slider'); ?></option>
								</select>
							</p>
							<p class="half">
								<label><?php _e('Limit','nemus_slider'); ?></label>
								<input type="number" value="<?php echo $slide['auto_slide']['limit']; ?>" name="slide_data[<?php echo $key; ?>][auto_slide][limit]" class="limit" />
							</p>
							<p class="half">
								<label><?php _e('Caption','nemus_slider'); ?></label>
								<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
									<a href="tl" <?php if($slide['caption_position']=='caption tl' || $slide['caption_position']==''):?>class="active"<?php endif; ?> title="<?php _e('Top Left','nemus_slider'); ?>"></a>
									<a href="tc" <?php if($slide['caption_position']=='caption tc'):?>class="active"<?php endif; ?> title="<?php _e('Top Center','nemus_slider'); ?>"></a>
									<a href="tr" <?php if($slide['caption_position']=='caption tr'):?>class="active"<?php endif; ?> title="<?php _e('Top Right','nemus_slider'); ?>"></a>
									<a href="cl" <?php if($slide['caption_position']=='caption cl'):?>class="active"<?php endif; ?> title="<?php _e('Center Left','nemus_slider'); ?>"></a>
									<a href="cc" <?php if($slide['caption_position']=='caption cc'):?>class="active"<?php endif; ?> title="<?php _e('Center','nemus_slider'); ?>"></a>
									<a href="cr" <?php if($slide['caption_position']=='caption cr'):?>class="active"<?php endif; ?> title="<?php _e('Center Right','nemus_slider'); ?>"></a>
									<a href="bl" <?php if($slide['caption_position']=='caption bl'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
									<a href="bc" <?php if($slide['caption_position']=='caption bc'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
									<a href="br" <?php if($slide['caption_position']=='caption br'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
									<input type="hidden" class="caption_position" name="slide_data[<?php echo $key; ?>][caption_position]" value="<?php echo $slide['caption_position']; ?>" />
								</span>
								<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
									<a href="<?php echo $slide['animation_position']; ?>" class="nemusicon-down"></a>
									<input type="hidden" class="animation_position" name="slide_data[<?php echo $key; ?>][animation_position]" value="<?php echo $slide['animation_position']; ?>" />
								</span>
							</p>
						</div>
						<input type="hidden" name="slide_data[<?php echo $key; ?>][autoslide]" class="autoslide" value="1" />
						<div class="clear"></div>
					</div>
					
					<div class="auto-slide-type-tab"<?php if($slide['auto_slide']['type'] == 'flickr'): ?> style="display:block;"<?php endif; ?>>
						<div class="auto-slide-form flickr">
							<p>
								<label><?php _e('Set ID','nemus_slider'); ?></label>
								<input type="text" value="<?php echo $slide['auto_slide']['flickr']['set_id']; ?>" name="slide_data[<?php echo $key; ?>][auto_slide][flickr][set_id]" class="field-flickr-setid" />
								<small>Go to your Flickr account and click on one of your sets. In the end of URL you'll see a number, this is the set id. Example: 72157625956932639</small>
							</p>
							<p>
								<label><?php _e('User ID','nemus_slider'); ?></label>
								<input type="text" value="<?php echo $slide['auto_slide']['flickr']['user_id']; ?>" name="slide_data[<?php echo $key; ?>][auto_slide][flickr][user_id]" class="field-flickr-user_id" />
								<small>You can use the public Flickr api to get photos from users without an api key</small>
							</p>
							<p>
								<label><?php _e('Link the slides to','nemus_slider'); ?></label>
								<select name="slide_data[<?php echo $key; ?>][auto_slide][flickr][linkto]" class="order nemus-chosen field-flickr-linkto">
									<option value=""<?php if($slide['auto_slide']['flickr']['linkto']==''):?> selected="selected"<?php endif; ?>><?php _e('No link','nemus_slider'); ?></option>
									<option value="image"<?php if($slide['auto_slide']['flickr']['linkto']=='image'):?> selected="selected"<?php endif; ?>><?php _e('Link to image','nemus_slider'); ?></option>
									<option value="flickr"<?php if($slide['auto_slide']['flickr']['linkto']=='flickr'):?> selected="selected"<?php endif; ?>><?php _e('Link to flickr','nemus_slider'); ?></option>
								</select>
							</p>
							<p class="half">
								<label><?php _e('Limit','nemus_slider'); ?></label>
								<input type="number" value="<?php echo $slide['auto_slide']['flickr']['limit']; ?>" name="slide_data[<?php echo $key; ?>][auto_slide][flickr][limit]" class="field-flickr-limit" />
							</p>
							<p class="half">
								<label><?php _e('Caption','nemus_slider'); ?></label>
								<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
									<a href="tl" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption tl'):?>class="active"<?php endif; ?> title="<?php _e('Top Left','nemus_slider'); ?>"></a>
									<a href="tc" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption tc'):?>class="active"<?php endif; ?> title="<?php _e('Top Center','nemus_slider'); ?>"></a>
									<a href="tr" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption tr'):?>class="active"<?php endif; ?> title="<?php _e('Top Right','nemus_slider'); ?>"></a>
									<a href="cl" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption cl'):?>class="active"<?php endif; ?> title="<?php _e('Center Left','nemus_slider'); ?>"></a>
									<a href="cc" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption cc'):?>class="active"<?php endif; ?> title="<?php _e('Center','nemus_slider'); ?>"></a>
									<a href="cr" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption cr'):?>class="active"<?php endif; ?> title="<?php _e('Center Right','nemus_slider'); ?>"></a>
									<a href="bl" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption bl'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
									<a href="bc" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption bc'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
									<a href="br" <?php if($slide['auto_slide']['flickr']['caption_position']=='caption br'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
									<input type="hidden" class="field-flickr-caption_position" name="slide_data[<?php echo $key; ?>][auto_slide][flickr][caption_position]" value="<?php echo $slide['auto_slide']['flickr']['caption_position']; ?>" />
								</span>
								<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
									<a href="<?php echo $slide['auto_slide']['flickr']['animation_position']; ?>" class="nemusicon-down"></a>
									<input type="hidden" class="field-flickr-animation_position" name="slide_data[<?php echo $key; ?>][auto_slide][flickr][animation_position]" value="<?php echo $slide['auto_slide']['flickr']['animation_position']; ?>" />
								</span>
							</p>
							<div class="clear"></div>
						</div>
					</div>
					<div class="auto-slide-type-tab"<?php if($slide['auto_slide']['type'] == 'instagram'): ?> style="display:block;"<?php endif; ?>>
						<div class="auto-slide-form instagram">
							<p>
								<label><?php _e('Hash','nemus_slider'); ?></label>
								<input type="text" class="field-instagram-hash" value="<?php echo $slide['auto_slide']['instagram']['hash']; ?>" name="slide_data[<?php echo $key; ?>][auto_slide][instagram][hash]" />
								<small>Get a list of recently tagged media.</small>
							</p>
							<p class="half">
								<label><?php _e('Limit','nemus_slider'); ?></label>
								<input type="number" value="<?php echo $slide['auto_slide']['instagram']['limit']; ?>" name="slide_data[<?php echo $key; ?>][auto_slide][instagram][limit]" class="field-instagram-limit" />
							</p>
							<p>
								<label><?php _e('Link the slides to','nemus_slider'); ?></label>
								<select name="slide_data[<?php echo $key; ?>][auto_slide][instagram][linkto]" class="order nemus-chosen field-instagram-linkto">
									<option value=""<?php if($slide['auto_slide']['instagram']['linkto']==''):?> selected="selected"<?php endif; ?>><?php _e('No link','nemus_slider'); ?></option>
									<option value="image"<?php if($slide['auto_slide']['instagram']['linkto']=='image'):?> selected="selected"<?php endif; ?>><?php _e('Link to image','nemus_slider'); ?></option>
									<option value="instagram"<?php if($slide['auto_slide']['instagram']['linkto']=='instagram'):?> selected="selected"<?php endif; ?>><?php _e('Link to instagram','nemus_slider'); ?></option>
								</select>
							</p>
							<p class="half">
								<label><?php _e('Caption','nemus_slider'); ?></label>
								<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
									<a href="tl" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption tl'):?>class="active"<?php endif; ?> title="<?php _e('Top Left','nemus_slider'); ?>"></a>
									<a href="tc" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption tc'):?>class="active"<?php endif; ?> title="<?php _e('Top Center','nemus_slider'); ?>"></a>
									<a href="tr" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption tr'):?>class="active"<?php endif; ?> title="<?php _e('Top Right','nemus_slider'); ?>"></a>
									<a href="cl" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption cl'):?>class="active"<?php endif; ?> title="<?php _e('Center Left','nemus_slider'); ?>"></a>
									<a href="cc" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption cc'):?>class="active"<?php endif; ?> title="<?php _e('Center','nemus_slider'); ?>"></a>
									<a href="cr" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption cr'):?>class="active"<?php endif; ?> title="<?php _e('Center Right','nemus_slider'); ?>"></a>
									<a href="bl" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption bl'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
									<a href="bc" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption bc'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
									<a href="br" <?php if($slide['auto_slide']['instagram']['caption_position']=='caption br'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
									<input type="hidden" class="field-instagram-caption_position" name="slide_data[<?php echo $key; ?>][auto_slide][instagram][caption_position]" value="<?php echo $slide['auto_slide']['instagram']['caption_position']; ?>" />
								</span>
								<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
									<a href="<?php echo $slide['auto_slide']['instagram']['animation_position']; ?>" class="nemusicon-down"></a>
									<input type="hidden" class="field-instagram-animation_position" name="slide_data[<?php echo $key; ?>][auto_slide][instagram][animation_position]" value="<?php echo $slide['auto_slide']['instagram']['animation_position']; ?>" />
								</span>
							</p>
							<div class="clear"></div>
						</div>
					</div>
					<div class="auto-slide-type-tab"<?php if($slide['auto_slide']['type'] == 'attached'): ?> style="display:block;"<?php endif; ?>>
						<div class="auto-slide-form attached">
							<p>
								<label><?php _e('Link the slides to','nemus_slider'); ?></label>
								<select name="slide_data[<?php echo $key; ?>][auto_slide][attached][linkto]" class="order nemus-chosen field-attached-linkto">
									<option value=""<?php if($slide['auto_slide']['attached']['linkto']==''):?> selected="selected"<?php endif; ?>><?php _e('No link','nemus_slider'); ?></option>
									<option value="image"<?php if($slide['auto_slide']['attached']['linkto']=='image'):?> selected="selected"<?php endif; ?>><?php _e('Link to image','nemus_slider'); ?></option>
									<option value="post"<?php if($slide['auto_slide']['attached']['linkto']=='post'):?> selected="selected"<?php endif; ?>><?php _e('Link to post','nemus_slider'); ?></option>
								</select>
							</p>
							<p>
								<label><?php _e('Caption','nemus_slider'); ?></label>
								<select name="slide_data[<?php echo $key; ?>][auto_slide][attached][caption]" class="order nemus-chosen field-attached-caption">
									<option value=""<?php if($slide['auto_slide']['attached']['caption']==''):?> selected="selected"<?php endif; ?>><?php _e('No caption','nemus_slider'); ?></option>
									<option value="caption"<?php if($slide['auto_slide']['attached']['caption']=='caption'):?> selected="selected"<?php endif; ?>><?php _e('Caption','nemus_slider'); ?></option>
									<option value="title"<?php if($slide['auto_slide']['attached']['caption']=='title'):?> selected="selected"<?php endif; ?>><?php _e('Title','nemus_slider'); ?></option>
									<option value="title_caption"<?php if($slide['auto_slide']['attached']['caption']=='title_caption'):?> selected="selected"<?php endif; ?>><?php _e('Title & Caption','nemus_slider'); ?></option>
								</select>
							</p>
							<p>
								<label><?php _e('Caption position','nemus_slider'); ?></label>
								<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
									<a href="tl" <?php if($slide['auto_slide']['attached']['caption_position']=='caption tl' || $slide['auto_slide']['attached']['caption_position']==''):?>class="active"<?php endif; ?> title="<?php _e('Top Left','nemus_slider'); ?>"></a>
									<a href="tc" <?php if($slide['auto_slide']['attached']['caption_position']=='caption tc'):?>class="active"<?php endif; ?> title="<?php _e('Top Center','nemus_slider'); ?>"></a>
									<a href="tr" <?php if($slide['auto_slide']['attached']['caption_position']=='caption tr'):?>class="active"<?php endif; ?> title="<?php _e('Top Right','nemus_slider'); ?>"></a>
									<a href="cl" <?php if($slide['auto_slide']['attached']['caption_position']=='caption cl'):?>class="active"<?php endif; ?> title="<?php _e('Center Left','nemus_slider'); ?>"></a>
									<a href="cc" <?php if($slide['auto_slide']['attached']['caption_position']=='caption cc'):?>class="active"<?php endif; ?> title="<?php _e('Center','nemus_slider'); ?>"></a>
									<a href="cr" <?php if($slide['auto_slide']['attached']['caption_position']=='caption cr'):?>class="active"<?php endif; ?> title="<?php _e('Center Right','nemus_slider'); ?>"></a>
									<a href="bl" <?php if($slide['auto_slide']['attached']['caption_position']=='caption bl'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
									<a href="bc" <?php if($slide['auto_slide']['attached']['caption_position']=='caption bc'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
									<a href="br" <?php if($slide['auto_slide']['attached']['caption_position']=='caption br'):?>class="active"<?php endif; ?> title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
									<input type="hidden" class="field-attached-caption_position" name="slide_data[<?php echo $key; ?>][auto_slide][attached][caption_position]" value="<?php echo $slide['auto_slide']['attached']['caption_position']; ?>" />
								</span>
								<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
									<a href="<?php echo $slide['auto_slide']['attached']['animation_position']; ?>" class="nemusicon-down"></a>
									<input type="hidden" class="field-attached-animation_position" name="slide_data[<?php echo $key; ?>][auto_slide][attached][animation_position]" value="<?php echo $slide['auto_slide']['attached']['animation_position']; ?>" />
								</span>
							</p>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		<?php endforeach; endif; ?>
	</div>
	
	<div class="slide" id="slide_empty" style="display:none">
		<header>
			<span><em>1.</em> <?php _e('slide','nemus_slider'); ?></span>
			<nav>
				<a href="#" class="nemusicon-docs duplicate"><?php _e('duplicate','nemus_slider'); ?></a>
				<a href="#" class="nemusicon-minus-circled delete"><?php _e('delete','nemus_slider'); ?></a>
			</nav>
		</header>
		<div class="body">
			<div class="left">
				<div class="image">
					<a href="#" class="nemusicon-upload-cloud upload"><?php _e('Upload Image','nemus_slider'); ?></a>
					<a href="#" class="upload_video" data-selected_text="Video selected" title="<?php _e('Youtube or Vimeo link','nemus_slider'); ?>"><?php _e('or video','nemus_slider'); ?></span></a>
					<input type="text" class="image-link" name="image" />
					<input type="text" class="video-link" name="video" />
				</div>
			</div>
			<div class="right">
				<div class="row caption">
					<div class="field">
						<label><?php _e('Image Caption','nemus_slider'); ?> <em><?php _e('you can use html code','nemus_slider'); ?></em></label>
						<textarea class="caption" name="caption"></textarea>
					</div>
					<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
						<a href="tl" class="active" title="<?php _e('Top Left','nemus_slider'); ?>"></a>
						<a href="tc" title="<?php _e('Top Center','nemus_slider'); ?>"></a>
						<a href="tr" title="<?php _e('Top Right','nemus_slider'); ?>"></a>
						<a href="cl" title="<?php _e('Center Left','nemus_slider'); ?>"></a>
						<a href="cc" title="<?php _e('Center','nemus_slider'); ?>"></a>
						<a href="cr" title="<?php _e('Center Right','nemus_slider'); ?>"></a>
						<a href="bl" title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
						<a href="bc" title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
						<a href="br" title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
						<input type="hidden" class="caption_position" name="caption_position" value="" />
					</span>
					<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
						<a href="right" class="nemusicon-down"></a>
						<input type="hidden" class="animation_position" name="" value="right" />
					</span>
				</div>
				<div class="row">
					<div class="field">
					<label><?php _e('Image Link','nemus_slider'); ?> <em>e.g. http://google.com</em></label>
					<input class="slide-link" name="link" type="text" />
					</div>
					<a href="#" class="nemusicon-popup link_target" title="<?php _e('Open in new window','nemus_slider'); ?>"></a>
					<input class="link_target" type="hidden" name="link_target" value="0" />
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="slide slide-auto" id="slide_auto_empty" style="display:none">
		<header>
			<span class="nemusicon-rocket"><?php _e('Automated slides','nemus_slider'); ?></span>
			<nav>
				<a href="#" class="nemusicon-docs duplicate"><?php _e('duplicate','nemus_slider'); ?></a>
				<a href="#" class="nemusicon-minus-circled delete"><?php _e('delete','nemus_slider'); ?></a>
			</nav>
		</header>
		<div class="body">
			<div class="auto-slide-type">
				<span>
					<a href="#autoslide-tab-posts" data-type="posts" class="nemusicon-pin active"><?php _e('Posts','nemus_slider'); ?></a>
					<a href="#autoslide-tab-flickr" data-type="flickr" class="nemusicon-flickr-circled"><?php _e('Flickr','nemus_slider'); ?></a>
					<a href="#autoslide-tab-instagram" data-type="instagram" class="nemusicon-instagram"><?php _e('Instagram','nemus_slider'); ?></a>
					<a href="#autoslide-tab-attached" data-type="attached" class="nemusicon-attach"><?php _e('Attached Photos','nemus_slider'); ?></a>
				</span>
				<input type="hidden" name="autoslide_type" class="field-autoslide_type" value="posts" />
			</div>
		
			<div class="auto-slide-type-tab" style="display:block;">
				<div class="auto-slide-form">
					<p>
						<label><?php _e('Post type','nemus_slider'); ?></label>
						<select name="post_type" class="post_type nemus-chosen">
							<?php 
							$post_types=get_post_types(array('public' => true),'objects'); 
							foreach ($post_types as $post_type ):
							?>
							<option value="<?php echo $post_type->name; ?>"><?php echo $post_type->label; ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label><?php _e('Category','nemus_slider'); ?></label>
						<?php wp_dropdown_categories(array(
							'show_count' => true,
							'hierarchical' => true,
							'show_option_all' => __('From all category','nemus_slider'),
							'class' => 'nemus-chosen'
						)); ?>
					</p>
					<p>
						<label><?php _e('Order by','nemus_slider'); ?></label>
						<select name="order" class="orderby nemus-chosen">
							<option value="title"><?php _e('Name','nemus_slider'); ?></option>
							<option value="date"><?php _e('Date','nemus_slider'); ?></option>
							<option value="comment_count"><?php _e('Comments','nemus_slider'); ?></option>
							<option value="rand"><?php _e('Random','nemus_slider'); ?></option>
						</select>
					</p>
					<p>
						<label><?php _e('Order','nemus_slider'); ?></label>
						<select name="order" class="order nemus-chosen">
							<option value="ASC"><?php _e('Ascending','nemus_slider'); ?></option>
							<option value="DESC"><?php _e('Descending','nemus_slider'); ?></option>
						</select>
					</p>
					<p class="half">
						<label><?php _e('Limit','nemus_slider'); ?></label>
						<input type="number" value="5" name="limit" class="limit" />
					</p>
					<p class="half">
						<label><?php _e('Caption','nemus_slider'); ?></label>
						<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
							<a href="tl" class="active" title="<?php _e('Top Left','nemus_slider'); ?>"></a>
							<a href="tc" title="<?php _e('Top Center','nemus_slider'); ?>"></a>
							<a href="tr" title="<?php _e('Top Right','nemus_slider'); ?>"></a>
							<a href="cl" title="<?php _e('Center Left','nemus_slider'); ?>"></a>
							<a href="cc" title="<?php _e('Center','nemus_slider'); ?>"></a>
							<a href="cr" title="<?php _e('Center Right','nemus_slider'); ?>"></a>
							<a href="bl" title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
							<a href="bc" title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
							<a href="br" title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
							<input type="hidden" class="caption_position" name="caption_position" value="" />
						</span>
						<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
							<a href="right" class="nemusicon-down"></a>
							<input type="hidden" class="animation_position" name="" value="right" />
						</span>
					</p>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="autoslide" class="autoslide" value="1" />
			</div>
			
			<div class="auto-slide-type-tab">
				<div class="auto-slide-form flickr">
					<p>
						<label><?php _e('Set ID','nemus_slider'); ?></label>
						<input type="text" value="" name="" class="field-flickr-setid" />
						<small>Go to your Flickr account and click on one of your sets. In the end of URL you'll see a number, this is the set id. Example: 72157625956932639</small>
					</p>
					<p>
						<label><?php _e('User ID','nemus_slider'); ?></label>
						<input type="text" value="" name="" class="field-flickr-user_id" />
						<small>You can use the public Flickr api to get photos from users without an api key</small>
					</p>
					<p>
						<label><?php _e('Link the slides to','nemus_slider'); ?></label>
						<select name="" class="order nemus-chosen field-flickr-linkto">
							<option value="" selected="selected"><?php _e('No link','nemus_slider'); ?></option>
							<option value="image"><?php _e('Link to image','nemus_slider'); ?></option>
							<option value="flickr"><?php _e('Link to flickr','nemus_slider'); ?></option>
						</select>
					</p>
					<p class="half">
						<label><?php _e('Limit','nemus_slider'); ?></label>
						<input type="number" value="5" name="" class="field-flickr-limit" />
					</p>
					<p class="half">
						<label><?php _e('Caption','nemus_slider'); ?></label>
						<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
							<a href="tl" title="<?php _e('Top Left','nemus_slider'); ?>"></a>
							<a href="tc" title="<?php _e('Top Center','nemus_slider'); ?>"></a>
							<a href="tr" title="<?php _e('Top Right','nemus_slider'); ?>"></a>
							<a href="cl" title="<?php _e('Center Left','nemus_slider'); ?>"></a>
							<a href="cc" title="<?php _e('Center','nemus_slider'); ?>"></a>
							<a href="cr" title="<?php _e('Center Right','nemus_slider'); ?>"></a>
							<a href="bl" title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
							<a href="bc" title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
							<a href="br" title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
							<input type="hidden" class="field-flickr-caption_position" name="" value="" />
						</span>
						<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
							<a href="right" class="nemusicon-down"></a>
							<input type="hidden" class="field-flickr-animation_position" name="" value="right" />
						</span>
					</p>
					<div class="clear"></div>
				</div>
			</div>	
			
			<div class="auto-slide-type-tab">
				<div class="auto-slide-form instagram">
					<p>
						<label><?php _e('Hash','nemus_slider'); ?></label>
						<input type="text" value="" name="" class="field-instagram-hash" />
						<small>Get a list of recently tagged media.</small>
					</p>
					<p class="half">
						<label><?php _e('Limit','nemus_slider'); ?></label>
						<input type="number" value="5" name="" class="field-instagram-limit" />
					</p>
					<p>
						<label><?php _e('Link the slides to','nemus_slider'); ?></label>
						<select name="" class="order nemus-chosen field-instagram-linkto">
							<option value="" selected="selected"><?php _e('No link','nemus_slider'); ?></option>
							<option value="image"><?php _e('Link to image','nemus_slider'); ?></option>
							<option value="instagram"><?php _e('Link to instagram','nemus_slider'); ?></option>
						</select>
					</p>
					<p class="half">
						<label><?php _e('Caption','nemus_slider'); ?></label>
						<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
							<a href="tl" title="<?php _e('Top Left','nemus_slider'); ?>"></a>
							<a href="tc" title="<?php _e('Top Center','nemus_slider'); ?>"></a>
							<a href="tr" title="<?php _e('Top Right','nemus_slider'); ?>"></a>
							<a href="cl" title="<?php _e('Center Left','nemus_slider'); ?>"></a>
							<a href="cc" title="<?php _e('Center','nemus_slider'); ?>"></a>
							<a href="cr" title="<?php _e('Center Right','nemus_slider'); ?>"></a>
							<a href="bl" title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
							<a href="bc" title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
							<a href="br" title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
							<input type="hidden" class="field-instagram-caption_position" name="" value="" />
						</span>
						<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
							<a href="right" class="nemusicon-down"></a>
							<input type="hidden" class="field-instagram-animation_position" name="" value="right" />
						</span>
					</p>
					<div class="clear"></div>
				</div>
			</div>	
			
			<div class="auto-slide-type-tab"<?php if($slide['auto_slide']['type'] == 'attached'): ?> style="display:block;"<?php endif; ?>>
				<div class="auto-slide-form attached">
					<p>
						<label><?php _e('Link the slides to','nemus_slider'); ?></label>
						<select name="" class="order nemus-chosen field-attached-linkto">
							<option value="" selected="selected"><?php _e('No link','nemus_slider'); ?></option>
							<option value="image"><?php _e('Link to image','nemus_slider'); ?></option>
							<option value="post"><?php _e('Link to post','nemus_slider'); ?></option>
						</select>
					</p>
					<p>
						<label><?php _e('Caption','nemus_slider'); ?></label>
						<select name="" class="order nemus-chosen field-attached-caption">
							<option value="" selected="selected"><?php _e('No caption','nemus_slider'); ?></option>
							<option value="caption"><?php _e('Caption','nemus_slider'); ?></option>
							<option value="title"><?php _e('Title','nemus_slider'); ?></option>
							<option value="title_caption"><?php _e('Title & Caption','nemus_slider'); ?></option>
						</select>
					</p>
					<p>
						<label><?php _e('Caption position','nemus_slider'); ?></label>
						<span class="caption_position" data-tooltip="<?php _e('Caption Position','nemus_slider'); ?>">
							<a href="tl" title="<?php _e('Top Left','nemus_slider'); ?>"></a>
							<a href="tc" title="<?php _e('Top Center','nemus_slider'); ?>"></a>
							<a href="tr" title="<?php _e('Top Right','nemus_slider'); ?>"></a>
							<a href="cl" title="<?php _e('Center Left','nemus_slider'); ?>"></a>
							<a href="cc" title="<?php _e('Center','nemus_slider'); ?>"></a>
							<a href="cr" title="<?php _e('Center Right','nemus_slider'); ?>"></a>
							<a href="bl" title="<?php _e('Bottom Left','nemus_slider'); ?>"></a>
							<a href="bc" title="<?php _e('Bottom Center','nemus_slider'); ?>"></a>
							<a href="br" title="<?php _e('Bottom Right','nemus_slider'); ?>"></a>
							<input type="hidden" class="field-attached-caption_position" name="" value="" />
						</span>
						<span class="animation_direction" data-tooltip="<?php _e('Animation Position','nemus_slider'); ?>">
							<a href="right" class="nemusicon-down"></a>
							<input type="hidden" class="field-attached-animation_position" name="" value="right" />
						</span>
					</p>
					<div class="clear"></div>
				</div>
			</div>
	
			
			<div class="clear"></div>
		</div>
	</div>
	
	<div class="align-center">
		<a href="#" class="nemusicon-plus-circled" id="add_slide"><?php _e('Create a static slide','nemus_slider'); ?></a>
		<a href="#" class="nemusicon-rocket" id="add_automated_slide"><?php _e('Create an automated slide','nemus_slider'); ?></a>
	</div>
	<?php
}

function save_nemus_slider_slide_box($post_id, $post) {
	global $post;

	if ( !isset( $_POST['nemus_slider_slide_nonce'] ) || !wp_verify_nonce( $_POST['nemus_slider_slide_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );

	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	$post_meta = array();
	$post_meta['slide_data'] = '';
	if (isset($_POST['slide_data'])) $post_meta['slide_data'] = $_POST['slide_data'];

	foreach ($post_meta as $key => $value) {
		$new_meta_value = ( isset( $value ) ? $value : '' );
		$meta_key = $key;
		$meta_value = get_post_meta( $post_id, $meta_key, true );
		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, $meta_key, $new_meta_value );
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );
	}
}
//Slider metabox left side

//Additional metabox for slider options
add_action( 'post_submitbox_misc_actions', 'register_nemus_slider_additionals' );
add_action( 'save_post', 'save_nemus_slider_additionals',10,2 );
function register_nemus_slider_additionals() {
    global $post;
    if (get_post_type($post) == 'nemus_slider') {
		$custom = get_post_custom($post->ID);

		$slider_options = '';
		if (isset($custom['slider_options'][0])) $slider_options = $custom['slider_options'][0];
		
		$slider_options = unserialize($slider_options);

		$slider_autoplay = '';
		if (isset($slider_options['autoplay'])) $slider_autoplay = $slider_options['autoplay'];
		$slider_inside_control = '';
		if (isset($slider_options['control_position'])) $slider_inside_control = $slider_options['control_position'];
		$slider_autoplay_delay = $slider_options['autoplay_delay'];
		$slider_autoheight = '';
		if (isset($slider_options['autoheight'])) $slider_autoheight = $slider_options['autoheight'];
		$slider_height = $slider_options['height'];
		$slider_animation = 'slide';
		if (isset($slider_options['animation'])) $slider_animation = $slider_options['animation'];
		$slider_color = $slider_options['color'];
		$slider_orientation = $slider_options['orientation'];
		$slider_image_scale_mode = '';
		if (isset($slider_options['image_scale'])) $slider_image_scale_mode = $slider_options['image_scale'];
		$slider_carousel = '';
		if (isset($slider_options['carousel'])) $slider_carousel = $slider_options['carousel'];
		$slider_carousel_width = '';
		if (isset($slider_options['carousel_width'])) $slider_carousel_width = $slider_options['carousel_width'];
		$slider_carousel_margin = '';
		if (isset($slider_options['carousel_margin'])) $slider_carousel_margin = $slider_options['carousel_margin'];
	
	
		wp_nonce_field( basename( __FILE__ ), 'slider_additionals' );
		
		//Enqueue color picker
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
	
		?>	
		<div class="misc-pub-section nemus-slider-option">
			<strong class="nemusicon-play"><?php _e('Autoplay','nemus_slider'); ?></strong> 
			<input type="checkbox" value="1" name="slider_options[autoplay]" <?php if($slider_autoplay): ?> checked="checked"<?php endif; ?> />
		</div>
		<div class="misc-pub-section nemus-slider-option slider-extra" id="autoplay-delay" <?php if(!$slider_autoplay): ?>style="display:none;"<?php endif; ?>>
			<strong class="nemusicon-back-in-time"><?php _e('Delay','nemus_slider'); ?></strong>
			<input type="text" value="<?php echo $slider_autoplay_delay; ?>" name="slider_options[autoplay_delay]" placeholder="3000" /> <em>ms</em>
		</div>
		<div class="misc-pub-section nemus-slider-option">
			<strong class="nemusicon-arrow-combo"><?php _e('Auto Height','nemus_slider'); ?></strong> 
			<input type="checkbox" value="1" name="slider_options[autoheight]" <?php if($slider_autoheight): ?> checked="checked"<?php endif; ?> />
		</div>
		<div class="misc-pub-section nemus-slider-option slider-extra" id="slider-height" <?php if($slider_autoheight): ?>style="display:none;"<?php endif; ?>>
			<strong class="nemusicon-resize-vertical"><?php _e('Height','nemus_slider'); ?></strong> 
			<input type="text" value="<?php echo $slider_height; ?>" name="slider_options[height]" placeholder="500px" /> <em>px</em>
		</div>
		<div class="misc-pub-section nemus-slider-option">
			<strong class="nemusicon-doc-landscape"><?php _e('Inside controls','nemus_slider'); ?></strong> 
			<input type="checkbox" value="1" name="slider_options[control_position]" <?php if($slider_inside_control): ?> checked="checked"<?php endif; ?> />
		</div>
		<div class="misc-pub-section nemus-slider-option slider-extra slider-color">
			<strong class="nemusicon-palette"><?php _e('Color','nemus_slider'); ?></strong> 
			<input type="text" value="<?php echo $slider_color; ?>" name="slider_options[color]" placeholder="#000000" class="color" />
		</div>
		<div class="misc-pub-section nemus-slider-option image-scale-option">
			<strong class="nemusicon-picture"><?php _e('Image Scale','nemus_slider'); ?></strong> 
			<select name="slider_options[image_scale]" style="display:none;">
				<option value="fill"<?php if ($slider_image_scale_mode == 'fill' || $slider_image_scale_mode == ''): ?> selected="selected"<?php endif; ?>><?php _e('Fill','nemus_slider'); ?></option>
				<option value="fit"<?php if ($slider_image_scale_mode == 'fit'): ?> selected="selected"<?php endif; ?>><?php _e('Fit','nemus_slider'); ?></option>
				<option value="none"<?php if ($slider_image_scale_mode == 'none'): ?> selected="selected"<?php endif; ?>><?php _e('None','nemus_slider'); ?></option>
			</select>
			<span class="image-scale-options">
				<a data-type="fill" href="#"<?php if ($slider_image_scale_mode == 'fill' || $slider_image_scale_mode == ''): ?> class="active"<?php endif; ?>><?php _e('Fill','nemus_slider'); ?></a>
				<a data-type="fit" href="#"<?php if ($slider_image_scale_mode == 'fit'): ?> class="active"<?php endif; ?>><?php _e('Fit','nemus_slider'); ?></a>
				<a data-type="none" href="#"<?php if ($slider_image_scale_mode == 'none'): ?> class="active"<?php endif; ?>><?php _e('None','nemus_slider'); ?></a>
			</span>
		</div>
		<div class="misc-pub-section nemus-slider-option carousel-option">
			<strong class="nemusicon-ellipsis"><?php _e('Carousel','nemus_slider'); ?></strong> 
			<input type="checkbox" value="1" name="slider_options[carousel]" <?php if($slider_carousel): ?> checked="checked"<?php endif; ?> />
			<div class="additional-fields<?php if($slider_carousel): ?> visible<?php endif; ?>" id="carousel-fields">
				<span><?php _e('Item width','nemus_slider'); ?></span> <input type="number" value="<?php echo $slider_carousel_width; ?>" name="slider_options[carousel_width]" placeholder="250" /> <em>px</em><br/>
				<span><?php _e('Margin','nemus_slider'); ?></span> <input type="number" value="<?php echo $slider_carousel_margin; ?>" name="slider_options[carousel_margin]" placeholder="5" /> <em>px</em>
			</div>
		</div>
		<div class="misc-pub-section nemus-slider-option slider-extra-checkbox fade-type">
			<strong class="fade <?php if($slider_animation == 'fade'): ?>active<?php endif; ?>"><?php _e('Fade','nemus_slider'); ?></strong> 
			<a href="#" <?php if($slider_animation == 'slide'): ?>class="on"<?php endif; ?>><span></span></a>
			<strong class="slide <?php if($slider_animation == 'slide'): ?>active<?php endif; ?>"><?php _e('Slide','nemus_slider'); ?></strong> 
			<input type="hidden" value="<?php echo $slider_animation; ?>" name="slider_options[animation]" />
			<div class="clear"></div>
		</div>
		<div class="misc-pub-section nemus-slider-option slider-extra-checkbox orientation-type">
			<strong class="horizontal <?php if($slider_orientation == 'horizontal' || $slider_orientation == ''): ?>active<?php endif; ?>"><?php _e('Horizontal','nemus_slider'); ?></strong> 
			<a href="#" <?php if($slider_orientation == 'vertical'): ?>class="on"<?php endif; ?>><span></span></a>
			<strong class="vertical <?php if($slider_orientation == 'vertical'): ?>active<?php endif; ?>"><?php _e('Vertical','nemus_slider'); ?></strong> 
			<input type="hidden" value="<?php echo $slider_orientation; ?>" name="slider_options[orientation]" />
			<div class="clear"></div>
		</div>
		<?php
		global $pagenow;
		if ($pagenow == 'post.php'):
		?>
		<div class="misc-pub-section nemus-slider-option">
			<strong class="nemusicon-code"><?php _e('Shortcode:','nemus_slider'); ?></strong>
			<input type="text" value="[nemus_slider id=&quot;<?php echo $post->ID; ?>&quot;]" readonly="true" />
		</div>
		<?php endif; ?>
		<div class="misc-pub-section">
			<?php
			if ( 'publish' == $post->post_status ) {
				$preview_link = NEMUS_SLIDER_URL.'nemus-slider-preview.php?id='.$post->ID;
				$preview_button = __( 'Preview Changes', 'nemus_slider' );
			} else {
				$preview_link = NEMUS_SLIDER_URL.'nemus-slider-preview.php?id='.$post->ID;
				$preview_button = __( 'Preview', 'nemus_slider' );
			}
			?>
			<a class="button" href="<?php echo $preview_link; ?>" target="_blank"><?php echo $preview_button; ?></a>
			<a href="#" class="nemus-slider-advanced-settings">Advanced settings</a>
		</div>
		<div class="misc-pub-section nemus-slider-advanced-settings-section">
			<ul>
				<?php
					$slider_options_reverse = ''; if (isset($slider_options['reverse'])) $slider_options_reverse = $slider_options['reverse'];
					$slider_options_animationloop = ''; if (isset($slider_options['animationLoop'])) $slider_options_animationloop = $slider_options['animationLoop'];
					$slider_options_startat = ''; if (isset($slider_options['startAt'])) $slider_options_startat = $slider_options['startAt'];
					$slider_options_animationspeed = ''; if (isset($slider_options['animationSpeed'])) $slider_options_animationspeed = $slider_options['animationSpeed'];
					$slider_options_randomize = ''; if (isset($slider_options['randomize'])) $slider_options_randomize = $slider_options['randomize'];
					$slider_options_controlnav = ''; if (isset($slider_options['controlNav'])) $slider_options_controlnav = $slider_options['controlNav'];
					$slider_options_directionnav = ''; if (isset($slider_options['directionNav'])) $slider_options_directionnav = $slider_options['directionNav'];
				?>
				<li><label>reverse:</label><input type="text" name="slider_options[reverse]" value="<?php echo $slider_options_reverse; ?>" placeholder="false" /></li>
				<li><label>animationLoop:</label><input type="text" name="slider_options[animationLoop]" value="<?php echo $slider_options_animationloop; ?>" placeholder="true" /></li>
				<li><label>startAt:</label><input type="text" name="slider_options[startAt]" value="<?php echo $slider_options_startat; ?>" placeholder="0" /></li>
				<li><label>animationSpeed:</label><input type="text" name="slider_options[animationSpeed]" value="<?php echo $slider_options_animationspeed; ?>" placeholder="600" /></li>
				<li><label>randomize:</label><input type="text" name="slider_options[randomize]" value="<?php echo $slider_options_randomize; ?>" placeholder="false" /></li>
				<li><label>controlNav:</label><input type="text" name="slider_options[controlNav]" value="<?php echo $slider_options_controlnav; ?>" placeholder="true" /></li>
				<li><label>directionNav:</label><input type="text" name="slider_options[directionNav]" value="<?php echo $slider_options_directionnav; ?>" placeholder="true" /></li>
			</ul>
		</div>
    <?php
    }
}
function save_nemus_slider_additionals($post_id, $post) {
 
	global $post;
	
	if ( !isset( $_POST['slider_additionals'] ) || !wp_verify_nonce( $_POST['slider_additionals'], basename( __FILE__ ) ) )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );

	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;
	
	$post_meta = array();
	$post_meta['slider_options'] = '';
	if (isset($_POST['slider_options'])) $post_meta['slider_options'] = $_POST['slider_options'];

	foreach ($post_meta as $key => $value) {
		$new_meta_value = ( isset( $value ) ? $value : '' );
		$meta_key = $key;
		$meta_value = get_post_meta( $post_id, $meta_key, true );
		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, $meta_key, $new_meta_value, true );
		elseif ( $new_meta_value && $new_meta_value != $meta_value )
			update_post_meta( $post_id, $meta_key, $new_meta_value );
		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, $meta_key, $meta_value );
	}
 
}
//Additional metabox for slider options

//Add post type class to body in admin
function nemus_slider_add_to_admin_body_class( $classes ) {
	global $post;
	$mode = '';
	$uri = $_SERVER["REQUEST_URI"];
	$post_type = '';
	if ($post) $post_type = get_post_type($post->ID);
	if (strstr($uri,'edit.php')) {
		$mode = ' edit-list-';
	}
	if (strstr($uri,'post.php')) {
		$mode = ' edit-page-';
	}
	$classes .= $mode . $post_type. ' ';
	return $classes;
}
add_filter('admin_body_class', 'nemus_slider_add_to_admin_body_class');
//Add post type class to body in admin

//Slider Custom Table and another modifications
function nemus_slider_custom_columns($columns) {
	$columns = array(
		'cb'	 	=> '<input type="checkbox" />',
		'preview'	=> __('Preview','nemus_slider'),
		'title' 	=> __('Slider','nemus_slider'),
		'number' 	=> __('Number of slides','nemus_slider'),
		'shortcode'	=> __('Shortcode','nemus_slider'),
		'slider_id'	=> __('Slider ID','nemus_slider'),
		'date'		=> __('Date','nemus_slider'),
	);
	return $columns;
}

function nemus_slider_custom_columns_data($column) {
	global $post;
	$custom = get_post_custom($post->ID);
	$slide_data = array();
	if (isset($custom['slide_data'][0])) $slide_data = unserialize($custom['slide_data'][0]);
	if($column == 'shortcode') {
		echo '<input type="text" value="[nemus_slider id=&quot;'.$post->ID.'&quot;]" readonly="true" />';
	}
	if($column == 'preview') {
		echo '<div class="preview_image">';
		if ($slide_data[0]['image']) {
			if(is_numeric($slide_data[0]['image'])) {
				$image_url = wp_get_attachment_image_src($slide_data[0]['image'],'thumbnail', true);
				$image_url = $image_url[0];
			} else {
				$image_url = $slide_data[0]['image'];
			}
			echo '<div style="background-image:url('.$image_url.')"></div>';
		} else {
			echo '<div class="nemusicon-docs"></div>';
		}
		echo '</div>';
	}
	if($column == 'number') {
		echo count($slide_data);
	}
	if($column == 'slider_id') {
		echo '#'.$post->ID;
	}
}
 
add_action("manage_nemus_slider_posts_custom_column", "nemus_slider_custom_columns_data");
add_filter("manage_edit-nemus_slider_columns", "nemus_slider_custom_columns");

add_filter('post_row_actions','nemus_slider_action_row',10,2);
function nemus_slider_action_row($actions,$post) {
	if ($post->post_type =="nemus_slider"){
		$preview_link = NEMUS_SLIDER_URL.'nemus-slider-preview.php?id='.$post->ID;
		$preview_button = __( 'Preview', 'nemus_slider' );
		array_splice($actions, 2, 0, '<a href="'.$preview_link.'" target="_blank">'.$preview_button.'</a>');
	}
	return $actions;
}
//Slider Custom Table and another modifications

//Convert hex codes to rgba
function nemus_slider_hex2rgb($hex) {
	$hex = str_replace("#", "", $hex);
		
	if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
	$rgb = array($r, $g, $b);
	return $rgb;
}

//Slider shortcode, this is where the magic happens!
add_shortcode( 'nemus_slider', 'nemus_slider_shortcode' );
function nemus_slider_shortcode( $atts, $content ){
	extract(shortcode_atts(array(
		'id' => '0'
	), $atts));

	ob_start();

		global $post;
		
		//If nothing is specified, get the latest slider
		if ($id == 0) {
			$recent_slider = get_posts( array('posts_per_page' => 1, 'post_type' => 'nemus_slider') );
			$id = $recent_slider[0]->ID;
		}
		
		//Get the slider data
		$custom = get_post_custom($id);
		$slide_data = '';
		if (isset($custom['slide_data'][0])) $slide_data = $custom['slide_data'][0];
		$slide_data = apply_filters('nemus-slider-slide-data',unserialize($slide_data),$id);
		
		//Stop if we don't have at least one slide
		if (!$slide_data || get_post_status ( $id ) == 'trash' ) return;
		
		//Get global slider options
		$slider_options = '';
		if (isset($custom['slider_options'][0])) $slider_options = $custom['slider_options'][0];
		$slider_options = apply_filters('nemus-slider-options',unserialize($slider_options),$id);
									
		$slider_autoplay = false; if (isset($slider_options['autoplay'])) $slider_autoplay = $slider_options['autoplay'];
		$slider_autoplay_delay = '3000'; if (isset($slider_options['autoplay_delay']) && $slider_options['autoplay_delay'] !='') $slider_autoplay_delay = $slider_options['autoplay_delay'];
		$slider_autoheight = false; if (isset($slider_options['autoheight'])) $slider_autoheight = $slider_options['autoheight'];		
		$slider_height = '500px'; if (isset($slider_options['height']) && $slider_options['height'] != '') $slider_height = $slider_options['height'];
		$slider_animation = 'fade'; if (isset($slider_options['animation'])) $slider_animation = $slider_options['animation'];
		if ($slider_autoheight) $slider_height = 'auto';
		$slider_inside_control = 'controls-outside'; if (isset($slider_options['control_position'])) $slider_inside_control = 'controls-inside';
		$slide_id = 1;
		$slider_orientation = 'horizontal'; if (isset($slider_options['orientation'])) $slider_orientation = $slider_options['orientation'];
		$slider_image_scale = 'fill'; if (isset($slider_options['image_scale'])) $slider_image_scale = $slider_options['image_scale'];

		//Change the color if neccessary
		$slider_color = false; if (isset($slider_options['color'])) $slider_color = $slider_options['color'];
		$slider_color_css = apply_filters('nemus-slider-color-css','#nemus-slider-'.$id.' .nemus-direction-nav a {color:'.$slider_color.';} #nemus-slider-'.$id.' .nemus-control-paging li a {background:'.$slider_color.';background:rgba('.implode(',',nemus_slider_hex2rgb($slider_color)).',0.1);border-color:'.$slider_color.';border-color:rgba('.implode(',',nemus_slider_hex2rgb($slider_color)).',0.5);}#nemus-slider-'.$id.' .nemus-control-paging li a:hover,#nemus-slider-'.$id.' .nemus-control-paging li a.nemus-active{background:'.$slider_color.';background:rgba('.implode(',',nemus_slider_hex2rgb($slider_color)).',0.7);}',$slider_color,$id);
		
		//Carousel CSS if neccessary
		$slider_carousel_class = '';
		$slider_carousel = false; if (isset($slider_options['carousel'])) $slider_carousel = $slider_options['carousel'];
		$slider_carousel_width = '250'; if (isset($slider_options['carousel_width'])) $slider_carousel_width = $slider_options['carousel_width'];
		$slider_carousel_margin = '5'; if (isset($slider_options['carousel_margin'])) $slider_carousel_margin = $slider_options['carousel_margin'];
		$slider_carousel_css = apply_filters('nemus-slider-carousel-css','#nemus-slider-'.$id.' .slides > li.slide {margin-right:'.$slider_carousel_margin.'px}',$id);
		$slider_carousel_attr = '';
		if ($slider_carousel) {
			$slider_carousel_attr = ' data-carousel="true" data-carousel-width="'.$slider_carousel_width.'" data-carousel-margin="'.$slider_carousel_margin.'"';
			$slider_carousel_class = 'carousel';
			$slider_orientation = 'horizontal';
		} 
		
		//Advanced settings
		$advanced_settings = array();
		if (isset($slider_options['reverse']) && $slider_options['reverse'] != '') $advanced_settings[] = 'reverse:'.$slider_options['reverse'];
		if (isset($slider_options['animationLoop']) && $slider_options['animationLoop'] != '') $advanced_settings[] = 'animationLoop:'.$slider_options['animationLoop'];
		if (isset($slider_options['startAt']) && $slider_options['startAt'] != '') $advanced_settings[] = 'startAt:'.$slider_options['startAt'];
		if (isset($slider_options['animationSpeed']) && $slider_options['animationSpeed'] != '') $advanced_settings[] = 'animationSpeed:'.$slider_options['animationSpeed'];
		if (isset($slider_options['randomize']) && $slider_options['randomize'] != '') $advanced_settings[] = 'randomize:'.$slider_options['randomize'];
		if (isset($slider_options['controlNav']) && $slider_options['controlNav'] != '') $advanced_settings[] = 'controlNav:'.$slider_options['controlNav'];
		if (isset($slider_options['directionNav']) && $slider_options['directionNav'] != '') $advanced_settings[] = 'directionNav:'.$slider_options['directionNav'];
		
		$advanced_settings = 'data-advanced="'.implode (',',$advanced_settings).'"';
		?>
		
		<?php do_action( 'nemus-slider-before', $id ); ?>
		<div class="nemus-slider animation-<?php echo $slider_animation; ?> <?php echo $slider_inside_control; ?> <?php echo $slider_orientation; ?> <?php echo $slider_image_scale; ?> <?php echo $slider_carousel_class; ?>" id="nemus-slider-<?php echo $id; ?>" data-autoplay="<?php echo $slider_autoplay; ?>" data-autoplay-delay="<?php echo $slider_autoplay_delay; ?>" style="height:<?php echo $slider_height; ?>" data-animation="<?php echo $slider_animation; ?>" data-autoheight="<?php echo $slider_autoheight; ?>" data-orientation="<?php echo $slider_orientation; ?>"<?php echo $slider_carousel_attr; ?> <?php echo $advanced_settings; ?> data-content_width="<?php global $content_width; echo $content_width; ?>">
			<ul class="slides">
			<?php foreach ($slide_data as $slide): ?>
				<?php if(!isset($slide['video'])) $slide['video'] = ''; ?>
				
				<?php if(!isset($slide['autoslide'])): ?>
					<li class="slide slide-<?php echo $slide_id; ?>">
						<?php
						if (is_numeric($slide['image'])) {
							$slide['image'] = wp_get_attachment_image_src($slide['image'],apply_filters('nemus-slider-image-size','full',$id));
							$slide['image'] = $slide['image'][0];
						}
						?>
						<?php if($slide['video']): ?>
							<div class="slide-image slide-image-video" style="background-image:url(<?php echo $slide['image']; ?>);height: <?php echo $slider_height; ?>;">
								<a href="" class="nemusicon-play start-video"></a>
							</div>
							<?php
							$image_url = parse_url($slide['video']);
							if (isset($image_url['host'])) {
								if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'){
									$array = explode("&", $image_url['query']);
									echo apply_filters('nemus-slider-youtube-embed-code','<iframe width="100%" style="height:'.$slider_height.';" src="http://www.youtube.com/embed/'.substr($array[0], 2).'?enablejsapi=1" frameborder="0" allowfullscreen class="youtubeplayer" id="youtubeplayer-'.$id.'-'.$slide_id.'"></iframe>',substr($array[0], 2),$id);
								} else if($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com'){							
									echo apply_filters('nemus-slider-vimeo-embed-code','<iframe src="http://player.vimeo.com/video/'.substr($image_url['path'], 1).'?badge=0&api=1&player_id=vimeoplayer-'.$id.'-'.$slide_id.'" class="vimeoplayer" id="vimeoplayer-'.$id.'-'.$slide_id.'" width="100%" style="height:'.$slider_height.';" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',substr($image_url['path'], 1),$id);
								}
							}
							?>
						<?php else: ?>
							<?php if($slider_autoheight): ?>
								<img src="<?php echo $slide['image']; ?>" />	
							<?php else: ?>
								<div class="slide-image" style="background-image:url(<?php echo $slide['image']; ?>);height: <?php echo $slider_height; ?>;"></div>	
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if(isset($slide['caption'])): ?>
							<?php if($slide['caption'] != ''): ?>
							<?php
							$position = 'tl';
							if (isset($slide['caption_position'])) {
								if ($slide['caption_position'] != '') $position = $slide['caption_position']; 
							} 
							?>
							<div class="caption <?php echo $position; ?> anim-<?php if (isset($slide['animation_position'])) echo $slide['animation_position']; ?>">
								<?php do_action( 'nemus-slider-before-caption', $id, $slide_id,0 ); ?>
								<?php echo apply_filters('nemus-slider-caption-text',do_shortcode($slide['caption']),$id,$slide_id); ?>
								<?php do_action( 'nemus-slider-after-caption', $id, $slide_id,0 ); ?>
							</div>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if(isset($slide['link'])): ?>
							<?php if($slide['link'] != ''): ?>
								<?php if ($slide['link_target']) $slide['link_target'] = 'target="_blank"'; ?>
								<a href="<?php echo $slide['link']; ?>" <?php echo $slide['link_target']; ?> class="slide-link"></a>
							<?php endif; ?>
						<?php endif; ?>
					</li>
				<?php else: ?>
				
				
					<?php
					//If regular posts
					if ($slide['auto_slide']['type'] == '' || $slide['auto_slide']['type'] == 'posts'): ?>
						<?php
						$args = array(
							'post_type' => $slide['auto_slide']['post_type'],
							'posts_per_page' => $slide['auto_slide']['limit'],
							'cat' => $slide['auto_slide']['category'],
							'orderby' => $slide['auto_slide']['orderby'],
							'order' => $slide['auto_slide']['order']
							);
						$auto_slides = new WP_Query(apply_filters( 'nemus-slider-auto-slide-query', $args, $id ));
						?>
						<?php while($auto_slides->have_posts()): $auto_slides->the_post(); ?>
						<li class="slide slide-<?php echo $slide_id; ?> autoslide-<?php the_ID(); ?>">
							<?php if(has_post_thumbnail()): ?>
								<?php $thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), apply_filters('nemus-slider-image-size','full',$id)); ?>
								<?php if($slider_autoheight): ?>
									<img src="<?php echo $thumb[0]; ?>" />	
								<?php else: ?>
									<div class="slide-image" style="background-image:url(<?php echo $thumb[0]; ?>);height: <?php echo $slider_height; ?>;"></div>
								<?php endif; ?>
							<?php endif; ?> 
							<?php
							$position = 'tl';
							if (isset($slide['caption_position'])) {
								if ($slide['caption_position'] != '') $position = $slide['caption_position']; 
							} 
							?>
							<div class="caption <?php echo $position; ?> anim-<?php if (isset($slide['animation_position'])) echo $slide['animation_position']; ?>">
								<h1><?php the_title(); ?></h1>
								<?php do_action( 'nemus-slider-before-autoslide-caption', $id, $slide_id, get_the_ID() ); ?>
								<?php echo apply_filters('nemus-slider-autoslide-caption','<p>'.get_the_excerpt().'</p>', $id, $slide_id, get_the_ID()); ?>
								<?php do_action( 'nemus-slider-after-autoslide-caption', $id, $slide_id, get_the_ID() ); ?>
							</div>
							<?php echo apply_filters('nemus-slider-autoslide-link','<a href="'.get_permalink().'" class="slide-link"></a>', $id, $slide_id, get_the_ID()); ?>
						</li>
						<?php $slide_id++; endwhile; wp_reset_query(); ?>
					<?php endif; ?>
					
					<?php
					//If flickr posts
					if ($slide['auto_slide']['type'] == 'flickr'): ?>
						<?php if (get_option('nemus_slider_flickr_api_key') != '' || $slide['auto_slide']['flickr']['user_id'] != ''): ?>
						<li class="slide slide-<?php echo $slide_id; ?> flickr-slides" data-api="<?php echo get_option('nemus_slider_flickr_api_key'); ?>" data-setid="<?php echo $slide['auto_slide']['flickr']['set_id']; ?>" data-caption="<?php echo $slide['auto_slide']['flickr']['caption_position']; ?>" data-caption-animation="<?php echo $slide['auto_slide']['flickr']['animation_position']; ?>" data-linkto="<?php echo $slide['auto_slide']['flickr']['linkto']; ?>" data-limit="<?php echo $slide['auto_slide']['flickr']['limit']; ?>" data-userid="<?php echo $slide['auto_slide']['flickr']['user_id']; ?>"></li>
						<?php endif; ?>
					<?php endif; ?>
					
					<?php
					//If instagram posts
					if ($slide['auto_slide']['type'] == 'instagram'): ?>
						<li class="slide slide-<?php echo $slide_id; ?> instagram-slides" data-client_id="<?php echo get_option('nemus_slider_instagram_client_id'); ?>" data-access_token="<?php echo get_option('nemus_slider_instagram_token'); ?>" data-caption="<?php echo $slide['auto_slide']['instagram']['caption_position']; ?>" data-caption-animation="<?php echo $slide['auto_slide']['instagram']['animation_position']; ?>" data-user_id="<?php echo get_option('nemus_slider_instagram_user_id'); ?>" data-limit="<?php echo $slide['auto_slide']['instagram']['limit']; ?>" data-hash="<?php echo $slide['auto_slide']['instagram']['hash']; ?>" data-linkto="<?php echo $slide['auto_slide']['instagram']['linkto']; ?>"></li>
					<?php endif; ?>
					
					<?php
					//If attached posts
					if ($slide['auto_slide']['type'] == 'attached'): ?>
						<?php
						
						$att_args = array(
							'post_type'	     => 'attachment',
							'numberposts'    => -1,
							'post_status'    => null,
							'post_parent'    => $post->ID,
							'post_mime_type' => 'image',
							'orderby'        => 'menu_order'
						);
						$attachments = get_posts(apply_filters( 'nemus-slider-auto-slide-attached-query', $att_args, $id ));
						
						if( $attachments ): ?>
						
							<?php foreach( $attachments as $attachment ): ?>
								<?php $attachment_img = wp_get_attachment_image_src( $attachment->ID ,'full'); ?>
								<li class="slide slide-<?php echo $slide_id; ?> attachment-<?php echo $attachment->ID; ?>">
									<?php if($slider_autoheight): ?>
										<img src="<?php echo $attachment_img[0]; ?>" />	
									<?php else: ?>
										<div class="slide-image" style="background-image:url(<?php echo $attachment_img[0]; ?>);height: <?php echo $slider_height; ?>;"></div>
									<?php endif; ?>
									
									<?php if($slide['auto_slide']['attached']['caption'] != ''): ?>
									<?php
									$position = 'tl';
									if (isset($slide['auto_slide']['attached']['caption_position'])) {
										if ($slide['auto_slide']['attached']['caption_position'] != '') $position = $slide['auto_slide']['attached']['caption_position']; 
									} 
									?>
									<div class="caption <?php echo $position; ?> anim-<?php if (isset($slide['auto_slide']['attached']['animation_position'])) echo $slide['auto_slide']['attached']['animation_position']; ?>">
										<?php if($slide['auto_slide']['attached']['caption'] == 'caption'): ?>
										<p><?php echo $attachment->post_excerpt; ?></p>
										<?php endif; ?>
										
										<?php if($slide['auto_slide']['attached']['caption'] == 'title'): ?>
										<h1><?php echo $attachment->post_title; ?></h1>
										<?php endif; ?>
										
										<?php if($slide['auto_slide']['attached']['caption'] == 'title_caption'): ?>
											<h1><?php echo $attachment->post_title; ?></h1>
											<p><?php echo $attachment->post_excerpt; ?></p>
										<?php endif; ?>
									</div>
									<?php endif; ?>
									<?php if($slide['auto_slide']['attached']['linkto'] == 'post'): ?>
										<?php echo apply_filters('nemus-slider-autoslide-attached-link','<a href="'.get_permalink($attachment->ID).'" class="slide-link"></a>', $id, $slide_id, $attachment->ID); ?>
									<?php endif; ?>
									<?php if($slide['auto_slide']['attached']['linkto'] == 'image'): ?>
										<?php echo apply_filters('nemus-slider-autoslide-attached-link','<a href="'.$attachment_img[0].'" class="slide-link"></a>', $id, $slide_id, $attachment->ID); ?>
									<?php endif; ?>
								</li>
							<?php $slide_id++; endforeach; ?>
						
						<?php endif; ?>

					<?php endif; ?>
			
				<?php endif; ?>
			<?php $slide_id++; endforeach; ?>
			</ul>
		</div>
		<?php do_action( 'nemus-slider-after', $id ); ?>
		<?php 
	
	$slider = ob_get_contents();
	ob_end_clean();

    wp_enqueue_style('nemus-slider-css', NEMUS_SLIDER_URL.'assets/frontend/nemus-slider.css');
    wp_enqueue_script('nemus-slider-js', NEMUS_SLIDER_URL.'assets/frontend/nemus-slider.js',array( 'jquery' ));
    if ($slider_color != '') wp_add_inline_style( 'nemus-slider-css', $slider_color_css );
	if ($slider_carousel != '') wp_add_inline_style( 'nemus-slider-css', $slider_carousel_css); 

    if (file_exists(get_template_directory() . '/nemus-slider.css')) {
    	wp_enqueue_style( 'nemus-slider-custom-css', get_template_directory_uri() . '/nemus-slider.css' );
    }
    
	return $slider;
}
//Slider shortcode, this is where the magic happens!

//Slider widget
class Nemus_Slider_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'nemus_slider_widget', // Base ID
			'Nemus Slider', // Name
			array( 'description' => __( 'Display a Nemus Slider', 'nemus_slider' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$slider = $instance['slider'];

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		echo do_shortcode('[nemus_slider id="'.$slider.'"]');
		echo $after_widget;
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Nemus Slider', 'nemus_slider' );
		}
		
		$slider = '';
		if ( isset( $instance[ 'slider' ] ) ) $slider = $instance[ 'slider' ];
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:','nemus_slider' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_name( 'slider' ); ?>"><?php _e( 'Slider:','nemus_slider' ); ?></label> 
			
			<?php
			$args = array(
				'post_type' => 'nemus_slider',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC'
				);
			$nemus_sliders = new WP_Query($args);
			?>
			<?php if ( $nemus_sliders->have_posts() ): ?>
			<select class="widefat" id="<?php echo $this->get_field_id( 'slider' ); ?>" name="<?php echo $this->get_field_name( 'slider' ); ?>">
				<?php while($nemus_sliders->have_posts()): $nemus_sliders->the_post(); ?>
				<option value="<?php the_ID(); ?>" <?php if(get_the_ID() == $slider): ?>selected="selected"<?php endif; ?>><?php the_title(); ?></option>
				<?php endwhile; wp_reset_query(); ?>
			</select>
			<?php else: ?>
			<em><?php _e('Create a slider first','nemus_slider'); ?></em>
			<?php endif; ?>
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['slider'] = ( !empty( $new_instance['slider'] ) ) ? strip_tags( $new_instance['slider'] ) : '';

		return $instance;
	}

}

function nemus_slider_register_widget() {
	register_widget( 'Nemus_Slider_Widget' );
}
add_action( 'widgets_init', 'nemus_slider_register_widget' );
//Slider widget

//Get slider using php function
function nemus_slider($id = 0) {
	if ($id == 0) {
		$recent_slider = get_posts( array('posts_per_page' => 1, 'post_type' => 'nemus_slider') );
		$id = $recent_slider[0]->ID;
	}
	echo do_shortcode('[nemus_slider id="'.$id.'"]');
}
//Get slider using php function

//Slider shortcode button
add_action('init', 'nemus_slider_add_tinymce_button');
function nemus_slider_add_tinymce_button() {
	if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') ) {
		add_filter('mce_external_plugins', 'nemus_slider_add_tinymce_plugin');
		add_filter('mce_buttons', 'nemus_slider_register_tinymce_button');
	}
}

function nemus_slider_register_tinymce_button($buttons) {
   array_push($buttons, "nemus_slider");
   return $buttons;
}

function nemus_slider_add_tinymce_plugin($plugin_array) {
   $plugin_array['nemus_slider'] = NEMUS_SLIDER_URL.'assets/admin/tinymce.js';
   return $plugin_array;
}

//Ajax function for populating the select field, we don't want to make a query unnecessary, right?
add_action('wp_ajax_nemus_slider_load_sliders', 'nemus_slider_load_sliders_callback');
function nemus_slider_load_sliders_callback() {
	global $wpdb;

	$args = array(
		'post_type' => 'nemus_slider',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC'
	);
	$nemus_sliders = new WP_Query($args);
	
	$postsArray = array(); 
		
	while($nemus_sliders->have_posts()): $nemus_sliders->the_post();

		$posts = array(
			'id' => get_the_ID(),
			'title' => get_the_title()
		);
		array_push($postsArray,$posts);

	endwhile; wp_reset_query();
	
	$data = json_encode($postsArray);
	echo $data;

	die();
}
//Slider shortcode button

//Nemus slider settings
require_once (NEMUS_SLIDER_DIR . '/nemus-slider-settings.php');