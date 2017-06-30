$(document).ready(function(){
	$("#cart_payment form").bind("submit",function(){
		var form = $(this);
		if($(form).find("input[name='payment']:checked").length==0)
		{
			$.showErr("请选择支付方式");
			return false;
		}
		var money = $.trim($(form).find("input[name='money']").val());
		if(money==""||isNaN(money)||parseFloat(money)<=0)
		{
			$.showErr("请输入正确的金额");
			return false;
		}
		return true;
	});
});