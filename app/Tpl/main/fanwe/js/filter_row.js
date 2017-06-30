$(document).ready(function(){
	/*头部分类，商圈的交互*/
	$(".choose_parent .name,.choose_parent .hidden_nav").hover(
		function()
		{
			var parentDom = $(this).parent();
			parentDom.stopTime();
			parentDom.find(".hidden_nav").fadeIn("fast");
			parentDom.addClass("active");
		},
		function()
		{
			var parentDom = $(this).parent();
			parentDom.oneTime(100,function(){
				parentDom.removeClass("active");
				parentDom.find(".hidden_nav").fadeOut("fast");
			});			
		}
	);
	
	$(".choose_parent .hidden_but").hover(function(){
		var parentDom = $(this).parent();
		parentDom.stopTime();
	},function(){
		var parentDom = $(this).parent();
		parentDom.oneTime(100,function(){
			parentDom.removeClass("active");
			parentDom.find(".hidden_nav").fadeOut("fast");
		});
	});
	
	//初始化展开模式
	init_filter_row_expand();
});


function init_filter_row_expand()
{
	$(".filter_row dl dd.expend").hide();
	$(".filter_row dl dd.wrap_filter").each(function(i,o){
		if($(o).height()>75)
		{
			$(o).css({"height":75,"overflow":"hidden"});
			
			//初始化展开收起按钮			
			var expend = $(o).parent().find(".expend");
			expend.css("padding-top",$(o).height()-expend.height());
			expend.find("a").hide();
			expend.find("a.open").show();
			expend.show();
			
			expend.find("a.open").bind("click",function(){
				$(o).css({"height":"auto","overflow":"auto"});
				expend.css("padding-top",$(o).height()-expend.height());
				expend.find("a").hide();
				expend.find("a.close").show();
			});

			expend.find("a.close").bind("click",function(){
				$(o).css({"height":75,"overflow":"hidden"});
				expend.css("padding-top",$(o).height()-expend.height());
				expend.find("a").hide();
				expend.find("a.open").show();
			});
		}
	});
	$("img[lazy][!isload]").ui_lazy({placeholder:LOADER_IMG});
}