$(document).ready(function(){
	init_main_roll();
	init_side_roll();
	init_news();
	init_allarea();
	init_index_cate();
	
	init_supplier_roll();
	init_youhui_roll();
	init_flow_cate();
	init_screen_size();
	
	app_download();
});


//图片滚动
var item_idx = 0; //初始化
function init_main_roll()
{
	var item_width = 760; //单个元素宽度
	var box = $("#main_roll  ul");
	var count = $(box).find("li").length;	//计算个数
	var offset_left = 0-item_idx*item_width;
	$(box).css({"width":count*item_width*2,"left":offset_left});
	$(box).find("li").each(function(i,o){
		$(o).attr("rel",i);
	});
	$(box).append($(box).html());
	
	
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
		if($("#main_roll ul.roll").queue("fx")==null||$("#main_roll ul.roll").queue("fx").length==0)
		{
			item_idx = $(this).attr("rel");
			roll_image(item_idx,"#main_roll",item_width);
		}
		
	});
	$("#main_roll").hover(function(){
		$("#main_roll").stopTime();
	},function(){
		auto_roll_image("#main_roll",item_width);
	});
	
	//绑定计时器
	auto_roll_image("#main_roll",item_width);
}
function auto_roll_image(box_id,item_width)
{
	var box = $(box_id+" ul.roll");
	var count = $(box).find("li").length/2;	//计算个数
	$(box_id).everyTime(5000,function(){
		item_idx++;
		if(item_idx>=count)item_idx=0;
		roll_image(item_idx,box_id,item_width);
	});
}

//图片滚动

//右侧的小图滚动
var side_idx = 0;
function init_side_roll()
{
	
	var item_width = 200; //单个元素宽度
	var box = $("#side_roll  ul");
	var count = $(box).find("li").length;	//计算个数
	var offset_left = 0-side_idx*item_width;
	$(box).css({"width":count*item_width*2,"left":offset_left});
	$(box).find("li").each(function(i,o){
		$(o).attr("rel",i);
	});
	$(box).append($(box).html());
	
	//初始化滚动操作钮
	var ctls = $("<ul class='op'></ul>");
	for(i=1;i<=count;i++)
	{
		var li = $("<li rel='"+(i-1)+"'>"+i+"</li>");
		ctls.append(li);
	}
	$("#side_roll").append(ctls);	
	$(ctls).find("li[rel='"+side_idx+"']").addClass("current");
	//绑定事件
	$(ctls).find("li").bind("click",function(){	
		if($("#side_roll ul.roll").queue("fx")==null||$("#side_roll ul.roll").queue("fx").length==0)
		{
			side_idx = $(this).attr("rel");
			roll_image(side_idx,"#side_roll",item_width);
		}
	});
	$("#side_roll").hover(function(){
		$("#side_roll").stopTime();
		$(this).find(".t_left").animate({ 
		    left: 0
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: 0
		  }, { duration: 100,queue:false });
	},function(){
		auto_side_roll_image("#side_roll",item_width);
		$(this).find(".t_left").animate({ 
		    left: -25
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: -25
		  },  { duration: 100,queue:false });
	});
	
	$("#side_roll").find(".t_left").bind("click",function(){
		if($("#side_roll ul.roll").queue("fx")==null||$("#side_roll ul.roll").queue("fx").length==0)
		{
			side_idx--;
			if(side_idx<0)side_idx=count-1;
			if(count==2)
				roll_image_keep_direct(side_idx,"#side_roll",item_width,0);
			else
				roll_image(side_idx,"#side_roll",item_width);
		}
	});
	
	$("#side_roll").find(".t_right").bind("click",function(){
		if($("#side_roll ul.roll").queue("fx")==null||$("#side_roll ul.roll").queue("fx").length==0)
		{
			side_idx++;
			if(side_idx>=count)side_idx=0;
			if(count==2)
				roll_image_keep_direct(side_idx,"#side_roll",item_width,1);
			else
				roll_image(side_idx,"#side_roll",item_width);
		}
		
	});
	
	//绑定计时器
	auto_side_roll_image("#side_roll",item_width);

}
function auto_side_roll_image(box_id,item_width)
{
	var box = $(box_id+" ul.roll");
	var count = $(box).find("li").length/2;	//计算个数
	$(box_id).everyTime(5000,function(){
		side_idx++;
		if(side_idx>=count)side_idx=0;
		roll_image(side_idx,box_id,item_width);
	});
}
//右侧小图滚动


//名店滚动
var supplier_idx = 0;
function init_supplier_roll()
{	
	var item_width = 968; //单个元素宽度
	var box = $("#supplier_roll  ul");
	var count = $(box).find("li").length;	//计算个数
	var spanCount = Math.ceil(count/4);
	
	var offset_left = 0-supplier_idx*item_width;
	$(box).css({"width":spanCount*item_width,"left":offset_left});
	$(box).find("li").each(function(i,o){
		$(o).attr("rel",i);
	});

	$("#supplier_roll ul.roll li").hover(function(){
		show_scan_box(this);
		$(this).addClass("current");
	},function(){
		hide_scan_box(this);
		$(this).removeClass("current");
	});
	
	$("#supplier_roll").hover(function(){		
		$("#supplier_roll").stopTime();
		$(this).find(".t_left").animate({ 
		    left: 0
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: 0
		  }, { duration: 100,queue:false });
	},function(){
		$(this).find(".t_left").animate({ 
		    left: -40
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: -40
		  },  { duration: 100,queue:false });
	});
	
	$("#supplier_roll").find(".t_left").bind("click",function(){
		supplier_idx--;
		if(supplier_idx<0)supplier_idx=spanCount-1;
		roll_span(supplier_idx,"#supplier_roll",item_width);
	});
	$("#supplier_roll").find(".t_right").bind("click",function(){
		supplier_idx++;
		if(supplier_idx>=spanCount)supplier_idx=0;
		roll_span(supplier_idx,"#supplier_roll",item_width);
	});
}
//名店滚动

//优惠滚动
var youhui_idx = 0;
function init_youhui_roll()
{	
	var item_width = 968; //单个元素宽度
	var box = $("#youhui_roll  ul");
	var count = $(box).find("li").length;	//计算个数
	var spanCount = Math.ceil(count/4);
	
	var offset_left = 0-youhui_idx*item_width;
	$(box).css({"width":spanCount*item_width,"left":offset_left});
	$(box).find("li").each(function(i,o){
		$(o).attr("rel",i);
	});

	$("#youhui_roll ul.roll li").hover(function(){
		show_scan_box(this);
		$(this).addClass("current");
	},function(){
		hide_scan_box(this);
		$(this).removeClass("current");
	});
	
	$("#youhui_roll").hover(function(){
		$("#youhui_roll").stopTime();
		$(this).find(".t_left").animate({ 
		    left: 0
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: 0
		  }, { duration: 100,queue:false });
	},function(){
		$(this).find(".t_left").animate({ 
		    left: -40
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: -40
		  },  { duration: 100,queue:false });
	});
	
	$("#youhui_roll").find(".t_left").bind("click",function(){
		youhui_idx--;
		if(youhui_idx<0)youhui_idx=spanCount-1;
		roll_span(youhui_idx,"#youhui_roll",item_width);
	});
	$("#youhui_roll").find(".t_right").bind("click",function(){
		youhui_idx++;
		if(youhui_idx>=spanCount)youhui_idx=0;
		roll_span(youhui_idx,"#youhui_roll",item_width);
	});
}
//优惠滚动

//封装的横向区域滚动
function roll_span(idx,box_id,item_width)
{
	var box = $(box_id+" ul.roll");
	var count = $(box).find("li").length;	//计算个数
	var spanCount = Math.ceil(count/4);
	
	var left = 0-idx*item_width;	
	$(box).animate({ 
		    left: left
		  }, {
			  "duration":200
		  } );	
}
//横向区域滚动

//横向滚动封装
//将图片移动至指定的位置 idx 0-最后一张
function roll_image(idx,box_id,item_width)
{
	var box = $(box_id+" ul.roll");
	var count = $(box).find("li").length/2;	//计算个数
	
	if(idx<0)idx=0;
	if(idx>count)idx=count-1;
	
	var currentIdx = $(box_id).find(".op li.current").attr("rel");

	
	$(box_id).find(".op li").removeClass("current");
	$(box_id).find(".op li[rel='"+idx+"']").addClass("current");
	
	if(idx==0&&currentIdx==count-1)
	{
		var left = 0-count*item_width;	
		$(box).animate({ 
			    left: left
			  }, {
				  "duration":300,
				  "complete":function(){					
					 $(box).css("left",0);						
				  }
			  });	
	}
	else if(idx==count-1&&currentIdx==0&&count>2)
	{
		$(box).css("left",0-count*item_width);
		var left = 0-idx*item_width;	
		$(box).animate({ 
			    left: left
			  }, {
				  "duration":300
			  } );		
	}
	else
	{
		
		var left = 0-idx*item_width;	
		$(box).animate({ 
			    left: left
			  }, {
				  "duration":300
			  } );	
	}
	
}
//按固定方向固动，用于左右事件 direct:0左 1:右
function roll_image_keep_direct(idx,box_id,item_width,direct)
{
	var box = $(box_id+" ul.roll");
	var count = 2;
	
	$(box_id).find(".op li").removeClass("current");
	$(box_id).find(".op li[rel='"+idx+"']").addClass("current");
	
	if(direct==1)
	{
		if(idx==0)
		{
			var left = 0-count*item_width;	
			$(box).animate({ 
				    left: left
				  },{
					  "duration":300
				  });	
		}
		else
		{
			if(idx==1)
			{
				 $(box).css("left",0);
			}
			var left = 0-idx*item_width;	
			$(box).animate({ 
				    left: left
				  }, {
					  "duration":300
				  } );	
		}	
	}
	else if(direct==0)
	{
		if(idx==0)
		{
			$(box).css("left",0-item_width);
			var left = 0;	
			$(box).animate({ 
				    left: left
				  }, {
					  "duration":300
				  } );		
		}
		else
		{
			$(box).css("left",0-2*item_width);
			var left = 0-idx*item_width;	
			$(box).animate({ 
				    left: left
				  }, {
					  "duration":300
				  } );	
		}	
	}
	
}
//横向滚动封装

//新闻滚动
function init_news()
{
	$(".notice_board").everyTime(3000,function(){	
		roll_news();
	});
	$(".notice_board").hover(function(){
		$(".notice_board").stopTime();
	},function(){
		$(".notice_board").everyTime(3000,function(){	
			roll_news();
		});
	});
	
}
function roll_news()
{
	$(".notice_board ul").find("li:first").animate({marginTop:"-"+$(".notice_board ul").find("li:first").height()+"px"},300,function(){
    	var li = $(this);
		$(".notice_board ul").append("<li>"+$(li).html()+"</li>");
        $(li).remove();
	});	
}

//新闻滚动


//全部地区
function init_allarea()
{
	$(".index_pick .tuan_area").find("a.more").live("mouseover",function(){	
		$(".index_pick").find(".tuan_area").addClass("open");	
	});
	
	$(".index_pick .tuan_area").bind("mouseover",function(){
		$(".index_pick .tuan_area").stopTime();
	});
	$(".index_pick .tuan_area").bind("mouseout",function(){
		$(".index_pick .tuan_area").oneTime(200, function(){
			$(".index_pick").find(".tuan_area").removeClass("open");
		});
	});
	
	
}
//全部地区


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

		var top = $(cate_box).offset().top - flow_top ;

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














