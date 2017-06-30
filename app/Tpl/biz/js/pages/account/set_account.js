$(function(){
	$("form[name='set_account_form']").submit(function(){
		var form = $("form[name='set_account_form']");
		var is_error = 0;
		var error_msg = '';
		var obj = null;
		
		var location_checked = 0;
		$("input.location_item").each(function(){
			if($(this).attr("checked"))
			   {
			    location_checked++;
			    
			   }
		});
		if(location_checked == 0){
			$.Show_error_tip("至少设置一个账号所属门店");
			return false;
		}
		
		if($("#login_password").val() == "" && is_error == 0) {
			$("#login_password").focus();
			obj = $("#login_password");
			is_error = 1;
			error_msg = "请输入验证登录密码！";
		}
		if(is_error == 1) {
			$.Show_field_error(obj);
			$.Show_error_tip(error_msg);
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
					$.showConfirm("修改成功！是否前往设置账号权限",function(){
						location.href = data.jump;
						return false;
					},function(){
						location.href = data.jump2;
						return false;
					});
					
				} else {
					if(data.field){
						$("#" + data.field).focus();
						$.Show_field_error($("#" + data.field));
					}
					$.Show_error_tip(data.info);
					
				}
			}
		});
	

		return false;
	});
	
	
	
	$("#login_password").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();

		if($.trim(val_data) =='') {
				$.Show_field_error(obj);
				$.Show_error_tip('请输入验证登录密码！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}

	});
	
});
