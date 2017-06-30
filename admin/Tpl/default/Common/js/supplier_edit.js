$(function(){
	$("select[name='allow_publish_verify']").bind("change",function(){
		if($(this).val() == 1){
			$(".apv_link_box").show();
		}else{
			$(".apv_link_box").hide();
		}
	});
});