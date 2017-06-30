$(document).ready(function(){
	var left_height=$(".cate_box").height();
	var right_height=$(".article_box").height();
	
	if(parseInt(left_height)>parseInt(right_height)){
		var tem_height=parseInt(left_height)-16;
		$(".article_box").css("height", tem_height);
	}
	
	$(".article_list ul li").hover(function(){
		$(this).addClass("current");
	},function(){
		$(this).removeClass("current");
	});
	
});
	


