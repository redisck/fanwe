$(function(){
	/*时间选择*/
	$(".time_txt").timepicker(
			{altFieldTimeOnly: false,
		altTimeFormat: "h:m"});
	
	$("form[name='dc_set_form']").bind("submit",function(){
		var form = $("form[name='dc_set_form']");
		var check_err = 0;
		//验证表单
		//营业时间验证
		$(".open_time_tiem").each(function(i){
			var obj = $(this);
			var t_count = $(obj).find(".time_txt").length;
			var t_v_count = 0;
			for(var i=0;i<($(obj).find(".time_txt").length);i++){
				if($(obj).find(".time_txt").eq(i).val()){
					t_v_count++;
				}
			}
			if(t_v_count>=1 && t_v_count != t_count){
				$.showErr("请正确设置营业时间");
				check_err=1;
				return false;
			}
			
		});

		
		//配送信息验证
		$(".delivery_item").each(function(i){
			var obj = $(this);
			var d_count = $(obj).find("input").length;
			var d_v_count = 0;
			for(var i=0;i<($(obj).find("input").length);i++){
				if($(obj).find("input").eq(i).val()){
					d_v_count++;
				}
			}

			if(d_v_count>=1 && d_v_count != d_count){
				$.showErr("请正确设置配送信息");
				check_err=1;
				return false;
			}
		});
		if(check_err!=1){
			var query = $(form).serialize();
			var url = $(form).attr("action");
			$.ajax({
					url : url,
					type : "POST",
					data : query,
					dataType : "json",
					success : function(result) {
						if(result.status == 1){
							$.showSuccess(result.info,function(){window.location= result.jump;});
						}else{
							$.showErr(result.info);
						}
						
						return false;
					}
				});	
		}
		
		return false;
	});
});
/**
 * 添加营业时间
 */
function add_open_time(){
	var html = '<div class="open_time_tiem">'+
		'<span style="padding-left: 50px;">从&nbsp;<input class="time_txt " style="width:50px;" name="op_begin_time[]"/>&nbsp;至&nbsp;'+
		'<input class="time_txt" style="width:50px;" name="op_end_time[]"/>&nbsp;时</span> '+
		'&nbsp;&nbsp;<a href="javascript:void(0);" onclick="remove_open_time(this);" style="text-decoration:none;">[ - ]</a>'+
		'<div class="blank5"></div></div>';
	$(".open_time_box").append(html);
	$(".time_txt").timepicker({format: "H:i"});
}

function remove_open_time(obj){
	$(obj).parent().remove();
}

function add_delivery_row(){
	var html = '<div class="delivery_item"><span style="padding-left: 50px;"><input style="width:50px;" name="scale[]"/>&nbsp;公里之内'
		+'&nbsp;&nbsp;起送费&nbsp;<input  style="width:50px;" name="start_price[]"/>&nbsp;元'
		+'&nbsp;&nbsp;配送费&nbsp;<input  style="width:50px;" name="delivery_price[]"/>&nbsp;元</span>'
		+'&nbsp;<a href="javascript:void(0);" onclick="remove_delivery_row(this);" style="text-decoration:none;">[ - ]</a>'
		+'<div class="blank5"></div></div>';
	$(".delivery_info_box").append(html);
}
function remove_delivery_row(obj){
	$(obj).parent().remove();
}
