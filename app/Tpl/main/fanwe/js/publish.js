var FANWE = new Object();
var uploading = false; //上传中的变量，正在上传中表单不能提交
$(function() {
	/**
	 * 图片采集面板
	 */
	$.init_topic_uploader = function(){
			var img_ids = new Array();
			var img_count = 0;
			var img_total = 0;
			var load_width = 0;
			var status_position = 250;
			$(".publish_box_options .pub_img").ui_upload(
				{url:TOPIC_IMAGE_UPLOAD,multi:true,FilesAdded:function(files){

							if(img_ids.length+files.length>8)
							{
								$.showErr("最多只能传8张图片");
								return false;
							}
							else
							{
								img_total = files.length;
								load_width = 200/img_total;
								load_width = load_width.toFixed(4);

								$(".up_loading").html('<span><em class="img_total">0</em> / '+img_total+'</span>');
								uploading = true;
								return true;
							}
							
						},FileUploaded:function(responseObject){
							if(responseObject.error==0)
							{
								img_count++;
								img_ids.push(responseObject.id);
								if(img_count <= img_total){
									$(".img_total").html(img_count);
									status_position = status_position-load_width;
									$(".loading_status").css("backgroundPositionX","-"+status_position+"px");
								}
							}
							else
							{
								if(responseObject.error==1000)
								{
									ajax_login();
								}
								else
								{
									$.showErr(errObject.message);
								}
							}
							
						},UploadComplete:function(files){
							var query = new Object();
							query.act = "publish_img_edit";
							query.img_ids = img_ids;
							
							$.ajax({
								url:AJAX_URL,
								data:query,
								dataType:"json",
								type:"post",
								global : false,
								success:function(data){
									if(data.status == 1){
										$.weeboxs.close("FAST_PUBLISH_BOX");
											//打开图片发布编辑面板
												$.weeboxs.open(data.html, {boxid : 'PUBLISH_IMG_EDIT', position:"fixed", title : "图片发布", contentType : 'text',draggable : false,showButton : false,width : 798,height : 460,onclose:function(){
													$.weeboxs.close("form_pop_box");
												},onopen:function(){
													$(".form_album").ui_upload({url:TOPIC_IMAGE_UPLOAD,multi:true,FilesAdded:function(files){
														/*传图过程禁用按钮*/
														$(".publish_btn_box").html("");
														$(".publish_btn_box").html('<button class="ui-button" rel="disabled" type="button">提交</button>');
														init_ui_button();
														if($("#pub_upload_img_box").find("span").length+files.length>9)
														{
															$.showErr("最多只能传9张图片");
															return false;
														}
														else
														{
															for(i=0;i<files.length;i++)
															{
																var html = '<span><div class="loader"></div></span>';
																var dom = $(html);		
																$("#pub_upload_img_box").append(dom);	
															}
															uploading = true;
															return true;
														}
														
													},FileUploaded:function(responseObject){
														if(responseObject.error==0)
														{
															var first_loader = $("#pub_upload_img_box").find("span div.loader:first");
															var box = first_loader.parent();
															box.remove(first_loader);
															var html = '<a href="javascript:void(0);"></a>'+
															'<img src="'+APP_ROOT+'/'+responseObject.thumb.preview.url+'" />'+
															'<input type="hidden" name="dp_image[]" value="'+responseObject.url+'" />'+
															'<input type="hidden" name="topic_image_id[]" value="'+responseObject.id+'" />';		
															$(box).html(html);
															$(box).find("a").bind("click",function(){
																$(this).parent().remove();
															});
														}
														else
														{
															if(responseObject.error==1000)
															{
																ajax_login();
															}
															else
															{
																$.showErr(errObject.message);
															}
														}
														
													},UploadComplete:function(files){
														$(".publish_btn_box").html("");
														$(".publish_btn_box").html('<button class="ui-button" rel="orange" type="submit"> 提 交 </button>');
														init_ui_button();
														uploading = false;
													},Error:function(errObject){
														$.showErr(errObject.message);
													}});
														
													init_ui_button(); 
													init_ui_textbox();
													init_ui_checkbox();
													init_ui_select();

													valid_length(2000);
													$("#publish_item_textarea").live("change keyup",function(){
														valid_length(2000);
													});	
													$("#publish_item_textarea").live("click",function(){
														$.weeboxs.close("form_pop_box");
													});	
													
													$("select[name='cate_id']").bind("change",function(){
														load_group();
													});
													
													$("#pub_upload_img_box").find("a").bind("click",function(){
																$(this).parent().remove();
															});
												}});	
											}
								}
							});
							
							uploading = false;
							//上传完成提交到显示页面
							
							
						},Error:function(errObject){
							$.showErr(errObject.message);
						}}
			);	
	};
	
	/*宝贝 ，图片 ， 文章 主面板*/
	$.fast_publish = function() {
		$.weeboxs.close("FAST_PUBLISH_BOX");
		if(FANWE.FAST_PUBLISH_HTML==null) {
			var query = new Object();
			query.act = "publish_box";
			$.ajax({
				url : AJAX_URL,
				data : query,
				dataType : "json",
				type : "post",
				global : false,
				success : function(data) {
					if(data.status) {
						FANWE.FAST_PUBLISH_HTML = data.html;
						$.weeboxs.open(FANWE.FAST_PUBLISH_HTML, {boxid : 'FAST_PUBLISH_BOX',position:"fixed",title : "我要发",onopen:function(){
							$.init_topic_uploader();
						}, contentType : 'text',draggable : false,showButton : false,width : 598,height : 218});	
						
						
					} else {
						ajax_login();
						return false;
					}
				}
			});
			
		} else {
			$.weeboxs.open(FANWE.FAST_PUBLISH_HTML, {boxid : 'FAST_PUBLISH_BOX',position:"fixed",title : "我要发",onopen:function(){
				$.init_topic_uploader();
			}, contentType : 'text',draggable : false,showButton : false,width : 598,height : 218});	
		}

	};
	//$.init_topic_uploader();
	/*宝贝采集面板*/
	$.publish_goods = function(){
		$.weeboxs.close("FAST_PUBLISH_BOX");
		$.weeboxs.close("PUBLISH_GOODS_BOX");
		if(FANWE.PUBLISH_GOODS_BOX_HTML == null) {
			var query = new Object();
			query.act = "publish_goods";
			$.ajax({
				url : AJAX_URL,
				data : query,
				dataType : "json",
				type : "post",
				global : false,
				success : function(data) {
					
					if(data.status) {
						FANWE.PUBLISH_GOODS_BOX_HTML = data.html;
						$.weeboxs.open(FANWE.PUBLISH_GOODS_BOX_HTML, {boxid : 'PUBLISH_GOODS_BOX',position:"fixed",title : "添加宝贝",contentType : 'text',draggable : false,showButton : false,width : 598,height : 190,onopen:function(){init_ui_button(); init_ui_textbox();}});						
					} else {
						ajax_login();
						return false;
					}
				}
			});
			
		} else {
			$.weeboxs.open(FANWE.PUBLISH_GOODS_BOX_HTML, {boxid : 'PUBLISH_GOODS_BOX',position:"fixed",title : "添加宝贝",contentType : 'text',draggable : false,showButton : false,width : 598,height : 190,onopen:function(){init_ui_button(); init_ui_textbox();}});
		}
		
	};
	
	/*宝贝采集面板*/
	$("form[name='pub_goods']").live("submit",function(){
		var form = $("form[name='pub_goods']");
		var ajax_url = $(form).attr("action");
		var query = $(form).serialize();
		
		$(".pub_g_content").html("");
		var load_html = '<div class="loading" style="width:100px;"></div>';
		$(".pub_g_content").css("margin-top","18px");
		$(".pub_g_content").html(load_html);
		
		$.ajax({
			url:ajax_url,
			data:query,
			dataType:"json",
			type:"post",
			global:false,
			success:function(data){
				$.weeboxs.close("PUBLISH_GOODS_BOX");
				if(data.status){
					$.publish_goods_info(data);
				}else{
					$.showErr(data.info);
				}
				
			}
		});
		return false;
	});
	
	/*宝贝编辑面板*/
	$.publish_goods_info = function(data){
		$.weeboxs.close("PUBLISH_GOODS_BOX");
		$.weeboxs.close("PUBLISH_GOODS_INFO_BOX");
		
		var query = new Object();
		query.act = "publish_goods_info";
		query.data = data;
		
		$.ajax({
			url : AJAX_URL,
			data : query,
			dataType : "json",
			type : "post",
			global : false,
			success : function(data) {
				if(data.status) {
					$.weeboxs.open(data.html, {boxid : 'PUBLISH_GOODS_INFO_BOX',position:"fixed",title : "添加描述",contentType : 'text',draggable : false,showButton : false,width : 598,height : 209,onclose:function(){
													$.weeboxs.close("form_pop_box");
												},onopen:function(){
													init_ui_button(); 
													init_ui_textbox();
													init_ui_checkbox();
													init_ui_textbox();
													valid_length(140);/*字数控制*/
						$("#publish_item_textarea").live("change keyup",function(){
								valid_length(140);
							});	
					}});						
				} else {
					ajax_login();
					return false;
				}
			}
		});
		
	};
	
	/*站内分享提交*/
	$("form[name='publish_edit_form']").live("submit",function(){
		var form = $("form[name='publish_edit_form']");
		
		if($.trim($("textarea[name='content']").val())==''){
			$.showErr("发布内容不能为空！");
			return false;
		}
		$(".publish_btn_box").html("");
		$(".publish_btn_box").html('<button class="ui-button" rel="disabled" type="button">提交中...</button>');
		init_ui_button(); 
		
		var url = $(form).attr("action");
		var query = $(form).serialize();
		$.ajax({
			url : url,
			type : "POST",
			data : query,
			dataType : "json",
			success : function(data) {
				if(data.status == 1){
					$.weeboxs.close("PUBLISH_GOODS_INFO_BOX");
					$.showSuccess("发布成功！",function(){
						if($("input[name='jump']").length>0)
							window.location=$("input[name='jump']").val();
						else
							location.reload();
					});
				}else{
					$.showErr(data.info);
				}
				
				return false;
			}
		});
		return false;
	});
	

	/*文章编辑面板*/
	$.publish_article = function(){
		var group_id = arguments[0] ? arguments[0] : 0;
		$.weeboxs.close("FAST_PUBLISH_BOX");
		$.weeboxs.close("PUBLISH_ARTICLE_EDIT_BOX");
		var query = new Object();
		query.act = "publish_article_edit";
		if(group_id>0){
			query.group_id = group_id;
		}
		$.ajax({
			url:AJAX_URL,
			data : query,
			type:"post",
			dataType:"json",
			success:function(data){
				$.weeboxs.open(data.html, {boxid : 'PUBLISH_ARTICLE_EDIT_BOX',position:"fixed",title : "添加描述",contentType : 'text',draggable : false,showButton : false,width : 798,height : 645,onclose:function(){
													$.weeboxs.close("form_pop_box");
												},
				onopen:function(){
					$(".form_album").ui_upload({url:TOPIC_IMAGE_UPLOAD,multi:true,FilesAdded:function(files){
							/*传图过程禁用按钮*/
							$(".publish_btn_box").html("");
							$(".publish_btn_box").html('<button class="ui-button" rel="disabled" type="button">提交</button>');
							init_ui_button();
							if($("#pub_upload_img_box").find("span").length+files.length>9)
							{
								$.showErr("最多只能传9张图片");
								return false;
							}
							else
							{
								for(i=0;i<files.length;i++)
								{
									var html = '<span><div class="loader"></div></span>';
									var dom = $(html);		
									$("#pub_upload_img_box").append(dom);	
								}
								uploading = true;
								return true;
							}
							
						},FileUploaded:function(responseObject){
							if(responseObject.error==0)
							{
								var first_loader = $("#pub_upload_img_box").find("span div.loader:first");
								var box = first_loader.parent();
								box.remove(first_loader);
								var html = '<a href="javascript:void(0);"></a>'+
								'<img src="'+APP_ROOT+"/"+responseObject.thumb.preview.url+'" />'+
								'<input type="hidden" name="dp_image[]" value="'+responseObject.url+'" />'+
								'<input type="hidden" name="topic_image_id[]" value="'+responseObject.id+'" />';		
								$(box).html(html);
								$(box).find("a").bind("click",function(){
									$(this).parent().remove();
								});
							}
							else
							{
								if(responseObject.error==1000)
								{
									ajax_login();
								}
								else
								{
									$.showErr(errObject.message);
								}
							}
							
						},UploadComplete:function(files){
							$(".publish_btn_box").html("");
							$(".publish_btn_box").html('<button class="ui-button" rel="orange" type="submit"> 提 交 </button>');
							init_ui_button();
							uploading = false;
						},Error:function(errObject){
							$.showErr(errObject.message);
						}});
							
						init_ui_button(); 
						init_ui_textbox();
						init_ui_checkbox();
						init_ui_textbox();
						init_ui_select();

						valid_length(2000);
						$("#publish_item_textarea").live("change keyup",function(){
							valid_length(2000);
						});	
						$("#publish_item_textarea").live("click",function(){
							$.weeboxs.close("form_pop_box");
						});	
						
						$("select[name='cate_id']").bind("change",function(){
							load_group();
						});
					}
				});	
			}
		});
	};
	
	/*文章分享提交*/
	$("form[name='publish_article_edit_form']").live("submit",function(){
		var form = $("form[name='publish_article_edit_form']");
		if($.trim($("input[name='forum_title']").val())==''){
			$.showErr("发布标题不能为空！");
			return false;
		}
		if($.trim($("textarea[name='content']").val())==''){
			$.showErr("发布内容不能为空！");
			return false;
		}
		$(".publish_btn_box").html("");
		$(".publish_btn_box").html('<button class="ui-button" rel="disabled" type="button">提交中...</button>');
		init_ui_button(); 
		var url = $(form).attr("action");
		var query = $(form).serialize();
		$.ajax({
			url : url,
			type : "POST",
			data : query,
			dataType : "json",
			success : function(data) {
				if(data.status == 1){
					$.weeboxs.close("PUBLISH_ARTICLE_EDIT_BOX");
					$.showSuccess(data.info,function(){
								if(data.jump)
									window.location=data.jump;
								else
									location.reload();}
							);
				}else{
					$.showErr(data.info);
				}
				
				return false;
			}
		});
		return false;
	});	
		
	/*图片分享提交*/
	$("form[name='publish_img_edit_form']").live("submit",function(){
		var form = $("form[name='publish_img_edit_form']");
		
		if($.trim($("textarea[name='content']").val())==''){
			$.showErr("发布内容不能为空！");
			return false;
		}

		$(".publish_btn_box").html("");
		$(".publish_btn_box").html('<button class="ui-button" rel="disabled" type="button">提交中...</button>');
		init_ui_button(); 
		var url = $(form).attr("action");
		var query = $(form).serialize();
		$.ajax({
			url : url,
			type : "POST",
			data : query,
			dataType : "json",
			success : function(data) {
				if(data.status == 1){
					$.weeboxs.close("PUBLISH_IMG_EDIT");
					$.showSuccess(data.info,function(){
								if(data.jump)
									window.location=data.jump;
								else
									location.reload();}
							);
				}else{
					$.showErr(data.info);
				}
				
				return false;
			}
		});
		return false;
	});	
	
});



/*=============  jquer 方法==============*/
$(function(){
	
	/*表情面板*/
	$(".form_face_publish_item_textarea").live("click",function(){
		var obj = this;
		var face_html = $("#face_box_hd_publish_item_textarea").html();
		var face_tab_html = $("#face_box_tab_publish_item_textarea").html();
		$.weeboxs.open(
			face_html, 
			{
				boxid:'form_pop_box',
				contentType:'text',
				position:'element',
				trigger:obj,
				draggable:false,
				modal:false,
				showButton:false,
				title:face_tab_html,
				width:405
			});
		bind_publish_item_textarea_set_expression();
	});
		
	/*主题*/
	$(".form_topic").live("click",function(){
		var obj = this;
		var cnt = $("#publish_item_textarea").val();
		var topic_append = "#在这里输入您发布的标题#";
		var topic_prepare = "在这里输入您发布的标题";
		if(cnt.indexOf(topic_append)==-1)
		insert_publish_item_textarea_cnt(topic_append);			
		$("#publish_item_textarea").parent().find("input[name='group']").val("share");
		chooseText("publish_item_textarea",topic_prepare);
		valid_length();
	});
	
});

function load_group(){
	var cate_id = $("select[name='cate_id']").val();
	var query = new Object();
	query.act="get_group_by_cateid";
	query.cate_id = cate_id;
	$.ajax({
		url:AJAX_URL,
		data : query,
		type:"post",
		dataType:"json",
		success:function(data){
			if(data[0]){
				var html = "<option value='0'>选择小组</option>";
				
			}else{
				var html = "<option value='0'>暂时没有小组</option>";
			}
			
			
			for(var item in data)
			{
				html+="<option value='"+data[item].id+"'>"+data[item].name+"</option>";
			}
			$("select[name='group_id']").html(html);
			$("select[name='group_id']").ui_select({refresh:true});
		}
		
	});
}
function bind_publish_item_textarea_set_expression()
{
	$(".emotion_publish_item_textarea").find("a").bind("click",function(){
		var o = $(this);
		insert_publish_item_textarea_cnt("["+$(o).attr("rel")+"]");	
	});
	
}

function insert_publish_item_textarea_cnt(cnt)
{
	var val = $("#publish_item_textarea").val();
//	var pos = $("#publish_item_textarea").attr("position");
//	var bpart = val.substr(0,pos);
//	var epart = val.substr(pos,val.length);
//	$("#publish_item_textarea").val(bpart+cnt+epart);
	$("#publish_item_textarea").val(val+cnt);
	$.weeboxs.close("form_pop_box");
	
}
function valid_length(number)
{
	var c = $("#publish_item_textarea").val();
	$(".publish_text_count em").html(c.length);
	if(c.length>number)
	{
		$("#publish_item_textarea").val(c.substr(0,number));
	}
}

function toogle_mo(o)
{
	$(o).blur();
	$(o).parent().parent().parent().parent().parent().find(".emotion").hide();
	$(o).parent().parent().find("li").removeClass("c");
	$(o).parent().addClass("c");
	$(o).parent().parent().parent().parent().parent().find(".emotion[f='"+$(o).parent().attr("f")+"']").css("display","inline-block");
}

//主题高亮显示  
 function chooseText(target,content)  
 {  
     var target = document.getElementById(target);  
	 var origin_content = target.value;
     var start = origin_content.indexOf(content);		
	 var l = target.value.length;
     if(target.createTextRange){//IE浏览器  
         var range = target.createTextRange();	         
         range.moveEnd("character",-l);                  
         range.moveEnd("character",content.length+origin_content.indexOf(content));
         range.moveStart("character", start);
         range.select();  
     }else{  
         target.setSelectionRange(start,start+content.length);  
         target.focus();  
     }  
 }  
 /*订单商品分享*/
 function deal_shere(id){
	 var query = new Object();
	 query.act="deal_shere";
	 query.id = id;
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"post",
			global:false,
			success:function(data){
				if(data.status){
					$.publish_goods_info(data);
				}else{
					$.showErr(data.info);
				}
				
			}
		});
	}
 
 /*优惠券领取分享*/
 function youhui_shere(id){
	 var query = new Object();
	 query.act="youhui_shere";
	 query.id = id;
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"post",
			global:false,
			success:function(data){
				if(data.status){
					$.weeboxs.close("wee_youhui_download");
					$.publish_goods_info(data);
				}else{
					$.showErr(data.info);
				}
				
			}
		});
	}
 
 function order_share(id){
	 var url_data = $("input[name='is_share']").attr("url_data");
	 if($("input[name='is_share']").attr("checked")==true){
		 var query = new Object();
		 query.act="order_shere";
		 query.id = id;
			$.ajax({
				url:AJAX_URL,
				data:query,
				dataType:"json",
				type:"post",
				global:false,
				success:function(obj){
					if(obj.status){
						$.showSuccess(obj.info,function(){
							window.location = url_data;
						});
					}else{
						$.showErr(obj.info);
					}
				}
			});
	 }else{
		 window.location = url_data;
	 }
	 
 }
