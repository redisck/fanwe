var region_obj = null;
$(document).ready(function(){
	
	$("#add_region_conf").bind("click",function(){
		add_region_row();
	});
	/*发布*/
	$("form[name='delivery_publish_form']").submit(function(){
		var form = $("form[name='delivery_publish_form']");
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
		
		return false;
	});

});
function add_region_row()
{
	if($("#region_conf").find("div").length>=5)
	{
		$.showErr("最多支持5条配置项");
	}
	else
	{
		var row_html = "<div>"+ 
		LANG['FIRST_WEIGHT'] + ":<input type='text' class='ui-textbox long_input' name='region_first_weight[]' style='width:40px;' />&nbsp;" +
		LANG['FIRST_FEE'] + ":<input type='text' class='ui-textbox long_input' name='region_first_fee[]' style='width:40px;' />&nbsp;" +
		LANG['CONTINUE_WEIGHT'] + ":<input type='text' class='ui-textbox long_input' name='region_continue_weight[]' style='width:40px;' />&nbsp;" +
		LANG['CONTINUE_FEE'] + ":<input type='text' class='ui-textbox long_input' name='region_continue_fee[]' style='width:40px;' />&nbsp;" +
		LANG['SUPPORT_REGION'] + ":<input type='text' class='ui-textbox long_input' name='region_support_region_name[]' style='width:100px;' onfocus='select_delivery_regions(this);' />&nbsp;" +
		"<input type='hidden' name='region_support_region[]' />"+
		" [ <a href='javascript:void(0);' onclick='$(this.parentNode).remove();' style='text-decoration:none;' title='删除'>-</a> ] </div>";
		$("#region_conf").append(row_html);
	}
	
	

}
function select_delivery_regions(obj)
{
	
	region_obj = obj;
	
	var region_conf_id = $(obj.parentNode).find("input[name='region_conf_id[]']").val();
	var ajax_url=APP_ROOT+'/biz.php?ctl=delivery&act=select_regions&region_conf_id='+region_conf_id;
	$.weeboxs.open(ajax_url, {boxid:'delivery_weebox',contentType:'ajax',showButton:false,title:LANG['SELECT_SUPPORT_REGION'],width:300,height:290,type:'wee',onopen:function(){
		init_ui_button();
		$(obj).removeClass("hover");
	    $(obj).removeClass("normal");
	    $(obj).addClass("normal");
	    $(obj).blur();
	}});
	
}
function switch_region(obj)
{
	
	var delivery_fee_id = $("input[name='delivery_fee_id']").val();

	var region_id = $(obj.parentNode).find("input[name='region_id[]']").val();
	
	var ajaxurl=APP_ROOT+'/biz.php?ctl=delivery&act=getSubRegion&id='+region_id+'&delivery_fee_id='+delivery_fee_id;
	if($.trim($(obj).html())=='+')
	{
		//打开
		if($(".region_level_"+region_id).length>0)
		{
			$(".region_level_"+region_id).find("input[name='region_id[]']").attr("checked",$(o.parentNode).find("input[name='region_id[]']").attr("checked"));
			$(".region_level_"+region_id).show();
			$(obj).html('-');
		}
		else
		{
			$.ajax({ 
				url: ajaxurl, 
				data: "ajax=1",
				success: function(html){
					
					$(obj.parentNode).append(html);
					if($(obj.parentNode).find("input[name='region_id[]']").attr("checked"))
					$(".region_level_"+region_id).find("input[name='region_id[]']").attr("checked",$(obj.parentNode).find("input[name='region_id[]']").attr("checked"));
					$(obj).html('-');				
				}
			});
		}
		
	}
	else
	{		
		$(".region_level_"+region_id).hide();
		$(obj).html('+');
	}
}
function select_region_ok()
{
	var cbo = $("input[name='region_id[]']:checked");
	var ids = '';
	var names = '';
	for(i=0;i<cbo.length;i++)
	{
		ids += $(cbo[i]).val()+",";
		names += $(cbo[i]).parent().find("span").html()+",";
	}
	ids = ids.substr(0,ids.length-1);
	names = names.substr(0,names.length-1);
	$(region_obj.parentNode).find("input[name='region_support_region[]']").val(ids);
	
	$(region_obj.parentNode).find("input[name='region_support_region_name[]']").val(names);


}

function check_sub(obj)
{
	var region_id = $(obj).val();
	if($(obj).attr("checked"))
		$(".region_level_"+region_id).find("input[name='region_id[]']").attr("checked",true);
	else
		$(".region_level_"+region_id).find("input[name='region_id[]']").attr("checked",false);
}
function close_pop()
{
	$(".dialog-close").click();
}
function do_submit_opform()
{
	
	$.showConfirm("确定替换当前配置吗？",function(){		
		select_region_ok();
		$.weeboxs.close();	
	});
	
}


