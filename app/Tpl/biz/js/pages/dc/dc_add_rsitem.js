$(function(){
	/*时间选择*/
	$(".time_txt").timepicker({timeFormat: 'HH:mm:00',
		stepHour: 1,
		stepMinute: 1});
	//提交数据
	$("form[name='add_rsitem_form']").submit(function(){
		var form = $("form[name='add_rsitem_form']");
		
		if($.trim($("input[name='name']").val()) == ''){
			$.showErr("请输入新增餐桌名称");
			return false;
		}
		
		var query = $(form).serialize();
		var url = $(form).attr("action");
		$.ajax({
			url:url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status==1){
					$.showSuccess(data.info,function(){window.location=data.jump;});
				}else{
					$.showErr(data.info);
					
				}
			}
		});
		
		return false;
	});
	
	$("button.add_time_btn").bind("click",function(){
		var html = '<tr class="time_item">'+
			'<td><input type="hidden" class="ui-textbox" name="rs_time_id[]" value="" /><input class="rs_time ui-textbox time_txt" name="rs_time[]" value="" /></td>'+
			'<td><input type="text" class="total_count ui-textbox"  name="total_count[]" value="1" /></td>'+
			'<td>'+
				'<select class="ui-select  filter_select small" name="t_is_effect[]">'+
					'<option value="1">有效</option>'+
					'<option value="0">无效</option>'+
				'</select>'+
			'<td><a href="javascript:void(0);" onclick="del_time_item(this)" data-id="" class="iconfont">&#xe613;</a></td>'+
		'</tr>';
		$(".rsitem_time_box").append(html);
		init_ui_textbox();
		init_ui_select();
		$(".time_txt").timepicker({timeFormat: 'HH:mm:00',
			stepHour: 1,
			stepMinute: 1});
	});
	
	
});
/**
 * 删除时间配置
 */
function del_time_item(obj){
	var id = $(obj).attr("data-id");
	var row_obj = $(obj).parent().parent();
	if(id>0){
		var query = new Object();
		query.act = "do_del_time_item";
		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status==1){
					$.showSuccess(data.info,function(){$(row_obj).remove();});
				}else{
					$.showErr(data.info);
					
				}
			}
		});
	}else{
		$(row_obj).remove();
	}
}