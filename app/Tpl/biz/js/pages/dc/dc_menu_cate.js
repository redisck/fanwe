$(function(){
	$(".cate_name_box").bind("click",function(){
		$(this).hide();
		$(this).parent().parent().find(".edit_item_txt").show();
		$(this).parent().parent().find(".edit_item_txt input").focus();
	});
	
	$(".edit_save").bind("click",function(){
		edit_menu_name($(this));
	});
	
	$(".edit_item_txt input").bind("blur",function(){
		edit_menu_name($(this));
	});
	
	
	$(".is_effect_btn").bind("click",function(){
		var obj = $(this);
		var id = $(this).attr("data-id");
		var query = new Object();
		query.act = "dc_menu_cate_status";
		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					if(data.is_effect ==1){
						$(obj).html("&#xe612;");
					}else{
						$(obj).html("&#xe60b");
					}
				}else{
					$.showErr(data.info);
				}
			}
		});
	});
	
	$(".sort_item").bind("blur",function(){
		var obj = $(this);
		var id = $(this).attr("data-id");
		var sort = $(this).val();
		var old_sort = $(this).attr("data-sort");
		var query = new Object();
		query.act = "do_menu_cate_sort";
		query.id = id;
		query.sort = sort;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					$(obj).attr("data-sort",sort);
				}else{
					$.showErr(data.info);
					$(obj).val(old_sort);
				}
			}
		});
	});
	
//	/*新增菜单分类*/
	$("button.add_menu_cate").bind("click",function(){
		var location_id = $(this).attr("data-id");
		var query = new Object();
		query.act = "load_add_menu_cate_weebox";
		query.location_id = location_id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(result){
				$.weeboxs.open(result.html, {boxid:'add_menu_cate_weebox',contentType:'text',showButton:false,title:"添加菜单分类",width:570,type:'wee',onopen:function(){
						init_ui_button();
						init_ui_textbox();
						//提交数据
						$("form[name='add_menu_cate_form']").submit(function(){
							var form = $("form[name='add_menu_cate_form']");
							
							if($.trim($("input[name='cate_name']").val()) == ''){
								$.showErr("请输入新增分类名称");
								return false;
							}
							
							var query = $(form).serialize();
							var url = $(form).attr("action");
							$.ajax({
								url:url,
								data:query,
								type:"post",
								dataType:"json",
								success:function(data){
									if(data.status==1){
										$.showSuccess("添加成功",function(){window.location=data.jump;});
									}else{
										$.showErr(data.info);
										
									}
								}
							});
							
							return false;
						});
						
					}
				});
			}
		});
	});
});

function edit_menu_name(obj){
	var name =$.trim($(obj).parent().find("input").val());
	var id = $(obj).parent().find("input").attr("data-id");
	if(name.length == 0){
		$.showErr("分类名称不能为空!");
		return false;
	}
	var hide_div_obj = $(obj).parent();
	var show_div_obj = $(obj).parent().parent().find(".cate_name_box");
	var txt_obj = $(obj).parent().parent().find(".name_edit_btn");
	var query = new Object();
	query.act = "do_edit_menu_cate_name";
	query.name = name; 
	query.id = id;
	$.ajax({
		url:ajax_url,
		data:query,
		type:"post",
		dataType:"json",
		success:function(data){
			if(data.status){
				$(txt_obj).html(name);
				$(hide_div_obj).hide();
				$(show_div_obj).show();
			}else{
				$.showErr(data.info);
			}
		}
	});
}
function del_menu_cate(obj){
	var id = $(obj).attr("data-id");

	$.showConfirm("确定删除吗？<br/>删除将无法恢复",function(){
		var query = new Object();
		query.act = "dc_menu_cate_del";

		query.id = id;
		$.ajax({
			url:ajax_url,
			data:query,
			type:"post",
			dataType:"json",
			success:function(data){
				if(data.status){
					window.location = data.jump;
				}else{
					$.showErr(data.info);
				}
			}
		});
	});
	
}