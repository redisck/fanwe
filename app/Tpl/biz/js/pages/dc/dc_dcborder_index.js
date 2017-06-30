$(function(){

init_ui_button();
$('button.balance_button').bind('click',function(){
			var sid=parseInt($(this).attr('data-i'));
			var query = new Object();
			query.sid = sid;
			query.act = "dc_supplier_balance";
			$.ajax({
				url:DC_AJAX_URL,
				type:"POST",
				dataType:"json",
				data:query,
				success:function(obj){
					if(obj.status=1){
							$.showSuccess(obj.info,function(){
								location.reload();
							});
					}
					else
					{
					
						$.showErr(obj.info);
					}
				
				}
				});

});

});

