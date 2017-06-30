$(document).ready(function(){
	init_store_row();

});

function init_store_row()
{
	$(".event_row").hover(function(){
		show_scan_box(this);
		$(this).addClass("row_current");
	},function(){
		hide_scan_box(this);
		$(this).removeClass("row_current");
	});
}