<?php

	if (!defined('ABSPATH')) { header('location: /'); die; }
	
	$conditions_text = array (
		'info_default'	=> __('Select a Condition Type to get started.', 'logichop'), 
		'select' 		=> __('Select', 'logichop'),
		'equal' 		=> __('Equal To', 'logichop'), 
		'greater'  		=> __('Greater Than', 'logichop'), 
		'less_equal' 	=> __('Less Than Or Equal To', 'logichop'),  
		'greater_equal' => __('Greater Than Or Equal To', 'logichop'), 
		'less'  		=> __('Less Than', 'logichop'), 
		'not_equal' 	=> __('Not Equal To', 'logichop'),
		'completed' 	=> __('Completed', 'logichop'),
		'not_completed' => __('Not Completed', 'logichop'),
		'is' 			=> __('Is', 'logichop'),
		'is_not' 		=> __('Is Not', 'logichop'),
		'is_in_list' 	=> __('Is In List', 'logichop'),
		'contains' 		=> __('Contains', 'logichop'),
		'if' 			=> __('If', 'logichop'),
		'and' 			=> __('And', 'logichop'),
		'or' 			=> __('Or', 'logichop'),
		'remove'		=> __('Remove', 'logichop'),
		'details' 		=> __('Details', 'logichop'),
		'add_cond' 		=> __('Add Condition', 'logichop'),
		'remove_cond' 	=> __('Remove Condition', 'logichop'),
		'select_type'	=> __('Select Type', 'logichop'),
		'show_logic' 	=> __('Show Conditional Logic', 'logichop'),
		'hide_logic'	=> __('Hide Conditional Logic', 'logichop'),
		'first' 		=> __('First Visit', 'logichop'),
		'first_true' 	=> __('This is the user\'s first visit', 'logichop'),
		'first_false' 	=> __('Not the user\'s first visit', 'logichop'),
		'first_info' 	=> __('Is this the first time the user has visited the site.', 'logichop'),
		'direct' 		=> __('Direct Visit', 'logichop'),
		'direct_true' 	=> __('The user visited the site directly', 'logichop'),
		'direct_false' 	=> __('The user was referred by another site or link', 'logichop'),
		'direct_info' 	=> __('Has the user visited the site directly or were they referred from another site.', 'logichop'),
		'mobile' 		=> __('Mobile Device', 'logichop'),
		'mobile_true' 	=> __('True', 'logichop'),
		'mobile_false' 	=> __('False', 'logichop'),
		'mobile_info' 	=> __('Is the visitor on a mobile device. <em>(Smartphone or tablet)</em>', 'logichop'),
		'tablet' 		=> __('Tablet', 'logichop'),
		'tablet_true' 	=> __('True', 'logichop'),
		'tablet_false' 	=> __('False', 'logichop'),
		'tablet_info' 	=> __('Is the visitor using a tablet.', 'logichop'),		
		'geo' 			=> __('Location', 'logichop'),
		'geo_info' 		=> __('Is the visitor located in a specific country, region, city, etc. Location is derived from IP address and may be imprecise. <em>Logic Hop API Key Required</em>', 'logichop'),
		'ip' 			=> __('IP Address', 'logichop'),
		'ip_info' 		=> __('Is the visitor coming from a specific IP Address.', 'logichop'),	
		'lead_score' 		=> __('Lead Score', 'logichop'),
		'lead_score_info' 	=> __("The visitor's lead score.", 'logichop'),		
		'elapsed' 		=> __('Time Elapsed', 'logichop'),
		'elapsed_1' 	=> __('Since First Visit', 'logichop'),
		'elapsed_2' 	=> __('Since Last Visit', 'logichop'),
		'elapsed_3' 	=> __('Since Last Page Viewed', 'logichop'),
		'elapsed_4' 	=> __('Since This Visit Started', 'logichop'),
		'elapsed_info' 	=> __('The amount of time elapsed since the user\'s first visit, last visit, the current visit started or the last page was viewed.', 'logichop'),
		'goal' 			=> __('Goal - All Visits', 'logichop'),
		'goal_info' 	=> __('Has a specific Goal been completed or not completed by the visitor.', 'logichop'),
		'goal_cnt' 		=> __('Goal Views - All Visits', 'logichop'),
		'goal_cnt_info' => __('The number of times the visitor has completed a specific Goal.', 'logichop'),
		'current'		=> __('Current Page Views - All Visits', 'logichop'),
		'current_info'	=> __('The number of times the current page has been viewed by the visitor.', 'logichop'),
		'total' 		=> __('Total Page Views - All Visits', 'logichop'),
		'total_info' 	=> __('The number of all page views combined for the visitor.', 'logichop'),
		'specific' 		=> __('Specific Page Views - All Visits', 'logichop'),
		'specific_info' => __('The number of times the user has viewed a specific page.<br><br>Select <em>"Enable Page/Post Tracking"</em> from the editor to add specific pages and posts.', 'logichop'),
		'goal_s' 		=> __('Goal - Current Session', 'logichop'),
		'goal_info_s' 	=> __('Has a specific Goal been completed or not completed by the visitor during the current session.', 'logichop'),
		'goal_cnt_s' 		=> __('Goal Views - Current Session', 'logichop'),
		'goal_cnt_info_s' 	=> __('The number of times the visitor has completed a specific Goal during the current session.', 'logichop'),
		'current_s'			=> __('Current Page Views - Current Session', 'logichop'),
		'current_info_s'	=> __('The number of times the current page has been viewed by the visitor during the current session.', 'logichop'),
		'total_s' 		=> __('Total Page Views - Current Session', 'logichop'),
		'total_info_s' 	=> __('The number of all page views combined during the current session.', 'logichop'),
		'specific_s' 		=> __('Specific Page Views - Current Session', 'logichop'),
		'specific_info_s' => __('The number of times the user has viewed a specific page during the current session.<br><br>Select <em>"Enable Page/Post Tracking"</em> from the editor to add specific pages and posts.', 'logichop'),
		'referrer' 		=> __('Referrer', 'logichop'),
		'url' 			=> __('URL', 'logichop'),
		'referrer_info' => __('The current referring URL of the current visitor.<br>Full path including query string.<br>Internal and external referrers.', 'logichop'),
		'query' 		=> __('Query String', 'logichop'),
		'query_se' 		=> __('Query String Session', 'logichop'),
		'variable' 		=> __('Variable', 'logichop'),
		'value' 		=> __('Value', 'logichop'),
		'query_info' 	=> __('Is the query string variable set to a specific value.<br>Example: http://logichop.com/?animal=kangaroo.<br>Variable is "animal", value is "kangaroo"', 'logichop'),
		'query_se_info' => __('Has the query string variable with the specific value been set during this session.<br>Example: http://logichop.com/?animal=kangaroo.<br>Variable is "animal", value is "kangaroo"', 'logichop'),
		'user' 			=> __('User Is', 'logichop'),
		'user_in' 		=> __('Logged In', 'logichop'),
		'user_out' 		=> __('Logged Out', 'logichop'),
		'user_info' 	=> __('Is the user currently logged in to Wordpress or logged out.', 'logichop'),
		'weekday' 		=> __('Day of the Week', 'logichop'),
		'weekday_1' 	=> __('Monday', 'logichop'),
		'weekday_2' 	=> __('Tuesday', 'logichop'),
		'weekday_3' 	=> __('Wednesday', 'logichop'),
		'weekday_4' 	=> __('Thursday', 'logichop'),
		'weekday_5' 	=> __('Friday', 'logichop'),
		'weekday_6' 	=> __('Saturday', 'logichop'),
		'weekday_7' 	=> __('Sunday', 'logichop'),
		'weekday_info' 	=> __('The current day of the week starting with Monday, ending with Sunday.<br>Tuesday is less than Friday.<br>Saturday is greater than Wednesday.<br>Based on Wordpress date & time.', 'logichop'),
		'day'			=> __('Day', 'logichop'),
		'day_info'		=> __('The current numerical day of the month.<br>Based on Wordpress date & time.', 'logichop'),
		'month' 		=> __('Month', 'logichop'),
		'month_01' 		=> __('January', 'logichop'),
		'month_02' 		=> __('February', 'logichop'),
		'month_03' 		=> __('March', 'logichop'),
		'month_04' 		=> __('April', 'logichop'),
		'month_05' 		=> __('May', 'logichop'),
		'month_06' 		=> __('June', 'logichop'),
		'month_07' 		=> __('July', 'logichop'),
		'month_08' 		=> __('August', 'logichop'),
		'month_09' 		=> __('September', 'logichop'),
		'month_10' 		=> __('October', 'logichop'),
		'month_11' 		=> __('November', 'logichop'),
		'month_12' 		=> __('December', 'logichop'),
		'month_info' 	=> __('The current month of the year starting with January, ending with December.<br>March is less than July.<br>October is greater than April.<br>Based on Wordpress date & time.', 'logichop'),
		'year' 			=> __('Year', 'logichop'),
		'year_info' 	=> __('The current year.<br>Based on Wordpress date & time.', 'logichop'),
		'hour' 			=> __('Hour', 'logichop'),
		'hour_info' 	=> __('The current hour of the day.<br>2am is less than 1pm.<br>11pm is greater than 12am.<br>Based on Wordpress date & time.', 'logichop'),
		'minutes' 		=> __('Minutes', 'logichop'),
		'minutes_info' 	=> __('The current minute of the hour.<br>Most useful with greater than or less than.<br>Based on Wordpress date & time.', 'logichop'),
		'date' 			=> __('Date', 'logichop'),
		'date_info' 	=> __('The current date.<br>Format: mm/dd/yyyy.<br>Halloween 2020 is 10/31/2020.<br>Based on Wordpress date & time.', 'logichop'),
		'path' 			=> __('User Path', 'logichop'),
		'page_1' 		=> __('Page 1', 'logichop'),
		'page_2' 		=> __('Page 2', 'logichop'),
		'page_3' 		=> __('Page 3', 'logichop'),
		'page_4' 		=> __('Page 4', 'logichop'),
		'page_5' 		=> __('Page 5', 'logichop'),
		'path_info'		=> __("The current visitor’s path through the site. Up to 5 pages.<br>Leave pages unselected for fewer than 5.<br>User's path always consists of the 5 most recent pages.<br><br>Select <em>\"Enable Page/Post Tracking\"</em> from the editor to add specific pages and posts.", 'logichop'),
		'tagged' 		=> __('Tagged', 'logichop'),
		'not_tagged' 	=> __('Not Tagged', 'logichop')
	);