$(document).ready(function(){

	init_side_deal_item();
	init_favdeal_list();

});
function init_side_deal_item()
{
	$(".side_deal_list li").hover(function(){
		$(this).addClass("active");
	},function(){
		$(this).removeClass("active");
	});
}

/**
 * 加载右侧的猜你喜欢 
 */
function init_favdeal_list()
{	
	var query = new Object();
	query.act = "change_favdeal";
	query.deal_id = $(this).attr("deal_id");
	$(".change_favdeal").bind("click",function(){
		$("#favdeal_list").html("<div class='loading'></div>");
		$.ajax({
			url:AJAX_URL,
			data:query,
			type:"POST",
			dataType:"json",
			success:function(obj){
				$("#favdeal_list").html(obj.html);
				init_ui_lazy();
				init_side_deal_item();
			}
		});
	});
}