/**
 * 初始显示
*/
function init_set(){
	new Swipe(document.getElementById('relate-slider'), {
		speed:500,
		auto:5000,
		callback: function(){
			var lis = $(this.element).next("ol").children();
			lis.removeClass("on").eq(this.index).addClass("on");
		}
	});
	
	//合并购买按钮事件
	$('#relateBuyBtn').click(function(){relateBy();});	
	
	$('#relate-box a').click(function(){
		dealItemClick($(this).attr('deal_id'));
	});
	$('.checkdiv').click(function(){
		dealItemClick($(this).attr('deal_id'));
	});
	
	//没有产品属性 并且有库存
	var max_bought = parseInt(jsonDeal[main_id]['max_bought']);
	if( (max_bought==-1)||(max_bought>0) ){
		if( !jsonAttr[main_id] ){
			$('#checkdiv_'+main_id).show();
			//重新统计价格
			totalPrice();
		}
	}
}

/**
 * 选择某个商品
*/
function dealItemClick(deal_id){
	deal_id = parseInt(deal_id);
	if( deal_id<=0 ){ return false; }
	var obj = $('#checkdiv_'+deal_id);
	if(!obj){ return false; }
	
	if( obj.is(":visible") ){
		//如果是主产品并且没有属性不允许取消选择
		if( (deal_id!=main_id)||(jsonAttr[deal_id]) ){
			obj.hide();
			//重新统计价格
			totalPrice();
		}
	}else{
		//选中有规格的产品
		if( jsonAttr[deal_id] ){
			var popDiv = '';
			//html += '规格：<label><input name="Fruit" type="radio" value="" />苹果 </label><label><input name="Fruit" type="radio" value="" />香蕉 </label><label><input name="Fruit" type="radio" value="" />梨 </label>';	
			var attrItem = jsonAttr[deal_id];
			
			var popDiv = '<div class="pop-div">';
			for( var key in attrItem ){
				popDiv +=	key+':<select name="'+key+'" onchange="changAttr('+deal_id+');">';
				popDiv +=	'<option value="0">请选择</option>';
				for(var i in attrItem[key]){
					popDiv +=	'<option value="'+i+'">'+attrItem[key][i]['name_1']+'</option>';
				}
				popDiv +=	'</select><br />';
			}
			popDiv += '<span name="stock" style="display:none;">库存：0</span></div>';
			
			layer.open({
				shadeClose: true,
				content: popDiv,
				btn: ['保存规格'],
				yes: function(layer_index){
					//设定规格
					var is_choose = true;
					var attr_id = [];//属性id数组
					var attr_key = [];	//属性规格名称数组
					$('.pop-div select').each(function(index, element) {
						var itemDealId = parseInt($(this).val());
                        if( itemDealId==0 ){
							is_choose = false;
						}else{
							attr_id.push(itemDealId);
							attr_key.push($(this).attr('name'));
						}
                    });
					if( !is_choose ){
						alert('你有规格没有选择!');
					}else{
						if(stock_is_ok){
							obj.attr("id_1",attr_id.join('_'));//规格id用 '_' 依次分开
							obj.attr("key",attr_key.join('_'));//规格名称用 '_' 依次分开	
							obj.show();
							layer.close(layer_index); 
							//重新统计价格
							totalPrice();
						}else{
							alert('库存不足');
						}						
					}
				}
			});
		}else{
			var max_bought = parseInt(jsonDeal[deal_id]['max_bought']);
			if( (max_bought==-1)||(max_bought>0) ){
				obj.show();
				//重新统计价格
				totalPrice();
			}else{
				alert('该产品库存不足!');
//				layer.open({
//					content: '该产品库存不足',
//					time: 2
//				});
			}
		}
	}
}

/**
 * 更改规格
*/
stock_is_ok = false;//是否可以购买
function changAttr(deal_id){
	//设定规格
	var is_choose = true;
	var attr_id = [];//属性id数组
	var attr_key = [];	//属性规格名称数组
	$('.pop-div select').each(function(index, element) {
		var itemDealId = parseInt($(this).val());
		if( itemDealId==0 ){
			is_choose = false;
		}else{
			attr_id.push(itemDealId);
			attr_key.push($(this).attr('name'));
		}
	});
	if( is_choose ){
		var sobj = $(".pop-div").find('span[name="stock"]');
		sobj.hide();
		var is_show_sobj = false;
		var stock = -1;
		stock_is_ok = true;
		for( var kk=0;kk<attr_key.length;kk++ ){
			if( jsonStock[deal_id][attr_id[kk]]&&parseInt(jsonStock[deal_id][attr_id[kk]]['stock_cfg'])>=0 ){	//设置的是单规格库存
				stock = jsonStock[deal_id][attr_id[kk]]['stock_cfg'];//sobj.html("库存："+jsonStock[deal_id][attr_id[kk]]['stock_cfg']);
				is_show_sobj = true;
			}else{	//多规格组合库存
				attr_id.sort(function(a,b){return a>b?1:-1});//从小到大排序
				if(jsonStock[deal_id][attr_id.join("_")]){
					stock = parseInt(jsonStock[deal_id][attr_id.join("_")]['stock_cfg']);
					is_show_sobj = true;
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
	}
	
}


/**
 * 重新统计价格
*/
function totalPrice(){
	var currP = 0;
	var origP = 0;
	var num   = 0;
	//搭配价格
	$('#relate-box .checkdiv').each(function(index, element) {
        if( $(this).is(":visible") ){	//选中
			var id = parseInt($(this).attr('deal_id'));
			var id_1 = $(this).attr('id_1');
			var key  = $(this).attr('key');
			
			currP += parseFloat(jsonDeal[id]['current_price']);
			origP += parseFloat(jsonDeal[id]['origin_price']);
			if( id_1&&key ){	//有规格属性
				//加上属性附加价格
				var id_1Arr = id_1.split('_');
				var attr_key = key.split('_');
			
				for(var i=0;i<id_1Arr.length;i++){
					if( jsonAttr[id][attr_key[i]][id_1Arr[i]] ){
						currP += parseFloat(jsonAttr[id][attr_key[i]][id_1Arr[i]]['price']);
						//origP += parseFloat(jsonAttr[id][attr_key[i]][id_1Arr[i]]['price']);
					}
				}	
			}
			if( id!=main_id ){
				num++;
			}
		}
    });
	$('#relateCheckNum').html(num);
	$('#relateCheckCurrPrice').html("￥"+currP.toFixed(2));
	$('#relateCheckOrigPrice').html(origP.toFixed(2));
}

/**
 * 合并购买
*/
function relateBy(){
	var idArray = [];
	var dealAttrArray = {};
	
	$('#relate-box .checkdiv').each(function(index, element) {
        if( $(this).is(":visible") ){	//选中
			var id = parseInt($(this).attr('deal_id'));
			var id_1 = $(this).attr('id_1');
			var key  = $(this).attr('key');
			idArray.push(id);
			dealAttrArray[id] = {};
			
			if( id_1&&key ){	//有规格属性
				
				var id_1Arr = id_1.split('_');
				var attr_key = key.split('_');
			
				for(var i=0;i<id_1Arr.length;i++){
					dealAttrArray[id][jsonAttr[id][attr_key[i]][id_1Arr[i]]['goods_type_attr_id']] = id_1Arr[i];
				}
			}
		}
    });
	if( idArray.length<=0 ){
		alert('请选择你要购买的商品');
//		layer.open({
//			content: '请选择你要购买的商品!',
//			time:2,
//		});
		return false;
	}
	//主商品没有选择
	if(!$('#checkdiv_'+main_id).is(":visible")){
		alert('搭配主项必需选择');
//		layer.open({
//			content: '请选择你要购买的商品!',
//			time:2,
//		});
		return false;
	}
	
	$.ajax({
		url:$('#goods-form').attr('action'),
		data:{'id':idArray,'dealAttrArray':dealAttrArray},
		dataType:"json",
		type:"post",
		global:false,
		success:function(obj){
			if( obj.jump ){
				location.href = obj.jump;
			}
		}
	});
	//
	
}




