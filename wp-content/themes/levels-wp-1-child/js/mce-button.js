// Add a button to MCE / Visual Editor

(function() {
	tinymce.PluginManager.add('levels_child_mce_button', function( editor, url ) {
		editor.addButton('levels_child_mce_button', {
			text: 'Levels Child',
			icon: false,
			onclick: function() {
        editor.focus();
				editor.selection.setContent('[course]' + editor.selection.getContent() + '[/course]';
			}
		});
	});
})();
