$(document).ready(function(){
	load_cart_list();
});



function init_cart_ui()
{
	$(".cart_table tr").hover(function(){
		$(this).addClass("active");
	},function(){
		$(this).removeClass("active");
	});
	
	$(".cart_table .minus,.cart_table .add").hover(function(){
		$(this).addClass("hover");
	},function(){
		$(this).removeClass("hover");
	});
	
	//删除的绑定
	$(".cart_table .w_op a").bind("click",function(){
		var id = $(this).attr("rel");
		$.showConfirm("确定要从购物车中移除该项目吗？",function(){
			//执行删除
			del_cart(id);
		});			
	});
	
	//计数统计
	$(".cart_table .w_num i.minus").bind("click",function(){
		var id = $(this).attr("rel");
		var num = parseInt(jsondata[id].number);
		if(num-1<=0)
		{
			$.showConfirm("确定要从购物车中移除该项目吗？",function(){
				//执行删除
				del_cart(id);
			});			
		}
		else
		{
			num = num - 1;
			recount_total(id,num);
		}		
	});
	$(".cart_table .w_num i.add").bind("click",function(){
		var id = $(this).attr("rel");
		var num = parseInt(jsondata[id].number);
		if(num+1>9999)
			num = 9999;
		else
			num = num + 1;
		recount_total(id,num);
	});
	$(".cart_table .w_num .num_ipt").bind("blur",function(){
		var id = $(this).attr("rel");
		var num = 1;
		if($.trim($(this).val())!=""&&!isNaN($(this).val()))
		{
			num = parseInt($(this).val());
		}
		if(num<=0)num=1;
		recount_total(id,num);
	});
	$(".cart_table .w_num .num_ipt").bind("focus",function(){
		$(this).select();
	});
	
	//清空购物车
	$(".cart_total .cart_btn button.remove").bind("click",function(){
		$.showConfirm("确定要清空购物车中的商品吗？",function(){
			//执行删除
			del_cart();
		});
		
	});
	
	//提交购物车
	$("form[name='cart_form']").bind("submit",function(){
		var query = $(this).serialize();
		var ajax_url = $(this).attr("action");
		$.ajax({
			url:ajax_url,
			data:query,
			type:"POST",
			dataType:"json",
			success:function(obj){
				if(obj.status==1)
				{
					location.href = obj.jump;
				}
				else if(obj.status==-1)
				{
					ajax_login();
				}
				else
				{
					$(".cart_table tr").removeClass("warning");					
					$(".cart_table tr[rel='"+obj.id+"']").addClass("warning");	
					$(".cart_table tr[rel='"+obj.id+"']").stopTime();
					$.showErr(obj.info,function(){							
						$(".cart_table tr[rel='"+obj.id+"']").oneTime(1500,function(){
							$(this).removeClass("warning");		
						});
					});
				}
			}
		});	
		return false;
	});
}

function recount_total(id,num)
{
	jsondata[id].number = parseInt(num);
	jsondata[id].total_price = jsondata[id].number * parseFloat(jsondata[id].unit_price);
	var total_price = 0;
	$.each(jsondata,function(id,row){
		$(".cart_table tr[rel='"+row.id+"']").find(".num_ipt").val(parseInt(row.number));
		$(".cart_table tr[rel='"+row.id+"']").find(".w_total span").html(Math.round(parseFloat(row.total_price)*100)/100);
		total_price+=parseFloat(row.total_price);
	});
	$("#sum").html(Math.round(total_price*100)/100);
}

function load_cart_list()
{	
		$("#cart_list").html("<div class='loading'></div>");
		var query = new Object();
		query.act = "load_cart_list";
		$.ajax({
			url:AJAX_URL,
			data:query,
			type:"POST",
			dataType:"json",
			success:function(obj){
				$("#cart_list").html(obj.html);
				init_ui_textbox();
				init_ui_button();
				init_cart_ui();
			}
		});	
}