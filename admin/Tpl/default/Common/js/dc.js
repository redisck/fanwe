$(document).ready(function(){

		$(".do_refund").bind("click",function(){		
		var action = $(this).attr("action");
		var query = new Object();
		query.ajax = 1;
		$.ajax({
			url:action,
			type:"POST",
			data:query,
			dataType:"json",
			success:function(obj){
				if(obj.status)
				{
					$.weeboxs.open(obj.html, {boxid:"refund_form",contentType:'text',showButton:false,title:"退款处理",width:530,onopen:function(){
						
						var form = $("#refund_form").find("form[name='refund_form']");
						
						$("#confirm").bind("click",function(){
							var query = $(form).serialize();
							var action = $(this).attr("action");
							ajax_do_submit(action,query);
						});
						$("#refuse").bind("click",function(){
							var query = $(form).serialize();
							var action = $(this).attr("action");
							ajax_do_submit(action,query);
						});
					}});
				}
				else
				{
					alert(obj.info);
				}
				
			}
		});
	});
	
	//查看退款理由
	$(".refund_reason").bind("click",function(){	
		var action = $(this).attr("action");

		$.ajax({ 
		url: action, 
		data: "ajax=1",
		type: "POST",
		dataType:"json",
		success: function(obj){
			$.weeboxs.open(obj.info, {boxid:'close_tip',contentType:'text',showButton:true, showCancel:true, showOk:true,title:'用户申请退款的原因',width:200,type:'wee',height:80,onopen:function(){
				
			}});
		}
		});
	});
	
});

function add_table_time()
{
	$.ajax({ 
		url: ROOT+"?"+VAR_MODULE+"=DcRsItem&"+VAR_ACTION+"=add_table_time", 
		data: "ajax=1",
		type: "POST",
		success: function(obj){
			$(".add_table_time_box").append(obj);
		}
	});
}

function add_open_time()
{
	$.ajax({ 
		url: ROOT+"?"+VAR_MODULE+"=SupplierLocation&"+VAR_ACTION+"=add_open_time", 
		data: "ajax=1",
		type: "POST",
		success: function(obj){
			$(".open_time_box").append(obj);
		}
	});
}
function remove_open_time(o)
{
	$(o).parent().remove();
}

function add_delivery_price()
{
	$.ajax({ 
		url: ROOT+"?"+VAR_MODULE+"=SupplierLocation&"+VAR_ACTION+"=add_delivery_price", 
		data: "ajax=1",
		type: "POST",
		success: function(obj){
			$(".delivery_price_box").append(obj);
		}
	});
}
function remove_delivery_price(o)
{
	$(o).parent().remove();
}

function add_takeaway_package_charge()
{
	$.ajax({ 
		url: ROOT+"?"+VAR_MODULE+"=SupplierLocation&"+VAR_ACTION+"=add_takeaway_package_charge", 
		data: "ajax=1",
		type: "POST",
		success: function(obj){
			$(".takeaway_package_charge_box").append(obj);
		}
	});
}
function remove_takeaway_package_charge(o)
{
	$(o).parent().remove();
}


function jump_to(url)
{
	location.href = url;	
}


function remove_promote(o)
{
	$(o).parent().remove();
}

function addmenucate(supplier_location_id)
{
	location.href = ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=add&supplier_location_id="+supplier_location_id;
}

//交易关闭
function close_order(id)
{	

		var CLOSE_TIP=ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=close_tip&id="+id;
			$.weeboxs.open(CLOSE_TIP, {boxid:'close_tip',contentType:'ajax',showButton:true, showCancel:true, showOk:true,title:'请选择关闭交易原因',width:220,type:'wee',height:150,onopen:function(){
			
			$("#close_formx label[class='ui-radiobox']").live("click",function(){	
				$("#close_formx input[type='text']").val('');
			});
		
			$("#close_formx input[type='text']").live('focus',function(){
				$("#close_formx label").each(function(i,val){
					$(val).find("input").attr('checked',false);
				});
			});
				
			},onok:function(){
			var is_done=true;
			if($.trim($("#close_formx input[type='text']").val())==''){
				is_done=false;
				$("#close_formx label[class='ui-radiobox']").each(function(i,val){
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
						success:function(obj){
						
							$("#info").html(obj.info);
							if(obj.status==1)
							location.href=location.href;
							
						}
					});
				}
				else
				{
					alert('请选择关闭交易的原因');

				}
				
			}});
}


/**
 * 接受订单
 */
function dc_accept(id){
		//var id = parseInt($(obj).attr("data-id"));
			var query = new Object();
			query.id = id;
			$.ajax({
				url:accept_order_url,
				data:query,
				type:"post",
				dataType:"json",
				success:function(data){
						$("#info").html(data.info);
						if(data.jump)
						location.href=data.jump;

					
				}
			});
	
	}
	
	/**
 *确认收货
 */
function over_order(id){
		//var id = parseInt($(obj).attr("data-id"));
			var query = new Object();
			query.id = id;
			$.ajax({
				url:confirm_order_url,
				data:query,
				type:"post",
				dataType:"json",
				success:function(data){
						$("#info").html(data.info);
						if(data.jump)
						location.href=data.jump;

					
				}
			});
	
	}
	
/**
 *电子劵短信补发
 */
function send_coupon_sms(id){
		//var id = parseInt($(obj).attr("data-id"));
			var query = new Object();
			query.id = id;
			$.ajax({
				url:send_coupon_sms_url,
				data:query,
				type:"post",
				dataType:"json",
				success:function(data){
						$("#info").html(data.info);
						if(data.jump)
						location.href=data.jump;

					
				}
			});
	
	}
	
		
/**
 *管理验证电子劵消费成功
 */
function admin_verify(id){
	
	if(confirm("确定电子劵已经消费?")){
		//var id = parseInt($(obj).attr("data-id"));
			var query = new Object();
			query.id = id;
			$.ajax({
				url:admin_verify_url,
				data:query,
				type:"post",
				dataType:"json",
				success:function(data){
						$("#info").html(data.info);
						if(data.jump)
						location.href=data.jump;

					
				}
			});
	
	}
}	
	

	
	function ajax_do_submit(action,query)
	{
		$.ajax({
			url:action,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status)
				{					
					$.weeboxs.close("refund_form");
					alert(obj.info);
					location.reload();
				}
				else
				{
					alert(obj.info);
				}
			}
		});
	}
	
	
	
	
