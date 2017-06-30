$(document).ready(function(){

	
	//订单操作
	$('.uc_dc_order_action_but').bind('click',function(){
		uc_dc_order_action_function($(this));
	});

//退款操作
    $(".uc_dc_order_has_reason").live("click",function(){
		if(!$(".uc_dc_order_has_reason").hasClass("y"))
		{
				var rsorder_view_reason='<div class="bg_fff ovhide pf w_percentage_80 bor_1 bdr5 l_percentage_10 t_percentage_25 rsorder_view_reason"><div class="clearfix lh080"><span class="f_l pl030">请输入退款原因</span><i class="icon iconfont f040 f_fe4d3d mr040 f_r rsorder_view_close_but">&#xe671;</i></div><div class="p030"><textarea placeholder="请输入您的原因" name="content"  class="uc_dc_order_has_reason_content textarea_content bor_1"></textarea></div><div class="p_017_030 w_b_s  w_b  bg_fff w_percentage_100  "><div class="w_b_f_1 pb030"><span  class="tc f_fff bg_ffb955 bdr5 h066 lh066 f026 w_percentage_100 block uc_dc_order_has_reason_but"> 确认</span></div></div></div>';    
		       $("body").append(rsorder_view_reason);
				 var uc_dc_order_has_reason_action_url=$(".uc_dc_order_has_reason").attr("action-url");
				 $(".uc_dc_order_has_reason_but").attr("action-url",uc_dc_order_has_reason_action_url);
				$(this).addClass("y");
		}
		
		
		$(".rsorder_view_close_but").click(function(){
		     $(this).parents(".rsorder_view_reason").detach();
			 $(".uc_dc_order_has_reason").removeClass("y");
	   });
	   
	      $(".uc_dc_order_has_reason_but").click(function(){
		  	if(!$(".textarea_content").val()) 
			{
				alert("请填写退款理由");
			}
			else
			{
				uc_dc_order_has_reason_function($(this));
			} 	     
		   
	});
	   
	});
	

	
	
});

//订单操作
function uc_dc_order_action_function(o){
		var query=new Object();

		var url=$(o).attr('action-url');
		$.ajax({
				url:url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
						alert(data.info);
						location.href=location.href;
					}else{
						alert(data.info);
					}
				
				}
		});
		
}
//退款操作
function uc_dc_order_has_reason_function(o){
		var query=new Object();
         query.content=$(".uc_dc_order_has_reason_content").val();		
		var url=$(o).attr('action-url');
		$.ajax({
				url:url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
						alert(data.info);
						location.href=location.href;
					}else{
						alert(data.info);
					}
				
				}
		});
		
}
