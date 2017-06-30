$(function(){
	$("label.ui-checkbox[is_main='1'] input").bind("checkon",function(){
	var module_name = $(this).parent().attr("module_name");
	$(".ui-checkbox input[module_name='"+module_name+"']").each(function(i,o){
		$(this).attr("checked",true);
		$(this).parent().ui_checkbox({refresh:true});
	});
	
});
$("label.ui-checkbox[is_main='1'] input").bind("checkoff",function(){
	var module_name = $(this).parent().attr("module_name");
	$(".ui-checkbox input[module_name='"+module_name+"']").each(function(i,o){
		$(this).attr("checked",false);
		$(this).parent().ui_checkbox({refresh:true});
	});
	
});


$("label.ui-checkbox[is_sub='1'] input").bind("checkon",function(){
	var module_name = $(this).attr("module_name");
	var total_count = $(".ui-checkbox input[module_name='"+module_name+"']").length;
	var count = 0;
	$(".ui-checkbox input[module_name='"+module_name+"']").each(function(i,o){
		if($(this).attr("checked")){
			count++;
		}
	});
	if(total_count==count){
		$("label.ui-checkbox[module_name='"+module_name+"'] input").attr("checked",true);
		$("label.ui-checkbox[module_name='"+module_name+"']").ui_checkbox({refresh:true});
	}
	
});
$("label.ui-checkbox[is_sub='1'] input").bind("checkoff",function(){
	var module_name = $(this).attr("module_name");
	var total_count = $(".ui-checkbox input[module_name='"+module_name+"']").length;
	var count = 0;
	$(".ui-checkbox input[module_name='"+module_name+"']").each(function(i,o){
		if($(this).attr("checked")){
			count++;
		}
	});
	if(count<total_count){
		$("label.ui-checkbox[module_name='"+module_name+"'] input").attr("checked",false);
		$("label.ui-checkbox[module_name='"+module_name+"']").ui_checkbox({refresh:true});
	}
});
});
	
