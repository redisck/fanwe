$(document).ready(function(){
	//加载合并购买
	load_relate_goods();
});

var is_load_relate_goods = false;
var stock_is_ok = true;

/**
 * 加载合并购买
*/
function load_relate_goods(){
	
	//加载关联购买的的物品
	$.load_relate_goods = function(){
		var query = new Object();
		query.deal_id = $("#relate_goods").attr("deal_id");
		query.supplier_id = $("#relate_goods").attr("supplier_id");
		query.supplier_name = $("#relate_goods").attr("supplier_name");
		query.act = "load_relate_goods";
		$("#relate_goods").html("<div class='loading'></div>");
		$.ajax({
			url:AJAX_URL,
			data:query,
			dataType:"json",
			type:"post",
			global:false,
			success:function(obj){						
				$("#relate_goods").html(obj.html);
				is_load_relate_goods = true;
				initRelateGoodUi();
				totalPrice();
				
			}				
		});
	};
	$.load_relate_goods();
	
	
}

/**
 * 初始化UI事件
*/
function initRelateGoodUi(){
	init_ui_button();
	init_relate_roll();
	init_ui_checkbox();

	initCheckPrice();
	
	$(".main-good-box .p-img").hover(function(){
		$(this).addClass("current");
	},function(){
		$(this).removeClass("current");
	});
	//绑定合并购买btn
	$('#relate_buy_btn').bind('click',function(){
		//
		relateBuy();
	});
}



//关联购买的ui滚动
var relate_idx = 0;
function init_relate_roll()
{	
	var item_width = 570; //单个元素宽度
	var box = $("#relate_content  ul");

	var count = $(box).find("li").length;	//计算个数
	var spanCount = Math.ceil(count/3);
	var offset_left = 0-relate_idx*item_width;
	$(box).css({"width":spanCount*item_width,"left":offset_left});
	$(box).find("li").each(function(i,o){
		$(o).attr("rel",i);
	});
	
	$("#relate_content ul.roll li").hover(function(){
		$(this).addClass("current");
	},function(){
		$(this).removeClass("current");
	});

	$("#relate_content").hover(function(){		
		$("#relate_content").stopTime();
		$(this).find(".t_left").animate({ 
		    left: 0
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: 0
		  }, { duration: 100,queue:false });
	},function(){
		$(this).find(".t_left").animate({ 
		    left: -40
		  },  { duration: 100,queue:false });
		$(this).find(".t_right").animate({ 
		    right: -40
		  },  { duration: 100,queue:false });
	});
	
	$("#relate_content").find(".t_left").bind("click",function(){
		relate_idx--;
		if(relate_idx<0)relate_idx=spanCount-1;
		roll_span(relate_idx,"#relate_content",item_width);
	});
	$("#relate_content").find(".t_right").bind("click",function(){
		relate_idx++;
		if(relate_idx>=spanCount)relate_idx=0;
		roll_span(relate_idx,"#relate_content",item_width);
	});
}

//封装的横向区域滚动
function roll_span(idx,box_id,item_width)
{
	var box = $(box_id+" ul.roll");
	var count = $(box).find("li").length;	//计算个数
	var spanCount = Math.ceil(count/3);
	
	var left = 0-idx*item_width;	
	$(box).animate({ 
		    left: left
		  }, {
			  "duration":200
		  } );	
}
//横向区域滚动

/**
 * 重新统计价格
*/
function initCheckPrice(){
	$('input[type="checkbox"][name="relateCheckbox"]').parent().unbind("click");
	$('input[type="checkbox"][name="relateCheckbox"]').parent().bind('click', function(e){  
		var cbo = $(this).find('input[type="checkbox"][name="relateCheckbox"]');
		var id = parseInt($(cbo).val());
		
		var item = jsonDeal[id];
		var attrItem = jsonAttr[id];
		
		if( !item ){
			return false;
		}
		
		if( !attrItem ){	//商品没有属性
			//验证库存
			var max_bought = parseInt(item['max_bought']);
			if( (max_bought!=-1)&&(max_bought<=0) ){
				$.showErr('该产品库存不足，无法购买');
				return false;
			}
			if($(cbo).attr("checked")){
				$(cbo).attr("checked",false);
			}else{
				$(cbo).attr("checked",true);
			}
			$(this).ui_checkbox({refresh:true});
			totalPrice();	
			return false;
		}else{			
			if( $(cbo).attr("checked")){	//取消这个商品
				$(cbo).parent().find('strong').html("&yen;"+parseFloat(jsonDeal[id]['current_price']).toFixed(2));
				$(cbo).attr("checked",false);
				$(this).ui_checkbox({refresh:true});
				totalPrice();
				return false;
			}
			
			//
			var popDiv = '<div class="component_rigth f_r">';
			for( var key in attrItem ){
				popDiv += 	'<div class="package_choose clearfix " key="'+key+'">'+
								'<span class="info_title f_l">'+key+'</span>'+
								'<div class="choose f_r clearfix active_parent" style="width:250px;">';
									for(var i in attrItem[key]){
										popDiv += 	'<a href="javascript:void(0);" dealid="'+id+'" id_1="'+i+'">'+
														attrItem[key][i]['name_1']+'<i class="iconfont">&#xe620;</i>'+
													'</a>';
									}
									popDiv += 	'</div>'+
							'</div>';
			}
			popDiv += '<br /><br /><span name="stock" style="display:none;">库存：0</span><button class="ui-button flow_btn" id="save_attr_btn" rel="white">保存规格</button></div>';

			$.weeboxs.open(popDiv, {boxid:'alertAttrBox',contentType:'text',type:'wee',showButton:false,title:"选择规格",width:400});
			
			//更新规格选项卡UI
			$("#alertAttrBox .package_choose").each(function(i,o){
				is_choose_all = false;  //有规格选项时，选中为false
				$(o).find("a").bind("click",function(){
					var spec_btn = $(this);  //当前按中的A
					var is_active = spec_btn.attr("active");
					$(o).find("a").removeAttr("active");
					$(o).removeAttr("is_choose");
					if(!is_active){
						spec_btn.attr("active",true);
						$(o).attr("is_choose",true);
					}
					var isAllChoose = true;
					var attr_key = [];
					var attr_id  = [];
					$("#alertAttrBox .package_choose").each(function(i1,o1){			
						$(o1).find("a").removeClass("active");
						if($(o1).attr("is_choose")){
							$(o1).find("a[active='true']").addClass("active");
							attr_id.push($(o1).find("a[active='true']").attr("id_1"));
							attr_key.push($(o1).attr("key"));
						}else{
							isAllChoose = false;
						}
					});	
					//显示库存span 对象
					var sobj = $("#alertAttrBox .component_rigth").find('span[name="stock"]');
					var is_checked   = isAllChoose?true:false;
					var is_show_sobj = false;
					var stock = -1;
					stock_is_ok = true;
					//如果所有规格全部选择，统计一下库存
					if( isAllChoose ){
						for( var kk=0;kk<attr_key.length;kk++ ){
							if( jsonStock[id][attr_id[kk]]&&parseInt(jsonStock[id][attr_id[kk]]['stock_cfg'])>=0 ){	//设置的是单规格库存
								stock = jsonStock[id][attr_id[kk]]['stock_cfg'];//sobj.html("库存："+jsonStock[id][attr_id[kk]]['stock_cfg']);
								is_show_sobj = true;
							}else{	//多规格组合库存
								attr_id.sort(function(a,b){return a>b?1:-1});//从小到大排序
								if(jsonStock[id][attr_id.join("_")]){
									stock = parseInt(jsonStock[id][attr_id.join("_")]['stock_cfg']);
									is_show_sobj = true;
								}							
							}
						}
					}
					if(stock==0){
						stock_is_ok = false;
					}
					//
					if(is_show_sobj&&stock!=-1){
						sobj.html( "库存："+stock );
						sobj.show();
					}else{
						sobj.hide();
					}
					
				});
			});
			init_ui_button();
			//绑定 保存规格 click
			$('#save_attr_btn').click(function(){saveAttr(cbo);});
		}
		
		
		
	});
}

/**
 * 合并购买
*/
function relateBuy(){
	var query     = new Object();
	query['mainId']  	= deal_id;	//主商品id
	
	query['id']   = [];
	query['attr_1'] = [];

//	//主商品属性
//	var attr_checked_ids = [];
//	var is_choose_all = true;
//	$("#main_package_choose .package_choose").each(function(i,o){		
//		if($(o).attr("is_choose")){
//			attr_checked_ids.push($(o).find("a[active='true']").attr("rel"));
//		}else{
//			is_choose_all = false;  //有一项规格未选中即为未选满
//		}			
//	});
//	
//	if( !is_choose_all ){
//		alert("请选择商品规格");
//		return false;
//	}
//	
//	query['id'].push(deal_id);
//	if( attr_checked_ids.length>0 ){
//		query['attr_1'].push(attr_checked_ids.join('_'));
//	}else{
//		query['attr_1'].push("0");
//	}
	
	//合并购买
	$('input[type="checkbox"][name="relateCheckbox"]:checkbox:checked').each(function(i,o){
		query['id'].push($(this).val());
		var id_1 = $(this).attr('id_1');
		if(!id_1){
			query['attr_1'].push('0');
		}else{
			query['attr_1'].push(id_1);
		}
		
	});
	if($("#main_goods:checked").length==0){$.showErr('搭配主项必需选择');return false;}
	if(query['id'].length<=0){$.showErr('请选择要购买的商品');return false;}
	query.act = "relate_add_cart";
	$.ajax({
		url:AJAX_URL,
		data:query,
		dataType:"json",
		type:"post",
		global:false,
		success:function(obj){						
			if(obj.status==1){
				
				$.weeboxs.open(obj.html, {boxid:'fanwe_cart_box',contentType:'text',showButton:false,title:"购物提示",width:570,type:'wee',onopen:function(){
					init_ui_button();
					$("#fanwe_cart_box").find("button[action='close']").bind("click",function(){
						var top = $("#cart_tip .cart_count").offset().top;
						var left = $("#cart_tip .cart_count").offset().left;
						$("#fanwe_cart_box").animate({width:0,height:0,left:left,top:top,opacity:0},{duration: 300,queue:false,complete:function(){
							$.weeboxs.close("fanwe_cart_box");
						}});
					});
					$("#fanwe_cart_box").find("button[action='checkout']").bind("click",function(){
						location.href = $(this).attr("action-url");
					});
				},onclose:function(){
					init_cart_tip();
				}});
			}else if(obj.status==-1){
				ajax_login();
			}else{
				$.showErr(obj.info);
			}	
		}				
	});
	
	
	

}

/**
 * 保存规格
*/
function saveAttr(cbo){
	var is_choose = true;
	var attr_idArr = [];//属性id数组
	var keyArr = [];	//属性规格名称数组
	var dealid = 0;
	$("#alertAttrBox .package_choose").each(function(i,o){
		
		if(!$(o).attr("is_choose")){
			is_choose = false;
		}else{
			//商品
			if(!dealid){
				dealid = parseInt($(o).find("a[active='true']").attr("dealid"));
			}
			//规格id
			id_1 = $(o).find("a[active='true']").attr("id_1");
			attr_idArr.push(id_1);
			keyArr.push($(o).attr('key'));
		}
	});	
	if( !is_choose ){
		$.showErr('请选择规格');
		return false;
	}
	//判断库存
	if(!stock_is_ok){
		$.showErr('库存不足');
		return false;
	}
	
	//绑定购买的物品 id
	var cbObj = $('input[type="checkbox"][name="relateCheckbox"][value="'+dealid+'"]');
	cbObj.attr("checked",true);
	cbObj.attr("id_1",attr_idArr.join('_'));//规格id用 '_' 依次分开
	cbObj.attr("key",keyArr.join('_'));//规格名称用 '_' 依次分开	
	
	$(cbo).attr("checked",true);
	$(cbo).parent().ui_checkbox({refresh:true});
	
	//统计总
	totalPrice();
	$(".dialog-close").click();
}

/**
 * 统计总价格
*/
function totalPrice(){
	var currP = 0;
	var origP = 0;
	var num   = 0;
	$('input[type="checkbox"][name="relateCheckbox"]:checkbox:checked').each(function(i,o){
		var id = $(this).attr('value');
		var id_1 = $(this).attr('id_1');
		var key  = $(this).attr('key');
		if( id_1&&key ){	//有规格属性
			currP += parseFloat(jsonDeal[$(this).val()]['current_price']);
			origP += parseFloat(jsonDeal[$(this).val()]['origin_price']);
			//加上属性附加价格
			var id_1Arr = id_1.split('_');
			var keyArr = key.split('_');
		
			for(var i=0;i<id_1Arr.length;i++){
				if( jsonAttr[id][keyArr[i]][id_1Arr[i]] ){
					currP += parseFloat(jsonAttr[id][keyArr[i]][id_1Arr[i]]['price']);
					//origP += parseFloat(jsonAttr[id][keyArr[i]][id_1Arr[i]]['price']);
				}
			}
			$(this).parent().find('strong').html("&yen;"+currP.toFixed(2));			
		}else{
			currP += parseFloat(jsonDeal[$(this).val()]['current_price']);
			origP += parseFloat(jsonDeal[$(this).val()]['origin_price']);
		}
		
		if($(this).attr("id")!="main_goods")
		num++;
	});

	$('#relateCheckNum').html(num);
	$('#relateCheckCurrPrice').html("￥"+currP.toFixed(2));
	$('#relateCheckOrigPrice').html(origP.toFixed(2));
	
}














