$(document).ready(function(){
	
	//移上的pin_box响应
	$(".pin_box").live("mouseover",function(){
		$(this).removeClass("pin_box_active");
		$(this).addClass("pin_box_active");
	});
	$(".pin_box").live("mouseout",function(){
		$(this).removeClass("pin_box_active");
	});
	$("#pin_layout_box").init_pin({hSpan:25,wSpan:10,isAnimate:true,speed:300});
	init_load_topic();

	
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
	
	var page = $("#hd_page").val();
	var step = $("#hd_step").val();
	var ajax_wait = $("#ajax_wait").val();
	var step_size = $("#hd_step_size").val();
	var id = $("#user_id").val();
	//滚动到位置+分段加载未结束+ajax未在运行
    if(windheight+scrolltop>=loadheight+50&&parseInt(step)>0&&ajax_wait==0)
    {
    	var query = new Object();
    	query.act = "uc_home_index";
    	query.page = page;
    	query.step = step;
    	query.step_size = step_size;
    	query.id = id;
    	$("#ajax_wait").val(1);  //表示开始加载
    	$("#loading").css("visibility","visible");
    	
    	$.ajax({ 
    		url: AJAX_URL,
    		data:query,
    		type: "POST",
    		dataType: "json",
    		success: function(data){
    			$("#loading").css("visibility","hidden");
    			$.each(data.doms, function(i,dom){    				
					$("#pin_layout_box").pin(dom);
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