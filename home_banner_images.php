<?php
/*
Plugin Name: Home Page Banner Images
Plugin URI: http://www.gorsgraphics.com
Description: Plugin for uploading images for the scrolling banner on the home page
Author: Goran Halusa
Version: 1.0
Author URI: http://www.gorsgraphics.com
*/

add_action('admin_menu', 'hpbi_admin_actions');
add_action("admin_menu", 'hpbi_admin_scripts');
add_action('admin_menu', 'hpbi_admin_styles');
add_action('admin_menu', 'include_thickbox');

function hpbi_admin()
{  
    include('home_banner_images_admin.php');
}

function hpbi_admin_actions()
{
	add_submenu_page('themes.php',
		"Home Page Banner Images", 
		"Home Page Banner Images", 
		"manage_options", 
		"home_banner_images", 
		"hpbi_admin");
}

function hpbi_admin_scripts()
{
	wp_enqueue_script( "admin_menu", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).
		"/home_banner_images.js"), array( 'jquery' ) );
	wp_register_script( 'jquery_tablednd', path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).
		'/jquery.tablednd_0_5.js') );
    wp_enqueue_script( 'jquery_tablednd' );
}


function hpbi_admin_styles()
{
	wp_register_style( 'styles', path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).
		'/styles.css') );
    wp_enqueue_style( 'styles' );
}


function include_thickbox()
{
   add_thickbox();
}

/*
 * Modifying TinyMCE editor to remove unused items.
 */

/*
function customformatTinyMCE($init) {
	// Add block format elements you want to show in dropdown
	$init['theme_advanced_blockformats'] = 'p,pre,h1,h2,h3,h4';
	$init['theme_advanced_disable'] = 'strikethrough,underline,forecolor,justifyfull';

	return $init;
}

// Modify Tiny_MCE init
add_filter('tiny_mce_before_init', 'customformatTinyMCE' );
*/

/*
add_action("admin_head","load_custom_wp_tiny_mce");

function load_custom_wp_tiny_mce() {

	if (function_exists('wp_tiny_mce')) {
	
		add_filter('teeny_mce_before_init', create_function('$a', '
		    $a["theme"] = "advanced";
		    $a["skin"] = "wp_theme";
		    $a["height"] = "100";
		    $a["width"] = "460";
		    $a["onpageload"] = "";
		    $a["mode"] = "exact";
		    $a["elements"] = "url";
		    $a["editor_selector"] = "mceEditor";
		    $a["plugins"] = "safari,inlinepopups,spellchecker";
		
		    $a["forced_root_block"] = false;
		    $a["force_br_newlines"] = true;
		    $a["force_p_newlines"] = false;
		    $a["convert_newlines_to_brs"] = true;
	
			$a["theme_advanced_disable"] = "bold,italic,bullist,numlist,justifyleft,justifycenter,justifyright,blockquote,strikethrough,underline,forecolor,justifyfull,undo,redo";
	    return $a;'));
	
	 wp_tiny_mce(true);
	}

}

add_filter('admin_head','ShowTinyMCE');
function ShowTinyMCE() {
	// conditions here
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'jquery-color' );
	wp_print_scripts('editor');
	if (function_exists('add_thickbox')) add_thickbox();
	if (function_exists('wp_tiny_mce')) wp_tiny_mce(true,false);
	wp_admin_css();
	do_action("admin_print_styles-post-php");
	do_action('admin_print_styles');
}
add_filter('teeny_mce_before_init', create_function('$a', '
    $a["theme"] = "advanced";
    $a["skin"] = "wp_theme";
    $a["height"] = "100";
    $a["width"] = "460";
    $a["onpageload"] = "";
    $a["mode"] = "exact";
    $a["elements"] = "url";
    $a["editor_selector"] = "mceEditor";
    $a["plugins"] = "safari,inlinepopups,spellchecker";

    $a["forced_root_block"] = false;
    $a["force_br_newlines"] = true;
    $a["force_p_newlines"] = false;
    $a["convert_newlines_to_brs"] = true;

	$a["theme_advanced_disable"] = "bold,italic,bullist,numlist,justifyleft,justifycenter,justifyright,blockquote,strikethrough,underline,forecolor,justifyfull,undo,redo";
    return $a;'));

*/

?>