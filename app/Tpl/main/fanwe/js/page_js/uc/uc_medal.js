$(document).ready(function(){
	
	$(".medal_item").find("a").bind("click",function(){
		
		get_medal($(this).attr("rel"));
	});


});


function get_medal(url)
{
	$.weeboxs.open(url, {contentType:'ajax',showButton:false,title:"获取勋章",width:400,type:'wee',onopen:function(){init_ui_button();}});	
}

function imp_get_medal(url)
{
	
	$.ajax({ 
		url: url,
		type: "POST",
		dataType: "json",
		success: function(data){
			if(data.status==0)
			{
				$.showErr(data.info);
				
			}
			else if(data.status==1)
			{
				$.showSuccess("领取成功",function(){location.reload();});
			}
			else
			{
				$.showErr(data.info);
			}
		},
		error:function(ajaxobj)
		{			
			alert(ajaxobj.responseText);
		}
	});	
}

function close_pop()
{
	$(".dialog-close").click();
}