$(document).ready(function(){
	$("select[name='pid']").bind("change",function(){
		init_seo();
	});
	init_seo();
});

function init_seo()
{
	if($("select[name='pid']").val()==0)
	{
		$(".seo").hide();
	}
	else
	{
		$(".seo").show();
	}
}