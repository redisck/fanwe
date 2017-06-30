$(function(){
	//优惠券验证
	$("form[name='event_form']").submit(function(){
		var form = $("form[name='event_form']");
		var event_sn = $.trim($("input[name='event_sn']").val());
		var location_id = $("select[name='location_id']").val();
		if(event_sn.length==0){
			$.showErr("请输入活动报名序列号");
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
						$.showConfirm("验证活动: "+result.msg,function(){
							var query2 = new Object();
							query2.act = "use_event";
							query2.event_sn = event_sn;
							query2.location_id = location_id;
							$.ajax({
								url:ajax_url,
								type : "POST",
								data : query2,
								dataType : "json",
								success :function(result){
									//消费成功
									if(result.status == 1){
										$.showSuccess("活动报名验证成功");
										$("input[name='event_sn']").val("");
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


