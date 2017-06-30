$(function(){
	//验证码刷新
	$(".img_verify_box img.verify").live("click",function(){
		$(this).attr("src",$(this).attr("rel")+"?"+Math.random());
	});
	$(".img_verify_box .refresh_verify").live("click",function(){
		var img = $(this).parent().find("img.verify");
		$(img).attr("src",$(img).attr("rel")+"?"+Math.random());
	});
	
	$("form[name='user_login']").bind("submit",function(){
		var is_error = 0;
		var error_msg = '';
		var form = $("form[name='user_login']");
		if($("#account_name").val()=="")
		{
			is_error = 1;	
			error_msg = "请正确输入账户！";
		}
		if($("#account_password").val()=="" && is_error==0)
		{
			is_error = 1;	
			error_msg = "请输入密码！";
		}
		if($("#verify_code").val()=="" && is_error==0)
		{
			is_error = 1;	
			error_msg = "请输入验证码！";
		}
		if(is_error == 1){
			$(".msg_tip .msg_content").html(error_msg);
			$(".msg_tip").addClass("sysmsg_error");
			$(".msg_tip .status").addClass("s_error");
			$(".msg_tip").slideDown("slow");
			return false;
		}
		var url = $(form).attr("action");
		var query = $(form).serialize();			
		$.ajax({
			url: url,
			type: "POST",
			data:query,
			dataType: "json",
			success: function(data){
				if(data.status)
    		    {
					location.href = data.jump;							    		    	
    		    }
    		    else
    		    {			
			     	$.Show_error_tip(data.info);
			     	$("#"+data.field).focus();
    		    }
			}
		});
		
		return false;
	});


	
	$("form[name='edit_pwd_form']").bind("submit",function(){
		var is_error = 0;
		var error_msg = '';
		var new_account_password = $("#new_account_password").val();
		var rnew_account_password = $("#rnew_account_password").val();
		
		var form = $("form[name='edit_pwd_form']");
		
		if($("#account_password").val()==''){
			is_array =1;
			error_msg = "请输入原密码";
		}
		if($("#new_account_password").val()=='' && is_error ==0){
			is_error =1;
			error_msg = "请输入新密码";
		}
		if(new_account_password.length <6  && is_error ==0){
			is_error =1;
			error_msg = "新密码不能小于6位";
		}
		if($("#rnew_account_password").val()=='' && is_error ==0){
			is_error =1;
			error_msg = "请输入确认新密码";
		}
		if($("#new_account_password").val()!=$("#rnew_account_password").val()){
			is_error =1;
			error_msg = "请确认输入的两次新密码相同";
		}
		if(is_error){
			$.Show_error_tip(error_msg);
			return false;
		}
		
		var url = $(form).attr("action");
		var query = $(form).serialize();			
		$.ajax({
			url: url,
			type: "POST",
			data:query,
			dataType: "json",
			success: function(data){
				if(data.status)
    		    {
					$.showSuccess("修改成功请重新登录~",function(){location.href = data.jump;	});	
					    		    	
    		    }
    		    else
    		    {			
			     	$.showErr(data.info);
    		    }
			}
		});
		
		return false;
	});
	
	
	
});


