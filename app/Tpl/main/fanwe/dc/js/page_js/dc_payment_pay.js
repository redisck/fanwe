$(document).ready(function(){
	init_buy_button();
	init_ui_button();
});

function init_buy_button()
{
$("button.paybutton").bind("click",function(){

	var query = new Object();
	query.pay_id = pay_id;
	query.act = "pay_is_out_time";
		$.ajax({
					url: DC_AJAX_URL,
					data: query,
					dataType: "json",
					type: "post",
					async:false,
					success: function(obj){
					
						if(obj.status==1){
						//不超时
								
								var p_links=$('#pay_form').attr('action');
								var w=window.open();

								$.weeboxs.open(PAY_TIP, {boxid:'pay_tip',contentType:'ajax',showButton:false, showCancel:false, showOk:false,title:'支付提示',width:450,type:'wee',onopen:function(){
								init_ui_button();
								$("#pay_done").bind("click",function(){					
									location.href = $(this).attr("url");
								});
								$("#pay_retry").bind("click",function(){					
									location.href = $(this).attr("url");
								});
									
								}});
								
								w.location=p_links;
								
						}else{
						//超时
								$.weeboxs.open(PAY_OUT_TIME, {boxid:'pay_tip',contentType:'ajax',showButton:false, showCancel:false, showOk:false,title:'超时提示',width:450,type:'wee',onopen:function(){
								init_ui_button();
								$("#pay_done").bind("click",function(){					
									location.href = $(this).attr("url");
								});
								$("#pay_retry").bind("click",function(){					
									location.href = $(this).attr("url");
								});
									
								}});
						}

					}
		});
	
	
	

	});
}