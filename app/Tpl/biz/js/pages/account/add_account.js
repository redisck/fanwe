$(function(){
	$("form[name='add_account_form']").submit(function(){
		var form = $("form[name='add_account_form']");
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
		if($("#account_name").val() == "") {
			$("#account_name").focus();
			obj = $("#account_name");
			is_error = 1;
			error_msg = "请正确输入账户！";
		}
		if($("#account_password").val() == "" && is_error == 0) {
			$("#account_password").focus();
			obj = $("#account_password");
			is_error = 1;
			error_msg = "请输入此账号的密码！";
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
					$.showConfirm("添加成功！是否前往设置子账号权限",function(){
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
	
	$("#account_name").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data.length > 0) {
			var query = new Object();
			query.act = "check_account_name";
			query.account_name = $(this).val();
			$.ajax({
				type : "POST",
				url : AJAX_URL,
				data : query,
				dataType : "json",
				success : function(data) {
					if(data.error == 1) {
						$.Show_field_error(obj);
						$.Show_error_tip('该账户名已经存在！');
						$(obj).focus();
					} else {
						$.Close_top_tip();
						$.Show_field_success(obj);
					}
				}
			});
		} else {
			$.Show_error_tip('请输入帐号！');
			$.Show_field_error(obj);
		}

	});
	
	$("#account_password").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();

		if($.trim(val_data) =='') {
				$.Show_field_error(obj);
				$.Show_error_tip('请输入此账号的密码！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}

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
