$(document).ready(function(){
	$("#amountdesc").bind("blur",function(){init_exchange_form();});
	$("#key").bind("change",function(){init_exchange_form();});
	
	if(ALLOW_EXCHANGE == 1){
		init_exchange_form();
	}
	
	
	$("#doexchange").bind("click",function(){
		var amountsrc = $("#amountsrc").val();  //所需的值
		var amountdesc = $("#amountdesc").val();
		var titledesc = $("#key").find("option:selected").attr("rel");
		var titlesrc = $("#titlesrc").html();
		var password = $("#user_pwd").val();
		if(isNaN(amountdesc)||parseInt(amountdesc)<=0)
		{
			amountdesc = 1;
		}
		else
		{
			amountdesc = Math.floor(amountdesc);
		}
		var key = $("#key").val();
		if(isNaN(amountsrc)||amountsrc<=0)
		{
			$.showErr("兑换所消耗的"+titlesrc+"不能为0，请兑换更多的"+titledesc, function(){
				$("#amountdesc").focus();				
			});
			return false;
		}
		if(password.length <=0){
			$.showErr("请输入登录密码");
			return false;
		}
		$.showConfirm("确定使用["+amountsrc+"]"+titlesrc+"兑换["+amountdesc+"]"+titledesc+"吗？",function(){
			//开始ajax请求
			var query = new Object();
			query.password = password;
			query.key = key;
			query.amountdesc = amountdesc;
			query.act = "doexchange";
			 $.ajax({ 
					url: AJAX_URL,
					dataType: "json",
					data:query,
					type:"post",
					success: function(obj){
						if(obj.status == -1000){
							ajax_login();
						}else if(obj.status){
							$.showSuccess("兑换成功",function(){
								$("#user_pwd").val("");
								$("#amountdesc").val("1");
								init_exchange_form();
								location.reload();
							});
						}
						else
						{
							$("#user_pwd").val("");
							$("#amountdesc").val("1");
							$("#amountsrc").val("1");
							
							$.showErr(obj.message);
						}
					},
					error:function(ajaxobj)
					{
						if(ajaxobj.responseText!='')
						alert(ajaxobj.responseText);
					}
				});	
			//end
			
		});
	});
});
function init_exchange_form()
{
	var amountdesc = $("#amountdesc").val();
	if(isNaN(amountdesc)||parseInt(amountdesc)<=0)
	{
		amountdesc = 1;
		$("#amountdesc").val("1");
	}
	else
	{
		amountdesc = Math.floor(amountdesc);
		$("#amountdesc").val(amountdesc);
	}
	var key = $("#key").val();
	var titlesrc = EXCHANGE_JSON_DATA[key]['srctitle'];  //所需的标题
	var amountsrc = Math.floor(amountdesc * EXCHANGE_JSON_DATA[key]['ratio']);
	$("#amountsrc").val(amountsrc);
	$("#titlesrc").html(titlesrc);
}
