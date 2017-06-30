//左侧结果点击对象
var cur_item = null;
var total;
var cur_page = 0;
var marker_array = new Array();
$(function(){
	$('.tangram-suggestion-main').css({'top':'372px'});

	//定位点击事件
	$("div.position_btn").live("click",function(){

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
				$('#q_text').val(dataset.title).change();
				$('.tangram-suggestion-main').slideUp();
			//	$('#q_text').attr('data-params',"{'xpoint':'"+dataset.lng+"','ypoint':'"+dataset.lat+"'}");

		}
	});
	});


	if($('.dc_cart_consignee #q_text').length>0){
		$('.dc_cart_consignee #q_text').bind({'focus':function(){
		get_dc_history();
		},'change':function(){
		
		//get_dc_delivery_price();
		}});
	}
	
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

	
});


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
					$('.tangram-suggestion').append("<form id='dc_loto' method='post' action=''></form>");
					for (var i=0;i<obj.results.length;i++)
					{
						
						
						 item = obj.results[i];
						
						var query = new Object();
						query.act = "get_dc_num";
						query.dc_xpoint = item.location.lng;
						query.dc_ypoint = item.location.lat;
						query.dc_title = item.name;
						query.dc_content = item.address;
						query.is_show_num = 0;
						query.dc_index = i;
						$.ajax({
							url:DC_AJAX_URL,
							data:query,
							dataType:"json",
							type:"POST",
							success:function(data)
							{

							$('#dc_loto').append(data.html);
							$('.tangram-suggestion-main').show();
							}
							});


					}
				}
			}
		});
	}	
	
}



function get_dc_history(){


		var query = new Object();
		query.act = "get_dc_history";
		query.is_show_num = 0;
		$.ajax({
			url:DC_AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj)
			{
			if(obj.status==1){
			$('.tangram-suggestion').empty();
			$('.dc_clear_history').remove();
			$('.tangram-suggestion').append("<form id='dc_loto' method='post' action=''><table id='dc_loto_t'><tbody></tbody></table></form>");
			$('#dc_loto_t tbody').append(obj.html);
			$('.tangram-suggestion').append("<tr class='dc_clear_history'><td>清空历史记录</td></tr>");
			$('.tangram-suggestion-main').show();		
			}
			}
			});



}


function get_dc_delivery_price(){

	if(!check_empty($("input[name='consignee']"),'请填写姓名')){
	return false;
	}	
	if(!check_empty($("input[name='mobile']"),'请填写电话')){
	return false;
	}
	if(!checkmobile($("input[name='mobile']"),'手机号码格式不正确')){
	return false;
	}
	var op_ak = BAIDU_APPKEY;
	var op_q = encodeURIComponent($.trim($("#q_text").val()));
	
	var op_page_size = 1;
	var op_page_num = cur_page;
	var op_region = encodeURIComponent(CITY_NAME);
	var url = "http://api.map.baidu.com/place/v2/search?ak="+op_ak+"&output=json&query="+op_q+"&page_size="+op_page_size+"&page_num="+op_page_num+"&scope=1&region="+op_region;
	
	var query=new Object();
	var ajaxurl=DC_AJAX_URL;
	query.consignee=$("input[name='consignee']").val();
	query.mobile=$("input[name='mobile']").val();
	query.act='save_consignee_info';
	query.api_address=$.trim($("#q_text").val());
	if($.trim($("#q_text").val())){
		$.ajax({
			url:url,
			dataType:"jsonp",
	        jsonp: 'callback',
			type:"GET",
			success:function(obj){
					var item = obj.results[0];	
					if(item.location){		
					query.xpoint=item.location.lng;
					query.ypoint=item.location.lat;

					query.location_id=location_id;
					query.address=$("input[name='address']").val();
					$.ajax({
						url:ajaxurl,
						data:query,
						type:'post',
						dataType:'json',
						success:function(data){
							if(data.status>0){
								$('.location_fl').attr('data-parmas',data.id);
								dc_count_buy_total();
							}

						}
					});
					}else{
					$.showErr('<span class="info_tip">请输入详细位置并在下拉框中进行选择</span>',function(){$("#q_text").val('').focus();});			
					}
				
					


			}
			});
	}else{
			$.showErr('<span class="info_tip">请输入地理位置</span>',function(){$("#q_text").focus();});
			return false;
	
	}
	
	
}




	