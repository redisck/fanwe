var is_load_topic_reply_list = false;
$(document).ready(function(){
	init_screen_size();
	init_add_comment();
	init_comment_list();
	init_fav_btn();
	$(".m_submit_btn").bind("click",function(){
		if($(".comment_txt").val()){
			$("form[name='message_form']").submit();
		}else{
			return false;
		}
		
	});
	$("form[name='message_form']").bind("submit",function(){
		var form = $("form[name='message_form']");
		var ajax_url = $(form).attr("action");
		var query = $(form).serialize();

		$.ajax({
			url:ajax_url,
			data:query,
			dataType:"json",
			type:"post",
			global:false,
			success:function(reply_data){
				if(reply_data.status==1){
					
					var html = '<li class="clearfix">'+
									'<span class="avatar">'+reply_data.avatar+'</span>'+
										'<div class="detail">'+
										'<p class="words"><a class="name" href="'+reply_data.user_url+'">'+reply_data.user_name+'</a><span class="content">'+reply_data.content+'</span></p> '+
										'<p class="other">'+
											'<span class="time">'+reply_data.create_time+'</span>   '+ 								
											'<span class="opt">'+
												'<a class="reply_btn" href="javascript:void(0);" onclick="set_reply('+reply_data.user_id+',\''+reply_data.user_name+'\');">回复</a>'+
												'<a class="del_btn yahei" href="javascript:void(0);" onclick="delete_topic_reply(42,$(\'#reply_item_'+reply_data.reply_id+'\'));">删除</a>    '+
											'</span> '+
										'</p>'+    
									'</div>'+
								'</li>';
					$(".comment_list").append(html);
					$("#comment_list_box").scrollTop($("#comment_list_box")[0].scrollHeight);
					$(".comment_txt").val('');
				}else if(reply_data.status==-1000){
					ajax_login();
				}else{
					$.showErr(reply_data.info);
				}
			}
		});
		return false;
	});
	
	
	$("#comment_list_box").scroll(function(){
　　　　 	if($(this)[0].scrollTop + $(this).height() >= $(this)[0].scrollHeight){
			//分页替换数据
			$(".page_box").css({"visibility":"visible","opacity":"1"});
		}else{
			$(".page_box").css({"visibility":"hidden","opacity":"0"});
		}
　　　　});
	
	
	
});


(function($){
	$.fn.load_topic_reply_list = function(){
		var topic_reply_list_box = $(this);
		var query = new Object();
		query.act = "load_topic_reply_list";
		query.topic_id = TOPIC_ID;
		$.do_load_topic_reply(topic_reply_list_box,AJAX_URL,query);			
	};
	$.do_load_topic_reply = function(dom,ajax_url,query){
		if(!is_load_topic_reply_list)
		{
			$(dom).html("<div class='loading'></div>");
		}		
		is_load_topic_reply_list = true;
		$.ajax({
			url:ajax_url,
			data:query,
			dataType:"json",
			type:"post",
			global:false,
			success:function(obj){						
				$(dom).html(obj.html);
				$.bind_topic_reply_list_pager(dom,query);
				$("img[lazy][!isload]").ui_lazy({placeholder:LOADER_IMG});
				
				if($(".comment_list li").length<3){
					$(".page_box").css({"visibility":"visible","opacity":"1"});
				}else{
					$("#comment_list_box").scrollTop(0);
				}
			}				
		});
		
	};
	$.bind_topic_reply_list_pager = function(dom,query){		
		$(dom).find(".pages a").bind("click",function(){			
			var ajax_url = $(this).attr("href");
			$.do_load_topic_reply(dom,ajax_url,query);			
			return false;
		});
	};
})(jQuery);

function init_add_comment(){
	if($(".comment_txt").val() != '')
		 $(".comment_add").addClass("xhcmt-focus");
}

function init_comment_list(){
	$("#comment_list_box").load_topic_reply_list();		
	
}

function init_fav_btn(){
	$(".i_like_btn").unbind();
	if(parseInt(IS_FAV)){
		$(".i_like_btn").bind("click",function(){del_fav_topic(TOPIC_ID);});
	}else{
		$(".i_like_btn").bind("click",function(){fav_topic2(TOPIC_ID);});
		
	}
}

/**
 * 回复
 */
function set_reply(id,user_name)
{
	$("form[name='message_form']").find("input[name='reply_id']").val(id);
	if(user_name!='')
		$("textarea[name='content']").val(LANG.REPLY+"@"+user_name+":");											
}
/**
 * 删除
 */
function delete_topic_reply(id,dom)
{
	$.showConfirm(LANG.CONFIRM_DELETE_RELAY,function(){
				var query = new Object();
		query.act = "delete_topic_reply";
		query.id =id;
		$.ajax({ 
			url: AJAX_URL,
			data:query,
			dataType: "json",
			type: "POST",
			success: function(ajaxobj){
				if(ajaxobj.status)
				{
					$(dom).remove();
				}
				else
					$.showErr(ajaxobj.info);	
			},
			error:function(ajaxobj)
			{
//				if(ajaxobj.responseText!='')
//				alert(ajaxobj.responseText);
			}
		});	
	});
}

/**
 * 喜欢操作
 */
function fav_topic2(id)
{//i_like_btn_liked
	var query = new Object();
	query.act = "do_fav_topic";
	query.id = id;
	$.ajax({ 
		url: AJAX_URL,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status)
			{
				$(".topic_fav_"+id).html(parseInt($(".topic_fav_"+id+":first").html())+1);
				$(".i_like_btn").addClass("i_like_btn_liked");
				IS_FAV = 1;
				init_fav_btn();
			}
			else
			{
				var query = new Object();
				query.act = "check_login_status";
				
				$.ajax({ 
					url: AJAX_URL,
					dataType: "json",
					data:query,
					type: "POST",
					success: function(ajaxobj){
						if(ajaxobj.status==0)
						{
							ajax_login();
						}
						else
						{
							$.showErr(obj.info);
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});	
				
			}
//			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

function del_fav_topic(id){
	var query = new Object();
	query.act = "do_del_topic";
	query.id = id;
	$.ajax({ 
		url: AJAX_URL,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status)
			{
				if(parseInt($(".topic_fav_"+id+":first").html())>1){
					$(".topic_fav_"+id).html(parseInt($(".topic_fav_"+id+":first").html())-1);
				}else{
					$(".topic_fav_"+id).html(0);
				}
				
				$(".i_like_btn").removeClass("i_like_btn_liked");
				IS_FAV = 0;
				init_fav_btn();
			}
			else
			{
				var query = new Object();
				query.act = "check_login_status";
				
				$.ajax({ 
					url: AJAX_URL,
					dataType: "json",
					data:query,
					type: "POST",
					success: function(ajaxobj){
						if(ajaxobj.status==0)
						{
							ajax_login();
						}
						else
						{
							$.showErr(obj.info);
						}
					},
					error:function(ajaxobj)
					{
//						if(ajaxobj.responseText!='')
//						alert(ajaxobj.responseText);
					}
				});	
				
			}
//			location.reload();
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});
}

