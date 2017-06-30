$(document).ready(function(){
	$("form[name='submit_message']").bind("submit",function(){
		var ajaxurl = $(this).attr("action");
		var query = $(this).serialize();
		$.ajax({
			url:ajaxurl,
			data:query,
			dataType:"json",
			type:"post",
			success:function(obj){
				if(obj.status)
				{
					$.showSuccess(obj.info,function(){
						if(obj.jump)
						{
							location.href = obj.jump;
						}
					});
				}
				else
				{
					$.showErr(obj.info,function(){
						if(obj.jump)
						{
							location.href = obj.jump;
						}
					});
				}
			}
		});
		
		return false;
	});
});