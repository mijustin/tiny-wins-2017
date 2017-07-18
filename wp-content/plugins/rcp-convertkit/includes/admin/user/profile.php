<?php
/**
 * Subscription settings
 *
 * @package     RCP\ConvertKit\Admin\User\Profile
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Display a signed up notice if user has subscribed
 *
 * @since       1.0.0
 * @param       int $user_id The user ID of this user
 * @return      void
 */
function rcp_convertkit_display_signup_notice( $user_id ) {
    $signed_up = get_user_meta( $user_id, 'rcp_subscribed_to_convertkit', true );
    $signed_up = ( $signed_up ? __( 'Yes', 'rcp-convertkit' ) : __( 'No', 'rcp-convertkit' ) );

    echo '<tr class="form-field">';
    echo '<th scope="row" valign="top">' . __( 'ConvertKit', 'rcp-convertkit' ) . '</th>';
    echo '<td>' . $signed_up . '</td>';
    echo '</tr>';
}
add_action( 'rcp_edit_member_after', 'rcp_convertkit_display_signup_notice' );