$(function(){
	$(".loginout_btn").bind("click",function(){
		var url = $(this).attr("rel");
		$.showConfirm("确定登出账户吗？",function(){
			window.location = url;
		});
		return false;
	});
});