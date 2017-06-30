$(document).ready(function(){
	init_ui_button();
	init_ui_textbox();
	init_ui_select();
	init_ui_checkbox();
	init_ui_radiobox();
	init_ui_lazy();
	init_ui_starbar();
	
	init_drop_nav();
	init_cate_tree();
	init_gotop();
	 
	nav_show();
	change_city();
	init_clear_history();
	init_clear_history_head();
	
	//顶部菜单搜索为空是截获
	$("form[name='search_form']").bind("submit",function(){
		if($(this).find(".search_keyword").val()=="")
		{
			$(this).find(".search_keyword").focus();
			return false;
		}
	});
	

	init_drop_head_cart();

});

//以下是处理UI的公共函数
function init_ui_lazy()
{
	$.refresh_image = function(){
		$("img[lazy][!isload]").ui_lazy({placeholder:LOADER_IMG});
	};		
	$.refresh_image();
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

function init_ui_checkbox()
{
	$("label.ui-checkbox[init!='init']").each(function(i,ImgCbo){
		$(ImgCbo).attr("init","init");  //为了防止重复初始化
		$(ImgCbo).ui_checkbox();		
	});
}

function init_ui_radiobox()
{
	$("label.ui-radiobox[init!='init']").each(function(i,ImgCbo){
		$(ImgCbo).attr("init","init");  //为了防止重复初始化
		$(ImgCbo).ui_radiobox();		
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

/**
 * 屏幕兼容
 */
function init_screen_size()
{
	if($(window).width()<1050)
	{
		$(".main_layout").removeClass("wrap_full");
		$(".main_layout").removeClass("wrap_full_w");
		$(".main_layout").addClass("wrap_full");


	}
	if($(window).width()>1200)
	{
		$(".main_layout").removeClass("wrap_full");
		$(".main_layout").removeClass("wrap_full_w");
		$(".main_layout").addClass("wrap_full_w");

	}
	
	$(window).resize(function(){
		
		
		if($(window).width()<1050)
		{
			$(".main_layout").removeClass("wrap_full");
			$(".main_layout").removeClass("wrap_full_w");
			$(".main_layout").addClass("wrap_full");


		}
		if($(window).width()>1200)
		{
			$(".main_layout").removeClass("wrap_full");
			$(".main_layout").removeClass("wrap_full_w");
			$(".main_layout").addClass("wrap_full_w");
		}
		
		
	});
}



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


function form_err(ipt,txt){
	$(ipt).parent().parent().find(".form_tip").html("<span class='error'>"+txt+"</span>");
}
function form_success(ipt,txt){
	if(txt!="")
	$(ipt).parent().parent().find(".form_tip").html("<span class='success'>"+txt+"</span>");
	else
		$(ipt).parent().parent().find(".form_tip").html("<span class='success'>&nbsp;</span>");
}
function form_tip(ipt,txt){
	$(ipt).parent().parent().find(".form_tip").html("<span class='tip'>"+txt+"</span>");
}
function form_tip_clear(ipt)
{
	$(ipt).parent().parent().find(".form_tip").html("");
}

//绑定主菜单的相关操作
function init_drop_nav()
{
	$("#drop_nav").find(".drop_box").hide();
	$("#drop_nav[ref!='no_drop']").hover(function(){	
		$("#drop_nav").stopTime();
		if($("#drop_nav").find(".drop_box dl").length>0)
		{
			$("#drop_nav").oneTime(300, function(){
				$("#drop_nav").find(".drop_box").slideDown("fast");
				$("#drop_nav .drop_title i").addClass("up");
			});		
		}
		
	},function(){
		$("#drop_nav").stopTime();		
		$(this).find(".drop_box").fadeOut("fast");
		$("#drop_nav .drop_title i").removeClass("up");
	});
}

//绑定分类树
function init_cate_tree()
{	
	$(".cate_tree dl").find(".pop_nav").hide();
	$(".cate_tree dl").hover(function(){		
		$(this).stopTime();
		$(this).oneTime(200, function(){
			$(this).find(".pop_nav").fadeIn("fast");
		});	
	},function(){
		$("#a").html($("#a").html()+"hoverout_");
		$(this).stopTime();
		$(this).oneTime(200, function(){
			$(this).find(".pop_nav").fadeOut("fast");
		});	
	});
}




function init_gotop()
{

	$(window).scroll(function(){		
		
		if($.browser.msie && $.browser.version =="6.0")
		{
			$("#go_top").css("top",$(document).scrollTop()+$(window).height()-80);
		}	
		
		if($(document).scrollTop()>0)
			$("#go_top").fadeIn();
		else
			$("#go_top").fadeOut();
	});	
	
	if($.browser.msie && $.browser.version =="6.0")
	$("#go_top").css("top",$(document).scrollTop()+$(window).height()-80);
	if($(document).scrollTop()>0)
		$("#go_top").fadeIn();
	else
		$("#go_top").fadeOut();
	
	$("#go_top").bind("click",function(){
		$("html,body").animate({scrollTop:0},"fast","swing",function(){
		});
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
 * 验证用户字段
 * @param field 字段名称
 * @param value 值
 * @param user_id	会员ID
 * @param ipt	输入框
 */
var allow_ajax_check = true;
function ajax_check_field(field,value,user_id,ipt)
{
	if(!allow_ajax_check)return;
	var query = new Object();
	query.act = "check_field";
	query.field = field;
	query.value = value;
	query.user_id = user_id;
	$.ajax({
		url:AJAX_URL,
		dataType: "json",
		data:query,
        type:"POST",
        global:false,
		success:function(data)
		{
		    if(!data.status)			    		   
		    {
		    	if(data.field)
		    	{
		    		form_err(ipt,data.info);
		    	}
		    	else
		    	$.showErr(data.info);
		    }
		    else
		    {
		    	form_success(ipt,data.info);
		    }
		}
	});
}


/**
 * 更新初始化购物车提示
 */
function init_cart_tip()
{
	var query = new Object();
	query.act = "cart_tip";
	$.ajax({
		url:AJAX_URL,
		data:query,
		dataType:"json",
        type:"POST",
        global:false,
		success:function(data)
		{
		    $("#cart_tip").html(data.html);
		    init_drop_head_cart();
		    
		}
	});
}

function init_clear_history()
{
	$(".clear_history").bind("click",function(){
		var query = new Object();
		query.act = "clear_history";
		query.type = $(this).attr("type");
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){
				if(obj.status)
				{
					location.reload();
				}
			}
		});
	});
}

function init_clear_history_head()
{	
	$(".clear_history_head").bind("click",function(){
		var query = new Object();
		query.act = "clear_history";		
		query.type = "alldeal";
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"POST",
			success:function(obj){				
				if(obj.status)
				{
					location.reload();
				}				
			}
		});
	});
}


function init_drop_user()
{ 
	$("#user_drop_box").hide();
	if($("#user_drop").length>0)
	{
		$("#user_drop").hover(function(){		
			$("#user_drop_box").stopTime();
			$("#user_drop_box").oneTime(300,function(){
				var left = $("#user_drop").position().left;
				$("#user_drop_box").css("left",left);
				$("#user_drop_box").css("top",31);	
				$("#user_drop_box").slideDown("fast");	
			});						
		},function(){
			$("#user_drop_box").stopTime();
			$("#user_drop_box").oneTime(300,function(){
				$("#user_drop_box").slideUp("fast");
			});
		});
		
		$("#user_drop_box").hover(function(){
	
			$("#user_drop_box").stopTime();
			$(this).show();
		},function(){
			$("#user_drop_box").stopTime();
			$("#user_drop_box").oneTime(300,function(){
				$("#user_drop_box").slideUp("fast");
			});
		});
	}
}

function init_drop_head_history()
{
	$("#head_history_drop_box").hide();
	if($("#head_history").length>0)
	{
		$("#head_history").hover(function(){		
			$("#head_history_drop_box").stopTime();
			$("#head_history_drop_box").oneTime(300,function(){
				var left = $("#head_history").position().left-161;
				$("#head_history_drop_box").css("left",left);
				$("#head_history_drop_box").css("top",31);	
				$("#head_history_drop_box").slideDown("fast");	
			});						
		},function(){
			$("#head_history_drop_box").stopTime();
			$("#head_history_drop_box").oneTime(300,function(){
				$("#head_history_drop_box").slideUp("fast");
			});
		});
		
		$("#head_history_drop_box").hover(function(){
			$("#head_history_drop_box").stopTime();
			$(this).show();
		},function(){
			$("#head_history_drop_box").stopTime();
			$("#head_history_drop_box").oneTime(300,function(){
				$("#head_history_drop_box").slideUp("fast");
			});
		});
	}
}

function init_drop_head_cart()
{
	$("#head_cart_drop_box").hide();
	$("#cart_drop_box").hover(function(){		
		$("#head_cart_drop_box").stopTime();
		$("#head_cart_drop_box").oneTime(300,function(){
			var left = $("#cart_drop_box").position().left-122;
			$("#head_cart_drop_box").css("left",left);
			$("#head_cart_drop_box").css("top",31);	
			$("#head_cart_drop_box").slideDown("fast");	
		});						
	},function(){
		$("#head_cart_drop_box").stopTime();
		$("#head_cart_drop_box").oneTime(300,function(){
			$("#head_cart_drop_box").slideUp("fast");
		});
	});
	
	$("#head_cart_drop_box").hover(function(){
		$("#head_cart_drop_box").stopTime();
		$(this).show();
	},function(){
		$("#head_cart_drop_box").stopTime();
		$("#head_cart_drop_box").oneTime(300,function(){
			$("#head_cart_drop_box").slideUp("fast");
		});
	});
	
	$("#head_cart_drop_box .deal-item .deal-price-w a").bind("click",function(){
		var id = $(this).attr("rel");
		$.showConfirm("确定要从购物车中移除该项目吗？",function(){
			//执行删除
			del_cart(id);
		});			
	});
}

/**
 * 城市切换
 */
function change_city()
{
	if( typeof(CITY_COUNT)!="undefined"){
			if(CITY_COUNT<=20){
				
				    $(".city").hover(
				            function(){
						    	var city_top=$(".city_switch").offset().top;
						    	var city_left=$(".city_switch").offset().left;
								$(this).stopTime();
								$(this).oneTime(200, function(){
						        	$(".city_list").fadeIn("fast");
							    	$(".city_list").css({ top: city_top+25, left:city_left});					
								});	
	    	
				            },function(){
				        		$(this).stopTime();
								$(this).oneTime(200, function(){		            		
				            		if (!$(".city_list").hasClass('hover')) 
				            		{ 
								        	$(".city_list").fadeOut("fast");							       
				            		} 
				            	});		            	
				            }
				     );						
				
				    $(".city_list").hover(
				            function(){
				            	$(this).show(); 
				                $(this).addClass("hover");				                
				            },
				            function(){
				                $(this).removeClass("hover");
						        	$(this).fadeOut("fast");					        
				            }
				     );					    
		    
				    
				    $(".city_item").hover(
				            function(){
				            	$(".city_item").removeClass("mover"); 
				                $(this).addClass("mover");                                
				            },
				            function(){
				                $(this).removeClass("mover");                            
				            }
				     );		
				
			}else{
				$(".city_switch,.city_name").bind("click",function(){
					location.href = $(this).attr("jump");
				});		
			}

	}
}


function show_signin_message(signin_result)
{
	if(signin_result.status)
	{
		var msg = "<span class='signin_msg'>"+signin_result.info+"</span>";
		if(signin_result.point||signin_result.score||signin_result.money)
		{
			msg+="<span class='signin_price'>";
			if(signin_result.money)
				msg+=signin_result.money+"&nbsp;";
			if(signin_result.score)
				msg+=signin_result.score+"&nbsp;";
			if(signin_result.point)
				msg+=signin_result.point+"&nbsp;";
			msg+="</span>";
		}
		$.showSuccess(msg);
	}
	
}

//导航菜单显示
function nav_show()
{
	var nav=$(".nav_bar");
	var drop_nav=$("#drop_nav");
	if(drop_nav.length>0){
		var drop_nav_width=drop_nav.width()+2;
	}else{
		var drop_nav_width=0;
	}
	if(nav.length>0){
		var max_length=$(".nav_bar .main_layout").width();
		if($(window).width()<max_length) max_length=$(window).width();
		var nav_li=$(".main_nav li");
		for(var i=0;i<nav_li.length;i++){
			drop_nav_width+=$(nav_li[i]).width()+2;
			if(drop_nav_width>max_length) {
				$(nav_li[i]).hide();
		
			}
		}
	}	
}



function del_cart(id)
{
	var query = new Object();
	query.act = "del_cart";
	if(id)query.id = id;
	$.ajax({
		url:AJAX_URL,
		data:query,
		type:"POST",
		dataType:"json",
		success:function(obj){
			if(obj.status)
			{
				if(typeof(load_cart_list)=="function")
				load_cart_list();					
			}
			
			init_cart_tip();
		}
	});	
}

function app_download(func){
	$(".android,.ios").bind("click",function(){
		
		var down_url=$(this).find("a").attr('down_url');
		$.weeboxs.open(down_url, {boxid:"app_box",contentType:'ajax',showButton:false,title:"手机APP下载",width:650, type:'wee',onopen:function(){init_ui_button(); init_ui_textbox();  init_login_panel();init_ui_checkbox();},onclose:func});	
		
	})
	
}


//关于二维码扫描UI显示
function show_scan_box(dom)
{
	if(QRCODE_ON==1)
	{
		$(dom).find("*[rel='qrcode']").css("opacity","1");
		$(dom).find("*[rel='qrcode']").animate({ 
			opacity: 'show'
		  },  { duration: 100,queue:false });
	}
	
}
function hide_scan_box(dom)
{
	if(QRCODE_ON==1)
	{
		$(dom).find("*[rel='qrcode']").css("opacity","1");
		$(dom).find("*[rel='qrcode']").animate({ 
			opacity: 'hide'
		  },  { duration: 100,queue:false });
	}
	
}