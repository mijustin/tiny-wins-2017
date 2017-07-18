<?php
/**
 * Subscription settings
 *
 * @package     RCP\ConvertKit\Admin\Subscription\MetaBoxes
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Add per-level setting fields
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_add_subscription_settings() {
	?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-convertkit-list"><?php _e( 'ConvertKit Form', 'rcp-convertkit' ); ?></label>
			</th>
			<td>
				<?php
				$lists = rcp_convertkit()->api_helper->get_lists();

				if( isset( $_GET['edit_subscription'] ) ) {
					$subscription_lists = get_option( 'rcp_convertkit_subscription_lists' );

					if( is_array( $subscription_lists ) && array_key_exists( $_GET['edit_subscription'], $subscription_lists ) ) {
						$saved_list = $subscription_lists[$_GET['edit_subscription']];
					} else {
						$saved_list = false;
					}
				} else {
					$saved_list = false;
				}

				if( $lists ) {
					?>
					<select name="convertkit-list" id="rcp-convertkit-list">
						<option value="inherit"<?php echo selected( $saved_list, 'inherit', false ); ?>><?php _e( 'Use System Default', 'rcp-convertkit' ); ?></option>
						<?php
						foreach( $lists as $list_id => $list_name ) {
							echo '<option value="' . esc_attr( $list_id ) . '"' . selected( $saved_list, $list_id, false ) . '>' . esc_html( $list_name ) . '</option>';
						} ?>
					</select>
					<p class="description"><?php _e( 'The ConvertKit form to subscribe users to at this level.', 'rcp-convertkit' ); ?></p>
					<?php
				} else {
					echo __( 'Enter valid API details to select a form.', 'rcp-convertkit' );
				}
				?>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<?php _e( 'ConvertKit Tags', 'rcp-convertkit' ); ?>
			</th>
			<td>
				<?php
				$tags = rcp_convertkit()->api_helper->get_tags();

				if( isset( $_GET['edit_subscription'] ) ) {
					$subscription_tags = get_option( 'rcp_convertkit_subscription_tags' );

					if( is_array( $subscription_tags ) && array_key_exists( $_GET['edit_subscription'], $subscription_tags ) ) {
						$saved_tags = $subscription_tags[$_GET['edit_subscription']];
					} else {
						$saved_tags = array();
					}
				} else {
					$saved_tags = array();
				}

				if( ! empty( $tags ) ) {
					foreach( $tags as $tag_id => $tag_name ) {
						echo '<input type="checkbox" name="convertkit-tags[' . esc_attr( $tag_id ) . ']" id="rcp-convertkit-tags[' . esc_attr( $tag_id ) . ']" value="' . esc_attr( $tag_id ) . '"' . checked( true, in_array( $tag_id, $saved_tags ), false ) . '>';
						echo '<label for="rcp-convertkit-tags[' . esc_attr( $tag_id ) . ']">' . esc_attr( $tag_name ) . '</label><br />';
					}
					?>
					<p class="description"><?php _e( 'The ConvertKit tags to subscribe users to at this level.', 'rcp-convertkit' ); ?></p>
					<?php
				} else {
					echo __( 'Enter valid API details to select tags.', 'rcp-convertkit' );
				}
				?>
			</td>
		</tr>
	<?php
}
add_action( 'rcp_add_subscription_form', 'rcp_convertkit_add_subscription_settings' );
add_action( 'rcp_edit_subscription_form', 'rcp_convertkit_add_subscription_settings' );


/**
 * Store the ConvertKit list in subscription meta
 *
 * @since       1.0.0
 * @param       int $level_id The subscription ID
 * @param       array $args Arguements passed to the action
 */
function rcp_convertkit_save_subscription( $level_id = 0, $args ) {
	if( ! empty( $_POST['convertkit-list'] ) ) {
		$saved_lists = get_option( 'rcp_convertkit_subscription_lists' );
		$saved_lists[$level_id] = $_POST['convertkit-list'];

		update_option( 'rcp_convertkit_subscription_lists', $saved_lists );
	}

	if( ! empty( $_POST['convertkit-tags'] ) ) {
		$saved_tags = get_option( 'rcp_convertkit_subscription_tags' );
		$saved_tags[$level_id] = $_POST['convertkit-tags'];

		update_option( 'rcp_convertkit_subscription_tags', $saved_tags );
	}
}
add_action( 'rcp_add_subscription', 'rcp_convertkit_save_subscription', 10, 2 );
add_action( 'rcp_edit_subscription_level', 'rcp_convertkit_save_subscription', 10, 2 );