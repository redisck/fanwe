$(document).ready(function(){
	init_countdown();
	init_buy_choose();
	init_dp_star();
	$(".J_location_more").click(function(){
        $(".business_display").toggleClass("business_blank");
    });
	init_addcart();
});

function init_addcart()
{
	$("#goods-form").bind("submit",function(){
		
		var query = $(this).serialize();
		var action = $(this).attr("action");

		$.ajax({
			url:action,
			data:query,
			type:"POST",
			dataType:"json",
			success:function(obj){
				if(obj.status==-1)
				{
					location.href = obj.jump;
				}
				else if(obj.status)
				{
					if(obj.jump!="")
					location.href = obj.jump;
				}
				else
				{
					$.showErr(obj.info);
				}
			}			
		});
		
		
		return false;
	});
}

function WapTosinaweibo(_rt,_ru) {
    _rt = encodeURI(_rt);
    _ru = encodeURIComponent(_ru);
    var _u = 'http://v.t.sina.com.cn/share/share.php?title='+_rt+'&url='+_ru;
    window.location.href = _u;
}

function init_buy_choose()
{
	$(".package_choose").find("a").bind("click",function(){
		var btn = $(this);
		if(btn.attr("active")=="active")
		{
			$(btn).attr("active","");
			$(btn).removeClass("current");
			$(btn).parent().find("input").val("0");
		}
		else
		{
			$(btn).parent().find("a").removeClass("current");
			$(btn).parent().find("a").attr("active","");
			$(btn).attr("active","active");
			$(btn).addClass("current");
			$(btn).parent().find("input").val($(btn).attr("rel"));
		}
		init_price();		
	});
}

function init_price()
{
	var add_price = 0;
	var current_price = parseFloat($("#current_price").attr("rel"));
	var is_choose_done = true;
	$(".deal_attr_ipt").each(function(i,o){
		var attr_id = $(o).val();		
		if(attr_id==0)is_choose_done = false;
		add_price+=parseFloat($("a[rel='"+attr_id+"']").attr("price"));
		
	});
	if(is_choose_done)
		$("#current_price").html(current_price+add_price);
	else
		$("#current_price").html(current_price);
}
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

function add_collect(id,obj){
	var query = new Object();
	query.id = id;
	query.act = "add_collect";
	$.ajax({
				url: ajax_url,
				data: query,
				dataType: "json",
				type: "post",
				success: function(obj){
					if(obj.status == 1){
						$.showSuccess(obj.info);								
					}else if(obj.status==-1)
					{
						ajax_login();
					}else{
						$.showErr(obj.info);
					}
				},
				error:function(ajaxobj)
				{
//					if(ajaxobj.responseText!='')
//					alert(ajaxobj.responseText);
				}
	});
}
