$(document).ready(function(){
	if($("#rel_nav")[0]){
		
			if($(document).scrollTop() + 10 - $("#rel_nav").offset().top > 0)
			{
				$(".fix-nav").show();
				$("#rel_nav").css("visibility","hidden");
			}
			else
			{
				$(".fix-nav").hide();
				$("#rel_nav").css("visibility","visible");
			}
			
			$(".show-nav").find("a").bind("click",function(){
				var cate_id = $(this).parent().attr("data-category");	
				var cate_box = $("div[data-category='"+cate_id+"']");
				var top = $(cate_box).offset().top - 40;
				//$(window).unbind("scroll");
				$("html,body").animate({scrollTop:top},"fast","swing",function(){reset_class();
					init_gotop();
					$(window).scroll(function(){
						reset_class();					
					});						
					
				});
			});
			
			$(window).scroll(function(){
				reset_class();
			});			
		
	}
	

	
	$("#submit_daren").bind("click",function(){
		daren_submit();
	});
	
	
});
	

function reset_class()
{
	if($.browser.msie && $.browser.version =="6.0")
	{
		$(".fix-nav").css("top",$(document).scrollTop());
		$(".fix-nav").css("width",document.body.clientWidth);
	}
	$("#vote").css("top",$(document).scrollTop()+200);	
	if($(document).scrollTop() + 10 - $("#rel_nav").offset().top > 0)
		{
			
			$(".fix-nav").show();
			$("#rel_nav").css("visibility","hidden");
		}
		else
		{
			$(".fix-nav").hide();
			$("#rel_nav").css("visibility","visible");
		}
		
		var cate_topic_box = $(".cate_topic");
		for(i=0;i<cate_topic_box.length;i++)
		{
			var scrollTop = $(document).scrollTop() + 55; //页面滚动量 加上菜单高
			var current_top = $(cate_topic_box[i]).offset().top;
			if(i<cate_topic_box.length-1)
			var next_top = $(cate_topic_box[i+1]).offset().top;
			else
			{
				next_top = current_top + 400;
			}
			
			if(scrollTop>current_top&&scrollTop<next_top)
			{
					cate_rel_id = $(cate_topic_box[i]).attr("data-category");
					$(".show-nav").find("li").removeClass("on");
					$(".show-nav").find("li[data-category='"+cate_rel_id+"']").addClass("on");
					break;
			}
		}
}


function daren_submit()
{
	if($.trim($("*[name='daren_title']").val())=='')
	{
		$.showErr("请输入达人称号");
		return;
	}
	else if($.trim($("*[name='daren_title']").val()).length>10)
	{
		$.showErr("达人称号太长");
		return;
	}
	if($.trim($("*[name='reason']").val())=='')
	{
		$.showErr("请输入申请理由");
		return;
	}
	
	var query = new Object();
	query.daren_title = $.trim($("*[name='daren_title']").val());
	query.reason = $.trim($("*[name='reason']").val());
	
	var ajaxurl = $("form[name='daren_form']").attr("action");	
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(ajaxobj){
			if(ajaxobj.status==1)
			{
				$.showSuccess("申请成功，请等待管理员审核");
				$("*[name='daren_title']").val("");
				$("*[name='reason']").val("");
			}
			else if(ajaxobj.status==2)  //其他原因
			{
				$.showErr(ajaxobj.info);	
			}
			else
			{
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}
