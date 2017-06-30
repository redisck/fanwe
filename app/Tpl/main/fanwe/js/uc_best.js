$(document).ready(function(){	
	$("button.edit_link_btn").bind("click",function(){
		window.location=$(this).attr("act");
	});
	init_screen_size();
	
	$(window).resize(function(){
		if($(window).width()<1280)
		{
			$("#flow_cate").fadeOut();
		}
		else
		{
			$("#flow_cate").fadeIn();
		}
		
		if($(window).width()<1050)
		{
			$(".main_layout").removeClass("wrap_full");
			$(".main_layout").removeClass("wrap_full_w");
			$(".main_layout").addClass("wrap_full");
			$("#pin_layout_box").reposition();

		}
		if($(window).width()>1200)
		{
			$(".main_layout").removeClass("wrap_full");
			$(".main_layout").removeClass("wrap_full_w");
			$(".main_layout").addClass("wrap_full_w");
			$("#pin_layout_box").reposition();
		}
		
		
	});
});

$(function(){
	
		$.fav_btn = function(uid,o){
		var query = new Object();
		query.act = "focus";
		query.uid = uid;
		$.ajax({ 
			url: AJAX_URL,
			data: query,
			dataType: "json",
			success: function(obj){	
				$(o).removeClass("fav_checked");	
				if(obj.tag==1)
				{
					$(o).addClass("fav_checked");
				}
				if(obj.tag==2)
				{
					$(o).removeClass("fav_checked");
				}
				if(obj.tag==3)
				{
					$.showSuccess(obj.html);
				}
				if(obj.tag==4)
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
});
