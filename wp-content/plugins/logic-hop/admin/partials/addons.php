<?php

	$integrations = $this->logic->api_post('integrations');
	
	$int_content = '';
	if (isset($integrations['Integrations'])) {
		foreach ($integrations['Integrations'] as $int) {
			$int_content .= sprintf('<div style="padding: 0 20px 5px; margin-top: 10px; border: 4px solid #f1f1f1">
										<h2 style="margin-bottom: 8px;"><a href="%s" title="%s" target="_blank">%s</a></h2>
										%s
										<p style="text-align: center">
											<a href="%s" title="%s" target="_blank" class="button button-primary">%s</a>
										</p>
									</div>',
									$int['URL'],
									$int['Title'],
									$int['Title'],
									$int['Description'],
									$int['URL'],
									$int['Title'],
									$int['Button']
								);	
		}
	} else {
		$int_content = '<h2>There are currently no add-ons available.</h2>Please visit <a href="https://logichop.com" target="_blank">Logic Hop</a> for more information.';
	}
	
	printf('<div class="logichop_settings_container">
			<h3>Logic Hop Add-ons</h3>
			<p>
				Need a specific Logic Hop add-on or integration? <a href="https://logichop.com/contact/" target="_blank" title="Contact us">Contact us</a> and let us know!
			</p>
			%s
			</div>',
			$int_content
		);
		
		