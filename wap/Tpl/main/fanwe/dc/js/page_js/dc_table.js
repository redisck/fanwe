$(document).ready(function(){


	$("#people_num_select").bind("change",function(){
			
			   var obk=$('#people_num_select option:selected');	
		       var people_num_option=obk.text();
			   var people_num_option_price=obk.attr("rel");
			   var people_num_option_href=obk.attr("href");
			   if($.trim(people_num_option_href)!=''){
				   //alert(people_num_option_href);
				    $(".people_num").html(people_num_option);
					$(".table_info_price").html(people_num_option_price);
					$("input[name='date']").val(0);
					$("input[name='table_time_id']").val(0);
					
					window.location.href=people_num_option_href;
				}
			
	});


	$("#book_time_select").bind("change",function(){
		  var obj=$('#book_time_select option:selected');
		  var book_time_option=obj.text();
		  $("input[name='date']").val(obj.attr('date'));
		  $("input[name='table_time_id']").val(obj.attr('time_id'));
		   $(".book_time").html(book_time_option);
		   
		   add_table_cart();
	});
	
	
	
	$('.dc_table_cart_clear').bind('click',function(){
		
		del_table_cart($(this));
		
	});
	
	
	$('#go_t_pay').bind('click',function(){	
		go_t_pay();
		
	});
	
	$(".book_way").click(function(){

		if(has_menu==1){
			location.href=$(this).attr('action-url');			
		}else{
			var book_way_this_index=$(this).attr("rel");
			$(".book_way_change_but").eq(book_way_this_index).show().siblings().hide();
		}

	});
	
	$('#go_t_menu').bind('click',function(){
		
		var url=$(this).attr("action-url");
		var query=new Object();
		query.lid=location_id;
		query.tid=tid;

		$.ajax({
			url:url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){
				if(data.status==1){
					if(data.jump){
						location.href=data.jump;
					}

				}else{
					$.showErr(data.info);
				}	
			}
		
	});
	
});


});

function add_table_cart(){
	
	var query=new Object();
	query.act='add_table_cart';
	query.lid=location_id;
	query.date=$("input[name='date']").val();
	query.table_time_id=$("input[name='table_time_id']").val();

	if(query.table_time_id >0){
		$.ajax({
				url:dc_table_url,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
					if(data.status==1){
					$('#dc_cart').html(data.html);
					
					$('.dc_table_cart_clear').bind('click',function(){
						
						del_table_cart($(this));
						
					});
					
					
					}else{
						$.showErr(data.info,function(){location.href=location.href;});
					}
				}
		});
		
	}
}

function del_table_cart(o){
	
	var id=$(o).parents('.menu_right').attr('menu-id');
	
	var query=new Object();
	query.act='del_table_cart';
	query.id=id;
	query.lid=location_id;
	$.ajax({
			url:dc_table_url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){
				$('#dc_cart').html(data.html);
				$(".book_time").html('请选择预订时间');
			}
	});
	
}

function go_t_pay(){

	var url=$('#go_t_pay').attr('action-url');
	var query=$('#cart_form').serialize();	

	$.ajax({
			url:url,
			data:query,
			type:'post',
			dataType:'json',
			success:function(data){
				if(data.status==1){
				
					location.href=data.jump;
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
