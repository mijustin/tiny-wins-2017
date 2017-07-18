<?php
/**
 * Subscription settings
 *
 * @package     RCP\ConvertKit\Template
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Add the subscription field to the registration form
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_add_fields() {
	ob_start();
	if( rcp_convertkit_show_checkbox() ) {
		$settings = get_option( 'rcp_convertkit_settings' );

		if( isset( $settings['auto_subscribe'] ) ) {
			echo '<input id="rcp_convertkit_signup" name="rcp_convertkit_signup" type="hidden" value="true" />';
		} else {
			echo '<p>';
			echo '<input id="rcp_convertkit_signup" name="rcp_convertkit_signup" type="checkbox" checked="checked" />';
			echo '<label for="rcp_convertkit_signup">' . ( isset( $settings['signup_label'] ) && ! empty( $settings['signup_label'] ) ? $settings['signup_label'] : __( 'Signup for Newsletter', 'rcp-convertkit' ) ) . '</label>';
			echo '</p>';
		}
	}
	echo ob_get_clean();
}
add_action( 'rcp_before_registration_submit_field', 'rcp_convertkit_add_fields', 100 );