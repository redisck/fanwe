$(document).ready(function(){
	
	
	
	   $(".choose_consignee").click(function(){
	   	    $(this).html("&#xe67a;").removeClass("f_c9cacf").addClass("f_fe4d3d");
		    $(this).parent().siblings().find('.choose_consignee').html("&#xe684;").addClass("f_c9cacf").removeClass("f_fe4d3d");
		    //如果从购物车进来的，返回购物车页面
		    if(from=='cart'){
		    	var consignee_id=$(this).attr('consignee_id');
		    	$("input[name='consignee_id']").val(consignee_id);
		    	$('#cart_form').submit();
		    }
			
	   });
	   
	   
	   $(".del_consignee").click(function(){
		   del_consignee($(this));
			
	   });
	   
	   $('#return_cart').bind('click',function(){
		   $('#cart_form').submit();
		   
	   });
	   

});


function del_consignee(o){
	var query=new Object();
	var consignee_id=$(o).attr('consignee_id');
	query.id=consignee_id;
	query.act='del';
	$.ajax({
			url:dc_consignee_url,
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



