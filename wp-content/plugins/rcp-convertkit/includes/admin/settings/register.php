<?php
/**
 * Register settings
 *
 * @package     RCP\ConvertKit\Admin\Settings\Register
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Register the ConvertKit settings
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_register_settings() {
	register_setting( 'rcp_convertkit_settings_group', 'rcp_convertkit_settings', 'rcp_convertkit_sanitize_settings' );
}
add_action( 'admin_init', 'rcp_convertkit_register_settings', 100 );


/**
 * Sanitize settings
 *
 * @since       1.0.0
 * @param       array $settings The settings to save
 * @return      array $settings The settings to save
 */
function rcp_convertkit_sanitize_settings( $settings ) {
	$saved_settings = get_option( 'rcp_convertkit_settings' );

	// Delete transient on key change
	if( $saved_settings['api_key'] ) {
		if( trim( $saved_settings['api_key'] ) !== trim( $settings['api_key'] ) ) {
			delete_transient( 'rcp_convertkit_lists' );
		}
	}

	if( empty( $settings['license_key'] ) ) {
		delete_option( 'rcp_convertkit_license_status' );
	}

	if( ! empty( $_POST['rcp_convertkit_license_deactivate'] ) ) {
		rcp_convertkit_deactivate_license();
	} elseif( ! empty( $settings['license_key'] ) ) {
		rcp_convertkit_activate_license();
	}

	return $settings;
}


/**
 * Add the ConvertKit Pro menu item
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_admin_menu() {
	add_submenu_page(
		'rcp-members',
		__( 'ConvertKit Settings', 'rcp-convertkit' ),
		__( 'ConvertKit', 'rcp-convertkit' ),
		'manage_options',
		'rcp-convertkit',
		'rcp_convertkit_render_settings_page'
	);
}
add_action( 'admin_menu', 'rcp_convertkit_admin_menu', 100 );


/**
 * Render the settings page
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_render_settings_page() {
	$settings   = get_option( 'rcp_convertkit_settings' );
	$saved_list = isset( $settings['saved_list'] ) ? $settings['saved_list'] : false;
	$saved_tags = isset( $settings['saved_tags'] ) ? $settings['saved_tags'] : array();

	echo '<div class="wrap">';
	echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

	if( isset( $_REQUEST['updated'] ) && $_REQUEST['updated'] !== false ) {
		echo '<div class="updated fade"><p><strong>' . __( 'Options saved', 'rcp-convertkit' ) . '</strong></p></div>';
	}
	?>

	<form method="post" action="options.php" class="rcp_options_form">
		<?php settings_fields( 'rcp_convertkit_settings_group' ); ?>
		<?php
			$lists = rcp_convertkit()->api_helper->get_lists();
			$tags  = rcp_convertkit()->api_helper->get_tags();
		?>

		<table class="form-table">
			<tr>
				<th>
					<label for="rcp_convertkit_settings[license_key]"><?php _e( 'License Key', 'rcp-convertkit' ); ?></label>
				</th>
				<td>
					<input class="regular-text" type="text" id="rcp_convertkit_settings[license_key]" name="rcp_convertkit_settings[license_key]" value="<?php echo ( isset( $settings['license_key'] ) ? $settings['license_key'] : '' ); ?>" />
					<?php
					$status = get_option( 'rcp_convertkit_license_status' );

					if( $status !== false && $status == 'valid' ) {
						wp_nonce_field( 'rcp_convertkit_deactivate_license', 'rcp_convertkit_deactivate_license' );
						echo '<input type="submit" class="button-secondary" name="rcp_convertkit_license_deactivate" value="' . __( 'Deactivate License', 'rcp-convertkit' ) . '" />';
						echo '<span style="color:green">' . __( 'active', 'rcp-convertkit' ) . '</span>';
					} else {
						echo '<input type="submit" class="button-secondary" name="rcp_convertkit_license_activate" value="' . __( 'Activate License', 'rcp-convertkit' ) . '" />';
					}
					?>
					<div class="description"><?php printf( __( 'Enter your RCP ConvertKit license key. This is required for automatic updates and <a href="%s">support</a>.', 'rcp-convertkit' ), 'https://section214.com/contact' ); ?></div>
				</td>
			</tr>
			<tr>
				<th>
					<label for="rcp_convertkit_settings[api_key]"><?php _e( 'ConvertKit API Key', 'rcp-convertkit' ); ?></label>
				</th>
				<td>
					<input class="regular-text" type="text" id="rcp_convertkit_settings[api_key]" name="rcp_convertkit_settings[api_key]" value="<?php echo ( isset( $settings['api_key'] ) ? $settings['api_key'] : '' ); ?>" />
					<div class="description"><?php printf( __( 'Your ConvertKit API key can be found %s.', 'rcp-convertkit' ), '<a href="https://app.convertkit.com/account/edit" target="_blank">' . __( 'here', 'rcp-convertkit' ) . '</a>' ); ?></div>
				</td>
			</tr>
			<tr>
				<th>
					<label for="rcp_convertkit_settings[api_secret]"><?php _e( 'ConvertKit API Secret', 'rcp-convertkit' ); ?></label>
				</th>
				<td>
					<input class="regular-text" type="text" id="rcp_convertkit_settings[api_secret]" name="rcp_convertkit_settings[api_secret]" value="<?php echo ( isset( $settings['api_secret'] ) ? $settings['api_secret'] : '' ); ?>" />
					<div class="description"><?php printf( __( 'Your ConvertKit API secret can be found %s.', 'rcp-convertkit' ), '<a href="https://app.convertkit.com/account/edit" target="_blank">' . __( 'here', 'rcp-convertkit' ) . '</a>' ); ?></div>
				</td>
			</tr>
			<tr>
				<th>
					<label for="rcp_convertkit_settings[saved_list]"><?php _e( 'Default Form', 'rcp-convertkit' ); ?></label>
				</th>
				<td>
					<?php if( ! empty( $lists ) ) { ?>
						<select id="rcp_convertkit_settings[saved_list]" name="rcp_convertkit_settings[saved_list]">
							<?php
							foreach( $lists as $list_id => $list_name ) {
								echo '<option value="' . esc_attr( $list_id ) . '"' . selected( $saved_list, $list_id, false ) . '>' . esc_html( $list_name ) . '</option>';
							} ?>
						</select>
						<div class="description"><?php _e( 'Choose the form to subscribe users to if no per-level form is selected.', 'rcp-convertkit' ); ?></div>
					<?php } else { ?>
						<?php _e( 'Please enter valid API details to choose a default form.', 'rcp-convertkit' ); ?>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th>
					<label for="rcp_convertkit_settings[signup_label]"><?php _e( 'Form Label', 'rcp-convertkit' ); ?></label>
				</th>
				<td>
					<input class="regular-text" type="text" id="rcp_convertkit_settings[signup_label]" name="rcp_convertkit_settings[signup_label]" value="<?php echo ( isset( $settings['signup_label'] ) && ! empty( $settings['signup_label'] ) ? $settings['signup_label'] : __( 'Signup for Newsletter', 'rcp-convertkit' ) ); ?>" />
					<div class="description"><?php _e( 'Enter the label to be used for the "Signup for Newsletter" checkbox.', 'rcp-convertkit' ); ?></div>
				</td>
			</tr>
			<tr>
				<th>
					<label for="rcp_convertkit_settings[auto_subscribe]"><?php _e( 'Auto Subscribe', 'rcp-convertkit' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="rcp_convertkit_settings[auto_subscribe]" name="rcp_convertkit_settings[auto_subscribe]" value="1" <?php echo ( isset( $settings['auto_subscribe'] ) ? checked( $settings['auto_subscribe'], 1, false ) : '' ); ?> />
					<span class="description"><?php _e( 'Check to hide the subscribe checkbox and automatically subscribe users.', 'rcp-convertkit' ); ?></span>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'rcp-convertkit' ); ?>" />
		</p>
	</form>
	<?php
	echo '</div>';
}