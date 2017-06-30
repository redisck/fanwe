<?php
class dcajaxModule extends MainBaseModule
{

	
	/**
	 * 获取外卖定位页面搜索到的餐厅数量
	 */
	
	public function get_dc_num(){
	
		global_run();
		$location=array();
		$xpoint=$location['dc_xpoint'] = floatval($_REQUEST['dc_xpoint']);
		$ypoint=$location['dc_ypoint'] = floatval($_REQUEST['dc_ypoint']);
		$location['dc_title']= strim($_REQUEST['dc_title']);
		$location['dc_content'] = strim($_REQUEST['dc_content']);
		$location['is_show_num'] = isset($_REQUEST['is_show_num'])?intval($_REQUEST['is_show_num']):1;
	
		//开始身边团购的地理定位
		$tname='sl';
	
		if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$append_field = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";
	
		}
	
		require_once APP_ROOT_PATH."system/model/dc.php";
		$deal_city_id = intval($GLOBALS['city']['id']);
		$param=array("city_id"=>$deal_city_id);
		$location_list=get_dc_location_list($type='is_dc',$limit='',$param,$tag=array(), $where='',$sort_field='',$append_field);
	
		$result=array();
		$location['dc_num']=count($location_list['list']);
	
		$GLOBALS['tmpl']->assign('location',$location);
		$result['html']=$GLOBALS['tmpl']->fetch('dc/get_dc_num.html');
		$result['dc_num']=$location['dc_num'];
		ajax_return($result);
	
	}
	

	/**
	 * 获取外卖的搜索记录
	 */
	public function get_dc_history(){
		$dc_search_history=es_cookie::get('dc_search_history');
	
		$dc_search_history=json_decode($dc_search_history,true);
		$is_show_num=isset($_REQUEST['is_show_num'])?intval($_REQUEST['is_show_num']):1;
		$result=array();
	
		if(count($dc_search_history)>0){
				
			foreach($dc_search_history as $k=>$location){
	
				$location['is_show_num']=$is_show_num;
				$GLOBALS['tmpl']->assign('location',$location);
				$result['html'].=$GLOBALS['tmpl']->fetch('dc/get_dc_num.html');
					
			}
			$result['status']=1;
				
		}else{
			$result['status']=0;
		}
	
		ajax_return($result);
	}
	

	/**
	 * 添加或者取消餐厅收藏
	 * status：操作返回的状态：status=0，操作失败，status=1，为收藏成功或取消收藏成功
	 * info，当state=0时的错误提示信息，如： 无效商家
	 * 
	 * 
	 */
	public function add_location_collect()
	{

		$param['location_id']=intval($_REQUEST['location_id']);
		$data = request_api("dcajax","add_location_collect",$param);
	
		if($data['user_login_status']==1)
		{
				$result['status']=$data['status'];
				$result['info']=$data['info'];
		
				ajax_return($result);
		}
		else
		{
			$url=wap_url('index','dctable',array('lid'=>$param['location_id']));
			es_session::set("wap_gopreview",$url);
			
			$result['status']=-1;
			$result['info']='未登录，请先登录';
			$result['jump']=wap_url('index','user#login');
			ajax_return($result);

		}
		
		
		
	}
	
	

	/**
	 * 外卖订餐页面加入购物车，并返回购物车模版
	 */
	public function dc_add_cart(){
	
		global_run();
		require_once APP_ROOT_PATH."system/model/dc.php";
		$location_id=intval($_REQUEST['location_id']);
		$supplier_id=intval($_REQUEST['supplier_id']);
		$distance=floatval($_REQUEST['distance']);
		$tid=intval($_REQUEST['tid']);
		$number= $_REQUEST['number'];
		$menu_id=intval($_REQUEST['menu_id']);
		$session_id=es_session::id();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
	
		/*
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where session_id = '".$session_id."'");
		*/
		$dc_cart_result = load_dc_cart_list(true,$location_id,$type=1);
	
	
		foreach($dc_cart_result['cart_list'] as $k=>$v){
			if($v['session_id']==$session_id && $v['menu_id']==$menu_id && $v['user_id']==$user_id){
				$cart_item=$v;
			}
		}
	
	
		if(!$cart_item)
		{
			$cart_item['session_id'] = $session_id;
			$cart_item['menu_id'] = $menu_id;
			$menu_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_menu where id=".$menu_id);
			$cart_item['name'] = strim($menu_info['name']);
			$cart_item['num'] = $number;
			$cart_item['unit_price']=$menu_info['price'];
			$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['num'];
			$cart_item['supplier_id'] = $supplier_id;
			$cart_item['location_id'] = $location_id;
			$cart_item['icon'] = $menu_info['image'];
			$cart_item['user_id'] = $user_id;
			$cart_item['add_time'] = NOW_TIME;
			$cart_item['cart_type'] = 1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_item);
	
		}
		else
		{
	
			$cart_item['num'] += $number;
			$cart_item['total_price'] = $cart_item['unit_price'] * $cart_item['num'];
			if($cart_item['num']<=0){
				$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and menu_id=".$menu_id);
			}else{
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_item,"UPDATE","id=".$cart_item['id']);
			}
		}
		
		$id_arr=array($location_id=>array('id'=>$location_id,'distance'=>$distance));
		$location_delivery_info=get_location_delivery_info($id_arr);
		$location_delivery=array();
		foreach($location_delivery_info as $kk=>$vv){
			$location_delivery=$vv;
		}
		$location_dc_table_cart = load_dc_cart_list(true,$location_id,$type=0);
		
		$is_in_open_time=is_in_open_time($location_id);
		if($location_dc_table_cart['total_data']['total_count'] == 0 ){
			if ($is_in_open_time==1){
				if($location_delivery['is_free_delivery']==2){
					$is_allow_add_cart=0;
				}else{
					$is_allow_add_cart=1;
				}
					
			}else{
				$is_allow_add_cart=0;
			}
		}else{
			$is_allow_add_cart=1;
		}
		
		$dclocation['id']=$location_id;
		$dclocation['location_delivery_info']=$location_delivery;
		$data['dclocation']=$dclocation;
		

		$dc_cart_result = load_dc_cart_list(true,$location_id,$type=1);
		$package_fee=get_location_package_fee($location_id,$dc_cart_result);
		$GLOBALS['tmpl']->assign("location_dc_cart",$dc_cart_result);

		$data['is_allow_add_cart']=$is_allow_add_cart;
		$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
		$less_money=$dclocation['location_delivery_info']['start_price']-$dc_cart_result['total_data']['total_price'];
		$less_table_money=$location_dc_table_cart['total_data']['total_price']-$dc_cart_result['total_data']['total_price'];
		$data['less_money']=$less_money;
		$data['less_table_money']=$less_table_money;
		$total_price=$dc_cart_result['total_data']['total_price'];
		if($location_dc_table_cart['total_data']['total_price'] > 0){
			$dc_type=1;
		}else{
			$dc_type=-1;
		}
		$data['dc_type']=$dc_type;
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->assign("tid",$tid);
		
		
		/*
		$GLOBALS['tmpl']->assign("package_fee",$package_fee);
		$GLOBALS['tmpl']->assign("total_price",$total_price);
		*/
		$res['html'] = $GLOBALS['tmpl']->fetch("dc/inc/dc_cart_total.html");
		
		$item=array();
		$item['id']=$menu_id;
		$item['cart_count']=$cart_item['num'];
		$GLOBALS['tmpl']->assign("item",$item);
		$res['cart_add'] = $GLOBALS['tmpl']->fetch("dc/inc/cart_add.html");
		
		$res['dc_cart'] = $GLOBALS['tmpl']->fetch("dc/inc/dc_cart.html");
		
		$res['status'] = 1;
		$res['total_price']=format_price($total_price);
		ajax_return($res);
	
	}
	
	
	/**
	 * 清空外卖购物车
	 */
	public function dc_cart_clear(){
		global_run();
		$location_id=intval($_REQUEST['location_id']);
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and location_id=".$location_id." and cart_type=1");
	
		if($GLOBALS['db']->affected_rows()>0){
			$res['status'] =1;
		}else{
			$res['status'] =0;
		}
		ajax_return($res);
	
	}

	
	

	/**
	 * 外卖订单页面，当登录成功时，把dc_cart中，把对应购物记录中user_id=0改为当前用户的ID
	 */
	public function update_dc_cart($location_id)
	{
		global_run();
		if(intval($GLOBALS['user_info']['id'])>0)
		{
			$cart_info['user_id']=intval($GLOBALS['user_info']['id']);
				
			if(!$location_id){
				$location_id=intval($_REQUEST['location_id']);
			}
				
				
			//$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where session_id='".es_session::id()."' and location_id=".$location_id." and user_id=".$GLOBALS['user_info']['id']);
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_info,$mode='UPDATE','session_id="'.es_session::id().'" and location_id='.$location_id." and user_id=0",$querymode = 'SILENT');
		}
	
	}
	
	
	/*
	 * 返回当前用户的ID
	 */
	public function get_user_id(){
	
		global_run();
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		ajax_return($user_id);
	}
	


	/**
	 *  设置外卖购物车中菜单的状态,用于外卖订单面切换  “只订座，不提前点菜”和“点菜定金”两种状态
	 * @param unknown_type $is_ajax_return  规定数据返回的类型，0为函数返回，数据返回到其他函数中使用，1为ajax返回，数据返回前端，供模版使用
	 * @param unknown_type $lid_info 函数所需要的数据
	 * @return 返回一个数组，当$is_ajax_return=1时，返回 到前端，当$is_ajax_return=0时，返回到函数，供函数使用
	 */
	public function set_dc_cart_menu_status($is_ajax_return=1,$lid_info=array()){
		global_run();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		
		if(count($lid_info)>0){
			$location_id=$lid_info['location_id'];
			$menu_status=$lid_info['menu_status'];	
		}else{
			$location_id=intval($_REQUEST['location_id']);
			$menu_status=intval($_REQUEST['menu_status']);
		}
		
		$cart_info['is_effect']=$menu_status;
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_info,$mode='UPDATE','user_id='.$user_id." and location_id=".$location_id." and session_id = '".es_session::id()."' and cart_type=1",$querymode = 'SILENT');
		if($GLOBALS['db']->affected_rows()>0){
			$result['status']=1;			
		}else{
			$result['status']=0;
		}
		if($is_ajax_return==1){
			ajax_return($result);
		}else{
			return $result;
		}
	}
	

	
	




	
}
?>