$(document).ready(function(){

	$('.save_lid').bind('click',function(){
		
		save_lid($(this));
	});
	
	
	
	$("#open_time_info_add").click(function(){

		get_open_time_html();
		
	});
	
	
	$('.del_row').bind('click',function(){
			$(this).parent().remove();
		});

//	$(".open_time_info_ul li .del_row").click(function(){
//		//alert();
//		$(this).parent().remove();
//	});
});



function save_lid(o){
	
	var query=$('#cart_form').serialize();
	query+='&act=save';

	$.ajax({
			url:dc_biz_url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){
				if(data.status==1){

					location.href=location.href;
				}else{
					
					alert(data.info);
				}
			}
	});
		
}

function get_open_time_html(){
	var query=new Object();
	query.act='get_open_time_html';
	query.ajax=1;
	$.ajax({
			url:dc_biz_url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){
				$(".open_time_info_ul").append(data);
				$('.del_row').bind('click',function(){
					$(this).parent().remove();
				});
			}
	});
	
}

