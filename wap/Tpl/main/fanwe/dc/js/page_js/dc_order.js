$(document).ready(function(){
	
	   $(".pay_ico").click(function(){
	
		  
	   	    $(this).html("&#xe67a;").removeClass("f_c9cacf").addClass("f_fe4d3d");
		    $(this).parent().siblings().find('.pay_ico').html("&#xe684;").addClass("f_c9cacf").removeClass("f_fe4d3d");
			var pay_this_rel=$(this).attr("rel");
			$("#payment_id").val(pay_this_rel);
			cart_form_sumbit();
	   });
	
	   
	   $("select[name='ecvsn']").bind('change',function(){
		   cart_form_sumbit();
		  	
	   });
	
	   
	   
	   $(".cart_done").live('click',function(){
		   cart_done_sumbit();
		  	
	   });
	   
	   init_payment(payment_id);
	   
	   init_check();
});


function cart_form_sumbit(){
	
	$('#cart_form').submit();
}

function init_payment(payment_id){
	
	
	var obj=$(".pay_ico[rel='"+payment_id+"']");
	$(obj).html("&#xe67a;").removeClass("f_c9cacf").addClass("f_fe4d3d");
	$(obj).parent().siblings().find('.pay_ico').html("&#xe684;").addClass("f_c9cacf").removeClass("f_fe4d3d");

}

function init_check(){
	
	if(is_return==0 && status==0){
		
		alert(info);
	}
}

function cart_done_sumbit(){
	
	var query=$('#cart_form').serialize();
	
	query+='&act=make_order';
	$.ajax({
			url:dc_order_url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){
				if(data.status==1){

					location.href=data.jump;
				}else{
					
					alert(data.info);
				}
			}
	});
	
	
	
	
}