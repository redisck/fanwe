$(document).ready(function(){

	
	//增加收藏
	$('.add_location_collect').bind('click',function(){
		add_location_collect_function($(this));
	});

});

//增加收藏
function add_location_collect_function(o){
		var query=new Object();
	
		var url=$(o).attr('action-url');
		$.ajax({
				url:url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
					
						$.showSuccess(data.info,function(){
							location.href=location.href;	
						});
					}else if(data.status==0){
						alert(data.info);
					}else if(data.status==-1){
						$.showErr(data.info,function(){
							location.href=data.jump;
						});
					}
				
				}
		});
		
}

