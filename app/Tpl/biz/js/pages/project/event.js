$(function(){
	//初始化地图
	ini_map();
	//加载城市
	init_select_city();
	//加载地区
	load_area_list_box();	
	//初始化图片删除事件
	init_img_del();
	//点击门店定位地图
	location_map();
	
	/*切换城市*/
	$("button.select_city_btn").bind("click",function(){
		if($(".city_list_box").is(":hidden")){
			$(document).bind("click",function(e){ 
				var obj = e.srcElement ? e.srcElement : e.target;
				var target = $(obj); 
				if(target.closest(".city_list_box").length == 0 && target.closest(".city_select_box").length == 0){ 
					$(".city_list_box").hide();
					$(document).unbind("click");
				}else{
					$(".city_list_box").show();
				}
			});
		}else{
			$(".city_list_box").hide();
			$(document).unbind("click");
		}
		
	});
	
	//上传控件
	$(".preview_upbtn div.preview_btn").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		if($(".preview_upload_box").find("span").length+files.length>1)
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
				$(".preview_upload_box").append(dom);	
			}
			uploading = true;
			return true;
		}
	},FileUploaded:function(responseObject){
		if(responseObject.error==0)
		{
			var first_loader = $(".preview_upload_box").find("span div.loader:first");
			var box = first_loader.parent();
			first_loader.remove();
			var html = '<a href="javascript:void(0);"></a>'+
			'<img src="'+APP_ROOT+'/'+responseObject.web_40+'" />'+
			'<input type="hidden" name="preview" value="'+responseObject.url+'" />';
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
	
	
	
	$(".icon_upbtn div.icon_btn").ui_upload({multi:false,FilesAdded:function(files){
		//选择文件后判断
		if($(".icon_upload_box").find("span").length+files.length>1)
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
				$(".icon_upload_box").append(dom);	
			}
			uploading = true;
			return true;
		}
	},FileUploaded:function(responseObject){
		if(responseObject.error==0)
		{
			var first_loader = $(".icon_upload_box").find("span div.loader:first");
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
	
	
	//编辑器
	var brief_editor = $("#content").ui_editor({url:K_UPLOAD_URL,width:"450",height:"250",fun:function(){$(this).html($("textarea[name='brief']").val())} });

	
	/*发布*/
	$("form[name='event_publish_form']").submit(function(){
		var form = $("form[name='event_publish_form']");
		var name = $("input[name='name']").val();
		var cate_id = $("select[name='cate_id']").val();
		if($.trim(name)==''){
			$.showErr("请输入活动名称");
			return false;
		}
		if(!cate_id >0){
			$.showErr("请选择分类");
			return false;
		}
		
		//支持门店
		if($("input.location_id_item:checked").length<=0){
			$.showErr("至少支持一个门店");
			return false;
		}

		$(".sub_form_btn").html('<button class="ui-button" rel="disabled" type="button">提交</button>');
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
					$(".sub_form_btn").html('<button class="ui-button" rel="orange" type="submit">确认提交</button>');
					init_ui_button();
					$.showErr(data.info);
				}else if(data.status==1){
					$.showSuccess(data.info,function(){window.location = data.jump;});
				}
				return false;
			}
		});
		
		return false;
	});
	
	/*日期控件*/
	$("input[name='event_begin_time']").datetimepicker({format: "Y-m-d H:i"});
	$("input[name='event_end_time']").datetimepicker({format: "Y-m-d H:i"});
	
	$("input[name='submit_begin_time']").datetimepicker({format: "Y-m-d H:i"});
	$("input[name='submit_end_time']").datetimepicker({format: "Y-m-d H:i"});
	
	

});//JQUERY END

/*地图初始化*/
function ini_map()
{
	var xpoint ='119.3';
	var ypoint ='26.1';
	if($("input[name='xpoint']").val()){
		 xpoint = $("input[name='xpoint']").val();
	}
	
	if($("input[name='ypoint']").val()){
		 ypoint = $("input[name='ypoint']").val();
	}
	
	draw_map(xpoint,ypoint);	
	$("#search_api").bind("click", function() {
		var api_address = $("input[name='api_address']").val();		
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
		if($("input[name='xpoint']").val()){
			xpoint = $("input[name='xpoint']").val();
		}
		
		if($("input[name='ypoint']").val()){
			ypoint = $("input[name='ypoint']").val();
		}
		editMap(xpoint, ypoint);
	});		
}
/**
 * 载入地区
 */
function load_area_list_box(){
	var id = $("input[name='id']").val();
	var city_id = $("input[name='city_id']").val();
	var edit_type = $("input[name='edit_type']").val();
	if(city_id>0){
		var query = new Object();
		query.act = "load_area_list_box";
		query.city_id = city_id;
		query.edit_type = edit_type;
		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			success:function(data){
				$("#area_list").html(data);
				$(".area_box").show();
				
				return false;
			}
		});
	}
}
/**
 * 选择城市
 * @param obj
 */
function select_city(obj){
	var city_id = $(obj).attr("data");
	$(".city_item").removeClass("curr");
	$(obj).addClass("curr");
	$(".selected_city").html("<span>"+$(obj).html()+"</span>");
	$("input[name='city_id']").val(city_id);
	$(".city_list_box").hide();
	$(document).unbind("click");
	if(city_id>0){
		$(".area_box").show();
		load_area_list_box();
	}else{
		$(".area_box").hide();
	}
	
}

function init_select_city(){
	var city_id = parseInt($("input[name='city_id']").val());
	if(city_id>0){
		$(".city_item[data='"+city_id+"']").addClass("curr");
	}
	var obj = $(".city_item.curr");
	if(obj.length>0){
		var city_id = $(obj).attr("data");
		$(".selected_city").html("<span>"+$(obj).html()+"</span>");
	}
	
}


function init_img_del(){
	$(".pub_upload_img_box").find("a").bind("click",function(){
		$(this).parent().remove();
	});
}

function add_submit_row()
{
	var html_str = '<div class="submit_item">'
				 	+'<input type="hidden" name="field_id[]" />'
					+'字段显示:<input type="text" class="ui-textbox" name="field_show_name[]" value="" />'
					+'类型:<select class="filter_select small" name="field_type[]" onchange="change_type(this);"><option value="0">用户填写</option><option value="1">预选下拉</option></select>'
					+'<span style="display:none;">预选内容:<input type="text" class="ui-textbox" name="value_scope[]" placeholder="用空格分隔" value="" /> </span> '
					+'[<a href=\'javascript:void(0);\' onclick=\'remove_row(this,0);\'>-</a>]'
					+'<div style="height:5px;"></div>';
					+'</div>';
	$("#submit_conf_row").append(html_str);
}
function remove_row(obj,id)
{
	if(id>0)
	{
		if(confirm("删除该配置有可能影响已报名的数据，确定删除吗？"))
		{
			$(obj).parent().remove();
		}
	}
	else
	$(obj).parent().remove();
}
function change_type(obj)
{
	if($(obj).val()>0)
	{
		$(obj).parent().find("span").show();
	}
	else
	{
		$(obj).parent().find("span").hide();
	}
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
