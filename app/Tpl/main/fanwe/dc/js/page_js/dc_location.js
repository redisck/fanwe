$(document).ready(function(){
	init_screen_size();
	
	
	$('.iconfont.location_collect').bind('click',function(){
		add_location_collect_new($(this));
	});
	$('.iconfont.del_location_collect').live('click',function(){
		del_location_collect($(this));
	});
	
	$('.deal_list_box .deal_list > li .deal_item , .dc_close_a').mouseover(function(){
		var index=$(this).parent().index()+1;
		if($(window).width()<1050)
		{
			if(index%4==0){
				$(this).siblings('.overlay').css({'left':'-241px'});
				$(this).parent().css({'z-index':'100'});
				$(this).siblings('.overlay').find('.arrow').html(arrow_right_icon).css({'left':'238px'});
			}
		}
		if($(window).width()>1200)
		{
			if(index%5==0){
				$(this).siblings('.overlay').css({'left':'-240px'});
				$(this).parent().css({'z-index':'100'});
				$(this).siblings('.overlay').find('.arrow').html(arrow_right_icon).css({'left':'236px'});
			}
		}		
		$(this).siblings('.overlay').show();	
	}).mouseout(function(){
		var index=$(this).parent().index()+1;
		if($(window).width()<1050)
		{
			if(index%4==0){

				$(this).parent().css({'z-index':'10'});
			}
		}
		if($(window).width()>1200)
		{
			if(index%5==0){
				$(this).parent().css({'z-index':'10'});
			}
		}
		$(this).siblings('.overlay').hide();
	});
	
	$('.dc_promote .ui-checkbox').bind('click',function(){
	
		location.href=$(this).find('input').attr('value');
	});
	
});



function add_location_collect_new(o){
		var location_id=o.attr('data-i');
		var query = new Object();
		query.location_id = location_id;
		query.act = "add_location_collect";
			$.ajax({
						url: DC_AJAX_URL,
						data: query,
						dataType: "json",
						type: "post",
						success: function(obj){
							if(obj.status == 1){
								//$.showSuccess(obj.info);								

									o.addClass('collected').html(is_collected_icon);
							}else if(obj.status==2)
							{
							//$.showSuccess(obj.info);
							o.removeClass('collected').html(not_collected_icon);
							}
							else if(obj.status==-1)
							{
								login_success_reload(location.href);
	
							}else{
								$.showErr(obj.info);
							}
						}
			});
	

}

