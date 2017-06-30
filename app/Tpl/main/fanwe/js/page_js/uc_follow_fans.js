$(function(){
	if(is_best_user){
		load_best_user();
	}
});

function load_best_user(){
	$(".u_list_box").html("<div class=\"loading\"></div>");
	var query = new Object();
	query.act = "load_best_user";
	query.count = 10;
	$.ajax({
		url:AJAX_URL,
		data:query,
		type:"post",
		dataType:"json",
		success:function(data){
			$(".u_list_box").html(data);
		}
	});
}
