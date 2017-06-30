$(function(){
	$("select[name='filter_point'],select[name='filter_is_img']").bind("change",function(){
		$("form[name='review_form']").submit();
	});
	
	$("button.reply_btn").bind("click",function(){
		var dp_id = $(this).attr("data-id");
		var query = new Object();
		query.dp_id = dp_id;
		query.act = "reply_dp";
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(result){
				$.weeboxs.open(result, {boxid:'reply_weebox',contentType:'text',showButton:false,title:"点评回复",width:570,type:'wee',onopen:function(){
						init_ui_button();
						init_ui_textbox();
						$("button.reply_submit_btn").bind("click",function(){
							var reply_content = $("textarea[name='reply_content']").val();
							query.act = "do_reply_dp";
							query.reply_content = reply_content;
							$.ajax({
								url:ajax_url,
								data:query,
								type:"post",
								dataType:"json",
								success:function(result){
									if(result.status){
										if($(".review_cnt_"+dp_id+" p.exp").length>0){
											$(".review_cnt_"+dp_id+" p.exp").html(result.msg);
										}else{
											$(".review_cnt_"+dp_id).append("<p class=\"exp\">"+result.msg+"</p>");
										}
										$.showSuccess("回复成功");
										$.weeboxs.close("reply_weebox");
									}else{
										$.weeboxs.close("reply_weebox");
										$.showErr(result.msg);
									}
								}
							});
						});
					}
				});
			}
		});
	});
});
