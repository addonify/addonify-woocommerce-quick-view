!function(e){"use strict";e(document).ready(function(){if(e("input.lc_switch").lc_switch(),addonify_objects.color_picker_is_available&&e(".color-picker").wpColorPicker(),addonify_objects.code_editor_is_available&&e("#addonify_qv_custom_css").length){var i=wp.codeEditor.defaultSettings?_.clone(wp.codeEditor.defaultSettings):{};i.codemirror=_.extend({},i.codemirror,{indentUnit:2,tabSize:2});wp.codeEditor.initialize(e("#addonify_qv_custom_css"),i)}let t=e("#addonify_qv_load_styles_from_plugin"),o=e("#addonify-content-colors-container");function d(e){e?o.slideDown():o.slideUp()}d(t.is(":checked")),e("body").delegate("#addonify_qv_load_styles_from_plugin","lcs-statuschange",function(){d(e(this).is(":checked"))});let n=e("#addonify_qv_show_view_detail_btn"),c=e("#addonify_qv_view_detail_btn_label").parents("tr");function a(){n.is(":checked")?c.fadeIn():c.fadeOut()}a(),e("body").delegate("#addonify_qv_show_view_detail_btn","lcs-statuschange",function(){a()})})}(jQuery);