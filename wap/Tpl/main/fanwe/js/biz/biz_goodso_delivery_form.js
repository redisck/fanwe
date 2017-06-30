$(function(){
	
	$("form[name='do_delivery_form']").submit(function(){
		if($(".doi_ids_v:checked").length == 0){
			$.showErr("请选择要发货的商品");
			return false;
		}
		
		if($("input[name='delivery_sn']").val()==''){
			$.showErr("请填写运单号");
			return false;
		}
		var form = $("form[name='do_delivery_form']");
		var query = $(form).serialize(); 
		var ajaxurl = $(form).attr("action");
		$.ajax({
			url:ajaxurl,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data["status"]==1){ 
					$.showSuccess(data.info,function(){
						window.location=data.jump;
							return false;
					});
				}else{
					$.showErr(data.info,function(){
						if(data.jump)
							window.location=data.jump;
						return false;
					});
				}
				return false;
			}
			,error:function(){
				$.showErr("服务器提交错误");
			}
		});	
		
		return false;
	});
	
	$(".deal_box").bind("click",function(){
		var check_box = $(this).find(".doi_ids_v");
		if($(check_box).is(":checked")){
			$(check_box).prop("checked",false);
			$(this).find(".d_check_box i").html('&#xe651;');
		}else{
			$(check_box).prop("checked",true);
			$(this).find(".d_check_box i").html('&#xe652;');
		}
	});
});