$(document).ready(function(){
	init_form_star();
	$(".dpsub_btn").bind("click",function(){
		$("form[name='submit_dp']").submit();
	});
	$("form[name='submit_dp']").bind("submit",function(){
		var content=$("#content").val();
		var point=$("#point").val();
		var form = $("form[name='submit_dp']");
		if(!content){
			$.showErr("请填写评论内容");
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
					$.showSuccess(data.info,function(){
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
function init_form_star()
{
	var star_tip = ["<i class='iconfont'>&#xe635;</i>亲~给个评价吧","<i class='iconfont'>&#xe632;</i>差","<i class='iconfont'>&#xe633;</i>一般","<i class='iconfont'>&#xe634;</i>好","<i class='iconfont'>&#xe631;</i>很好","<i class='iconfont'>&#xe630;</i>非常好"];
	$(".star_tip").html(star_tip[0]);
	$(".tx_star .five_star_grey i").mouseover(function(){
		var index_v = $(this).index();
 	    var t= index_v+1;
        $(".tx_star .five_star_grey i").css("color","#e2e2e2");
	 	$(".tx_star .five_star_grey i:lt("+t+")").css("color","#ffc000");
        $("input[name='point']").val(t);
        $(".star_tip").html(star_tip[t]);
	});
}


