$(document).ready(function(){

	collect_location();
	init_store_image();
	init_content_nav();

	$('.iconfont.add_to_cart_icon').bind('click',function(){
		dc_add_cart($(this));
	});
	
	$('.dc_m .descrese').live('click',function(){
		var num=-1;
		dc_change_num($(this),num);
	});
	
	$('.dc_m .increase').live('click',function(){
		var num=1;
		dc_change_num($(this),num);
	});
	
	$('.cart_head #dc_clear').live('click',function(){
		dc_cart_clear();
	});

	$('#order_done').live('click',function(){

		location.href=order_url;
	});
	
	$('#cart-title .f_l .iconfont').live('click',function(){
	var height=$('#cart-section #cart-content').height();
	$('#cart-section #cart-content').css({'height':height}).slideToggle();
	});
	
	
});

function collect_location()
{
	
	$("#add_location_collect").bind("click",function(){
		var location_id=$(this).attr('data-i');
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
							//	$.showSuccess(obj.info);	
								$('#add_location_collect').addClass('collected').html(is_collected_icon);
								$('#location_count').html('('+obj.count+')');
							}else if(obj.status==2)
							{
							//	$.showSuccess(obj.info);	
								$('#add_location_collect').removeClass('collected').html(not_collected_icon);
								$('#location_count').html('('+obj.count+')');
							
							}
							else if(obj.status==-1)
							{ 
								login_success_reload(location.href);

							}else{
								$.showErr(obj.info);
							}
						},
						error:function(ajaxobj)
						{
//							if(ajaxobj.responseText!='')
//							alert(ajaxobj.responseText);
						}
			});
	
		
		
		
	});
	
	
}


/**
 * 初始化图库功能
 */
function init_store_image()
{
	$("#store_image").bind("click",function(){

		if(STORE_IMAGES.length>0)
		{
			var group = new Array();
			for(i=0;i<STORE_IMAGES.length;i++)
			{
				var item = new Object();
				item.href = STORE_IMAGES[i]['image'];
				item.title = STORE_IMAGES[i]['brief'];
				group.push(item);
			}

			$.fancybox.open(group,{
				prevEffect : 'fade',
				nextEffect : 'fade',
				nextClick : true,
				helpers : {
					thumbs : {
						width  : 50,
						height : 50
					}
				}
			});
		}
	
	});
	
}



//关于内容页的滚动定位,包含x店通用的点击滚动
function init_content_nav()
{	

	var navheight = $("#rel_nav").offset().top;
	var is_show_fix = false;	
	var content_idx = -1;
	$.reset_nav = function(){
		if($.browser.msie && $.browser.version =="6.0")
		{
			$("#rel_nav").css("top",$(document).scrollTop());
		}	
		
	//	var navheight = $("#rel_nav").offset().top;
		var docheight = $(document).scrollTop();		
		if(docheight>navheight)		
		{	
			if(!is_show_fix)
			{		
				is_show_fix = true;
				$("#rel_nav").css({"top":0,"position":"fixed","background":"#FAFAFA",'z-index':1000});										
			}
		}
		else
		{
			if(is_show_fix)
			{
				is_show_fix = false;
				
					$("#rel_nav").css({"top":navheight,"position":"","background":"#fff"});		
			}
			
		}
		
		//开始自定定位nav的当前位置	
		var content_boxes = $(".show-content .content_box");
		$(".show-nav").find("li").removeClass("active active_now");
		$(".show-nav").find("li[rel='n0']").addClass("active_now");
		content_idx = -1;
		for(i=0;i<content_boxes.length;i++)
		{
			var scrollTop = $(document).scrollTop() + 50; 
			var current_top = $(content_boxes[i]).offset().top;//内容盒子高度偏移，预留菜单高度
			var next_top = current_top + 50000;  //下一个高度
			if(i<content_boxes.length-1)
			next_top = $(content_boxes[i+1]).offset().top;	
			if(scrollTop>=current_top&&scrollTop<next_top)
			{
				var rel_id = $(content_boxes[i]).attr("rel");	
				content_idx = rel_id;
				break;
			}
			
		}

		$(".show-nav").find("li[rel='"+content_idx+"']").addClass("active active_now");
	};

	$.reset_nav();	
	$(window).scroll(function(){
		$.reset_nav();
	});

	
	//滚动至xx定位
	$.scroll_to = function(idx){
		var rel_id = idx;	
		var content_box = $(".show-content .content_box[rel='"+rel_id+"']");
		var top = $(content_box).offset().top-110;
		$("html,body").animate({scrollTop:top},"fast","swing",function(){
			content_idx = rel_id;
			$(".show-nav").find("li").removeClass("active active_now");
			$(".show-nav").find("li[rel='"+content_idx+"']").addClass("active active_now");
		});
	};
	//菜单点击
	$(".show-nav").find("li").bind("click",function(){
		
		var rel_id = $(this).attr("rel");	
		$.scroll_to(rel_id);
	});
	
	//x店通用点击
	$("#show_store").bind("click",function(){
		$.scroll_to('n0');
	});	
	
	
	$(".wrap_m2 .filter_row").find("li").bind("click",function(){
		
		var rel_id = $(this).attr("rel");	
		$.scroll_menu_to(rel_id);
	});
	
	$.scroll_menu_to = function(idx){
		var rel_id = idx;	
		var content_box = $(".show-content .content_box .box_title[rel='"+rel_id+"']");
		var top = $(content_box).offset().top-110;
		$("html,body").animate({scrollTop:top},"fast","swing",function(){
			content_idx = rel_id;
			$(".wrap_m2 .filter_row").find("li a").removeClass("active active_now");
			$(".wrap_m2 .filter_row").find("li[rel='"+content_idx+"'] a").addClass("active active_now");
		});
	};
	
	
	
	
}


function dc_add_cart(o){

	var menu_id=o.attr('data-i');
	var number=1;
	var ajaxurl=DC_AJAX_URL;
	var query=new Object();
	query.menu_id=menu_id;
	query.number=number;
	query.location_id=location_id;
	query.supplier_id=supplier_id;
	query.distance=distance;
	query.act='dc_add_cart';
	
	$.ajax({
		url:ajaxurl,
		data:query,
		type:'post',
		dataType:'json',
		success:function(data){
		var top=o.offset().top;
		var left=o.offset().left;
		var cartleft=$('#dc_cart_icon').offset().left;
		var carttop=$('#dc_cart_icon').offset().top;
		var ccopy=o.clone();
		
		ccopy.css({'position':'absolute','left':left,'top':top,'z-index':10000});
		$('body').append(ccopy);
		//o.css({'position':'absolute'});
		if(data.status==1){
		ccopy.animate({width:10,height:10,left:cartleft,top:carttop},'show',function(){
		ccopy.remove();
		$('#dc_cartsection').html(data.html);
		change_menu_buy_count(o,1);
		});
		}	
		}
	});
}


function change_menu_buy_count(o,num){
	var obj=o.parents('.f_r').find('.menu_buy_count');
	var menu_id=o.attr('data-i');
	var numx=parseInt(obj.html())+num;
	if(obj.length>0){
	
		if(numx>0){

		obj.html(numx);
		}
		else
		{
		obj.remove();
		}
	
	}
	else
	{

		var htmlx='<i class="menu_buy_count" data-i="'+menu_id+'">1</i>';
		o.parents('.f_r').prepend(htmlx);
	}
}

function dc_change_num(o,num){
		var menu_o=o.parent().find('#dc_num');
		var menu_id=parseInt(menu_o.attr('menu-id'));
		var number_x=parseInt(menu_o.html())+num;
		var number=parseInt(num);
		var ajaxurl=DC_AJAX_URL;
		var query=new Object();
		query.menu_id=menu_id;
		query.number=number;
		query.location_id=location_id;
		query.supplier_id=supplier_id;
		query.act='dc_add_cart';
		$.ajax({
				url:ajaxurl,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
				var top=menu_o.offset().top;
				var left=menu_o.offset().left;
				var cartleft=$('#dc_cart_icon').offset().left;
				var carttop=$('#dc_cart_icon').offset().top;
				var ccopy=$('<div id="num_show"></div>');

				if(data.status==1){
				$('#dc_cartsection').html(data.html);
				
				ccopy.css({'left':left,'top':top}).html(number_x);
				$('body').append(ccopy);

				ccopy.animate({'left':cartleft+7,'top':carttop},'show',function(){
				ccopy.remove();
				change_menu_buy_count($(".iconfont.add_to_cart_icon[data-i='"+menu_id+"']"),number);
				});	

				}
				}
		});
		
}


function dc_cart_clear(){
		var ajaxurl=DC_AJAX_URL;
		var query=new Object();
		query.location_id=location_id;
		query.act='dc_cart_clear';
		$.ajax({
				url:ajaxurl,
				data:query,
				type:'post',
				dataType:'json',
				success:function(data){
				if(data.status==1){
					var o=$('#dc_cart_info tr').last();
					var len=$('#dc_cart_info tr').length;
					$('#cart-section #cart-content').css({'height':'auto'});
					go_away(o,len);
				}
				}
		});		


}


function go_away(o,i){
				var width=$('#dc_cart_info tr').width();
				var height=$('#dc_cart_info tr').height();
				o.css({'position':'absolute','display':'block','width':width,'bottom':height+1});
				o.animate({left:-width-20},'show',function(){
				i--;
				go_away(o.prev(),i);
				o.remove();
				if(i==0){
				$('.menu_buy_count').remove();
				$('#cart-section #cart-content').slideUp();
				$('#cart-section #cart-title .f_r').html('<div class="cart_tip">购物车是空的</div>');
				}
				});
}
