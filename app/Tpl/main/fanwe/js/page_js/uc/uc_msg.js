$(document).ready(function(){
	$(".msg_row").hover(function(){
		$(this).addClass("msg_row_hover");
		$(this).find(".msg_icon").addClass("hover");
		$(this).find(".msg_content .op a").show();
	},function(){
		$(this).removeClass("msg_row_hover");
		$(this).find(".msg_icon").removeClass("hover");
		$(this).find(".msg_content .op a").hide();
	});
	
	
	$(".msg_content .op a").bind("click",function(){
		var url = $(this).attr("action");
		$.showConfirm("确定要删除该消息吗？",function(){
			$.ajax({
				url:url,
				dataType:"json",
				type:"POST",
				success:function(obj){
					if(obj.status==1000)
					{
						ajax_login();
					}
					else if(obj.status==1)
					{
						$.showSuccess("删除成功",function(){
							location.reload();
						});
					}
					else
					{
						$.showErr(obj.info);
					}
				}
			});
		});
	});
});