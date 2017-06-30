$(function(){
	$("button.is_close_btn").live("click",function(){
		var id = $(this).attr("data-id");
		var obj = $(this).parent();
		
		var query = new Object();
		query.act="set_is_close";
		query.id = id;
		$.ajax({
				url : ajax_url,
				type : "POST",
				data : query,
				dataType : "json",
				success : function(result) {
					if(result.status == 1){
						var txt = '';
						if(result.is_close == 1){
							txt="恢复营业";
						}else{
							txt = "暂停营业";
						}
						var btn_html = '<button class="ui-button is_close_btn" rel="white" type="button" data-id="'+id+'" is_close="'+result.is_close+'">'+txt+'</button>';
						$(obj).html(btn_html);
						init_ui_button();
					}else{
						$.showErr(result.info);
					}
					return false;
				}
			});	
	});
});