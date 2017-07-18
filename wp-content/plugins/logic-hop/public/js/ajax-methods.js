function logichop_goal (goal) {
	jQuery.ajax({
		type: 'POST',
		dataType: 'json',
    	url: logichop.ajaxurl,
    	data: {
        	action: 'logichop_goal',
			goal: goal,
			uncache: new Date().valueOf()
    	},
    	cache: false,
		success: function (data) {
        	return true;
    	}
	});
}

function logichop_parse_logic () {
	
	logichop.send 			= (logichop.js_track) ? true : false;
	logichop.conditions 	= [];
	logichop.variables 		= [];
	logichop.referrer 		= ('referrer' in document) ? document.referrer : '';
	
	jQuery('.logichop-js').each(function () {
		var cid = jQuery(this).attr('data-cid');
		var data_var = jQuery(this).attr('data-var');
		if (cid) {
			logichop.send = true;
			if (jQuery.inArray(cid, logichop.conditions) == -1) {
				logichop.conditions.push(cid);
			}
		}
		if (data_var && logichop.js_vars) {
			logichop.send = true;
			if (jQuery.inArray(data_var, logichop.variables) == -1) {
				logichop.variables.push(data_var);
			}
		}
	});
	
	if (logichop.send) {
		var post_data = logichop_qs();
		post_data.action = 'logichop_parse_logic';
		post_data.uncache = new Date().valueOf();
		post_data.data = logichop;
		
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: logichop.ajaxurl,
			data: post_data,
			cache: false,
			success: function (data) {
				if (data.success) {
					if (data.redirect) window.location = data.redirect;
					if (data.conditions.length > 0) logichop_conditions(data.conditions);
					if (data.variables.length > 0) logichop_variables(data.variables);
					
					if (typeof data.css != 'undefined' && data.css != '') {
						jQuery('body').addClass(data.css);
					}
				}
			}
		});
	}
}

function logichop_conditions (conditions) {
	for (var i = 0; i < conditions.length; i++) {
		jQuery('.logichop-js[data-cid="' + conditions[i].cid + '"]').each(function () {
			var el = jQuery(this);
			var not = el.attr('data-not');
			var condition = conditions[i].condition;
			if (not === 'true') {
				condition = !condition;
			}
			if (condition) {	
				var event = (el.attr('data-event')) ? el.attr('data-event').toLowerCase() : '';

				el.addClass(el.attr('data-css-add')).removeClass(el.attr('data-css-remove'));

				if (event == 'hide') el.hide();
				if (event == 'show') el.show();
				if (event == 'toggle') el.toggle();
				if (event == 'slideup') el.slideUp();
				if (event == 'slidedown') el.slideDown();
				if (event == 'slidetoggle') el.slideToggle();
				if (event == 'fadein') el.fadeIn();
				if (event == 'fadeout') el.fadeOut();
				if (event == 'fadetoggle') el.fadeToggle();
								
				if (event == 'callback') {
					var callback = window[el.attr('data-callback')];
					if (typeof callback === 'function') {
						var args = [el];
						callback.apply(null, args);
					}
				}
			}
		});
	}
}

function logichop_variables (vars) {
	for (var i = 0; i < vars.length; i++) {
		if (vars[i].data_var && vars[i].value) {
			jQuery('.logichop-js[data-var="' + vars[i].data_var + '"]').each(function () {
				var el = jQuery(this);
				var type = (el.attr('data-type')) ? el.attr('data-type').toLowerCase() : 'append';
				var event = (el.attr('data-event')) ? el.attr('data-event').toLowerCase() : '';
				var charcase = (el.attr('data-case')) ? el.attr('data-case').toLowerCase() : false;
				var spaces = (el.attr('data-spaces')) ? el.attr('data-spaces').toLowerCase() : false;
				
				var value = vars[i].value;
				if (charcase == 'upper') value = value.toUpperCase();
				if (charcase == 'lower') value = value.toLowerCase();
				if (spaces) value = value.replace(/ /g, spaces);
				
				if (type == 'append') {
					el.append(value).addClass(el.attr('data-css-add')).removeClass(el.attr('data-css-remove'));
				}			
				
				if (type == 'prepend') {
					el.prepend(value).addClass(el.attr('data-css-add')).removeClass(el.attr('data-css-remove'));
				}
				
				if (type == 'replace' || type == 'html') {
					el.html(value).addClass(el.attr('data-css-add')).removeClass(el.attr('data-css-remove'));
				}
				
				if (type == 'text') {
					el.text(value).addClass(el.attr('data-css-add')).removeClass(el.attr('data-css-remove'));
				}
				
				if (type == 'value') {
					el.val(value).addClass(el.attr('data-css-add')).removeClass(el.attr('data-css-remove'));
				}
				
				if (type == 'class') {
					var data_class = el.attr('data-class');
					if (data_class) {
						var new_class = data_class.replace(/#VAR#/, value);
						el.addClass(new_class).addClass(el.attr('data-css-add')).removeClass(el.attr('data-css-remove'));
					}
				}
				
				if (type == 'source') {
					var data_src = el.attr('data-src');
					if (data_src) {
						var new_src = data_src.replace(/#VAR#/, value);
						el.attr('src', new_src);
					}
				}
	
				if (event == 'hide') el.hide();
				if (event == 'show') el.show();
				if (event == 'toggle') el.toggle();
				if (event == 'slideup') el.slideUp();
				if (event == 'slidedown') el.slideDown();
				if (event == 'slidetoggle') el.slideToggle();
				if (event == 'fadein') el.fadeIn();
				if (event == 'fadeout') el.fadeOut();
				if (event == 'fadetoggle') el.fadeToggle();
			});
		}
	}
}

if (typeof logichop.pid != 'undefined') {
	jQuery(document).ready(function () {
		logichop_parse_logic();
		
		if (typeof logichop.goal_ev != 'undefined' && typeof logichop.goal_el != 'undefined' && typeof logichop.goal_js != 'undefined') {
			jQuery(logichop.goal_el).on(logichop.goal_ev, function () {
				logichop_goal(logichop.goal_js);
			});
		}
	});
}

function logichop_qs () {
	var match,
        pl     = /\+/g,
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, ' ')); },
        query  = window.location.search.substring(1);
	var qs = {};
    while (match = search.exec(query)) qs[decode(match[1])] = decode(match[2]);
    return qs;
}


