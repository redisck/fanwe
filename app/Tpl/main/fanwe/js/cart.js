
/**
 * 加入购物车
 * @param id 商品ID
 * @param attr 购买的属性规格 array()
 * @param number  购买数量
 */
function add_cart(id,number,attr)
{
	attr = $.extend([],attr);
	var ajaxurl = AJAX_URL;
	var query = new Object();
	query['id'] = id;
	query['attr[]'] = attr;
	query['number'] = number;
	query['act'] = "addcart";
	$.ajax({
		url: ajaxurl,
		data: query,
		dataType: "json",
		type: "post",
		success: function(obj){
			if(obj.status==1)
			{
				
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
			}
			else if(obj.status==-1)
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
//			if(ajaxobj.responseText!='')
//			alert(ajaxobj.responseText);
		}
	});

}