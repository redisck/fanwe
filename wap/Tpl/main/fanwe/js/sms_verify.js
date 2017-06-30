var lesstime = 0;
$(document).ready(function(){
	init_sms_btn($("#sms_btn"));
	$("#sms_btn").bind("click",function(){
		do_send($("#sms_btn"));
	});
});

function do_send(btn)
{
	if($.trim($("#mobile").val())=="")
	{
		$.showErr("请输入手机号码");
		return false;
	}
	
	if(lesstime>0)return;
	var query = new Object();
	query.mobile = $("#mobile").val();
	query.act = "send_sms_code";
	query.unique = $(btn).attr("unique");
	$.ajax({
		url:AJAX_URL,
		data:query,
		type:"POST",
		dataType:"json",
		success:function(obj){
			if(obj.status)
			{
				$(btn).attr("lesstime",obj.lesstime);
				init_sms_btn(btn);
				$.showSuccess(obj.info);
				
			}
			else
			{
				$.showErr(obj.info);
			}
		}
	});
}


//关于短信验证码倒计时
function init_sms_btn(btn)
{
	$(btn).stopTime();
	$(btn).everyTime(1000,function(){
		lesstime = parseInt($(btn).attr("lesstime"));
		lesstime--;
		$(btn).val("重新获取("+lesstime+")");
		$(btn).attr("lesstime",lesstime);
		if(lesstime<=0)
		{
			$(btn).stopTime();
			$(btn).val("发送验证码");
		}
	});
}