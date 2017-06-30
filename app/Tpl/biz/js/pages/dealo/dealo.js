$(document).ready(function(){
	
	$(".do_refund_coupon").bind("click",function(){
		var id = $(this).attr("rel");
		$.showConfirm("确定同意退款操作吗？",function(){
			var query = new Object();
			query.act = "do_refund_coupon";
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
	
	$(".do_refuse_coupon").bind("click",function(){
		var id = $(this).attr("rel");
		$.showConfirm("确定拒绝退款操作吗？",function(){
			var query = new Object();
			query.act = "do_refuse_coupon";
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