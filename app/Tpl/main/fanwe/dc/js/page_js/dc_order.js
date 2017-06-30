$(document).ready(function(){
	
	init_payment_change();
	init_voucher_verify();
	
	$('#pay_confirm').live('click',function(){
		check_pay();
	});
	
	$('.dc_con_td .dc_con').mouseover(function(){
	
		$(this).find('.dc_con_name .f_r').show();
	}).mouseout(function(){
		$(this).find('.dc_con_name .f_r').hide();
	});


	
	
	$("input[name='dc_comment']").bind({'focus':function(){
		$('.comment_tip').slideDown();
	}});

	$(document).click(function(e){
		e=window.event || e;
		var obj=$(e.srcElement || e.target);
		if(!$(obj).is(".dc_comment , .dc_comment *")){
			$('.comment_tip').slideUp();
		}
	});
	
	
	$('.comment_tip .comment_tip_item').bind('click',function(){
		if($.trim($("input[name='dc_comment']").val())==''){
				$("input[name='dc_comment']").val($(this).text());
		}else{
			var tip_arr=$("input[name='dc_comment']").val().split(',');
			if($.inArray($(this).text(),tip_arr) ==-1){
				tip_arr.push($(this).text());
				$("input[name='dc_comment']").val(tip_arr.join(','));
			}
		}
		
	
	});

	
	$(".pay_method input[name='payment_id']").bind('checked',function(){
	
		//var obj=$(this).find("input[name='payment_id']");
		var pay_box=$('#online_pay #cart_payment');
		dc_count_buy_total();
		if($(this).val()==0){
			if(!pay_box.is(":visible")){
			$('#online_pay #cart_payment').show();
			change_page_height2();
			}
		}else{
			if(pay_box.is(":visible")){
			$('#online_pay #cart_payment').hide();
			change_page_height2();
			}
		}
		
	});
	
	$(".pay_method input[name='dc_type']").bind('checked',function(){

		//var obj=$(this).find("input[name='dc_type']");
		var pay_box=$('#online_pay #cart_payment');

			if($(this).val()==0){
		
				init_table_method();
				if(!pay_box.is(":visible")){	
				$('#online_pay #cart_payment').show();
				change_page_height2();
				$('.p_box #p_pay').html(pay_button);
				}
			}else if($(this).val()==1){
				if(pay_box.is(":visible")){
				$('#online_pay #cart_payment').hide();
				change_page_height2();
				$('.p_box #p_pay').html(go_menu_button);
				
				}
			}else if($(this).val()==2){
			init_menu_method($(this).parent())
			
			}
			dc_count_buy_total();
	});
	
	
	
	$('.iconfont.table_delete , #table_delete').live('click',function(){
		
		var query = new Object();
		query.location_id = location_id;
		query.id = parseInt($(this).attr('data-parmas'));	
		query.act = "delete_dc_cart_table";
			$.ajax({
						url: DC_AJAX_URL,
						data: query,
						dataType: "json",
						type: "post",
						success: function(obj){
							if(obj.status>0){
							location.href=obj.jump;
							}
						}
			});
		
	
	});
	
	$('.dc_con_td .dc_con').bind('click',function(){
		var is_selected_icon='<i class="iconfont is_selected">&#xe620;</i>';
		$('.is_selected').remove();
		$(this).addClass('is_selected_td').append(is_selected_icon).parents('.dc_con_td').siblings().find('.dc_con').removeClass('is_selected_td').find('.is_selected').remove();
	});
	
	
	$('.dc_con_edit , #add_new_div').bind('click',function(){
	
		var query = new Object();
		query.id = parseInt($(this).attr('data-parmas'));	
		query.act = "get_consignee_row";
			$.ajax({
						url: dc_order_url,
						data: query,
						dataType: "json",
						type: "post",
						success: function(obj){

							$.weeboxs.open( obj.html, {boxid:'fanwe_success_box',contentType:'text',showButton:true, showCancel:false, showOk:true,title:'提示',width:820,height:80,type:'wee',onopen:function(){
								init_ui_textbox();
								init_ui_button();
							
							},onclose:function(){
								//init_cart_tip();
							}});
						

						}
			});
	
	});
	
	$('.dc_con_del').bind('click',function(){
		var query = new Object();
		var o=$(this);
		query.id = parseInt($(this).attr('data-parmas'));	
		query.act = "del_consignee_row";
			$.ajax({
						url: dc_order_url,
						data: query,
						dataType: "json",
						type: "post",
						success: function(obj){

							if(obj.status==1){
								$.showSuccess(obj.info,function(){
								location.reload();
								});
								//o.parents('.dc_con_td').remove();

							}
							else
							{
								$.showErr(obj.info);
							}
						}
			});
	});
	
	
	$('.dc_save_con').live('click',function(){
	var obj=$(this).parents('.dc_action');
	
	if(!check_empty(obj.siblings('.dc_name').find("input[name='consignee']"),'请填写姓名')){
	return false;
	}	
	if(!check_empty(obj.siblings('.dc_mobile').find("input[name='mobile']"),'请填写电话')){
	return false;
	}
	if(!checkmobile(obj.siblings('.dc_mobile').find("input[name='mobile']"),'手机号码格式不正确')){
	return false;
	}
	
	if(obj.siblings('.dc_position').find('#q_text').length > 0){
		if(!check_empty(obj.siblings('.dc_position').find('#q_text'),'请输入地理位置')){	
			return false;
		}
	}
	if(!check_empty(obj.siblings('.dc_address').find("input[name='address']"),'请输入门牌号等详细信息')){
	return false;
	}
	
	save_dc_consignee($(this));

	});
	
	
	$('.dc_save_can').live('click',function(){
	
	$(this).parents('#fanwe_success_box').remove();
	$('.dialog-mask').remove();
	});
	
	$('#switch_show_all').bind('click',function(){
		if($('.dc_con_td_hide').length>0){
			$('.dc_cart_consignee .dc_con_td').removeClass('dc_con_td_hide');
			$(this).html('<i class="iconfont">&#xe648;</i>&nbsp;隐藏部分地址');
			
		}
		else
		{
			
			$('.dc_cart_consignee .dc_con_td').each(function(i){
				if(i>1){
					$(this).addClass('dc_con_td_hide');
				}			
			});
			
			$(this).html('<i class="iconfont">&#xe647;</i>&nbsp;显示所有地址');
		}
		change_page_height2();

	});
	

});


/*只订座，不提前点菜*/
function init_table_method(){

	$('.go_menu').remove();
	set_dc_cart_menu_status(0);  /*0表示菜单状态改为不生效*/
	
}

/*点菜定金  例如 ¥3099*/
function init_menu_method(o){
	o.after(coutinue_menu_button);
	set_dc_cart_menu_status(1);  /*1表示菜单状态改为生效*/

}
/**
改变购物车中菜单的状态
*/
function set_dc_cart_menu_status(stutus){

	var query = new Object();
	query.location_id = location_id;
	query.menu_status = stutus;  /* 改变购物车中菜单的状态 */
	query.act = "set_dc_cart_menu_status";
		$.ajax({
					url: DC_AJAX_URL,
					data: query,
					dataType: "json",
					type: "post",
					success: function(obj){
						if(obj.status==1){
							dc_count_buy_total();
						}

					}
		});

}

function init_payment_method(){	

	if($(".pay_method input[name='payment_id']:checked").val()==0){
		$('#online_pay #cart_payment').show();
	}else{
		$("#dc_cod").attr('checked',true);
		$('#online_pay #cart_payment').hide();
	}
	
	if($(".pay_method input[name='dc_type']:checked").val()==0){
		$('#online_pay #cart_payment').show();
		$('.p_box #p_pay').html(pay_button);
	}else if($(".pay_method input[name='dc_type']:checked").val()==1){
		$('#online_pay #cart_payment').hide();
		$('.p_box #p_pay').html(go_menu_button);
		
	}else if($(".pay_method input[name='dc_type']:checked").val()==2){
		$('#online_pay #cart_payment').show();
		if(order_id==0){
		$('.s_method .p_method').append(coutinue_menu_button);
		}
		$('.p_box #p_pay').html(pay_button);
	}
	init_page_height();
	
}

function init_page_height(){

	var olheight=$('.dc_order_info').height();
	var orheight=$('.dc_order_pay').height();

	if(olheight<=orheight){
		var oheight=orheight;
	}
	else
	{
		var oheight=olheight;
	}
	$('.dc_order_info').css('height',oheight);
	$('.dc_order_pay').css('height',oheight);
	
}
/*
function change_page_height(o){
	pay_box_height=$('#cart_payment .cart_row .content').height();
	$('.dc_order_info').css('height','auto');
	$('.dc_order_pay').css('height','auto');
	var olheight=$('.dc_order_info').height();
	var orheight=$('.dc_order_pay').height();
	if(o==1){
		var rightheight=orheight+pay_box_height;
	}else{
		var rightheight=orheight-pay_box_height;
	}

	if(rightheight<=olheight){
		var oheight=olheight;
	}
	else
	{
		var oheight=rightheight;
	}
	$('.dc_order_info').css({'height':oheight});
	$('.dc_order_pay').css({'height':oheight});
	
}
*/
function change_page_height2(){

	$('.dc_order_info').css('height','auto');
	$('.dc_order_pay').css('height','auto');
	var olheight=$('.dc_order_info').height();
	var orheight=$('.dc_order_pay').height();

	if(orheight<=olheight){
		var oheight=olheight;
	}
	else
	{
		var oheight=orheight;
	}
	$('.dc_order_info').css({'height':oheight});
	$('.dc_order_pay').css({'height':oheight});
	
}
function check_pay(){

	if(!check_empty($("input[name='consignee']"),'请填写姓名')){
	return false;
	}	
	if(!check_empty($("input[name='mobile']"),'请填写电话')){
	return false;
	}
	if(!checkmobile($("input[name='mobile']"),'手机号码格式不正确')){
	return false;
	}
	
	if($("input[id='q_text']").length > 0){
		if(!check_empty($("input[id='q_text']"),'请输入地理位置')){	
			return false;
		}
	}
	if(!check_empty($("input[name='address']"),'请输入门牌号等详细信息')){
	return false;
	}
	if($("select[name='order_delivery_time']").val()==0){
		$.showErr('<span class="info_tip">请选择送达时间</span>',function(){
			$("dl[name='order_delivery_time'] .ui-select-selected").css({'color':'red'});
		});
		return false;
	}
		
	dc_submit_buy(1);

}

function checkmobile(o,info){
	if(o.length>0){
		if (!o.val().match(/^1\d{10}$/)) {
		$.showErr('<span class="info_tip">'+info+'</span>',function(){o.focus();});
		return false;
		}
		return true;
	}else{
		return true;
	}	
} 

function check_empty(o,info){
	if(o.length>0){
		if($.trim(o.val())==''){
		$.showErr('<span class="info_tip">'+info+'</span>',function(){o.focus();});
		return false;	
		}else{
		return true;
		}
	}else{
		return true;
	}
}
	

function update_dcorder_data(){

		var query = new Object();
		query.location_id = location_id;
		query.act = "update_dc_cart";
			$.ajax({
						url: DC_AJAX_URL,
						data: query,
						dataType: "json",
						type: "post",
						success: function(obj){
							location.reload();
						}
			});
		
}


		
function save_dc_consignee(o){

	var op_ak = BAIDU_APPKEY;
	var op_q = encodeURIComponent($.trim($("#fanwe_success_box #q_text").val()));
	var op_page_size = 1;
	var op_page_num = cur_page;
	var op_region = encodeURIComponent(CITY_NAME);
	var url = "http://api.map.baidu.com/place/v2/search?ak="+op_ak+"&output=json&query="+op_q+"&page_size="+op_page_size+"&page_num="+op_page_num+"&scope=1&region="+op_region;
	var query=new Object();
	var dc_order_url=dc_order_url;
	query.consignee=$("input[name='consignee']").val();
	query.mobile=$("input[name='mobile']").val();
	query.id=parseInt(o.attr('data-parmas'));
	query.user_id=user_id;
	query.api_address=$("#q_text").val();
	query.act='save_dc_consignee';
	if($.trim($("#q_text").val())){
		$.ajax({
			url:url,
			dataType:"jsonp",
	        jsonp: 'callback',
			type:"GET",
			success:function(obj){
				var	item = obj.results[0];	
					if(typeof(item)!='undefined' && typeof(item.location)!='undefined'){		
					query.xpoint=item.location.lng;
					query.ypoint=item.location.lat;
			
					query.address=$("input[name='address']").val();
					$.ajax({
						url:dc_order_url,
						data:query,
						type:'post',
						dataType:'json',
						success:function(data){
							if(data.status==1){
								location.reload();
							}
							else{
								$.weeboxs.close("fanwe_success_box");
							}

						}
					});
					}else{
					$.showErr('<span class="info_tip">请输入详细位置并在下拉框中进行选择</span>',function(){$("#q_text").val('').focus();});			
					}
				
					


			}
			});
	}else{
			$.showErr('<span class="info_tip">请输入地理位置</span>',function(){$("#q_text").focus();});
			return false;
	
	}
	
	
}


function init_payment_change()
{

	$("select[name='ecvsn']").bind("change",function(){

		dc_count_buy_total();
	});
	$("input[name='account_money']").bind("blur",function(){

		dc_count_buy_total();
	});
	
	$("input[name='payment']").bind("checked",function(){
		$(this).parent('label').addClass('payment_active').siblings('label').removeClass('payment_active');
		dc_count_buy_total();
	});
	$("#check-all-money").bind("checkon",function(){
	
		dc_count_buy_total();
	});
	$("#check-all-money").bind("checkoff",function(){
		$("#account_money").val("0");
		dc_count_buy_total();
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
			url: DC_AJAX_URL,
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
//总价计算
function dc_count_buy_total(reload)
{

	var query = new Object();
	if(reload){
		query.reload = reload;
	}
		
	if($(".dc_con.is_selected_td").length>0){
	var consignee_id = parseInt($(".dc_con.is_selected_td").attr("data-parmas"));
	}else{
	var consignee_id = parseInt($(".location_fl").attr("data-parmas"));
	}
	//配送地区

	query.consignee_id = consignee_id;
	//余额支付
	var account_money = $("input[name='account_money']").val();
	
	if(!account_money||$.trim(account_money)=='')
	{
		account_money = 0;
	}
	query.account_money = account_money;

	//送货上门的支付方式
	if($("input[name='payment_id']").length>0){
		query.payment_id = $("input[name='payment_id']:checked").val();
	}
	//预订方式,都不享受促销优惠
	if($("input[name='dc_type']").length>0){
		query.dc_type = $("input[name='dc_type']:checked").val();
	}

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
	var ecvsn = $("select[name='ecvsn']").val();
	if(!ecvsn)
	{
		ecvsn = '';
	}
	var ecvpassword = $("input[name='ecvpassword']").val();
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
	query.location_id = location_id;
	query.bank_id = $("input[name='payment']:checked").attr("rel");

	query.id = order_id;
	if(!isNaN(order_id) && order_id>0)
		query.act = "dc_count_order_total";
	else
		query.act = "dc_count_buy_total";

	$.ajax({ 
		url: DC_AJAX_URL,
		data:query,
		type: "POST",
		dataType: "json",
		success: function(data){
			if(data.consignee_info_error==1){
					$.showErr('<span class="info_tip">配送地址不正确,新定位</span>',function(){$("#q_text").val('').focus();});
					if(data.is_out_scale==1){
					$.showErr('<span class="info_tip">超出该商家配送范围</span>',function(){$("#q_text").val('').focus();});
					}
			}
			$("#dc_total_box").html(data.html);
			$("input[name='account_money']").val(data.account_money);
			if(data.pay_price == 0)
			{
				$("input[name='payment']").attr("checked",false);
				$("input[name='payment']").parent().each(function(i,o){
					$(o).ui_radiobox({refresh:true});
		
				});
			}
			
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(LANG['REFRESH_TOO_FAST']);
		}
	});	
}

//购物提交,ecv_pass是否要验证，0为不验证，1为要验证
function dc_submit_buy(ecv_pass)
{
	
	//提交订单
	var ajaxurl = $("#dc_cart_form").attr("action");
	var query = $("#dc_cart_form").serialize();
	
	if($(".dc_con.is_selected_td").length>0){
	var consignee_id = parseInt($(".dc_con.is_selected_td").attr("data-parmas"));
	}else{
	var consignee_id = parseInt($(".location_fl").attr("data-parmas"));
	}
	query+='&consignee_id='+consignee_id;
	query+='&location_id='+location_id;
	query+='&ecv_pass='+ecv_pass;

	$.ajax({
		url:ajaxurl,
		data:query,
		dataType:"json",
		type:"POST",
		success:function(obj){

			
			if(obj.status)
			{ 
					if(obj.status==1000){
					//未登录，请先登录
					ajax_login(function(){
					location.href=location.href;
					});
					}
					else if(obj.status==1){
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
							$.weeboxs.open( obj.info, {boxid:'fanwe_ecv_pass',contentType:'text',showButton:true, showCancel:true, showOk:true,title:'提示',width:320,height:50,type:'wee',onopen:function(){
								init_ui_button();
							
							},onclose:function(){
								//init_cart_tip();
							},onok:function(){
								dc_submit_buy(0);
								$.weeboxs.close("fanwe_ecv_pass");
							}});
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

