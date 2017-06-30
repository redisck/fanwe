$(function(){
	//优惠券验证
	$("form[name='youhui_form']").submit(function(){
		var form = $("form[name='youhui_form']");
		var youhui_sn = $.trim($("input[name='youhui_sn']").val());
		var location_id = $("select[name='location_id']").val();
		if(youhui_sn.length==0){
			$.showErr("请输入优惠券序列号");
			return false;
		}
		var query = $(form).serialize();
		var url = $(form).attr("action");
		$.ajax({
				url : url,
				type : "POST",
				data : query,
				dataType : "json",
				success : function(result) {
					if(result.status == 1){
						$.showConfirm("是否确定使用该优惠券",function(){
							var query2 = new Object();
							query2.act = "use_youhui";
							query2.youhui_sn = youhui_sn;
							query2.location_id = location_id;
							$.ajax({
								url:ajax_url,
								type : "POST",
								data : query2,
								dataType : "json",
								success :function(result){
									//消费成功
									if(result.status == 1){
										$.showSuccess("优惠券验证成功");
										$("input[name='youhui_sn']").val("");
									}else{
										$.showErr(result.msg);
									}
								}
							});
						});
					}else{
						$.showErr(result.msg);
					}
					
					return false;
				}
			});	
		return false;
	});
	
});
