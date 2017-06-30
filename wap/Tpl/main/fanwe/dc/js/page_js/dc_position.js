//左侧结果点击对象
var cur_item = null;
var total;
var cur_page = 0;
var marker_array = new Array();
$(function(){

	init_search_name();
	var map = new BMap.Map("map_show");
	map.centerAndZoom(CITY_NAME,12);                   // 初始化地图,设置城市和地图级别。
	//添加点击事件监听
	map.addEventListener("click", function(e){    
	 
	 var query = {ak:BAIDU_APPKEY,location:e.point.lat+","+e.point.lng,output:"json"};
		$.ajax({
			url:"http://api.map.baidu.com/geocoder/v2/",
			dataType:"jsonp",
			callback:"callback",
			data:query,
			success:function(obj){
				var address = obj.result.formatted_address;
				var title = obj.result.sematic_description;
				var infoWindow_obj = create_window({title:title,content:address,lng:e.point.lng,lat:e.point.lat});
				map.openInfoWindow(infoWindow_obj,new BMap.Point(e.point.lng,e.point.lat)); //开启信息窗口
			}
		});

	});
	var ac = new BMap.Autocomplete(    //建立一个自动完成的对象
		{"input" : "q_text"
		,"location" : map
	});


	if($.trim(dc_title)!=''){
		$('#q_text').val(dc_title);
		ac.setInputValue(dc_title);
	}
	var myValue;
	ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
		var _value = e.item.value;
		myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
		searchlocation(myValue);
	});

	
	$('.dc_clear_history').bind('click',function(){
		$.ajax({
			url:dc_clear_history_url,
			type:"GET",
			success:function(data){
				$('.history').empty().hide();

			}
		});
		return false;
	});

		

	$('.result-item').bind("click",function(){

		var data=$(this).attr('data-params');
		var dataset=eval("("+data+")");  //json字体串转为json对象
		url=dc_position_url;
		$.ajax({
			url:url,
			type:"POST",
			data:{'xpoint':dataset.lng,'m_longitude':dataset.lng,'ypoint':dataset.lat,'m_latitude':dataset.lat,'dc_title':dataset.title,'dc_content':dataset.content,'dc_num':dataset.dc_num},
			success:function(data){
			location.href=dc_url;	

			}
		});
		});


	$('#do_search').bind('click',function(){
		var kw=dc_title;
		searchlocation(kw);
	});

		
		
});
function init_search_name(){

	if(typeof(dc_title)!='undefined'){
	$('#q_text').val(dc_title);
	}
}


function searchlocation(kw){

	cur_item = null;
	marker_array = new Array();
	var op_ak = BAIDU_APPKEY;
	if($.trim(kw)){
	var op_q=encodeURIComponent(kw);
	}
	else
	{
	var op_q = encodeURIComponent($.trim($("#q_text").val()));
	}

	if(op_q==''){
		alert('请输入地址搜索周边商家');
		return false;
	}
	
	var op_page_size = 1;
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
						var item=obj.results[0];
						
						var query = new Object();
						query.act = "get_dc_num";
						query.dc_xpoint = item.location.lng;
						query.dc_ypoint = item.location.lat;
						query.dc_title = item.name;
						query.dc_content = item.address;
						$.ajax({
							url:DC_AJAX_URL,
							data:query,
							dataType:"json",
							type:"POST",
							success:function(objdata)
							{
						
								url=dc_position_url;
								$.ajax({
									url:url,
									type:"POST",
									data:{'xpoint':item.location.lng,'m_longitude':item.location.lng,'ypoint':item.location.lat,'m_latitude':item.location.lat,'dc_title':item.name,'dc_content':item.address,'dc_num':objdata.dc_num},
									success:function(data){
									location.href=dc_url;	
							
									}
								});
								
							
							}
						});
						


				}
			}
		});
	}	
	
}



			