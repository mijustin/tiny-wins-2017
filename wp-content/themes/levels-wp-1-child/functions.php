<?php
/* Pull in the parent theme's CSS and enqueue the child theme's CSS. */

function levels_child_enqueue_styles() {

    $parent_style = 'levels-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'levels_child_enqueue_styles' );



/* SHORTCODES */
/* Add some delicious shortcodes. */
include('shortcodes.php');



/* VISUAL EDITOR CUSTOMIZATIONS */
// Add WPcomplete custom styling to Visual Editor

// Callback function to insert 'styleselect' into the $buttons array
function my_mce_buttons_2( $buttons ) {
 array_unshift( $buttons, 'styleselect' );
 return $buttons;
}
// Register our callback to the appropriate filter
add_filter( 'mce_buttons', 'my_mce_buttons_2' );

// Callback function to filter the MCE settings
function levels_child_mce_before_init_insert_formats( $init_array ) {

// Define the style_formats array
$style_formats = array(
		// Each array child is a format with it's own settings
		array(
			'title' => 'Heading 3 Course',
			'selector' => 'h3',
			'classes' => 'course__heading',
			'wrapper' => true,

		),
	);
// Insert the array, JSON ENCODED, into 'style_formats'
  $init_array['style_formats'] = json_encode( $style_formats );
  return $init_array;
}
// Attach callback to 'tiny_mce_before_init'
add_filter( 'tiny_mce_before_init', 'levels_child_mce_before_init_insert_formats' );

// Display CSS inside of Visual Editor in the Admin
function levels_child_theme_add_editor_styles() {
    add_editor_style( 'custom-editor-style.css' );
}
add_action( 'admin_init', 'levels_child_theme_add_editor_styles' );



/* ADD SHORTCODES TO VISUAL EDITOR */
// Hooks your functions into the correct filters
function levels_child_add_mce_button() {
	// check user permissions
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	// check if WYSIWYG is enabled
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'levels_child_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'levels_child_register_mce_button' );
	}
}
add_action('admin_head', 'levels_child_add_mce_button');

// Declare script for new button
function levels_child_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['levels_child_mce_button'] = get_stylesheet_directory_uri() .'/js/mce-button.js';
	return $plugin_array;
}

// Register new button in the editor
function levels_child_register_mce_button( $buttons ) {
	array_push( $buttons, 'levels_child_mce_button' );
	return $buttons;
}

// Remove unwanted paragraphs from shortcodes

add_filter( 'the_content', 'levels_child_shortcode_empty_paragraph_fix' );
/**
 * @param string $content  String of HTML content.
 * @return string $content Amended string of HTML content.
 */
function levels_child_shortcode_empty_paragraph_fix( $content ) {

    $array = array(
        '<p>['    => '[',
        ']</p>'   => ']',
        ']<br />' => ']'
    );
    return strtr( $content, $array );

}

?>
