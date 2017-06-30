$(document).ready(function(){
	
	//电子券验证
	$('.dc_biz_verify_but').bind('click',function(){
		if(!$(".dc_biz_verify_content").val())
		{
			alert("请输入电子号码");
		}
		else
		{
		   dc_biz_verify_function($(this));
		}
		
	});
	
});

	
	

//电子券验证
function dc_biz_verify_function(o){
		var query=new Object();
		query.verify_sn=$(".dc_biz_verify_content").val();
        var url=$(o).attr('action-url');
		$.ajax({
				url:url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
						alert(data.info);
						location.href=location.href;
					}else{
						alert(data.info);
					}
				
				}
		});
		
}
