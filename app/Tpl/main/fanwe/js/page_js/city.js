$(document).ready(function(){

	city_py_hover();	
	search_city();
	$("select[name='province']").bind("change",function(){
		load_select();
	});
	select_jump();
});
	

function load_select(){
		var id = $("select[name='province']").val();
		var evalStr=city_json[id];
		if(id==0)
		{
			var html = "<option value='0'>==城市</option>";
		}
		else
		{
			var regionConfs=evalStr['city_list'];
			
			var html = "<option value='0'>==城市</option>";
			for(key in regionConfs)
			{				
				html+="<option value='"+regionConfs[key]['url']+"'>"+regionConfs[key]['name']+"</option>";
			}
		}
		$("select[name='city']").html(html);
}

function select_jump(){
	$("form[name='city_province']").bind("submit",function(){		
		if($("select[name='city']").val()!=0)location.href = $("select[name='city']").val();
		return false;
	});
}

/**
 * 城市拼音区块鼠标移入
 */
function city_py_hover()
{
    $(".city_names").hover(
            function(){
            	$(".city_names").removeClass("cur"); 
                $(this).addClass("cur");                                
            },
            function(){
                $(this).removeClass("cur");                            
            }
     );			
}

/**
 * 搜索结果鼠标移入
 */
function search_hover()
{
    $(".search_list ul li").hover(
            function(){
            	$(".search_list ul li").removeClass("cur"); 
                $(this).addClass("cur");                                
            },
            function(){
                $(this).removeClass("cur");                            
            }
     );	
//    $(".search_list ul li").mousedown(
//    		function(){
//	    		location.href = $(this).attr("url");	    		
//    		}
//     );			
}

/**
 * 城市搜索
 */
function search_city(){
	var current_li = null;
	
	var city_top=$("input[name='search_city']").offset().top;
	var city_left=$("input[name='search_city']").offset().left;   
	$(".search_list").css({ top: city_top+36, left:city_left});
	
	$("input[name='search_city']").bind("keyup",function(event){	    
		 $(".search_list").show();
	    var keyword = $("input[name='search_city']").val();
	    if(keyword!="")
	    {
		    $(".search_list ul li").hide();
		    $(".search_list ul li[suname*='"+keyword+"']").show();
		    $(".search_list ul li[sname*='"+keyword+"']").show();
		    $(".search_list").css('height','auto');
	    }
	    else
	    {
	    	 $(".search_list ul li").show();
	    	 $(".search_list").css('height','280px');
	    }
	    
		search_hover();		
		if (event.keyCode == 40) {
			$('.search_list ul li').removeClass("cur");
			if(current_li==null)
			{
				current_li = $('.search_list ul li:visible').first();
			}
			else
			{
				current_li = $(current_li).next("li:visible");
			}			
			
			if(current_li.length==0)
			{
				current_li = $('.search_list ul li:visible').first();
			}
			
			$(current_li).addClass("cur"); 
		}
		
		if (event.keyCode == 38) {
			$('.search_list ul li').removeClass("cur");
			if(current_li==null)
			{
				current_li = $('.search_list ul li:visible').last();
			}
			else
			{
				current_li = $(current_li).prev("li:visible");
			}			
			
			if(current_li.length==0)
			{
				current_li = $('.search_list ul li:visible').last();
			}			
			$(current_li).addClass("cur"); 
		}	
	});
	
	$('.search_list ul li').hover(function(){		
		$('.search_list ul li').removeClass("cur");
		current_li = $(this);
		$(current_li).addClass("cur"); 
	},function(){
		$.clear_city();
	});
	
	
	$("input[name='search_city']").bind("blur",function(){
			if(current_li==null&&$('.search_list ul li:visible').length==1)
			{
				current_li = $('.search_list ul li:visible').first();
			}
			$(".search_list").hide();			
			//$.jump_city();
			$.clear_city();
	});
	
	$('.search_list ul li').bind("mousedown",function(){
		current_li = $(this);
		$.jump_city();
	});
	
	$("form[name='search_city_form']").bind("submit",function(){
		$.jump_city();
		return false;
	});
	
	$.clear_city = function(){
		current_li = null;
	};
	$.jump_city = function(){
		if(current_li!=null&&current_li.attr("url"))location.href = current_li.attr("url");
	};
	
}


