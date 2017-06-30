$(document).ready(function(){
	init_ui_button();
	init_ui_textbox();
	init_ui_select();
	init_ui_lazy();
	init_ui_starbar();
	init_ui_confirm();
	
	 $(".h_search").click(function(){
       $(".pull_down").toggle();
       $(".biz_pull_down").toggle();
     });
	 

	 $(".Client").find(".close_but").bind("click",function(){
		 $(".Client").hide();
		 var query = new Object();
		 query.act = "close_appdown";
		 $.ajax({
			 url:AJAX_URL,
			 data:query,
			 type:"POST",
			 success:function(){
				 
			 },
			 error:function(o){
				 alert(o.responseText);
			 }
		 });
		
	 });
});

//以下是处理UI的公共函数
function init_ui_confirm()
{
	$("a.confirm").bind("click",function(){
		var href = $(this).attr("href");
		$.showConfirm("确认操作吗？",function(){
			location.href = href;
		});
		
		return false;
	});
}
function init_ui_lazy()
{
	$.refresh_image = function(){
		$("img[lazy][!isload]").ui_lazy({placeholder:LOADER_IMG});
	};		
	$.refresh_image();
	$(window).bind("touchmove", function(e){
		$.refresh_image();
	});	
	$(window).bind("scroll", function(e){
		$.refresh_image();
	});	
	
}

function init_ui_starbar()
{
	$("input.ui-starbar[init!='init']").each(function(i,ipt){
		$(ipt).attr("init","init");  //为了防止重复初始化
		$(ipt).ui_starbar();		
	});
}


var droped_select = null; //已经下拉的对象
var uiselect_idx = 0;
function init_ui_select()
{
	$("select.ui-select[init!='init']").each(function(i,o){
		uiselect_idx++;
		var id = "uiselect_"+Math.round(Math.random()*10000000)+""+uiselect_idx;
		var op = {id:id};
		$(o).attr("init","init");  //为了防止重复初始化		
		$(o).ui_select(op);		
	});
	
	//追加hover的ui-select
	$("select.ui-drop[init!='init']").each(function(i,o){
		uiselect_idx++;
		var id = "uiselect_"+Math.round(Math.random()*10000000)+""+uiselect_idx;
		var op = {id:id,event:"hover"};
		$(o).attr("init","init");  //为了防止重复初始化		
		$(o).ui_select(op);		
	});
	
	$(document.body).click(function(e) {		
		if($(e.target).attr("class")!='ui-select-selected'&&$(e.target).parent().attr("class")!='ui-select-selected')
    	{
			$(".ui-select-drop").fadeOut("fast");
			$(".ui-select").removeClass("dropdown");
			droped_select = null;
    	}
		else
		{			
			if(droped_select!=null&&droped_select.attr("id")!=$(e.target).parent().attr("id"))
			{
				$(droped_select).find(".ui-select-drop").fadeOut("fast");
				$(droped_select).removeClass("dropdown");
			}
			droped_select = $(e.target).parent();
		}
	});
	
}

function init_ui_button()
{
	
	$("button.ui-button[init!='init']").each(function(i,o){
		$(o).attr("init","init");  //为了防止重复初始化		
		$(o).ui_button();		
	});
	
}

function init_ui_textbox()
{
	
	$(".ui-textbox[init!='init'],.ui-textarea[init!='init']").each(function(i,o){
		$(o).attr("init","init");  //为了防止重复初始化		
		$(o).ui_textbox();		
	});

}
//ui初始化结束



function init_sms_btn()
{
	$(".login-panel").find("button.ph_verify_btn[init_sms!='init_sms']").each(function(i,o){
		$(o).attr("init_sms","init_sms");
		var lesstime = $(o).attr("lesstime");
		var divbtn = $(o).next();
		divbtn.attr("form_prefix",$(o).attr("form_prefix"));
		divbtn.attr("lesstime",lesstime);
		if(parseInt(lesstime)>0)
		init_sms_code_btn($(divbtn),lesstime);	
	});
}
//关于短信验证码倒计时
function init_sms_code_btn(btn,lesstime)
{

	$(btn).stopTime();
	$(btn).removeClass($(btn).attr("rel"));
	$(btn).removeClass($(btn).attr("rel")+"_hover");
	$(btn).removeClass($(btn).attr("rel")+"_active");
	$(btn).attr("rel","disabled");
	$(btn).addClass("disabled");	
	$(btn).find("span").html("重新获取("+lesstime+")");
	$(btn).attr("lesstime",lesstime);
	$(btn).everyTime(1000,function(){
		var lt = parseInt($(btn).attr("lesstime"));
		lt--;
		$(btn).find("span").html("重新获取("+lt+")");
		$(btn).attr("lesstime",lt);
		if(lt==0)
		{
			$(btn).stopTime();
			$(btn).removeClass($(btn).attr("rel"));
			$(btn).removeClass($(btn).attr("rel")+"_hover");
			$(btn).removeClass($(btn).attr("rel")+"_active");
			$(btn).attr("rel","light");
			$(btn).addClass("light");
			$(btn).find("span").html("发送验证码");
		}
	});
}



/*验证*/
$.minLength = function(value, length , isByte) {
	var strLength = $.trim(value).length;
	if(isByte)
		strLength = $.getStringLength(value);
		
	return strLength >= length;
};

$.maxLength = function(value, length , isByte) {
	var strLength = $.trim(value).length;
	if(isByte)
		strLength = $.getStringLength(value);
		
	return strLength <= length;
};
$.getStringLength=function(str)
{
	str = $.trim(str);
	
	if(str=="")
		return 0; 
		
	var length=0; 
	for(var i=0;i <str.length;i++) 
	{ 
		if(str.charCodeAt(i)>255)
			length+=2; 
		else
			length++; 
	}
	
	return length;
};

$.checkMobilePhone = function(value){
	if($.trim(value)!='')
	{
		var reg = /^(1[34578]\d{9})$/;
		return reg.test($.trim(value));
	}		
	else
		return true;
};
$.checkEmail = function(val){
	var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/; 
	return reg.test(val);
};


/**
 * 检测密码的复杂度
 * @param pwd
 * 分数 1-2:弱 3-4:中 5-6:强 
 * 返回 0:弱 1:中 2:强 -1:无
 */
function checkPwdFormat(pwd)
{
	var regex0 = /[a-z]+/;  
	var regex1 = /[A-Z]+/;  
	var regex2 = /[0-9]+/;
	var regex3 = /\W+/;   //符号
	var regex4 = /\S{6,8}/;	    
	var regex5 = /\S{9,}/;   
	
	
	var result = 0;
	
	if(regex0.test(pwd))result++;
	if(regex1.test(pwd))result++;
	if(regex2.test(pwd))result++;
	if(regex3.test(pwd))result++;
	if(regex4.test(pwd))result++;
	if(regex5.test(pwd))result++;
	
	if(result>=1&&result<=2)
		result=0;
	else if(result>=3&&result<=4)
		result=1;
	else if(result>=5&&result<=6)
		result=2;
	else 
		result=-1;
	
	return result;
}

/**
 * 点评星星初始化
 */
function init_dp_star(){
	$(".stars").each(function(i,stars){
		var avg_point = $(stars).attr("data"); //评分
		var start_cut = parseInt(avg_point-1);	//选中的星星数
		var start_half = '';	//小数点后的分数
		var half_width = 0;	//有小数的星星百分百宽度
		
		var star_html = '<i class="text-icon icon-star"></i>'
					+'<i class="text-icon icon-star"></i>'
					+'<i class="text-icon icon-star"></i>'
					+'<i class="text-icon icon-star"></i>'
					+'<i class="text-icon icon-star"></i>';
		$(stars).html(star_html);
		if(avg_point.indexOf(".")>0){
			start_half = "0"+avg_point.substring(avg_point.indexOf("."),avg_point.length);
			half_width = (parseFloat(start_half)*100).toFixed(1);
		}
			
		if(avg_point>1)
			$(stars).find(".text-icon:gt("+start_cut+")").removeClass("icon-star").addClass("icon-star-gray");
		else
			$(stars).find(".text-icon").removeClass("icon-star").addClass("icon-star-gray");
		
		if(start_half.length>0){
			$(stars).find(".text-icon").eq(avg_point).html('<i class="text-icon icon-star-half" style="width:'+half_width+'%"></i>');
		}
	});
	
}

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
			var tag = obj.tag;
			var html = obj.html;
			if(tag==1) //取消关注
			{
				$(o).html(html);
			}
			if(tag==2)//关注TA
			{
				$(o).html(html);
			}
			if(tag==3)//不能关注自己
			{
				$.showSuccess(html);
			}
			if(tag==4)
			{
				$.showErr(obj.info,function(){
					if(obj.jump){
						window.location = obj.jump;
					}
				});
			}
				
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function weixin_login()
{
	var url = location.href;
	if(url.indexOf("?")==-1)
	{
		url+="?weixin_login=1";
	}
	else
	{
		url+="&weixin_login=1";
	}
	location.href = url;
}
