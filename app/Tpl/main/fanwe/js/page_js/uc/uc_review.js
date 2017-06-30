$(document).ready(function(){
	$.bind_review_image($("#review_list"));
	$("img[lazy][!isload]").ui_lazy({placeholder:LOADER_IMG});
});



/**
 * 绑定图片事件
 */
$.bind_review_image = function(dom){
	var item_width = 100;
	var show_max = 6;
	var margin = 10;
	var border_width = 2;
	item_width+=(margin+border_width*2);
	$(dom).find(".review_pic").each(function(i,o){
		//初始化
		var img_count = $(o).find(".pic_box li").length;
		var current = 0;
		$(o).find(".over").width(show_max*item_width-margin);
		$(o).find(".pic_box").width(img_count*item_width);
		$(o).find("li").css("margin-right",margin);
		
		//绑定移上
		$(o).find(".over").hover(function(){
			$(o).oneTime(100,function(){
				$(o).find(".pre").animate({left:2},{duration:100,queue:false});
				$(o).find(".next").animate({right:2},{duration:100,queue:false});
			});
		},function(){
			$(o).stopTime();
			$(o).find(".pre").animate({left:-29},{duration:100,queue:false});
			$(o).find(".next").animate({right:-29},{duration:100,queue:false});
		});
		
		//绑定点击箭头事件
		$(o).find(".pre").bind("click",function(){
			current = current-1<0?0:current-1;		
			var left = current * item_width * -1;
			$(o).find(".pic_box").animate({left:left},{duration:100,queue:false});
		});
		$(o).find(".next").bind("click",function(){
			current = current+1>img_count-show_max?img_count-show_max:current+1;
			var left = current * item_width * -1;
			$(o).find(".pic_box").animate({left:left},{duration:100,queue:false});
		});
		
		//绑定点击小图事件
		var img_idx = -1; //当前图片的顺序idx
		$(o).find("li a").each(function(i,o){
			$(o).attr("idx",i); //设置顺序
		});
		$(o).find("li a").bind("click",function(){
			var big_img = $(this).attr("rel");
			$(o).find("li a").removeClass("active");
			if($(this).attr("is_active")=="active")
			{
				$(o).find("li a").removeAttr("is_active");
				$(o).find(".big_img").hide();
				img_idx = -1;
			}
			else
			{
				$(o).find("li a").removeAttr("is_active");
				$(this).attr("is_active","active");
				$(this).addClass("active");
				$(o).find(".big_img").show();		
				$.do_load_big_img($(o).find(".big_img img"),big_img);
				img_idx = parseInt($(this).attr("idx"));
				
				$.do_init_arrow_ui(o,img_idx,img_count);					
			}
		});
		
		//绑定上下切换
		$(o).find(".big_img a.bprev").bind("click",function(){
			img_idx = img_idx-1<0?0:img_idx-1;			
			
			var big_img = $(o).find("li a:eq("+img_idx+")").attr("rel");
			$(o).find("li a").removeAttr("is_active");
			$(o).find("li a").removeClass("active");
			$(o).find("li a:eq("+img_idx+")").attr("is_active","active");
			$(o).find("li a:eq("+img_idx+")").addClass("active");
			$(o).find(".big_img").show();		
			$.do_load_big_img($(o).find(".big_img img"),big_img);
			
			$.do_init_arrow_ui(o,img_idx,img_count);
			if(img_count-img_idx>img_count-current)
			{
				$(o).find(".pre").trigger("click");
			}
			
		});
		
		$(o).find(".big_img a.bnext").bind("click",function(){
			img_idx = img_idx+1>img_count-1?img_count-1:img_idx+1;
			
			var big_img = $(o).find("li a:eq("+img_idx+")").attr("rel");
			$(o).find("li a").removeAttr("is_active");
			$(o).find("li a").removeClass("active");
			$(o).find("li a:eq("+img_idx+")").attr("is_active","active");
			$(o).find("li a:eq("+img_idx+")").addClass("active");
			$(o).find(".big_img").show();		
			$.do_load_big_img($(o).find(".big_img img"),big_img);				
			
			$.do_init_arrow_ui(o,img_idx,img_count);
			if(img_idx>=current+show_max)
			{
				$(o).find(".next").trigger("click");
			}
		});
		
		
	});
};


//初始化大图上的ui箭头
$.do_init_arrow_ui = function(dom,img_idx,img_count){
	
	if(img_idx==0)
	{
		$(dom).find(".big_img a.bprev").hide();
	}
	else
	{
		$(dom).find(".big_img a.bprev").show();
	}
	if(img_idx==img_count-1)
	{
		$(dom).find(".big_img a.bnext").hide();
	}
	else
	{
		$(dom).find(".big_img a.bnext").show();
	}
	
	//绑定大图切换与ui
	$(dom).find(".big_img a").css("width",$(dom).find(".big_img").width()/2);
	$(dom).find(".big_img a").css("height",$(dom).find(".big_img").height());
	
};

$.do_load_big_img = function(imgdom,src){
	//$(imgdom).attr("src",src);
	//$(imgdom).attr("data-src",src);
	//$(imgdom).removeAttr("isload");
	$(imgdom).ui_lazy({placeholder:LOADER_IMG,src:src,refresh:true});
};