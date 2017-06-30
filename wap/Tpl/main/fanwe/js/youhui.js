$(function(){
	init_countdown();
	init_dp_star();
	$(".J_item_more").click(function(){
		
        $(this).parent().find(".business_display").toggleClass("business_blank");
    });
});

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

function download_youhui(id){
	var query = new Object();
	query.act = "download_youhui";
	query.data_id = id;
	$.ajax({
		url:ajax_url,
		data:query,
		type:"POST",
		dataType:"json",
		success:function(obj){
			if(obj.status)
			{
				
				$.showSuccess(obj.info,function(){
					if(obj.jump)
						location.href = obj.jump;
					});
			}
			else
			{	
				if(obj.info)
				{
					$.showErr(obj.info,function(){
						if(obj.jump)
							location.href = obj.jump;
						});
				}
				else
				{
					if(obj.jump)
						location.href = obj.jump;
				}
				
			}
		}
	});
}
