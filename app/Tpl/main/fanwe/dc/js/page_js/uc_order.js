$(document).ready(function(){
		
	/**
	 * 继续付款
	 */
	$(".continue_pay").bind("click",function(){
		var dom = $(this);
	
			var query = new Object();
			var order_url=dom.attr('action');
			query.order_id = parseInt(dom.attr('date-i'));
			query.act = "pay_is_out_time";
			$.ajax({
				url:DC_AJAX_URL,
				type:"POST",
				dataType:"json",
				data:query,
				success:function(obj){
					if(obj.status==1000)
					{
						ajax_login();
					}
					else if(obj.status==1)
					{	
						location.href=order_url;
					}
					else if(obj.status==0)
					{
								$.weeboxs.open(obj.info , {boxid:'pay_tip',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'超时提示',width:210,type:'wee',onopen:function(){
								init_ui_button();
								
								/*
								$("#pay_done").bind("click",function(){					
									location.href = $(this).attr("url");
								});
								$("#pay_retry").bind("click",function(){
										
									location.href = $(this).attr("url");
								});
								*/	
								},onok:function(){
								location.href=location.href;
								}});
					}
				}
			});

	});
	
	
	/**
	 * 确认收货
	 */
	$(".verify_delivery").bind("click",function(){
		var dom = $(this);
		$.showConfirm("亲，您的外卖已送达？",function(){
			$.ajax({
				url:$(dom).attr("action"),
				type:"POST",
				dataType:"json",
				success:function(obj){
					if(obj.status==1000)
					{
						ajax_login();
					}
					else if(obj.status==1)
					{
							location.reload();

					}
					else if(obj.status==0)
					{
						$.showErr(obj.info);
					}
				}
			});
		});
		return false;
	});
	
	
	/**
	 * 没收到货
	 */
	$(".refuse_delivery").bind("click",function(){
		var dom = $(this);
		$.showConfirm("没收到货吗？确定提交维权订单吗？",function(){
			$.weeboxs.open(refuse_delivery_form_html, {boxid:'refuse_box',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'没收到货',width:250,type:'wee',onopen:function(){
				init_ui_button();
				init_ui_textbox();
			},onok:function(){
				var content = $("#refuse_box").find("textarea[name='content']").val();
				var query = new Object();
				query.content = content;
				$.ajax({
					url:$(dom).attr("action"),
					data:query,
					type:"POST",
					dataType:"json",
					success:function(obj){
						$.weeboxs.close("refuse_box");
						if(obj.status==1000)
						{
							ajax_login();
						}
						else if(obj.status==1)
						{							
							$.showSuccess("维权订单已提交，请等待管理员审核",function(){
								location.reload();
							});
						}
						else
						{
							$.showErr(obj.info);
						}
					}
				});
					
			}});
			return false;
			
			
			
		});
		return false;
	});
	
	
	$(".del_order").bind("click",function(){
		var dom = $(this);

			$.ajax({
				url:$(dom).attr("action"),
				type:"POST",
				dataType:"json",
				success:function(obj){
					if(obj.status==1000)
					{
						ajax_login();
					}
					else if(obj.status==1)
					{
						$.showSuccess("订单取消成功！",function(){
							location.reload();
						});
					}
					else if(obj.status==2)
					{							
						$.weeboxs.open(obj.info , {boxid:'del_tip',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'提示',width:350,type:'wee',onopen:function(){
						init_ui_button();	
						}});	
					}else if(obj.status==3 || obj.status==0){
						$.showErr(obj.info);
					}
				}
			});
	});
	
	
	$(".refund").bind("click",function(){
		var dom = $(this);
		$.showConfirm("确定要申请退款吗？",function(){
			$.ajax({
				url:$(dom).attr("action"),
				type:"POST",
				dataType:"json",
				success:function(obj){
					if(obj.status==1000)
					{
						ajax_login();
					}
					else if(obj.status)
					{
						$.weeboxs.open(obj.html, {boxid:'refund_box',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'退款申请',width:250,type:'wee',onopen:function(){
							init_ui_button();
							init_ui_textbox();
						},onok:function(){
							var form = $("form[name='refund_form']");
							var query = $(form).serialize();
							$.weeboxs.close("refund_box");
							$.ajax({
								url:$(form).attr("action"),
								data:query,
								dataType:"json",
								type:"POST",
								success:function(obj){
									if(obj.status==1000)
									{
										ajax_login();
									}
									else if(obj.status)
									{
										$.showSuccess(obj.info,function(){
											location.reload();
										});
									}
									else
									{
										$.showErr(obj.info);
									}
								}
							});
						}});
					}
					else
					{
						$.showErr(obj.info);
					}
				}
			});
		});
		return false;
	});
	
	
	$(".dc_reminder").bind("click",function(){
		var dom = $(this);
		$.ajax({
			url:$(dom).attr("action"),
			type:"POST",
			dataType:"json",
			success:function(obj){
				if(obj.status==1000)
				{
					ajax_login();
				}
				else if(obj.status==1)
				{
					$.showSuccess(obj.info);
				}
				else
				{
					$.showErr(obj.info);
				}
			}
		});
	});
	
		
	$(".rend_coupon_sms").bind("click",function(){
		var dom = $(this);
		$.ajax({
			url:$(dom).attr("action"),
			type:"POST",
			dataType:"json",
			success:function(obj){
				if(obj.status==1000)
				{
					ajax_login();
				}
				else if(obj.status==1)
				{
					$.showSuccess(obj.info);
				}
				else
				{
					$.showErr(obj.info);
				}
			}
		});
	});
	
	
	
	
});