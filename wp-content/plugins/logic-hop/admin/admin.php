<?php
 
 /**
 * Admin-specific functionality.
 *
* @since      1.0.0
 * @package    LogicHop
 * @subpackage LogicHop/includes
 */
class LogicHop_Admin {

	/**
	 * Core functionality & logic class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;
	
	/**
	 * Plugin basename - Plugin file path
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_basename    Plugin basename.
	 */
	private $plugin_basename;
	
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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      object    $logic    			LogicHop_Core functionality & logic.
	 * @param      string    $plugin_basename   Plugin file path
	 * @param      string    $plugin_name   	The name of this plugin.
	 * @param      string    $version    		The version of this plugin.
	 */
	public function __construct( $logic, $plugin_basename, $plugin_name, $version ) {
		$this->logic = $logic;
		$this->plugin_basename = $plugin_basename;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	
	/**
	 * Register Plugin Settings.
	 *
	 * @since    1.0.0
	 */
	public function settings_register () {
		
		if (get_option('logichop-settings') == false) add_option('logichop-settings');
    
		add_settings_section(
			'logichop_settings_section',         
        	'Settings',              
        	array($this, 'section_callback'), 	
        	'logichop-settings'          	
    	);
		
		$settings = array (
						'domain' 		=> array (
												'name' 	=> __('Domain Name', 'logichop'),
												'meta' 	=> sprintf(__('Recommended: %s', 'logichop'), isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ''),
												'type' 	=> 'text',
												'label' => 'Domain Name',
												'opts'  => null
											),
						'api_key' 		=> array (
												'name' 	=> __('API Key', 'logichop'),
												'meta' 	=> __('Optional API access for data storage & geolocation. <a href="https://logichop.com" target="_blank">Learn more</a>.', 'logichop'),
												'type' 	=> 'text',
												'label' => 'API Key',
												'opts'  => null
											),
						'cookie_ttl' 	=> array (
												'name' 	=> __('Cookie Duration', 'logichop'),
												'meta' 	=> __('Optional API data storage duration. <a href="https://logichop.com" target="_blank">Learn more</a>.', 'logichop'),
												'type' 	=> 'select',
												'label' => '',
												'opts'  => array (
																0 => array (
																		'name' => __('Never Expire', 'logichop'),
																		'value' => '+ 20 year'
																	),
																1 => array (
																		'name' => __('One Year', 'logichop'),
																		'value' => '+ 1 year'
																	),
																2 => array (
																		'name' => __('One Month', 'logichop'),
																		'value' => '+ 1 month'
																	),
																3 => array (
																		'name' => __('One Week', 'logichop'),
																		'value' => '+ 1 week'
																	),
																4 => array (
																		'name' => __('One Day', 'logichop'),
																		'value' => '+ 1 day'
																	),
																5 => array (
																		'name' => __('One Hour', 'logichop'),
																		'value' => '+ 1 hour'
																	),
																)
											),
						'ajax_referrer' => array (
												'name' 	=> __('Javascript Referrer', 'logichop'),
												'meta' 	=> __('Restrict AJAX requests to specific domain. Leave blank to allow from all.', 'logichop'),
												'type' 	=> 'referrer',
												'label' => __('Enable Referrer', 'logichop'),
												'opts'  => null
											),
						'js_tracking' => array (
												'name' 	=> __('Javascript Tracking', 'logichop'),
												'meta' 	=> __('Recommended when using Cache Plugins. <a href="https://logichop.com/docs/using-logic-hop-with-javascript/" target="_blank">Learn More</a>.', 'logichop'),
												'type' 	=> 'tracking',
												'label' => __('Enable Javascript Tracking', 'logichop'),
												'opts'  => null
											),
						'disable_conditional_css' => array (
												'name' 	=> __('Conditional CSS', 'logichop'),
												'meta' 	=> __('Check to disable Conditional CSS output. <a href="https://logichop.com/docs/using-logic-hop-as-conditional-css/" target="_blank">Learn More</a>.', 'logichop'),
												'type' 	=> 'checkbox',
												'label' => __('Disable Conditional CSS', 'logichop'),
												'opts'  => null
											),
						'js_variables' => array (
												'name' 	=> __('Javascript Variables', 'logichop'),
												'meta' 	=> __('Check to display data via Javascript. <a href="https://logichop.com/docs/using-logic-hop-with-javascript/" target="_blank">Learn More</a>.', 'logichop'),
												'type' 	=> 'checkbox',
												'label' => __('Enable Javascript Variable Display', 'logichop'),
												'opts'  => null
											),
						'js_disabled_vars' => array (
												'name' 	=> __('Disable Variables ', 'logichop'),
												'meta' 	=> __('Prevents specifc variables from being accessed via Javsascript. <br>Comma-separated list of variable names. <a href="https://logichop.com/docs/using-logic-hop-with-javascript/" target="_blank">Learn More</a>.', 'logichop'),
												'type' 	=> 'textarea',
												'label' => __('Disable Javascript Variables', 'logichop'),
												'opts'  => null
											)
					);
		
		$settings = apply_filters('logichop_settings_register', $settings); 
		
		$settings['session_debug'] = array (
												'name' 	=> __('Session Output', 'logichop'),
												'meta' 	=> __('Output session data for testing & debugging.<br>Append to URL: <em>?session=display</em> - Disabled when JS Tracking active<br>Javascript Output: <em>console.log(logichop.debug);</em><br><strong>FOR TESTING ONLY - Disable on production websites.</strong>', 'logichop'),
												'type' 	=> 'checkbox',
												'label' => __('Enable Session Display', 'logichop'),
												'opts'  => null
											);
		
		foreach ($settings as $var => $params) {
			add_settings_field( 
				$var,                     
				$params['name'],                           	
				array($this, 'render_setting_input'),   
				'logichop-settings',                    
				'logichop_settings_section',        
				array($var, $params['type'], $params['meta'], $params['label'], $params['opts'])
			);
		}
		
		register_setting(
			'logichop-settings',
			'logichop-settings',
			array($this, 'setting_validation')
		);
		
		do_action('logichop_settings_register');
	}
	
	/**
	 * Plugin section callback.
	 *
	 * @since	1.0.0
	 * @return  null
	 */
	public function section_callback () {
		$this->logic->validate_api();
		return;
	}
	
	/**
	 * Validate Plugin Settings.
	 *
	 * @since    1.0.0
	 * @param	array	$input		Plugin settings
	 * @return	array	Plugin settings
	 */
	public function setting_validation ($input) {
		
		$output = array();
    	$error = false;
    	$error_msg = '';
    
    	foreach ($input as $key => $value) {
        	if (isset($input[$key])) {
         		$output[$key] = strip_tags(stripslashes($input[$key]));
         		if ($input[$key] != '') {
         			$validation = new stdclass;
					$validation->error = false;
	    			apply_filters('logichop_settings_validate', $validation, $key, $input);
	         		if ($validation->error) {
	         			$error = true;
         				$error_msg .= $validation->error_msg;
         				$output[$key] = '';
         			}
         		}
        	}
    	}
    	
    	if ($error) {
    		add_settings_error(
        		'logichop_settings_error',
				'settings_updated',
        		sprintf('<h2>Settings Error</h2><ul>%s</ul>', $error_msg),
        		'error'
    		);
    	}
		return $output;
	}
	
	/**
	 * Settings updated callback.
	 *
	 * @since    1.1.0
	 * @param	string	$updated		Settings Updated
	 */
	public function settings_updated ($updated, $old_value, $new_value) {
		if ($updated == 'logichop-settings') $this->logic->update_client_meta();
	}

	/**
	 * Render Settings Form Inputs.
	 *
	 * @param	array	$args		Setting arguments
	 * @since    1.0.0
	 */
	public function render_setting_input ($args) {
		
		$var 	= isset($args[0]) ? $args[0] : '';
		$type 	= isset($args[1]) ? $args[1] : 'text';
		$meta 	= isset($args[2]) ? $args[2] : '';
		$label 	= isset($args[3]) ? $args[3] : '';
		$opts 	= isset($args[4]) ? $args[4] : array();
		
		$options = get_option('logichop-settings');
		
		if ($type == 'text') {
			printf('<input type="text" id="logichop-settings[%s]" name="logichop-settings[%s]" value="%s" style="width: 400px; height: 30px;" placeholder="%s">
					<p><small>%s</small></p>',
					$var,
					$var,
					isset($options[$var]) ? sanitize_text_field($options[$var]) : '',
					$label,
					$meta
				);
		}
		
		if ($type == 'textarea') {
			printf('<textarea id="logichop-settings[%s]" name="logichop-settings[%s]" style="width: 400px; height: 60px;">%s</textarea>
					<p><small>%s</small></p>',
					$var,
					$var,
					isset($options[$var]) ? sanitize_text_field($options[$var]) : '',
					$meta
				);
		}
		
		if ($type == 'select') {
			$value = isset($options[$var]) ? sanitize_text_field($options[$var]) : '';
			
			$option_items = '';
			foreach ($opts as $o) {
				$option_items .= sprintf('<option value="%s" %s>%s</option>',
											$o['value'],
											($value == $o['value']) ? 'selected' : '',
											$o['name']
										);
			}
			
			printf('<select id="logichop-settings[%s]" name="logichop-settings[%s]" style="width: 400px; height: 30px;">
						%s
					</select>
					<p><small>%s</small></p>',
					$var,
					$var,
					$option_items,
					$meta
				);
		}
		
		if ($type == 'checkbox') {
			$value = isset($options[$var]) ? $options[$var] : '';
			printf('<input type="checkbox" id="logichop-settings[%s]" name="logichop-settings[%s]" value="1" %s />
					<label for="%s"><strong>%s</strong></label>
					<p><small>%s</small></p>',
					$var,
					$var,
					checked(1, $value, false),
					$var,
					$label,
					$meta
				);
		}
		
		if ($type == 'referrer') {
			$default = $this->logic->get_referrer_host();
			
			if ($default) {
				$messsage = sprintf(__('Recommended: %s', 'logichop'), $default);
			} else {
				$messsage = __('Referrer not found in $_SERVER[\'HTTP_REFERER\']. Please check with your hosting company.', 'logichop');
			}	
			
			$ajax_referrer = isset($options['ajax_referrer']) ? sanitize_text_field($options['ajax_referrer']) : $default;
			$js_tracking = isset($options['js_tracking']) ? $options['js_tracking'] : '';
			printf('<input type="text" id="logichop-settings[%s]" name="logichop-settings[%s]" value="%s" style="width: 400px; height: 30px;">
					<p><small>%s<br>%s</small></p>',
					$var,
					$var,
					$ajax_referrer,
					$meta,
					$messsage
				);
		}
		
		if ($type == 'tracking') {
			$value = isset($options['js_tracking']) ? $options['js_tracking'] : '';
			printf('<input type="checkbox" id="logichop-settings[%s]" name="logichop-settings[%s]" value="1" %s />
					<label for="%s"><strong>%s</strong></label>
					<p><small>%s</small></p>
					<p><strong style="color: rgb(255,0,0);">%s</strong></p>',
					$var,
					$var,
					checked(1, $value, false),
					$var,
					$label,
					$meta,
					(defined('WP_CACHE') && WP_CACHE && !$value) ? 'Cache Enabled: Javascript Tracking is recommended.' : ''
				);
		}
		
		if ($type == 'domain') {
			printf('<input type="text" id="logichop-settings[%s]" name="logichop-settings[%s]" value="%s" style="width: 400px; height: 30px;" readonly>
					<p><small>%s</small></p>',
					$var,
					$var,
					isset($options[$var]) ? sanitize_text_field($options[$var]) : strtolower($_SERVER['SERVER_NAME']),
					''
				);
		}
	}
	
	/**
	 * Register plugin settings 
	 *
	 * @since	1.0.0
	 * @param	array	$links		Wordpress plugin links
	 * @return	array	Wordpress plugin links
	 */
	public function display_settings_link ($links) {
		$new_links = array();
        $new_links['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url('admin.php?page=logichop-settings' ), __('Settings', 'plugin_domain') );
 		$new_links['deactivate'] = $links['deactivate'];
 		return $new_links;
	}
	
	/**
	 * Register admin notices 
	 *
	 * @since    1.0.0
	 */
	public function display_admin_notice () {
		global $pagenow;
		
		if ($pagenow == 'plugins.php') {
			if (!isset($_SESSION['logichop']) || !$_SESSION['logichop']) {
				printf('<div class="error">
							<p>
								<strong>%s</strong>
							</p>
						</div>',
						sprintf(__('Logic Hop is disabled. Please go to the plugin <a href="%s">settings page</a> to enable.', 'logichop'),
								admin_url('admin.php?page=logichop-settings')
							)
					);
			}
		}
	}
	
	/**
	 * Register plugin notices 
	 *
	 * @since   1.0.0
	 * @param	string	$plugin		Plugin name
	 */
	public function display_plugin_notice ($plugin) {
		if ($plugin == $this->plugin_basename) {
			if (!isset($_SESSION['logichop']) || !$_SESSION['logichop']) {
				printf('<td colspan="5" class="plugin-update">
							%s
						</td>',
						sprintf(__('Logic Hop must be configured. Go to the <a href="%s">settings page</a> to enable and configure the plugin.', 'logichop'),
								admin_url('admin.php?page=logichop-settings')
							)
					);
			}
		}
	}	
	
	/**
	 * Register Custom Post Types Text Filter.
	 *
	 * @since	1.0.0
	 * @param	array	$messages	Wordpress post messages
	 * @return	array	Wordpress post messages	
	 */
	public function custom_post_messages ($messages) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages[$this->plugin_name . '-conditions'] = array(
			0  => '', 
			1  => __( 'Condition updated.', 'logichop' ),
			2  => __( 'Custom field updated.', 'logichop' ),
			3  => __( 'Custom field deleted.', 'logichop' ),
			4  => __( 'Condition updated.', 'logichop' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Condition restored to revision from %s', 'logichop' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Condition published.', 'logichop' ),
			7  => __( 'Condition saved.', 'logichop' ),
			8  => __( 'Condition submitted.', 'logichop' ),
			9  => sprintf(
				__( 'Condition scheduled for: <strong>%1$s</strong>.', 'logichop' ),
				date_i18n( __( 'M j, Y @ G:i', 'logichop' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Condition draft updated.', 'logichop' )
		);
		
		$messages[$this->plugin_name . '-goals'] = array(
			0  => '',
			1  => __( 'Goal updated.', 'logichop' ),
			2  => __( 'Custom field updated.', 'logichop' ),
			3  => __( 'Custom field deleted.', 'logichop' ),
			4  => __( 'Goal updated.', 'logichop' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Goal restored to revision from %s', 'logichop' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Goal published.', 'logichop' ),
			7  => __( 'Goal saved.', 'logichop' ),
			8  => __( 'Goal submitted.', 'logichop' ),
			9  => sprintf(
				__( 'Goal scheduled for: <strong>%1$s</strong>.', 'logichop' ),
				date_i18n( __( 'M j, Y @ G:i', 'logichop' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Goal draft updated.', 'logichop' )
		);
		
		return $messages;
	}
	
	/**
	 * Register Menu Pages CSS.
	 *
	 * @since    1.0.0
	 */
	public function menu_pages () {	
		add_menu_page( 
			'Logic Hop', 
			'Logic Hop', 
			'edit_theme_options', 
			$this->plugin_name . '-menu', 
			null, 
			plugins_url('images/icon-green.png', __FILE__),
			85
		);		 
		add_submenu_page(
			$this->plugin_name . '-menu',
			__('Insights', 'logichop'),
			__('Insights', 'logichop'),
			'edit_theme_options',
			$this->plugin_name . '-insights',
			array($this, 'insights_page')
		);		
		add_submenu_page(
			$this->plugin_name . '-menu',
			__('Settings', 'logichop'),
			__('Settings', 'logichop'),
			'manage_options',
			$this->plugin_name . '-settings',
			array($this, 'settings_page')
		);		
		add_submenu_page(
			$this->plugin_name . '-menu',
			__('Add-ons', 'logichop'),
			__('Add-ons', 'logichop'),
			'manage_options',
			$this->plugin_name . '-settings&tab=addons',
			array($this, 'settings_page')
		);
		
		do_action('logichop_admin_menu_pages');
	}	
	
	/**
	 * Display Insights Page.
	 * Include partial file insights.php
	 *
	 * @since    1.0.0
	 */
	public function insights_page () {
		include_once( plugin_dir_path( dirname(__FILE__) ) . 'admin/partials/insights.php');
	}
	
	/**
	 * Display Settings Page.
	 * Include partial file settings.php
	 *
	 * @since    1.0.0
	 */
	public function settings_page () {
		global $wp_version;
		include_once( plugin_dir_path( dirname(__FILE__) ) . 'admin/partials/settings.php');
	}
	
	/**
	 * Override Widget Form
	 *
	 *
	 * @since    1.0.0
	 * @param	 object		$widget			Wordpress widget object
	 * @param	 object		$return			Wordpress widget object
	 * @param	 object		$instance		Wordpress widget objects
	 * @return	 object		Wordpress widget object
	 */
	public function widget_form_override ($widget, $return, $instance) {
		$logichop_widget = isset($instance['logichop_widget']) ? $instance['logichop_widget'] : '';
		$logichop_widget_not = isset($instance['logichop_widget_not']) ? $instance['logichop_widget_not'] : false;
		
		printf('<p style="margin-bottom: 10px;">
					<label for="%s" style="margin-bottom: 4px;">%s: 
					</label>
					<select class="widefat" id="%s" name="%s">
						<option value="">%s</option>
						%s
					</select>	
				</p>
				<p>
					<input id="%s" name="%s" type="checkbox" %s>&nbsp;<label for="%s">%s</label>
				</p>',
				$widget->get_field_id('logichop_widget'),
				__('Display if Logic Hop Condition is', 'logichop'),
				$widget->get_field_id('logichop_widget'),
				$widget->get_field_name('logichop_widget'),
				__('Always Display', 'logichop'),
				$this->logic->conditions_get_options($logichop_widget),
				
				$widget->get_field_id('logichop_widget_not'),
				$widget->get_field_name('logichop_widget_not'),
				($logichop_widget_not) ? 'checked' : '',
				$widget->get_field_name('logichop_widget_not'),
				__('Logic Hop Condition Not Met', 'logichop')
			);
	}

	/**
	 * Override Widget Form
	 *
	 * @since    1.0.0
	 * @param	 object		$instance		Wordpress widget object
	 * @param	 object		$new_instance	Wordpress widget object
	 * @return	 object		Wordpress widget object
	 */
	public function widget_save_override ($instance, $new_instance) {
		if (isset($new_instance['logichop_widget'])) {
			$instance['logichop_widget'] = $new_instance['logichop_widget'];
		}
		if (isset($new_instance['logichop_widget_not'])) {
			$instance['logichop_widget_not'] = true;
		} else {
			$instance['logichop_widget_not'] = false;
		}
		return $instance;
	}
	
	/**
	 * Register & Add Help Menus.
	 *
	 * @since    1.0.0
	 * @param	 object		$contextual_help	Wordpress Help object
	 * @param	 object		$screen_id			Current screen
	 * @param	 string		$screen				Wordpress screen object
	 * @return	 object		Wordpress Help object
	 */
	public function help_menus ($contextual_help, $screen_id, $screen) {	
		
		$help_on = array (
				'logichop-conditions',
				'edit-logichop-conditions',
				'logichop-goals',
				'edit-logichop-goals',
				'logic-hop_page_logichop-settings',
				'logic-hop_page_logichop-insights'
			);
 					
 		if (!in_array($screen_id, $help_on) || !method_exists($screen, 'add_help_tab')) return $contextual_help;
 
    	$screen->add_help_tab(array(
        		'id'      => 'logichop-conditions-help-tab',
        		'title'   => __( 'Conditions', 'logichop' ),
        		'content' => '<h4>Logic Hop Conditions</h4><p>Conditions are used by Logic Hop to perform actions such as displaying content, setting goals and redirecting visitors to different pages.</p><p>Conditions consist of one or more statements which evaluate to either true or false. When a Condition is true Logic Hop can perform an action.</p><p>Conditional statements can be thought of as "questions" answered by Logic Hop. The question consists of one or more "values" which are compared by a "comparison operator". The "answer" to a Condition is either true or false which can be thought of as "yes" or "no" respectively.</p><p><a href="https://logichop.com/docs/how-to-create-logic-hop-conditions/" target="_blank">Learn more about creating and using Logic Hop Conditions.</a></p>',
    		));
    	$screen->add_help_tab(array(
        		'id'      => 'logichop-goals-help-tab',
        		'title'   => __( 'Goals', 'logichop' ),
        		'content' => '<h4>Logic Hop Goals</h4><p>Goals are how Logic Hop stores user actions. You create a Goal for any event or action you would like to store and track. When a Goal is "triggered" by a user, that Goal is associated with the user for their current and future visits to your site.</p><p>Goals can be triggered on page load, when a Logic Hop Condition is met or by a combination of events, including Javascript events. This allows for Goals to be easily integrated with third-party plugins.</p><p>Logic Hop can use Goals within Logic Hop Conditions to dynamically display content to a specific user or redirect them to a different page. Any one, or a combination of, multiple Goals can be used to show content and offers to your users or redirect them to specific pages on your site.</p><p><a href="https://logichop.com/docs/how-to-create-logic-hop-goals/" target="_blank">Learn more about creating and using Logic Hop Goals.</a></p>',
    		));
    	$screen->add_help_tab(array(
        		'id'      => 'logichop-insights-help-tab',
        		'title'   => __( 'Insights', 'logichop' ),
        		'content' => '<h4>Logic Hop Insights</h4><p>Logic Hop Insights provide a near real-time report of pages viewed, goals triggered and referring pages. Insights can be viewed for the past 30 days, 7 days, 1 day and for the current day.</p><p>Insights can be viewed as:</p><ul><li>Unique Views</li><li>Each item counted once per user</li><li>Aggregate Views</li><li>Every item counted for every user</li></ul><p><a href="https://logichop.com/docs/working-with-logic-hop-insights/" target="_blank">Learn more about Logic Hop Insights.</a></p>',
    		));
    	return $contextual_help;
	}		
	
	/**
	 * Add editor shortcode button
	 * Send directly to HTML via printf()
	 *
	 * @since    1.0.0
	 * @param	 object		$context	Wordpress Editor context
	 * @return	 object		Wordpress Editor context
	 */
	public function editor_buttons ($context) {
		printf('<a href="#" class="button logichop-button logichop-editor" title="Logic Hop"><img src="%s"> %s</a>',
				plugins_url('images/icon-green.png', __FILE__), 
				'L<span>o</span>gic H<span>o</span>p'
			);

		return $context;
    }
	
	/**
	 * Add editor modal window
	 *
	 * @since    1.0.0
	 * @return		string		Echos admin HTML & Javascript
	 */
	public function editor_shortcode_modal () {
		global $pagenow;
		
		if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php', 'post-edit.php', 'widgets.php'))) {
			
			$conditions = $this->logic->conditions_get_options();
			$goals = $this->logic->goals_get_options();
			
			$tab_conditions = sprintf('<h4>%s</h4>
					<select id="logichop_condition">
						<option value="">%s</option>
						%s
					</select>
					<p>
						<button id="logichop_insert_condition" class="button button-primary">%s</button>
					</p>
					<hr>
					<h4>%s</h4>
					<select id="logichop_condition_not">
						<option value="">%s</option>
						%s
					</select>
					<p>
						<button id="logichop_insert_condition_not" class="button button-primary">%s</button>
					</p>',
					__('Conditional Shortcode', 'logichop'),
					($conditions) ? __('Select a condition', 'logichop') : __('No conditions have been created', 'logichop'),
					$conditions,
					__('Insert Conditional Shortcode', 'logichop'),
					__('Conditional Not Shortcode', 'logichop'),
					($conditions) ? __('Select a condition', 'logichop') : __('No conditions have been created', 'logichop'),
					$conditions,
					__('Insert Conditional Not Shortcode', 'logichop')
				);
			
			$vars_datalist = $this->editor_shortcode_modal_get_vars();
			
			$tab_data = sprintf('<h4>%s</h4>
								<p>
									<input list="logichop_data_var_list" id="logichop_data_var" placeholder="%s">
									<datalist id="logichop_data_var_list">
										%s
									</datalist>
								</p>
								<p>
									<button class="button button-primary logichop_insert_data_shortcode" data-input="#logichop_data_var">%s</button>
								</p>
								<hr>
								
								<h4>%s</h4>
								<p>
									<input list="logichop_data_js_list" id="logichop_data_js_var" placeholder="%s">
									<datalist id="logichop_data_js_list">
										%s
									</datalist>
								</p>
								
								<h4>%s</h4>
								<select id="logichop_data_js_var_type">
									<option value="html">Replace</option>
									<option value="prepend">Prepend</option>
									<option value="append">Append</option>
									<option value="value">Value</option>
								</select>
								<select id="logichop_data_js_var_event">
									<option value="show">Show</option>
									<option value="fadeIn">Fade In</option>
									<option value="slideDown">Slide Down</option>
									<option value="">None</option>
								</select>
								
								<p>
									<button class="button button-primary logichop_insert_data_javascript" data-input="#logichop_data_js_var">%s</button>
								</p>',
					__('Variable Display Shortcode', 'logichop'),
					__('Logic Hop Variable', 'logichop'),
					$vars_datalist,
					__('Insert Variable Shortcode', 'logichop'),
					
					__('Variable Display Javascript', 'logichop'),
					__('Logic Hop Variable', 'logichop'),
					$vars_datalist,
					__('Events', 'logichop'),
					__('Insert Variable Javascript ', 'logichop')
				);
				
			$tab_goals = sprintf('<h4>%s</h4>
								<select id="logichop_goal">
									<option value="">%s</option>
									%s
								</select>
								<p>
									<button id="logichop_insert_goal" class="button button-primary">%s</button>
								</p>
								<hr>
						
								<h4>%s</h4>
								<select id="logichop_conditional">
									<option value="">%s</option>
									%s
								</select>
								<select id="logichop_conditional_goal">
									<option value="">%s</option>
									%s
								</select>
								<p>
									<button id="logichop_insert_conditional_goal" class="button button-primary">%s</button>
								</p>',
					__('Goal Shortcode', 'logichop'),
					($goals) ? __('Select a goal', 'logichop') : __('No goals have been created', 'logichop'),
					$goals,
					__('Insert Goal Shortcode', 'logichop'),
					__('Conditional Goal Shortcode', 'logichop'),
					($conditions) ? __('Select a condition', 'logichop') : __('No conditions have been created', 'logichop'),
					$conditions,
					($goals) ? __('Select a goal', 'logichop') : __('No goals have been created', 'logichop'),
					$goals,
					__('Insert Conditional Goal Shortcode', 'logichop')
				);
				
			$tab_javascript = sprintf('<h4>%s</h4>
								<select id="logichop_condition_js">
									<option value="">%s</option>
									%s
								</select>
								
								<h4>%s</h4>
								<select id="logichop_condition_display">
									<option value="display: none;">%s</option>
									<option value="">%s</option>
								</select>
								
								<h4>%s</h4>
								<select id="logichop_condition_event" class="logichop_event">
									<option value="show">Show</option>
									<option value="hide">Hide</option>
									<option value="toggle">Toggle</option>
									<option value="fadeIn">Fade In</option>
									<option value="fadeOut">Fade Out</option>
									<option value="fadeToggle">Fade Toggle</option>
									<option value="slideDown">Slide Down</option>
									<option value="slideUp">Slide Up</option>
									<option value="slideToggle">Slide Toggle</option>
									<option value="callback">Callback</option>
									<option value="">None</option>
								</select>
								
								<h4>%s</h4>
								<select id="logichop_condition_not_js">
									<option value="">%s</option>
									<option value="true">%s</option>
								</select>
								
								<p>
									<input type="text" id="logichop_condition_css_add" placeholder="%s">
								</p>
								
								<p>
									<input type="text" id="logichop_condition_css_remove" placeholder="%s">
								</p>
								
								<p>
									<button id="logichop_insert_js_condition" class="button button-primary">%s</button>
								</p>',
					__('Condition', 'logichop'),
					($conditions) ? __('Select a condition', 'logichop') : __('No conditions have been created', 'logichop'),
					$conditions,
					__('Initial State', 'logichop'),
					__('Hidden', 'logichop'),
					__('Visible', 'logichop'),
					__('Event', 'logichop'),
					__('When', 'logichop'),
					__('Condition Met', 'logichop'),
					__('Condition Not Met', 'logichop'),
					__('Add CSS Classes on Condition', 'logichop'),
					__('Remove CSS Classes on Condition', 'logichop'),
					__('Insert Javascript Condition', 'logichop')
				);
			
			$tab_navigation = $tab_panel = '';
			$tab_navigation = apply_filters('logichop_editor_modal_nav', $tab_navigation); 
			$tab_panel = apply_filters('logichop_editor_modal_panel', $tab_panel); 
					
			printf('<div id="logichop-modal-backdrop"></div>
					<div id="logichop-modal-wrap" class="wp-core-ui has-text-field" role="dialog" aria-labelledby="link-modal-title">
						<form class="logichop-modal-form" tabindex="-1">
						
							<h1 class="logichop-modal-title"><img src="%s"> %s</h1>
							
							<div class="logichop-modal-close"><span class="screen-reader-text">Close</span></div>
							
							<div class="logichop-modal-content">
								<h2 class="nav-tab-wrapper">
									<a href="#" class="nav-tab nav-tab-active" data-tab="logichop-modal-conditions">Conditions</a>
									<a href="#" class="nav-tab" data-tab="logichop-modal-goals">Goals</a>
									<a href="#" class="nav-tab" data-tab="logichop-modal-data">Data</a>
									<a href="#" class="nav-tab" data-tab="logichop-modal-javascript">Javascript</a>
									%s
								</h2>
								<div class="nav-tab-display logichop-modal-conditions nav-tab-display-active">%s</div>
								<div class="nav-tab-display logichop-modal-goals">%s</div>
								<div class="nav-tab-display logichop-modal-data">%s</div>
								<div class="nav-tab-display logichop-modal-javascript">%s</div>						
								%s				
							</div>
							
							<div class="logichop-modal-footer">
								<a href="%s" class="logichop-hide">Add Conditions</a>
								<a href="%s" class="logichop-hide">Add Goals</a>
								<button type="button" class="button logichop-modal-cancel">%s</button>
							</div>
						</form>
					</div>',
					plugins_url('images/icon-green.png', __FILE__),
					__('Logic Hop', 'logichop'),
					$tab_navigation,
					$tab_conditions,
					$tab_goals,
					$tab_data,
					$tab_javascript,
					$tab_panel,
					admin_url('edit.php?post_type=logichop-conditions'),
					admin_url('edit.php?post_type=logichop-goals'),
					__('Cancel', 'logichop')
				);
		}
    }	
	
	/**
	 * Get editor modal window variables
	 *
	 * @since    1.0.0
	 * @return		string		Datalist Options
	 */
	public function editor_shortcode_modal_get_vars () {
		$datalist = '<option value=""></option>
					<option value="Query:#var#">Query String</option>
					<option value="QueryStore:#var#">Query String - Stored</option>
					<option value="Location.CountryCode">Country Code (US, CA)</option>
					<option value="Location.CountryName">Country Name</option>
					<option value="Location.RegionCode">Region Code (CA, NY)</option>
					<option value="Location.RegionName">Region Name (California, New York)</option>
					<option value="Location.City">City</option>
					<option value="Location.ZIPCode">ZIP Code</option>
					<option value="Location.TimeZone">Time Zone</option>
					<option value="Location.Latitude">Latitude</option>
					<option value="Location.Longitude">Longitude</option>
					<option value="Location.IP">IP Address</option>
					<option value="Timestamp.FirstVisit">Timestamp: First Visit</option>
					<option value="Timestamp.LastVisit">Timestamp: Last Visit</option>
					<option value="Timestamp.ThisVisit">Timestamp: This Visit</option>
					<option value="Timestamp.LastPage">Timestamp: Last Page Visited</option>
					<option value="LandingPage">Landing Page</option>
					<option value="LeadScore">Lead Score</option>
					<option value="Source">Source / Referral</option>
					<option value="UserAgent">User Agent</option>';
		
		return apply_filters('logichop_editor_shortcode_variables', $datalist); 
	}
	
	/**
	 * Adds & removes metaboxes
	 *
	 * @since    1.0.0
	 */
	public function configure_metaboxes () {
		
		remove_meta_box('postexcerpt', 'logichop-conditions', 'normal');
		remove_meta_box('postexcerpt', 'logichop-goals', 'normal');
		
		add_meta_box(
			'logichop_metabox', 
			sprintf('<img src="%s"> Logic Hop', plugins_url('images/icon-green.png', __FILE__)), 
			array($this, 'primary_metabox_display'),
			apply_filters('logichop_metabox_post_types', array('post', 'page')),
			'normal',
			'high'
			);
		add_meta_box(
			'logichop_condition_builder', 
			__('Logic Builder', 'logichop'),
			array($this, 'condition_builder_display'),
			array('logichop-conditions'),
			'advanced',
			'high'
			);		
		add_meta_box(
			'logichop_goal_detail', 
			__('Goal Description', 'logichop'), 
			array($this, 'goal_detail_display'),
			array('logichop-goals'),
			'normal',
			'high'
			);
		
		do_action('logichop_configure_metaboxes');
	}
	
	/**
	 * Displays primary metabox on Page & Post editor
	 *
	 * @since    1.0.0  
	 * @param		object		$post		Wordpress Post object
	 * @return		string		Echos metabox form
	 */
	public function primary_metabox_display ($post) {
	
		$hide_tracking = apply_filters('logichop_primary_metabox_tracking', false, $post);
		
		$values = get_post_custom($post->ID);
		
		$lead_score		= isset($values['_logichop_page_leadscore']) 		? esc_attr($values['_logichop_page_leadscore'][0]) 	: 0;
		$lead_freq		= isset($values['_logichop_page_lead_freq']) 		? esc_attr($values['_logichop_page_lead_freq'][0]) 	: 'every';
		$track_page 	= isset($values['_logichop_track_page']) 			? esc_attr($values['_logichop_track_page'][0]) 		: '';
		
		$condition		= isset($values['_logichop_page_condition'])		? esc_attr($values['_logichop_page_condition'][0])	: '';
		$redirect		= isset($values['_logichop_page_redirect']) 		? esc_attr($values['_logichop_page_redirect'][0]) 			: '';
		$condition_not	= isset($values['_logichop_page_condition_not']) 	? esc_attr($values['_logichop_page_condition_not'][0])		: '';
		
		$goal			= isset($values['_logichop_page_goal']) 			? esc_attr($values['_logichop_page_goal'][0])				: '';
		
		$goal_condition		= isset($values['_logichop_page_goal_condition'])		? esc_attr($values['_logichop_page_goal_condition'][0]) 	: '';
		$goal_on_condition	= isset($values['_logichop_page_goal_on_condition'])	? (int) esc_attr($values['_logichop_page_goal_on_condition'][0])	: '';
		$goal_condition_not	= isset($values['_logichop_page_goal_condition_not']) 	? esc_attr($values['_logichop_page_goal_condition_not'][0])	: '';
		
		$goal_js			= isset($values['_logichop_page_goal_js'])			? (int) esc_attr($values['_logichop_page_goal_js'][0]) 	: '';
		$goal_js_event		= isset($values['_logichop_page_goal_js_event'])	? esc_attr($values['_logichop_page_goal_js_event'][0]) 	: '';
		$goal_js_element	= isset($values['_logichop_page_goal_js_element'])	? esc_attr($values['_logichop_page_goal_js_element'][0])	: '';
		
		$goals_js		= $this->logic->goals_get_options($goal_js);
		$goal_js_events = $this->logic->javascript_get_events($goal_js_event);
		$conditions_on	= $this->logic->conditions_get_options($goal_condition);
		$goals_on		= $this->logic->goals_get_options($goal_on_condition);
		$conditions		= $this->logic->conditions_get_options($condition);
		$goals			= $this->logic->goals_get_options($goal);
		
		$goal_on_css = '';
		if ($goal_condition || $goal_on_condition) $goal_on_css = 'half-set';
		if ($goal_condition && $goal_on_condition) $goal_on_css = 'set';
		
		$goal_js_css = '';
		if ($goal_js || $goal_js_event || $goal_js_element) $goal_js_css = 'half-set';
		if ($goal_js && $goal_js_event && $goal_js_element) $goal_js_css = 'set';
		
		$redirect_css = '';
		if ($condition || $redirect) $redirect_css = 'half-set';
		if ($condition && $redirect) $redirect_css = 'set'; 
		
		wp_nonce_field('_logichop_metabox_nonce', 'meta_box_nonce');
		
		$tracking_input = sprintf('<div class="logichop-meta %s">
										<label><strong>%s</strong></label><br>
										<select id="_logichop_track_page" name="_logichop_track_page" style="width: 100%%;">
											%s
										</select>
										<p></p>
									</div>
									<hr>',
									($track_page) ? 'set' : '',
									__('Logic Hop Tracking', 'logichop'),
									sprintf('<option value="disabled" %s>%s</option>
											<option value="enabled" %s>%s</option>',
											($track_page) ? '' : 'selected',
											__('Tracking Disabled', 'logichop'),
											($track_page) ? 'selected' : '',
											__('Tracking Enabled', 'logichop')
									)
								);
		
		printf('<div>
					
					<div class="logichop-meta %s">
						<label><strong>%s</strong></label><a href="#" class="logichop-meta-clear">%s</a><br>
						<input type="number" value="%s" id="_logichop_page_leadscore" name="_logichop_page_leadscore" style="">
						<select id="_logichop_page_lead_freq" name="_logichop_page_lead_freq" style="margin-top: -4px;">
		 					<option value="every" %s>%s</option>
		 					<option value="first" %s>%s</option>
		 					<option value="session" %s>%s</option>
		 				</select>
		 				<p></p> 
		 			</div>
		 			<hr>
		 			
					%s
		 			
		 			<div class="logichop-meta %s">
						<label><strong>%s</strong></label><a href="#" class="logichop-meta-clear">%s</a><br>
						<label><strong>%s</strong></label><br>
						<select id="_logichop_page_goal" name="_logichop_page_goal" style="width: 100%%;">
		 					<option value="">%s</option>
		 					%s
		 				</select>
		 				<p></p>
		 			</div>
		 			<hr>
		 			
		 			<div class="logichop-meta %s">
						<label><strong>%s</strong></label><a href="#" class="logichop-meta-clear">%s</a><br>
						<label><strong>%s</strong></label><br>
						<select id="_logichop_page_goal_condition" name="_logichop_page_goal_condition" style="width: 100%%;">
							<option value="">%s</option>
							%s
						</select>
						<p></p>
						<label><strong>%s</strong></label><br>
						<select id="_logichop_page_goal_on_condition" name="_logichop_page_goal_on_condition" style="width: 100%%;">
							<option value="">%s</option>
							%s
						</select>
						<p></p>
						<label for="_logichop_page_goal_condition_not" class="selectit">
		 					<input type="checkbox" id="_logichop_page_goal_condition_not" name="_logichop_page_goal_condition_not" %s> 
		 					%s
		 				</label>
		 				<p></p>
					</div>
		 			<hr>
		 			
		 			<div class="logichop-meta %s">
						<label><strong>%s</strong></label><a href="#" class="logichop-meta-clear">%s</a><br>
						<label><strong>%s</strong></label><br>
						<select id="_logichop_page_goal_js_event" name="_logichop_page_goal_js_event" style="width: 100%%;">
							<option value="">%s</option>
							%s
						</select>
						<p></p>
						<label><strong>%s</strong></label><br>
						<input type="text" value="%s" id="_logichop_page_goal_js_element" name="_logichop_page_goal_js_element" placeholder="%s" style="width: 100%%;">
						<p></p>
						<label><strong>%s</strong></label><br>
						<select id="_logichop_page_goal_js" name="_logichop_page_goal_js" style="width: 100%%;">
							<option value="">%s</option>
							%s
						</select>
						<p></p>
					</div>
		 			<hr>
		 			
		 			<div class="logichop-meta %s">
						<label><strong>%s</strong></label><a href="#" class="logichop-meta-clear">%s</a><br>
						<label><strong>%s</strong></label><br>
						<select id="_logichop_page_condition" name="_logichop_page_condition" style="width: 100%%;">
							<option value="">%s</option>
							%s
						</select>
						<p></p>
						<label><strong>%s</strong></label><br>
						<input type="text" value="%s" id="_logichop_page_redirect" name="_logichop_page_redirect" placeholder="%s" style="width: 100%%;">
						<p></p>
						<label for="_logichop_page_condition_not" class="selectit">
		 					<input type="checkbox" id="_logichop_page_condition_not" name="_logichop_page_condition_not" %s> 
		 					%s
		 				</label>
		 				<p></p>
					</div>		 			
				</div>',
				($lead_score != 0) ? 'set' : '',
				__('Logic Hop Lead Score', 'logichop'),
				__('Clear', 'logichop'),
				$lead_score,
				($lead_freq == 'every') ? 'selected' : '',
				__('Increment on every view', 'logichop'),
				($lead_freq == 'first') ? 'selected' : '',
				__('Increment on first view only', 'logichop'),
				($lead_freq == 'session') ? 'selected' : '',
				__('Increment on first view each session', 'logichop'),
				
				($hide_tracking) ? '' : $tracking_input,
				
				($goal) ? 'set' : '',
				__('Set Goal on Load', 'logichop'),
				__('Clear', 'logichop'),
				__('Set Goal', 'logichop'),
				__('No Goal', 'logichop'),
				$goals,
				
				$goal_on_css,
				__('Set Goal on Condition', 'logichop'),
				__('Clear', 'logichop'),
				__('On Condition', 'logichop'),
				__('No Condition', 'logichop'),
				$conditions_on,
				__('Set Goal', 'logichop'),
				__('No Goal', 'logichop'),
				$goals_on,
				($goal_condition_not) ? 'checked' : '',
				__('Condition Not Met', 'logichop'),
				
				$goal_js_css,
				__('Set Goal on Javascript Event', 'logichop'),
				__('Clear', 'logichop'),
				__('Event', 'logichop'),
				__('No Event', 'logichop'),
				$goal_js_events,
				__('Element', 'logichop'),
				$goal_js_element,
				__('Class or ID. Example: .class-name or #id-name', 'logichop'),
				__('Set Goal', 'logichop'),
				__('No Goal', 'logichop'),
				$goals_js,
				
				$redirect_css,
				__('Redirect on Condition', 'logichop'),
				__('Clear', 'logichop'),
				__('On Condition', 'logichop'),
				__('No Redirect', 'logichop'),
				$conditions,
				__('Redirect to Page/URL', 'logichop'),
				$redirect,
				__('Path or URL', 'logichop'),
				($condition_not) ? 'checked' : '',
				__('Condition Not Met', 'logichop')
			);
	}
	
	/**
	 * Saves primary metabox data
	 *
	 * @since    1.0.0
	 * @param		integer		$post_id	Post ID
	 */
	public function primary_metabox_save ($post_id) {
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], '_logichop_metabox_nonce')) return;
		if (!current_user_can('edit_post', $post_id)) return;
		
		$track_page = false;
		if (isset($_POST['_logichop_track_page'])) {
			if ($_POST['_logichop_track_page'] == 'enabled') $track_page = true;
		}
		update_post_meta($post_id, '_logichop_track_page', wp_kses($track_page,''));
		
		if (isset($_POST['_logichop_page_leadscore'])) {
			$lead_score = (is_numeric($_POST['_logichop_page_leadscore'])) ? $_POST['_logichop_page_leadscore'] : 0;
			update_post_meta($post_id, '_logichop_page_leadscore', $lead_score);
		}
		if (isset($_POST['_logichop_page_lead_freq']))	update_post_meta( $post_id, '_logichop_page_lead_freq', wp_kses($_POST['_logichop_page_lead_freq'],''));
				
		if (isset($_POST['_logichop_page_goal']))	update_post_meta( $post_id, '_logichop_page_goal', wp_kses($_POST['_logichop_page_goal'],''));
		
		if (isset($_POST['_logichop_page_goal_condition'])) 	update_post_meta( $post_id, '_logichop_page_goal_condition', wp_kses($_POST['_logichop_page_goal_condition'],''));
		if (isset($_POST['_logichop_page_goal_on_condition'])) 	update_post_meta( $post_id, '_logichop_page_goal_on_condition', wp_kses($_POST['_logichop_page_goal_on_condition'],''));
		$checkbox = (isset($_POST['_logichop_page_goal_condition_not'])) ? true : false;
		update_post_meta($post_id, '_logichop_page_goal_condition_not', wp_kses($checkbox,''));
		
		if (isset($_POST['_logichop_page_goal_js'])) 			update_post_meta( $post_id, '_logichop_page_goal_js', wp_kses($_POST['_logichop_page_goal_js'],''));
		if (isset($_POST['_logichop_page_goal_js_event'])) 		update_post_meta( $post_id, '_logichop_page_goal_js_event', wp_kses($_POST['_logichop_page_goal_js_event'],''));
		if (isset($_POST['_logichop_page_goal_js_element']))	update_post_meta( $post_id, '_logichop_page_goal_js_element', wp_kses($_POST['_logichop_page_goal_js_element'],''));
		
		if (isset($_POST['_logichop_page_condition'])) 	update_post_meta( $post_id, '_logichop_page_condition', wp_kses($_POST['_logichop_page_condition'],''));
		if (isset($_POST['_logichop_page_redirect'])) 	update_post_meta( $post_id, '_logichop_page_redirect', wp_kses($_POST['_logichop_page_redirect'],''));
		$checkbox = (isset($_POST['_logichop_page_condition_not'])) ? true : false;
		update_post_meta($post_id, '_logichop_page_condition_not', wp_kses($checkbox,''));
	}
	
	/**
	 * Displays condition builder metabox on Condition editor
	 *
	 * @since    1.0.0
	 * @param		object		$post		Wordpress Post object
	 * @return		string		Echos metabox form
	 */
	public function condition_builder_display ($post) {
		
		require_once( plugin_dir_path( dirname(__FILE__) ) . 'admin/partials/conditions.php');
		
		$data = ($post->post_excerpt) ? $post->post_excerpt : 'false';
		$goals = $this->logic->goals_get_json();
		$pages = $this->logic->pages_get_json();
		
		$condition_vars = '';
		$condition_vars = apply_filters('logichop_condition_builder_vars', $condition_vars); 
		
		$values			= get_post_custom($post->ID);
		$description	= isset($values['logichop_condition_description']) ? esc_attr($values['logichop_condition_description'][0]) : '';
		$css_condition	= isset($values['logichop_css_condition']) ? esc_attr($values['logichop_css_condition'][0]) : '';
		
		wp_nonce_field('logichop_metabox_description_nonce', 'meta_box_nonce');
		
		printf('
				<label><strong>%s</strong></label>
				<textarea rows="3" cols="40" id="logichop_condition_description" name="logichop_condition_description" style="width: 100%%; margin-top: 5px;">%s</textarea>
				<p></p>
				
				<label><strong>%s</strong></label>
				<div class="col-xs-12 col-sm-3 logichop-conditions"></div>
				<p></p>
				
				<label><strong>%s</strong></label>
				<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value=\'[logichop_condition id="%d" condition="%s"][/logichop_condition]\'>
				<p></p>
				
				<label><strong>%s</strong></label>
				<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value=\'[logichop_condition_not id="%d" condition="%s"][/logichop_condition_not]\'>
				<p></p>
				
				<label><strong>%s</strong></label>
				<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value=\'&lt;div style="display: none;" class="logichop-js" data-cid="%d" data-event="show" data-not="false"  data-css-add="" data-css-remove=""&gt;&lt;/div&gt;\'>
				<p></p>
				
				<hr>
				
				<p>
					<label for="logichop_css_condition" class="selectit"><input type="checkbox" id="logichop_css_condition" name="logichop_css_condition" %s>
						<strong>%s</strong>
					</label>
				</p>
				
				<div class="logichop-css %s">
					<label><strong>%s</strong></label>
					<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value="lh-%s">
					<p></p>
				
					<label><strong>%s</strong></label>
					<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value="logichop-%s">
					<p></p>
				
					<label><strong>%s</strong></label>
					<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value="logichop-not-%s">
					<p></p>
				</div>
				
				%s
				<textarea rows="3" cols="40" id="excerpt" name="excerpt" style="width: 100%%; margin-top: 5px;" class="logichop-condition-excerpt logichop-condition-excerpt-hide">%s</textarea>
				
				<script>
					var logichop_data = %s;
					var logichop_goals = %s;
					var logichop_pages = %s;
					var logichop_text = %s;
					%s
				</script>
				',
				__('Condition Description', 'logichop'),
				$description,
				__('Conditional Statements', 'logichop'),
				
				__('Shortcode', 'logichop'),
				$post->ID,				
				$post->post_name,
				__('Not Shortcode', 'logichop'),
				$post->ID,				
				$post->post_name,
								
				__('Javascript HTML', 'logichop'),
				$post->ID,
				
				($css_condition) ? 'checked' : '',
				__('Enable Conditional CSS', 'logichop'),
				
				($css_condition) ? '' : 'logichop-condition-excerpt-hide',
				__('CSS Body Class', 'logichop'),				
				$post->post_name,
				__('CSS Class', 'logichop'),				
				$post->post_name,
				__('CSS Not Class', 'logichop'),				
				$post->post_name,
				
				
				sprintf('<div class="logichop-condition-logic-label"><small><a href="#" class="logichop-condition-logic">%s</a></small></div>', __('Show Conditional Logic', 'logichop')),
				$post->post_excerpt,
				htmlspecialchars_decode($data),
				$goals,
				$pages,
				json_encode($conditions_text),
				$condition_vars
			);
	}
	
	/**
	 * Saves condition builder metabox data
	 *
	 * @since    1.0.0
	 * @param		integer		$post_id	Post ID
	 */
	public function condition_builder_save ($post_id) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'logichop_metabox_description_nonce')) return;
		if (!current_user_can('edit_post', $post_id)) return;
		
		$checkbox = (isset($_POST['logichop_css_condition'])) ? true : false;
		update_post_meta($post_id, 'logichop_css_condition', wp_kses($checkbox,''));
		
		if (isset($_POST['logichop_condition_description'])) {
			update_post_meta($post_id, 'logichop_condition_description', wp_kses($_POST['logichop_condition_description'],''));
		}
	}
	
	/**
	 * Displays goal tracker metabox on Goal editor
	 *
	 * @since    1.0.0
	 * @param		object		$post		Wordpress Post object
	 * @return		string		Echos metabox form
	 */
	public function goal_detail_display ($post) {
		
		$goal_detail = '';
		if ($_SESSION['logichop']) {
			$data = $this->logic->api_post('event-detail', array('event-id' => $post->ID));
			$completed = isset($data['Event']['Views']) ? (int) $data['Event']['Views'] : 0;
			$goal_detail = sprintf(__('Goal triggered <a href="%s%d">%s %s</a> since %s.', 'logichop'),
								admin_url('admin.php?page=logichop-insights&goal='),
								$post->ID,
								$completed,
								($completed == 1) ? __('time', 'logichop') : __('times', 'logichop'),
								get_the_date(false, $post->ID)
							);
		}
		
		$values			= get_post_custom($post->ID);
		$lead_adjust	= isset($values['logichop_goal_lead_score_adjust']) ? esc_attr($values['logichop_goal_lead_score_adjust'][0]) : 0;
		$lead_freq		= isset($values['logichop_goal_lead_freq']) ? esc_attr($values['logichop_goal_lead_freq'][0]) : 'every';
		
		printf('
				<textarea rows="3" cols="40" id="excerpt" name="excerpt" style="width: 100%%; margin-top: 5px;" class="">%s</textarea>
				<p></p>
				
				<label><strong>%s </strong></label>
				<input type="number" style="margin-top: 5px;" class="" id="logichop_goal_lead_score_adjust" name="logichop_goal_lead_score_adjust" value="%s">
				<select id="logichop_goal_lead_freq" name="logichop_goal_lead_freq" style="margin-top: -3px;">
		 			<option value="every" %s>%s</option>
		 			<option value="first" %s>%s</option>
		 			<option value="session" %s>%s</option>
		 		</select>
				<p></p>
				
				%s
				<p></p>
								
				<label><strong>%s</strong></label>
				<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value=\'[logichop_goal goal=%d goal-name="%s"]\'>
				<p></p>
				
				<label><strong>%s</strong></label>
				<input type="text" onfocus="this.select();" readonly="readonly" style="width: 100%%; margin-top: 5px;" class="" value="&lt;script&gt;logichop_goal(%d);&lt;/script&gt;">
				',
				$post->post_excerpt,
				__('Logic Hop Lead Score:', 'logichop'),
				$lead_adjust,
				($lead_freq == 'every') ? 'selected' : '',
				__('Increment on every complete', 'logichop'),
				($lead_freq == 'first') ? 'selected' : '',
				__('Increment on first complete only', 'logichop'),
				($lead_freq == 'session') ? 'selected' : '',
				__('Increment on first complete each session', 'logichop'),
				$goal_detail,
				__('Shortcode', 'logichop'),
				$post->ID,
				$post->post_name,
				__('Javascript', 'logichop'),
				$post->ID
			);
			
		wp_nonce_field('logichop_goal_event_nonce', 'meta_box_nonce');
	}
	
	/**
	 * Saves goal metabox data
	 *
	 * @since   	2.0.0
	 * @param		integer		$post_id	Post ID
	 */
	public function goal_detail_save ($post_id) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'logichop_goal_event_nonce')) return;
		if (!current_user_can('edit_post', $post_id)) return;
		
		if (isset($_POST['logichop_goal_lead_score_adjust'])) update_post_meta($post_id, 'logichop_goal_lead_score_adjust', wp_kses($_POST['logichop_goal_lead_score_adjust'],''));
		if (isset($_POST['logichop_goal_lead_freq'])) update_post_meta($post_id, 'logichop_goal_lead_freq', wp_kses($_POST['logichop_goal_lead_freq'],''));
		
		do_action('logichop_event_save', $post_id); 
	}
	
	/**
	 * Get Active Plugins.
	 *
	 * @since    1.0.0
	 * @return		array		Active Plugin Name/Version
	 */
	public function get_active_plugins ($list = false) {
		$active		= array();
		$plugins 	= get_plugins();
		$output 	= '<ul class="logichop-ul-blank">';
		
		foreach ($plugins as $k => $p) {
			if (is_plugin_active($k)) {
				$active[] = sprintf('%s, %s', $p['Name'], $p['Version']);
				$output .= sprintf('<li>%s, %s</li>', $p['Name'], $p['Version']);
			}
		}
		$output .=  '</ul>';
		
		if (!$list) return $active;
		return $output;		
	}
	
	/**
	 * Register Admin CSS.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles ($hook) {
		if (in_array($hook, array('post.php', 'post-new.php', 'logic-hop_page_logichop-insights', 'logic-hop_page_logichop-settings', 'widgets.php'))) {
			wp_enqueue_style('thickbox');
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.min.css', array(), $this->version, 'all' );
		}
		
		do_action('logichop_admin_enqueue_styles', $hook); 
	}

	/**
	 * Register Admin JavaScript.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts ($hook) {
		global $post_type;
		
		if ($post_type == 'logichop-conditions' && in_array($hook, array('post.php', 'post-new.php'))) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/condition-builder.min.js', array( 'jquery' ), $this->version, false );
	   		wp_dequeue_script('autosave');
		}
		
		if (in_array($hook, array('post.php', 'post-new.php', 'widgets.php'))) {
			wp_enqueue_script('thickbox');
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/editor.min.js', array( 'jquery' ), $this->version, false );
		}
		
		if ($hook == 'logic-hop_page_logichop-insights') {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/Chart.min.js', array( 'jquery' ), $this->version, false );
		}
		
		do_action('logichop_admin_enqueue_scripts', $hook, $post_type); 
	}
}


