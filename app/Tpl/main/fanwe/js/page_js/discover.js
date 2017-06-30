$(document).ready(function(){
	init_flow_cate();
	
	//移上的pin_box响应
	$(".pin_box").live("mouseover",function(){
		$(this).removeClass("pin_box_active");
		$(this).addClass("pin_box_active");
	});
	$(".pin_box").live("mouseout",function(){
		$(this).removeClass("pin_box_active");
	});
	
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
		$("#discover_pin").reposition();

	}
	if($(window).width()>1200)
	{
		$(".main_layout").removeClass("wrap_full");
		$(".main_layout").removeClass("wrap_full_w");
		$(".main_layout").addClass("wrap_full_w");
		$("#discover_pin").reposition();
	}
	
	var first_height = $("#discover_tags").height();
	$("#discover_pin").init_pin({pin_col_init_height:[first_height],hSpan:25,wSpan:10,isAnimate:true,speed:300});
	init_load_topic();

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
			$("#discover_pin").reposition();

		}
		if($(window).width()>1200)
		{
			$(".main_layout").removeClass("wrap_full");
			$(".main_layout").removeClass("wrap_full_w");
			$(".main_layout").addClass("wrap_full_w");
			$("#discover_pin").reposition();
		}
		
		
	});
});
function init_load_topic()
{
	$(".pages").hide();
	$("#hd_step").val(1);
	$("#ajax_wait").val(0);
	$(window).bind("scroll", function(e){
		load_topic();
	});
	load_topic();
}

function load_topic()
{
	var scrolltop = $(window).scrollTop();
	var loadheight = $("#loading").offset().top;
	var windheight = $(window).height();
	
	var cid = $("#hd_cid").val();
	var tag = $("#hd_tag").val();
	var page = $("#hd_page").val();
	var step = $("#hd_step").val();
	var ajax_wait = $("#ajax_wait").val();
	var step_size = $("#hd_step_size").val();
	//滚动到位置+分段加载未结束+ajax未在运行
    if(windheight+scrolltop>=loadheight+50&&parseInt(step)>0&&ajax_wait==0)
    {
    	var query = new Object();
    	query.act = "discover";
    	query.cid = cid;
    	query.tag = tag;
    	query.page = page;
    	query.step = step;
    	query.step_size = step_size;
    	$("#ajax_wait").val(1);  //表示开始加载
    	$("#loading").css("visibility","visible");
    	
    	$.ajax({ 
    		url: AJAX_URL,
    		data:query,
    		type: "POST",
    		dataType: "json",
    		success: function(data){
    			$("#loading").css("visibility","hidden");
//    			$("body").append(data.sql+"<br />");
    			$.each(data.doms, function(i,dom){
					$("#discover_pin").pin(dom);
    			});
    			if(data.status)  //继续加载
    			{    				
	    			$("#hd_step").val(data.step);    			
    				$("#ajax_wait").val(0);    		
       			}
    			else //加载结束
    			{    			
    				$("#ajax_wait").val(0); 
    				$("#hd_step").val(0);
    				$(".pages").show();
    			}
    			
    			$(".dialog-mask").css("height",$(document).height());
    		},
    		error:function(ajaxobj)
    		{
//    			if(ajaxobj.responseText!='')
//    			alert(ajaxobj.responseText);
    		}
    	});	

    	
    }	
}



function init_flow_cate()
{
	$("#flow_cate").css("top",$(document).scrollTop());	
	$(window).scroll(function(){
		$("#flow_cate").css("top",$(document).scrollTop());			
	});	
	
}