

//设置队列的检测定时器

function promote_sender_fun()
{
	window.clearInterval(promote_sender);
	$.ajax({
		url: "index.php?ctl=cron&act=promote_msg_list",
		dataType: "jsonp",
        jsonp: 'callback',  
		success:function(data)
		{
			if(!isNaN(data.count)&&parseInt(data.count)>=1)
			{						
				$("#promote_msg").show();			
			}
			else
			{
				$("#promote_msg").hide();
			}
			promote_sender = window.setInterval("promote_sender_fun()",send_span);
		}
	});
}


function deal_sender_fun()
{
	window.clearInterval(deal_sender);
	$.ajax({
		url: "index.php?ctl=cron&act=deal_msg_list",
		dataType: "jsonp",
        jsonp: 'callback',  
		success:function(data)
		{
			if(!isNaN(data.count)&&parseInt(data.count)>=1)
			{						
				$("#deal_msg").show();			
			}
			else
			{
				$("#deal_msg").hide();
			}
			deal_sender = window.setInterval("deal_sender_fun()",send_span);
		}
	});
}

var promote_sender = window.setInterval("promote_sender_fun()",send_span);
var deal_sender = window.setInterval("deal_sender_fun()",send_span);	