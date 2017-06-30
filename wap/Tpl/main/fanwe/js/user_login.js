function setTab(name,cursel,n){
	 for(i=1;i<=n;i++){
	  var menu=document.getElementById(name+i);
	  var con=document.getElementById("con_"+name+"_"+i);
	  menu.className=i==cursel?"hover":"";
	  con.style.display=i==cursel?"block":"none";
	 }
}
    
$(document).ready(function(){
	$("#com_login_box").bind("submit",function(){
		
		var user_key = $.trim($(this).find("input[name='user_key']").val());
		var user_pwd = $.trim($(this).find("input[name='user_pwd']").val());
		if(user_key=="")
		{
			$.showErr("请输入登录帐号");
			return false;
		}
		if(user_pwd=="")
		{
			$.showErr("请输入密码");
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
	
	
	
	$("#ph_login_box").bind("submit",function(){
		
		var mobile = $.trim($(this).find("input[name='mobile']").val());
		var sms_verify = $.trim($(this).find("input[name='sms_verify']").val());
		if(mobile=="")
		{
			$.showErr("请输入手机号");
			return false;
		}
		if(sms_verify=="")
		{
			$.showErr("请输入收到的验证码");
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