$(document).ready(function(){
	
	   $(".pay_ico").click(function(){
			var pay_this_rel=$(this).attr("rel");
			if($(this).hasClass('y')){
				$("input[name='payment']").val(pay_this_rel);
				$("input[name='all_account_money']").val(0);
			}else{
				$("input[name='payment']").val(0);
				$("input[name='all_account_money']").val(1);
			}
			
			cart_form_sumbit();
	   });
	   
	   $('.pay_account').bind('click',function(){

			if($(this).hasClass('y')){
				$("input[name='all_account_money']").val(1);
			}else{
				$("input[name='all_account_money']").val(0);
			}
			cart_form_sumbit();
	   });
	   
	   $('#pay_button').bind('click',function(){
		   cart_form_pay();
	   });
	

});

function cart_form_sumbit(){
	
	$('#cart_form').submit();
	
}

function cart_form_pay(){
	
	var query=$('#cart_form').serialize();
	query+='&act=order_done';

	$.ajax({
		url:dc_order_url,
		data:query,
		type:'post',
		dataType:'json',
		success:function(data){

			if(data.status==1){
				location.href=data.jump;
			}else{
				$.showErr(data.info,0,data.jump);
			}
		}
	});
	
	
}


