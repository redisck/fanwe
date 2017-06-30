$(function(){
	$("form[name='user_login_form']").bind("submit",function(){
		var account_name = $.trim($("input[name='account_name']").val());
		var account_password = $.trim($("input[name='account_password']").val());
		var form = $("form[name='user_login_form']");
		if(!account_name){
			alert("请填写账户名称");
			return false;
		}	
		if(!account_password){
			alert("请输入密码");
			return false;
		}
		
		var query = $(form).serialize(); 
		var ajaxurl = $(form).attr("action");
		$.ajax({
			url:ajaxurl,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data["status"]==1){ 
					$.showSuccess(data.info,function(){
						location.href = data.jump;
					});
				}else{
					$.showErr(data.info);
					return false;
				}
			}
			,error:function(){
				$.showErr("服务器提交错误");
				return false;
			}
		});	
		return false;
	});
});