<?php

//Include wordpress core files
for ($i = 0; $i < $depth = 10; $i++) {
	$wp_root_path = str_repeat( '../', $i );

	if ( file_exists("{$wp_root_path}wp-load.php" ) ) {
		require_once("{$wp_root_path}wp-load.php");
		require_once("{$wp_root_path}wp-admin/includes/admin.php");
		break;
	}
}

//Redirect if user is not logged in
auth_redirect();

//Die if user can't edit posts
if(!current_user_can('edit_posts') ) die(__("You don't have the neccessary permission to preview forms.", "nemus_slider"));

//And the actual preview code
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php _e("Nemus Slider Preview", "nemus_slider") ?></title>
	<?php wp_enqueue_style('nemus_slider_admin_css', NEMUS_SLIDER_URL.'assets/admin/admin.css'); ?>
	<?php wp_head(); ?>
</head>
<body class="nemus_slider_preview">
	<div id="nemus_slider_preview">
		<?php $post = get_post($_GET["id"]); ?>
		<?php if($post && $post->post_type == 'nemus_slider'): ?>
		<header>
			<h1><em>#<?php echo $_GET["id"]; ?></em><?php echo get_the_title($_GET["id"]); ?></h1>
		</header>
		<div class="slider-container">
			<?php echo do_shortcode('[nemus_slider id="'.$_GET["id"].'"]'); ?>
			<p><?php _e("This is just a preview of your awesome slider. Use the following shortcode to insert it to any page:", "nemus_slider") ?> <br/><input type="text" value="[nemus_slider id=&quot;<?php echo $_GET["id"]; ?>&quot;]" readonly="true" /></p>
		</div>
		<?php else: ?>
			<p><?php _e("Sorry, but this slider doesn't exists.", "nemus_slider") ?></p>
		<?php endif; ?>
	</div>
	<?php wp_footer(); ?>
</body>
</html>