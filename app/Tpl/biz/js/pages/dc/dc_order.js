$(function(){

	$('.pp_pay label.ui-radiobox').live('checked',function(){
		$("form[name='search_form']").submit();
	
	});
	
});


/**
 * 接受订单
 */
function dc_accept(obj){
		var id = parseInt($(obj).attr("data-id"));
			var query = new Object();
			query.act = "accept_order";
			query.id = id;
			$.ajax({
				url:ajax_url,
				data:query,
				type:"post",
				dataType:"json",
				success:function(data){
					if(data.status==1){
						$.showSuccess(data.info,function(){window.location=data.jump;});
					}else{
						$.showErr(data.info,function(){
						if(data.jump){
						window.location=data.jump;
						}
						});
						
					}
				}
			});
	
	}

function close_order(obj){
		var id = parseInt($(obj).attr("data-id"));
		var CLOSE_TIP=$(obj).attr("action");
			$.weeboxs.open(CLOSE_TIP, {boxid:'close_tip',contentType:'ajax',showButton:true, showCancel:true, showOk:true,title:'请选择关闭交易原因',width:220,type:'wee',height:150,onopen:function(){
			init_ui_button();
			init_ui_radiobox();
			init_ui_textbox();
			
			$("#close_formx label[name='close_reason']").live("click",function(){	
				$("#close_formx input[type='text']").val('');
			});
		
			$("#close_formx input[type='text']").live('focus',function(){
				$("#close_formx label").each(function(i,val){
					$(val).find("input").attr('checked',false);
					$(val).ui_radiobox({refresh:true});
				});
			});
				
			},onok:function(){
			var is_done=true;
			if($.trim($("#close_formx input[type='text']").val())==''){
				is_done=false;
				$("#close_formx label[name='close_reason']").each(function(i,val){
					if($(val).find("input").attr('checked')){
						is_done=true;

					}
				});
			}
			if(is_done){
					var url=$("form[name='close_tip']").attr('action');
					var query = $("form[name='close_tip']").serialize();
					$.ajax({
						url:url,
						data:query,
						type:"post",
						dataType:"json",
						success:function(data){
							if(data.status==1){
								$.showSuccess(data.info,function(){window.location=data.jump;});
							}else{
								$.showErr(data.info);
								
							}
						}
					});
				}
				else
				{
					$.showErr('请选择关闭交易的原因');
				}
				
			}});

	
	}

/**
 * 替用户完成订单
 */
function dc_over(obj){
		var id = parseInt($(obj).attr("data-id"));

			var query = new Object();
			query.act = "over_order";
			query.id = id;
			$.ajax({
				url:ajax_url,
				data:query,
				type:"post",
				dataType:"json",
				success:function(data){
					if(data.status==1){
						$.showSuccess(data.info,function(){window.location=data.jump;});
					}else{
						$.showErr(data.info);
						
					}
				}
			});
	
	}
