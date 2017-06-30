$(document).ready(function(){
	$(".check_delivery[ajax='true']").bind("click",function(){
		return false;
	});
	$(".check_delivery[ajax='true']").hover(function(){
		var id = "delivery_box_"+$(this).attr("rel");
		$("#"+id).stopTime();
		var dom = $(this);
		if($("#"+id).length>0)
		{
			$("#"+id).show();
		}
		else
		{
			var html = "<div id='"+id+"' class='check_delivery_pop'><div class='loading'></div></div>";
			var box = $(html);
			$("body").append(box);
			$(box).css({"position":"absolute","left":$(dom).position().left-80,"top":$(dom).position().top+20,"z-index":10});
			$.ajax({
				url:$(dom).attr("action"),
				type:"POST",
				dataType:"json",
				success:function(obj){
					if(obj.status)
					{
						$(box).html(obj.html);
					}
					else
					{
						$(box).remove();
					}
				}
			});
		}
		$("#"+id).hover(function(){
			$("#"+id).stopTime();
			$("#"+id).show();
		},function(){
			$("#"+id).oneTime(300,function(){
				$("#"+id).remove();
			});
		});
	},function(){
		var id = "delivery_box_"+$(this).attr("rel");
		if($("#"+id).length>0)
		{
			$("#"+id).oneTime(300,function(){
				$("#"+id).remove();
			});
		}
		
	});
	
	
	$(".do_delivery").bind("click",function(){
		var query = new Object();
		query.act = "load_delivery_form";
		query.id = $(this).attr("rel");
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status==1000)
				{
					location.reload();
				}
				else if(obj.status ==0)
				{
					$.showErr(obj.info);
				}
				else
				{
					$.weeboxs.open(obj.html, {boxid:'delivery_form',contentType:'text',showButton:false, showCancel:false, showOk:false,title:'发货',width:550,type:'wee',onopen:function(){
						init_ui_button();
						init_ui_select();
						init_ui_textbox();
						
						$("form[name='delivery_form']").bind("submit",function(){
							var url = $(this).attr("action");
							var query = $(this).serialize();
							$.ajax({
								url:url,
								data:query,
								dataType:"json",
								type:"POST",
								success:function(obj){
									$.weeboxs.close("delivery_form");
									if(obj.status)
									{
										$.showSuccess(obj.info,function(){
											location.reload();
										});
									}
									else
									{
										$.showSuccess(obj.info,function(){
											if(obj.jump)
											{
												location.href = obj.jump;
											}
										});
									}
								}
							});
							return false;
						});
						
					}});
				}
			}
		});
	});
	
	
	$(".do_verify_delivery").bind("click",function(){
		var query = new Object();
		query.act = "do_verify_delivery";
		query.id = $(this).attr("rel");
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status==1000)
				{
					location.reload();
				}
				else if(obj.status ==0)
				{
					$.showErr(obj.info);
				}
				else
				{
					$.showSuccess(obj.info,function(){
						location.reload();
					});
				}
			}
		});
	});
	
	$(".do_refund_item").bind("click",function(){
		var id = $(this).attr("rel");
		$.showConfirm("确定同意退款操作吗？",function(){
			var query = new Object();
			query.act = "do_refund_item";
			query.id = id;
			$.ajax({
				url:AJAX_URL,
				data:query,
				dataType:"json",
				type:"POST",
				success:function(obj){
					if(obj.status==1000)
					{
						location.reload();
					}
					else if(obj.status ==0)
					{
						$.showErr(obj.info);
					}
					else
					{
						$.showSuccess(obj.info,function(){
							location.reload();
						});
					}
				}
			});
		});
	});
	
	$(".do_refuse_item").bind("click",function(){
		var id = $(this).attr("rel");
		$.showConfirm("确定拒绝退款操作吗？",function(){
			var query = new Object();
			query.act = "do_refuse_item";
			query.id = id;
			$.ajax({
				url:AJAX_URL,
				data:query,
				dataType:"json",
				type:"POST",
				success:function(obj){
					if(obj.status==1000)
					{
						location.reload();
					}
					else if(obj.status ==0)
					{
						$.showErr(obj.info);
					}
					else
					{
						$.showSuccess(obj.info,function(){
							location.reload();
						});
					}
				}
			});
		});
	});
	
});