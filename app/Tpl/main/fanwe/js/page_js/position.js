//左侧结果点击对象
var cur_item = null;
var total;
var cur_page = 0;
var marker_array = new Array();
$(function(){
	

	//定位点击事件
	$("div.position_btn").bind("click",function(){
		cur_page = 0;
		doOptionMap();
		
	});
	
	
});


function doOptionMap(){
	cur_item = null;
	marker_array = new Array();
	var op_ak = BAIDU_APPKEY;
	var op_q = encodeURIComponent($.trim($("#q_text").val()));

	var op_page_size = 6;
	var op_page_num = cur_page;
	var op_region = encodeURIComponent(CITY_NAME);
	var url = "http://api.map.baidu.com/place/v2/search?ak="+op_ak+"&output=json&query="+op_q+"&page_size="+op_page_size+"&page_num="+op_page_num+"&scope=1&region="+op_region;

	if($.trim($("#q_text").val())){
		$.ajax({
			url:url,
			dataType:"jsonp",
	        jsonp: 'callback',
			type:"GET",
			success:function(obj){
				if(obj.status == 0){
					$(".result-panel").show();
					total = obj.total;
					var data = obj.results;
					var item = new Array();
					var result_html = '';
					
					//清除所有覆盖物
					map.clearOverlays();
					if(obj.total>0){
						map.centerAndZoom(new BMap.Point(obj.results[0].location.lng, obj.results[0].location.lat), 16);
					}else{
						map.centerAndZoom("福州", 12);
					}
						
					
					for (var i=0;i<obj.results.length;i++)
					{
						item = obj.results[i];
						result_html += '<li class="result-item" data-params="{\'lng\':\''+item.location.lng+'\',\'lat\':\''+item.location.lat+'\',\'title\':\''+item.name+'\',\'content\':\''+item.address+'\',\'type\':\'0\',\'index\':\''+i+'\'}" data-i="'+i+'" id="'+item.uid+'">'
							+'<span class="icon icon-'+i+'"></span>'
							+'<a class="btn-selected" href="javascript:;">查看附近团购</a>'
							+'<h3>'+item.name+'</h3><p class="desc" >地址：'+item.address+'</p></li>';
						
						var position_y = i*-25;
						//循环给地图加点
						var pt = new BMap.Point(item.location.lng,item.location.lat);
						var myIcon = new BMap.Icon(MARKER_ICON, new BMap.Size(22,25),{imageOffset:new BMap.Size(0,position_y)});
						var marker = new BMap.Marker(pt,{icon:myIcon});  // 创建标注
						var content = '<a class="btn-selected" href="javascript:;">查看附近团购</a>';
						marker_array.push(marker);
						map.addOverlay(marker);               // 将标注添加到地图中
						var infoWindow_obj = create_window({title:item.name,content:item.address,lng:item.location.lng,lat:item.location.lat,index:i});
						marker.addEventListener("click", function(){          
							map.openInfoWindow(infoWindow_obj,pt); //开启信息窗口
						});
					}
					$("#search-result").html(result_html);	
					
					create_page();
					$(".search-number").html("共有"+obj.total+"条结果");
					
					
					//鼠标移过结果列表事件
					$(".result-item").hover(
							  function () {
							    $(this).addClass("selected");
							    //改变地图气球内容

							    var json_str =$(this).attr("data-params");
							    json_str = json_str.replace(/\'/g,'"');
							    var json_obj =  $.parseJSON(json_str);
							    var maker_item = marker_array[json_obj.index];

							    var position_y = -390+(json_obj.index*-28);
							    var myIcon = new BMap.Icon(MARKER_ICON, new BMap.Size(25,28),{imageOffset:new BMap.Size(0,position_y)});
							    maker_item.setIcon(myIcon);
							  },
							  function () {
								if($(".result-item").index($(this)) !=cur_item){
									$(this).removeClass("selected");
									var json_str =$(this).attr("data-params");
								    json_str = json_str.replace(/\'/g,'"');
								    var json_obj =  $.parseJSON(json_str);
								    var maker_item = marker_array[json_obj.index];
									var position_y = json_obj.index*-25;
								    var myIcon = new BMap.Icon(MARKER_ICON, new BMap.Size(22,25),{imageOffset:new BMap.Size(0,position_y)});
								    maker_item.setIcon(myIcon);
								}
								
							    
							  }
							);
					$(".result-item").bind("click",function(){
							$(".result-item").removeClass("selected");
							$(this).addClass("selected");
							cur_item = $(".result-item").index($(this));
							refresh_maker();
							var json_str =$(this).attr("data-params");
						    json_str = json_str.replace(/\'/g,'"');
						    var json_obj =  $.parseJSON(json_str);
						    var maker_item = marker_array[json_obj.index];

						    var position_y = -390+(json_obj.index*-28);
						    var myIcon = new BMap.Icon(MARKER_ICON, new BMap.Size(25,28),{imageOffset:new BMap.Size(0,position_y)});
						    maker_item.setIcon(myIcon);
						    
						    var pt = new BMap.Point(json_obj.lng, json_obj.lat);
						    var infoWindow_obj = create_window(json_obj);
						    map.openInfoWindow(infoWindow_obj,pt); //开启信息窗口
						    //弹出窗口
			
						}
					);
				}else{
					$(".result-panel").hide();
				}
			}
		});
	}	
	
}
function refresh_maker(){
	for (var i=0;i<marker_array.length;i++)
	{
		var position_y = i*-25;
		//循环给地图加点
		var maker_item = marker_array[i];
		var position_y = i*-25;
	    var myIcon = new BMap.Icon(MARKER_ICON, new BMap.Size(22,25),{imageOffset:new BMap.Size(0,position_y)});
	    maker_item.setIcon(myIcon);
	}
}

function create_window(obj){
	 var title_html = '<h3 style="color:#f80">'+obj.title+'</h3>';
		var opts = {
			  width : 0,     // 信息窗口宽度
			  height: 0,     // 信息窗口高度
			  title : title_html , // 信息窗口标题
			  enableMessage:false//设置允许信息窗发送短息
			};
		var content = '<div style="text-align:center;font-size:14px;margin:10px 0 15px 0;">'+obj.content+'</div>'
					 +'<div style="text-align:center;"><a href="javascript:void(0);" style="display:inline-block; *display:inline; *zoom:1;border:1px solid #ccc;padding:2px 5px;" onclick="do_position_tuan();">查看周边团购</a>'
					 +'<form name="do_submit_position" method="post" action="'+DO_POSITION_URL+'"><input type="hidden" name="xpoint" value="'+obj.lng+'"/><input type="hidden" name="ypoint" value="'+obj.lat+'"/><input type="hidden" name="address" value="'+obj.content+'"/></form></div>';

		return new BMap.InfoWindow(content, opts); 
}
function do_position_tuan(){
	
	$("form[name='do_submit_position']").submit();
	
}
//转换成JSon 对象
function jsonstr_to_obj(json_str){
	json_str = json_str.replace(/\'/g,'"');
    return $.parseJSON(json_str);
}

function create_page(){
	if(total>6){ //创建分页
		var total_page = parseInt(total/6);
		var page_html = '';
		//上一页
		var page_previous_html='';
		//下一页
		var page_next_html = '';
		//大于一页时候显示 之前页的页码
		var page_obj_html = '';
		
		//当前页后面显示的页数
		var j = 0;
		var temp_num = cur_page+3;
		if(temp_num<=total_page){
			j = temp_num;
		}else{
			j = total_page;
		}
		
		//当前页后面显示的页数 html
		var p_item = '';
		var show_page_num=0;
		for(var i=cur_page;i<=j;i++){
			show_page_num = i+1;
			if(i==cur_page){
				p_item = '<a class="current" href="javascript:void(0);" onclick="do_page('+i+')">'+show_page_num+'</a>&nbsp;';
			}else{
				p_item = '<a href="javascript:void(0);" onclick="do_page('+i+')">'+show_page_num+'</a>&nbsp;';
			}
			
			page_html+=p_item;
		}
		
		if(cur_page>0){
			page_previous_html = '<a href="javascript:void(0);" onclick="do_page('+(cur_page-1)+')">上一页</a>&nbsp;';
			page_obj_html = '<a href="javascript:void(0);" onclick="do_page('+(cur_page-1)+')">'+cur_page+'</a>&nbsp;';
		}

		if(temp_num<total){
			page_next_html = '<a href="javascript:void(0);" onclick="do_page('+(temp_num+1)+')">下一页</a>';
		}
		
		page_html = page_previous_html+page_obj_html+page_html+page_next_html;
		$(".page-wrapper").html(page_html);
	}
}
//切换分页
function do_page(num){
	cur_page = num;
	doOptionMap();
	create_page();
}

