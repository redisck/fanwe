$(document).ready(function(){
	init_main_roll();
	init_index_cate();
	init_flow_cate();
	init_screen_size();
});


//商品移上
function init_index_cate()
{
	$(".index_cate .tuan_list .tuan_item").hover(function(){
		show_scan_box(this);
		$(this).addClass("tuan_item_border_hover");	
		var fx_queue = $(this).find("a.quan").queue("fx");
		if(fx_queue!=null)
		{
			while(fx_queue.length>1)
			{
				fx_queue.pop();
			}
		}
		$(this).find("a.quan").slideDown("fast");
	},function(){
		hide_scan_box(this);
		$(this).removeClass("tuan_item_border_hover");
		$(this).find("a.quan").slideUp("fast");
	});
}
//商品移上

var item_idx = 0; //初始化
function init_main_roll()
{
	var item_width = 1920; //单个元素宽度
	var box = $("#main_roll #wrap #full_outer #scroll_outer ul");
	var count = $(box).find("li").length;	//计算个数
	var offset_left = 0-item_idx*item_width;
	$(box).css({"width":count*item_width,"left":offset_left});
	$(box).find("li").each(function(i,o){
		$(o).attr("rel",i);
	});
	
	
	//初始化滚动操作钮
	var ctls = $("<ul class='op'></ul>");
	for(i=1;i<=count;i++)
	{
		var li = $("<li rel='"+(i-1)+"'>"+i+"</li>");
		ctls.append(li);
	}
	$("#main_roll").append(ctls);	
	$(ctls).find("li[rel='"+item_idx+"']").addClass("current");
	//绑定事件
	$(ctls).find("li").bind("click",function(){	
		item_idx = $(this).attr("rel");
		roll_image(item_idx);
	});
	$("#main_roll").hover(function(){
		$("#main_roll").stopTime();
	},function(){
		auto_roll_image();
	});
	
	//绑定计时器
	auto_roll_image();
}


function auto_roll_image()
{
	var box = $("#main_roll #wrap #full_outer #scroll_outer ul");
	var count = $(box).find("li").length;	//计算个数
	$("#main_roll").everyTime(3000,function(){
		item_idx++;
		if(item_idx>=count)item_idx=0;
		roll_image(item_idx);
	});
}

//将图片移动至指定的位置 idx 0-最后一张
function roll_image(idx)
{
	var item_width = 1920; //单个元素宽度
	var box = $("#main_roll #wrap #full_outer #scroll_outer ul");
	var count = $(box).find("li").length;	//计算个数
	
	if(idx<0)idx=0;
	if(idx>count)idx=count-1;
	
	$("#main_roll").find(".op li").removeClass("current");
	$("#main_roll").find(".op li[rel='"+idx+"']").addClass("current");
	
	var left = 0-idx*item_width;	
	$(box).animate({ 
		    left: left
		  }, 300 );	
}

//浮动的团购分类
var is_auto_scroll = false;
function init_flow_cate()
{
	$("#flow_cate li").each(function(i,o){
		
		if($(o).attr("bg")&&$(o).attr("bg")!="")
		{
			$(o).hover(function(){
				$(this).css("background",$(this).attr("bg"));
				$(this).css("color","#ffffff");
			},function(){
				if($.trim($(this).attr("class"))!="current")
				{
					$(this).css("background","");
					$(this).css("color","");
				}				
			});
		}		
	});

	if($.browser.msie && $.browser.version =="6.0")
	{
		$("#flow_cate").css("top",$(document).scrollTop()+20);	
	}	
	else
	{
		var left = $("#flow_cate_outer").offset().left - 60;
		var top = $("#flow_cate_outer").offset().top + 20;
		$("#flow_cate").css({"left":left ,"top":top });	
	}
	
	$(window).scroll(function(){
		if($(window).width()>1280&&$(document).scrollTop()>0)
			$("#flow_cate").fadeIn();			
		else
			$("#flow_cate").fadeOut();
		
		if($.browser.msie && $.browser.version =="6.0")
		$("#flow_cate").css("top",$(document).scrollTop()+20);			
		if(!is_auto_scroll)
		{
			reset_cate_class();
		}
			
		//end
	});	
	$(window).resize(function(){
		if(!$.browser.msie || $.browser.version !="6.0")
		{
			var left = $("#flow_cate_outer").offset().left - 60;
			var top = $("#flow_cate_outer").offset().top + 20;
			$("#flow_cate").css({"left":left ,"top":top });	
		}
		
		if($(window).width()<1280)
		{
			$("#flow_cate").fadeOut();
		}
		else
		{
			$("#flow_cate").fadeIn();
		}
	});
	
	$("#flow_cate").find("li").bind("click",function(){
		is_auto_scroll = true;
		$("#flow_cate").find("li").removeClass("current");
		$("#flow_cate").find("li").css("background","");
		$("#flow_cate").find("li").css("color","");
		
		$(this).addClass("current");
		$(this).css("background",$(this).attr("bg"));
		$(this).css("color","#ffffff");
		
		
		var cate_id = $(this).attr("rel");	
		var cate_box = $(".index_cate[rel='"+cate_id+"']");
		
		var flow_top = $("#flow_cate_outer").offset().top + 20 -5;
		
		var top = $(cate_box).offset().top - flow_top;
		$("html,body").animate({scrollTop:top},"fast","swing",function(){
			is_auto_scroll = false;
		});
		
		
	});
}

function reset_cate_class()
{
	//定位菜单当前样式
	$("#flow_cate").find("li").removeClass("current");
	$("#flow_cate").find("li").css("background","");
	$("#flow_cate").find("li").css("color","");
	var scrollTop = $("#flow_cate").offset().top;
	var cate_box = $(".index_cate");
	for(i=0;i<cate_box.length;i++)
	{
		var current_top = $(cate_box[i]).offset().top;
		if(i<cate_box.length-1)
		{
			var next_top = $(cate_box[i+1]).offset().top;
		}
		else
		{
			next_top = $(document).height();
		}
		
		if(scrollTop>current_top&&scrollTop<next_top)
		{
			cate_rel_id = $(cate_box[i]).attr("rel");
			var current_li = $("#flow_cate").find("li[rel='"+cate_rel_id+"']");
			current_li.addClass("current");			
			current_li.css("background",current_li.attr("bg"));
			current_li.css("color","#ffffff");
			break;
		}
	}	
}