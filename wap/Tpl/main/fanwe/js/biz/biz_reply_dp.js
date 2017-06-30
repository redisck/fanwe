$(document).ready(function(){
	$(".dpsub_btn").bind("click",function(){
		$("form[name='submit_reply_dp']").submit();
	});
	$("form[name='submit_reply_dp']").bind("submit",function(){
		var content=$("#reply_content").val();

		var form = $("form[name='submit_reply_dp']");
		if(!content){
			$.showErr("请填写回复内容");
			return false;		
		}
		
		var query = $(form).serialize(); 
		var ajaxurl = $(form).attr("action");
		$.ajax({
			url:ajaxurl,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data["status"]==1){ 
					$.showSuccess("回复成功",function(){
						location.href = data.jump;
					});
				}else{
					$.showErr(data.info,function(){
						if(data.jump)
							window.location=data.jump;
					});
				}
			}
			,error:function(){
				$.showErr("服务器提交错误");
			}
		});	
		return false;
	});
});



