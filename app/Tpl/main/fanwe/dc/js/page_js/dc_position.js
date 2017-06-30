//左侧结果点击对象
var cur_item = null;
var total;
var cur_page = 0;
var marker_array = new Array();
$(function(){


	$("img[src='']").each(function(i,obj){
		$(obj).attr('src',NO_IMAGE);
	});
	
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
		$('#q_text').ui_textbox({refresh:true});
		ac.setInputValue(dc_title);
	}
	var myValue;
	ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
		var _value = e.item.value;
		myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
		searchlocation(myValue);
	});



	//定位点击事件
	$("div.position_btn").bind("click",function(){

		doOptionMap();
		
	});

	$('.result-item').live("click",function(){

	var data=$(this).attr('data-params');
	var dataset=eval("("+data+")");  //json字体串转为json对象
	url=dc_position_url;
	$.ajax({
		url:url,
		type:"POST",
		data:{'xpoint':dataset.lng,'ypoint':dataset.lat,'dc_title':dataset.title,'dc_content':dataset.content,'dc_num':dataset.dc_num},
		success:function(data){
		location.href=dc_url;	

		}
	});
	});


	$('#q_text').bind({'focus':function(){
		get_dc_history();
	}});
	
	$('.dc_clear_history').live('click',function(){
		$.ajax({
			url:dc_clear_history_url,
			type:"GET",
			success:function(data){
				$('.tangram-suggestion').empty();
				$('.tangram-suggestion-main').hide();
			}
		});
		return false;
	});

		
	$('#dc_location_search').keyup(function(){

		get_location_search();
	
	}).focus(function(){
		$('#lid_search_result').show();
	});
	
	
	$('#lid_search_result table tr').live('click',function(){
	
		var url=$(this).attr('data-i');
		location.href=url;
	});
	
	$(document).click(function(e){
		e=window.event || e;
		var obj=$(e.srcElement || e.target);
		if(!$(obj).is("#lid_search_result , #dc_location_search , #lid_search_result *")){
			$('#lid_search_result').hide();
		}
	});
	
	
	
});
function init_search_name(){

	if(typeof(dc_title)!='undefined'){
	$('#q_text').val(dc_title);
	}
}
function get_location_search(){


	var query = new Object();
	query.act = "get_location_search";
	var kw=$.trim($('#dc_location_search').val());
	query.kw=kw;
	if(kw){
		$.ajax({
					url: DC_AJAX_URL,
					data: query,
					dataType: "json",
					type: "post",
					success: function(data){
						if(data.lid_count > 0 || data.menu_count > 0){	
						$('#lid_search_result').remove();
						$('.location_fl').append(data.html);
						$('#lid_search_result').show();
						
						}
					}
		});
	}


}


function doOptionMap(kw){
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

	var map = new BMap.Map("map_show");
	var op_page_size = 10;
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
						
					$('.tangram-suggestion').empty();
				$('.tangram-suggestion').append("<div id='dc_loto'><table id='dc_loto_t' cellpadding=0 cellspacing=0 border=0><tbody></tbody></table></div>");
					for (var i=0;i<obj.results.length;i++)
					{
						
						item = obj.results[i];
						
						var query = new Object();
						query.act = "get_dc_num";
						query.dc_xpoint = item.location.lng;
						query.dc_ypoint = item.location.lat;
						query.dc_title = item.name;
						query.dc_content = item.address;
						query.dc_index = i;
						$.ajax({
							url:DC_AJAX_URL,
							data:query,
							dataType:"json",
							type:"POST",
							success:function(data)
							{
							$('#dc_loto_t tbody').append(data.html);
							$('.tangram-suggestion-main').show();
							}
							});

					}

				}
			}
		});
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
									data:{'xpoint':item.location.lng,'ypoint':item.location.lat,'dc_title':item.name,'dc_content':item.address,'dc_num':objdata.dc_num},
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


function get_dc_history(){


		var query = new Object();
		query.act = "get_dc_history";
		$.ajax({
			url:DC_AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(data)
			{

			if(data.status==1){
			
			$('.tangram-suggestion').empty();
			$('.dc_clear_history').remove();
			$('.tangram-suggestion').append("<div id='dc_loto'><table id='dc_loto_t' cellpadding=0 cellspacing=0 border=0><tbody></tbody></table></div>");
			$('#dc_loto_t tbody').append(data.html);
			$('.tangram-suggestion').append("<div class='dc_clear_history'>清空历史记录</div>");
			$('.tangram-suggestion-main').show();	
		
			}
			
			
			}
		});



}



			