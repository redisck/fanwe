$(function(){
	//加载城市
	init_select_city();
	//加载地区
	load_area_list_box();
	//子分类载入
	init_sub_cate();	
	//初始化图片删除事件
	init_img_del();
	
	
	/*页面事件绑定调用*/
	$("select[name='cate_id']").bind("change",function(){
		init_sub_cate();	//子分类载入
	});
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
			'<input type="hidden" name="logo" value="'+responseObject.url+'" />';
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
	
	
	
	$(".location_images_upbtn div.location_images_btn").ui_upload({multi:true,FilesAdded:function(files){
		//选择文件后判断
		if($(".location_images_upload_box").find("span").length+files.length>MAX_SP_IMAGE)
		{
			$.showErr("最多只能传"+MAX_SP_IMAGE+"张图片");
			return false;
		}
		else
		{
			for(i=0;i<files.length;i++)
			{
				var html = '<span><div class="loader"></div></span>';
				var dom = $(html);		
				$(".location_images_upload_box").append(dom);	
			}
			uploading = true;
			return true;
		}
	},FileUploaded:function(responseObject){
		if(responseObject.error==0)
		{
			var first_loader = $(".location_images_upload_box").find("span div.loader:first");
			var box = first_loader.parent();
			first_loader.remove();
			var html = '<a href="javascript:void(0);"></a>'+
			'<img src="'+APP_ROOT+'/'+responseObject.web_40+'" />'+
			'<input type="hidden" name="banner_images[]" value="'+responseObject.url+'" />';
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
	var brief_editor = $("#brief").ui_editor({url:K_UPLOAD_URL,width:"450",height:"250",fun:function(){$(this).html($("textarea[name='brief']").val())} });

	
	/*发布*/
	$("form[name='location_publish_form']").submit(function(){
		
		var form = $("form[name='location_publish_form']");
		var name = $("input[name='name']").val();
		if($.trim(name)==''){
			$.showErr("请输入门店名称");
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
					$(".sub_form_btn").html('<button class="ui-button " rel="orange" type="submit">确认提交</button>');
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
	
	
	/*选项卡切换*/
	$(".tab_item").bind("click",function(){
		var index = $(".tab_item").index(this);
		$(".tab_item").removeClass("curr");
		$(this).addClass("curr");
		$(".con_item").removeClass("curr");
		$(".con_item").eq(index).addClass("curr");
		
		if($(".tab_item").length ==(index+1) ){
			$("div.next_form_btn").hide();
		}else{
			$("div.next_form_btn").show();
		}
	});
	/*下一页表单切换*/
	$("button.next_form_btn").bind("click",function(){
		var curr_tab_index = $(".tab_item").index($(".tab_item.curr"));
		if(($(".tab_item").length-1)>curr_tab_index){
			$(".tab_item").removeClass("curr");
			$(".tab_item").eq((curr_tab_index+1)).addClass("curr");
			$(".con_item").removeClass("curr");
			$(".con_item").eq((curr_tab_index+1)).addClass("curr");
		}
		if(($(".tab_item").length-1) == (curr_tab_index+1)){
			$("div.next_form_btn").hide();
		}else{
			$("div.next_form_btn").show();
		}
		$("html,body").animate({scrollTop:0},200);
	});
	
	
	

	
});//JQUERY END


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



