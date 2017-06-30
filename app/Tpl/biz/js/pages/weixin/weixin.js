$(function(){

    //初始化图片删除事件
    init_img_del();

	//绑定团购商品类型，显示属性
	$("select[name='deal_goods_type']").bind("change",function(){
		load_attr_html();
	});
	
	// 回复跳转链接
	$("select[name='ctltype']").bind("change",function(){
		var key = $("select[name='ctltype']").val();
		var field_id = navs[key]['field'];
		var field_name = navs[key]['fname'];

        if(field_id && field_id != 'spid')
        {
            $(".data").show();
        }
		else
		{
			$(".data").hide();
		}
		
		$(".field_name").html(field_name);
		if(field_name!="")
		{
			$(".field_name").show();
		}
		else
		{
			$(".field_name").hide();
		}
	});

    $(".remove_relate").live("click",function(){
        $(this).parent().parent().remove();
        if($("input[name='relate_reply_id[]']").length==0)
        {
            $("#relate_table").remove();
            $("#relate_table_div").hide();
        }
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
			'<input type="hidden" name="reply_news_picurl" value="'+responseObject.url+'" />';
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

	
	/*新增商品属性*/
	$("button.add_goods_type").bind("click",function(){
				
		var query = new Object();
		query.act = "load_add_mutil_news_weebox";
		
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(result){
				$.weeboxs.open(result.html, {boxid:'add_goods_type_weebox',contentType:'text',showButton:true,title:"选择要关联的图文回复",width:570,type:'wee',onopen:function(){
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
						
					},
					onok:onConfirmRelate
				});
			}
		});
	});
	
	function onConfirmRelate()
	{
	    var rowsCbo = $("input[rel='relate_reply_id']:checked");
	    
	    if(rowsCbo.length>0)
	    {
	        var relate_table = $("#relate_table");
	        if(relate_table.length==0)
	        {
	            var relate_table = $("<table class='table_box' id='relate_table' style='border: 1px solid #ddd;border-collapse: collapse;border-spacing: 0;clear: both;margin-bottom: 10px; width: 100%;'><tr><th>操作</th><th>回复内容</th></tr></table>");
	            $("#relate_table_div").append(relate_table);
	            $("#relate_table_div").show();
	        }

	        $.each(rowsCbo,function(i,o){
	            //alert($(o).val());
	            if($("#relate_reply_id_"+$(o).val()).length==0)
	            {
	                    if($("input[name='relate_reply_id[]']").length>=9)
	                    {
	                    	$.weeboxs.close();
	                        return;
	                    }
	                    var row = $("<tr><td><a href='javascript:void(0);' class='remove_relate'>删除</a></td><td><input type='hidden' id='relate_reply_id_"+$(o).val()+"' name='relate_reply_id[]' value='"+$(o).val()+"' />"+$(o).parent().parent().find(".reply_content").html()+"</td></tr>");
	                    $(relate_table).append(row);

	            }
	        });

	    }
	    
	    $.weeboxs.close();
	}
	
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

    /*默认图文回复*/
    $("form[name='wxreply_dnews_form']").submit(function(){
        var form = $("form[name='wxreply_dnews_form']");
        if(check_wxreply_news_form_submit()){
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
                        //$(".sub_form_btn").html('<button class="ui-button " rel="orange" type="submit">确认提交</button>');
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

	/*发布*/
	$("form[name='wxreply_text_publish_form']").submit(function(){
		var form = $("form[name='wxreply_text_publish_form']");
		if(check_wxreply_text_form_submit()){
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
						//$(".sub_form_btn").html('<button class="ui-button " rel="orange" type="submit">确认提交</button>');
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


    /*图文发布 发布操作JS待整合  TODO....*/
    $("form[name='wxnews_publish_form']").submit(function(){
        var form = $("form[name='wxnews_publish_form']");
        if(check_wxnews_publish_form_submit()){
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
                        //$(".sub_form_btn").html('<button class="ui-button " rel="orange" type="submit">确认提交</button>');
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

    /*文本发布 发布操作JS待整合  TODO....*/
    $("form[name='wxtext_publish_form']").submit(function(){
        var form = $("form[name='wxtext_publish_form']");
        if(check_wxtext_publish_form_submit()){
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
                        //$(".sub_form_btn").html('<button class="ui-button " rel="orange" type="submit">确认提交</button>');
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

    /*LBS发布 发布操作JS待整合  TODO....*/
    $("form[name='wxlbs_publish_form']").submit(function(){
        var form = $("form[name='wxlbs_publish_form']");
        if(check_wxlbs_publish_form_submit()){
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
                        //$(".sub_form_btn").html('<button class="ui-button " rel="orange" type="submit">确认提交</button>');
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

    /*删除操作*/
    $("button.down_btn").bind("click",function(){
        var id = $(this).attr("data-id");

        if(id>0){
            $.showConfirm("确定要删除吗?",function(){
                var query = new Object();
                query.act = "do_delete";
                query.id = id;
                $.ajax({
                    url: ajax_url,
                    data: query,
                    dataType: "json",
                    success: function(obj){
                        if(obj.status)
                        {
                            $.showSuccess(obj.info,function(){window.location.href=window.location.href;});
                        }else
                        {
                            $.showErr(obj.info);
                        }
                    },
                    error:function(ajaxobj)
                    {
                        if(ajaxobj.responseText!='')
                            alert(ajaxobj.responseText);
                    }

                });
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
function check_wxreply_text_form_submit(){
	//回复内容
	if($.trim($("textarea[name='reply_content']").val())==''){
		$.showErr("请输入回复内容",function(){$("input[name='reply_content']").focus();});
		return false;
	}

	return true;
	
}

/*表单提交验证*/
function check_wxreply_news_form_submit(){
    //回复标题
    if($.trim($("input[name='reply_news_title']").val())==''){
        $.showErr("请输入回复标题",function(){$("input[name='reply_news_title']").focus();});
        return false;
    }

    //回复内容
    if($.trim($("textarea[name='reply_news_description']").val())==''){
        $.showErr("请输入回复内容",function(){$("textarea[name='reply_news_description']").focus();});
        return false;
    }

    return true;

}

/*图文表单提交验证*/
function check_wxnews_publish_form_submit(){
    //关键词
    if($.trim($("input[name='keywords']").val())==''){
        $.showErr("请输入关键词",function(){$("input[name='keywords']").focus();});
        return false;
    }
    //标题
    if($.trim($("input[name='reply_news_title']").val())==''){
        $.showErr("请输入回复标题",function(){$("input[name='reply_news_title']").focus();});
        return false;
    }
    //回复内容
    if($.trim($("textarea[name='reply_news_description']").val())==''){
        $.showErr("请输入回复内容",function(){$("textarea[name='reply_news_description']").focus();});
        return false;
    }

    return true;

}

/*文本表单提交验证*/
function check_wxtext_publish_form_submit(){
    //回复标题
    if($.trim($("input[name='keywords']").val())==''){
        $.showErr("请输入关键词",function(){$("input[name='keywords']").focus();});
        return false;
    }

    //回复内容
    if($.trim($("textarea[name='reply_content']").val())==''){
        $.showErr("请输入回复内容",function(){$("textarea[name='reply_content']").focus();});
        return false;
    }

    return true;

}

/*LBS表单提交验证*/
function check_wxlbs_publish_form_submit(){
    //回复地址
    if($.trim($("input[name='address']").val())==''){
        $.showErr("请输入地址",function(){$("input[name='address']").focus();});
        return false;
    }

    //回复匹配范围
    if($.trim($("input[name='scale_meter']").val())==''){
        $.showErr("请输入匹配范围",function(){$("input[name='scale_meter']").focus();});
        return false;
    }

    //标题
    if($.trim($("input[name='reply_news_title']").val())==''){
        $.showErr("请输入回复标题",function(){$("input[name='reply_news_title']").focus();});
        return false;
    }

    //回复内容
    if($.trim($("textarea[name='reply_news_description']").val())==''){
        $.showErr("请输入回复内容",function(){$("textarea[name='reply_news_description']").focus();});
        return false;
    }

    return true;

}

function init_img_del(){
	$(".pub_upload_img_box").find("a").bind("click",function(){
		$(this).parent().remove();
	});
}


function init_nav_row_state(row)
{
    var key = $(row).find(".ctl").val();

    var field_id = navs[key]['field'];
    var field_name = navs[key]['fname'];

    if(field_id && field_id != 'spid')
    {
        $(row).find(".data").show();
    }
    else
    {
        $(row).find(".data").hide();
    }

    $(row).find(".field_name").html(field_name);
    if(field_name!="")
    {
        $(row).find(".field_name").show();
    }
    else
    {
        $(row).find(".field_name").hide();
    }
}

function init_nav_row(row)
{
    init_nav_row_state(row);
    $(row).find(".ctl").bind("change",function(){
        init_nav_row_state(row);
    });
}
