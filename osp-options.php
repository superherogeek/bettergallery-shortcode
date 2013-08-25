<?php
// add the admin options page
add_action('admin_menu', 'osp_bettergallery_admin_page');

function osp_bettergallery_admin_page() {
	add_options_page('Better Gallery Shortcode', 'Better Gallery', 'manage_options', 'osp-bettergallery', 'osp_bettergallery_options_page');
	// No Need for a menu page.
	// add_menu_page('Better Gallery Shortcode', 'Better Gallery', 'administrator', __FILE__, 'osp-bettergallery',plugins_url('/images/osp.png', __FILE__));
}
?>
<?php // display the admin options page
function osp_bettergallery_options_page() {
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2>Better Gallery [shortcode]</h2>
<p>BetterGallery [shortcode] allows you to Paginate your Wordpress Galleries by just telling the shortcode how many pages you want your images to span.</p>
<form action="options.php" method="post">
	<?php settings_fields('osp_bettergallery_options'); ?>
	<?php do_settings_sections('osp-bettergallery'); ?>
	<input name="Submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
</form>
</div>
<?php
}?>
<?php 
// Better Gallery Settings
add_action('admin_init', 'osp_bettergallery_admin_init');

function osp_bettergallery_admin_init(){
	register_setting( 'osp_bettergallery_options', 'osp_bettergallery_options', 'osp_bettergallery_options_validate' );
	
	add_settings_section('plugin_main', 'Main Settings and Defaults', 'plugin_section_text', 'osp-bettergallery');

	add_settings_field('opt_images_per_page', 'Photos Per Page', 'osp_bettergallery_opt_images_per_page', 'osp-bettergallery', 'plugin_main');
	add_settings_field('opt_columns', 'Columns', 'osp_bettergallery_opt_columns', 'osp-bettergallery', 'plugin_main');
	add_settings_field('opt_hide_content', 'Hide Content', 'osp_bettergallery_opt_hide_content', 'osp-bettergallery', 'plugin_main');
}

?>
<?php function plugin_section_text() {
echo '<p>All of these settings can be modified in the shortcode itself with the exception of Hide Content.</p>';
} ?>
<?php 

function osp_bettergallery_opt_images_per_page() {
	$options = get_option('osp_bettergallery_options');
	echo "<input id='opt_images_per_page' name='osp_bettergallery_options[images_per_page]' size='3' type='text' value='{$options['images_per_page']}' />";
} 
function osp_bettergallery_opt_columns() {
	$options = get_option('osp_bettergallery_options');
	echo "<input id='opt_columns' name='osp_bettergallery_options[columns]' size='3' type='text' value='{$options['columns']}' />";
} 
function osp_bettergallery_opt_hide_content() {
	$options = get_option('osp_bettergallery_options');
	if(isset($options['hide_content'])){
		if($options['hide_content'] == '1') {
			$checked = "checked='checked'";
		} else {
			$checked = "";
		}
	}
	echo "<input id='opt_hide_content' name='osp_bettergallery_options[hide_content]' size='3' type='checkbox' value='{$options['hide_content']}' $checked />";

	echo "<p class='description'>If your gallery is paginated, load the <strong>gallery only</strong> after the first page.</p>";
} 
function osp_bettergallery_options_validate($input) {
	$options = get_option('osp_bettergallery_options');
	$options['images_per_page'] = trim($input['images_per_page']);
	$options['columns'] = trim($input['columns']);
	$options['hide_content'] = trim($input['hide_content']);

	if(isset($input['hide_content'])){
		
			$options['hide_content'] = "1";
		} else {
			$options['hide_content'] = "0";
		}
	

/* Validate
	if(!preg_match('/^[a-z0-9]{32}$/i', $options['text_string'])) {
		$options['text_string'] = 'hi';
	}
*/
	return $options;
}
?>
<?php 
// Set the icon
/* add_menu_page('Better Gallery Shortcode', 'Better Gallery', '', __FILE__, 'osp_bettergallery_options', get_stylesheet_directory_uri('stylesheet_directory')."/images/osp.png"); */

?>