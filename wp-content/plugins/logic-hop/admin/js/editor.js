jQuery(function($) {
	var selected_text = '';
	
	$('#content').on('mouseout mouseup keyup', function () {
		if (window.getSelection()) selected_text = window.getSelection().toString();
	});
    
	$('body').on('click', '.logichop-editor', function () {
		if ($('#wp-content-wrap').hasClass('tmce-active')) {
			try {
   				selected_text = tinyMCE.activeEditor.selection.getContent({format : 'text'});
			} catch (e) {}
		}
		$('#logichop-modal-backdrop').fadeIn();
		$('#logichop-modal-wrap').fadeIn();
	});
	
	$('#logichop-modal-backdrop, .logichop-modal-close, .logichop-modal-cancel').on('click', logichop_modal_close);
	
	function logichop_modal_close () {
		$('#logichop-modal-backdrop').hide();
		$('#logichop-modal-wrap').hide();
		$('.logichop-modal-form input').val('');
		$('.logichop-modal-form select').each(function () { $(this)[0].selectedIndex = 0; });
	}
		
	$('.logichop-modal-content .nav-tab').on('click', function (e) {
		$('.logichop-modal-content .nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.nav-tab-display').removeClass('nav-tab-display-active');
		$('.' + $(this).attr('data-tab')).addClass('nav-tab-display-active');
		e.preventDefault();
	});
	
	$('#logichop_insert_condition').on('click', function (e) {		
		var cid = $('#logichop_condition').val();
		var condition = $('#logichop_condition option:selected').attr('data-slug');
		if (cid && condition) {
			window.send_to_editor('[logichop_condition id="' + cid + '" condition="' + condition + '"]' + selected_text + '[/logichop_condition]');
			logichop_modal_close();
		}
		e.preventDefault();
	});
	
	$('#logichop_insert_condition_not').on('click', function (e) {
		var cid = $('#logichop_condition_not').val();
		var condition = $('#logichop_condition_not option:selected').attr('data-slug');
		if (cid && condition) {
			window.send_to_editor('[logichop_condition_not id="' + cid + '" condition="' + condition + '"]' + selected_text + '[/logichop_condition_not]');
			logichop_modal_close();
		}
		e.preventDefault();
	});
		
	$('#logichop_insert_goal').on('click', function (e) {
		var gid = $('#logichop_goal').val();
		var goal = $('#logichop_goal option:selected').attr('data-slug');
		if (gid && goal) {
			window.send_to_editor('[logichop_goal goal=' + gid + ' goal-name="' + goal + '"]');
			logichop_modal_close();
		}
		e.preventDefault();
	});
	
	$('#logichop_insert_conditional_goal').on('click', function (e) {
		var cid = $('#logichop_conditional').val();
		var condition = $('#logichop_conditional option:selected').attr('data-slug');
		var gid = $('#logichop_conditional_goal').val();
		var goal = $('#logichop_conditional_goal option:selected').attr('data-slug');
		if (cid && condition && gid && goal) {
			window.send_to_editor('[logichop_conditional_goal id="' + cid + '" condition="' + condition + '" goal=' + gid + ' goal-name="' + goal + '"]');
			logichop_modal_close();
		}
		e.preventDefault();
	});
	
	$('.logichop_insert_data_shortcode').on('click', function (e) {
		var variable = $($(this).attr('data-input')).val();
		if (variable) {
			window.send_to_editor("[logichop_data var='" + variable + "']");
			logichop_modal_close();
		}
		e.preventDefault();
	});
	
	$('.logichop_insert_data_javascript').on('click', function (e) {
		var data_var	= $($(this).attr('data-input')).val();
		var type 		= ($($(this).attr('data-input') + '_type').val()) ? ' data-type="'+ $($(this).attr('data-input') + '_type').val() +'"' : ''; 
		var event 		= ($($(this).attr('data-input') + '_event').val()) ? ' data-event="'+ $($(this).attr('data-input') + '_event').val() +'"' : ''; 
		
		if (data_var) {
			window.send_to_editor('<span class="logichop-js" data-var="' + data_var + '"' + type + event + '></span>');
			logichop_modal_close();
		}
		e.preventDefault();
	});
	
	$('#logichop_insert_js_condition').on('click', function (e) {
		var cid 	= $('#logichop_condition_js').val();
		var display = ($('#logichop_condition_display').val()) ? ' style="'+$('#logichop_condition_display').val()+'"' : '';
		var event 	= ($('#logichop_condition_event').val()) ? ' data-event="'+$('#logichop_condition_event').val()+'"' : '';
		var not 	= ($('#logichop_condition_not_js').val()) ? ' data-not="'+$('#logichop_condition_not_js').val()+'"' : '';
		var add 	= ($('#logichop_condition_css_add').val()) ? ' data-css-add="'+$('#logichop_condition_css_add').val()+'"' : '';
		var remove 	= ($('#logichop_condition_css_remove').val()) ? ' data-css-remove="'+$('#logichop_condition_css_remove').val()+'"' : '';
		
		if (cid) {
			if ($('#logichop_condition_event').val() != 'callback') {
				window.send_to_editor('<div class="logichop-js" data-cid="' + cid + '"' + display + event + not + add + remove + '>' + selected_text + '</div>');
			} else {
				window.send_to_editor('<script class="logichop-js" data-cid="' + cid + '"' + event + not + ' data-callback=""></script>');
			}
			logichop_modal_close();
		}
		e.preventDefault();
	});
	
	
	$('.logichop-meta-clear').on('click', function (e) {
		var element = $(this).closest('.logichop-meta');
		element.removeClass('half-set set').children('select, input').val('');
		element.children('input[type="number"]').val('0');
		e.preventDefault();
	});
		
	$('#_logichop_page_leadscore').on('change', function () {
		if ($(this).val() != 0) {
			$(this).parent().addClass('set');
		} else {
			$(this).parent().removeClass('set');
		}
	});
	
	$('#_logichop_page_goal').on('change', function () {
		if ($(this).val()) {
			$(this).parent().addClass('set');
		} else {
			$(this).parent().removeClass('set');
		}
	});
	
	$('#_logichop_track_page').on('change', function () {
		if ($(this).val() == 'enabled') {
			$(this).parent().addClass('set');
		} else {
			$(this).parent().removeClass('set');
		}
	});
	
	$('#_logichop_page_goal_condition, #_logichop_page_goal_on_condition').on('change', function () {		
		if ($('#_logichop_page_goal_condition').val() && $('#_logichop_page_goal_on_condition').val()) {
			$(this).parent().removeClass('half-set').addClass('set');
		} else if ($('#_logichop_page_goal_condition').val() || $('#_logichop_page_goal_on_condition').val()) {
			$(this).parent().removeClass('set').addClass('half-set');
		} else {
			$(this).parent().removeClass('half-set set');
		}
	});
	
	
	$('#_logichop_page_goal_js_event, #_logichop_page_goal_js_element, #_logichop_page_goal_js').on('change', logichop_js_goal_form);
	$('#_logichop_page_goal_js_element').on('keyup', logichop_js_goal_form);
	
	function logichop_js_goal_form () {
		if ($('#_logichop_page_goal_js_event').val() && $('#_logichop_page_goal_js_element').val() && $('#_logichop_page_goal_js').val()) {
			$(this).parent().removeClass('half-set').addClass('set');
		} else if ($('#_logichop_page_goal_js_event').val() || $('#_logichop_page_goal_js_element').val() || $('#_logichop_page_goal_js').val()) {
			$(this).parent().removeClass('set').addClass('half-set');
		} else {
			$(this).parent().removeClass('half-set set');
		}
	}
	
	
	$('#_logichop_page_condition, #_logichop_page_redirect').on('change', logichop_redirect_form);
	$('#_logichop_page_redirect').on('keyup', logichop_redirect_form);
	
	function logichop_redirect_form () {
		if ($('#_logichop_page_condition').val() && $('#_logichop_page_redirect').val()) {
			$(this).parent().removeClass('half-set').addClass('set');
		} else if ($('#_logichop_page_condition').val() || $('#_logichop_page_redirect').val()) {
			$(this).parent().removeClass('set').addClass('half-set');
		} else {
			$(this).parent().removeClass('half-set set');
		}
	}
});
