var rec_timer;
var c_idx = 1;
var total = 0;
var login_callback = function(){
	location.reload();
};
$(document).ready(function(){			
		var form=$(".group_panel");
		if(form.length>0){	
			
			//绑定小组申请页的上传控件
			$("#icon_uploader div.ui-button").ui_upload({multi:false,FilesAdded:function(files){
				$(".group_icon").css("display","block");return true;
			},FileUploaded:function(responseObject){
				if(responseObject.error==1000)
				{
					ajax_login();
				}
				else if(responseObject.error==0)
				{	
					//alert(responseObject.url);
					$("#icon_uploader [name='icon']").val(responseObject.url);
					var html = 	'<img src="'+APP_ROOT+"/"+responseObject.url+'" />';		
					$(".group_icon .loader").html(html);					
				}
				else
				{
					$.showErr(responseObject.message);
				}
			},UploadComplete:function(files){
				
			},Error:function(errObject){
				$.showErr(errObject.message);
			}});
			
			$("#image_uploader div.ui-button").ui_upload({multi:false,FilesAdded:function(files){
				$(".group_image").css("display","block");return true;
			},FileUploaded:function(responseObject){
				if(responseObject.error==1000)
				{
					ajax_login();
				}
				else if(responseObject.error==0)
				{
					$("#image_uploader [name='image']").val(responseObject.url);					
					var html = 	'<img src="'+APP_ROOT+"/"+responseObject.url+'" />';		
					$(".group_image .loader").html(html);		
				}
				else
				{
					$.showErr(responseObject.message);
				}
			},UploadComplete:function(files){
				
			},Error:function(errObject){
				$.showErr(errObject.message);
			}});

		}
		
		$(".group_item , .group_icon").hover(function(){
			$(this).addClass("current");
		},function(){
			$(this).removeClass("current");
		});	
		
		$("#submit_group").bind("click",function(){
			do_create_group();
		});
		total = $("#rec_topic").find(".rec_image_topic_item").length;	
		init_rec_topic();
});
	

function init_rec_topic(){
	$("#rec_topic").find(".rec_image_topic_item[rel='1']").show();
	$("#rec_topic").find(".img_ico[rel='1']").addClass("act");
	
	rec_timer = window.setInterval("auto_play_rec_topic()", 2000);
	$("#rec_topic").find(".img_ico").hover(function(){
		show_current_tab($(this).attr("rel"));		
	});
	
	$("#rec_topic").hover(function(){
		clearInterval(rec_timer);
	},function(){
		rec_timer = window.setInterval("auto_play_rec_topic()", 2000);
	});
}

function auto_play_rec_topic(){	
	if(c_idx == total){
		c_idx = 1;
	}else{
		c_idx++;
	}
	show_current_tab(c_idx);
}

function show_current_tab(idx){	
	$("#rec_topic").find(".rec_image_topic_item[rel!='"+idx+"']").hide();
	$("#rec_topic").find(".img_ico").removeClass("act");
	if($("#rec_topic").find(".rec_image_topic_item[rel='"+idx+"']").css("display")=='none')
	$("#rec_topic").find(".rec_image_topic_item[rel='"+idx+"']").fadeIn();
	$("#rec_topic").find(".img_ico[rel='"+idx+"']").addClass("act");
	c_idx = idx;	
}



function do_create_group()
{
	var name = $("input[name='name1']").val();
	var memo = $("textarea[name='memo']").val();
	var icon = $("input[name='icon']").val();
	var image = $("input[name='image']").val();
	var cate_id = $("select[name='cate_id']").val();
	
	if($.trim(name)==''){
		$.showErr("请填写小组名称",function(){
			$("form[name='create_group']").find("input[name='name']").focus();
		});
		return;
	}
	if($.trim(memo)==''){
		$.showErr("请填写小组说明",function(){
			$("form[name='create_group']").find("textarea[name='memo']").focus();
		});
		return;
	}
	
	var query = new Object();
	query.name = name;
	query.icon = icon;
	query.image = image;
	query.memo = memo;
	query.cate_id = cate_id;
	var ajaxurl = $("form[name='create_group']").attr("action");
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		data:query,
		type: "POST",
		success: function(obj){
			if(obj.status==1){
				$.showSuccess("创建成功，请等待管理员审核",function(){
					location.href = obj.url;
				});
			}else if(obj.status==2){
				ajax_login();
			}else{
				$.showErr(obj.info);
			}
		},
		error:function(ajaxobj)
		{
			
		}
	});	
}

function op_group_setmemo(id)
{
	$.weeboxs.open(edit, {contentType:'ajax',showButton:false,title:"编辑小组说明",width:570,type:'wee',onopen:function(){init_ui_button();}});
}

function do_submit_opform()
{
	var query = $("form[name='opform']").serialize();
	
	var ajaxurl = $("form[name='opform']").attr("action");
	$.ajax({ 
		url: ajaxurl,
		data:query,
		type: "POST",
		dataType: "json",
		success: function(o){
			if(o.status==1)
				location.reload();
			else
				$.showErr(o.info);
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}

function close_pop()
{
	$(".dialog-close").click();
}


function join_group()
{
	var ajaxurl = join_url;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{
				location.reload();
			}		
			else if(obj.status == 2)
			{
				ajax_login();
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}
function exit_group(group_id)
{
	var ajaxurl =exit_url;
	$.ajax({ 
		url: ajaxurl,
		dataType: "json",
		success: function(obj){
			if(obj.status == 1)
			{
				location.reload();
			}		
			else if(obj.status == 2)
			{
				ajax_login();
			}
			else if(obj.status == 3)
			{
				$.showErr("组长只能请后台管理员退出");
			}
		},
		error:function(ajaxobj)
		{
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});	
}










