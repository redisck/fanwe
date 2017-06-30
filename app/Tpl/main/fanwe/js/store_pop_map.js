//弹出商家地图的脚本类

(function($) {
	$.fn.bind_load_store_map = function(){
		var dom = $(this);
		dom.bind("click",function(){
			var store_id = dom.attr("rel");
			var query = new Object();
			query.act = "load_store_map";
			query.id = store_id;
			$.ajax({
				url:AJAX_URL,
				data:query,
				dataType:"json",
				type:"POST",
				success:function(obj){
					if(obj.status)
					{
						$.weeboxs.open(obj.html, {boxid:'store_map',contentType:'html',showButton:false, showCancel:false, showOk:false,title:obj.info,width:750,height:500,type:'wee',onopen:function(){
							$.on_open_map();
						}});
					}
					else
					{
						$.showErr(obj.info);
					}					
				}
			});			
			return false;
		});
	};


	$.on_open_map = function(){
		
		
		var map = new BMap.Map("store_pop_map"); 
        var opts = {type: BMAP_NAVIGATION_CONTROL_ZOOM}  ;
        map.addControl(new BMap.NavigationControl());  
        map.centerAndZoom(new BMap.Point(116.404, 39.915), 14);
        map.enableScrollWheelZoom();   //启用滚轮放大缩小，默认禁用
    	map.enableContinuousZoom();    //启用地图惯性拖拽，默认禁用

        /*创建地理编码服务实例  */
        var point = new BMap.Point(store_info.xpoint,store_info.ypoint);
        /*将结果显示在地图上，并调整地图视野*/  
        map.centerAndZoom(point, 14);  			
		var marker = new BMap.Marker(new BMap.Point(store_info.xpoint,store_info.ypoint));


		var label = new BMap.Label(store_info.name,{"offset":new BMap.Size(-8,-10)});
		label.setStyle({
            borderColor:"#808080",
            color:"#333",
            cursor:"pointer"
        });
		
		marker.setLabel(label);
		marker.getLabel().hide();
		map.addOverlay(marker);
		marker.addEventListener('mouseover',function(){
           marker.getLabel().show();
        }); 
		marker.addEventListener('mouseout',function(){
           marker.getLabel().hide();
	    }); 
	 	marker.addEventListener('click',function(){            
	 		var infoWindow = new BMap.InfoWindow("<a href='"+store_info.url+"' target='_blank' class='pop_store_title'>"+store_info.name+"</a><br />电话："+store_info.tel+"<br />地址："+store_info.address,{offset:new BMap.Size(0,-20)});  // 创建信息窗口对象
			var point = new BMap.Point(store_info.xpoint, store_info.ypoint);
			infoWindow.setWidth(200);
			map.openInfoWindow(infoWindow,point); //开启信息窗口
			
	    }); 

        
	 	var infoWindow = new BMap.InfoWindow("<a href='"+store_info.url+"' target='_blank' class='pop_store_title'>"+store_info.name+"</a><br />电话："+store_info.tel+"<br />地址："+store_info.address,{offset:new BMap.Size(0,-20)});  // 创建信息窗口对象
		var point = new BMap.Point(store_info.xpoint, store_info.ypoint);
		infoWindow.setWidth(200);
		map.openInfoWindow(infoWindow,point); //开启信息窗口
		
	};
})(jQuery); 