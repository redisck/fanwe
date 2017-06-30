var deal_sender;

function deal_sender_fun()
{
    if(IS_RUN_CRON==1)
    {
    	window.clearInterval(deal_sender);
    	$.ajax({
    		url:DEAL_MSG_URL,
    		dataType: "jsonp",
            jsonp: 'callback',  
            type:"GET",
            global:false,
    		success:function(data)
    		{
    		    deal_sender = window.setInterval("deal_sender_fun()",send_span);
    			if(data.count!='0')
    			IS_RUN_CRON = 1;
    			else
    			IS_RUN_CRON = 0;
    		}
    	});
	}
}

$(document).ready(function(){
	
	//关于队列群发检测
	deal_sender = window.setInterval("deal_sender_fun()",send_span);
});