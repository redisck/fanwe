$(document).ready(function(){
	init_cartnum_btn();
	init_buy_form();
});

function init_buy_form()
{
	$("#buy_form").bind("submit",function(){
		
		var query = $(this).serialize();
		var action = $(this).attr("action");

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
		
		return false;
	});
}

function init_cartnum_btn()
{
	$(".minus").bind("click",function(){
		var cart_id = $(this).attr("rel");
		var cart_num = parseInt(jsondata[cart_id]["number"]);
		var unit_price = parseFloat(jsondata[cart_id]["unit_price"]);
		jsondata[cart_id]["number"] = cart_num-1<=0?1:cart_num-1;
		jsondata[cart_id]["total_price"] = jsondata[cart_id]["number"]*unit_price;
		call_total_show(cart_id);
	});
	
	$(".plus").bind("click",function(){
		var cart_id = $(this).attr("rel");
		var cart_num = parseInt(jsondata[cart_id]["number"]);
		var cart_max = parseInt(jsondata[cart_id]["max"]);
		var unit_price = parseFloat(jsondata[cart_id]["unit_price"]);
		jsondata[cart_id]["number"] = cart_num+1>=cart_max?cart_max:cart_num+1;
		jsondata[cart_id]["total_price"] = jsondata[cart_id]["number"]*unit_price;
		call_total_show(cart_id);
	});
	
	$(".buy_number").bind("blur",function(){
		var cart_id = $(this).attr("rel");
		var cart_num = $(this).val();
		var cart_max = parseInt(jsondata[cart_id]["max"]);
		var unit_price = parseFloat(jsondata[cart_id]["unit_price"]);
		
		if(isNaN(cart_num))
			cart_num = 1;
		else
			cart_num = parseInt(cart_num);
		
		if(cart_num<=0)cart_num = 1;
		if(cart_num>=cart_max)cart_num = cart_max;
		
		jsondata[cart_id]["number"] = cart_num;
		jsondata[cart_id]["total_price"] = jsondata[cart_id]["number"]*unit_price;
		call_total_show(cart_id);
	});
	
}

function call_total_show(cart_id)
{
	var cart_item = jsondata[cart_id];
	$(".cart_row[rel='"+cart_id+"']").find(".buy_number").val(cart_item["number"]);
	$(".cart_row[rel='"+cart_id+"']").find(".cart_row_total").html(Math.round(cart_item["total_price"]*100)/100);
}