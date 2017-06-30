$(document).ready(function(){
	init_gallary();
	init_countdown();
	init_buy_choose();
	if($("#supplier_deal").length>0)
	init_load_supplier_deal();
	init_content_nav();
	collect_deal();
	init_kf_qq();
	app_download();
});
var is_load_supplier_deal = false;

/**
 * 加载商户其他团购
 */
function init_load_supplier_deal()
{
	$.bind_supplier_deal_pager = function(query){
		$("#supplier_deal").find(".pages a").bind("click",function(){
			
			var ajax_url = $(this).attr("href");			
			$("#supplier_deal").html("<div class='loading'></div>");
			is_load_supplier_deal = true;
			$.ajax({
				url:ajax_url,
				data:query,
				dataType:"json",
				type:"post",
				global:false,
				success:function(obj){						
					$("#supplier_deal").html(obj.html);
					$.bind_supplier_deal_pager(query);
				}				
			});
			
			return false;
		});
	};
	
	
	$.load_supplier_deal = function(){
		var scrolltop = $(window).scrollTop();
		var loadheight = $("#supplier_deal").offset().top;
		var windheight = $(window).height();
		if(!is_load_supplier_deal)
		{			
			if(windheight+scrolltop>=loadheight)
			{
				var query = new Object();
				query.deal_id = $("#supplier_deal").attr("deal_id");
				query.supplier_id = $("#supplier_deal").attr("supplier_id");
				query.supplier_name = $("#supplier_deal").attr("supplier_name");
				query.act = "load_supplier_deal";
				$("#supplier_deal").html("<div class='loading'></div>");
				is_load_supplier_deal = true;
				$.ajax({
					url:AJAX_URL,
					data:query,
					dataType:"json",
					type:"post",
					global:false,
					success:function(obj){						
						$("#supplier_deal").html(obj.html);
						$.bind_supplier_deal_pager(query);
					}				
				});
			}
		}
		
	};
	$.load_supplier_deal();
	$(window).bind("scroll", function(e){
		$.load_supplier_deal();
	});
	
}


/**
 * 初始化图集工具
 */
function init_gallary(){
	$("#small_pic").find("a").bind("click",function(){
		if($(this).attr("class")=="active")return;
		var img = $(this).find("img");
		$("#small_pic").find("a").removeClass("active");
		$(this).addClass("active");
		$("#big_pic").attr("origin",$(img).attr("origin"));
		
		//$("#big_pic").attr("src",$(img).attr("big_pic"));		
		//$("#big_pic").attr("data-src",$(img).attr("big_pic"));
		//$("#big_pic").removeAttr("isload");
		//$("#big_pic").ui_lazy({placeholder:LOADER_IMG});
		$("#big_pic").ui_lazy({placeholder:LOADER_IMG,src:$(img).attr("big_pic"),refresh:true});
	});
	
	$(".pic_hidden").hover(function(){
		$(this).find(".pre").animate({ 
		    left: 0
		  },  { duration: 100,queue:false });
		$(this).find(".next").animate({ 
		    right: 0
		  }, { duration: 100,queue:false });
	},function(){
		$(this).find(".pre").animate({ 
		    left: -29
		  },  { duration: 100,queue:false });
		$(this).find(".next").animate({ 
		    right: -29
		  }, { duration: 100,queue:false });
	});
	
	
	var img_total = $("#small_pic").find("a").length;
	var current_idx = 0;
	var width_unit = 94; //每次移的宽度
	$(".pic_hidden").find(".pre").addClass("disable_tag");	
	$(".pic_hidden").find(".pre").bind("click",function(){		
		if(current_idx>0)
		{
			current_idx--;
			$(".pic_hidden").find(".next").removeClass("disable_tag");
			if(current_idx==0)
			{
				$(this).addClass("disable_tag");			
			}
			else
			{
				$(this).removeClass("disable_tag");	
			}			
		}
		$("#small_pic").animate(
			{left:0-94*current_idx},{ duration: 100,queue:false }
		);
	});
	$(".pic_hidden").find(".next").bind("click",function(){
		if(current_idx<img_total-5)
		{
			current_idx++;
			$(".pic_hidden").find(".pre").removeClass("disable_tag");
			if(current_idx==img_total-5)
			{
				$(this).addClass("disable_tag");				
			}
			else
			{				
				$(this).removeClass("disable_tag");
			}			
		}
		$("#small_pic").animate(
			{left:0-94*current_idx},{ duration: 100,queue:false }
		);
		
	});
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


/**
 * 初始化购物的选择（包括规格，数量）
 * 1. 规格必需选满，选满后如有递加价格，显示新价格，如有新库存显示新库存，并修正购买数量
 * 2. 购买数量的选择
 * 3. 更新购买按钮
 */

function init_buy_choose()
{

	//购物按钮事件
	$.buy_action = function(){

		var is_choose_all = true; //是否已选中所有规格
		var attr_checked_ids = [];
		$("#main_package_choose .package_choose").each(function(i,o){		
			if($(o).attr("is_choose"))
			{
				attr_checked_ids.push($(o).find("a[active='true']").attr("rel"));
			}
			else
			{
				is_choose_all = false;  //有一项规格未选中即为未选满
			}			
		});	
		var id = deal_id;
		var number = $("#deal_num").val();
		if(is_choose_all)
			 add_cart(id,number,attr_checked_ids);
	};
	$.init_buy_num_ui = function(buy_num){
		if(buy_num==1)
		{
			$(".num_choose .less").addClass("num_choose_disabled");
		}
		else
		{
			$(".num_choose .less").removeClass("num_choose_disabled");
		}
		if(buy_num>=9999)
		{
			$(".num_choose .increase").addClass("num_choose_disabled");
		}
		else
		{
			$(".num_choose .increase").removeClass("num_choose_disabled");
		}
	};
	
	//填写最小的购物数量
	if(deal_user_min_bought==0)
	{
		$("#deal_num").val(1);
		$.init_buy_num_ui(1);
	}
	else
	{
		$("#deal_num").val(deal_user_min_bought);
		$.init_buy_num_ui(deal_user_min_bought);
	}
	
	
	
	//更新购物UI
	$.init_buy_ui = function(){
		var is_choose_all = true; //是否已选中所有规格
		var is_stock = true;      //库存是否满足
		var stock = deal_stock;   //无规格时的库存数
		var deal_show_price = deal_price;
		var deal_show_buy_count = deal_buy_count;	
		var deal_remain_stock = -1;  //剩余库存 -1:无限
		
		
		
		//更新规格选项卡UI
		var attr_checked_ids = new Array();
		$("#main_package_choose .package_choose").each(function(i,o){			
			$(o).find("a").removeClass("active");
			if($(o).attr("is_choose"))
			{
				$(o).find("a[active='true']").addClass("active");
				deal_show_price+=parseFloat($(o).find("a[active='true']").attr("price"));
				attr_checked_ids.push($(o).find("a[active='true']").attr("rel"));
			}
			else
			{
				is_choose_all = false;  //有一项规格未选中即为未选满
			}	
			
		});		
		
		//开始计算库存
		attr_checked_ids = attr_checked_ids.sort();
		attr_checked_ids_str = attr_checked_ids.join("_");
		if($("#main_package_choose .package_choose").length>0)
		{			
			var attr_spec_stock_cfg = deal_attr_stock_json[attr_checked_ids_str];
			if(attr_spec_stock_cfg)
			{
				deal_show_buy_count = attr_spec_stock_cfg['buy_count'];
				stock = attr_spec_stock_cfg['stock_cfg'];
			}
			else
			{
				var has_stock_attr = false;
				for(var k=0;k<attr_checked_ids.length;k++)
				{
					var key = attr_checked_ids[k];
					attr_spec_stock_cfg = deal_attr_stock_json[key];
					if(attr_spec_stock_cfg)
					{
						stock = attr_spec_stock_cfg['stock_cfg'];
						has_stock_attr = true;
						break;
					}
				}
				if(!has_stock_attr)
				stock = -1;
			}
		}
		if(stock>=0)
		{
			deal_remain_stock = stock;
			//if(deal_remain_stock<0)deal_remain_stock=0;
		}
		//更新库存显示
		if(deal_remain_stock>=0)
		{
			$("#stock_span").find("div").show();
			$("#stock_span").find(".inventory").html(deal_remain_stock);
		}
		else
		{
			$("#stock_span").find("div").hide();
		}
		
		//判断库存，并更新提示
		var buy_num = parseInt($("#deal_num").val());
		if(deal_remain_stock>=0)
		{
			if(deal_remain_stock<deal_user_min_bought)
			{
				//剩余库存小于最小购买量，表示库存不足
				is_stock = false;
				$("#stock_tips").html("每单最少购买"+deal_user_min_bought+"份,库存不足");
			}
			else if(buy_num>deal_remain_stock)
			{
				is_stock = false;
				$("#stock_tips").html("库存不足");
			}
			else if(buy_num<deal_user_min_bought)
			{
				is_stock = false;
				$("#stock_tips").html("每单最少购买"+deal_user_min_bought+"份");
			}
			else if(deal_user_max_bought>0&&buy_num>deal_user_max_bought)
			{
				is_stock = false;
				$("#stock_tips").html("每单最多购买"+deal_user_max_bought+"份");
			}
			else
			{
				$("#stock_tips").html("");
			}
		}
		else
		{
			if(buy_num<deal_user_min_bought)
			{
				is_stock = false;
				$("#stock_tips").html("每单最少购买"+deal_user_min_bought+"份");
			}
			else if(deal_user_max_bought>0&&buy_num>deal_user_max_bought)
			{
				is_stock = false;
				$("#stock_tips").html("每单最多购买"+deal_user_max_bought+"份");
			}
			else
			{
				$("#stock_tips").html("");
			}
		}
		

		
		//更新购物按钮
		var buy_btn = $("#buy_btn");
		var buy_btn_ui = buy_btn.next();
		if(is_choose_all&&is_stock)
		{
			//更新价格
			if(buy_type!=1)
			$("#deal_price").html(deal_show_price);
			
			buy_btn_ui.attr("rel","orange");
			buy_btn_ui.removeClass("disabled");
			buy_btn_ui.addClass("orange");
			
			buy_btn.unbind("click");
			buy_btn.bind("click",function(){
				$.buy_action();
			});
		}
		else
		{
			//更新价格
			if(is_choose_all)
			{
				if(buy_type!=1)
				$("#deal_price").html(deal_show_price);
			}
			else
			{
				if(buy_type!=1)
				$("#deal_price").html(deal_price);
			}
			
			
			buy_btn_ui.attr("rel","disabled");
			buy_btn_ui.removeClass("orange");
			buy_btn_ui.addClass("disabled");
			
			buy_btn.unbind("click");
		}
		
		
	};
	
	$.init_buy_ui();
	$("#main_package_choose .package_choose").each(function(i,o){
		is_choose_all = false;  //有规格选项时，选中为false
		$(o).find("a").bind("click",function(){
			var spec_btn = $(this);  //当前按中的A
			var is_active = spec_btn.attr("active");
			$(o).find("a").removeAttr("active");
			$(o).removeAttr("is_choose");
			if(!is_active)
			{
				spec_btn.attr("active",true);
				$(o).attr("is_choose",true);
			}
			
			$.init_buy_ui();
		});
	});
	
	//绑定购物数量
	$("#deal_num").bind("blur",function(){
		var buy_num = $(this).val();
		if(isNaN(buy_num)||parseInt(buy_num)<=0)buy_num=1;
		if(buy_num>9999)buy_num=9999;
		$.init_buy_num_ui(buy_num);
		$(this).val(buy_num);
		$.init_buy_ui();
	});
	$("#deal_num").bind("focus",function(){
		$(this).select();
	});
	$(".num_choose .less").bind("click",function(){
		var buy_num = $("#deal_num").val();
		buy_num = parseInt(buy_num) - 1;
		if(isNaN(buy_num)||parseInt(buy_num)<=0)buy_num=1;
		if(buy_num>9999)buy_num=9999;
		$.init_buy_num_ui(buy_num);
		$("#deal_num").val(buy_num);
		$.init_buy_ui();
	});
	$(".num_choose .increase").bind("click",function(){
		var buy_num = $("#deal_num").val();
		buy_num = parseInt(buy_num) + 1;
		if(isNaN(buy_num)||parseInt(buy_num)<=0)buy_num=1;
		if(buy_num>9999)buy_num=9999;
		$.init_buy_num_ui(buy_num);
		$("#deal_num").val(buy_num);
		$.init_buy_ui();
	});
}



//关于内容页的滚动定位,包含x店通用的点击滚动
function init_content_nav()
{	
	$("#flow_btn").bind("click",function(){
		var is_choose_all = true; //是否已选中所有规格
		var attr_checked_ids = [];
		$("#main_package_choose .package_choose").each(function(i,o){		
			if($(o).attr("is_choose"))
			{
				attr_checked_ids.push($(o).find("a[active='true']").attr("rel"));
			}
			else
			{
				is_choose_all = false;  //有一项规格未选中即为未选满
			}			
		});	
		var id = deal_id;
		var number = $("#deal_num").val();
		if(is_choose_all)
			 add_cart(id,number,attr_checked_ids);
		else
			$.showErr("请选择商品规格");
		
	});
	var is_show_fix = false;	
	var content_idx = -1;
	$.reset_nav = function(){
		if($.browser.msie && $.browser.version =="6.0")
		{
			$(".fix-nav").css("top",$(document).scrollTop());
		}	
		
		var navheight = $("#rel_nav").offset().top;
		var docheight = $(document).scrollTop();		
		if(docheight>navheight)		
		{			
			if(!is_show_fix)
			{	
				is_show_fix = true;
				$(".fix-nav").show();
				$("#rel_nav").css("visibility","hidden");
				if($.browser.msie && $.browser.version =="6.0")
				{						
					$(".fix-nav").css("width",900);			
				}
				else
				{
					$(".fix-nav").css({"top":0,"position":"fixed"});					
					$(".fix-nav").animate({
						width:990
					}, {duration: 200,queue:false });
				}
			}
		}
		else
		{
			if(is_show_fix)
			{
				is_show_fix = false;
				$("#rel_nav").css("visibility","visible");
				if($.browser.msie && $.browser.version =="6.0")
				{
					$(".fix-nav").hide();
					$(".fix-nav").css("width",750);
				}
				else
				{
					$(".fix-nav").css({"top":navheight,"position":"absolute"});
					$(".fix-nav").animate({
						width:750
					}, {duration: 200,queue:false,complete:function(){
						$(".fix-nav").hide();
					}});
				}
				
				
			}
		}
		
		//开始自定定位nav的当前位置	
		var content_boxes = $(".show-content .content_box");
		$(".show-nav").find("li").removeClass("active");
		content_idx = -1;
		for(i=0;i<content_boxes.length;i++)
		{
			var scrollTop = $(document).scrollTop() + 50; 
			var current_top = $(content_boxes[i]).offset().top;//内容盒子高度偏移，预留菜单高度
			var next_top = current_top + 50000;  //下一个高度
			if(i<content_boxes.length-1)
			next_top = $(content_boxes[i+1]).offset().top;	
			if(scrollTop>=current_top&&scrollTop<next_top)
			{
				var rel_id = $(content_boxes[i]).attr("rel");	
				content_idx = rel_id;
				break;
			}
			
		}

		$(".show-nav").find("li[rel='"+content_idx+"']").addClass("active");
	};
	$.reset_nav();	
	$(window).scroll(function(){
		$.reset_nav();
	});	
	
	//滚动至xx定位
	$.scroll_to = function(idx){
		var rel_id = idx;	
		var content_box = $(".show-content .content_box[rel='"+rel_id+"']");
		var top = $(content_box).offset().top-40;
		$("html,body").animate({scrollTop:top},"fast","swing",function(){
			content_idx = rel_id;
			$(".show-nav").find("li").removeClass("active");
			$(".show-nav").find("li[rel='"+content_idx+"']").addClass("active");
		});
	};
	//菜单点击
	$(".show-nav").find("li").bind("click",function(){
		
		var rel_id = $(this).attr("rel");	
		$.scroll_to(rel_id);
	});
	
	//x店通用点击
	$("#show_store").bind("click",function(){
		$.scroll_to('n0');
	});	
}


/**
 * 加入收藏
 */
function collect_deal()
{	
	$.add_collect = function(){			
			var query = new Object();
			query.id = deal_id;
			query.act = "add_collect";
			$.ajax({
						url: AJAX_URL,
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
//							if(ajaxobj.responseText!='')
//							alert(ajaxobj.responseText);
						}
			});
	};
	
	$("#add_collect").bind("click",function(){
		$.add_collect();
	});
	
	
}

function init_kf_qq()
{

	$(window).scroll(function(){		
		
		if($.browser.msie && $.browser.version =="6.0")
		{
			$("#s_kf_qq").css("top",$(document).scrollTop()+$(window).height()-80);
		}	
		
	});	
	
	if($.browser.msie && $.browser.version =="6.0")
	$("#s_kf_qq").css("top",$(document).scrollTop()+$(window).height()-80);
	

}









