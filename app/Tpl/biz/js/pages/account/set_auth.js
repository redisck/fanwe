$(function() {
	
	$("label.ui-checkbox[is_main='1'] input").bind("checkon", function() {
		var module_name = $(this).parent().attr("module_name");
		$(".ui-checkbox input[module_name='" + module_name + "']").each(function(i, o) {
			$(this).attr("checked", true);
			$(this).parent().ui_checkbox({
				refresh : true
			});
		});
	});
	$("label.ui-checkbox[is_main='1'] input").bind("checkoff", function() {
		var module_name = $(this).parent().attr("module_name");
		$(".ui-checkbox input[module_name='" + module_name + "']").each(function(i, o) {
			$(this).attr("checked", false);
			$(this).parent().ui_checkbox({
				refresh : true
			});
		});
	});

	$("label.ui-checkbox[is_sub='1'] input").bind("checkon", function() {
		var module_name = $(this).attr("module_name");
		var total_count = $(".ui-checkbox input[module_name='" + module_name + "']").length;
		var count = 0;
		$(".ui-checkbox input[module_name='" + module_name + "']").each(function(i, o) {
			if($(this).attr("checked")) {
				count++;
			}
		});
		if(total_count == count) {
			$("label.ui-checkbox[module_name='" + module_name + "'] input").attr("checked", true);
			$("label.ui-checkbox[module_name='" + module_name + "']").ui_checkbox({
				refresh : true
			});
		}

	});
	
	$("label.ui-checkbox[is_sub='1'] input").bind("checkoff", function() {
		var module_name = $(this).attr("module_name");
		var total_count = $(".ui-checkbox input[module_name='" + module_name + "']").length;
		var count = 0;
		$(".ui-checkbox input[module_name='" + module_name + "']").each(function(i, o) {
			if($(this).attr("checked")) {
				count++;
			}
		});
		if(count < total_count) {
			$("label.ui-checkbox[module_name='" + module_name + "'] input").attr("checked", false);
			$("label.ui-checkbox[module_name='" + module_name + "']").ui_checkbox({
				refresh : true
			});
		}
	});
	
	$.Refresh_mainstatus = function(){
		$("label.ui-checkbox[is_main='1'] input").each(function(i,o){
			var module_name = $(this).val();
			var total_count = $(".ui-checkbox input[module_name='" + module_name + "']").length;
			var count = 0;
			$(".ui-checkbox input[module_name='" + module_name + "']").each(function(i, o) {
				if($(this).attr("checked")) {
					count++;
				}
			});
			if(total_count == count) {
				$("label.ui-checkbox[module_name='" + module_name + "'] input").attr("checked", true);
				$("label.ui-checkbox[module_name='" + module_name + "']").ui_checkbox({
					refresh : true
				});
			}
		});
	};
	$.Refresh_mainstatus();
	$("form[name='set_auth_form']").submit(function(){
		var form = $("form[name='set_auth_form']");

		var module_check = 0;
		var action_check = 0;
		
		$("input[name='module[]']").each(function(){
			if($(this).attr("checked"))
			   {
			    module_check++;
			   }
		});
		$("input[name='action[]']").each(function(){
			if($(this).attr("checked"))
			   {
			    action_check++;
			   }
		});
		
		if(module_check==0 && action_check==0){
			$.showConfirm("不选择，默认为没有任何权限");
			return false;
		}
		
		var url = $(form).attr("action");
		var query = $(form).serialize();
		$.ajax({
			url : url,
			type : "POST",
			data : query,
			dataType : "json",
			success : function(data) {
				if(data.status) {
					location.href = data.jump;
				} else {
					$.Show_error_tip(data.info);
				}
			}
		});
		return false;
	});
});
