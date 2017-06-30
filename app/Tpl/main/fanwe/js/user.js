
var timer; //定时器
userCard=(function(){
	var cardDiv;  //名片dom对象
	var userCardStr="userCard"; //名片dom对象ID前缀
	var qObj,userId;	//触发对象以及用户ID
	var mout=function(){
		//移出事件
		 timer = setTimeout(function(){
	          cardDiv.fadeOut("fast");
	      },200);
	};
	var mover=function(){
		//移入事件
		clearTimeout(timer);
	};	
	var createLoadDiv=function(){
		//创建名片dom对象，首次载入时用
		cardDiv=$("<div id='"+userCardStr+userId+"' style='display:none;' class='nameCard'><div class='load'></div></div>");
		$("body").append(cardDiv);
	};	
	var resetXY=function(){
		//重置名片dom对象坐标

		var offset = qObj.offset();		
		var of_left = 2;
		if(offset.left+236+qObj.width()>$(document).width())
		{
			of_left = offset.left - 236;
		}
		else
		{
			of_left +=  offset.left+qObj.width();
		}
		cardDiv.css( {
			top : offset.top,
			left : of_left
		});
	};	
	var showUserCard = function(){
		//显示名片
		resetXY();
		cardDiv.fadeIn("fast");	
	};
	
	var loadCard=function(){		
		$(".nameCard").hide();
		cardDiv=$("#"+userCardStr+userId);		
		if(!cardDiv.length){
			createLoadDiv();
			showUserCard();		
			cardDiv.load(AJAX_URL,{act:"usercard",uid:userId});
		}else{
			//已有名片对象时
			showUserCard(); //直接显示
		};
		//为名片对象与触发对象绑定事件
		cardDiv.hover(mover,mout);
		qObj.hover(mover,mout);
	};
	
	return {
		load : function(e,id){//加载id的名片。e:当前DOM元素,直接写this; id:名片上的用户ID		
	
				clearTimeout(timer);
				if(e===undefined || id===undefined || isNaN(id) || id<1){
					return false;
				};				
				qObj=$(e); //为触发对象赋值
				userId=id; //用户ID
				//加载名片
				loadCard(); //加载名片
			}
	  	};
})();

function focus_user(uid,o)
{
	var query = new Object();
	query.act = "focus";
	query.uid = uid;
	$.ajax({ 
		url: AJAX_URL,
		data: query,
		dataType: "json",
		success: function(obj){				
			if(obj.tag==1)
			{
				$(o).removeClass("add_focus");
				$(o).removeClass("remove_focus");
				$(o).addClass("remove_focus");
				$(o).html(obj.html);
			}
			if(obj.tag==2)
			{
				$(o).removeClass("add_focus");
				$(o).removeClass("remove_focus");
				$(o).addClass("add_focus");
				$(o).html(obj.html);
			}
			if(obj.tag==3)
			{
				$.showSuccess(obj.html);
			}
			if(obj.tag==4)
			{
				ajax_login();
			}
				
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}