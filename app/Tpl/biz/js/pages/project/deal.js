$(function(){
	/*页面初始化调用*/
	init_sub_cate();	//子分类载入
	init_select_city();	//城市初始化
	load_attr_html();
	load_attr_stock();
	init_img_del();
	/*页面事件绑定调用*/
	$("select[name='cate_id']").bind("change",function(){
		init_sub_cate();
	});
	//绑定团购商品类型，显示属性
	$("select[name='deal_goods_type']").bind("change",function(){
		load_attr_html();
	});
	
	/* 图片上传初始化  */
	//上传控件
	$(".img_icon_upbtn div.img_icon_btn").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		if($(".img_icon_upload_box").find("span").length+files.length>1)
		{
			$.showErr("最多只能传1张图片");
			return false;
		}
		else
		{
			for(i=0;i<files.length;i++)
			{
				var html = '<span><div class="loader"></div></span>';
				var dom = $(html);		
				$(".img_icon_upload_box").append(dom);	
			}
			uploading = true;
			return true;
		}
	},FileUploaded:function(responseObject){
		if(responseObject.error==0)
		{
			var first_loader = $(".img_icon_upload_box").find("span div.loader:first");
			var box = first_loader.parent();
			first_loader.remove();
			var html = '<a href="javascript:void(0);"></a>'+
			'<img src="'+APP_ROOT+'/'+responseObject.web_40+'" />'+
			'<input type="hidden" name="img_icon" value="'+responseObject.url+'" />';
			$(box).html(html);
			$(box).find("a").bind("click",function(){
				$(this).parent().remove();
			});
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
	$(".focus_imgs_upbtn div.focus_imgs_btn").ui_upload({multi:true,FilesAdded:function(files){
		//选择文件后判断
		if($(".focus_imgs_upload_box").find("span").length+files.length>8)
		{
			$.showErr("最多只能传8张图片");
			return false;
		}
		else
		{
			for(i=0;i<files.length;i++)
			{
				var html = '<span><div class="loader"></div></span>';
				var dom = $(html);		
				$(".focus_imgs_upload_box").append(dom);	
			}
			uploading = true;
			return true;
		}
	},FileUploaded:function(responseObject){
		if(responseObject.error==0)
		{
			var first_loader = $(".focus_imgs_upload_box").find("span div.loader:first");
			var box = first_loader.parent();
			first_loader.remove();
			var html = '<a href="javascript:void(0);"></a>'+
			'<img src="'+APP_ROOT+'/'+responseObject.web_40+'" />'+
			'<input type="hidden" name="focus_imgs[]" value="'+responseObject.url+'" />';
			$(box).html(html);
			$(box).find("a").bind("click",function(){
				$(this).parent().remove();
			});
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
	
	
	/*编辑器*/
	var notes_editor = $("#notes").ui_editor({url:K_UPLOAD_URL,width:"450",height:"250",fun:function(){$(this).html($("textarea[name='notes']").val())} });

	var description_editor = $("#description").ui_editor({url:K_UPLOAD_URL,width:"450",height:"250",fun:function(){$(this).html($("textarea[name='description']").val())} });
	
	
	/*日期控件*/
	$("input[name='begin_time']").datetimepicker({format: "Y-m-d H:i"});
	$("input[name='end_time']").datetimepicker({format: "Y-m-d H:i"});
	
	$("input[name='coupon_begin_time']").datetimepicker({format: "Y-m-d H:i"});
	$("input[name='coupon_end_time']").datetimepicker({format: "Y-m-d H:i"});

	
	/*新增商品属性*/
	$("button.add_goods_type").bind("click",function(){
		var query = new Object();
		query.act = "load_add_goods_type_weebox";
		
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(result){
				$.weeboxs.open(result.html, {boxid:'add_goods_type_weebox',contentType:'text',showButton:false,title:"增加商品属性",width:570,type:'wee',onopen:function(){
						init_ui_button();
						init_ui_textbox();
						//添加属性输入框
						$("button.add_gt_btn").bind("click",function(){
							$(".attr_item_list").append('<p><input type="text" name="goods_attr[]" class="ui-textbox i_text" value=""/>&nbsp;<a href="javascript:void(0);" onclick="attr_del(this)">删除</a></p>');
						});
						
						//提交数据
						$("form[name='add_goods_type_form']").submit(function(){
							var form = $("form[name='add_goods_type_form']");
							
							if($.trim($("input[name='goods_type_name']").val()) == ''){
								$.showErr("请输入新增分类名称");
								return false;
							}
							
							var attr_objs = $("input[name='goods_attr[]']");
							var attr_arr = new Array();
							$("input[name='goods_attr[]']").each(function(i,o){
								if($.trim($(o).val())!=''){
									attr_arr.push($.trim($(o).val()));
								}
							});
							if(attr_arr.length==0){
								$.showErr("至少要有一个属性");
								return false;
							}
							//判断重复
							var temp_v = '';
							for(i=0;i<attr_arr.length;i++){
								temp_v = attr_arr[i];
								for(j=i+1;j<attr_arr.length;j++){
									if(temp_v ==attr_arr[j]){
										$.showErr("请修改重复属性！");
										return false;
									}
								}
							}
							var query = $(form).serialize();
							var url = $(form).attr("action");
							$.ajax({
								url:url,
								data:query,
								type:"post",
								dataType:"json",
								success:function(data){
									if(data.status==0){
										$.showErr(data.info,data.jump);
									}else if(data.status==1){
										//增加成功，重新载入商品分类
										$("select[name='deal_goods_type']").val(0);
										load_attr_html();
										$.weeboxs.close("add_goods_type_weebox");
										
										load_goods_type();
										
									}
								}
							});
							
							return false;
						});
						
					}
				});
			}
		});
	});
	
	$("select[name='coupon_time_type']").bind("change",function(){
		var cur_type = $(this).val();
		if(cur_type == 1){
			$(".coupon_time_type_day").show();
			$(".coupon_time_type_datetime").hide();
		}else{
			$(".coupon_time_type_day").hide();
			$(".coupon_time_type_datetime").show();
		}
	});
	
	/*发布*/
	$("form[name='deal_publish_form']").submit(function(){
		
		var form = $("form[name='deal_publish_form']");
		if(check_deal_form_submit()){
			//$(".sub_form_btn").html('<button class="ui-button" rel="disabled" type="button">提交</button>');
			init_ui_button();
			var query = $(form).serialize();
			var url = $(form).attr("action");
			$.ajax({
				url:url,
				data:query,
				type:"post",
				dataType:"json",
				success:function(data){
					if(data.status == 0){
						$(".sub_form_btn").html('<button class="ui-button " rel="orange" type="submit">确认提交</button>');
						init_ui_button();
						$.showErr(data.info,function(){
							if(data.jump&&data.jump!="")
							{
								location.href = data.jump;
							}					
						});
					}else if(data.status==1){
						$.showSuccess(data.info,function(){window.location = data.jump;});
					}
					return false;
				}
			});
		}
		return false;
	});
	

/*JQUERY END*/
});

/*删除属性*/
function attr_del(obj){
	$(obj).parent().remove();
}


/*表单提交验证*/
function check_deal_form_submit(){
	//团购名称
	if($.trim($("input[name='name']").val())==''){
		$.showErr("请输入团购名称",function(){$("input[name='name']").focus();});
		return false;
	}
	//简短名称
	if($.trim($("input[name='sub_name']").val())==''){
		$.showErr("请输入简短名称",function(){$("input[name='sub_name']").focus();});
		return false;
	}
	//城市
	if($.trim($("input[name='city_id']").val())==0){
		$.showErr("请选择城市");
		return false;
	}

	//分类
	if(parseInt($("select[name='cate_id']").val())<=0){
		$.showErr("请选择分类");
		return false;
	}
	//支持门店
	if($("input.location_id_item:checked").length<=0){
		$.showErr("至少支持一个门店");
		return false;
	}
	//团购缩略图
	if($(".img_icon_upload_box span").length<=0){
		$.showErr("请上传缩略图");
		return false;
	}
	//团购图片集
	if($(".focus_imgs_upload_box span").length<=0){
		$.showErr("请上传图集");
		return false;
	}
	

	return true;
	
}

function init_img_del(){
	$(".pub_upload_img_box").find("a").bind("click",function(){
		$(this).parent().remove();
	});
}



