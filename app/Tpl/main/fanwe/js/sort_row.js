$(document).ready(function(){
	init_tag_event();
	init_drop_event();

});

function init_tag_event()
{
	$(".dtag").bind("click",function(){
		location.href = $(this).find("input").val();
	});
}

function init_drop_event()
{
	$(".dnode dd a").bind("click",function(){
		location.href = $(this).attr("value");
		return false;
	});
}