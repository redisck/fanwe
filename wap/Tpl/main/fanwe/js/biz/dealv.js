$(function(){
	$("form[name='dealv_submit_form']").bind("submit",function(){
		var coupon_pwd = $.trim($("input[name='coupon_pwd']").val());
		
		var form = $("form[name='dealv_submit_form']");

		if(!coupon_pwd){
			$.showErr("请输入验证码");
			return false;
		}
		
		var query = $(form).serialize(); 
		var ajaxurl = $(form).attr("action");
		$.ajax({
			url:ajaxurl,
			data:query,
			type:"post",
			dataType:"json",
			success:function(obj){
				if(obj["status"]==1){ 
					var msg = '';
					var use_count = $("input[name='coupon_use_count']").val();
					
					if(obj.data.count>1 && use_count==''){
						msg = obj.info+"\n请输入使用张数再次验证";
						$.showConfirm(msg,function(){
							$(".coupon_use_count_box").show();
							$(".coupon_use_count_box .valid_use_count").html(obj.data.count);
							
						});
						
					}else if(obj.data.count>1 && use_count>0){
						//多张团购券有效的情况
						if(parseInt(use_count)>parseInt(obj.data.count)){
							$.showErr("输入张数有错误，请重新输入！");
							return false;
						}else{
							msg = obj.info+"\n确认使用：【"+use_count+"】张";
							$.showConfirm(msg,function(){
								$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" disabled="disabled" style=" background:#6C6C6C;">');
								var query = new Object();
								query.act="use_coupon";
								query.location_id = obj.data.location_id;
								query.coupon_pwd = obj.data.coupon_pwd;
								query.coupon_use_count = use_count;
								$.ajax({
									url:ajax_url,
									data:query,
									type:"post",
									dataType:"json",
									success:function(obj){
										if(obj.status == 1){
											$.showSuccess(obj.info);
											$("input[name='coupon_pwd']").val("");
											$("input[name='coupon_use_count']").val('');
											$(".coupon_use_count_box").hide();
											$(".coupon_use_count_box .valid_use_count").html("");
											$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" >');
										}else{
											$.showErr(obj.info);
											$("input[name='coupon_pwd']").val("");
											$("input[name='coupon_use_count']").val('');
											$(".coupon_use_count_box").hide();
											$(".coupon_use_count_box .valid_use_count").html("");
											$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" >');
										}
									}
								});
									
								return false;
							});
						}
						
					}else if(obj.data.count==1){
						//单张团购券有效的情况
						msg = obj.info+";是否确认使用？";
						$.showConfirm(msg,function(){
							$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" disabled="disabled" style=" background:#6C6C6C;">');
							var query = new Object();
							query.act="use_coupon";
							query.location_id = obj.data.location_id;
							query.coupon_pwd = obj.data.coupon_pwd;
							query.coupon_use_count = 1;
							$.ajax({
								url:ajax_url,
								data:query,
								type:"post",
								dataType:"json",
								success:function(obj){
									if(obj.status == 1){
										$.showSuccess(obj.info);
										$("input[name='coupon_pwd']").val("");
										$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" >');
									}else{
										$.showErr(obj.info);
										$("input[name='coupon_pwd']").val("");
										$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" >');
									}
								}
							});
								
							return false;
						});
					}
					
					
				}else{
					$.showErr(obj.info);
				}
				return false;
			}
			,error:function(){
				$.showErr("服务器提交错误");
				return false;
			}
		});	
		return false;
	});
});