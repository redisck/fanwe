KindEditor.ready(function(K) {
				var editor = K.editor({
					allowFileManager : true,
					pluginsPath:'{$TMPL}/js/utils/kindeditor/plugins/',
					uploadJson:'{url x="biz"  r="upload#biz_register"}',
				});
	});