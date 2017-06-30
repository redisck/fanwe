$(document).ready(function(){
	
	/*******************************************地图地位部分******************************************************/
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
		{"input" : "q_text",
		"location" : map
	});

	if($.trim(dc_title)!=''){
		$('#q_text').val(dc_title);
		ac.setInputValue(dc_title);
	}
	
	var myValue;
	ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
		var _value = e.item.value;
		myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;

	});
	
	/*************************************************************************************************/

	   
	   $(".del_consignee").click(function(){
		   del_consignee($(this));
			
	   });
	   
	   
	   $(".set_default_address").click(function(){
		   	var is_main= $("input[name='is_main']").val();
		   	if(is_main==1){
		   		is_main=0;
		   	}else{
		   		is_main=1;
		   	}
		   	$("input[name='is_main']").val(is_main);
			
	   });
	   
	   $(".dc_consignee_save").click(function(){
		   dc_consignee_save();

	   });
	   
	   

});


function del_consignee(o){
	var query=new Object();
	var consignee_id=$(o).attr('consignee_id');
	query.id=consignee_id;
	query.act='del';
	$.ajax({
			url:dc_consignee_url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){
				if(data.status==1){

					location.href=dc_consignee_url;
				}else{
					alert(data.info);
				}
			}
	});
	
}


function dc_consignee_save(){
	
	if(!check_empty($("input[name='consignee']"),'请填写姓名')){
		return false;
	}	
	if(!check_empty($("input[name='mobile']"),'请填写手机号')){
		return false;
	}
	if(!checkmobile($("input[name='mobile']"),'手机号码格式不正确')){
		return false;
	}
		
	if($("input[id='q_text']").length > 0){
			if(!check_empty($("input[id='q_text']"),'请输入地理位置')){	
				return false;
			}
	}
	if(!check_empty($("input[name='address']"),'请输入门牌号等详细信息')){
		return false;
	}
	
	

	cur_item = null;
	var op_ak = BAIDU_APPKEY;
	var op_q = encodeURIComponent($.trim($("#q_text").val()));
	var op_page_size = 1;
	var op_page_num = 10;
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
						
						if(typeof(item)!='undefined' && typeof(item.location)!='undefined'){
						var query = new Object();
						query.act = "save_dc_consignee";
						query.consignee = $("input[name='consignee']").val();
						query.mobile = $("input[name='mobile']").val();
						query.xpoint = item.location.lng;
						query.ypoint = item.location.lat;
						query.api_address = $.trim($("#q_text").val());
						query.address = $("input[name='address']").val();
						query.is_main = $("input[name='is_main']").val();
						query.id=$("input[name='id']").val();
						$.ajax({
							url:dc_consignee_url,
							data:query,
							dataType:"json",
							type:"POST",
							success:function(data)
							{
						
								if(data.status==1){
									if(from){
										location.href=dc_consignee_cart;
									}else{
										location.href=dc_consignee_url;	
									}
									
								}else{
									
									$.showErr(data.info);
								}
								
							}
						});
						
					}else{
						$.showErr('地理定位不正确',function(){$("#q_text").focus();});
						
					}

				}else{
					$.showErr('地理定位不正确',function(){$("#q_text").focus();});
					
				}
			}
		});
	}	
	
	
	
	
	
	
}


function checkmobile(o,info){
	if(o.length>0){
		if (!o.val().match(/^1\d{10}$/)) {
		$.showErr(info,function(){o.focus();});
		return false;
		}
		return true;
	}else{
		return true;
	}	
} 

function check_empty(o,info){
	if(o.length>0){
		if($.trim(o.val())==''){
		$.showErr(info,function(){o.focus();});
		return false;	
		}else{
		return true;
		}
	}else{
		return true;
	}
}
	


