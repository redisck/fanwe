$(document).ready(function(){
	
	$(".modify_submit").bind("click",function(){
		var EVENT_ID = $(this).attr("event_id");
		load_submit(EVENT_ID);
	});
	
	$(".send_event").bind("click",function(){
		var dom = $(this);
		$.ajax({
			url:$(dom).attr("action"),
			type:"POST",
			dataType:"json",
			success:function(obj){
				if(obj.status==1000)
				{
					ajax_login();
				}
				else if(obj.status==1)
				{
					IS_RUN_CRON = 1;
					$.showSuccess(obj.info,function(){
						location.reload();
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
	});
	
	
	//绑定查看报名项事件
	$(".view_submit").hover(function(){
		var id = "view_submit_"+$(this).attr("event_id");
		$("#"+id).stopTime();
		var dom = $(this);
		if($("#"+id).length>0)
		{
			$("#"+id).show();
		}
		else
		{
			var html = "<div id='"+id+"' class='view_submit_pop'><div class='loading'></div></div>";
			var box = $(html);
			$("body").append(box);
			$(box).css({"position":"absolute","left":$(dom).position().left-80,"top":$(dom).position().top+20,"z-index":10});
			$.ajax({
				url:$(dom).attr("action"),
				type:"POST",
				dataType:"json",
				success:function(obj){
					if(obj.status)
					{
						$(box).html(obj.html);
					}
					else
					{
						$(box).remove();
					}
				}
			});
		}
		$("#"+id).hover(function(){
			$("#"+id).stopTime();
			$("#"+id).show();
		},function(){
			$("#"+id).oneTime(300,function(){
				$("#"+id).hide();
			});
		});
	},function(){
		var id = "view_submit_"+$(this).attr("event_id");
		if($("#"+id).length>0)
		{
			$("#"+id).oneTime(300,function(){
				$("#"+id).hide();
			});
		}
		
	});
});



function init_event_form()
{
	$("form[name='event_submit_form']").bind("submit",function(){
		var url = $(this).attr("action");
		var query = $(this).serialize();
		$.ajax({
			url:url,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status==1000)
				{
					$.weeboxs.close("wee_event_submit");
					ajax_login();
				}
				else if(obj.status)
				{
					$.showSuccess(obj.info,function(){
						location.reload();
					});
				}
				else
				{
					$.showErr(obj.info,function(){
						if(obj.jump&&obj.jump!="")
						{
							location.href = obj.jump;
						}
					});
				}
			}
		});
		
		return false;
	});
}

function load_submit(EVENT_ID)
{
	var query = new Object();
	query.id = EVENT_ID;
	query.act = "load_event_submit";
	$.ajax({
		url:AJAX_URL,
		data:query,
		dataType:"json",
		type:"POST",
		success:function(obj){
			if(obj.status==1000)
			{
				ajax_login(function(){
					location.reload();
				});
			}
			else if(obj.status==1)
			{
				//弹出报名信息
				$.weeboxs.open(obj.html, {boxid:"wee_event_submit",position:"fixed", showButton:false,title:"修改报名信息",width:500,type:'wee',onopen:function(){
					init_ui_button();
					init_ui_select();
					init_event_form();
				}});	
			}
			else
			{
				$.showErr(obj.info,function(){
					if(obj.jump&&obj.jump!="")
					{
						location.href = obj.jump;
					}
				});
			}
		}
	});
}