$(function(){
	$("form[name='withdrawal_form']").submit(function(){
		var money = $.trim($("input[name='money']").val());
		
		if(money ==''){
			$.showErr("请输入提现金额");
			return false;
		}

		if(sms_no == 1){
			var sms_verify = $.trim($("input[name='sms_verify']").val());
			if(sms_verify == ''){
				$.showErr("请输入短信验证码");
				return false;
			}
		}else{
			var pwd_verify = $.trim($("input[name='pwd_verify']").val());
			if(pwd_verify == ''){
				$.showErr("请输入密码");
				return false;
			}
		}
		if(money>parseFloat($(".total_money").html())){
			$.showErr("提现超额");
			return false;
		}
		
		var query = new Object();
		query.act = "do_submit";
		query.money = money;
		if(sms_no == 1){
			query.sms_verify = sms_verify;
		}else{
			query.pwd_verify = pwd_verify;
		}
		
		$.ajax({
			url:ajax_url,
			data:query,
			type:"POST",
			dataType:"json",
			success:function(obj){
				if(obj.status)
				{
					$.showSuccess(obj.info,function(){if(obj.jump)window.location = obj.jump;});
				}
				else
				{
					$.showErr(obj.info,function(){if(obj.jump)window.location = obj.jump;});
				}
			}
		});
		return false;
	});
});
