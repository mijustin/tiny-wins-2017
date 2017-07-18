<?php

/**
 * Common functionality of the plugin.
 *
 * @link       https://wpcomplete.co
 * @since      2.0.0
 *
 * @package    WPComplete
 * @subpackage wpcomplete/includes
 */

/**
 * The common functionality throughout the plugin.
 *
 * @package    WPComplete
 * @subpackage wpcomplete/includes
 * @author     Zack Gilbert <zack@zackgilbert.com>
 */
class WPComplete_Common {

  /**
   * The ID of this plugin.
   *
   * @since    2.0.0
   * @access   protected
   * @var      string    $plugin_name    The ID of this plugin.
   */
  protected $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    2.0.0
   * @access   protected
   * @var      string    $version    The current version of this plugin.
   */
  protected $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since      2.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
   * Returns an array of all wordpress post types that can be completed. This includes custom types.
   *
   * @since  2.0.0
   */
  public function get_enabled_post_types() {
    $post_type = get_option( $this->plugin_name . '_post_type', 'page_post' );
    if ( $post_type == 'page_post' ) {
      $screens = array();
      $screens['post'] = 'post';
      $screens['page'] = 'page';
    } else if ( $post_type == 'all' ) {
      $screens = get_post_types( array( '_builtin' => false ) );
      $screens['post'] = 'post';
      $screens['page'] = 'page';
    } else {
      $screens = array( $post_type );
    }
    return $screens;
  }

  /**
   * Returns an array of all the current user's completed posts.
   *
   * @since  2.0.0
   */
  public function get_user_completed($user_id = false) {
    if (!$user_id) $user_id = get_current_user_id();
    $user_completed = array();
    $user_activity = $this->get_user_activity($user_id);

    foreach ($user_activity as $button_id => $value) {
      if (isset($value['completed']) && !empty($value['completed'])) {
        $user_completed[$button_id] = $value;
      }
    }

    return $user_completed;
  }

  /**
   * Returns an array of all the current user's completion activity.
   *
   * @since  2.0.0
   */
  public function get_user_activity($user_id = false) {
    if (!$user_id) $user_id = get_current_user_id();
    // First: check if we have this cached already in the page request:
    if ( $user_completed_json = wp_cache_get( "user-" . $user_id, 'wpcomplete' ) ) {
      return json_decode( $user_completed_json, true );     
    }
    // Second: check the database for newest database structure (2.0 format):
    if ( $user_completed_json = get_user_meta( $user_id, 'wpcomplete', true ) ) {
      // Save new format into page request cache:
      wp_cache_set( "user-" . $user_id, $user_completed_json, 'wpcomplete' );
      return json_decode( $user_completed_json, true );
    }
    // Otherwise, we have the older format version... 
    // this should only run once per user...
    
    // Check for older database formats:
    $user_completed_json = get_user_meta( $user_id, 'wp_completed', true );
    $user_completed = ( $user_completed_json ) ? json_decode( $user_completed_json, true ) : array();
    // Convert old old format to new storage format if we didn't track time of completion:  
    if ( $user_completed == array_values( $user_completed ) ) {
      $new_array = array();
      foreach ( $user_completed as $p ) {
        $new_array[ $p ] = true;
      }
      $user_completed = $new_array;
    }

    // Convert to new 2.0 format:
    if ( ( count($user_completed) > 0 ) && ! is_array( current($user_completed) ) ) {
      // if it's not, correct it...
      $_user_completed = array();
      foreach ( $user_completed as $post_id => $value ) {
        $_user_completed[ $post_id ] = array(
          "completed" => $value
        );
      }
      $user_completed = $_user_completed;
    }

    $this->set_user_activity($user_completed, $user_id);
    //delete_user_meta( $user_id, 'wp_completed' );

    return $user_completed;
  }

  /**
   * Accepts new user completed data that should be stored in database. 
   * Returns an array of all the current user's completed posts.
   *
   * @since  2.0.0
   * @last   2.0.3
   */
  public function set_user_activity($data, $user_id = false) {
    if (!$user_id) $user_id = get_current_user_id();

    if (!is_string($data)) {
      $data = json_encode( $data, JSON_UNESCAPED_UNICODE );
    }

    // Update the database with the new data:
    $saved = update_user_meta( $user_id, 'wpcomplete', $data );

    // If database saved, we should try to cache it for the rest of the page request:
    if ( $saved ) {
      // Save new user completion data into page request cache:
      wp_cache_set( "user-" . $user_id, $data, 'wpcomplete' );
    }

    return $saved;
  }

  /**
   * Returns a string containing the normalized class name for the current course.
   *
   * @since  2.0.0
   * @last   2.0.0
   */
  public function get_button_class($button) {
    return str_replace( array(' ', '_'), '-', preg_replace("/[^a-z0-9 _-]/", '', strtolower( $button )) );
  }

  /**
   * Returns a string containing the normalized class name for the current course.
   *
   * @since  2.0.0
   * @last   2.0.6
   */
  public function get_course_class($atts = array()) {
    if ( isset( $atts['course'] ) ) {
      if ( strtolower( $atts['course'] ) == 'all') {
        return 'all-courses';
      } else {
        return str_replace(array(' ', '_', "'", '"', "&", ".", ":", ";", "/", "\\", "{", "}", "|", "`", "?", "!", "@", "#", "$", "%", "^", "*", "(", ")"), '-', strtolower($atts['course']));
      }
    } 
    return 'default-course';
  }

  /**
   * Returns a string containing the percentage of completed / total posts for a given course.
   *
   * @since  2.0.0
   */
  public function get_percentage($atts = array()) {
    $user_completed = $this->get_user_completed();
    $total_buttons = $this->get_buttons( $atts );

    if ( count($total_buttons) > 0 ) {
      $completed_posts = array_intersect( $total_buttons, array_keys( $user_completed ) );
      $percentage = round(100 * ( count($completed_posts) / count($total_buttons) ), 0);
    } else {
      $percentage = 0;
    }
    return $percentage;
  }

  /**
   * Helper method to query for all pages and posts that are completable.
   *
   * @since    2.0.0
   * @last     2.0.3
   */
  public function get_completable_posts( ) {
    global $wpdb;

    if ( $posts_json = wp_cache_get( 'posts', 'wpcomplete' ) ) {
      return json_decode( $posts_json, true );      
    }

    // New 2.0 database format:
    $r = $wpdb->get_results( $wpdb->prepare( "
        SELECT pm.post_id,pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s' 
        AND (p.post_status != '%s')
        AND (p.post_status != '%s')
        AND (p.post_type = '" . join("' OR p.post_type = '", $this->get_enabled_post_types()) . "')
    ", 'wpcomplete', 'trash', 'draft'), ARRAY_A );

    if ($r && ( count($r) > 0 ) ) {
      // clean up database to be in format we can easily handle:
      $posts = array();
      foreach ($r as $row) {
        $posts[$row['post_id']] = json_decode( $row['meta_value'], true );
      }
      wp_cache_set( "posts", json_encode( $posts, JSON_UNESCAPED_UNICODE ), 'wpcomplete' );
      return $posts;
    }

    // check for pre 2.0 format entries...
    $r = $wpdb->get_results( $wpdb->prepare( "
        SELECT pm.post_id,pm.meta_key,pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE (pm.meta_key = '%s' OR pm.meta_key = '%s') 
        AND (p.post_status != '%s')
        AND (p.post_status != '%s')
        AND (p.post_type = '" . join("' OR p.post_type = '", $this->get_enabled_post_types()) . "')
    ", 'completable', 'completion-redirect', 'trash', 'draft'), ARRAY_A );

    if ($r && ( count($r) > 0 ) ) {
      // if we have old content, rework it into new format...
      // {"4":{"course":"First Course", "redirect":{"title":"My Page. (Page #6)","url":"http://localhost:8888/WPComplete/working-site/my-page/"}}}
      $posts = array();
      foreach ($r as $post) {
        if ( ! isset( $posts[$post['post_id']] ) ) $posts[$post['post_id']] = array();
        if ($post['meta_key'] == 'completable') {
          if ($post['meta_value'] !== 'true') {
            $posts[$post['post_id']]['course'] = $post['meta_value'];
          }
        } else if ($post['meta_key'] == 'completion-redirect') {
          $posts[$post['post_id']]['redirect'] = json_decode( $post['meta_value'], true );
        }
      }
      // save new format to database
      foreach ($posts as $post_id => $value) {
        update_post_meta( $post_id, 'wpcomplete', json_encode( $value, JSON_UNESCAPED_UNICODE ) );
        // delete old format
      }
      wp_cache_set( "posts", json_encode( $posts, JSON_UNESCAPED_UNICODE ), 'wpcomplete' );
      
      return $posts;
    }

    return array();
  }

  /**
   * Returns all completable buttons for a specific course.
   *
   * @since    2.0.0
   * @last     2.0.4
   */
  public function get_buttons( $atts = array() ) {
    if ( !isset( $atts['course'] ) || ( $atts['course'] == get_bloginfo( 'name' ) ) ) $atts['course'] = '';
    return $this->get_course_buttons($atts['course']);
  }

  /**
   * Accepts info about button, and builds a button id.
   *
   * @since  2.0.0
   */
  public function get_button_id( $post_id, $button = '' ) {
    $button_id = '' . $post_id;
    if ( isset( $button ) && !empty( $button ) ) {
      $button_id .= ("-" . $button);
    }
    return $button_id;
  }

  /**
   * Get post id and button name from full button name.
   *
   * @since  2.0.0
   */
  public function extract_button_info( $button = '' ) {
    if (strpos($button, '-') !== false) {
      list($post_id, $button_id) = explode('-', $button, 2);
    } else {
      $post_id = $button;
      $button_id = '';
    }
    return array($post_id, $button_id);
  }

  /**
   * Returns a boolean for whether a button is completed or not.
   *
   * @since  2.0.0
   */
  public function button_is_completed( $post_id, $button ) {
    $user_completed = $this->get_user_completed();
    $button_id = $this->get_button_id( $post_id, $button );

    return isset( $user_completed[ $button_id ] ) && isset( $user_completed[ $button_id ]["completed"] );
  }

  /**
   * Returns a boolean for whether a post can be marked as completable or not.
   *
   * @since  2.0.0
   */
  public function post_can_complete($post_id) {
    $posts = $this->get_completable_posts();

    return isset($posts[$post_id]);
  }

  /**
   * Returns a string indicating a post's completion status. Can be: not-completable, incomplete, partial or completed
   *
   * @since  2.0.0
   */
  public function post_completion_status($post_id, $user_id = false) {
    if (!$user_id) $user_id = get_current_user_id();
    $posts = $this->get_completable_posts();

    if ( ! isset( $posts[$post_id] ) ) {
      // hmm... post isn't completable...
      return 'not-completable';
    }

    $post = $posts[$post_id];
    $user_completed = $this->get_user_completed($user_id);

    if ( isset( $post['buttons'] ) ) {
      $count = 0;
      foreach ( $post['buttons'] as $button ) {
        if ( isset( $user_completed[$button] ) ) {
          $count++;
        }
      }
      if ( $count <= 0 ) {
        return 'incomplete';
      } elseif ( $count < count($post['buttons']) ) {
        return 'partial';
      } else {
        return 'completed';
      }
    } else {
      if ( isset( $user_completed[$post_id] ) ) {
        return 'completed';
      } else {
        return 'incomplete';
      }
    }
  }

  /**
   * Returns a string indicating a course's completion status. Can be: incomplete, partial or completed
   *
   * @since  2.1.0
   */
  public function course_completion_status($course, $user_id = false) {
    if (!$user_id) $user_id = get_current_user_id();

    $buttons = $this->get_course_buttons($course);
    $user_completed = $this->get_user_completed($user_id);
    
    $count = 0;
    foreach ($buttons as $button) {
      if ( isset( $user_completed[$button] ) ) {
        $count++;
      }
    }

    if ( $count >= count($buttons) ) {
      return 'completed';
    } elseif ( $count <= 0 ) {
      return 'incomplete';
    } else {
      return 'partial';
    }
  }

  /**
   * Returns a string for the name of the course a post is associated with
   *
   * @since  2.0.0
   * @last   2.0.6
   */
  public function post_course($post_id, $posts = false) {
    if ($posts === false) $posts = $this->get_completable_posts();
    // make sure we actually have the post_id, and not the full button name:
    list($post_id, $button) = $this->extract_button_info($post_id);

    if ( ! isset( $posts[$post_id] ) ) {
      // hmm... post isn't completable...
      return false;
    }
    // Get specific post:
    $post = $posts[$post_id];
    // See if post has an assigned course:
    if ( isset( $post['course'] ) ) {
      return $post['course'];
    }
    // No course...
    return false;
  }

  /**
   * Return a boolean 
   *
   * @since  2.0.0
   * @last   2.0.0
   */
  public function post_has_multiple_buttons($post_id) {
    $posts = $this->get_completable_posts();

    if ( ! isset( $posts[$post_id] ) ) {
      // hmm... post isn't completable...
      return false;
    }
    // Get specific post:
    $post = $posts[$post_id];
    // See if post has multiple buttons:
    return isset( $post['buttons'] ) && ( count($post['buttons']) > 1 );
  }

  /**
   * Accepts a string of a course name (or empty).
   * Returns an array of buttons that belong to that course.
   *
   * @since  2.0.0
   * @last   2.0.4
   */
  public function get_course_buttons($course = '', $posts = false) {
    if ( $buttons_json = wp_cache_get( "course_buttons-" . $course, 'wpcomplete' ) ) {
      return json_decode( $buttons_json, true );      
    }

    if ( strtolower( $course ) == strtolower( get_bloginfo( 'name' ) ) ) {
      $course = '';
    }

    if ($posts === false) $posts = $this->get_completable_posts();
    $buttons = array();
    foreach ($posts as $post_id => $post) {
      if ( !empty($course) ) {
        if (strtolower($course) == 'all') { // All posts on entire site
          if (isset($post['buttons'])) {
            foreach ($post['buttons'] as $button) {
              $buttons[] = $button;
            }
          } else {
            $buttons[] = ''.$post_id;
          }
        // Specific course:
        } else if ( isset( $post['course'] ) && ( strtolower( $post['course'] ) == strtolower( $course ) ) ) {
          if (isset($post['buttons'])) {
            foreach ($post['buttons'] as $button) {
              $buttons[] = $button;
            }
          } else {
            $buttons[] = ''.$post_id;
          }
        }
      } else { // default SiteTitle Course
        if ( !isset( $post['course'] ) || ( strtolower( $post['course'] ) == strtolower( get_bloginfo( 'name' ) ) ) || ( $post['course'] === 'true' ) ) {
          if (isset($post['buttons'])) {
            foreach ($post['buttons'] as $button) {
              $buttons[] = $button;
            }
          } else {
            $buttons[] = ''.$post_id;
          }
        }
      }
    }

    wp_cache_set( "course_buttons-" . $course, json_encode( $buttons, JSON_UNESCAPED_UNICODE ), 'wpcomplete' );

    return $buttons;
  }

}
