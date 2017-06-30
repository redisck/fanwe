$(function(){
	
	$(".sort_item").bind("blur",function(){
		var obj = $(this);
		var id = $(this).attr("data-id");
		var sort = $(this).val();
		var old_sort = $(this).attr("data-sort");
		var query = new Object();
		query.act = "do_rsitem_sort";
		query.id = id;
		query.sort = sort;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					$(obj).attr("data-sort",sort);
				}else{
					$.showErr(data.info);
					$(obj).val(old_sort);
				}
			}
		});
	});
	
	
	$(".price_item").bind("blur",function(){
		var obj = $(this);
		var id = $(this).attr("data-id");
		var price = $(this).val();
		var old_price = $(this).attr("data-price");
		var query = new Object();
		query.act = "do_rsitem_price";
		query.id = id;
		query.price = price;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					$(obj).attr("data-price",price);
				}else{
					$.showErr(data.info);
					$(obj).val(old_price);
				}
			}
		});
	});
	
	
	
	$(".is_effect_btn").bind("click",function(){
		var obj = $(this);
		var id = $(this).attr("data-id");
		var query = new Object();
		query.act = "do_rsitem_status";
		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					if(data.is_effect ==1){
						$(obj).html("&#xe612;");
					}else{
						$(obj).html("&#xe60b");
					}
				}else{
					$.showErr(data.info);
				}
			}
		});
	});
});

/**
 * 删除餐桌
 */
function del_rsitem(obj){
	var id = $(obj).attr("data-id");
	var row_obj = $(obj).parent().parent();
	$.showConfirm("确定删除吗?<br/>同时会删除餐桌的时间配置！",function(){
		if(id>0){
			var query = new Object();
			query.act = "do_del_rsitem";
			query.id = id;
			$.ajax({
				url:ajax_url,
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
		}else{
			$(row_obj).remove();
		}
	});
	
}
