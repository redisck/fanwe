$(document).ready(function(){

	init_tag_row();
	init_form_star();
	init_form_tag();
	init_review_upload();
	init_review_form();
	
	
	
});

var uploading = false; //上传中的变量，正在上传中表单不能提交

/**
 * 点评表单提交
 */
function init_review_form()
{
	
	$("form[name='review_form']").bind("submit",function(){
		if(uploading)
		{
			$.showErr("图片上传中，请稍候提交!");
			return false;
		}
		var checker = true;
		var form = $(this);
		$(form).find("input.point_star[value='0']").each(function(i,dp_point){
			if($(dp_point).val()=='0')
			{
				var title = $(dp_point).attr("rel");
				$(dp_point).parent().parent().parent().parent().find(".star_tip").html("请为"+title+"评分，最高五分");
				checker = false;
			}
		});
		
		if(checker)
		{
			$.showConfirm("确认你的点评内容，按确定提交点评。",function(){
				//提交内容
				var ajaxurl = $(form).attr("action");
				var query = $(form).serialize();
				$.ajax({
					url:ajaxurl,
					data:query,
					dataType:"json",
					type:"POST",
					global:false,
					success:function(obj){
						if(obj.status==1)
						{
							$.showSuccess(obj.info,function(){
								if(obj.jump&&obj.jump!="")
								{
									location.href = obj.jump;
								}
							});
						}
						else
						{
							if(obj.status==-1)
							{
								ajax_login();
							}
							else
							{
								$.showErr(obj.info);
							}
						}
					}
				});
			});
			
		}
		return false;
	});
}


function init_review_upload()
{
	$("#uploader").find("div.ui-button").ui_upload({url:TOPIC_IMAGE_UPLOAD,multi:true,FilesAdded:function(files){

		if($("#review_images").find("span").length+files.length>9)
		{
			$.showErr("最多只能传9张图片");
			return false;
		}
		else
		{
			for(i=0;i<files.length;i++)
			{
				var html = '<span><div class="loader"></div></span>';
				var dom = $(html);		
				$("#review_images").append(dom);	
			}
			uploading = true;
			return true;
		}
		
	},FileUploaded:function(responseObject){
		if(responseObject.error==0)
		{
			var first_loader = $("#review_images").find("span div.loader:first");
			var box = first_loader.parent();
			box.remove(first_loader);
			var html = '<a href="javascript:void(0);"></a>'+
			'<img src="'+APP_ROOT+"/"+responseObject.thumb.preview.url+'" />'+
			'<input type="hidden" name="dp_image[]" value="'+responseObject.url+'" />'+
			'<input type="hidden" name="topic_image_id[]" value="'+responseObject.id+'" />';		
			$(box).html(html);
			$(box).find("a").bind("click",function(){
				$(this).parent().remove();
			});
		}
		else
		{
			if(responseObject.error==1000)
			{
				ajax_login();
			}
			else
			{
				$.showErr(errObject.message);
			}
		}
		
		
	},UploadComplete:function(files){
		uploading = false;
	},Error:function(errObject){
		$.showErr(errObject.message);
	}});
}

function init_form_tag()
{
	$(".tag_dl .write_more").bind("click",function(){
		var more = $(this);
		var ipt = $(more).next();
		if(ipt.css("display")=="none")
		{
			$(ipt).show();
			$(more).html("关闭");
		}
		else
		{
			$(ipt).hide();
			$(more).html("展开");
		}		
	});
}

function init_form_star()
{
	//评分星级的测试
	var star_tip = ["评分，最高五分","差","一般","好","很好","非常好"];
	$("input.point_star").bind("onchange",function(){	
		var val = $(this).val();
		if(!isNaN(val))
		{
			$(this).parent().parent().parent().parent().find(".star_tip").html($(this).attr("rel")+star_tip[parseInt(val)]);
		}
	});
	$("input.point_star").bind("uichange",function(){	
		var val = $(this).attr("sector");
		
		if(!isNaN(val))
		{
			$(this).parent().parent().parent().parent().find(".star_tip").html($(this).attr("rel")+star_tip[parseInt(val)]);
		}		
	});
	
	$("input.point_star").each(function(i,o){
		var val = $(this).val();
		if(!isNaN(val))
		{
			$(this).parent().parent().parent().parent().find(".star_tip").html($(this).attr("rel")+star_tip[parseInt(val)]);
		}
	});
}
function init_tag_row()
{
	$(".tag_row .tag_content").each(function(i,tag_content){
		$(tag_content).find("label").each(function(ii,label){
			if($(label).offset().top-$(tag_content).offset().top>60)
			{
				$(label).hide();
				if($(tag_content).find("a.show_more").length==0)
				{
					var show_more = $("<a href='javascript:void(0);' rel='open' class='show_more'>展开</a>");
					$(tag_content).append(show_more);
					show_more.bind("click",function(){
						if($(show_more).attr("rel")=="open")
						{
							$(tag_content).find("label").show();
							$(show_more).html("收起");
							$(show_more).attr("rel","close");
						}
						else
						{
							$(tag_content).find("label").each(function(ii,label){
								if($(label).offset().top-$(tag_content).offset().top>60)
								{
									$(label).hide();
								}
							});
							$(show_more).html("展开");
							$(show_more).attr("rel","open");
						}//end else
					});
				}
			}
		});
		
		
	});
}