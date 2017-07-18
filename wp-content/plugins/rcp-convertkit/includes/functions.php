<?php
/**
 * Helper functions
 *
 * @package     RCP\ConvertKit\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Check if we should show the subscription field
 *
 * @since       1.0.0
 * @param       int $level A specific level to check
 * @return      bool $show True to show, false otherwise
 */
function rcp_convertkit_show_checkbox() {
	$settings = get_option( 'rcp_convertkit_settings' );
	$lists    = get_option( 'rcp_convertkit_subscription_lists' );
	$show     = false;

	if( ! empty( $settings['api_key'] ) ) {
		if( ! empty( $settings['saved_list'] ) ) {
			$show = true;
		}

		if( is_array( $lists ) ) {
			$show = true;
		}
	}

	return $show;
}


/**
 * License activation
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_activate_license() {
	if( ! isset( $_POST['rcp_convertkit_license_activate'] ) ) {
		return;
	}

	if( ! isset( $_POST['rcp_convertkit_settings']['license_key'] ) ) {
		return;
	}

	if( ! current_user_can( 'rcp_manage_settings' ) ) {
		return;
	}

	$status     = get_option( 'rcp_convertkit_license_status' );
	$license    = trim( $_POST['rcp_convertkit_settings']['license_key'] );

	if( $status != 'valid' ) {
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => 'RCP ConvertKit',
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post( 'https://section214.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if( is_wp_error( $response ) ) {
			return false;
		}

		// Decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'rcp_convertkit_license_status', $license_data->license );
		delete_transient( 'rcp_convertkit_license_check' );

		if( $license_data->license !== 'valid' ) {
			wp_die( sprintf( __( 'Your license key could not be activated. Error: %s', 'rcp-convertkit' ), $license_data->error ), __( 'Error', 'rcp-convertkit' ), array( 'response' => 401, 'back_link' => true ) );
		}
	}
}


/**
 * License deactivation
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_deactivate_license() {
	if( isset( $_POST['rcp_convertkit_license_deactivate'] ) ) {
		if( ! check_admin_referer( 'rcp_convertkit_deactivate_license', 'rcp_convertkit_deactivate_license' ) ) {
			return;
		}

		if( ! current_user_can( 'rcp_manage_settings' ) ) {
			return;
		}

		$settings   = get_option( 'rcp_convertkit_settings' );
		$license    = trim( $settings['license_key'] );

		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( 'RCP ConvertKit' ),
			'url'        => home_url()
		);

		$response = wp_remote_post( 'https://section214.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if( is_wp_error( $response ) ) {
			return false;
		}

		// Decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $license_data->license == 'deactivated' ) {
			delete_option( 'rcp_convertkit_license_status' );
			delete_transient( 'rcp_convertkit_license_check' );
		}
	}
}


/**
 * Check license
 *
 * @since       1.0.0
 * @return      void
 */
function rcp_convertkit_check_license() {
	// Don't fire when saving settings
	if( ! empty( $_POST['rcp_convertkit_settings'] ) ) {
		return;
	}

	$settings = get_option( 'rcp_convertkit_settings' );
	$status   = get_transient( 'rcp_convertkit_license_check' );

	if( $status === false && ! empty( $settings['license_key'] ) ) {
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => trim( $settings['license_key'] ),
			'item_name'  => urlencode( 'RCP ConvertKit' ),
			'url'        => home_url()
		);

		$response = wp_remote_post( 'https://section214.com', array( 'timeout' => 35, 'sslverify' => false, 'body' => $api_params ) );

		if( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		$settings['license_status'] = $license_data->license;

		update_option( 'rcp_convertkit_settings', $settings );

		set_transient( 'rcp_convertkit_license_check', $license_data->license, DAY_IN_SECONDS );

		$status = $license_data->license;

		if( $status !== 'valid' ) {
			delete_option( 'rcp_convertkit_license_status' );
		}
	}

	return $status;
}
add_action( 'admin_init', 'rcp_convertkit_check_license' );
