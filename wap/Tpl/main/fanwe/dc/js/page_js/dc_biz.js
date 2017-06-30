$(document).ready(function(){


    //商户中心-结算
	$('#dc_biz_balance').bind('click',function(){
		dc_biz_balance($(this));
	});
	
	
	//预定接单
	$('.dc_accept_order_all').bind('click',function(){
		dc_accept_order_all($(this));
	});
	
	//关闭订单--外卖，预定--列表页
	$('.dc_close_order_all').live('click',function(){
	$(".cancel_agreement").show();
    this_url=$(this).attr("action-url");
	$(".uc_dc_order_has_reason_but").attr("action-url",this_url);
	$(".uc_dc_order_has_reason_but").click(function(){
		if(!$(".cancel_agreement_reason_text").val())
		{			
			reason=$('input:radio:checked').val();
		}
		else
		{
			reason=$(".cancel_agreement_reason_text").val();
		}
         dc_close_order_all($(this),reason);
	});
	});
	
	//取消-理由操作
	$(".cancel_agreement_reason_text").keyup (function(){
		if(!$(".cancel_agreement_reason_text").val())
		{
			$(".cancel_agreement_reason:first").attr("checked","checked");
		}
		else
		{
			$(".cancel_agreement_reason").removeAttr("checked");
		}
		
	});
	$(".cancel_agreement_reason").click(function(){
		$(".cancel_agreement_reason_text").val("");
	});
	$(".cancel_agreement_reason_close_but").click(function(){
	 $(".cancel_agreement_reason:first").attr("checked","checked");
	  $(".cancel_agreement_reason_text").val("");
	  $(".cancel_agreement").hide();
	});
});

//商户页，结算
function dc_biz_balance(o){
		var query=new Object();
	
		var url=$(o).attr('action-url');
		$.ajax({
				url:url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
						$.showSuccess(data.info,function(){
							location.href=location.href;
						});
					}else{
						alert(data.info);
					}
				
				}
		});
		
}



//外卖关闭订单
function dc_close_order_all(o,s){
		var query=new Object();
	    query.close_reason=s;
		var url=$(o).attr('action-url');	
		$.ajax({
				url:url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
						$.showSuccess(data.info,function(){
							location.href=location.href;
						});
					}else{
						alert(data.info);
					}
				
				}
		});
		
}

//预定接单
function dc_accept_order_all(o){
		var query=new Object();
		var url=$(o).attr('action-url');
		$.ajax({
				url:url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
						
						$.showSuccess(data.info,function(){
							location.href=location.href;
						});
						
					}else{
						alert(data.info);
					}
				
				}
		});
		
}
