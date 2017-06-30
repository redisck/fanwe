//左侧结果点击对象
var cur_item = null;
var total;
var cur_page = 0;
var marker_array = new Array();
$(function(){
	$('.tangram-suggestion-main').css({'top':'372px'});
	
});

function save_uc_dc_address(){


	var op_ak = BAIDU_APPKEY;
	var op_q = encodeURIComponent($.trim($("#q_text").val()));
	
	var op_page_size = 1;
	var op_page_num = cur_page;
	var op_region = encodeURIComponent(CITY_NAME);
	var url = "http://api.map.baidu.com/place/v2/search?ak="+op_ak+"&output=json&query="+op_q+"&page_size="+op_page_size+"&page_num="+op_page_num+"&scope=1&region="+op_region;
	
	var query=new Object();
	query.id=c_id;
	query.consignee=$("input[name='consignee']").val();
	query.mobile=$("input[name='mobile']").val();
	query.api_address=$.trim($("#q_text").val());
	query.act='save_user_consignee';
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
		
					query.address=$("input[name='address']").val();
					$.ajax({
						url:DC_AJAX_URL,
						data:query,
						type:'post',
						dataType:'json',
						success:function(data){						
							if(data.info==1)
									{
										
									$.showSuccess('<span class="info_tip">添加成功</span>',location.href = data.url);	
									}	
									else
									{
									$.showSuccess('<span class="info_tip">更新成功</span>',location.href = data.url);	
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



$(document).ready(function(){
	$('#sub_dc_address').bind('click',function(){	
		check_pay();
	});
});


function check_pay(){

	if(!check_empty($("input[name='consignee']"),'请填写姓名')){ 
	return false;
	}	
	if(!check_empty($("input[name='mobile']"),'请填写电话')){ 
	return false;
	}
	if(!checkmobile($("input[name='mobile']"),'手机号码格式不正确！')){ 
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
	
	save_uc_dc_address();
}

function checkmobile(o,info){
		if (!o.val().match(/^1\d{10}$/)) {
		$.showErr('<span class="info_tip">'+info+'</span>',function(){o.focus();});
		return false;
		}
		return true;
} 

function check_empty(o,info){

		if($.trim(o.val())==''){
		$.showErr('<span class="info_tip">'+info+'</span>',function(){o.focus();});
		return false;	
		}else{
		return true;
		}

}
