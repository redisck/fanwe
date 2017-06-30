$(document).ready(function(){
	count_buy_total();
	$("input[name='delivery'],input[name='payment'],input[name='all_account_money']").bind("click",function(){
		count_buy_total();	
	});
	$("input[name='ecvsn'],input[name='ecvpassword']").bind("blur",function(){
		count_buy_total();
	});
	$("*[name='ecvsn']").bind("change",function(){
		count_buy_total();
	});
	
	$("#pay-form").bind("submit",function(){
		var query = $(this).serialize();
		var action = $(this).attr("action");
		
		if(!ajaxing)
		{
			$.ajax({
				url:action,
				data:query,
				type:"POST",
				dataType:"json",
				success:function(obj){
					if(obj.status)
					{
						location.href = obj.jump;
					}
					else
					{
						if(obj.info)
						{
							$.showErr(obj.info,function(){
								if(obj.jump)
								{
									location.href = obj.jump;
								}
							});
						}
						else
						{
							if(obj.jump)
							{
								location.href = obj.jump;
							}
						}
						
					}
				}			
			});
		}
		
		return false;
	});
});

function count_buy_total()
{
	ajaxing = true;
	var query = new Object();
	
	//获取配送方式
	var delivery_id = $("input[name='delivery']:checked").val();

	if(!delivery_id)
	{
		delivery_id = 0;
	}
	query.delivery_id = delivery_id;

	
	//全额支付
	if($("input[name='all_account_money']").attr("checked"))
	{
		query.all_account_money = 1;
	}
	else
	{
		query.all_account_money = 0;
	}
	
	//代金券
	var ecvsn = $("*[name='ecvsn']").val();
	if(!ecvsn)
	{
		ecvsn = '';
	}
	var ecvpassword = $("input[name='ecvpassword']").val();
	if(!ecvpassword)
	{
		ecvpassword = '';
	}
	query.ecvsn = ecvsn;
	query.ecvpassword = ecvpassword;
	
	//支付方式
	var payment = $("input[name='payment']:checked").val();
	if(!payment)
	{
		payment = 0;
	}
	query.payment = payment;
	query.bank_id = $("input[name='payment']:checked").attr("rel");
	query.id = order_id;
	if(!isNaN(order_id)&&order_id>0)
		query.act = "count_order_total";
	else
		query.act = "count_buy_total";
	$.ajax({ 
		url: AJAX_URL,
		data:query,
		type: "POST",
		dataType: "json",
		success: function(data){
			$("#cart_total").html(data.html);	
			for(var k in data.delivery_fee_supplier)
			{
				if(data.delivery_fee_supplier[k]>=0)
					$("#delivery_fee_"+k).html("运费："+data.delivery_fee_supplier[k]+"元");
				else
				{
					if(data.delivery_info)
					$("#delivery_fee_"+k).html("不支持"+data.delivery_info['name']);
					else
					{
						$("#delivery_fee_"+k).html("");
					}
				}
			}
			if(data.pay_price == 0)
			{
				$("input[name='payment']").attr("checked",false);
			}
			ajaxing = false;
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(LANG['REFRESH_TOO_FAST']);
		}
	});	
}