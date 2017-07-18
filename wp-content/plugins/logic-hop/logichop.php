<?php
/**
 * Plugin Name: Logic Hop
 * Plugin URI:	https://logichop.com
 * Description: Personalization for Wordpress.
 * Version:		2.1.1
 * Author:		Logic Hop
 * Author URI:	https://logichop.com/about
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: logichop
 * Domain Path: languages
 *
 * Logic Hop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Logic Hop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

if (!defined('ABSPATH')) { header('location: /'); die; }

function logichop_activate () {	
	update_option('logichop_activated', true);
	require_once plugin_dir_path(__FILE__) . 'includes/activate.php';
	LogicHop_Activate::activate();
}

function logichop_deactivate () {
	require_once plugin_dir_path(__FILE__) . 'includes/deactivate.php';
	LogicHop_Deactivate::deactivate();
}

register_activation_hook(__FILE__, 'logichop_activate');
register_deactivation_hook(__FILE__, 'logichop_deactivate');

require plugin_dir_path(__FILE__) . 'includes/LogicHop.php';

function logichop_init () {
	$logichop = new LogicHop(plugin_basename(__FILE__));
	$logichop->init();
	
	if (is_admin() && get_option('logichop_activated')) {
		delete_option('logichop_activated');
		$logichop->log_activation();
    }
    
	return $logichop;
}

$logichop = logichop_init();