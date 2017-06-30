$(document).ready(function(){


//	$('#Classification_list_all .nav-bar_00 li').bind('click',function(){
//		var index=$(this).index();
//		$(this).parents(".nav-bar_00").addClass("pf");
//		$(this).parents('.w_percentage_100').siblings('.toggle_down').find('.hide_show').eq(index).toggle();
//	})
//	

	//首页的交互
$(window).bind("scroll", function(){ 
    var x=$(document).scrollTop();
     if(x>1){ //在wap，滚动条是弹性的，没有个定性，会跳来跳去的
	 	$("#Classification_list_all").addClass("pf");
	 }else{
	 	$("#Classification_list_all").find(".toggle_down .child").addClass("hide_show");
	 	$("#Classification_list_all").removeClass("pf");
		}	 	
}); 

$(".nav-bar_00 li").click(function(){
	index_rel=$(this).index();
	$(this).parents("#Classification_list_all").addClass("pf");
	$(this).parents("#Classification_list_all").find(".toggle_down .child").eq(index_rel).removeClass("hide_show").siblings(".child").addClass("hide_show");
	if(!$(this).hasClass("y"))
	{
	$(this).addClass("y").siblings().removeClass("y");
	$(this).siblings().find(".iconfont").html("&#xe665;");
	$(this).find(".iconfont").html("&#xe60f;");
	$(this).parents.siblings(".child").addClass("hide_show");
	$(this).parents("#Classification_list_all").addClass("pf");
	$(this).parents("#Classification_list_all").find(".toggle_down .child").eq(index_rel).removeClass("hide_show").siblings(".child").addClass("hide_show");
	}
	else
	{
		$(this).removeClass("y");
		$(this).find(".iconfont").html("&#xe665;");
		$(this).parents("#Classification_list_all").removeClass("pf");
	    $(this).parents("#Classification_list_all").find(".toggle_down .child").addClass("hide_show");
	}
});

$(".nav_list_close_but").click(function(){
	$(".nav-bar_00 li").removeClass("y");
	$("#Classification_list_all").find(".toggle_down .child").addClass("hide_show");
	$("#Classification_list_all").removeClass("pf");
});


$('.Classification_list_parents .first_q').bind('click',function(){

	var height=$('#qlist').height();
	$('.sub_q').hide();
	$(this).find('.sub_q').height(height).show();
	
});

});


