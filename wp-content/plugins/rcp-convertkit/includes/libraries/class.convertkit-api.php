<?php
/**
 * ConvertKit API Handler
 *
 * @package     RCP\ConvertKit\API
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * ConvertKit API handler class
 *
 * @since       1.0.0
 */
class RCP_ConvertKit_API {


	/**
	 * @var         string $api_key The ConvertKit API key
	 * @since       1.0.0
	 */
	public $api_key;


	/**
	 * @var         string $api_secret The ConvertKit API secret
	 * @since       1.0.0
	 */
	public $api_secret;


	/**
	 * @var         string $api_url The ConvertKit API URL
	 * @since       1.0.0
	 */
	public $api_url = 'https://api.convertkit.com/v3/';


	/**
	 * @var         array $settings MailChimp Pro settings
	 * @since       1.0.0
	 */
	public $settings;


	/**
	 * @var         array $lists Available lists
	 * @since       1.0.0
	 */
	public $lists;


	/**
	 * @var         array $tags Available tags
	 * @since       1.0.0
	 */
	public $tags;


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function __construct() {
		$this->setup_api();

		// Maybe signup a user
		add_action( 'rcp_form_processing', array( $this, 'maybe_signup' ), 10, 2 );

		// Update ConvertKit when user changes email
		// ConvertKit API doesn't pass back the subscriber ID, so we can't currently update subscriptions
		//add_action( 'profile_update', array( $this, 'update_subscription' ), 10, 2 );
		//add_action( 'rcp_user_profile_updated', array( $this, 'update_subscription_from_frontend' ), 10, 2 );
	}


	/**
	 * Setup the API object
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      void
	 */
	private function setup_api() {
		if( ! $this->api_key || ! $this->api_secret ) {
			$this->settings = get_option( 'rcp_convertkit_settings' );

			if( ! empty( $this->settings['api_key'] ) && ! empty( $this->settings['api_secret'] ) ) {
				$this->api_key    = $this->settings['api_key'];
				$this->api_secret = $this->settings['api_secret'];
			}
		}
	}


	/**
	 * Retrieve the available lists
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      array
	 */
	public function get_lists() {
		if( $this->api_key ) {
			$lists = get_transient( 'rcp_convertkit_list_data' );
			$url   = $this->api_url . 'forms?api_key=' . $this->api_key;

			if( $lists === false ) {
				$request = wp_remote_get( $url );

				if( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) == 200 ) {
					$lists = json_decode( wp_remote_retrieve_body( $request ) );

					set_transient( 'rcp_convertkit_list_data', $lists, 24*24*24 );
				}
			}

			if( ! empty( $lists ) && ! empty( $lists->forms ) ) {
				foreach( $lists->forms as $key => $form ) {
					$this->lists[$form->id] = $form->name;
				}
			}
		}

		return (array) $this->lists;
	}


	/**
	 * Retrieve the available tags
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      array
	 */
	public function get_tags() {
		if( $this->api_key ) {
			delete_transient( 'rcp_convertkit_tag_data' );
			$tags = get_transient( 'rcp_convertkit_tag_data' );
			$url   = $this->api_url . 'tags?api_key=' . $this->api_key;

			if( $tags === false ) {
				$request = wp_remote_get( $url );

				if( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) == 200 ) {
					$tags = json_decode( wp_remote_retrieve_body( $request ) );

					set_transient( 'rcp_convertkit_tag_data', $tags, 24*24*24 );
				}
			}

			if( ! empty( $tags ) && ! empty( $tags->tags ) ) {
				foreach( $tags->tags as $key => $tag ) {
					$this->tags[$tag->id] = $tag->name;
				}
			}
		}

		return (array) $this->tags;
	}


	/**
	 * Retrieve the list to signup for
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       int $level_id The ID of the subscription level to lookup
	 * @return      string $list The MailChimp list to signup for
	 */
	public function get_list( $level_id ) {
		$lists = get_option( 'rcp_convertkit_subscription_lists' );

		if( is_array( $lists ) && array_key_exists( $level_id, $lists ) && $lists[$level_id] !== 'inherit' ) {
			$list = $lists[$level_id];
		} else {
			$list = $this->settings['saved_list'];
		}

		return apply_filters( 'rcp_convertkit_get_list', $list, $level_id );
	}


	/**
	 * Subscribe an email to MailChimp
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $email The email address to subscribe
	 * @param       string $name The name of the user to subscribe
	 * @return      bool True if added successfully, false otherwise
	 */
	public function subscribe( $email, $name ) {
		$return = false;
		$list   = $this->get_list( $_POST['rcp_level'] );
		$tags   = get_option( 'rcp_convertkit_subscription_tags' );

		$args = apply_filters( 'rcp_convertkit_subscribe_vars', array(
			'email' => $email,
			'name'  => $name
		) );

		$request = wp_remote_post(
			$this->api_url . 'forms/' . $list . '/subscribe?api_key=' . $this->api_key,
			array(
				'body'    => $args,
				'timeout' => 30
			)
		);

		if( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) == 200 ) {
			if( is_array( $tags ) && array_key_exists( $_POST['rcp_level'], $tags ) ) {
				foreach( $tags[$_POST['rcp_level']] as $tag ) {
					$request = wp_remote_post(
						$this->api_url . 'tags/' . $tag . '/subscribe?api_key=' . $this->api_key,
						array(
							'body'    => $args,
							'timeout' => 30
						)
					);
				}
			}

			return true;
		}

		return false;
    }


    /**
     * Maybe sign up a given user
     *
     * @access      public
     * @since       1.0.0
     * @param       array $posted The fields posted by the submission form
     * @param       int $user_id The ID of this user
     * @return      void
     */
    public function maybe_signup( $posted, $user_id ) {
        $email = false;

        if( $this->api_key ) {
			if( isset( $posted['rcp_convertkit_signup'] ) ) {
				if( is_user_logged_in() ) {
					$user_data  = get_userdata( $user_id );
					$email      = $user_data->user_email;
					$name       = '';

					if( $user_data->first_name ) {
						$name = $user_data->first_name;
					}

					if( $user_data->last_name ) {
						if( $name != '' ) {
							$name .= ' ';
						}

						$name .= $user_data->last_name;
					}

					if( $name == '' ) {
						$name = $user_data->user_login;
					}
				} else {
					$email = $posted['rcp_user_email'];
					$name  = '';

					if( $posted['rcp_user_first'] ) {
						$name = $posted['rcp_user_first'];
					}

					if( $posted['rcp_user_last'] ) {
						if( $name != '' ) {
							$name .= ' ';
						}

						$name .= $posted['rcp_user_last'];
					}

					if( $name == '' ) {
						$name = $posted['rcp_user_login'];
					}
				}
			}

			if( $email && $name ) {
				$subscribed = $this->subscribe( $email, $name );

				if( $subscribed !== false ) {
					update_user_meta( $user_id, 'rcp_subscribed_to_convertkit', $subscribed );
				}
			}
		}
	}


	/**
	 * Update subscription when user changes their email
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       int $user_id The ID of the user
	 * @param       object $old_user_data The old data for the user
	 * @return      void
	 */
	public function update_subscription( $user_id, $old_user_data ) {
		$settings = get_option( 'rcp_convertkit_settings' );

		if( $this->api_key ) {
			$user_data = get_userdata( $user_id );

			$new_email = $user_data->user_email;
			$old_email = $old_user_data->user_email;

			if( $new_email != $old_email ) {
				$subscriber = get_user_meta( $user_id, 'rcp_subscribed_to_convertkit', true );

				$args = apply_filters( 'rcp_convertkit_subscribe_vars', array(
					'email_address' => $new_email
				) );

				$request = wp_remote_request(
					$this->api_url . 'subscribers/' . $subscriber . '?api_secret=' . $this->api_secret,
					array(
						'method'  => 'PUT',
						'body'    => $args,
						'timeout' => 30
					)
				);
			}
		}
	}


	/**
	 * Update subscription when user changes their email from frontend form
	 *
	 * @access      public
	 * @since       1.0.
	 * @param       int $user_id The ID of the user
	 * @param       object $user_data The data for the user
	 * @return      void
	 */
	public function update_subscription_from_frontend( $user_id, $user_data ) {
		$settings = get_option( 'rcp_convertkit_settings' );

		if( $this->api_key ) {
			$email      = $user_data['user_email'];
			$subscriber = get_user_meta( $user_id, 'rcp_subscribed_to_convertkit', true );

			$args = apply_filters( 'rcp_convertkit_subscribe_vars', array(
				'email_address' => $email
			) );
			echo $this->api_url . 'subscribers/' . $subscriber . '?api_secret=' . $this->api_secret;

			$request = wp_remote_request(
				$this->api_url . 'subscribers/' . $subscriber . '?api_secret=' . $this->api_secret,
				array(
					'method'  => 'PUT',
					'body'    => $args,
					'timeout' => 30
				)
			);

			var_dump( $request ); exit;
		}
	}
}
