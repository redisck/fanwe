$(document).ready(function(){
	/*左边列表的hover事件*/
	$(".yh_l .back").hover(function(){
		show_scan_box(this);
		$(this).addClass("current");
	},
	function(){
		hide_scan_box(this);
		$(this).removeClass("current");
	});
	
	/*右边领券动态hover*/
	$(".get_list").each(function(i,get_list){
		$(get_list).find("ul li:eq(0)").addClass("current");
		$(get_list).find("ul li").hover(function(){
			$(this).addClass("current").siblings().removeClass("current");
		});
	});
	
	

});