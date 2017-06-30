$(document).ready(function(){
	$("form[name='account_form']").bind("submit",function(){
		
		var user_name = $.trim($(this).find("input[name='user_name']").val());
		var email = $.trim($(this).find("input[name='user_email']").val());
		var user_pwd = $.trim($(this).find("input[name='user_pwd']").val());		
		var cfm_user_pwd = $.trim($(this).find("input[name='cfm_user_pwd']").val());
		if(user_name=="")
		{
			$.showErr("请输入登录帐号");
			return false;
		}
		if(email=="")
		{
			$.showErr("请输入邮箱地址");
			return false;
		}
		if(user_pwd=="")
		{
			$.showErr("请输入密码");
			return false;
		}
		if(user_pwd!=cfm_user_pwd)
		{
			$.showErr("密码输入不匹配，请确认");
			return false;
		}
		
		var query = $(this).serialize();
		var ajax_url = $(this).attr("action");
		$.ajax({
			url:ajax_url,
			data:query,
			type:"POST",
			dataType:"json",
			success:function(obj){
				if(obj.status)
				{
					$.showSuccess(obj.info,function(){
						if(obj.jump)
						location.href = obj.jump;
					});					
				}
				else
				{
					$.showErr(obj.info,function(){
						if(obj.jump)
						location.href = obj.jump;
					});
				}
			}
		});
		
		return false;
	});	
	
});