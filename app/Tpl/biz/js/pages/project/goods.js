$(function(){
	
	load_filter_box(); //初始化筛选关键词
	$("select[name='shop_cate_id']").bind("change",function(){
		 load_filter_box();
	});
	
	/*发布*/
	$("form[name='goods_publish_form']").submit(function(){
		
		
		var form = $("form[name='goods_publish_form']");
		if(check_goods_form_submit()){
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
	
//end jquery	
});



/*表单提交验证*/
function check_goods_form_submit(){
	//团购名称
	if($.trim($("input[name='name']").val())==''){
		$.showErr("请输入商品名称",function(){$("input[name='name']").focus();});
		return false;
	}
	//简短名称
	if($.trim($("input[name='sub_name']").val())==''){
		$.showErr("请输入简短名称",function(){$("input[name='sub_name']").focus();});
		return false;
	}


	//分类
	if(parseInt($("select[name='shop_cate_id']").val())<=0){
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


