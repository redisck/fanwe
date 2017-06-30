$(function(){
	//优惠券验证
	$("form[name='dcverify_form']").submit(function(){
		var form = $("form[name='dcverify_form']");
		var verify_sn = $.trim($("input[name='verify_sn']").val());
		var location_id = $("select[name='location_id']").val();
		if(verify_sn.length==0){
			$.showErr("请输入电子券序列号");
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
						$.showConfirm(result.msg,function(){
							var query2 = new Object();
							query2.act = "use_dcverify";
							query2.verify_sn = verify_sn;
							query2.location_id = location_id;
							$.ajax({
								url:ajax_url,
								type : "POST",
								data : query2,
								dataType : "json",
								success :function(result){
									//消费成功
									if(result.status == 1){
										$.showSuccess(result.msg);
										$("input[name='verify_sn']").val("");
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
