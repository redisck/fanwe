function fav_topic(id)
{
	var query = new Object();
	query.act = "do_fav_topic";
	query.id = id;
	$.ajax({ 
		url: AJAX_URL,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status)
			{
				$(".topic_fav_"+id).html(parseInt($(".topic_fav_"+id+":first").html())+1);
				$.showSuccess(obj.info);
			}
			else
			{
				var query = new Object();
				query.act = "check_login_status";
				
				$.ajax({ 
					url: AJAX_URL,
					dataType: "json",
					data:query,
					type: "POST",
					success: function(ajaxobj){
						if(ajaxobj.status==0)
						{
							ajax_login();
						}
						else
						{
							$.showErr(obj.info);
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});	
				
			}
//			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}


function relay_topic(id)
{
	var query  = new Object();
	query.act = "do_relay_topic";
	query.id = id;
	$.ajax({ 
		url: AJAX_URL,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status)
			{
				$(".topic_relay_"+id).html(parseInt($(".topic_relay_"+id+":first").html())+1);
				$.showSuccess(obj.info);
			}
			else
			{
				var query = new Object();
				query.act = "check_login_status";
				
				$.ajax({ 
					url: AJAX_URL,
					dataType: "json",
					data:query,
					type: "POST",
					success: function(ajaxobj){
						if(ajaxobj.status==0)
						{
							ajax_login();
						}
						else
						{
							$.showErr(obj.info);
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});	
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}