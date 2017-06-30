$(function(){
	$("form[name='youhuiv_submit_form']").bind("submit",function(){
		var youhui_sn = $.trim($("input[name='youhui_sn']").val());
		
		var form = $("form[name='youhuiv_submit_form']");

		if(!youhui_sn){
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
					$.showConfirm(obj.info,function(){
						$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" disabled="disabled" style=" background:#6C6C6C;">');
						var query = new Object();
						query.act="use_youhui";
						query.location_id = obj.data.location_id;
						query.youhui_sn = obj.data.youhui_sn;
						$.ajax({
							url:ajax_url,
							data:query,
							type:"post",
							dataType:"json",
							success:function(obj){
								if(obj.status == 1){
									$.showSuccess(obj.info);
									$("input[name='youhui_sn']").val("");
									$(".submit_btn_row").html('<input type="submit" class="plank" value="验证" >');
								}else{
									$.showErr(obj.info);
								}
							}
						});
							
						return false;
					});
					
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