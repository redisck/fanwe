$(document).ready(function(){	
		
		$(".del").bind("click",function(){
			del_url=$(this).attr('url');
			$.showConfirm("确定要删除吗？",function(){		
				collect_del(del_url);
			});
		});
});




function collect_del(url)
{
	
	$.ajax({ 
		url: url,
		type: "GET",
		dataType: "json",
		success: function(data){
			if(data.status==0)
			{
				$.showErr(data.info);				
			}
			else if(data.status==1)
			{
				$.showSuccess("删除成功",function(){location.reload();});
			}
			else if(data.status==2)
			{
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{			
			alert('删除失败');
		}
	});	
}

