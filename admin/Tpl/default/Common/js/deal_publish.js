function refused_apply(id)
{
		
		if(id>0)
		{
			if(confirm("确定要拒绝申请吗？")){
				var query = new Object();
				query.id = id;
				query.ajax = 1;
				$.ajax({ 
					url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=refused_apply", 
					data: query,
					dataType: "json",
					success: function(obj){
						if(obj.status){
							alert(obj.info);
							window.location = window.location.href;
						}else{
							alert(obj.info);
						}
					}
				});
			}
			
		}
		else
		{
			alert("参数错误");
		}
}

function downline(id){
	if(id>0)
	{
		if(confirm("确定要同意下架吗？")){
			var query = new Object();
			query.id = id;
			query.ajax = 1;
			$.ajax({ 
				url: ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=downline", 
				data: query,
				dataType: "json",
				success: function(obj){
					if(obj.status){
						alert(obj.info);
						window.location = window.location.href;
					}else{
						alert(obj.info);
					}
				}
			});
		}
		
	}
	else
	{
		alert("参数错误");
	}
}