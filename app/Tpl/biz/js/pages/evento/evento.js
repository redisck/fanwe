$(function(){
	
	$(".approval").bind("click",function(){
		var url=$(this).attr("url");
		var this_td=$(this).parent();
		$.showConfirm("确定要通过吗？",function(){				
			set_approval(url,this_td);
		});			
	});
	
	$(".refuse").bind("click",function(){
		var url=$(this).attr("url");
		var this_td=$(this).parent();
		$.showConfirm("确定要拒绝吗？",function(){				
			set_refuse(url,this_td);
		});			
	});

	/*日期控件*/
	$("input[name='begin_time']").datetimepicker({format: "Y-m-d H:i"});
	$("input[name='end_time']").datetimepicker({format: "Y-m-d H:i"});
	
});
function set_refuse(url,this_td){

	var ajaxurl = url;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		type: "GET",
		success: function(obj){
			if(obj.status==0){
				$.showErr(obj.info);
				location.href = obj.jump;
			}else if(obj.status==1){
				IS_RUN_CRON = 1;
				$.showSuccess("拒绝成功",function(){
					this_td.html(obj.show_code);
				});				
			}else{
				$.showErr("操作失败");
			}
		},
		error:function(ajaxobj)
		{
			
		}
	});		

}

function set_approval(url,this_td){

		var ajaxurl = url;
		$.ajax({ 
			url: ajaxurl,
			dataType: "json",
			type: "GET",
			success: function(obj){
				if(obj.status==0){
					$.showErr(obj.info);
					location.href = obj.jump;
				}else if(obj.status==1){
					IS_RUN_CRON = 1;
					$.showSuccess("审核成功",function(){
						this_td.html(obj.show_code);
					});				
				}else{
					$.showErr("审核失败");
				}
			},
			error:function(ajaxobj)
			{
				
			}
		});		

}

