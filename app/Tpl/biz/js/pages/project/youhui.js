$(function(){	
		
	/*页面初始化调用*/
	init_sub_cate();	//子分类载入
	init_select_city();	//城市初始化
	init_img_del();
	ini_map();//地图初始化
	location_map();//点击门店定位地图
	/*页面事件绑定调用*/
	$("select[name='cate_id']").bind("change",function(){
		init_sub_cate();
	});

	
	/* 图片上传初始化  */
	//列表展示图上传控件
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
			'<input type="hidden" name="icon" value="'+responseObject.url+'" />';
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
	
	

	//打印图上传控件
	$(".print_imgs_upbtn div.print_imgs_btn").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		if($(".print_imgs_upload_box").find("span").length+files.length>1)
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
				$(".print_imgs_upload_box").append(dom);	
			}
			uploading = true;
			return true;
		}
	},FileUploaded:function(responseObject){
		if(responseObject.error==0)
		{
			var first_loader = $(".print_imgs_upload_box").find("span div.loader:first");
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


	
	
	/*编辑器*/
	var description_editor = $("#description").ui_editor({url:K_UPLOAD_URL,width:"450",height:"250",fun:function(){$(this).html($("textarea[name='description']").val())} });
	var notes_editor = $("#notes").ui_editor({url:K_UPLOAD_URL,width:"450",height:"250",fun:function(){$(this).html($("textarea[name='notes']").val())} });

	
	/*日期控件*/
	$("input[name='begin_time']").datetimepicker({format: "Y-m-d H:i"});
	$("input[name='end_time']").datetimepicker({format: "Y-m-d H:i"});	

	
	/*发布*/
	$("form[name='deal_publish_form']").submit(function(){
		
		var form = $("form[name='deal_publish_form']");
		if(check_form_submit()){
			$(".sub_from_btn").html('<button class="ui-button" rel="disabled" type="button">提交</button>');
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
						$(".sub_from_btn").html('<button class="ui-button" rel="orange" type="submit">确认提交</button>');
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


/*地图初始化*/
function ini_map()
{	
	var xpoint='119.3';
	var ypoint='26.1';
	if($("input[name='xpoint']").val()>0&&$("input[name='ypoint']").val()>0){
		draw_map($("input[name='xpoint']").val(),$("input[name='ypoint']").val());	
	}else{
		draw_map('119.3','26.1');	
	}
	
	$("#search_api").bind("click", function() {
		var api_address = $("input[name='search_api_address']").val();		
		var city = $(".selected_city span").html();
	    if($.trim(api_address) == '') {
			$.showErr("请先输入地址");
		} else {
			search_api(api_address,city);
		}
	});
	
	$("#container_front").hide();
	$("#cancel_btn").bind("click", function() {
		$("#container_front").hide();
	});
	$("#chang_api").bind("click", function() {
		if($("input[name='xpoint']").attr('value')){
			xpoint=$("input[name='xpoint']").attr('value')
		}
		if($("input[name='ypoint']").attr('value')){
			ypoint=$("input[name='ypoint']").attr('value')
		}
		editMap(xpoint, ypoint);
	});		
}

/*点击门店定位地图*/
function location_map()
{
	$("input[name='location_id[]']").bind("click",function(){
		if(!$(this).attr("checked")&&$(this).attr("x")>0&&$(this).attr("y")>0){
			load_location_info($(this).attr("x"),$(this).attr("y"));
		}
		
	});	
}
function load_location_info(x,y)
{
		draw_map(x,y);
		$("input[name='xpoint']").val(x);
		$("input[name='ypoint']").val(y);
}





/*表单提交验证*/
function check_form_submit(){
	
	//团购名称
	if($.trim($("input[name='name1']").val())==''){
		$.showErr("请输入优惠券名称",function(){$("input[name='name']").focus();});
		return false;
	}
	//城市
	if(parseInt($("input[name='city_id']").val())<=0||$("input[name='city_id']").val()==""){
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
	//优惠券列表图
	if($(".img_icon_upload_box span").length<=0){
		$.showErr("请上传列表图");
		return false;
	}
	//优惠券打印图
	if($(".print_imgs_upload_box span").length<=0){
		$.showErr("请上传打印图");
		return false;
	}
	

	return true;
	
}

function init_img_del(){
	$(".pub_upload_img_box").find("a").bind("click",function(){
		$(this).parent().remove();
	});
}



