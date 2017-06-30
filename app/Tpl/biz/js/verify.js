
$(function(){
	/**
	 * 验证团购券
	 */
	$("form[name='verify_form']").submit(function(){
		var form = $("form[name='verify_form']");
		var location_id = $("select[name='location_id']").val();
		var coupon_pwd = $.trim($("input[name='coupon_pwd']").val());
		if(!isNaN(coupon_pwd) && location_id>0 && coupon_pwd.length>5){
			var url = $(form).attr("action");
			var query = $(form).serialize();
			$.ajax({
				url : url,
				type : "POST",
				data : query,
				dataType : "json",
				success : function(result) {
					if(result.status == 1){
						$.showConfirm(result.sub_msg+"<br/>是否确定使用团购券",function(){
							use_coupon(location_id,coupon_pwd);
						});
					}else{
						$.showErr(result.msg);
						return false;
					}
				}
			});	
			
		}else{
			$.showErr("团购券验证码错误！");
			return false;
		}
		return false;
	});
	
	
	
	/**
	 * 批量使用JS
	 */
	$("form[name='verify_batch_form']").submit(function(){
		var form = $("form[name='verify_batch_form']");
		var location_id = $("select[name='location_id']").val();
		if(location_id<=0 ){
			$.showErr("请先选择一家门店");
			return false;
		}
		$.showConfirm("确定验证这些团购券吗？",function(){
			var url = $(form).attr("action");
			var query = $(form).serialize();
			$.ajax({
				url : url,
				type : "POST",
				data : query,
				dataType : "json",
				success : function(result) {
					if(result.is_err == 1){
						$.showErr("有错误数据，请先修改后再提交！");
					}
					var res_data = result.data;
					
					$(".coupon_pwd_item").each(function(i,o){
						
						var msg_tip = $(o).parent().parent().find(".field_buttom_tip");
						var cur_data = res_data[i+1];
						if(cur_data.status == 0){
							$(msg_tip).html("<i class=\"iconfont iconfont_err\">&#xe600;</i>"+cur_data.msg);
						}
						if(cur_data.status == 1){
							$(msg_tip).html("<i class=\"iconfont iconfont_succ\">&#xe602;</i>"+cur_data.msg);
						}
							
					});
				}
			});	
			
		});
		return false;
	});
	
	
	/**
	 * 清空
	 */
	var is_hover_clear = false;
	$(".input_clear").hover(
	  function () {
	  		is_hover_clear=true;
	  },
	  function () {
	    	is_hover_clear = false;
	  }
	);
	
	
	$.bind_blur = function(){
		$(".coupon_pwd_item").bind("blur",function(){
			if(is_hover_clear)return false;
			
			
			var location_id = $("select[name='location_id']").val(); //选择的门店
			var coupon_v = $(this).val();	//失去焦点的验证码框
			var field_buttom_tip = $(this).parent().parent().find(".field_buttom_tip");	//错误提示
			var obj = $(this);	//当前对象
			var index =$(".coupon_pwd_item ").index(obj);
			
			if($.trim($(this).val()).length ==0){//如果为空清掉提示
				$(".field_buttom_tip:eq("+index+")").html("");
				return false;
			}
			if(location_id<=0){
				$.showErr("必须先选择一家门店");
				return false;
			}
			//格式验证
			if(isNaN(coupon_v) || coupon_v.length<6){ //必须为数字且大于5位数字
				$(".field_buttom_tip:eq("+index+")").html("<i class=\"iconfont \">&#xe601;</i>验证码格式错误");
				$(obj).attr("is_check",0);
			}else{
				//验证有效性
				var query = new Object();
				query.location_id = location_id;
				query.coupon_pwd = coupon_v;
				query.act = "check_coupon";
				$.ajax({
					url : ajax_url,
					type : "POST",
					data : query,
					dataType : "json",
					success : function(data) {
						if(data.status == 1){
							$(obj).attr("is_check",1);
							$(".field_buttom_tip:eq("+index+")").html("<i class=\"iconfont iconfont_succ\">&#xe602;</i>");
						}else{
							$(obj).attr("is_check",0);
							$(".field_buttom_tip:eq("+index+")").html("<i class=\"iconfont iconfont_err\">&#xe600;</i>"+data.msg);
						}
					}
				});
			}
			return false;

		});
		
		$(".coupon_pwd_item").bind("keyup",function(){
			var obj = $(this);
			var index =$(".coupon_pwd_item ").index(obj);
			if($(this).val().length>0)
				$(".input_clear:eq("+index+")").show();
			else
				$(".input_clear:eq("+index+")").hide();
		});
	};
	//绑定失去焦点
	$.bind_blur();
	
	//超级验证
	$("form[name='super_form']").bind("submit",function(){
		//验证部分
		var location_id = $("select[name='location_id']").val(); //选择的门店
		var coupon_pwd = $.trim($("input[name='coupon_pwd']").val());	//失去焦点的验证码框
		var form = $("form[name='super_form']");
		if(location_id<=0){
			$.showErr("请选择门店");
			return false;
		}
		if(coupon_pwd=='')return false;
		if(isNaN(coupon_pwd) || coupon_pwd.length<6){
			$.showErr("输入验证码格式错误");
			return false;
		}
		
		var url = $(form).attr("action");
		var query = $(form).serialize();
		$.ajax({
			url : url,
			type : "POST",
			data : query,
			dataType : "json",
			success : function(result) {
				if(result.status == 1){
					
					$.weeboxs.open(result.weebox_html, {boxid : 'use_coupon_count',title : "输入验证的数量",type:'wee',onopen:function(){
						init_ui_button();
						//不可以超过最大数量
						$("#use_coupon_count input[name='coupon_use_count']").bind("keyup",function(){
							if($(this).val()>parseInt($(".coupon_count em").html())){
								$(this).val(parseInt($(".coupon_count em").html()));
							}
						});
						
						//提交按钮
						$("form[name='super_use_form']").bind("submit",function(){
							if(!$("#use_coupon_count input[name='coupon_use_count']").val()>=1 || $("#use_coupon_count input[name='coupon_use_count']")==''){
								$.showErr("最少使用1涨");
								return false;
							}
							var form = $("form[name='super_use_form']");
							var url = $(form).attr("action");
							var query = $(form).serialize();
							$.ajax({
								url : url,
								type : "POST",
								data : query,
								dataType : "json",
								success : function(result) {
									$.weeboxs.close("use_coupon_count");
									$(".succ_coupon_box ").html("");
									if(result.status == 1){
										$.showSuccess(result.msg);
										var send_data = result.send_data;
										$(send_data).each(function(i,o){
											$(".succ_coupon_box").append('<span class="c_item">'+send_data[i]['pwd']+'</span>');
										});
										$(".succ_send_coupon").show();
									}else{
										$.showErr(result.msg);
									}
									return false;
								}
							});
							return false;
						});
					}, contentType : 'text',draggable : false,showButton : false,width : 460,height : 200});
				}else{
					$.showErr(result.msg);
					return false;
				}
			}
		});	
		return false;
	});
});


/**
 * 使用团购券
 */
function use_coupon(location_id,coupon_pwd){
	var query = new Object();
	query.location_id = location_id;
	query.coupon_pwd = coupon_pwd;
	query.act = "use_coupon";
	$.ajax({
				url : ajax_url,
				type : "POST",
				data : query,
				dataType : "json",
				success : function(data) {
					if(data.status == 1){
						$.showSuccess(data.sub_msg,function(){$("input[name='coupon_pwd']").val("");});
					}else{
						$.showErr(data.msg);
					}
				}
			});
}
function clear_coupon_input(obj){
	$(obj).parent().find(".coupon_pwd_item ").val("");
	$(obj).parent().parent().find(".field_buttom_tip").html("");
	$(obj).hide();
}



