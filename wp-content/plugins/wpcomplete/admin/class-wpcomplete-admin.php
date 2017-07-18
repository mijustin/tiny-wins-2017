<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpcomplete.co
 * @since      1.0.0
 * @last       2.0.0
 *
 * @package    WPComplete
 * @subpackage wpcomplete/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WPComplete
 * @subpackage wpcomplete/admin
 * @author     Zack Gilbert <zack@zackgilbert.com>
 */
class WPComplete_Admin extends WPComplete_Common {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The ID of this plugin.
   */
  protected $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of this plugin.
   */
  protected $version;

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Plugin_Name_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The WPComplete_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpcomplete-admin.css', array('wp-color-picker'), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Plugin_Name_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The WPComplete_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */
    wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpcomplete-admin.js', array( 'jquery', 'jquery-ui-autocomplete', 'wp-color-picker', 'inline-edit-post' ), $this->version, true );

    wp_localize_script( $this->plugin_name, 'WPComplete', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
  }

  /**
   * Add WPComplete specific dashboard widget
   *
   * @since  2.0.0
   * @last   2.0.0
   */
  public function add_dashboard_widget() {
    if ( get_option( $this->plugin_name . '_show_widget', 'true' ) === 'true' ) {
      wp_add_dashboard_widget( $this->plugin_name . '-course-statistics', 'WPComplete Course Statistics', array( $this, 'add_dashboard_widget_cb' ) );
    }
  }

  /**
   * Callback for WPComplete dashboard widget. Adds actual content.
   *
   * @since  2.0.0
   * @last   2.0.4
   */
  public function add_dashboard_widget_cb() {
    $posts = $this->get_completable_posts();
    if ( count( $posts ) <= 0 ) {
      include_once 'partials/wpcomplete-admin-widget-empty.php';
      return;
    }

    $courses = array();
    foreach ($this->get_course_names($posts) as $course) {
      // buttons count, number of users that started, number of users that finished
      $courses[$course] = array('buttons' => count($this->get_course_buttons($course, $posts)), 'started' => 0, 'finished' => 0);
    }
    $courses[''] = array('buttons' => count($this->get_course_buttons('', $posts)), 'started' => 0, 'finished' => 0);

    $selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
    // Get all users that are able to complete the post:
    $args = array('fields' => 'id');
    if ($selected_role != 'all') $args['role'] = $selected_role;
    $total_users = get_users($args);

    foreach ($total_users as $user_id) {
      foreach ($courses as $course => $course_info) {
        if ( $this->user_has_started_course( $user_id, $course, $posts ) ) {
          $courses[$course]['started']++;
        }
        if ( $this->user_has_completed_course( $user_id, $course, $posts ) ) {
          $courses[$course]['finished']++;
        }
      }
    }

    if ( count($courses) <= 1 ) {
      $courses['Entire Site'] = $courses[''];
    } else {
      if ( $courses['']['buttons'] > 0 ) {
        $courses[get_bloginfo( 'name' )] = $courses[''];
      }
    }
    unset($courses['']);

    include_once 'partials/wpcomplete-admin-widget.php';
  }

  /**
   * Add WPComplete specific page under the Settings submenu.
   *
   * @since  1.0.0
   */
  public function add_options_page() {
  
    $this->plugin_screen_hook_suffix = add_options_page(
      __( 'WPComplete Settings', $this->plugin_name ),
      __( 'WPComplete', $this->plugin_name ),
      'manage_options',
      $this->plugin_name,
      array( $this, 'display_settings_page' )
    );
  
  }

  /**
   * Render the WPComplete specific settings page for plugin.
   *
   * @since  1.0.0
   */
  public function display_settings_page() {
    include_once 'partials/wpcomplete-admin-display.php';
  }

  /**
   * Build all the settings for plugin on the WPComplete settings page.
   *
   * @since  1.0.0
   */
  public function register_settings() {
    // PREMIUM:
    register_setting( $this->plugin_name, $this->plugin_name . '_license_key', array( $this, 'sanitize_license' ) );

    // Section related to students:
    add_settings_section(
      $this->plugin_name . '_students',
      __( 'General Settings', $this->plugin_name ),
      array( $this, 'settings_section_cb' ),
      $this->plugin_name
    );

    add_settings_field(
      $this->plugin_name . '_role',
      __( 'Student Role Type', $this->plugin_name ),
      array( $this, 'settings_role_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_students',
      array( 'label_for' => $this->plugin_name . '_role' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_role', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_post_type',
      __( 'Lesson Content Type', $this->plugin_name ),
      array( $this, 'settings_post_type_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_students',
      array( 'label_for' => $this->plugin_name . '_post_type' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_post_type', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_auto_append',
      '',
      array( $this, 'settings_auto_append_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_students',
      array()
    );
    register_setting( $this->plugin_name, $this->plugin_name . '_auto_append', 'sanitize_text_field' );

    // Section related to the Mark as Complete button:
    add_settings_section(
      $this->plugin_name . '_incomplete_button',
      __( 'Mark Complete Button', $this->plugin_name ),
      array( $this, 'settings_section_cb' ),
      $this->plugin_name
    );

    add_settings_field(
      $this->plugin_name . '_incomplete_text',
      __( 'Button Text', $this->plugin_name ),
      array( $this, 'settings_incomplete_text_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_incomplete_button',
      array( 'label_for' => $this->plugin_name . '_incomplete_text' )
    );
    register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_text', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_incomplete_active_text',
      __( 'Saving Text', $this->plugin_name ),
      array( $this, 'settings_incomplete_active_text_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_incomplete_button',
      array( 'label_for' => $this->plugin_name . '_incomplete_active_text' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_active_text', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_incomplete_background',
      __( 'Button Color', $this->plugin_name ),
      array( $this, 'settings_incomplete_background_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_incomplete_button',
      array( 'label_for' => $this->plugin_name . '_incomplete_background' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_background', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_incomplete_color',
      __( 'Button Text Color', $this->plugin_name ),
      array( $this, 'settings_incomplete_color_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_incomplete_button',
      array( 'label_for' => $this->plugin_name . '_incomplete_color' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_color', 'sanitize_text_field' );

    // Section related to the Completed! button:
    add_settings_section(
      $this->plugin_name . '_completed_button',
      __( 'Completed Button', $this->plugin_name ),
      array( $this, 'settings_section_cb' ),
      $this->plugin_name
    );

    add_settings_field(
      $this->plugin_name . '_completed_text',
      __( 'Button Text', $this->plugin_name ),
      array( $this, 'settings_completed_text_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_completed_button',
      array( 'label_for' => $this->plugin_name . '_completed_text' )
    );
    register_setting( $this->plugin_name, $this->plugin_name . '_completed_text', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_completed_active_text',
      __( 'Saving Text', $this->plugin_name ),
      array( $this, 'settings_completed_active_text_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_completed_button',
      array( 'label_for' => $this->plugin_name . '_completed_active_text' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_completed_active_text', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_completed_background',
      __( 'Button Color', $this->plugin_name ),
      array( $this, 'settings_completed_background_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_completed_button',
      array( 'label_for' => $this->plugin_name . '_completed_background' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_completed_background', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_completed_color',
      __( 'Button Text Color', $this->plugin_name ),
      array( $this, 'settings_completed_color_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_completed_button',
      array( 'label_for' => $this->plugin_name . '_completed_color' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_completed_color', 'sanitize_text_field' );

    // PREMIUM: Section related to the graphs:
    add_settings_section(
      $this->plugin_name . '_graphs',
      __( 'Graph Settings', $this->plugin_name ),
      array( $this, 'settings_section_cb' ),
      $this->plugin_name
    );

    add_settings_field(
      $this->plugin_name . '_graph_primary',
      __( 'Primary Color', $this->plugin_name ),
      array( $this, 'settings_graph_primary_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_graphs',
      array( 'label_for' => $this->plugin_name . '_graph_primary' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_graph_primary', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_graph_secondary',
      __( 'Secondary Color', $this->plugin_name ),
      array( $this, 'settings_graph_secondary_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_graphs',
      array( 'label_for' => $this->plugin_name . '_graph_secondary' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_graph_secondary', 'sanitize_text_field' );

    // PREMIUM: Section related to advanced features:
    add_settings_section(
      $this->plugin_name . '_advanced',
      __( 'Advanced Settings', $this->plugin_name ),
      array( $this, 'settings_section_cb' ),
      $this->plugin_name
    );

    add_settings_field(
      $this->plugin_name . '_custom_styles',
      __( 'Custom Styles (CSS)', $this->plugin_name ),
      array( $this, 'settings_custom_styles_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_advanced',
      array( 'label_for' => $this->plugin_name . '_custom_styles' )
    );
    if (WPCOMPLETE_IS_ACTIVATED)
      register_setting( $this->plugin_name, $this->plugin_name . '_custom_styles', 'sanitize_text_field' );

    add_settings_field(
      $this->plugin_name . '_show_widget',
      '',
      array( $this, 'settings_show_widget_cb' ),
      $this->plugin_name,
      $this->plugin_name . '_advanced',
      array()
    );
    register_setting( $this->plugin_name, $this->plugin_name . '_show_widget', 'sanitize_text_field' );

  }

  /**
   * Render extra text for sections.
   *
   * @since  1.0.0
   */
  public function settings_section_cb() {
  }

  /**
   * Sanitation helper for license field.
   *
   * @since  1.0.0
   */
  public function sanitize_license( $new ) {
    $old = get_option( $this->plugin_name . '_license_key' );
    if ( $old && $old != $new ) {
      delete_option( $this->plugin_name . '_license_status' ); // new license has been entered, so must reactivate
      wp_cache_delete( $this->plugin_name . '_license_status' );
    }
    return $new;
  }

  /**
   * Render select menu for assigning which type of user roles should be tracked as students.
   *
   * @since  1.0.0
   */
  public function settings_role_cb() {
    $selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-role.php';
  }

  /**
   * Render select menu for assigning which type of user roles should be tracked as students.
   *
   * @since  1.0.3
   */
  public function settings_post_type_cb() {
    $selected_type = get_option( $this->plugin_name . '_post_type', 'page_post' );
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-post-type.php';
  }

  /**
   * Render checkbox for if should attempt to append [complete_button] shortcode if not found
   *
   * @since  1.3.0
   */
  public function settings_auto_append_cb() {
    $is_enabled = get_option( $this->plugin_name . '_auto_append', 'true' );
    $disabled = false;
    include 'partials/wpcomplete-admin-settings-auto-append.php';
  }

  /**
   * Render the Mark as Complete button text setting field.
   *
   * @since  1.0.0
   */
  public function settings_incomplete_text_cb() {
    $name = $this->plugin_name . '_incomplete_text';
    $text = get_option( $name, 'Mark as complete' );
    $class = '';
    $disabled = false;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * Render the Mark as Complete button active text setting field.
   *
   * @since  1.4.7
   */
  public function settings_incomplete_active_text_cb() {
    $name = $this->plugin_name . '_incomplete_active_text';
    $text = get_option( $name, 'Saving...' );
    $class = '';
    $disabled = false;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * Render the Mark as Complete button background color setting field.
   *
   * @since  1.0.0
   */
  public function settings_incomplete_background_cb() {
    $name = $this->plugin_name . '_incomplete_background';
    $text = get_option( $name, '#ff0000' );
    $class = 'color-picker';
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * Render the Mark as Complete button text color setting field.
   *
   * @since  1.0.0
   */
  public function settings_incomplete_color_cb() {
    $name = $this->plugin_name . '_incomplete_color';
    $text = get_option( $name, '#ffffff' );
    $class = 'color-picker';
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * Render the Completed! button text setting field.
   *
   * @since  1.0.0
   */
  public function settings_completed_text_cb() {
    $name = $this->plugin_name . '_completed_text';
    $text = get_option( $name, 'COMPLETED!' );
    $class = '';
    $disabled = false;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * Render the Completed! button active text setting field.
   *
   * @since  1.4.7
   */
  public function settings_completed_active_text_cb() {
    $name = $this->plugin_name . '_completed_active_text';
    $text = get_option( $name, 'Saving...' );
    $class = '';
    $disabled = false;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * Render the Completed! button background color setting field.
   *
   * @since  1.0.0
   */
  public function settings_completed_background_cb() {
    $name = $this->plugin_name . '_completed_background';
    $text = get_option( $name, '#666666' );
    $class = 'color-picker';
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * Render the Completed! button text color setting field.
   *
   * @since  1.0.0
   */
  public function settings_completed_color_cb() {
    $name = $this->plugin_name . '_completed_color';
    $text = get_option( $name, '#ffffff' );
    $class = 'color-picker';
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * PREMIUM:
   * Render graph primary color setting field.
   *
   * @since  1.0.0
   */
  public function settings_graph_primary_cb() {
    $name = $this->plugin_name . '_graph_primary';
    $text = get_option( $name, '#97a71d' );
    $class = 'color-picker';
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * PREMIUM:
   * Render graph secondary color setting field.
   *
   * @since  1.0.0
   */
  public function settings_graph_secondary_cb() {
    $name = $this->plugin_name . '_graph_secondary';
    $text = get_option( $name, '#ebebeb' );
    $class = 'color-picker';
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-input.php';
  }

  /**
   * PREMIUM:
   * Render textarea for custom styles.
   *
   * @since  1.2.0
   */
  public function settings_custom_styles_cb() {
    $name = $this->plugin_name . '_custom_styles';
    $default = '
li .wpc-lesson {} li .wpc-lesson-complete {} li .wpc-lesson-completed { opacity: .5; } li .wpc-lesson-completed:after { content: "✔"; margin-left: 5px; }
';
    $text = get_option( $name, $default );
    if ( empty( $text ) ) {
      $text = '
.wpc-lesson {} li .wpc-lesson-complete {} li .wpc-lesson-completed {}
';
    }
    $text = str_replace("} ", "}\n", $text);
    $disabled = !WPCOMPLETE_IS_ACTIVATED;

    include 'partials/wpcomplete-admin-settings-textarea.php';
  }

  /**
   * Render checkbox for if should show dashboard widget
   *
   * @since  2.1.0
   */
  public function settings_show_widget_cb() {
    $is_enabled = get_option( $this->plugin_name . '_show_widget', 'true' );
    $disabled = false;
    include 'partials/wpcomplete-admin-settings-widget.php';
  }

  /**
   * PREMIUM: 
   * Script used to activate license keys.
   *
   * @since  1.0.0
   */
  public function activate_license() {
    // Clear cache...
    delete_transient( WPCOMPLETE_PREFIX . '_license_status' );
    // listen for our activate button to be clicked
    if ( isset( $_POST[$this->plugin_name . '_license_activate'] ) ) {
      // run a quick security check 
      //if ( ! check_admin_referer( $this->plugin_name . '_license_nonce', $this->plugin_name . '_license_nonce' ) )  
      //  return; // get out if we didn't click the Activate button

      // retrieve the license from the database
      $license = trim( $_POST[ $this->plugin_name . '_license_key'] );

      // If posted license isn't the same as what's stored, store it.
      $current = get_option( $this->plugin_name . '_license_key');
      if ( $current != $license ) {
        update_option( $this->plugin_name . '_license_key', $license);
      }
        
      // data to send in our API request
      $api_params = array( 
        'edd_action' => 'activate_license', 
        'license'    => $license, 
        'item_name'  => urlencode( WPCOMPLETE_PRODUCT_NAME ),
        'url'        => home_url()
      );
      
      // Call the custom API.
      $response = wp_remote_post( WPCOMPLETE_STORE_URL, array(
        'timeout'   => 15,
        'sslverify' => false,
        'body'      => $api_params
      ) );

      $message = '';
      // make sure the response came back okay
      if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        $message =  ( is_wp_error( $response ) && $response->get_error_message() ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
      } else {
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        if ( false === $license_data->success ) {
          switch( $license_data->error ) {
            case 'expired' :
              $message = sprintf(
                __( 'Your license key expired on %s.' ),
                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
              );
              break;
            case 'revoked' :
              $message = __( 'Your license key has been disabled.' );
              break;
            case 'missing' :
              $message = __( 'Invalid license.' );
              break;
            case 'invalid' :
            case 'site_inactive' :
              $message = __( 'Your license is not active for this URL.' );
              break;
            case 'item_name_mismatch' :
              $message = sprintf( __( 'This appears to be an invalid license key for %s.' ), WPCOMPLETE_PRODUCT_NAME );
              break;
            case 'no_activations_left':
              $message = __( 'Your license key has reached its activation limit.' );
              break;
            default :
              $message = __( 'An error occurred, please try again.' );
              break;
          }
        }
      }
      // Check if anything passed on a message constituting a failure
      if ( ! empty( $message ) ) {
        $base_url = admin_url( 'options-general.php?page=' . $this->plugin_name );
        $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
        wp_redirect( $redirect );
        exit();
      }

      update_option( $this->plugin_name . '_license_status', $license_data->expires );
      wp_redirect( admin_url( 'options-general.php?page=' . $this->plugin_name ) );
      exit();

    }
  }

  /* END SETTINGS PAGE HELPERS */

  /**
   * Render the meta box for this plugin enabling completion functionality
   *
   * @since  1.0.0
   */
  public function add_completable_metabox() {
    $screens = $this->get_enabled_post_types();

    foreach ( $screens as $screen ) {
      add_meta_box(
        'completable',                                 // Unique ID
        __( 'WPComplete', $this->plugin_name ),        // Box title
        array( $this, 'add_completable_metabox_cb' ),  // Content callback
        $screen                                        // post type
      );
    }
  }

  /**
   * Callback which renders the actual html for completable metabox. Includes enabling completability and redirect url.
   *
   * @since  1.0.0
   * @last   2.0.0
   */
  public function add_completable_metabox_cb( $post ) {
    // get the variables we need to build the form:
    $completable = false;
    $redirect = array('title' => '', 'url' => '');
    $post_meta = get_post_meta( $post->ID, 'wpcomplete', true);
    $post_course = false;

    if ($post_meta) {
      $completable = true;
      $post_meta = json_decode($post_meta, true);
      $post_course = ( isset( $post_meta['course'] ) ) ? $post_meta['course'] : false;

      $redirect = ( isset( $post_meta['redirect'] ) ) ? $post_meta['redirect'] : array('title' => '', 'url' => '');
    }
    // include a nonce to ensure we can save:
    wp_nonce_field( $this->plugin_name, 'completable_nonce' );

    include 'partials/wpcomplete-admin-metabox.php';
  }

  /**
   * Add options to the bulk menu for posts and pages.
   *
   * @since  1.0.0
   */
  public function add_bulk_actions() {
    global $post_type;
 
    if ( in_array( $post_type, $this->get_enabled_post_types() ) ) {
      ?>
      <script defer type="text/javascript">
        jQuery(document).ready(function() {
          jQuery('<option>').val('completable').text("<?php _e('Can Complete', $this->plugin_name)?>").appendTo("select[name='action'],select[name='action2']");
<?php 
          $courses = $this->get_course_names();
          if ( count( $courses ) > 0 ) { ?>
            jQuery('<option>').val('course::true').text("<?php _e('Assign to: ' . get_bloginfo( 'name' ), $this->plugin_name)?>").appendTo("select[name='action'],select[name='action2']");
<?php       foreach ( $courses as $course_name ) { ?>
              jQuery('<option>').val('course::<?php echo $course_name; ?>').text("Assign to: <?php _e($course_name, $this->plugin_name)?>").appendTo("select[name='action'],select[name='action2']");
<?php       }
          } ?>
        
        });
      </script>
      <?php
    }
  }

  /**
   * Save script for saving an individual post/page, enabling it as completable
   * PREMIUM: and custom redirect url.
   *
   * @since  1.0.0
   * @last   2.0.3
   */
  public function save_completable( $post_id ) {
    if ( isset( $_POST['completable_nonce'] ) && isset( $_POST['post_type'] ) && isset( $_POST['wpcomplete'] ) && isset( $_POST['wpcomplete']['completable'] ) ) {
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        //echo '<!-- Autosave -->';
        return;
      } // end if

      // Verify that the input is coming from the proper form
      if ( ! wp_verify_nonce( $_POST['completable_nonce'], $this->plugin_name ) ) {
        //echo '<!-- NONCE FAILED -->';
        return;
      } // end if

      // Make sure the user has permissions to posts and pages
      if ( ! in_array( $_POST['post_type'], $this->get_enabled_post_types() ) ) {
        // echo '<!-- Post type isn\'t allowed to be marked as completable -->';
        return;
      }

      $is_completable = $_POST['wpcomplete']['completable'];
      // PREMIUM:
      $course_name = ( isset( $_POST['wpcomplete']['course-custom'] ) ) ? $_POST['wpcomplete']['course-custom'] : $_POST['wpcomplete']['course'];
      if ( empty( $course_name ) ) $course_name = 'true';
      $redirect_to = $_POST['wpcomplete']['completion-redirect-to'];
      $redirect_url = $_POST['wpcomplete']['completion-redirect-url'];
      $redirect = array('title' => $redirect_to, 'url' => $redirect_url);

      if ($is_completable == 'true') {

        $post_meta = array();
        
        if ( $course_name !== 'true' ) {
          $post_meta['course'] = $course_name;
        }

        // TODO: Can we optimze this any?
        $content = '';
        if (!empty($_POST['post_content'])) {
          $content = $_POST['post_content'];
        } else {
          foreach ($_POST as $key => $value) {
            if (is_array($value)) {
              foreach ($value as $key2 => $value2) {
                if ( !is_array( $value2 ) && false !== strpos( $value2, '[wpc' ) ) {
                  $content = $value2;
                  break;
                }
              }
            } else {
              if ( false !== strpos( $value, '[wpc' ) ) {
                $content = $value;
                break;
              }
            }
          }
        }
        $post_meta = $this->add_multiple_buttons_to_meta($post_id, $post_meta, $content);

        if ( !empty( $redirect_to ) ) {
          $post_meta['redirect'] = $redirect;
        }

        // Update it for this post.
        update_post_meta( $post_id, 'wpcomplete', json_encode( $post_meta, JSON_UNESCAPED_UNICODE ) );

      } else {

        // If the value exists, delete it.
        delete_post_meta( $post_id, 'wpcomplete' );
      
      }

      wp_cache_delete( "posts", 'wpcomplete' );

    }
  }

  /**
   * Save script for the bulk action that marks multiple pages/posts as completable.
   *
   * @since  1.0.0
   * @last   2.0.3
   */
  public function save_bulk_completable() {
    global $typenow;
    $post_type = $typenow;

    if ( in_array( $post_type, $this->get_enabled_post_types() ) && isset($_REQUEST['post']) ) {
      if ( (($_REQUEST['action'] == 'completable') || ($_REQUEST['action2'] == 'completable')) ) {
        // security check
        check_admin_referer( 'bulk-posts' );

        $action = ($_REQUEST['action'] == '-1') ? $_REQUEST['action2'] : $_REQUEST['action'];
        
        // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
        if ( isset($_REQUEST['post'] ) ) {
          $post_ids = array_map( 'intval', $_REQUEST['post'] );
        }
        
        if ( empty( $post_ids ) ) return;

        // this is based on wp-admin/edit.php
        $sendback = remove_query_arg( array('exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
        if ( ! $sendback )
          $sendback = admin_url( "edit.php?post_type=$post_type" );     

        // do the marking as complete!
        $marked = 0;
        foreach ( $post_ids as $post_id ) {
          $post_meta = get_post_meta( $post_id, 'wpcomplete', true );

          if ( ! $post_meta ) {
            // Enable the post because it wasn't previously.
            $post_meta = array();
            // Check to see if we need to add multiple buttons to database meta info:
            $post_content = get_post_field('post_content', $post_id);
            $post_meta = $this->add_multiple_buttons_to_meta($post_id, $post_meta, $post_content);

            update_post_meta( $post_id, 'wpcomplete', json_encode( $post_meta, JSON_UNESCAPED_UNICODE ) );
            $marked++;
          } else {
            // Already enabled... no need to do anything...
          }
        }

        $sendback = add_query_arg( array('completable' => $marked, 'ids' => join(',', $post_ids) ), $sendback );
        $sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );

        wp_cache_delete( "posts", 'wpcomplete' );

        wp_redirect( $sendback );
        exit();
      } else if ( ( (substr($_REQUEST['action'], 0, strlen('course::')) == 'course::') || (substr($_REQUEST['action2'], 0, strlen('course::')) == 'course::') ) ) {
        // security check
        check_admin_referer( 'bulk-posts' );

        $action = ($_REQUEST['action'] == '-1') ? $_REQUEST['action2'] : $_REQUEST['action'];
        list($action, $course_name) = explode("::", $action);
        
        // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
        if ( isset($_REQUEST['post'] ) ) {
          $post_ids = array_map( 'intval', $_REQUEST['post'] );
        }
        
        if ( empty( $post_ids ) ) return;

        // this is based on wp-admin/edit.php
        $sendback = remove_query_arg( array('exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
        if ( ! $sendback )
          $sendback = admin_url( "edit.php?post_type=$post_type" );     

        // do the marking as complete!
        $marked = 0;
        foreach ( $post_ids as $post_id ) {
          $post_meta = get_post_meta( $post_id, 'wpcomplete', true );
          
          if ( ! $post_meta ) {
            // Enable the post because it wasn't previously.
            $post_meta = array('course' => $course_name);
            // Check to see if we need to add multiple buttons to database meta info:
            $post_content = get_post_field('post_content', $post_id);
            $post_meta = $this->add_multiple_buttons_to_meta($post_id, $post_meta, $post_content);

            update_post_meta( $post_id, 'wpcomplete', json_encode( $post_meta, JSON_UNESCAPED_UNICODE ) );
            $marked++;
          } else {
            $post_meta = json_decode( $post_meta, true );
            if ( !isset($post_meta['course']) || ( $post_meta['course'] != $course_name ) ) {
              $post_meta['course'] = $course_name;
              // Check to see if we need to add multiple buttons to database meta info:
              $post_content = get_post_field('post_content', $post_id);
              $post_meta = $this->add_multiple_buttons_to_meta($post_id, $post_meta, $post_content);

              update_post_meta( $post_id, 'wpcomplete', json_encode( $post_meta, JSON_UNESCAPED_UNICODE ) );
              $marked++;
            } else {
              // Already in this course... no need to do anything...
            }
          }
        }

        $sendback = add_query_arg( array('course' => $marked, 'ids' => join(',', $post_ids) ), $sendback );
        $sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );

        wp_cache_delete( "posts", 'wpcomplete' );

        wp_redirect( $sendback );
        exit();
      }
    }
  }

  /**
   * Add a notice message for completed bulk actions.
   *
   * @since  1.0.0
   */
  public function show_bulk_action_notice() {
    global $post_type, $pagenow;
 
    if ( $pagenow == 'edit.php' && in_array( $post_type, $this->get_enabled_post_types() ) && isset($_REQUEST['completable']) && (int) $_REQUEST['completable']) {
      $message = sprintf( _n( 'Post marked completable by students.', '%s posts marked as completable by students.', $_REQUEST['completable'] ), number_format_i18n( $_REQUEST['completable'] ) );
      echo "<div class=\"updated\"><p>{$message}</p></div>";
    } else if ( $pagenow == 'edit.php' && in_array( $post_type, $this->get_enabled_post_types() ) && isset($_REQUEST['course']) && (int) $_REQUEST['course']) {
      $message = sprintf( _n( 'Post assigned to course.', '%s posts assigned to course.', $_REQUEST['course'] ), number_format_i18n( $_REQUEST['course'] ) );
      echo "<div class=\"updated\"><p>{$message}</p></div>";
    }
  }

  /**
   * PREMIUM:
   * If the license has not been configured properly, display an admin notice.
   *
   * @since  1.0.0
   */
  public function show_license_notice() {
    global $pagenow;

    if ( !WPCOMPLETE_IS_ACTIVATED ) {
      $msg = __( 'Please activate your license key to enable all WPComplete PRO features.', $this->plugin_name );

      include 'partials/wpcomplete-admin-license-notice.php'; 
    }
  }

  /**
   * Add the new custom column header, "User Completion" to pages and posts edit.php page.
   *
   * @since  1.0.0
   */
  public function add_custom_column_header( $columns ) {
    global $post_type;

    if (!$post_type) $post_type = $_POST['post_type'];

    if ( in_array( $post_type, $this->get_enabled_post_types() ) ) {

      if ( count( $this->get_course_names() ) > 0 ) {
        $columns = array_merge( $columns, array( 'completable-course' => __( 'Course Name', $this->plugin_name ) ) );
      }

      $columns = array_merge( $columns, array( 'completable' => __( 'Completion', $this->plugin_name ) ) );
    }

    return $columns;
  }

  /**
   * Add the values for each post/page of the new custom "Completion %" column.
   * If post/page isn't enabled to be completed, it shows — in column.
   * If wordpress install doesn't have any subscribers (students), it shows "0 Students".
   * Otherwise, it'll show the ratio and percentage of how many students have completed it.
   *
   * @since  1.0.0
   * @last   2.0.0
   */
  public function add_custom_column_value( $column_name, $post_id ) {
    if ( $column_name == 'completable-course' ) {
      $posts = $this->get_completable_posts();
    
      if ( isset( $posts[$post_id] ) ) {
        $course_name = (!isset($posts[$post_id]['course'])) ? get_bloginfo( 'name' ) : $posts[$post_id]['course'];
      } else {
        $course_name = '—';
      }

      echo '<div id="completable-course-' . $post_id . '">' . $course_name . '</div>';

    } else if ( $column_name == 'completable' ) {
      $posts = $this->get_completable_posts();
    
      if ( isset( $posts[$post_id] ) ) {
        $users_of_blog = count_users();
        $selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );

        if ( $selected_role == 'all' ) {
          $avail_users = $users_of_blog['total_users'];
        } else {
          $avail_users = ( isset( $users_of_blog['avail_roles'][$selected_role] ) ) ? $users_of_blog['avail_roles'][$selected_role] : 0;
        }

        if ($avail_users > 0) {
          $completion = '';

          $args = array('fields' => 'id');
          if ($selected_role != 'all') $args['role'] = $selected_role;
          //$args['meta_key'] = 'wpcomplete';
          $users = get_users($args);

          if ( isset( $posts[$post_id]['buttons'] ) ) {
            foreach ( $posts[$post_id]['buttons'] as $button) {
              // calculate how many of these users are completed...
              $completed_users = 0;
              foreach ($users as $user_id) {
                $user_completed = $this->get_user_completed($user_id);
                if ( isset( $user_completed[$button] ) ) {
                  $completed_users++;
                }
              }
              list($button_post_id, $button_id) = $this->extract_button_info($button);
              $button_name = ($button === ''.$post_id) ? 'Default Button' : "Button '$button_id'";
              $completion .= ('<a href="edit.php?page=wpcomplete-buttons&post_id=' . $post_id . '&button=' . $button . '">' . "$button_name: $completed_users/$avail_users Users (" . round(100 * ($completed_users / $avail_users), 1) . '%)</a><br>');
            }
          } else {
            // calculate how many of these users are completed...
            $completed_users = 0;
            foreach ($users as $user_id) {
              $user_completed = $this->get_user_completed($user_id);
              if ( isset( $user_completed[$post_id] ) ) {
                $completed_users++;
              }
            }
            $completion = '<a href="edit.php?page=wpcomplete-posts&post_id=' . $post_id . '">' . ("$completed_users/$avail_users Users (" . round(100 * ($completed_users / $avail_users), 1) . '%)')  . '</a>';
          }
        } else {
          $completion = "0 Users";
        }
        echo '<div id="completable-' . $post_id . '">' . $completion . '</div>';
      } else {
        echo '<div id="completable-' . $post_id . '">—</div>';
      }
    }
  }

  /**
   * 
   *
   * @since  1.4.0
   */
  public function add_post_completion_page() {
    add_submenu_page( 
      null, 
      __( 'Post Completion', $this->plugin_name ), 
      __( 'Post Completion', $this->plugin_name ), 
      'manage_options', 
      'wpcomplete-posts', 
      array( $this, 'render_post_completion_page' )
    );
  }

  /**
   * 
   *
   * @since  1.4.0
   * @last   2.0.0
   */
  public function render_post_completion_page() {
    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( ! $_GET['post_id'] ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( ! WPCOMPLETE_IS_ACTIVATED ) {
      wp_die( __( 'You only get access to this data once you activate your license.' ) );
    }
    // Get post info:
    $post_id = $_GET['post_id'];
    $post = get_post($post_id);

    $selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
    // Get all users that are able to complete the post:
    $args = array('fields' => 'all');
    if ($selected_role != 'all') $args['role'] = $selected_role;
    $total_users = get_users($args);
    
    $user_completed = array();
    foreach ($total_users as $user) {
      $user_completed_raw = $this->get_user_completed( $user->ID );
      if ( isset( $user_completed_raw[$post_id] ) && isset( $user_completed_raw[$post_id]['completed'] ) ) {
        if ($user_completed_raw[$post_id]['completed'] === true) {
          $user_completed_raw[$post_id]['completed'] = 'Yes';
        }
        $user_completed[$user->ID] = $user_completed_raw[$post_id]['completed'];
      }
    }

    include_once 'partials/wpcomplete-admin-post-completion.php';
  }

  /**
   * 
   *
   * @since  2.0.0
   * @last   2.0.0
   */
  public function add_button_completion_page() {
    add_submenu_page( 
      null, 
      __( 'Button Completion', $this->plugin_name ), 
      __( 'Button Completion', $this->plugin_name ), 
      'manage_options', 
      'wpcomplete-buttons', 
      array( $this, 'render_button_completion_page' )
    );
  }

  /**
   * 
   *
   * @since  2.0.0
   * @last   2.0.6
   */
  public function render_button_completion_page() {
    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( ! $_GET['post_id'] || ! $_GET['button'] ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( ! WPCOMPLETE_IS_ACTIVATED ) {
      wp_die( __( 'You only get access to this data once you activate your license.' ) );
    }
    // Get post info:
    $button_id = $_GET['button'];
    list($post_id, $button) = $this->extract_button_info($button_id);
    $post_id = $_GET['post_id'];
    $post = get_post($post_id);

    $selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
    // Get all users that are able to complete the post:
    $args = array('fields' => 'all');
    if ($selected_role != 'all') $args['role'] = $selected_role;
    $total_users = get_users($args);
    # TODO: confirm this update is fixed
    $total_posts = $this->get_completed_posts();//$this->get_buttons();

    $user_completed = array();
    foreach ($total_users as $user) {
      $user_completed_raw = $this->get_user_completed( $user->ID );
      //if ( in_array( $post_id, $total_posts ) && isset( $user_completed_raw[$button_id] ) && isset( $user_completed_raw[$button_id]['completed'] ) ) {
      if ( isset( $total_posts[ $post_id ] ) && isset( $user_completed_raw[$button_id] ) && isset( $user_completed_raw[$button_id]['completed'] ) ) {
        if ($user_completed_raw[$button_id]['completed'] === true) {
          $user_completed_raw[$button_id]['completed'] = 'Yes';
        }
        $user_completed[$user->ID] = $user_completed_raw[$button_id]['completed'];
      }
    }

    include_once 'partials/wpcomplete-admin-button-completion.php';
  }

  /**
   * Add custom field for quick edit of posts and pages.
   *
   * @since  1.0.0
   */
  public function add_custom_quick_edit( $column_name, $post_type ) {
    if ( in_array( $post_type, $this->get_enabled_post_types() ) ) {
      include 'partials/wpcomplete-admin-quickedit.php';
    }
  }

  /**
   * Add the new custom column header, "Lesson Completion" to users page.
   *
   * @since  1.0.0
   * @last   2.0.0
   */
  public function add_user_column_header( $columns ) {
    $posts = $this->get_completable_posts();
    if ( count($posts) > 0 ) {
      return array_merge( $columns, array( 'completable' => __( 'Completion', $this->plugin_name) ));
    } else {
      return $columns;
    }
  }

  /**
   * Add the values for each user of the new custom "Completion" column.
   * If user is not in a student role, it shows — in column.
   * Otherwise, it'll show the ratio and percentage of student's completion.
   *
   * @since  1.0.0
   * @last   2.0.0
   */
  public function add_user_column_value( $value, $column_name, $user_id ) {
    if ( $column_name == 'completable' ) {
      $posts = $this->get_completable_posts();
      $selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
      $user = get_userdata( $user_id );

      if ( ( $selected_role == 'all' ) || in_array( $selected_role, $user->roles ) ) {
        $total_posts = $this->get_buttons( array( 'course' => 'all' ) );
      
        $user_completed_raw = $this->get_user_completed( $user_id );
        $user_completed = array();
        foreach ($user_completed_raw as $button_id => $value) {
          if ( in_array( $button_id, $total_posts ) && isset( $value['completed'] ) ) {
            if ($value['completed'] === true) {
              $value['completed'] = 'Yes';
            }
            $user_completed[$button_id] = $value['completed'];
          }
        }
        $completed_posts = count($user_completed);
        $total_posts = count($total_posts);

        return '<div id="completable-' . $user_id . '"><a href="users.php?page=wpcomplete-users&user_id=' . $user_id . '">' . $completed_posts . '/' . $total_posts . ' Buttons (' . round(100 * ($completed_posts / $total_posts), 1) . '%)</a></div>';
      } else {
        return '<div id="completable-' . $user_id . '">—</div>';
      }
      
    }

  }

  /**
   * 
   *
   * @since  1.4.0
   */
  public function add_user_completion_page() {
    add_submenu_page( 
      null, 
      __( 'User Completion', $this->plugin_name ), 
      __( 'User Completion', $this->plugin_name ), 
      'manage_options', 
      'wpcomplete-users', 
      array( $this, 'render_user_completion_page' )
    );
  }

  /**
   * 
   *
   * @since  1.4.0
   * @last   2.0.0
   */
  public function render_user_completion_page() {
    if ( ! current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( ! $_GET['user_id'] ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( ! WPCOMPLETE_IS_ACTIVATED ) {
      wp_die( __( 'You only get access to this data once you activate your license.' ) );
    }

    $user_id = $_GET['user_id'];
    $total_posts = $this->get_buttons( array( 'course' => 'all' ) );
    $post_data = $this->get_completable_posts();

    $user_completed_raw = $this->get_user_completed( $user_id );
    $user_completed = array();
    foreach ($user_completed_raw as $button_id => $value) {
      if ( in_array( $button_id, $total_posts ) && isset( $value['completed'] ) ) {
        if ($value['completed'] === true) {
          $value['completed'] = 'Yes';
        }
        $user_completed[$button_id] = $value['completed'];
      }
    }

    $user = get_userdata( $user_id );

    include_once 'partials/wpcomplete-admin-user-completion.php';
  }

  /**
   * Returns an array of all specific courses that have been added to the database.
   *
   * @since  1.4.0
   * @last   2.0.4
   */
  public function get_course_names($posts = false) {
    if ( $course_names = wp_cache_get( 'course_names', 'wpcomplete' ) ) {
      return json_decode( $course_names, true );
    }

    $course_names = array();
    if ($posts === false) $posts = $this->get_completable_posts();
    foreach ($posts as $post_id => $info) {
      if ( isset($info['course']) && ( $info['course'] != 'true' ) && ( $info['course'] != get_bloginfo( 'name' ) ) ) {
        $course_names[] = $info['course'];
      }
    }
    $course_names = array_unique( $course_names );

    wp_cache_set( "course_names", json_encode( $course_names, JSON_UNESCAPED_UNICODE ), 'wpcomplete' );

    return $course_names;
  }

  /**
   * Add multiple buttons to a post's meta data if they exist in the post content.
   *
   * @since  2.0.0
   * @last   2.0.2
   */
  public function add_multiple_buttons_to_meta($post_id, $post_meta, $post_content = '') {
    // check if we need to store multiple buttons...
    if ( false !== strpos( $post_content, '[' ) ) {
      // Check for shortcodes to see what buttons we have...
      //$pattern = get_shortcode_regex();
      // We just want wpcomplete buttons... can ignore anything else:
      $pattern = '\[(\[?)(complete_button|wpc_complete_button|wpc_button|wpcomplete_button)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
      preg_match_all('/'.$pattern.'/s', $post_content, $matches);

      $shortcodes = array_unique( $matches[0] );

      // loop through and only keep button tags...
      $shortcodes = array_filter($shortcodes, function($value) {
        if (strstr($value, '[complete_button') !== false) return true;
        if (strstr($value, '[wpc_complete_button') !== false) return true;
        if (strstr($value, '[wpc_button') !== false) return true;
        if (strstr($value, '[wpcomplete_button') !== false) return true;

        return false;
      });

      $buttons = array();
      foreach ($shortcodes as $code) {
        // normalize button code...
        $code = str_replace('[complete_button', '[wpc_complete_button', $code);
        $code = str_replace('[wpc_button', '[wpc_complete_button', $code);
        $code = str_replace('[wpcomplete_button', '[wpc_complete_button', $code);

        $parsed_args = shortcode_parse_atts($code);

        if ( count( $parsed_args ) <= 1 ) {
          // no attributes: 
          $buttons[] = $this->get_button_id($post_id);
        } else {
          $unparsed_args = trim(str_replace(array('[wpc_complete_button', ']'), '', $code));
          $atts_array = new SimpleXMLElement("<element " . stripslashes($unparsed_args) . " />");
          $parsed_args = current((array) $atts_array);

          // cleanup attributes...
          $args = array();
          foreach( $parsed_args as $key => $value ) {
            if ( $key == 'name' ) $key = 'id';
            if ( $key == 'post' ) $key = 'post_id';
            $args[$key] = $value;
          }

          if ( isset( $args['post_id'] ) && !empty( $args['post_id'] ) ) {
            // Skip this button, because its not related to this post...
            continue;
          }

          // build button based on defaults...
          if ( isset( $args['id'] ) && !empty( $args['id'] ) ) {
            $buttons[] = $this->get_button_id($post_id, $args['id']);
          } else {
            $buttons[] = $this->get_button_id($post_id);
          }
        }
      }

      $buttons = array_unique($buttons);
      // Only store the buttons if we have more than 1 or its named different than the post id:
      if ( ( count( $buttons ) > 0 ) && ( $buttons != array( ''.$post_id ) ) ) {
        $post_meta['buttons'] = $buttons;
      }

    }

    return $post_meta;
  }

  /**
   * PREMIUM:
   * Autocomplete ajax lookup function. Given search criteria, returns matching posts and pages.
   *
   * @since  1.0.0
   */
  public function post_lookup() {
    // TODO: don't include current page in returned results.
    $term = strtolower( $_GET['term'] );
    $suggestions = array();
    // We want to allow redirect to ANY post type on completion, not just enabled ones:
    $args = array('s' => $term);

    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) {
      $loop->the_post();
      $suggestion = array();
      $suggestion['label'] = get_the_title() . " (" . ucwords(str_replace("_", " ", get_post_type( get_the_ID() ))) . " #" . get_the_ID() . ")";
      $suggestion['link'] = get_permalink();
      
      $suggestions[] = $suggestion;
    }
    
    wp_reset_query();
      
      
    $response = json_encode( $suggestions, JSON_UNESCAPED_UNICODE );
    echo $response;
    exit();
  }

  /**
   * Return boolean of whether user has started a course or not.
   *
   * @since  2.0.0
   * @last   2.0.4
   */
  public function user_has_started_course( $user_id, $course, $posts = false ) {
    $user_activity = $this->get_user_activity( $user_id );

    foreach ($user_activity as $post_id => $value) {
      if ( ''.$this->post_course($post_id, $posts) === $course ) {
        if ( isset( $value['first_seen'] ) || isset( $value['completed'] ) ) {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Return boolean of whether user has finished a course or not.
   *
   * @since  2.0.0
   * @last   2.0.4
   */
  public function user_has_completed_course( $user_id, $course, $posts = false ) {
    // Get the buttons just for this course:
    $buttons = $this->get_course_buttons($course, $posts);
    
    if ( count( $buttons ) <= 0 ) return false; 

    $user_activity = $this->get_user_activity( $user_id );
    foreach ($buttons as $button_id ) {
      // if user hasnt completed this button, they havent completed the course...
      if ( !isset( $user_activity[$button_id] ) || !isset( $user_activity[$button_id]['completed'] ) ) {
        return false;
      }
    }

    return true;
  }

}
