$(function(){
	$(".J_item_more").click(function(){
		
        $(this).parent().find(".business_display").toggleClass("business_blank");
    });
});