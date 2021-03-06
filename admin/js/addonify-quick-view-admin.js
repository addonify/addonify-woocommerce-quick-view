(function( $ ) {
	'use strict';

	$(document).ready(function(){

		// ios style switch
		$('input.lc_switch').lc_switch();

		
		if( addonify_objects.color_picker_is_available ){
			// initiate wp color picker
			$('.color-picker').wpColorPicker();
		}


		if( addonify_objects.code_editor_is_available ){
			// code editor
			if( $('#addonify_qv_custom_css').length ) {
				var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
				editorSettings.codemirror = _.extend(
					{},
					editorSettings.codemirror,
					{
						indentUnit: 2,
						tabSize: 2
					}
				);
				var editor = wp.codeEditor.initialize( $('#addonify_qv_custom_css'), editorSettings );
			}
		}



		// show hide content colors ------------------------------

		let $style_options_sel = $('#addonify_qv_load_styles_from_plugin');
		let $content_colors_sel = $('#addonify-content-colors-container');

		// self activate
		show_hide_content_colors( $style_options_sel.is(":checked") );

		// detect state change
		$('body').delegate('#addonify_qv_load_styles_from_plugin', 'lcs-statuschange', function() {
			show_hide_content_colors( $(this).is(':checked') );
		});

		
		function show_hide_content_colors( state ){
			if( state ){
				$content_colors_sel.slideDown();
			}
			else{
				$content_colors_sel.slideUp();
			}
		}


		// show hide view details button ------------------------------

		let $view_edtails_btn = $('#addonify_qv_show_view_detail_btn');
		let $view_details_tr = $('#addonify_qv_view_detail_btn_label').parents('tr');

		// self activate
		show_hide_view_details_label();

		// detect state change
		$('body').delegate('#addonify_qv_show_view_detail_btn', 'lcs-statuschange', function() {
			show_hide_view_details_label();
		});

		
		function show_hide_view_details_label(){

			let state = $view_edtails_btn.is(":checked");

			if( state ){
				$view_details_tr.fadeIn();
			}
			else{
				$view_details_tr.fadeOut();
			}
		}

	
	})

})( jQuery );
