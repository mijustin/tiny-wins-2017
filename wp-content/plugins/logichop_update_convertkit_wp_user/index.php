<?php
	
	/*
		Plugin Name: Logic Hop Update ConvertKit on WP User
		Description: Helper plugin to set Goal and add ConvertKit Tag when WordPress User is logged in & ConvertKit data is present
		Author: Logic Hop
		Version: 1.0.0
		Author URI: https://logichop.com
	*/
	
	function logichop_update_convertkit_wp_user () {
		global $logichop;
		
		$condition_is_goal_not_set 	= 265; // CONDITION ID --> IF GOAL ALL VISITS ##GOAL NAME## NOT COMPLETED
		$condition_is_goal_achieved = 262; // CONDITION ID --> IF CONVERTKIT USER DATA AVAILABLE AND USER IS LOGGED IN
		$goal_to_set 				= 264; // GOAL ID --> GOAL TO SET :: SAME AS ##GOAL NAME##
		
		if (isset($logichop)) {
			if ($logichop->get_condition($condition_is_goal_not_set)) {
				if ($logichop->get_condition($condition_is_goal_achieved)) {
					$logichop->set_goal($goal_to_set);
				}
			}
		}
	}
	add_action('shutdown', 'logichop_update_convertkit_wp_user');
	
	