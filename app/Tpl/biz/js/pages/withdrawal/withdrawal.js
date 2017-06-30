$(document).ready(function(){

	//验证码刷新
	$(".img_verify_box img.verify").live("click",function(){
		$(this).attr("src",$(this).attr("rel")+"?"+Math.random());
	});
	$(".img_verify_box .refresh_verify").live("click",function(){
		var img = $(this).parent().find("img.verify");
		$(img).attr("src",$(img).attr("rel")+"?"+Math.random());
	});

	$("#withdraw form").bind("submit",function(){

		var money = $("#withdraw form").find("input[name='money']").val();
		if($.trim(money)==""||isNaN(money)||parseFloat(money)<=0)
		{
			$.showErr("请输入正确的提现金额");
			return false;
		}
		
		var ajax_url = $("#withdraw form").attr("action");
		var query = $("#withdraw form").serialize();
		$.ajax({
			url:ajax_url,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){

				if(obj.status==1)
				{
					$.showSuccess(obj.info,function(){
						location.reload();
					});
				}
				else
				{
					$.showErr(obj.info,function(){
						if(obj.jump)
						{
							location.href = obj.jump;
						}
					});
				}
			}
		});
		
		
		return false;
	});


	init_bind_sms_btn();
	//绑定按钮事件
	init_sms_btn();
	//初始化倒计时
	function init_sms_btn() {
		$("#withdraw").find("button.ph_verify_btn[init_sms!='init_sms']").each(function(i, o) {
			$(o).attr("init_sms", "init_sms");
			var lesstime = $(o).attr("lesstime");
			var divbtn = $(o).next();
			divbtn.attr("form_prefix", $(o).attr("form_prefix"));
			divbtn.attr("lesstime", lesstime);
			if(parseInt(lesstime) > 0)	init_sms_code_btn($(divbtn), lesstime);
		});
	}

	function init_bind_sms_btn() {
		if(!$("#withdraw").find("div.ph_verify_btn").attr("bindclick")) {
			$("#withdraw").find("div.ph_verify_btn").attr("bindclick", true);
			$("#withdraw").find("div.ph_verify_btn").bind("click", function() {
				if($(this).attr("rel") == "disabled")	return false;
				var is_error = 0;
				var error_msg = '';
				var form = $("form[name='withdraw_form']");
				var btn = $(this);
				var query = new Object();
				query.act = "biz_sms_code";

				if(is_error) {
					$.Show_error_tip(error_msg);
					return false;
				}
				
				query.verify_code = $.trim($(form).find("input[name='verify_code']").val());				
				//是否验证手机是否被注册过
				//发送手机验证登录的验证码
				$.ajax({
					url : SMS_URL,
					dataType : "json",
					data : query,
					type : "POST",
					global : false,
					success : function(data) {
						if(data.status) {
							init_sms_code_btn(btn, data.lesstime);
							IS_RUN_CRON = true;
							$(form).find("img.verify").click();
							if(data.sms_ipcount > 1) {
								$(form).find(".ph_img_verify").show();
							} else {
								$(form).find(".ph_img_verify").hide();
							}
						} else {
								$.showErr(data.info);
								return false;
						}
					}
				});
			});
		}
	}	
	
	
	

});