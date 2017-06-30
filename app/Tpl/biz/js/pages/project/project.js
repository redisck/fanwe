$(function(){
	
	
	/*关联商品*/
	$("button.add_related_deal").bind("click",function(){
		var query = new Object();
		query.deal_id=$("input[name='id']").val();
		query.edit_type=$("input[name='edit_type']").val();
		query.act = "add_related_deal";
		
		$('.relate_deal tr.alt').live('mouseover',function(){
			$(this).addClass('relate_cur');
		});
		$('.relate_deal tr.alt').live('mouseout',function(){
			$(this).removeClass('relate_cur');
		});
		
		$.ajax({
			url:AJAX_URL,
			data:query,
			type:"post",
			dataType:"json",
			success:function(result){
				$.weeboxs.open(result.html, {boxid:'add_goods_type_weebox',contentType:'text',showButton:false,title:"设置关联商品",width:570,height:550,type:'wee',onopen:function(){
						init_ui_button();
						init_ui_textbox();
						if($('#relate2 ul li').length>0){							
							$('#relate1').html($('#relate2').html());
						}
						load_relate();
						
						
						add_relate();
						del_relate();
						$('.sure_relate').bind('click',function(){
							$('#relate2').html($('#relate1').html());
							$.weeboxs.close();
						});

						$("#relate_deal .pages a").live("click",function(){							
							var url = $(this).attr("href");
							var query = new Object();
							query.deal_id=$("input[name='id']").val();
							query.is_shop = is_shop;
							query.related_deal=$("#relate1 input[name='related_deal']").val();
							query.act="load_relate";
							$.ajax({
								url:url,
								data:query,
								type:"post",
								dataType:"json",
								success: function(obj){									
									$("#relate_deal").html(obj.html);
									
								}
							});	
							return false;
						});						
						//提交数据
						$("form[name='search_relate']").submit(function(){
							keyword=$("input[name='search_relate_deal']").val();
							load_relate(keyword);
							return false;
						});
						
					},onclose:function(){	relate_unbind();}
				});
			}
		});
	});	
	
	
	
	
	
	
	/*选项卡切换*/
	$(".tab_item").bind("click",function(){
		var index = $(".tab_item").index(this);
		$(".tab_item").removeClass("curr");
		$(this).addClass("curr");
		$(".con_item").removeClass("curr");
		$(".con_item").eq(index).addClass("curr");
		
		if($(".tab_item").length ==(index+1) ){
			$("div.next_form_btn").hide();
		}else{
			$("div.next_form_btn").show();
		}
	});
	/*下一页表单切换*/
	$("button.next_form_btn").bind("click",function(){
		var curr_tab_index = $(".tab_item").index($(".tab_item.curr"));
		if(($(".tab_item").length-1)>curr_tab_index){
			$(".tab_item").removeClass("curr");
			$(".tab_item").eq((curr_tab_index+1)).addClass("curr");
			$(".con_item").removeClass("curr");
			$(".con_item").eq((curr_tab_index+1)).addClass("curr");
		}
		if(($(".tab_item").length-1) == (curr_tab_index+1)){
			$("div.next_form_btn").hide();
		}else{
			$("div.next_form_btn").show();
		}
		$("html,body").animate({scrollTop:0},200);
	});

	/**
	 * 选择城市
	 */
	$("button.select_city_btn").bind("click",function(){
		if($(".city_list_box").is(":hidden")){
			$(document).bind("click",function(e){ 
				var obj = e.srcElement ? e.srcElement : e.target;
				var target = $(obj); 
				if(target.closest(".city_list_box").length == 0 && target.closest(".city_select_box").length == 0){ 
					$(".city_list_box").hide();
					$(document).unbind("click");
				}else{
					$(".city_list_box").show();
				}
			});
		}else{
			$(".city_list_box").hide();
			$(document).unbind("click");
		}
	});
	
	/*顶部条件筛选*/
	$("select[name='filter_admin_check']").bind("change",function(){
		$("form[name='project_form']").submit();
	});

	
	/*删除*/
	$("button.del_btn").bind("click",function(){
		
		var query = new Object();
		query.act = "del";
		query.id = $(this).attr("data-id");
		$.showConfirm('<p style="text-align:center;">--待审核数据将会永久删除--</p><p style="text-align:center;">--审核通过的仅删除申请记录--</p> <p style="text-align:center;">确定删除吗？</p>',function(){
			$.ajax({ 
				url: ajax_url, 
				data: query,
				dataType: "json",
				success: function(obj){
					if(obj.status)
					{
						$.showSuccess(obj.info,function(){window.location.href=window.location.href;});
					}else
					{
						$.showErr(obj.info);
					}
				},
				error:function(ajaxobj)
				{
					if(ajaxobj.responseText!='')
					alert(ajaxobj.responseText);
				}
			
			});
		});
	});
	
	/*下架申请*/
	$("button.down_btn").bind("click",function(){
		var id = $(this).attr("data-id");
		
		if(id>0){
			$.showConfirm("确定要下架吗?下架后将无法在上架！",function(){
				var query = new Object();
				query.act = "down_line";
				query.id = id;
				$.ajax({ 
					url: ajax_url, 
					data: query,
					dataType: "json",
					success: function(obj){
						if(obj.status)
						{
							$.showSuccess(obj.info,function(){window.location.href=window.location.href;});
						}else
						{
							$.showErr(obj.info);
						}
					},
					error:function(ajaxobj)
					{
						if(ajaxobj.responseText!='')
						alert(ajaxobj.responseText);
					}
				
				});
			});
		}
		return false;
		
	});
	
/*JQUERY END*/
});

/**
 * 初始化子分类
 */
function init_sub_cate()
{
	var cate_id = $("select[name='cate_id']").val();
	var select_sub_cate = $("input[name='select_sub_cate']").val();
	var edit_type = $("input[name='edit_type']").val();
	if(cate_id>0)
	{
		var query = new Object();
		query.act = "load_sub_cate";
		query.cate_id = cate_id;
		query.edit_type = edit_type;
		query.id = $("input[name='id']").val();
		query.select_sub_cate = select_sub_cate;
		$.ajax({ 
			url: ajax_url, 
			data: query,
			dataType: "json",
			success: function(obj){
				if(obj.status)
				{
					$("#sub_cate_box").show();
					$("#sub_cate_box").find(".item_input").html(obj.html);
				}
				else
				{
					$("#sub_cate_box").hide();
				}
				
				init_ui_checkbox();
			},
			error:function(ajaxobj)
			{
				if(ajaxobj.responseText!='')
				alert(ajaxobj.responseText);
			}
		
		});
	}
	else
	{
		$("#sub_cate_box").hide();
		$("#sub_cate_box").find(".item_input").html("");
	}
}

/*加载商品分类*/
function load_goods_type(){
	var query = new Object();
	query.act = "load_goods_type";
	$.ajax({
		url:AJAX_URL,
		data:query,
		type:"post",
		success:function(data){
			$(".goods_type_box").html();
			$(".goods_type_box").html(data);
			init_ui_select();
			//绑定团购商品类型，显示属性
			$("select[name='deal_goods_type']").bind("change",function(){
				load_attr_html();
			});
		}
	});
}
/**
 * 初始化属性
 */
function load_attr_html()
{
		var deal_goods_type = $("select[name='deal_goods_type']").val();
		var id = $("input[name='id']").val();
		if(deal_goods_type>0)
		{
			var query = new Object();
			query.act = "load_attr_html";
			query.id = id;
			query.edit_type = $("input[name='edit_type']").val();
			query.deal_goods_type = deal_goods_type;
			
			$("#deal_attr_row").show();
			$.ajax({ 
				url:AJAX_URL, 
				data:query,
				success: function(obj){
					$("#deal_attr").html(obj);
					init_ui_checkbox();
				}
			});
		}
		else
		{
			$("#deal_attr_row").hide();
			$("#deal_attr").html("");
		}
}

//加载属性库存表
function load_attr_stock(obj)
{
	if(obj)
	{
		 attr_cfg_json = '';
		 attr_stock_json = '';
	}


	if($(".deal_attr_stock:checked").length>0)
	{
			$(".max_bought_row").find("input[name='max_bought']").val("");
			$(".max_bought_row").hide();
	}
	else
	{
			$(".max_bought_row").show();
	}

	//初始化deal_attr_stock_hd
	var deal_attr_stock_box = $(".deal_attr_stock");
	
	for(i=0;i<deal_attr_stock_box.length;i++)
	{
		var v = $(deal_attr_stock_box[i]).attr("checked")?1:0;
		$(deal_attr_stock_box[i]).parent().parent().find(".deal_attr_stock_hd").val(v);
	}
	var box = $(".deal_attr_stock:checked");
	if(!box.length>0)
	{
		$("#stock_table").html("");
		return;
	}
	
	var x = 1; //行数
	var y = 0; //列数
	var attr_id = 0;
	var attr_item_count = 0; //每组属性的个数
	var attr_arr = new Array();
	for(i=0;i<box.length;i++)
	{
		if($(box[i]).attr("rel")!=attr_id)
		{
			y++;
			attr_id = $(box[i]).attr("rel");
			attr_arr.push(attr_id);
		}
		else
		{
			attr_item_count++;
		}
	}
	
	//开始计算行数
	for(i=0;i<attr_arr.length;i++)
	{
		x = x * parseInt($("input[name='deal_attr_stock["+attr_arr[i]+"][]']:checked").length);
	}	
	var html = "<table width='100%' style='border: solid #ccc 1px;'>";	
	html += "<tr>";
	for(j=0;j<attr_arr.length;j++)
	{
		html+="<th>"+$("#title_"+attr_arr[j]).html()+"</th>";
	}
	html+="<th>库存数</th>";
	html +="</tr>";
	for(i=0;i<x;i++)
	{
		html += "<tr>";
		for(j=0;j<attr_arr.length;j++)
		{
			html+="<td><select name='stock_attr["+attr_arr[j]+"][]' class='attr_select_box' onchange='check_same(this);'><option value=''>未选择</option>";
			
			//开始获取相应的选取值
			var cbo = $("input[name='deal_attr_stock["+attr_arr[j]+"][]']:checked");
			for(k=0;k<cbo.length;k++)
			{
				var cnt = $(cbo[k]).parent().parent().find("*[name='deal_attr["+attr_arr[j]+"][]']").val();	
				html =  html + "<option value='"+cnt+"'";

				if(attr_cfg_json!=''&&attr_cfg_json[i][attr_arr[j]]==cnt)
				html = html + " selected='selected' ";
				html = html + ">"+cnt+"</option>";
			}
			
			html+="</select></td>";
		}
		html+="<td><input type='text' class='textbox' style='width: 50px;' name='stock_cfg_num[]' value='";
		if(attr_stock_json!='')
		html = html + attr_stock_json[i]['stock_cfg'];		
		html=html+"' /> <input type='hidden' name='stock_cfg[]' value='";
		if(attr_stock_json!='')
		html+=attr_stock_json[i]['attr_str'];
		html+="' /> </td>";
		html +="</tr>";
	}	
	html += "</table>";
	$("#stock_table").html(html);
}

//检测当前行的配置
function check_same(obj)
{
	var selectbox = $(obj).parent().parent().find("select");
	var row_value = '';
	for(i=0;i<selectbox.length;i++)
	{
		if($(selectbox[i]).val()!='')
			row_value += $(selectbox[i]).val();
		else
		{
			$(obj).parent().parent().find("input[name='stock_cfg[]']").val("");
			return;
		}
	}
	//开始检测是否存在该配置
	var stock_cfg = $("input[name='stock_cfg[]']");
	for(i=0;i<stock_cfg.length;i++)
	{
		if(row_value==$(stock_cfg[i]).val()&&row_value!=''&&stock_cfg[i]!=obj)
		{
			alert("规格组合重复了");
			$(obj).parent().parent().find("input[name='stock_cfg[]']").val("");
			$(obj).val("");
			return;
		}
	}
	$(obj).parent().parent().find("input[name='stock_cfg[]']").val(row_value);
}
/**
 * 选择城市
 * @param obj
 */
function select_city(obj){
	var city_id = $(obj).attr("data");
	$(".city_item").removeClass("curr");
	$(obj).addClass("curr");
	$(".selected_city").html("<span>"+$(obj).html()+"</span>");
	$("input[name='city_id']").val(city_id);
	$(".city_list_box").hide();
	$(document).unbind("click");
	
}

function init_select_city(){
	var city_id = parseInt($("input[name='city_id']").val());
	if(city_id>0){
		$(".city_item[data='"+city_id+"']").addClass("curr");
	}
	var obj = $(".city_item.curr");
	if(obj.length>0){
		var city_id = $(obj).attr("data");
		$(".selected_city").html("<span>"+$(obj).html()+"</span>");
		$("input[name='city_id']").val(city_id);
	}
	
}

/**
 * 载入筛选词
 */
function load_filter_box()
{
	var cate_id = $("select[name='shop_cate_id']").val();
	var id = $("input[name='id']").val();

	if(cate_id>0)
	{
		var query = new Object();
		query.act = "load_filter_box";
		query.shop_cate_id = cate_id;
		query.id = id;
		query.edit_type = $("input[name='edit_type']").val();
		$("#filter_row").show();
		
		
		$.ajax({ 
			url:AJAX_URL, 
			data:query,
			success: function(obj){
				$("#filter").html(obj);
				init_ui_textbox();
			}
		});
		

	}
	else
	{
		
		$("#filter_row").hide();
		$("#filter").html("");
	}
	
}

function add_relate(){
	$('.relate_deal tr.alt').live('click',function(){
		if($('#relate1 ul li').length>=6){
			$.showErr('关联商品最多6个,请先删除后再添加'); 
		}else{
			relate_name=$(this).find('td.relate_name').html();
			relate_img=$(this).find('img').attr('src');
			relate_id=$(this).attr('rel');
			relate_ids=$("#relate1 input[name='related_deal']").val();
			if(relate_ids == ''){
				$("#relate1 input[name='related_deal']").val(relate_id);
			}else{
				$("#relate1 input[name='related_deal']").val(relate_ids+','+relate_id);
			}
			$(this).hide();
			$("#relate1 ul").append('<li rel="'+relate_id+'"><a href="javascript:void(0);" title="'+relate_name+'"></a><div class="relate_img"><img  src="'+relate_img+'" style="width:70px;height:50px;"></div><div class="relate_name">'+relate_name.substr(0, 5)+'</div></li>');
			
		}
	});
	
}
function del_relate(){
	$('.select_relate li a').live('click',function(){

		relate_name=$(this).attr('title');
		relate_img=$(this).parent().find('img').attr('src');
		relate_id=$(this).parent().attr('rel');
		$(this).parent().remove();
		select_relates=$('#relate1 li');
		var str_relate_ids=[];
		for(i=0;i<select_relates.length;i++){			
			str_relate_ids.push($(select_relates[i]).attr('rel'));
		}		

		$("#relate1 input[name='related_deal']").val(str_relate_ids.join());
	
		$(".relate_deal table").append('<tr class="alt" rel="'+relate_id+'"><td class="add_relate">+</td><td class="detail"><img  src="'+relate_img+'" style="width:70px;height:50px;"></td><td class="relate_name">'+relate_name+'</td> </tr>');

});
	
}
function relate_unbind(){	
	$('.select_relate li a').die();
	$('.relate_deal tr.alt').die();
	$("#relate_deal .pages a").die();
}
function load_relate(keyword){
	var query = new Object();
	query.deal_id=$("input[name='id']").val();
	query.act = "load_relate";
	query.is_shop = is_shop;
	query.related_deal=$("#relate1 input[name='related_deal']").val();//alert(query.related_deal);
	if(keyword!='')query.keyword=keyword;
	
	$.ajax({
		url:AJAX_URL,
		data:query,
		type:"post",
		dataType:"json",
		success: function(obj){
			$("#relate_deal").html(obj.html);
			
		}
	});	

}