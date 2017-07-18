<?php

	if (!defined('ABSPATH')) { header('location: /'); die; }
	
	$tab = 'settings';
	if (isset($_GET['tab'])) {
		$tab = $_GET['tab'];
	}
	
	$status = false;
	if ($this->logic->get_option('api_key', false)) {
		$status = $this->logic->api_post('status');
	}
	
	$upgrade_available = '';
	if (isset($status['Client']['Version'])) {
		if ($status['Client']['Version'] > $this->version) {
			$upgrade_available = sprintf('<p><strong>%s</strong><br>%s</p>',
											__('There is a newer version of Logic Hop available.', 'logichop'),
											__('Please <a href="https://logichop.com/my-account/" target="_blank">Upgrade now</a>.', 'logichop')
									);
		}
	}
	
	
	if ((isset($status['Client']['Active']) && $status['Client']['Active'])) {
		$api_message_css = 'success';
		$api_message = '';
	} else {
		if ($this->logic->get_option('api_key', false)) {
			$api_message_css = 'error';
			$api_message = sprintf('<p><strong>%s</strong></p><p>%s %s<p>',
							__('You have entered an invalid API Key and/or Domain Name.', 'logichop'),
							__('Logic Hop is fully functional without an API Key.', 'logichop'),
							__('<a href="https://logichop.com/" target="_blank">Learn more</a> about the Logic Hop API.', 'logichop')
						);		
		} else {
			$api_message_css = 'info';
			$api_message = sprintf('<p><strong>%s</strong><br>%s</p><p>%s %s<p>',
							__('Want to remember your visitors?', 'logichop'),
							__('Store user data for future visits and get geolocation with the optional Logic Hop API.', 'logichop'),
							__('Logic Hop is fully functional without an API Key.', 'logichop'),
							__('<a href="https://logichop.com/" target="_blank">Learn more</a>.', 'logichop')
						);
		}
	}
	
	print('<div class="wrap">');
	printf('<h2>%s</h2>',
			__('Logic Hop Settings', 'logichop')
		);
	
	printf('<div class="notice notice-%s">
				<p>
					Logic Hop API %s: 
					<strong>API Data Storage & Geolocation %s</strong><br>
					<small>%s: %s</small>
					%s
				</p>
				<ul>
					<li><strong>%s:</strong> %s</li>
					<li><strong>%s %s:</strong> %s</li>
					<li><strong>%s:</strong> %s</li>
				</ul>
				<p>%s</p>
				%s
			</div>',
			$api_message_css,
			__('Status', 'logichop'),
			(isset($status['Client']['Active']) && $status['Client']['Active']) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			__('Version', 'logichop'),
			$this->version,
			$upgrade_available,
			__('Account Type', 'logichop'),
			(isset($status['Client']['Account']) && $status['Client']['Account']) ? $status['Client']['Tier'] : sprintf('<a href="http://logichop.com" target="_blank">%s</a>', __('Create an Account', 'logichop')),
			__('Account', 'logichop'),
			(isset($status['Client']['Active']) && $status['Client']['Active']) ? __('Expires', 'logichop') : __('Inactive', 'logichop'),
			(isset($status['Client']['Expires']) && $status['Client']['Expires']) ? date('M. jS, Y \a\t g:ia', strtotime($status['Client']['Expires'])) : sprintf('<a href="http://logichop.com" target="_blank">%s</a>', __('Create an Account', 'logichop')),
			__('Domain Name', 'logichop'),
			(isset($status['Client']['DomainStatus'])) ? $status['Client']['DomainStatus'] : 'N/A',
			(isset($status['Client']['Message'])) ? $status['Client']['Message'] : '',
			$api_message
		);
    
	settings_errors();
	
	$integration_tabs = '';
	$integration_tabs = apply_filters('logichop_admin_settings_tabs', $integration_tabs, $tab); 
	
	
	printf('<h2 class="nav-tab-wrapper">
            	<a href="%s" class="nav-tab %s">%s</a>
            	%s
            	<a href="%s" class="nav-tab %s">%s</a>
            	<a href="%s" class="nav-tab %s">%s</a>
        	</h2>',
        	'?page=logichop-settings',
        	($tab == 'settings') ? 'nav-tab-active' : '',
        	__('Settings', 'logichop'),
        	$integration_tabs,
        	'?page=logichop-settings&tab=instructions',
        	($tab == 'instructions') ? 'nav-tab-active' : '',
        	__('Instructions', 'logichop'),
        	'?page=logichop-settings&tab=addons',
        	($tab == 'addons') ? 'nav-tab-active' : '',
        	__('Add-ons', 'logichop')
        );
	
	if ($tab == 'settings') {
		print('<form method="post" action="options.php">');
			settings_fields( 'logichop-settings' );
			do_settings_sections( 'logichop-settings' );      
			submit_button();
		print('</form>');
	} else if ($tab == 'instructions') {
		include_once('instructions.php');
	} else if ($tab == 'addons') {
		include_once('addons.php');
	}
	
	do_action('logichop_admin_settings_page', $tab); 
	
	print('</div>');
	
	