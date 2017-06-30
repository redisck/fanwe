$(document).ready(function(){
	init_store_row();
	$(".store_pop_map").each(function(i,o){
		$(o).bind_load_store_map();
	});
});

function init_store_row()
{
	$(".store_row").hover(function(){
		show_scan_box(this);
		$(this).addClass("row_current");
	},function(){
		hide_scan_box(this);
		$(this).removeClass("row_current");
	});
}