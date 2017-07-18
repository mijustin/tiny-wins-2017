<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpcomplete.co
 * @since      1.0.0
 *
 * @package    WPComplete
 * @subpackage wpcomplete/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WPComplete
 * @subpackage wpcomplete/public
 * @author     Zack Gilbert <zack@zackgilbert.com>
 */
class WPComplete_Public extends WPComplete_Common {

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
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpcomplete-public.css', array(), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {

    wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpcomplete-public.js', array( 'jquery' ), $this->version, true );

    $completion_nonce = wp_create_nonce( 'completion' );
    wp_localize_script( $this->plugin_name, 'wpcompletable', array( 
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'nonce' => $completion_nonce
    ) );

  }

  /**
   * Register the shortcode for [complete_button] for the public-facing side of the site.
   *
   * @since    1.0.0
   * @last     2.1.0
   */
  public function complete_button_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return; // should replace with button redirect to signup
    
    $post_id = get_the_ID();
    $button_id = '';
    if ( isset( $atts['id'] ) && !empty( $atts['id'] ) ) {
      $button_id = $atts['id'];
    } else if ( isset( $atts['name'] ) && !empty( $atts['name'] ) ) {
      $button_id = $atts['name'];
    }
    if ( isset( $atts['post_id'] ) && !empty( $atts['post_id'] ) ) {
      $post_id = $atts['post_id'];
    } else if ( isset( $atts['post'] ) && !empty( $atts['post'] ) ) {
      $post_id = $atts['post'];
    }
    
    if ( ! in_array( get_post_type( $post_id ), $this->get_enabled_post_types() ) ) return;
    if ( ! $this->post_can_complete( $post_id ) ) return;

    $unique_button_id = $this->get_button_id($post_id, $button_id);

    // Feature: Allow for custom button texts if it exists for this button:
    $button_text = get_option($this->plugin_name . '_incomplete_text', 'Mark as complete');
    if ( isset( $atts['text'] ) && !empty( $atts['text'] ) ) {
      $button_text = $atts['text'];
    }
    $completed_button_text = get_option($this->plugin_name . '_completed_text', 'COMPLETED');
    if ( isset( $atts['completed_text'] ) && !empty( $atts['completed_text'] ) ) {
      $completed_button_text = $atts['completed_text'];
    }

    // Start displaying button:
    ob_start();
    if ( $this->button_is_completed( $post_id, $button_id ) ) {
      include 'partials/wpcomplete-public-completed-button.php';
    } else {
      // Let's make sure we track that this user has seen this button...
      $user_activity = $this->get_user_activity();
      if ( !isset( $user_activity[$unique_button_id] ) ) $user_activity[$unique_button_id] = array();
      if ( !isset( $user_activity[$unique_button_id]['first_seen'] ) && !isset( $user_activity[$unique_button_id]['completed'] )  ) {
        $user_activity[$unique_button_id]['first_seen'] = date('Y-m-d H:i:s');
        $this->set_user_activity($user_activity);
      }

      include 'partials/wpcomplete-public-incomplete-button.php';
    }
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Register the shortcode for [progress_in_percentage] for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function progress_percentage_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return;
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
      if ( $post_course = $this->post_course( get_the_ID() ) ) $atts['course'] = $post_course;
    }
    $percentage = $this->get_percentage( $atts );

    return '<span class="wpcomplete-progress-percentage ' . $this->get_course_class( $atts ) . '">' . $percentage . "%" . '</span>';
  }

  /**
   * PREMIUM:
   * Register the shortcode for [progress_ratio] for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function progress_ratio_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return;
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
      if ( $post_course = $this->post_course( get_the_ID() ) ) $atts['course'] = $post_course;
    }
    $total_buttons = $this->get_buttons( $atts );
    // Don't show chart if there's no data to populate it:
    if ( count($total_buttons) <= 0 ) return;

    $user_completed = $this->get_user_completed();

    $completed_posts = array_intersect( $total_buttons, array_keys( $user_completed ) );

    return '<span class="wpcomplete-progress-ratio ' . $this->get_course_class($atts) . '">' . count($completed_posts) . "/" . count($total_buttons) . '</span>';
  }

  /**
   * PREMIUM:
   * Register the shortcode for [progress_graph] for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function progress_radial_graph_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return;
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
      if ( $post_course = $this->post_course( get_the_ID() ) ) $atts['course'] = $post_course;
    }
    $percentage = $this->get_percentage( $atts );
    
    ob_start();
    include 'partials/wpcomplete-public-radial-graph.php';
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Register the shortcode for [progress_graph] for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function progress_bar_graph_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return;
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
      if ( $post_course = $this->post_course(get_the_ID()) ) $atts['course'] = $post_course;
    }
    $percentage = $this->get_percentage( $atts );
    
    ob_start();
    include 'partials/wpcomplete-public-bar-graph.php';
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Add custom completion code to the end of post and page content
   *
   * @since    1.0.0
   */
  public function append_custom_styles() {
    if (!WPCOMPLETE_IS_ACTIVATED)
      return;

    $style_default = '
li .wpc-lesson-completed { opacity: .5; }
li .wpc-lesson-completed:after { content: "✔"; margin-left: 5px; }
';

    $complete_background = get_option( $this->plugin_name . '_incomplete_background', '#ff0000' );
    $complete_color = get_option( $this->plugin_name . '_incomplete_color', '#ffffff' );
    $completed_background = get_option( $this->plugin_name . '_completed_background', '#666666' );
    $completed_color = get_option( $this->plugin_name . '_completed_color', '#ffffff' );
    $graph_primary_color = get_option( $this->plugin_name . '_graph_primary', '#97a71d' );
    $graph_secondary_color = get_option( $this->plugin_name . '_graph_secondary', '#ebebeb' );
    $custom_styles = get_option( $this->plugin_name . '_custom_styles', $style_default );

    echo "<style type=\"text/css\"> a.wpc-complete { background: $complete_background; color: $complete_color; } a.wpc-completed { background: $completed_background; color: $completed_color; } .wpc-radial-progress, .wpc-bar-progress .wpc-progress-track { background-color: $graph_secondary_color; } .wpc-radial-progress .wpc-fill, .wpc-bar-progress .wpc-progress-fill { background-color: $graph_primary_color; } .wpc-radial-progress .wpc-numbers, .wpc-bar-progress .wpc-numbers { color: $graph_primary_color; } .wpc-bar-progress[data-progress=\"75\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"76\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"77\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"78\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"79\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"80\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"81\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"82\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"83\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"84\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"85\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"86\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"87\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"88\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"89\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"90\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"91\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"92\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"93\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"94\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"95\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"96\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"97\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"98\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"99\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"100\"] .wpc-numbers { color: $graph_secondary_color; } $custom_styles </style>";
  }

  /**
   * Add custom completion code to the end of post and page content
   *
   * @since    1.0.0
   */
  public function append_completion_code($content) {
    $post_type = get_post_type();

    // Don't append if it's been disabled:
    if ( get_option( $this->plugin_name . '_auto_append', 'true' ) == 'false' ) {
      return $content;
    }

    // Don't append if we aren't suppose to complete this type of post:
    if ( ! in_array( $post_type, $this->get_enabled_post_types() ) ) {
      return $content;
    }

    // See if this post is actually completable:
    if ( ! $this->post_can_complete(get_the_ID()) ) {
      return $content;
    }

    // Only append to body if we can't find any record of the button anywhere on the content:
    // NOTE: This doesn't fix the issue with OptimizePress... but it should help:
    if ( ( strpos( get_the_content(), '[complete_button' ) === false ) && ( strpos( get_the_content(), '[wpc_complete_button' ) === false ) && ( strpos( get_the_content(), '[wpc_button' ) === false ) && is_main_query() ) {
      if ( ( strpos( $content, '[complete_button' ) === false ) && ( strpos( $content, '[wpc_complete_button' ) === false ) && ( strpos( $content, '[wpc_button' ) === false ) && ( strpos( $content, 'class="wpc-button' ) === false ) ) {
        $content .= "\n\n[wpc_complete_button]";
      }
    }

    return $content;
  }

  /**
   * Handle trying to mark a lesson as completed as a logged out user... should just redirect to login.
   *
   * @since    1.0.0
   */
  public function nopriv_mark_completed() {
    $redirect = 'http' . ((isset($_SERVER['HTTPS']) && ('on' === $_SERVER['HTTPS'])) ? 's' : '') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      // return something indicating that the page should redirect to login?
      echo json_encode( array( 'redirect' => wp_login_url( $redirect ) ) );
      die();
    } else {
      wp_redirect( wp_login_url( $redirect ) );
      exit();
    }
  }

  /**
   * Handle marking a lesson as completed.
   *
   * @since    1.0.0
   * @last     2.1.0
   */
  public function mark_completed() {
    check_ajax_referer( 'completion' );

    // Get any existing lessons this user has completed:
    $user_completed = $this->get_user_activity();
    
    $unique_button_id = $_POST['button'];
    list($post_id, $button_id) = $this->extract_button_info($unique_button_id);
    
    $posts = $this->get_completable_posts();
    if ( isset( $button_id ) && ( !isset( $posts[$post_id]['buttons'] ) || !in_array( $unique_button_id, $posts[$post_id]['buttons'] ) ) ) {
      $post_meta = $posts[$post_id];
      if ( !isset( $post_meta['buttons'] ) ) $post_meta['buttons'] = array();
      $post_meta['buttons'][] = $unique_button_id;
      $posts[ $post_id ] = $post_meta;
      // Save changes:
      update_post_meta( $post_id, 'wpcomplete', json_encode( $post_meta, JSON_UNESCAPED_UNICODE ) );
      wp_cache_set( "posts", json_encode( $posts, JSON_UNESCAPED_UNICODE ), 'wpcomplete' );
    }

    // Mark this button as completed:
    if ( ! isset( $user_completed[ $unique_button_id ] ) ) $user_completed[ $unique_button_id ] = array();
    $user_completed[ $unique_button_id ]['completed'] = date('Y-m-d H:i:s');
    
    // Save to database/cache:
    $this->set_user_activity($user_completed);

    // Feature: Allow for custom button texts if it exists for this button:
    $button_text = get_option($this->plugin_name . '_incomplete_text', 'Mark as complete');
    if ( isset( $_POST['old_button_text'] ) && !empty( $_POST['old_button_text'] ) ) {
      $button_text = $_POST['old_button_text'];
    }
    $completed_button_text = get_option($this->plugin_name . '_completed_text', 'COMPLETED');
    if ( isset( $_POST['new_button_text'] ) && !empty( $_POST['new_button_text'] ) ) {
      $completed_button_text = $_POST['new_button_text'];
    }
    
    // update the button
    $updates_to_sendback = array( 
      ('.wpc-button-' . $this->get_button_class( $unique_button_id )) => $this->complete_button_cb( array( 'post_id' => $post_id, 'name' => $button_id, 'text' => $button_text, 'completed_text' => $completed_button_text ) )
    );

    $post_status = $this->post_completion_status( $post_id );

    //sleep ( rand ( 0, 2 ) );

    // PREMIUM: redirect student if teacher has added redirect url:
    if (WPCOMPLETE_IS_ACTIVATED) {
      // PREMIUM: get info for progress percentage:
      $atts = array();
      if ( $course = $this->post_course($post_id) ) {
        $atts['course'] = $course;
      }
      // Add lesson indicators
      $updates_to_sendback['lesson-' . $post_status] = $post_id;

      // Toggle content blocks:
      $updates_to_sendback['.wpc-content-button-' . $this->get_button_class( $unique_button_id ) . '-completed'] = 'show';
      $updates_to_sendback['.wpc-content-button-' . $this->get_button_class( $unique_button_id ) . '-incomplete'] = 'hide';
      if ( $this->post_completion_status($post_id) == 'completed' ) {
        $updates_to_sendback['.wpc-content-page-' . $post_id . '-completed'] = 'show';
        $updates_to_sendback['.wpc-content-page-' . $post_id . '-incomplete'] = 'hide';
      }
      if ( $this->course_completion_status($course) == 'completed' ) {
        $updates_to_sendback['.wpc-content-course-' . $this->get_course_class( $course ) . '-completed'] = 'show';
        $updates_to_sendback['.wpc-content-course-' . $this->get_course_class( $course ) . '-incomplete'] = 'hide';
      }

      // Update premium feature widgets:
      $updates_to_sendback['.wpcomplete-progress-ratio.' . $this->get_course_class($atts)] = $this->progress_ratio_cb( $atts );
      $updates_to_sendback['.wpcomplete-progress-percentage.' . $this->get_course_class($atts)] = $this->progress_percentage_cb( $atts );
      $updates_to_sendback['.wpcomplete-progress-ratio.all-courses'] = $this->progress_ratio_cb( array('course' => 'all') );
      $updates_to_sendback['.wpcomplete-progress-percentage.all-courses'] = $this->progress_percentage_cb( array('course' => 'all') );
      $updates_to_sendback['.' . $this->get_course_class($atts) . '[data-progress]'] = $this->get_percentage($atts);
      $updates_to_sendback['.all-courses[data-progress]'] = $this->get_percentage(array('course' => 'all') );

      // Add redirect if needed:
      $posts = $this->get_completable_posts();
      if ( ( $post_status == 'completed' ) && isset( $posts[ $post_id ] ) && isset( $posts[ $post_id ]['redirect'] ) ) {
        $redirect = $posts[ $post_id ]['redirect'];

        if ($redirect['url'] && !empty($redirect['url'])) {
          $updates_to_sendback['redirect'] = $redirect['url'];
        } else if (strpos($redirect['title'], 'http') === 0) {
          $updates_to_sendback['redirect'] = $redirect['title'];
        }
      }
    }

    // Add action for other plugins to hook in:
    do_action( 'wpcomplete_mark_completed', array('user_id' => get_current_user_id(), 'post_id' => $post_id, 'button_id' => $unique_button_id, 'post_status' => $post_status ) );

    // TODO: trigger course completion status!

    echo json_encode( $updates_to_sendback, JSON_UNESCAPED_UNICODE );
    die();
  }

  /**
   * Handle trying to mark a lesson as incomplete as a logged out user... should just redirect to login.
   *
   * @since    1.0.0
   */
  public function nopriv_mark_uncompleted() {
    $redirect = 'http' . ((isset($_SERVER['HTTPS']) && ('on' === $_SERVER['HTTPS'])) ? 's' : '') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
      // return something indicating that the page should redirect to login?
      echo json_encode( array( 'redirect' => wp_login_url( $redirect ) ) );
      die();
    } else {
      wp_redirect( wp_login_url( $redirect ) );
      exit();
    }
  }

  /**
   * Handle mark a lesson as incomplete.
   *
   * @since    1.0.0
   * @last     2.1.0
   */
  public function mark_uncompleted() {
    check_ajax_referer( 'completion' );
  
    // Get any existing lessons this user has completed:
    $user_completed = $this->get_user_activity();

    // Get any existing lessons this user has completed:
    $unique_button_id = $_POST['button'];
    list($post_id, $button_id) = $this->extract_button_info($unique_button_id);

    // Remove this post id:
    if ( ! isset( $user_completed[ $unique_button_id ] ) ) $user_completed[ $unique_button_id ] = array();
    unset($user_completed[ $unique_button_id ]['completed']);

    // and update the meta storage values:
    $this->set_user_activity($user_completed);

    // Feature: Allow for custom button texts if it exists for this button:
    $button_text = get_option($this->plugin_name . '_incomplete_text', 'Mark as complete');
    if ( isset( $_POST['new_button_text'] ) && !empty( $_POST['new_button_text'] ) ) {
      $button_text = $_POST['new_button_text'];
    }
    $completed_button_text = get_option($this->plugin_name . '_completed_text', 'COMPLETED');
    if ( isset( $_POST['old_button_text'] ) && !empty( $_POST['old_button_text'] ) ) {
      $completed_button_text = $_POST['old_button_text'];
    }
    
    $updates_to_sendback = array( 
      ('.wpc-button-' . $this->get_button_class( $unique_button_id )) => $this->complete_button_cb( array( 'post_id' => $post_id, 'name' => $button_id, 'text' => $button_text, 'completed_text' => $completed_button_text ) )
    );

    //sleep ( rand ( 0, 2 ) );

    // PREMIUM:
    if (WPCOMPLETE_IS_ACTIVATED) {
      // get info for progress percentage:
      $atts = array();
      if ( $course = $this->post_course($post_id) ) {
        $atts['course'] = $course;
      }
      // Add lesson indicators:
      $updates_to_sendback['lesson-' . $this->post_completion_status( $post_id )] = $post_id;

      // Toggle content blocks:
      $updates_to_sendback['.wpc-content-button-' . $this->get_button_class( $unique_button_id ) . '-incomplete'] = 'show';
      $updates_to_sendback['.wpc-content-button-' . $this->get_button_class( $unique_button_id ) . '-completed'] = 'hide';
      $updates_to_sendback['.wpc-content-page-' . $post_id . '-incomplete'] = 'show';
      $updates_to_sendback['.wpc-content-page-' . $post_id . '-completed'] = 'hide';
      $updates_to_sendback['.wpc-content-course-' . $this->get_course_class( $course ) . '-incomplete'] = 'show';
      $updates_to_sendback['.wpc-content-course-' . $this->get_course_class( $course ) . '-completed'] = 'hide';
      
      $updates_to_sendback['.wpcomplete-progress-ratio.' . $this->get_course_class($atts)] = $this->progress_ratio_cb( $atts );
      $updates_to_sendback['.wpcomplete-progress-percentage.' . $this->get_course_class($atts)] = $this->progress_percentage_cb( $atts );
      $updates_to_sendback['.wpcomplete-progress-ratio.all-courses'] = $this->progress_ratio_cb( array('course' => 'all') );
      $updates_to_sendback['.wpcomplete-progress-percentage.all-courses'] = $this->progress_percentage_cb( array('course' => 'all') );
      $updates_to_sendback['.' . $this->get_course_class($atts) . '[data-progress]'] = $this->get_percentage($atts);
      $updates_to_sendback['.all-courses[data-progress]'] = $this->get_percentage(array('course' => 'all') );
    }

    // Add action for other plugins to hook in:
    do_action( 'wpcomplete_mark_incomplete', array('user_id' => get_current_user_id(), 'post_id' => $post_id, 'button_id' => $unique_button_id, 'post_status' => $this->post_completion_status( $post_id ) ) );

    // TODO: trigger course completion status!
    
    // Send back new button:
    echo json_encode( $updates_to_sendback, JSON_UNESCAPED_UNICODE );
    die();
  }

  /**
   * Returns an array of all wordpress posts that are "completable".
   *
   * @since  1.2.0
   * @last   2.0.8
   */
  public function get_completable_list() {
    $nonce = $_POST['_ajax_nonce'];
    if ( empty( $_POST ) || !wp_verify_nonce( $nonce, 'completion' ) ) die( 'Security check' );

    $updates_to_sendback = array();
    
    if ( get_current_user_id() > 0 ) {
      $updates_to_sendback['timestamp'] = time();
      $updates_to_sendback['user'] = get_current_user_id();
      $total_posts = $this->get_completable_posts();
      foreach ( $total_posts as $post_id => $value ) {
        $updates_to_sendback[ get_permalink( $post_id ) ] = array(
          'id' => $post_id,
          'status' => $this->post_completion_status( $post_id ),
          'completed' => ( $this->post_completion_status( $post_id ) == 'completed' ) ? true : false
        );
      }
    }
    // Send back array of posts:
    wp_send_json( $updates_to_sendback );
  }

  /**
   * PREMIUM:
   * Handles the [wpc_completed_content] or [wpc_if_completed] shortcodes
   *
   * @since    2.0.0
   * @last     2.1.0
   */
  public function completed_content_cb($atts, $content = null, $tag = '') {
    if ( isset( $atts['course'] ) && !empty( $atts['course'] ) ) {
      return $this->if_course_completed_cb($atts, $content, $tag);
    } else if ( isset( $atts['page'] ) && !empty( $atts['page'] ) ) {
      return $this->if_page_completed_cb($atts, $content, $tag);
    } else {
      return $this->if_button_completed_cb($atts, $content, $tag);
    }
  }

  /**
   * PREMIUM:
   * Handles the [wpc_incomplete_content] or [wpc_if_incomplete] shortcodes
   *
   * @since    2.0.0
   * @last     2.1.0
   */
  public function incomplete_content_cb($atts, $content = null, $tag = '') {    
    $atts = array_change_key_case((array)$atts, CASE_LOWER); // normalize attribute keys, lowercase
    if ( isset( $atts['course'] ) ) {
      return $this->if_course_incomplete_cb($atts, $content, $tag);
    } else if ( isset( $atts['page'] ) ) {
      return $this->if_page_incomplete_cb($atts, $content, $tag);
    } else {
      return $this->if_button_incomplete_cb($atts, $content, $tag);
    }
  }

  /**
   * PREMIUM:
   * Handles the [wpc_if_button_completed] or [wpc_if_completed] shortcodes
   *
   * @since    2.1.0
   * @last     2.1.0
   */
  public function if_button_completed_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return; // dont show conditional content for logged out users
    $atts = array_change_key_case((array)$atts, CASE_LOWER); // normalize attribute keys, lowercase

    $post_id = get_the_ID();
    $button_id = '';
    if ( isset( $atts['id'] ) && !empty( $atts['id'] ) ) {
      if ( is_numeric($atts['id']) ) {
        $post_id = $atts['id'];
      } else {
        $button_id = $atts['id'];
      }
    } else if ( isset( $atts['name'] ) && !empty( $atts['name'] ) ) {
      $button_id = $atts['name'];
    } else if ( isset( $atts['button'] ) && !empty( $atts['button'] ) ) {
      $button_id = $atts['button'];
    }
    if ( isset( $atts['post'] ) && !empty( $atts['post'] ) ) {
      $post_id = $atts['post'];
    } else if ( isset( $atts['post_id'] ) && !empty( $atts['post_id'] ) ) {
      $post_id = $atts['post_id'];
    }
    
    // dont show conditional content if post isn't completable
    if ( ! in_array( get_post_type( $post_id ), $this->get_enabled_post_types() ) ) return;
    if ( ! $this->post_can_complete( $post_id ) ) return;

    $unique_button_id = $this->get_button_id($post_id, $button_id);
    $completion_status = 'completed';
    
    $user_completed = $this->get_user_completed();
    $should_hide = !isset( $user_completed[ $unique_button_id ] );
    
    ob_start();
    include 'partials/wpcomplete-public-content-button.php';
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Handles the [wpc_if_page_completed] or [wpc_if_completed page=""] shortcodes
   *
   * @since    2.1.0
   * @last     2.1.0
   */
  public function if_page_completed_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return; // dont show conditional content for logged out users
    $atts = array_change_key_case((array)$atts, CASE_LOWER); // normalize attribute keys, lowercase

    $post_id = get_the_ID();
    if ( isset( $atts['page'] ) && !empty( $atts['page'] ) ) {
      $post_id = $atts['page'];
    } else if ( isset( $atts['page_id'] ) && !empty( $atts['page_id'] ) ) {
      $post_id = $atts['page_id'];
    } else if ( isset( $atts['post'] ) && !empty( $atts['post'] ) ) {
      $post_id = $atts['post'];
    } else if ( isset( $atts['post_id'] ) && !empty( $atts['post_id'] ) ) {
      $post_id = $atts['post_id'];
    }
    
    // dont show conditional content if post isn't completable
    if ( ! in_array( get_post_type( $post_id ), $this->get_enabled_post_types() ) ) return;
    if ( ! $this->post_can_complete( $post_id ) ) return;
    
    $completion_status = 'completed';
    $should_hide = ( $this->post_completion_status($post_id) != 'completed' );
    
    ob_start();
    include 'partials/wpcomplete-public-content-page.php';
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Handles the [wpc_if_course_completed] or [wpc_if_completed course=""] shortcodes
   *
   * @since    2.1.0
   * @last     2.1.0
   */
  public function if_course_completed_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return; // dont show conditional content for logged out users
    $atts = array_change_key_case((array)$atts, CASE_LOWER); // normalize attribute keys, lowercase
    
    if ( isset( $atts['course'] ) && !empty( $atts['course'] ) ) {
      $course = $atts['course'];
    } else {
      $course = $this->post_course( get_the_ID() );
    }

    $completion_status = 'completed';
    $should_hide = ( $this->course_completion_status($course) != 'completed' );

    ob_start();
    include 'partials/wpcomplete-public-content-course.php';
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Handles the [wpc_if_incomplete] or [wpc_if_button_incomplete] shortcodes
   *
   * @since    2.1.0
   * @last     2.1.0
   */
  public function if_button_incomplete_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return; // dont show conditional content for logged out users
    $atts = array_change_key_case((array)$atts, CASE_LOWER); // normalize attribute keys, lowercase

    $post_id = get_the_ID();
    $button_id = '';
    if ( isset( $atts['id'] ) && !empty( $atts['id'] ) ) {
      if ( is_numeric($atts['id']) ) {
        $post_id = $atts['id'];
      } else {
        $button_id = $atts['id'];
      }
    } else if ( isset( $atts['name'] ) && !empty( $atts['name'] ) ) {
      $button_id = $atts['name'];
    }
    if ( isset( $atts['post'] ) && !empty( $atts['post'] ) ) {
      $post_id = $atts['post'];
    } else if ( isset( $atts['post_id'] ) && !empty( $atts['post_id'] ) ) {
      $post_id = $atts['post_id'];
    }
    
    // dont show conditional content if post isn't completable
    if ( ! in_array( get_post_type( $post_id ), $this->get_enabled_post_types() ) ) return;
    if ( ! $this->post_can_complete( $post_id ) ) return;

    $unique_button_id = $this->get_button_id($post_id, $button_id);
    $completion_status = 'incomplete';
    
    $user_completed = $this->get_user_completed();
    $should_hide = isset( $user_completed[ $unique_button_id ] );
    
    ob_start();
    include 'partials/wpcomplete-public-content-button.php';
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Handles the [wpc_if_page_incomplete] or [wpc_if_incomplete page=""] shortcodes
   *
   * @since    2.1.0
   * @last     2.1.0
   */
  public function if_page_incomplete_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return; // dont show conditional content for logged out users
    $atts = array_change_key_case((array)$atts, CASE_LOWER); // normalize attribute keys, lowercase

    $post_id = get_the_ID();
    if ( isset( $atts['page'] ) && !empty( $atts['page'] ) ) {
      $post_id = $atts['page'];
    } else if ( isset( $atts['page_id'] ) && !empty( $atts['page_id'] ) ) {
      $post_id = $atts['page_id'];
    } else if ( isset( $atts['post'] ) && !empty( $atts['post'] ) ) {
      $post_id = $atts['post'];
    } else if ( isset( $atts['post_id'] ) && !empty( $atts['post_id'] ) ) {
      $post_id = $atts['post_id'];
    }
    
    // dont show conditional content if post isn't completable
    if ( ! in_array( get_post_type( $post_id ), $this->get_enabled_post_types() ) ) return;
    if ( ! $this->post_can_complete( $post_id ) ) return;
    
    $completion_status = 'incomplete';
    $should_hide = ( $this->post_completion_status($post_id) == 'completed' );
    
    ob_start();
    include 'partials/wpcomplete-public-content-page.php';
    return ob_get_clean();
  }

  /**
   * PREMIUM:
   * Handles the [wpc_if_course_incomplete] or [wpc_if_incomplete course=""] shortcodes
   *
   * @since    2.1.0
   * @last     2.1.0
   */
  public function if_course_incomplete_cb($atts, $content = null, $tag = '') {
    if ( ! is_user_logged_in() ) return; // dont show conditional content for logged out users
    $atts = array_change_key_case((array)$atts, CASE_LOWER); // normalize attribute keys, lowercase
    
    if ( isset( $atts['course'] ) && !empty( $atts['course'] ) ) {
      $course = $atts['course'];
    } else {
      $course = $this->post_course( get_the_ID() );
    }

    $completion_status = 'incomplete';
    $should_hide = ( $this->course_completion_status($course) == 'completed' );
    
    ob_start();
    include 'partials/wpcomplete-public-content-course.php';
    return ob_get_clean();
  }

}
