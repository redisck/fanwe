$(document).ready(function(){
	init_content_nav();
	init_countdown();
	init_collect_btn();
	init_event_map();
	init_submit_btn();
	$(window).bind("scroll", function(e){
		$(".dialog-mask").css("height",$(document).height());
	});
});

function init_submit_btn()
{
	$("#submit_btn").bind("click",function(){
		load_submit();
	});

}

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

function load_submit()
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
				$.weeboxs.open(obj.html, {boxid:"wee_event_submit",position:"fixed", showButton:false,title:"填写报名信息",width:500,type:'wee',onopen:function(){
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

function init_collect_btn()
{
	$("#collect_btn").bind("click",function(){
		var query = new Object();
		query.act = "collect_event";
		query.id = EVENT_ID;
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status==1000)
				{
					ajax_login();
				}
				else if(obj.status==1)
				{
					$.showSuccess(obj.info);
				}
				else
				{
					$.showErr(obj.info);
				}
			}
		});
	});
}


/**
 * 初始化倒计时
 */
function init_countdown()
{
	var endtime = $("#countdown").attr("endtime");
	var nowtime = $("#countdown").attr("nowtime");
	var timespan = 1000;
	$.show_countdown = function(dom){
		var showTitle = $(dom).attr("showtitle");
		var timeHtml = "";
		var sysSecond = (parseInt(endtime) - parseInt(nowtime))/1000;
		
		if(sysSecond>=0)
		{
			var second = Math.floor(sysSecond % 60);              // 计算秒     
			var minite = Math.floor((sysSecond / 60) % 60);       //计算分
			var hour = Math.floor((sysSecond / 3600) % 24);       //计算小时
			var day = Math.floor((sysSecond / 3600) / 24);        //计算天
			
			if(day > 0)
				timeHtml ="<span>"+day+"</span>天";
			timeHtml = timeHtml+"<span>"+hour+"</span>时<span>"+minite+"</span>分"+"<span>"+second+"</span>秒";
			timeHtml = showTitle+timeHtml;
			
			$(dom).html(timeHtml);		
			nowtime = parseInt(nowtime) + timespan;
		}
		else
		{
			$("#countdown").stopTime();
		}	
	};
	
	$.show_countdown($("#countdown"));
	$("#countdown").everyTime(timespan,function(){
		$.show_countdown($("#countdown"));
	});	
}

//关于内容页的滚动定位,包含x店通用的点击滚动
function init_content_nav()
{	
	$("#flow_btn").bind("click",function(){
		load_submit();	
	});
	var is_show_fix = false;	
	var content_idx = -1;
	$.reset_nav = function(){
		if($.browser.msie && $.browser.version =="6.0")
		{
			$(".fix-nav").css("top",$(document).scrollTop());
		}	
		
		var navheight = $("#rel_nav").offset().top;
		var docheight = $(document).scrollTop();		
		if(docheight>navheight)		
		{			
			if(!is_show_fix)
			{	
				is_show_fix = true;
				$(".fix-nav").show();
				$("#rel_nav").css("visibility","hidden");
				if($.browser.msie && $.browser.version =="6.0")
				{						
					$(".fix-nav").css("width",900);			
				}
				else
				{
					$(".fix-nav").css({"top":0,"position":"fixed"});					
					$(".fix-nav").animate({
						width:990
					}, {duration: 200,queue:false });
				}
			}
		}
		else
		{
			if(is_show_fix)
			{
				is_show_fix = false;
				$("#rel_nav").css("visibility","visible");
				if($.browser.msie && $.browser.version =="6.0")
				{
					$(".fix-nav").hide();
					$(".fix-nav").css("width",750);
				}
				else
				{
					$(".fix-nav").css({"top":navheight,"position":"absolute"});
					$(".fix-nav").animate({
						width:750
					}, {duration: 200,queue:false,complete:function(){
						$(".fix-nav").hide();
					}});
				}
				
				
			}
		}
		
		//开始自定定位nav的当前位置	
		var content_boxes = $(".show-content .content_box");
		$(".show-nav").find("li").removeClass("active");
		content_idx = -1;
		for(i=0;i<content_boxes.length;i++)
		{
			var scrollTop = $(document).scrollTop() + 50; 
			var current_top = $(content_boxes[i]).offset().top;//内容盒子高度偏移，预留菜单高度
			var next_top = current_top + 50000;  //下一个高度
			if(i<content_boxes.length-1)
			next_top = $(content_boxes[i+1]).offset().top;	
			if(scrollTop>=current_top&&scrollTop<next_top)
			{
				var rel_id = $(content_boxes[i]).attr("rel");	
				content_idx = rel_id;
				break;
			}
			
		}

		$(".show-nav").find("li[rel='"+content_idx+"']").addClass("active");
	};
	$.reset_nav();	
	$(window).scroll(function(){
		$.reset_nav();
	});	
	
	//滚动至xx定位
	$.scroll_to = function(idx){
		var rel_id = idx;	
		var content_box = $(".show-content .content_box[rel='"+rel_id+"']");
		var top = $(content_box).offset().top-40;
		$("html,body").animate({scrollTop:top},"fast","swing",function(){
			content_idx = rel_id;
			$(".show-nav").find("li").removeClass("active");
			$(".show-nav").find("li[rel='"+content_idx+"']").addClass("active");
		});
	};
	//菜单点击
	$(".show-nav").find("li").bind("click",function(){
		
		var rel_id = $(this).attr("rel");	
		$.scroll_to(rel_id);
	});
	

}


function init_event_map(){
	if($("#event_map").length<=0)return;
	var map = new BMap.Map("event_map"); 
    var opts = {type: BMAP_NAVIGATION_CONTROL_ZOOM}  ;
    map.addControl(new BMap.NavigationControl());  
    map.centerAndZoom(new BMap.Point(116.404, 39.915), 14);
    var box = $("#event_map");
   
	var event_name = $(box).attr("name");
	var xpoint = $(box).attr("xpoint");
	var ypoint = $(box).attr("ypoint");
	var event_address = $(box).attr("address");
	
	
	/*创建地理编码服务实例  */
    var point = new BMap.Point(xpoint,ypoint);
    /*将结果显示在地图上，并调整地图视野*/  
    map.centerAndZoom(point, 14);  			
	var marker = new BMap.Marker(new BMap.Point(xpoint,ypoint));

	var label = new BMap.Label(name,{"offset":new BMap.Size(-8,-10)});
	label.setStyle({
        borderColor:"#808080",
        color:"#333",
        cursor:"pointer"
    });
	
	marker.setLabel(label);
	marker.getLabel().hide();
	map.addOverlay(marker);
 	marker.addEventListener('click',function(){            
 		var infoWindow = new BMap.InfoWindow("<span class='pop_event_name'>"+event_name+"</span><br />地址："+event_address,{offset:new BMap.Size(0,-20)});  // 创建信息窗口对象
		var point = new BMap.Point(xpoint, ypoint);
		infoWindow.setWidth(200);
		map.openInfoWindow(infoWindow,point); //开启信息窗口
    }); 
	
};
