$(document).ready(function(){	
	if($("#business_address").length>0)
	init_business_address();	
});

var is_load_business = false;
function init_business_address()
{	
	$.load_business = function(){
		var scrolltop = $(window).scrollTop();
		var loadheight = $("#business_address").offset().top;
		var windheight = $(window).height();
		if(!is_load_business)
		{			
			if(windheight+scrolltop>=loadheight)
			{
				$("#business_address").load_business_address();			
			}
		}
	};
	
	$.load_business();
	$(window).bind("scroll", function(e){
		$.load_business();
	});
}


(function($) {  
	
	$.init_supplier_map = function(dom){
		
		$.create_lable = function(name){
			var label = new BMap.Label(name,{"offset":new BMap.Size(-8,-10)});
			label.setStyle({
                borderColor:"#808080",
                color:"#333",
                cursor:"pointer"
            });
            return label;
		};
		
		$.create_mark = function(name,xpoint,ypoint,url){
			/*创建地理编码服务实例  */
	        var point = new BMap.Point(xpoint,ypoint);
	        /*将结果显示在地图上，并调整地图视野*/  
	        map.centerAndZoom(point, 14);  
	        map.enableScrollWheelZoom();   //启用滚轮放大缩小，默认禁用
	    	map.enableContinuousZoom();    //启用地图惯性拖拽，默认禁用
			var marker = new BMap.Marker(new BMap.Point(xpoint,ypoint));
			var label= $.create_lable(name);
			marker.setLabel(label);
			marker.getLabel().hide();
			map.addOverlay(marker);
			marker.addEventListener('mouseover',function(){
				//map.panTo(new BMap.Point(xpoint,ypoint));
	             marker.getLabel().show();
	        }); 
			marker.addEventListener('mouseout',function(){
		           marker.getLabel().hide();
		        }); 
		 	marker.addEventListener('click',function(){            
		           window.open(url);
		        }); 
		};
		
		if($(dom).find(".map_data").length>0)
		{
			
			var map = new BMap.Map("supplier_map"); 
	        var opts = {type: BMAP_NAVIGATION_CONTROL_ZOOM}  ;
	        map.addControl(new BMap.NavigationControl());  
	        map.centerAndZoom(new BMap.Point(116.404, 39.915), 14);
	        
	        $(dom).find(".map_data").each(function(i,box){
	        	var store_name = $(box).attr("store_name");
	        	var xpoint = $(box).attr("xpoint");
	        	var ypoint = $(box).attr("ypoint");
	        	var store_url = $(box).attr("store_url");
	        	var store_address = $(box).attr("store_address");
	        	var store_tel = $(box).attr("store_tel");
	        	$.create_mark(store_name,xpoint,ypoint,store_url);
	        	$(box).bind("click",function(){
	        		map.panTo(new BMap.Point(xpoint,ypoint));        		
	        		var infoWindow = new BMap.InfoWindow("<a href='"+store_url+"' target='_blank' class='pop_store_title'>"+store_name+"</a><br />电话："+store_tel+"<br />地址："+store_address,{offset:new BMap.Size(0,-20),enableMessage:false});  // 创建信息窗口对象
	        		var point = new BMap.Point(xpoint, ypoint);
	        		infoWindow.setWidth(200);
	        		map.openInfoWindow(infoWindow,point); //开启信息窗口
	        	});
	        });
		}
		
        
	};
	
	$.init_ui = function(dom){
		$(dom).find(".show_hide_child").hide();
		$(dom).find(".show_hide_child:first").show();
		$(dom).find(".show_hide").hover(
			function()
			{
				$(this).siblings().find(".show_hide_child").hide();
				$(this).find(".show_hide_child").show();			
			},
			function()
			{
				
			}
		);
	};


	$.bind_location_pager = function(dom,query){		
		$(dom).find(".pages a").bind("click",function(){			
			var ajax_url = $(this).attr("href");
			$.do_load_business_address(dom,ajax_url,query);			
			return false;
		});
	};
	
	$.bind_location_select = function(dom,ajax_url,query){
		$(dom).find(".select_list select.ui-select").bind("change",function(){
			var field_name = $(this).attr("name");
			var field_value = $(this).val();
			if(field_name=="city_id")
			{
				query.aid = 0;
				query.qid = 0;
			}
			if(field_name=="aid")
			{
				query.qid = 0;
			}
			query[field_name] = field_value;
			$.do_load_business_address(dom,AJAX_URL,query);
		});
	};
	

	$.do_load_business_address = function(dom,ajax_url,query){		
		$(dom).html("<div class='loading'></div>");
		is_load_business = true;
		$.ajax({
			url:ajax_url,
			data:query,
			dataType:"json",
			type:"post",
			global:false,
			success:function(obj){						
				$(dom).html(obj.html);
				$.init_ui(dom);
				init_ui_select();
				$.bind_location_pager(dom,query);
				$.bind_location_select(dom,ajax_url,query);
				$.init_supplier_map(dom);
			}				
		});
	};
	
	$.fn.load_business_address = function(){
		var query = new Object();
		var business_box = $(this);
		query.deal_id = $(business_box).attr("deal_id");
		query.event_id = $(business_box).attr("event_id");
		query.youhui_id = $(business_box).attr("youhui_id");
		query.supplier_id = $(business_box).attr("supplier_id");
		query.act = "load_business_address";
		$.do_load_business_address(business_box,AJAX_URL,query);		
	};
	
})(jQuery); 