$(function() {
	init_bind_sms_btn();
	//绑定按钮事件
	init_sms_btn();
	//初始化倒计时
	function init_sms_btn() {
		$(".register-panel").find("button.ph_verify_btn[init_sms!='init_sms']").each(function(i, o) {
			$(o).attr("init_sms", "init_sms");
			var lesstime = $(o).attr("lesstime");
			var divbtn = $(o).next();
			divbtn.attr("form_prefix", $(o).attr("form_prefix"));
			divbtn.attr("lesstime", lesstime);
			if(parseInt(lesstime) > 0)
				init_sms_code_btn($(divbtn), lesstime);
		});
	}

	function init_bind_sms_btn() {
		if(!$(".register-panel").find("div.ph_verify_btn").attr("bindclick")) {
			$(".register-panel").find("div.ph_verify_btn").attr("bindclick", true);
			$(".register-panel").find("div.ph_verify_btn").bind("click", function() {
				if($(this).attr("rel") == "disabled")
					return false;
				var is_error = 0;
				var error_msg = '';
				var form = $("form[name='user_register_3']");
				var btn = $(this);
				var query = new Object();
				query.act = "send_sms_code";
				var mobile = $(form).find("input[name='account_mobile']").val();
				if($.trim(mobile) == "") {
					$("#account_mobile").focus();
					is_error = 1;
					error_msg = "请输入手机号";
				}

				if(!$.checkMobilePhone(mobile)) {
					$("#account_mobile").focus();
					is_error = 1;
					error_msg = "手机号格式不正确";
				}
				if(is_error) {
					$.Show_error_tip(error_msg);
					return false;
				}
				query.mobile = $.trim(mobile);
				query.verify_code = $.trim($(form).find("input[name='verify_code']").val());
				query.unique = 1;
				//是否验证手机是否被注册过
				//发送手机验证登录的验证码
				$.ajax({
					url : AJAX_URL,
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
							if(data.field) {
								$.Show_error_tip(data.info);
								return false;

							} else
								$.showErr(data.info);
						}
					}
				});
			});
		}

	}


	$("form[name='user_register_3']").bind("submit", function() {
		var form = $("form[name='user_register_3']");
		var is_error = 0;
		var error_msg = '';
		var obj = null;
		if($("#account_name").val() == "") {
			$("#account_name").focus();
			obj = $("#account_name");
			is_error = 1;
			error_msg = "请正确输入账户！";
		}
		if($("#account_password").val() == "" && is_error == 0) {
			$("#account_password").focus();
			obj = $("#account_password");
			is_error = 1;
			error_msg = "请输入密码！";
		}
		if($("#account_password_confirm").val() == "" && is_error == 0) {
			$("#account_password_confirm").focus();
			obj = $("#account_password_confirm");
			is_error = 1;
			error_msg = "请输入确认密码！";
		}
		if($("#account_mobile").val() == "" && is_error == 0) {
			$("#account_mobile").focus();
			obj = $("#account_mobile");
			is_error = 1;
			error_msg = "请输入手机号！";
		}

//		if(!$.checkMobilePhone($("#account_mobile").val())) {
//			$("#account_mobile").focus();
//			is_error = 1;
//			error_msg = "手机号格式不正确";
//		}

		if($("#h_name").val() == "" && is_error == 0) {
			$("#h_name").focus();
			obj = $("#h_name");
			is_error = 1;
			error_msg = "请输入企业名称！";
		}
		if($("#h_faren").val() == "" && is_error == 0) {
			$("#h_faren").focus();
			obj = $("#h_faren");
			is_error = 1;
			error_msg = "请输入法人姓名！";
		}
		if($("#h_license").val() == "" && is_error == 0) {
			obj = $("#h_license");
			is_error = 1;
			error_msg = "营业执照不能为空！";
		}
		if($("#h_tel").val() == "" && is_error == 0) {
			$("#h_tel").focus();
			obj = $("#h_tel");
			is_error = 1;
			error_msg = $("#h_tel").attr("holder");
		}
		if($("#h_bank_user").val() == "" && is_error == 0) {
			$("#h_bank_user").focus();
			obj = $("#h_bank_user");
			is_error = 1;
			error_msg = $("#h_bank_user").attr("holder");
		}
		if($("#h_bank_name").val() == "" && is_error == 0) {
			$("#h_bank_name").focus();
			obj = $("#h_bank_name");
			is_error = 1;
			error_msg = $("#h_bank_name").attr("holder");
		}
		if($("#h_bank_info").val() == "" && is_error == 0) {
			$("#h_bank_info").focus();
			obj = $("#h_bank_info");
			is_error = 1;
			error_msg = $("#h_bank_info").attr("holder");
		}

		if($("#account_password").val() != $("#account_password_confirm").val()) {
			$("#account_password_confirm").focus();
			obj = $("#account_password_confirm");
			is_error = 1;
			error_msg = "密码与确认密码不一致，请重新输入！";
		}
		
		

		if(is_error == 1) {
			$.Show_field_error(obj);
			position_scroll();
			$(".msg_tip .msg_content").html(error_msg);
			$(".msg_tip").addClass("sysmsg_error");
			$(".msg_tip .status").addClass("s_error");
			$(".msg_tip").fadeIn();
			return false;
		}

		var url = $(form).attr("action");
		var query = $(form).serialize();
		$.ajax({
			url : url,
			type : "POST",
			data : query,
			dataType : "json",
			success : function(data) {
				if(data.status) {
					$.showSuccess(data.info, function() {
						location.href = data.jump;
					});
				} else {
					$.showErr(data.info);
//					$.Show_field_error($("#" + data.field));
//					$.Show_error_tip(data.info);
//					$("#" + data.field).focus();
				}
			}
		});

		return false;
	});

	$("#account_name").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data.length > 0) {
			var query = new Object();
			query.act = "check_account_name";
			query.account_name = $(this).val();
			$.ajax({
				type : "POST",
				url : AJAX_URL,
				data : query,
				dataType : "json",
				success : function(data) {
					if(data.error == 1) {
						$.Show_field_error(obj);
						$.Show_error_tip('该账户名已经存在！');
						$(obj).focus();
					} else {
						$.Close_top_tip();
						$.Show_field_success(obj);
					}
				}
			});
		} else {
			$.Show_error_tip('请输入帐号！');
			$.Show_field_error(obj);
		}

	});

	$("#account_mobile").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		
		if(val_data.length > 0) {
			if(!$.checkMobilePhone(val_data)) {
				$("#account_mobile").focus();
				$.Show_error_tip('手机号格式错误！');
				$.Show_field_error(obj);
			}
			var query = new Object();
			query.act = "check_account_mobile";
			query.account_mobile = $(this).val();
			$.ajax({
				type : "POST",
				url : AJAX_URL,
				data : query,
				dataType : "json",
				success : function(data) {
					if(data.error == 1) {
						$.Show_field_error(obj);
						$.Show_error_tip(data.msg);
					} else {
						$.Close_top_tip();
						$.Show_field_success(obj);
					}
				}
			});
		} else {
			$.Show_error_tip('请输入手机号！');
			$.Show_field_error(obj);
		}
	});
	$("#account_password_confirm").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		var account_password = $("#account_password").val();
		if(val_data != '' && account_password != '') {
			if(val_data != account_password) {
				$.Show_field_error(obj);
				$.Show_error_tip('两次密码输入不一致！');
			} else {
				$.Close_top_tip();
				$.Show_field_success($("#account_password"));
				$.Show_field_success(obj);
			}
		}
	});

	$("#h_name").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入企业名称！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});

	$("#h_faren").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入法人姓名！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});

	$("#h_tel").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入联系电话！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});

	$("#h_bank_user").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入开户银行户名！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});

	$("#h_bank_name").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入开户银行名称！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});

	$("#h_bank_info").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入开户银行帐号！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
		val_data = val_data.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
		$("#h_bank_info").val(val_data);
	});
	//输入银行帐号空格分割
	$("#h_bank_info").keyup(function() {
		var val_data = $(this).val();
		val_data = val_data.replace(/\s/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
		$("#h_bank_info").val(val_data);
	});
	// 填写信息大图查看
	$.Show_big_img = function(field_id) {
		if($('#' + field_id + " .big_img_item").html().length > 0) {
			$.weeboxs.open('#' + field_id, {
				title : "查看大图",
				modal : true, //默认为true
				draggable : true, //默认为true
				position : "center",
				animate : true,
				timeout : 0, //默认为0
				width : 0,
				height : 0,
				showButton : false,
				clickClose : true,
				showTitle : false
			});
		}

	};

	
});



$(document).ready(function() {
	$("select[name='deal_cate_id']").bind("change", function() {
		load_sub_cate();
	});
	load_sub_cate();
	$("select[name='city_id']").bind("change", function() {
		load_city_area();
	});
	load_city_area();

	$("form[name='user_register_2']").bind("submit", function() {
		var form = $("form[name='user_register_3']");
		var is_error = 0;
		var error_msg = '';
		var obj = null;
		if($("#name").val() == "") {
			$("#name").focus();
			obj = $("#name");
			is_error = 1;
			error_msg = "请正确输入商户名称！";
		}
		if($("#address").val() == "" && is_error == 0) {
			$("#address").focus();
			obj = $("#address");
			is_error = 1;
			error_msg = "请正确输入商户名称！";
		}
		if($("#tel").val() == "" && is_error == 0) {
			$("#tel").focus();
			obj = $("#tel");
			is_error = 1;
			error_msg = "请正确输入商户名称！";
		}
		if(is_error == 1) {
			$.Show_field_error(obj);
			$.Show_error_tip(error_msg);
			return false;
		} else {
			return true;
		}

	});
	$("form[name='user_register_2'] input[name='name']").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入商户名称！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});
	$("form[name='user_register_2'] input[name='tel']").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入商户电话！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});
	$("form[name='user_register_2'] input[name='open_time']").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入营业时间！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});
	
	$("form[name='user_register_2'] input[name='address']").blur(function() {
		var obj = $(this);
		var val_data = $(this).val();
		if(val_data == '') {
			$.Show_field_error(obj);
			$.Show_error_tip('请输入商户地址！');
		} else {
			$.Close_top_tip();
			$.Show_field_success(obj);
		}
	});
	
	
	$("#mark_map").bind("click", function() {
		var api_address = $("input[name='address']").val();
		var city = $("select[name='city_id']").find("option:selected").html();

		if($.trim(api_address) == '') {
			$.showErr("请先输入地址");
		} else {
			search_api(api_address, city);
		}
	});
	$("#container_front").hide();
	$("#cancel_btn").bind("click", function() {
		$("#container_front").hide();
	});
	$("#chang_api").bind("click", function() {
		editMap($("input[name='xpoint']").attr('value'), $("input[name='ypoint']").attr('value'));
	});
	
	//上传控件
	$("div.h_license").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		$(".h_license_img").attr("src",LOADER_IMG_GIF);
		return true;
	},FileUploaded:function(responseObject){
		//每张图片上传后
		if(responseObject.error==0)
		{
			$("#h_license").val(responseObject.url);	
			$(".h_license_img").attr("src",APP_ROOT+"/"+responseObject.small_url);
			$("#h_license_big_img .big_img_item").html("<img  src=\'"+APP_ROOT+"/"+responseObject.big_url+"\'/>");
		}
		else
		{
			$.showErr(responseObject.message);
		}
	},UploadComplete:function(files){
		//全部上传完成
		uploading = false;
	},Error:function(errObject){
		$.showErr(errObject.message);
	}});
	
	//上传控件
	$("div.h_other_license").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		$(".h_other_license_img").attr("src",LOADER_IMG_GIF);
		return true;
	},FileUploaded:function(responseObject){
		//每张图片上传后
		if(responseObject.error==0)
		{
			$("#h_other_license").val(responseObject.url);	
			$(".h_other_license_img").attr("src",APP_ROOT+"/"+responseObject.small_url);
			$("#h_other_license_big_img .big_img_item").html("<img  src=\'"+APP_ROOT+"/"+responseObject.big_url+"\'/>");
		}
		else
		{
			$.showErr(responseObject.message);
		}
	},UploadComplete:function(files){
		//全部上传完成
		uploading = false;
	},Error:function(errObject){
		$.showErr(errObject.message);
	}});
	
	//上传控件
	$("div.h_supplier_logo").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		$(".h_supplier_logo_img").attr("src",LOADER_IMG_GIF);
		return true;
	},FileUploaded:function(responseObject){
		//每张图片上传后
		if(responseObject.error==0)
		{
			$("#h_supplier_logo").val(responseObject.url);	
			$(".h_supplier_logo_img").attr("src",APP_ROOT+"/"+responseObject.small_url);
			$("#h_supplier_logo_big_img .big_img_item").html("<img  src=\'"+APP_ROOT+"/"+responseObject.big_url+"\'/>");
		}
		else
		{
			$.showErr(responseObject.message);
		}
	},UploadComplete:function(files){
		//全部上传完成
		uploading = false;
	},Error:function(errObject){
		$.showErr(errObject.message);
	}});
	
	//上传控件
	$("div.h_supplier_image").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		$(".h_supplier_image_img").attr("src",LOADER_IMG_GIF);
		return true;
	},FileUploaded:function(responseObject){
		//每张图片上传后
		if(responseObject.error==0)
		{
			$("#h_supplier_image").val(responseObject.url);	
			$(".h_supplier_image_img").attr("src",APP_ROOT+"/"+responseObject.small_url);
			$("#h_supplier_image_big_img .big_img_item").html("<img  src=\'"+APP_ROOT+"/"+responseObject.big_url+"\'/>");
		}
		else
		{
			$.showErr(responseObject.message);
		}
	},UploadComplete:function(files){
		//全部上传完成
		uploading = false;
	},Error:function(errObject){
		$.showErr(errObject.message);
	}});
	
});
function load_sub_cate() {
	var id = $("select[name='deal_cate_id']").val();
	var ajaxurl = APP_ROOT + "/biz.php?ctl=user&act=load_sub_cate&id=" + id;
	$.ajax({
		url : ajaxurl,
		success : function(html) {
			if(html != "") {
				$("#sub_cate").find(".cnt").html(html);
				$("#sub_cate").show();
				init_ui_checkbox();
			} else {
				$("#sub_cate").find(".cnt").html("");
				$("#sub_cate").hide();
			}
		},
		error : function(ajaxobj) {
			//			if(ajaxobj.responseText!='')
			//			alert(ajaxobj.responseText);
		}
	});
}

function load_city_area() {
	var id = $("select[name='city_id']").val();
	var ajaxurl = APP_ROOT + "/biz.php?ctl=user&act=load_city_area&id=" + id;
	$.ajax({
		url : ajaxurl,
		success : function(html) {
			if(html != "") {
				$("#area").html(html);
				$("#area").show();
				load_quan_list();
				$("select[name='area_id[]']").bind("change", function() {
					load_quan_list();
				});
				init_ui_select();
			} else {
				$("#area").html("");
				$("#area").hide();
			}
		},
		error : function(ajaxobj) {
			//			if(ajaxobj.responseText!='')
			//			alert(ajaxobj.responseText);
		}
	});
}

function load_quan_list() {
	var id = $("select[name='area_id[]']").val();
	var ajaxurl = APP_ROOT + "/biz.php?ctl=user&act=load_quan_list&id=" + id;
	$.ajax({
		url : ajaxurl,
		success : function(html) {
			if(html != "") {
				$("#region_mark").find(".cnt").html(html);
				$("#region_mark").show();
				init_ui_checkbox();
			} else {
				$("#region_mark").find(".cnt").html("");
				$("#region_mark").hide();
			}
		},
		error : function(ajaxobj) {
			//			if(ajaxobj.responseText!='')
			//			alert(ajaxobj.responseText);
		}
	});
}
