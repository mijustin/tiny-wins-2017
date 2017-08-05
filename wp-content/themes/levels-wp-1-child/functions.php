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

/* Add some delicious shortcodes. */
include('shortcodes.php');

/* Customize the Visual Editor */
// Add WPcomplete custom styling to Visual Editor

// Callback function to insert 'styleselect' into the $buttons array
function my_mce_buttons_2( $buttons ) {
 array_unshift( $buttons, 'styleselect' );
 return $buttons;
}
// Register our callback to the appropriate filter
add_filter( 'mce_buttons_2', 'my_mce_buttons_2' );

// Callback function to filter the MCE settings
function my_mce_before_init_insert_formats( $init_array ) {

// Define the style_formats array
$style_formats = array(
 // Each array child is a format with it's own settings
 array(
   'title' => 'Heading 3 Course',
   'block' => 'H3',
   'classes' => 'course__heading',
   'wrapper' => true,
 ),
);
// Insert the array, JSON ENCODED, into 'style_formats'
$init_array['style_formats'] = json_encode( $style_formats );

return $init_array;

}
// Attach callback to 'tiny_mce_before_init'
add_filter( 'tiny_mce_before_init', 'my_mce_before_init_insert_formats' );

// Display CSS inside of Visual Editor in the Admin
function wpdocs_theme_add_editor_styles() {
    add_editor_style( 'custom-editor-style.css' );
}
add_action( 'admin_init', 'wpdocs_theme_add_editor_styles' );
?>
