$(function(){	
	
	$(".is_effect_btn").bind("click",function(){
		var obj = $(this);
		var id = $(this).attr("data-id");
		var query = new Object();
		query.act = "dc_menu_status";
		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					if(data.is_effect ==1){
						$(obj).html("&#xe612;");
					}else{
						$(obj).html("&#xe60b");
					}
				}else{
					$.showErr(data.info);
				}
			}
		});
	});
	
	
	/*新增菜单*/
	$("button.add_menu_btn").bind("click",function(){
		var location_id = $(this).attr("data-id");
		var query = new Object();
		query.act = "load_add_menu_weebox";
		query.location_id = location_id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(result){
				$.weeboxs.open(result.html, {boxid:'add_menu_weebox',contentType:'text',showButton:false,title:"添加菜单",width:570,type:'wee',onopen:function(){
						init_ui_button();
						init_ui_textbox();
						init_ui_select();
						init_ui_checkbox();
						
						//上传控件
						$(".img_image_upbtn div.img_image_btn").ui_upload({multi:false,FilesAdded:function(files){
							//选择文件后判断
							if($(".img_image_upload_box").find("span").length+files.length>1)
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
									$(".img_image_upload_box").append(dom);	
								}
								uploading = true;
								return true;
							}
						},FileUploaded:function(responseObject){
							if(responseObject.error==0)
							{
								var first_loader = $(".img_image_upload_box").find("span div.loader:first");
								var box = first_loader.parent();
								first_loader.remove();
								var html = '<a href="javascript:void(0);"></a>'+
								'<img src="'+APP_ROOT+'/'+responseObject.web_40+'" />'+
								'<input type="hidden" name="image" value="'+responseObject.url+'" />';
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
						
						
						
						//提交数据
						$("form[name='add_menu_form']").submit(function(){
							var form = $("form[name='add_menu_form']");
							
							if($.trim($("input[name='menu_name']").val()) == ''){
								$.showErr("请输入新增菜单名称");
								return false;
							}
							
							var query = $(form).serialize();
							var url = $(form).attr("action");
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
							
							return false;
						});
						
					}
				});
			}
		});
	});
	
	
	/*编辑菜单*/
	$(".edit_menu_btn").bind("click",function(){
		var id = $(this).attr("data-id");
		var query = new Object();
		query.act = "load_edit_menu_weebox";
		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(result){
				if(result.status){
				$.weeboxs.open(result.html, {boxid:'edit_menu_weebox',contentType:'text',showButton:false,title:"编辑菜单",width:570,type:'wee',onopen:function(){
						init_ui_button();
						init_ui_textbox();
						init_ui_select();
						init_ui_checkbox();
						init_img_del();
						//上传控件
						$(".img_image_upbtn div.img_image_btn").ui_upload({multi:false,FilesAdded:function(files){
							//选择文件后判断
							if($(".img_image_upload_box").find("span").length+files.length>1)
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
									$(".img_image_upload_box").append(dom);	
								}
								uploading = true;
								return true;
							}
						},FileUploaded:function(responseObject){
							if(responseObject.error==0)
							{
								var first_loader = $(".img_image_upload_box").find("span div.loader:first");
								var box = first_loader.parent();
								first_loader.remove();
								var html = '<a href="javascript:void(0);"></a>'+
								'<img src="'+APP_ROOT+'/'+responseObject.web_40+'" />'+
								'<input type="hidden" name="image" value="'+responseObject.url+'" />';
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
						
						
						
						//提交数据
						$("form[name='edit_menu_form']").submit(function(){
							var form = $("form[name='edit_menu_form']");
							
							if($.trim($("input[name='menu_name']").val()) == ''){
								$.showErr("请输入新增菜单名称");
								return false;
							}
							
							var query = $(form).serialize();
							var url = $(form).attr("action");
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
							
							return false;
						});
						
					}
				});
			 }else{
				 $.showErr(result.info);
			 }
			}
		});
	});
	
	$("button.batch_del").bind("click",function(){
		$.showConfirm("确定要批量删除菜单吗?",function(){
			$("form[name='menu_manage_form']").submit();
		});
		return false;
		
	});
	
	$("form[name='menu_manage_form']").submit(function(){
		var form = $("form[name='menu_manage_form']");
		var query = $(form).serialize();
		var url = $(form).attr("action");
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
				return false;
			}
		});
		return false;
	});
	
});


function del_menu(obj){
	var id = $(obj).attr("data-id");
	$.showConfirm("确认删除吗？",function(){
		var query = new Object();
		query.act = "dc_menu_del";

		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					window.location = data.jump;
				}else{
					$.showErr(data.info);
				}
			}
		});
		
	});
	
}

function init_img_del(){
	$(".pub_upload_img_box").find("a").bind("click",function(){
		$(this).parent().remove();
	});
}