$(function(){
	$("form[name='event_submit_form']").submit(function(){
		
		var is_err = 0;
		$(".event_submit_panel input").each(function(){
			if($(this).val()==''){
				is_err++;
			}
		});
		if(is_err>0){
			$.showErr("请正确填写报名项");
			return false;
		}
		var query = $(this).serialize();
		var action = $(this).attr("action");
		$.ajax({
			url:action,
			data:query,
			type:"POST",
			dataType:"json",
			success:function(obj){

				if(obj.status)
				{
					$.showSuccess(obj.info,function(){
						if(obj.jump)
							location.href = obj.jump;
						});
				}
				else
				{
					if(obj.info)
					{
						$.showErr(obj.info,function(){
							if(obj.jump)
								location.href = obj.jump;
							});
					}
					else
					{
						if(obj.jump)
							location.href = obj.jump;
					}
					
				}
			}			
		});
		return false;
	});
});