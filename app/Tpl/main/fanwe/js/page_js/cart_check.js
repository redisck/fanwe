$(document).ready(function(){
	init_cart_list_ui();
	if($("#cart_consignee").length>0)
	{
		load_consignee();
	}
	else
	{
		count_buy_total();
	}
	init_payment_change();
	init_voucher_verify();
	init_sms_event();
	init_modify_consignee();
});

function init_modify_consignee()
{
	$("#modify_consignee").bind("click",function(){
		var query = new Object();
		query.act = "modify_consignee";
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status==1000)
				{
					ajax_login();
				}
				else if(obj.status==1)
				{
					$.weeboxs.open(obj.html, {boxid:'modify_consignee_box',contentType:'text',showButton:false, showCancel:false, showOk:false,title:'选择其他的配送地址',width:650,type:'wee',onopen:function(){
						$("#user_consignee_list").find(".select_consignee").bind("click",function(){
							var id = $(this).attr("rel");
							$("#cart_consignee").attr("rel",id);
							load_consignee();
							$.weeboxs.close("modify_consignee_box");
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
}

function init_cart_list_ui()
{
	$(".cart_table tr").hover(function(){
		$(this).addClass("active");
	},function(){
		$(this).removeClass("active");
	});
	
}

//关于购物结算页的相关脚本
//装载配送地区
function load_consignee()
{
	
		var consignee_id = $("#cart_consignee").attr("rel");
		var query = new Object();
		query.act = "load_consignee";
		query.id = consignee_id;
		query.order_id = order_id;
		$.ajax({ 
			url: AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success: function(data){
				$("#cart_consignee").html(data.html);				
				init_region_ui_change();
				init_ui_select();
				init_ui_textbox();
				load_delivery();
				
			}
		});	
	

}


/**
 * 初始化地区切换事件
 */
function init_region_ui_change(){	

	$.load_select = function(lv)
	{
		var name = "region_lv"+lv;
		var next_name = "region_lv"+(parseInt(lv)+1);
		var id = $("select[name='"+name+"']").val();
		
		if(lv==1)
		var evalStr="regionConf.r"+id+".c";
		if(lv==2)
		var evalStr="regionConf.r"+$("select[name='region_lv1']").val()+".c.r"+id+".c";
		if(lv==3)
		var evalStr="regionConf.r"+$("select[name='region_lv1']").val()+".c.r"+$("select[name='region_lv2']").val()+".c.r"+id+".c";
		
		if(id==0)
		{
			var html = "<option value='0'>="+LANG['SELECT_PLEASE']+"=</option>";
		}
		else
		{
			var regionConfs=eval(evalStr);
			evalStr+=".";
			var html = "<option value='0'>="+LANG['SELECT_PLEASE']+"=</option>";
			for(var key in regionConfs)
			{
				html+="<option value='"+eval(evalStr+key+".i")+"'>"+eval(evalStr+key+".n")+"</option>";
			}
		}
		$("select[name='"+next_name+"']").html(html);
		$("select[name='"+next_name+"']").ui_select({refresh:true});
		if(lv == 4)
		{
			load_delivery();
		}
		else
		{
			
			$.load_select(parseInt(lv)+1);
		}	
	};
	
	$("select[name='region_lv1']").bind("change",function(){
		$.load_select("1");
	});
	$("select[name='region_lv2']").bind("change",function(){
		$.load_select("2");
	});
	$("select[name='region_lv3']").bind("change",function(){
		$.load_select("3");
	});	
	$("select[name='region_lv4']").bind("change",function(){
		$.load_select("4");
	});	
}

/**
 * 加载配送方式
 * @returns
 */
function load_delivery()
{
	var select_last_node = $("#cart_consignee").find("select[value!='0']");
	if(select_last_node.length>0)
	{		
		var region_id = $(select_last_node[select_last_node.length - 1]).val();
	}
	else
	{
		var region_id = 0;
	}
	
	var query = new Object();
	query.act = "load_delivery";
	query.id = region_id;
	query.order_id = order_id;
	$.ajax({ 
		url: AJAX_URL,
		data:query,
		dataType:"json",
		type:"POST",
		success: function(obj){
			$("#cart_delivery").html(obj.html);
			$("input[name='delivery']").bind("checked",function(){
				count_buy_total();
			});
			init_ui_radiobox();
			count_buy_total();  //加载完配送方式重新计算总价
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(LANG['REFRESH_TOO_FAST']);
		}
	});	
}

function init_payment_change()
{
	$("input[name='account_money'],input[name='ecvsn'],input[name='ecvpassword']").bind("blur",function(){
		count_buy_total();
	});
	$("*[name='ecvsn']").bind("change",function(){
		count_buy_total();
	});
	$("input[name='payment']").bind("checked",function(){
		count_buy_total();
	});
	$("#check-all-money").bind("checkon",function(){
		count_buy_total();
	});
	$("#check-all-money").bind("checkoff",function(){
		$("#account_money").val("0");
		count_buy_total();
	});
}

function init_voucher_verify()
{
	$('#verify_ecv').bind("click",function(){
		var query = new Object();
		query.ecvsn = $(this).parent().find("input[name='ecvsn']").val();
		query.ecvpassword = $(this).parent().find("input[name='ecvpassword']").val();
		query.act = "verify_ecv";
		$.ajax({ 
			url: AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success: function(obj){
				$.showSuccess(obj.info);
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});
	});
}

function count_buy_total()
{

	set_buy_btn_status(false);
	var query = new Object();
	
	//获取配送方式
	var delivery_id = $("input[name='delivery']:checked").val();

	if(!delivery_id)
	{
		delivery_id = 0;
	}
	query.delivery_id = delivery_id;

	//配送地区
	var select_last_node = $("#cart_consignee").find("select[value!='0']");
	if(select_last_node.length>0)
	{		
		var region_id = $(select_last_node[select_last_node.length - 1]).val();
	}
	else
	{
		var region_id = 0;
	}
	query.region_id = region_id;
	
	//余额支付
	var account_money = $("input[name='account_money']").val();
	if(!account_money||$.trim(account_money)=='')
	{
		account_money = 0;
	}
	query.account_money = account_money;
	
	//全额支付
	if($("#check-all-money").attr("checked"))
	{
		query.all_account_money = 1;
	}
	else
	{
		query.all_account_money = 0;
	}
	
	//代金券
	var ecvsn = $("*[name='ecvsn']").val();
	if(!ecvsn)
	{
		ecvsn = '';
	}
	var ecvpassword = $("*[name='ecvpassword']").val();
	if(!ecvpassword)
	{
		ecvpassword = '';
	}
	query.ecvsn = ecvsn;
	query.ecvpassword = ecvpassword;
	
	//支付方式
	var payment = $("input[name='payment']:checked").val();
	if(!payment)
	{
		payment = 0;
	}
	query.payment = payment;
	query.bank_id = $("input[name='payment']:checked").attr("rel");
	query.id = order_id;
	if(!isNaN(order_id)&&order_id>0)
		query.act = "count_order_total";
	else
		query.act = "count_buy_total";
	$.ajax({ 
		url: AJAX_URL,
		data:query,
		type: "POST",
		dataType: "json",
		success: function(data){
			$("#cart_total").html(data.html);
			for(var k in data.delivery_fee_supplier)
			{
				if(data.delivery_fee_supplier[k]>=0)
					$("#delivery_fee_"+k).html("运费："+data.delivery_fee_supplier[k]+"元");
				else
				{
					if(data.delivery_info)
					$("#delivery_fee_"+k).html("不支持"+data.delivery_info['name']);
					else
					{
						$("#delivery_fee_"+k).html("");
					}
				}
			}
			$("input[name='account_money']").val(data.account_money);
			if(data.pay_price == 0)
			{
				$("input[name='payment']").attr("checked",false);
				$("input[name='payment']").parent().each(function(i,o){
					$(o).ui_radiobox({refresh:true});
				});
			}
			set_buy_btn_status(true);
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(LANG['REFRESH_TOO_FAST']);
		}
	});	
}

/**
 * 设置购物提交按钮状态
 */
function set_buy_btn_status(status,refresh_ui)
{
	if(!refresh_ui)
	{
		refresh_ui = false;
	}
	
	var buy_btn = $("#order_done");
	var buy_btn_ui = buy_btn.next();
	
	if(status)
	{
		if(refresh_ui)
		{
			buy_btn_ui.attr("rel","blue");
			buy_btn_ui.removeClass("disabled");
			buy_btn_ui.addClass("blue");
		}
		
		
		buy_btn.unbind("click");
		buy_btn.bind("click",function(){
			submit_buy();
		});
	}
	else
	{
		if(refresh_ui)
		{
			buy_btn_ui.attr("rel","disabled");
			buy_btn_ui.removeClass("blue");
			buy_btn_ui.addClass("disabled");
		}		
		
		buy_btn.unbind("click");
	}
	
}

//购物提交
function submit_buy()
{
	set_buy_btn_status(false,true);
	
	//提交订单
	var ajaxurl = $("#cart_form").attr("action");
	var query = $("#cart_form").serialize();
	$.ajax({
		url:ajaxurl,
		data:query,
		dataType:"json",
		type:"POST",
		success:function(obj){
			set_buy_btn_status(true,true);
			if(obj.status)
			{
				if(obj.info!="")
				{
					$.showSuccess(obj.info,function(){
						if(obj.jump!="")
							location.href = obj.jump;
					});
				}
				else
				{
					if(obj.jump!="")
						location.href = obj.jump;
				}
			}
			else
			{
				if(obj.info!="")
				{
					$.showErr(obj.info,function(){
						if(obj.jump!="")
							location.href = obj.jump;
					});
				}
				else
				{
					if(obj.jump!="")
						location.href = obj.jump;
				}
			}
		}
	});
}


/**
 * 初始化会员手机绑定的操作
 */
function init_sms_event()
{

	//验证码刷新
	$("#user_mobile img.verify").live("click",function(){
		$(this).attr("src",$(this).attr("rel")+"?"+Math.random());
	});
	$("#user_mobile .refresh_verify").live("click",function(){
		var img = $(this).parent().find("img.verify");
		$(img).attr("src",$(img).attr("rel")+"?"+Math.random());
	});
	
	//验证验证码
	$("#user_mobile").each(function(k,mobile_panel){
		if(!$(mobile_panel).find("input[name='verify_code']").attr("bindfocus"))
		{
			$(mobile_panel).find("input[name='verify_code']").attr("bindfocus",true);
			$(mobile_panel).find("input[name='verify_code']").bind("focus",function(){
				form_tip_clear($(this));
			});
		}
	});
	
	
	$("#user_mobile").each(function(k,mobile_panel){
		if(!$(mobile_panel).find("input[name='verify_code']").attr("bindblur"))
		{
			$(mobile_panel).find("input[name='verify_code']").attr("bindblur",true);
			$(mobile_panel).find("input[name='verify_code']").bind("blur",function(){
				var txt = $(this).val();
				var ipt = $(this);
				if($.trim(txt)=="")
				{
					form_tip($(this),"请输入图片文字");
				}
				else
				{
					//验证图片验证码
					ajax_check_field("verify_code",txt,0,ipt);
				}
			});
		}
	});
	
	$("#user_mobile").each(function(k,mobile_panel){
		if(!$(mobile_panel).find("input[name='user_mobile']").attr("bindblur"))
		{
			$(mobile_panel).find("input[name='user_mobile']").attr("bindblur",true);
			$(mobile_panel).find("input[name='user_mobile']").bind("blur",function(){
				var txt = $(this).val();
				var ipt = $(this);
				if($.trim(txt)=="")
				{
					form_tip($(this),"请输入手机号");
				}
				else if(!$.checkMobilePhone(txt))
				{
					form_err($(this),"手机号格式不正确");
				}
				else
				{
					//验证手机唯一性
					ajax_check_field("mobile",txt,0,ipt);
				}
			});
		}
	});
	
	
	$("#user_mobile").each(function(k,mobile_panel){
		if(!$(mobile_panel).find("input[name='sms_verify']").attr("bindfocus"))
		{
			$(mobile_panel).find("input[name='sms_verify']").attr("bindfocus",true);
			$(mobile_panel).find("input[name='sms_verify']").bind("focus",function(){
				form_tip_clear($(this));
			});
		}
	});
	
	
	$("#user_mobile").each(function(k,mobile_panel){
		if(!$(mobile_panel).find("input[name='sms_verify']").attr("bindblur"))
		{
			$(mobile_panel).find("input[name='sms_verify']").attr("bindblur",true);	
			$(mobile_panel).find("input[name='sms_verify']").bind("blur",function(){
				var txt = $(this).val();
				var ipt = $(this);
				if($.trim(txt)=="")
				{
					form_tip($(this),"请输入收到的验证码");
				}
			});
		}
	});
	
	$.init_cart_sms_btn = function()
	{
		$("#user_mobile").find("button.ph_verify_btn[init_sms!='init_sms']").each(function(i,o){
			$(o).attr("init_sms","init_sms");
			var lesstime = $(o).attr("lesstime");
			var divbtn = $(o).next();
			divbtn.attr("lesstime",lesstime);
			if(parseInt(lesstime)>0)
			init_sms_code_btn($(divbtn),lesstime);
		});
	};
	
	
	
	//发短信的按钮事件
	$.init_cart_sms_btn();
	$("#user_mobile").each(function(k,mobile_panel){
		if(!$(mobile_panel).find("div.ph_verify_btn").attr("bindclick"))
		{
			$(mobile_panel).find("div.ph_verify_btn").attr("bindclick",true);
			$(mobile_panel).find("div.ph_verify_btn").bind("click",function(){		
				
				if($(this).attr("rel")=="disabled")return false;
				var btn = $(this);
				var query = new Object();
				query.act = "send_sms_code";
				var mobile = $(mobile_panel).find("input[name='user_mobile']").val();
				if($.trim(mobile)=="")
				{
					form_tip($(mobile_panel).find("input[name='user_mobile']"),"请输入手机号");
					return false;
				}
				if(!$.checkMobilePhone(mobile))
				{
					form_err($(mobile_panel).find("input[name='user_mobile']"),"手机号格式不正确");
					return false;
				}
				query.mobile = $.trim(mobile);
				query.verify_code = $.trim($(mobile_panel).find("input[name='verify_code']").val());
				query.unique = 1; //是否验证手机是否被注册过
				//发送手机验证登录的验证码
				$.ajax({
		    		url:AJAX_URL,
		    		dataType: "json",
		    		data:query,
		            type:"POST",
		            global:false,
		    		success:function(data)
		    		{
		    		    if(data.status)
		    		    {
		    		    	init_sms_code_btn(btn,data.lesstime);
		    		    	IS_RUN_CRON = true;
		    		    	$(mobile_panel).find("img.verify").click();
		    		    	if(data.sms_ipcount>1)
		    		    	{
		    		    		$(mobile_panel).find(".ph_img_verify").show();
		    		    	}
		    		    	else
		    		    	{
		    		    		$(mobile_panel).find(".ph_img_verify").hide();
		    		    	}
		    		    }
		    		    else
		    		    {
		    		    	if(data.field)
		    		    	{
		    		    		form_err($(mobile_panel).find("input[name='"+data.field+"']"),data.info);
		    		    	}
		    		    	else
		    		    	$.showErr(data.info);
		    		    }
		    		}
		    	});
			});
		}
	});
	
}