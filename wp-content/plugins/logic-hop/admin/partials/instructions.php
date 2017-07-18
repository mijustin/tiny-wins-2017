<?php

	printf('<div class="logichop_settings_container">
			<h2>%s</h2>
			<ol class="logichop-ol">
				<li>%s
					<ul class="logichop-ul">
						<li>%s</li>
					</ul>
				</li>
				<li>%s
					<ul class="logichop-ul">
						<li>%s</li>
					</ul>
				</li>
				<li>%s
					<ul class="logichop-ul">
						<li>%s</li>
					</ul>
				</li>
			</ol>
			
			<p>
				<strong>%s</strong><br>
				%s
			</p>
			
			<h2>%s</h2>
			<p>%s</p>
			<p>%s</p>
			<p><strong>%s</strong></p>
			<p>%s</p>
			
			<h2>%s</h2>
			<ol>
				<li>%s
					<ul class="logichop-ul">
						<li>%s</li>
					</ul>
				</li>
				<li>%s
					<ul class="logichop-ul">
						<li>%s</li>
					</ul>
				</li>
			</ol>
			
			<h2>%s</h2>
			<ul class="logichop-ul">
				<li><a href="https://logichop.com/docs/logic-hop-quick-start-guide/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/how-to-install-and-configure-logic-hop/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/how-to-create-logic-hop-conditions/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/how-to-create-logic-hop-goals/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/using-logic-hop-with-pages-and-posts/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/using-logic-hop-shortcodes/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/using-logic-hop-with-widgets/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/using-logic-hop-with-javascript/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/using-logic-hop-as-conditional-css/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/working-with-logic-hop-insights/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/using-logic-hop-with-cache-plugins/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/condition-type-operator-reference/" target="_blank">%s</a></li>
				<li><a href="https://logichop.com/docs/plugin-theme-settings-guide/" target="_blank">%s</a></li>
			</ul>
			</div>',
			
			__('Quick Start Instructions', 'logichop'),
			__('Create a Logic Hop Condition', 'logichop'),
			__('<a href="https://logichop.com/docs/how-to-create-logic-hop-conditions/" target="_blank">Click here</a> to learn how to create a Logic Hop Condition.', 'logichop'),
			__('Create a Logic Hop Goal', 'logichop'),
			__('<a href="https://logichop.com/docs/how-to-create-logic-hop-goals/" target="_blank">Click here</a> to learn how to create a Logic Hop Goal.', 'logichop'),
			__('Add a Logic Hop Shortcode to a Page or Post', 'logichop'),
			__('<a href="https://logichop.com/docs/using-logic-hop-shortcodes/" target="_blank">Click here</a> to learn how to use Logic Hop Shortcodes.', 'logichop'),
			
			__('Getting Started Tip', 'logichop'),
			__('Create a “Day of the Week” Condition – It’s a fast, easy way to start exploring Logic Hop functionality.', 'logichop'),
			
			__('Logic Hop Data Storage & API Access', 'logichop'),
			__('Logic Hop stores user data and activity for the user\'s current session. Data is available as the user interacts with your website and remains accessible until their session expires – Typically when the user closes their browser, or after a few hours of inactivity.', 'logichop'),
			__('The optional Logic Hop API provides persistent data storage which allows you to access user data and activity every time the user visits your website.  The API also includes added functionality such as IP-based geolocation.', 'logichop'),
			__('API access and data storage are not required. Logic Hop is fully functional without an API Key.', 'logichop'),
			__('<a href="https://logichop.com/" target="_blank">Learn more</a> about the Logic Hop API.', 'logichop'),
			
			__('Logic Hop API Instructions', 'logichop'),
			__('Purchase a Logic Hop API Key.', 'logichop'),
			__('<a href="https://logichop.com/pricing/" target="_blank">Click here</a> to select a Logic Hop plan and create an account.', 'logichop'),
			__('Enter your API Key on the Logic Hop Settings page.', 'logichop'),
			__('<a href="https://logichop.com/docs/how-to-install-and-configure-logic-hop/" target="_blank">Click here</a> to learn how to install and configure Logic Hop.', 'logichop'),
			
			__('Logic Hop Documentation', 'logichop'),
			__('Logic Hop 5-Minute Quick Start Guide', 'logichop'),
			__('How to Install & Configure Logic Hop', 'logichop'),
			__('How to Create Logic Hop Conditions', 'logichop'),
			__('How to Create Logic Hop Goals', 'logichop'),
			__('Using Logic Hop with Pages & Posts', 'logichop'),
			__('Using Logic Hop Shortcodes', 'logichop'),
			__('Using Logic Hop with Widgets', 'logichop'),
			__('Using Logic Hop with Javascript', 'logichop'),
			__('Using Logic Hop as Conditional CSS', 'logichop'),
			__('Working with Logic Hop Insights', 'logichop'),
			__('Using Logic Hop with Cache Plugins', 'logichop'),
			__('Condition Type & Operator Reference', 'logichop'),
			__('Plugin & Theme Settings Guide', 'logichop')			
		);
		
	$options 	= get_option('logichop-settings');
	$theme 		= wp_get_theme();
	
	printf('<div class="logichop_settings_container">
			<h2>%s</h2>
			<ul class="logichop-ul-blank">
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong style="color: rgb(255,0,0);">%s</strong></li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
			</ul>
			</div>',
			__('Configuration', 'logichop'),
			__('Wordpress Domain:', 'logichop'),
			$_SERVER['SERVER_NAME'],
			__('Domain Name:', 'logichop'),
			(isset($options['domain']) && $options['domain']) ? $options['domain'] : __('Not Set', 'logichop'),
			__('Wordpress Version:', 'logichop'),
			$wp_version,
			__('PHP Version:', 'logichop'),
			PHP_VERSION,
			__('Logic Hop Version:', 'logichop'),
			$this->version,
			__('Logic Hop Data API:', 'logichop'),
			(isset($options['api_key']) && $options['api_key']) ? $options['api_key'] : __('Disabled', 'logichop'),
			__('Cookie TTL:', 'logichop'),
			isset($options['cookie_ttl']) ? $options['cookie_ttl'] : __('Not Set', 'logichop'),
			__('Javscript Referrer:', 'logichop'),
			isset($options['ajax_referrer']) ? $options['ajax_referrer'] : __('Not Set', 'logichop'),
			__('Cache Enabled:', 'logichop'),
			(defined('WP_CACHE') && WP_CACHE) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			__('Javscript Tracking:', 'logichop'),
			($this->logic->js_tracking()) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			(defined('WP_CACHE') && WP_CACHE && !$this->logic->js_tracking()) ? __('Cache Enabled: Javascript Tracking is recommended.', 'logichop') : '',
			__('Theme:', 'logichop'),
			sprintf('%s, %s', $theme->Name, $theme->Version),
			__('Plugins:', 'logichop'),
			$this->get_active_plugins(true)
		);
		