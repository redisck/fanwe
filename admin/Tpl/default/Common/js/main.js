$(document).ready(function(){	
	load_ofc("sale_line_data_chart",sale_line_data_url,"100%",350);
	load_ofc("sale_refund_data_chart",sale_refund_data_url,"100%",350);
	
	
	
	$.ajax({
		url:"http://o2ov.fanwe.net/v.php",
		data:{"v":version,"t":app_type},
		dataType: "jsonp",
        jsonp: 'callback',  
        type:"GET",
        global:false,
		success:function(data)
		{
		   $("#version_tip").html(data.html);
		}
	});
});