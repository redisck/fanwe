$(function(){

	$("form[name='bind_form']").bind('submit',function(){
		var bank_name = $.trim($("input[name='bank_name']").val());
		var bank_num = $.trim($("input[name='bank_num']").val());
		var bank_user = $.trim($("input[name='bank_user']").val());
		var sms_verify = '';
		var pwd_verify = '';
		
		//验证
		if(bank_name == ''){
			$.showErr('请填写开户行!');
			return false;
		}
			
		if(bank_num == ''){
			$.showErr('请填写卡号!');
			return false;
		}
		if(bank_user == ''){
			$.showErr('请填写持卡人!');
			return false;
		}
		
		if(sms_on == 1){
			 sms_verify = $.trim($("input[name='sms_verify']").val());
			 if(sms_verify == ''){
					$.showErr('请填写验证码!');
					return false;
				}
		}else{
			 pwd_verify = $.trim($("input[name='pwd_verify']").val());
			 if(pwd_verify == ''){
					$.showErr('请填写登录密码!');
					return false;
				}
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
				return false;
			}
		});
		
		
		return false;
	});
});