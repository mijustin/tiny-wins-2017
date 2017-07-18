<?php

/**
 * Public-specific functionality.
 *
 * @since      1.0.0
 * @package    LogicHop
 * @subpackage LogicHop/includes
 */
class LogicHop_Public {

	/**
	 * The class that's responsible for core functionality & logic
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      LogicHop_Test    $logic    Core functionality & logic.
	 */
	private $logic;
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Array of active conditional CSS classes
	 *
	 * @since    2.1.0
	 * @access   public
	 * @var      array    $css    Array of active conditional CSS classes
	 */
	public $css;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    		The version of this plugin.
	 */
	public function __construct( $logic, $plugin_name, $version ) {
		$this->logic = $logic;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->css = array();
	}
	
	/**
	 * Javascript Goal logging.
	 *
	 * @since    1.0.0
	 */
	public function logichop_goal () {	
		if ($this->logic->is_valid_referrer()) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
				if (isset($_POST['goal'])) {
					$goal = (int) $_POST['goal'];
					$this->logic->update_goal($goal);
				}
			}
		}
		wp_die();
	}	
	
	/**
	 * Javascript Page tracking.
	 * Available only when js_tracking setting enabled.
	 *
	 * @since    1.0.0
	 */
	public function logichop_page_view () {	
		if ($this->logic->is_valid_referrer()) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
				$pid = isset($_POST['pid']) ? (int) $_POST['pid'] : false;
				$gid = isset($_POST['gid']) ? (int) $_POST['gid'] : false;
				$la = isset($_POST['la']) ? (int) $_POST['la'] : false;
				$lf = isset($_POST['lf']) ? $_POST['lf'] : 'every';
				$cid = isset($_POST['cid']) ? $_POST['cid'] : false;	// CONDITION
				$gcid = isset($_POST['gcid']) ? (int) $_POST['gcid'] : false;
				$rcid = isset($_POST['rcid']) ? $_POST['rcid'] : false;	// CONDITION
				$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : false;
				$referrer = isset($_POST['referrer']) ? $_POST['referrer'] : false;
				
				$response = new stdclass;
				$response->success = true;
				$response->redirect = false;
				
				if ($pid) $this->logic->update_data($pid, $referrer); // UPDATE PAGE
				if ($gid) $this->logic->update_goal($gid); // UPDATE GOAL
				if ($pid && $la && $lf) $this->logic->validate_lead_score($pid, $la, $lf); // UPDATE LEAD SCORE
				if ($cid && $gcid && $this->logic->condition_get($cid)) $this->logic->update_goal($gcid); // UPDATE CONDITIONAL GOAL
				if ($rcid && $redirect && $this->logic->condition_get($rcid)) {
					$response->redirect = $redirect; // RETURN REDIRECT
				}
				echo json_encode($response);
			}
		}
		wp_die();
	}
	
	/**
	 * Javascript Condition evaluation.
	 *
	 * @since    1.0.4
	 */
	public function logichop_condition () {	
		if ($this->logic->is_valid_referrer()) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
				$cid = isset($_POST['cid']) ? $_POST['cid'] : false;
			
				$response = new stdclass;
				$response->success = true;
				$response->cid = $cid;
				$response->condition = false;
				
				if ($cid && $this->logic->condition_get($cid)) {
					$response->condition = true;
				}
				echo json_encode($response);
			}
		}
		wp_die();
	}
	
	/**
	 * Javascript data variable return.
	 *
	 * @since    1.0.4
	 */
	public function logichop_data () {
		if ($this->logic->is_valid_referrer()) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
				$data_var = isset($_POST['data_var']) ? $_POST['data_var'] : false;
				
				$response 			= new stdclass;
				$response->success 	= false;
					
				if ($this->logic->js_variable_display()) {
					if (!$this->logic->is_variable_disabled($data_var)) {
						$response->success 	= true;
						$response->data_var = $data_var;
						$response->value 	= $this->logic->data_return($data_var);
					}
				}
				echo json_encode($response);
			}
		}
		wp_die();
	}
	
	/**
	 * Javascript parse Logic Hop data
	 *
	 * @since    1.5.0
	 */
	public function logichop_parse_logic () {
		if ($this->logic->is_valid_referrer()) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
				$data = isset($_POST['data']) ? $_POST['data'] : array();
				
				$response = new stdclass;
				$response->success 		= false;
				$response->conditions 	= array();
				$response->variables 	= array();
				$response->redirect 	= false;
				$response->css 			= '';
						
				if ( $this->logic->js_tracking() ) {
					$pid = isset($data['pid']) ? (int) $data['pid'] : false;
					$gid = isset($data['gid']) ? (int) $data['gid'] : false;
					$la = isset($data['la']) ? (int) $data['la'] : false;
					$lf = isset($data['lf']) ? $data['lf'] : 'every';
					$cid = isset($data['cid']) ? $data['cid'] : false;	// CONDITION
					$gcid = isset($data['gcid']) ? (int) $data['gcid'] : false;
					$rcid = isset($data['rcid']) ? $data['rcid'] : false;	// CONDITION
					$redirect = isset($data['redirect']) ? $data['redirect'] : false;
					$referrer = isset($data['referrer']) ? $data['referrer'] : false;
				
					if ($pid) $this->logic->update_data($pid, $referrer); // UPDATE PAGE
					if ($gid) $this->logic->update_goal($gid); // UPDATE GOAL
					if ($pid && $la && $lf) $this->logic->validate_lead_score($pid, $la, $lf); // UPDATE LEAD SCORE
					if ($cid && $gcid && $this->logic->condition_get($cid)) $this->logic->update_goal($gcid); // UPDATE CONDITIONAL GOAL
					if ($rcid && $redirect && $this->logic->condition_get($rcid)) $response->redirect = $redirect; // RETURN REDIRECT
					$response->success = true;
				}
				
				if ( isset($data['conditions']) ) { // EVALUATE CONDITIONS
					$response->success = true;
					foreach ($data['conditions'] as $cid) {
						$tmp = new stdclass();
						$tmp->cid 				= $cid;
						$tmp->condition 		= ($this->logic->condition_get($cid)) ? true : false;
						$response->conditions[] = $tmp;
					}
				}
				
				if (!$this->logic->disable_conditional_css()) {
					$this->generate_conditional_css();
					$response->css = implode(' ', $this->css);
				}
				
				if ( $this->logic->js_variable_display() ) { // DATA RETURN ENABLED?
					if ( isset($data['variables']) ) { // RETRIEVE VARIABLE DATA
						foreach ($data['variables'] as $data_var) { 
							if (!$this->logic->is_variable_disabled($data_var)) {
								$tmp = new stdclass();
								$tmp->data_var 			= $data_var;
								$tmp->value 			= $this->logic->data_return($data_var);
								$response->variables[] 	= $tmp;
								$response->success 		= true;
							}
						}
					}
				}
				
				echo json_encode($response);
			}
		}
		wp_die();
	}
	
	/**
	 * Catch widget and push through widget_redirected_callback
	 *
	 * @since    1.0.0
	 * @param    array		$params		Widget parameters
	 * @return    array		processed widget parameters
	 */
	public function widget_display_callback ($params) {	
		global $wp_registered_widgets;
		
		$id = $params[0]['widget_id'];
		$wp_registered_widgets[$id]['callback_wl_redirect'] = $wp_registered_widgets[$id]['callback'];
		$wp_registered_widgets[$id]['callback'] = array($this, 'widget_redirected_callback');
		return $params;
	}
	
	/**
	 * Filter widget content
	 *
	 * QUESTION: IS 'widget_' PREFIX A CONSTANT WHEN SETTING $widget_name
	 *
	 * @since    	1.0.0
	 * @return		false or string		Echos widget content
	 */
	public function widget_redirected_callback () {
		global $wp_registered_widgets, $wp_reset_query_is_done;
		
		$params = func_get_args();
		$id = $params[0]['widget_id'];
		
		$widget_name = 'widget_' . substr($id, 0, strrpos($id, '-', -1));
		
		$widget_settings = get_option($widget_name);
		
		$condition_id = (isset($widget_settings[$params[1]['number']]['logichop_widget'])) ? $widget_settings[$params[1]['number']]['logichop_widget'] : 0;
		$condition_not = (isset($widget_settings[$params[1]['number']]['logichop_widget_not'])) ? (boolean) $widget_settings[$params[1]['number']]['logichop_widget_not'] : false;
		
		if (!$this->logic->js_tracking()) { 
			// JAVASCRIPT TRACKING IS DISABLED
			if ($condition_id) {
				$display_widget = $this->logic->condition_get($condition_id);
				if ($condition_not) $display_widget = !$display_widget;
			
				if (!$display_widget) return false;
			}
				
			$callback = $wp_registered_widgets[$id]['callback_wl_redirect'];
			$wp_registered_widgets[$id]['callback'] = $callback;
		
			if (is_callable($callback)) {	
				ob_start();
					call_user_func_array($callback, $params);
					$widget_content = ob_get_contents();
				ob_end_clean();
				echo $widget_content;
			}
		} else { 
			 // JAVASCRIPT TRACKING IS ENABLED --> JS POST-LOAD EVALUATE WIDGETS IF CONDITION SET
			$callback = $wp_registered_widgets[$id]['callback_wl_redirect'];
			$wp_registered_widgets[$id]['callback'] = $callback;
			
			if (is_callable($callback)) {	
				ob_start();
					if ($condition_id) {
						printf('<span class="logichop-js" style="display: none;" data-cid="%d" %s data-event="%s">', 
									$condition_id, 
									$condition_not ? 'data-not="true"' : '',
									'fadeIn'
								);
					}
						call_user_func_array($callback, $params);
					if ($condition_id) echo '</span>';
					$widget_content = ob_get_contents();
				ob_end_clean();
				echo $widget_content;
			}
		}
	}
	
	/**
	 * Register shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes () {
		add_shortcode( 'logichop_condition', array($this, 'shortcode_conditional_display') );
		add_shortcode( 'logichop_condition_not', array($this, 'shortcode_conditional_not_display') );
		add_shortcode( 'logichop_goal', array($this, 'shortcode_goal_embed') );
		add_shortcode( 'logichop_conditional_goal', array($this, 'shortcode_conditional_goal_embed') );
		add_shortcode( 'logichop_conditional_redirect', array($this, 'shortcode_conditional_redirect') );
		add_shortcode( 'logichop_data', array($this, 'shortcode_logichop_data_display') );
		add_shortcode( 'logichop_data_input', array($this, 'shortcode_logichop_data_input_display') );
		
		do_action('logichop_register_shortcodes', $this);
	}
	
	/**
	 * Conditional content display shortcode - If condition met
	 *
	 * @since    1.0.0
	 * @param  		array	$atts		Shortcode attributes
	 * @param  		string	$content	Shortcode content
	 * @return  	null or do_shortcode()		Formatted shortcode content
	 */
	public function shortcode_conditional_display ($atts = null, $content = null) {
		
		$condition_id = (isset($atts['id'])) ? $atts['id'] : null;
		if ($this->logic->condition_get($condition_id)) return do_shortcode($content);
		return;
	}
	
	/**
	 * Conditional content display shortcode - If condition not met
	 *
	 * @since    1.0.0
	 * @param  		array	$atts		Shortcode attributes
	 * @param  		string	$content	Shortcode content
	 * @return  	null or do_shortcode()		Formatted shortcode content
	 */
	public function shortcode_conditional_not_display ($atts = null, $content = null) {
		
		$condition_id = (isset($atts['id'])) ? $atts['id'] : null;
		if (!$this->logic->condition_get($condition_id)) return do_shortcode($content);
		return;
	}
	
	/**
	 * Goal shortcode
	 * Embeds Goal Javascript
	 * Javascript option instead of using page-level logic
	 *
	 * @since    1.0.0
	 * @param  		array	$atts		Shortcode attributes
	 * @param  		string	$content	Shortcode content
	 * @return   null		
	 */
	public function shortcode_goal_embed ($atts = null, $content = null) {
		
		$goal_id = (isset($atts['goal'])) ? (int) $atts['goal'] : null;
		if ($goal_id) {
			printf('<script>logichop_goal(%d);</script>', $goal_id);
		}
		return;
	}
	
	/**
	 * Conditional Goal shortcode
	 * Embeds Goal Javascript based on the outcome of a condition
	 * Javascript option instead of using page-level logic
	 *
	 * @since    	1.0.0
	 * @param  		array	$atts		Shortcode attributes
	 * @param  		string	$content	Shortcode content
	 * @return    	null		
	 */
	public function shortcode_conditional_goal_embed ($atts = null, $content = null) {
		
		$goal_id = (isset($atts['goal'])) ? (int) $atts['goal'] : null;
		$condition_id = (isset($atts['id'])) ? $atts['id'] : null;
		
		if ($goal_id && $this->logic->condition_get($condition_id)) {
			printf('<script>logichop_goal(%d);</script>', $goal_id);
		}
		return;
	}
	
	/**
	 * Conditional redirect shortcode
	 *
	 * @since    	1.0.0
	 * @param  		array	$atts		Shortcode attributes
	 * @param  		string	$content	Shortcode content
	 * @return    	null or redirect	Based on outcome of conditional logic
	 */
	public function shortcode_conditional_redirect ($atts = null, $content = null) {
		
		$condition_id = (isset($atts['id'])) ? $atts['id'] : null;
		$redirect = (isset($atts['redirect'])) ? $atts['redirect'] : null;
		
		if ($redirect && $this->logic->condition_get($condition_id)) {
			wp_redirect($redirect, 302);
			exit;
		}
		return;
	}
	
	/**
	 * Logic Hop data display shortcode 
	 *
	 * Data extracted from $_SESSION['logichop-data']
	 * Accepts [logichop_data vars=""]
	 * Parameter 'vars' accepts '.' delimited object elements and ':' delimited array elements
	 * Example: Date.DateTime OR QueryStore:ref 
	 *
	 * @since    	1.0.9
	 * @param  		array	$atts		Shortcode attributes
	 * @return  	null or content		Formatted shortcode content
	 */
	public function shortcode_logichop_data_display ($atts = null) {
		$var = (isset($atts['var'])) ? $atts['var'] : null;
		$case = (isset($atts['case'])) ? $atts['case'] : '';
		$spaces = (isset($atts['spaces'])) ? $atts['spaces'] : false;
		if ($var) {
			$data = $this->logic->data_return($var);
			if ($case == 'lower') $data = strtolower($data);
			if ($case == 'upper') $data = strtoupper($data);
			if ($spaces) $data = str_replace(' ', $spaces, $data);
			return $data;
		}
		return;
	}
	
	/**
	 * Logic Hop data display <input> shortcode 
	 *
	 * Data extracted from $_SESSION['logichop-data']
	 * Accepts [logichop_data_input vars="" type="" id="" class=""]
	 * Parameter 'vars' accepts '.' delimited object elements and ':' delimited array elements
	 * Example: Date.DateTime OR QueryStore:ref 
	 *
	 * @since    	1.5.0
	 * @param  		array	$atts		Shortcode attributes
	 * @return  	null or content		Formatted shortcode content
	 */
	public function shortcode_logichop_data_input_display ($atts = null) {
		$var 	= (isset($atts['var'])) ? $atts['var'] : null;
		$type 	= (isset($atts['type'])) ? $atts['type'] : 'text';
		$id 	= (isset($atts['id'])) ? $atts['id'] : '';
		$name 	= (isset($atts['name'])) ? $atts['name'] : '';
		$class 	= (isset($atts['class'])) ? $atts['class'] : '';
		
		if ($var) {
			$input = sprintf('<input id="%s" name="%s" class="%s" type="%s" value="%s">',
								$id,
								$name,
								$class,
								$type,
								$this->logic->data_return($var)
							);
			return $input;
		}
		return;
	}
	
	/**
	 * Parse Conditions, Goals & redirects prior to template load
	 *
	 * @since    1.0.0
	 */
	public function template_level_parsing () {
		
		if (!$this->logic->js_tracking()) { // ONLY PARSE IF JAVASCRIPT TRACKING IS DISABLED
			
			$post_id = get_the_id();
			
			// UPDATE USER DATA
			$this->logic->update_data();
			
			// LEAD SCORE
			$lead_adjust = (int) get_post_meta($post_id, '_logichop_page_leadscore', true);
			$lead_freq = get_post_meta($post_id, '_logichop_page_lead_freq', true);
			if ($lead_adjust && $lead_freq) $this->logic->validate_lead_score($post_id, $lead_adjust, $lead_freq);
				
			// GOAL
			$goal_id = (int) get_post_meta($post_id, '_logichop_page_goal', true);
			if ($goal_id) $this->logic->update_goal($goal_id);
		
			// CONDITIONAL GOAL
			$condition_id 	= get_post_meta($post_id, '_logichop_page_goal_condition', true);
			$goal_id 		= (int) get_post_meta($post_id, '_logichop_page_goal_on_condition', true);
			$condition_not	= (boolean) get_post_meta($post_id, '_logichop_page_goal_condition_not', true);
		
			if ($goal_id && $condition_id) {
				
				$do_goal = $this->logic->condition_get($condition_id);
				if ($condition_not) $do_goal = !$do_goal;
				
				if ($do_goal) $this->logic->update_goal($goal_id);
			}
		
			// REDIRECT
			$condition_id 	= get_post_meta($post_id, '_logichop_page_condition', true);
			$condition_not	= (boolean) get_post_meta($post_id, '_logichop_page_condition_not', true);
			
			if ($condition_id) {
			
				$do_redirect = $this->logic->condition_get($condition_id);
				if ($condition_not) $do_redirect = !$do_redirect;
				
				if ($do_redirect) {
					$redirect = get_post_meta($post_id, '_logichop_page_redirect', true);
					if ($redirect) {
						wp_redirect($redirect);
						exit();
					}
				}
			}
		}
    }
	
	/**
	 * Add CSS classes to <body>
	 * .logichop-page-views-#
	 * And Conditional CSS
	 *
	 * @since    1.0.8
	 */
	public function body_class_insertion ($classes) {
		if (!$this->logic->js_tracking()) { // ONLY PARSE IF JAVASCRIPT TRACKING IS DISABLED
			$views 		= ($this->logic->session_get_var('Views')) ? $this->logic->session_get_var('Views') : 0;
			$classes[] 	= sprintf('logichop-views-%d', $views);
		
			if ($this->css) {
				foreach ($this->css as $css) {
					$classes[] = $css;
				}
			}
		}		
		return $classes;
	}
	
	/**
	 * Generate CSS based on Conditions
	 *
	 * @since    1.5.0
	 */
	public function generate_conditional_css () {
		
		$this->logic->get_referrer_query_string();
		
		$args = array(
			'post_type' => $this->plugin_name . '-conditions',
			'meta_query' => array(
					array(
            			'key' => 'logichop_css_condition',
            			'value' => true,
            			'compare' => 'LIKE'
        			)	
        		)
			);
		$posts = get_posts($args);
		
		$css_conditions = '';
		
		if ($posts) {
			foreach ($posts as $post) {
				$rule = json_decode($post->post_excerpt, true);
				$result = $this->logic->logic_apply($rule, $this->logic->session_get());
				
				if ($result) {
					$this->css[] = sprintf('lh-%s', $post->post_name);
					$css_conditions .= sprintf('.logichop-%s { display: block !important; } ', $post->post_name);
					$css_conditions .= sprintf('.logichop-not-%s { display: none !important; } ', $post->post_name);
				} else {
					$css_conditions .= sprintf('.logichop-%s { display: none !important; } ', $post->post_name);
					$css_conditions .= sprintf('.logichop-not-%s { display: block !important; } ', $post->post_name);
				}
			}
		}
		
		return $css_conditions;
	}
	
	/**
	 * Output Conditional CSS via WP Admin AJAX Call
	 *
	 * @since    1.0.1
	 */
	public function logichop_conditional_css () {
		if (!defined('DOING_AJAX') || !DOING_AJAX) wp_die();
		
		header('Content-type: text/css; charset: UTF-8');
		echo $this->generate_conditional_css();
		wp_die();
	}
	
	/**
	 * Display public CSS.
	 *
	 * If JS Tracking is NOT enabled, display via wp_head()
	 *
	 * @since    1.5.0
	 */
	public function output_header_css () {
		if (!$this->logic->js_tracking() && !$this->logic->disable_conditional_css()) {
			printf("<style type=\"text/css\">\n%s\n</style>\n", $this->generate_conditional_css());
		}
	}
	
	/**
	 * Register public CSS.
	 *
	 * If JS Tracking is enabled, display via WP Admin AJAX to bypass caching
	 *
	 * @since    1.0.1
	 */
	public function enqueue_styles () {
		if ($this->logic->js_tracking() && !$this->logic->disable_conditional_css()) {
			wp_enqueue_style('logichop-conditions', admin_url('admin-ajax.php').'?action=logichop_conditional_css', array(), $this->version, 'all');
		}
		
		do_action('logichop_public_enqueue_styles', $hook); 
	}
	
	/**
	 * Register public JavaScript.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts () {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url(__FILE__) . 'js/ajax-methods.min.js', array('jquery'), $this->version, false );
		
		$id = get_the_id();
		$pid = $gid = $cid = $gcid = $rcid = $redirect = null;
		
		$goal_ev	= get_post_meta($id, '_logichop_page_goal_js_event', true);
		$goal_el	= get_post_meta($id, '_logichop_page_goal_js_element', true);
		$goal_js	= (int) get_post_meta($id, '_logichop_page_goal_js', true);
		
		$views = ($this->logic->session_get_var('Views')) ? $this->logic->session_get_var('Views') : 0;
		
		if ($this->logic->js_tracking()) {
			$pid = $this->logic->wordpress_post_get();
			$la = (int) get_post_meta($id, '_logichop_page_leadscore', true);
			$lf = get_post_meta($id, '_logichop_page_lead_freq', true);
			$gid = (int) get_post_meta($id, '_logichop_page_goal', true);
			$cid = get_post_meta($id, '_logichop_page_goal_condition', true);
			$gcid =	(int) get_post_meta($id, '_logichop_page_goal_on_condition', true);
			$rcid = get_post_meta($id, '_logichop_page_condition', true);
			$redirect = get_post_meta($id, '_logichop_page_redirect', true);
		}
		
		$js_params = array(
						'ajaxurl' 	=> admin_url('admin-ajax.php'),
						'pid' 		=> $pid,		// PAGE
						'la' 		=> $la,			// LEAD SCORE ADJUSTER
						'lf' 		=> $lf,			// LEAD SCORE FREQUENCY
						'gid'		=> $gid,		// GOAL
						'cid'		=> $cid,		// CONDITIONAL GOAL CONDITION
						'gcid'		=> $gcid,		// CONDITIONAL GOAL
						'rcid'		=> $rcid,		// REDIRECT CONDITIONAL
						'redirect'	=> $redirect,	// REDIRECT URL
						'goal_ev'	=> $goal_ev,	// JAVASCRIPT GOAL EVENT
						'goal_el'	=> $goal_el,	// JAVASCRIPT GOAL ELEMENT
						'goal_js'	=> $goal_js,	// JAVASCRIPT GOAL
						'views'		=> $views,		// PAGE VIEWS
						'js_track' 	=> $this->logic->js_tracking(),			// JS TRACKING
						'js_vars' 	=> $this->logic->js_variable_display()	// JS VARIABLES
						);
		
		if ($this->logic->get_option('session_debug', false)) {
			$js_params['debug'] = $_SESSION['logichop-data'];
		}
				
 		wp_localize_script( $this->plugin_name, 'logichop', $js_params);
 		
 		do_action('logichop_public_enqueue_scripts', $hook, $post_type);
	}
	
	/**
	 * Filter SiteOrigin Widgets
	 * Displays SiteOrigin PageBuilder widgets based on the outcome of a condition
	 *
	 * @since    	1.0.1
	 * @param  		object	$the_widget		Wordpress Widget
	 * @param  		string	$widget			Widget name
	 * @param  		array	$instance		Widget details
	 * @return    	object	$the_widget		Wordpress Widget		
	 */
	public function siteorigin_panels_widget_filter ($the_widget, $widget, $instance = null) {
		
		if (is_admin()) return $the_widget;
		
		$condition_id 		= (isset($instance) && isset($instance['logichop_widget'])) ? $instance['logichop_widget'] : 0;
		$condition_not		= (isset($instance) && isset($instance['logichop_widget_not'])) ? (boolean) $instance['logichop_widget_not'] : false;
		
		if ($condition_id) {
			$display_widget = $this->logic->condition_get($condition_id);
			if ($condition_not) $display_widget = !$display_widget;
			
			if (!$display_widget) {
				$object = new stdclass;
				$object->widget_options = array();
				return $object;
			}
		}
		
		return $the_widget;
	}
}








