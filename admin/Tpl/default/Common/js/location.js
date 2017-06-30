$(document).ready(function(){
        $("table.form#location input[type='submit']").bind('click',function(){
        	if($.trim($("input[name='name']").val())==''){
        		alert('请填写名称');
        		$("input[name='name']").focus();
        		return false;
        	}
        	
        	if($("select[name='city_id']").val()==0){
        		alert('请选择城市');
        		$("select[name='city_id']").focus();
        		return false;
        	}
        	
        	if($.trim($("input[name='xpoint']").val())=='' || $.trim($("input[name='ypoint']").val())==''){
        		alert('请标注地图地位');
        		$("input[name='api_address']").focus();
        		return false;
        	}
			
			if($("input[name='is_dc']").is(':checked') && !$("input[name='dc_online_pay']").is(':checked') && !$("input[name='dc_allow_cod']").is(':checked')){
				alert('请选择支付方式');
				return false;
			}
        
        });
		
     });

function init_sub_cate()
{
	var cate_id = $("select[name='deal_cate_id']").val();
	var id = $("input[name='id']").val();
	var edit_type = $("input[name='edit_type']").val();
	if(cate_id>0)
	{
		
		$.ajax({ 
			url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_sub_cate&cate_id="+cate_id+"&id="+id+"&edit_type="+edit_type, 
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				if(obj.status)
				{
					$("#sub_cate_box").show();
					$("#sub_cate_box").find(".item_input").html(obj.data);
				}
				else
				{
					$("#sub_cate_box").hide();
				}
				
			},
			error:function(ajaxobj)
			{
				if(ajaxobj.responseText!='')
				alert(ajaxobj.responseText);
			}
		
		});
	}
	else
	{
		$("#sub_cate_box").hide();
		$("#sub_cate_box").find(".item_input").html("");
	}
}


function init_tag_list(){
	var cate_id = $("select[name='deal_cate_id']").val();
	var id = $("input[name='id']").val();
	if(cate_id>0)
	{
		$.ajax({ 
	            url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=load_tag_list&cate_id="+cate_id+"&id="+id, 
	            data: "ajax=1",
	            success: function(obj){
					$("#tag_group_preset").find(".item_input").html(obj);
					if(obj!='')
	                	$("#tag_group_preset").show();
					else
						$("#tag_group_preset").hide();	
	            }
	        });
	}
	else
	{
		$("#tag_group_preset").hide();
		$("#tag_group_preset").find(".item_input").html("");
	}
}

$(document).ready(function(){
	$("select[name='deal_cate_id']").bind("change",function(){
		init_sub_cate();
		init_tag_list();
	});
	init_sub_cate();
	init_tag_list();
	
	$("input[name='is_dc']").live("click",function(){
		if($(this).attr("checked")==true){
			$("tr#takeaway_box").each(function(){
			$(this).show();
			});
		}
		else
		{
			$("tr#takeaway_box").each(function(){
			$(this).hide();
			});
		}
	});

		
	$("select[name='balance_type']").change(function(){
		
			if($(this).val() == 1)
				$(".b_number").html("元，不能超过10元");
			else
				$(".b_number").html("例：网站收取每笔订单20%做为提成，填写 0.2，不能超过1");
		});
});

function promote(id,type,location_id,supplier_id)
{	
	$.weeboxs.open(ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=promote&"+type+"="+id+"&location_id="+location_id+"&supplier_id="+supplier_id, {contentType:'ajax',showButton:false,title:"促销规则设置",width:700,height:430});
}