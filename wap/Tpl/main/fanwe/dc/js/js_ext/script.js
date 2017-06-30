
//ctl=dcorder&act=cart
$(document).ready(function(){
	$("[class^=sub_menu]  img[src=''] , dl img[src='']").each(function(i,obj){
		$(obj).attr('src',NO_IMAGE);
	});


	$("#add_remark").click(function(){
		$("#cart_Remark_textarea").slideToggle();
	});
});

//在头部，城市选择位置
$(document).ready(function(){
	$("#dc_biz_menu_but").click(function(){
		$("#dc_biz_menu_list").toggle();
	});
});
//dc_view.html
$(document).ready(function(){
	    $("#is_close_but").click(function() {
        open($("#is_close_change"));
    });

});
//dcorder_order.html
//dcresorder_order.html
$(document).ready(function(){
	$("#dcorder_order_time_select").change(function(){
		var select_val=$('#dcorder_order_time_select option:selected').attr("href");
		 window.location.href=select_val;
	});
});
//dc_point.html
$(document).ready(function(){
	
	$(".img_list_show").each(function(){
		if($(this).height()>60)
		{
			$(this).siblings(".img_list_show_but").show();
			$(this).css({"overflow":"hidden","height":"60"});
			$(this).siblings(".img_list_show_but").click(function(){
				if($(this).siblings(".img_list_show").hasClass("y"))
				{
					 $(this).siblings(".img_list_show").css({"height":"60"}).removeClass("y");
					 $(this).html("点击查看更多图片");
				}
				else
				{
					 $(this).siblings(".img_list_show").css({"height":"auto"}).addClass("y");
					 $(this).html("收起");
				}
			     
			});
		}
		else
		{
			$(this).siblings(".img_list_show_but").hide();
		}
		
	});
});
//dc_consignee_add.html
$(document).ready(function(){
    $(".default_but").click(function(){
		if($(this).hasClass("y"))
		{
			$(this).addClass("f_c9cacf").removeClass("f_fe4d3d y").html(not_select_icon);
			
		}
		else
		{
			$(this).addClass("f_fe4d3d y").removeClass("f_c9cacf").html(is_select_icon);
			$(this).parent().siblings().find('.pay_ico').html(not_select_icon).addClass("f_c9cacf").removeClass("f_fe4d3d y");
		}
	});
});
//dcbuy.html
$(document).ready(function(){
	$(".dcbuy_ico_but_open").click(function(){
		$(".dcbuy_ico_but_parents").css("height","auto");
		$(".dcbuy_ico_but_close").show();
		$(".dcbuy_ico_but_open").hide();
	});
	$(".dcbuy_ico_but_close").click(function(){
		$(".dcbuy_ico_but_parents").css("height","0.80rem");
		$(".dcbuy_ico_but_open").show();
		$(".dcbuy_ico_but_close").hide();
	});
	
	$(".main_cate li").click(function(){
		//alert($(this).attr("rel"));
		$(".sub_menu_"+$(this).attr("rel")).show().siblings().hide();
		$(this).addClass("current").siblings().removeClass("current");
	});
});
//-----------------------------------------------------------------------------------
//商户详细页-外卖-商品弹出窗
$(document).ready(function(){
	     body_height=$(window).height();
	 body_width=$(window).width();
     height_1=body_height-193;
     height_2=$(".mask_block_con").height();//弹出层的高度
     h_3=(body_height-height_2)/2;
	//height_2=$(".nav_ul li").outerHeight(true);
	//w_1=$(".nav_ul li").outerWidth(true);


$(".height_1").height(height_1);//分类列表限高
$(".mask_block_con").css("margin-top",h_3);



$(".star_width").each(function(){
	var this_star_width=$(this).attr('rel');
	$(this).css("width",this_star_width*19.2);
});
});

//-----------------------------------------------------------------------------------
$(document).ready(function(){
	//点评页--星星
	$(".score_star i").click(function(){
		var score_rel=$(this).attr("rel");
		//alert(score_rel);
		$(this).parents(".score_star").find("i").removeClass("f_fa952f").addClass("f_a6a6a6");
		$(this).parents(".score_star").find("i:lt("+score_rel+")").addClass("f_fa952f");
		$(this).siblings("input").val(score_rel);
	});
});
//-----------------------------------------------------------------------------------
//
//-----------------------------------------------------------------------------------
$(document).ready(function() {
	//商户详情页-订座.html
	//dctable.html
    $(".people_num_change").click(function() {
        open($("#people_num_select"));
    });
	$(".people_num").click(function() {
        open($("#people_num_select"));
    });
	$(".book_time_change").click(function() {
        open($("#book_time_select"));
    });
	$(".book_time").click(function() {
        open($("#book_time_select"));
    });

//
	//男士还是女士
	$(".sex_check .sex_li").click(function(){
		$(this).addClass("current").siblings().removeClass("current");
		var sex_this_rel=$(this).attr("rel");
		//alert(this_rel);
		$("#sex_change").val(sex_this_rel);
	});
	

});

function open(elem) {
    if (document.createEvent) {
        var e = document.createEvent("MouseEvents");
        e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
        elem[0].dispatchEvent(e);
    } else if (element.fireEvent) {
        elem[0].fireEvent("onmousedown");
    }
}

//-----------------------------------------------------------------------------------

$(document).ready(function(){	
/*-------------------轮播--*/

if($('#pagenavi').length >0){
	var active=0;
	as=document.getElementById('pagenavi').getElementsByTagName('a');
	
for(var i=0;i<as.length;i++){
	(function(){
		var j=i;
		as[i].onclick=function(){
			t2.slide(j);
			return false;
		}
	})();
}

var t1=new TouchScroll({id:'wrapper','width':5,'opacity':0.7,color:'#555',minLength:20});
var t2=new TouchSlider({id:'slider', speed:600, timeout:6000, before:function(index){
		as[active].className='';
		active=index;
		as[active].className='active';
	}});
	
}
//-----------------------------------------------------------------------------------	


$(".Preferential_Detail .open_but").click(function(){
	$(this).parents(".Preferential_Detail").hide().siblings(".Preferential_Detail").show();
});
$(".Preferential_Detail .close_but").click(function(){
	$(this).parents(".Preferential_Detail").hide().siblings(".Preferential_Detail").show();
});
});
//-----------------------------------------------------------------------------------	

