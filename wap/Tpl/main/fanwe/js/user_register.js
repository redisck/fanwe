$(document).ready(function(){
	
	
	$("#register_box").bind("submit",function(){
		
		var email = $.trim($(this).find("input[name='email']").val());
		var user_name = $.trim($(this).find("input[name='user_name']").val());
		var user_pwd = $.trim($(this).find("input[name='user_pwd']").val());
		var cfm_user_pwd = $.trim($(this).find("input[name='cfm_user_pwd']").val());
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
		if(email=="")
		{
			$.showErr("请输入邮箱地址");
			return false;
		}
		if(user_name=="")
		{
			$.showErr("请输入用户名");
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
						location.href = obj.jump;
					});					
				}
				else
				{
					$.showErr(obj.info);
				}
			}
		});
		
		return false;
	});
	
	
	
});