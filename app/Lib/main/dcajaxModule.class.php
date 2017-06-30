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
	 * 添加餐厅收藏
	 */
	public function add_location_collect()
	{
		global_run();
		if(intval($GLOBALS['user_info']['id'])==0)
		{
			$result['status'] = -1;
			$result['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			ajax_return($result);
		}
		else
		{
			$location_id = intval($_REQUEST['location_id']);
			$location_info = $GLOBALS['db']->getRow("select id from ".DB_PREFIX."supplier_location where id = ".$location_id." and is_effect = 1");
			if($location_info)
			{
	
				$sql = "INSERT INTO `".DB_PREFIX."dc_location_sc` (`id`,`location_id`, `user_id`, `add_time`) select '','".$location_info['id']."','".intval($GLOBALS['user_info']['id'])."','".get_gmtime()."' from dual where not exists (select * from `".DB_PREFIX."dc_location_sc` where `location_id`= '".$location_info['id']."' and `user_id` = ".intval($GLOBALS['user_info']['id']).")";
				$GLOBALS['db']->query($sql);
				if($GLOBALS['db']->affected_rows()>0){
					$result['info'] = $GLOBALS['lang']['COLLECT_SUCCESS'];
					$result['status'] = 1;
					$result['count']=$GLOBALS['db']->getOne("select count(*) from `".DB_PREFIX."dc_location_sc` where `location_id`= ".$location_info['id']);
					ajax_return($result);
				}else{
					$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_location_sc where location_id=".$location_info['id']." and user_id=".intval($GLOBALS['user_info']['id']));
					if($GLOBALS['db']->affected_rows()>0){
						$result['info'] = $GLOBALS['lang']['LOCATION_COLLECT_CANCEL'];
						$result['status'] = 2;
						$result['count']=$GLOBALS['db']->getOne("select count(*) from `".DB_PREFIX."dc_location_sc` where `location_id`= ".$location_info['id']);
						ajax_return($result);
					}
	
				}
			}
			else
			{
				$result['status'] = 0;
				$result['info'] = $GLOBALS['lang']['INVALID_LOCATION'];
				ajax_return($result);
			}
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
		
		
		$dclocation['location_delivery_info']=$location_delivery;
		$GLOBALS['tmpl']->assign("dclocation",$dclocation);
		$dc_cart_result = load_dc_cart_list(true,$location_id,$type=1);
		$package_fee=get_location_package_fee($location_id,$dc_cart_result);
		$GLOBALS['tmpl']->assign("location_dc_cart",$dc_cart_result);
		$GLOBALS['tmpl']->assign("is_allow_add_cart",$is_allow_add_cart);
		$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
		$less_money=$dclocation['location_delivery_info']['start_price']-$dc_cart_result['total_data']['total_price'];
		$less_table_money=$location_dc_table_cart['total_data']['total_price']-$dc_cart_result['total_data']['total_price'];
		$GLOBALS['tmpl']->assign("less_money",$less_money);
		$GLOBALS['tmpl']->assign("less_table_money",$less_table_money);
		$total_price=$package_fee+$dc_cart_result['total_data']['total_price'];
		$GLOBALS['tmpl']->assign("package_fee",$package_fee);
		$GLOBALS['tmpl']->assign("total_price",$total_price);
		$res['html'] = $GLOBALS['tmpl']->fetch("dc/inc/dc_cart.html");
		$res['status'] = 1;
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
	public function update_dc_cart()
	{
		global_run();
		if(intval($GLOBALS['user_info']['id'])>0)
		{
			$cart_info['user_id']=intval($GLOBALS['user_info']['id']);
			$location_id=intval($_REQUEST['location_id']);
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where session_id='".es_session::id()."' and location_id=".$location_id." and user_id=".$GLOBALS['user_info']['id']);
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_cart",$cart_info,$mode='UPDATE','session_id="'.es_session::id().'" and location_id='.$location_id." and user_id=0",$querymode = 'SILENT');
		}

	}
	

	/**
	 * 外卖订单页面，改变地理定位，并保存外卖送餐地址，重新计算配送费，返回购物车金额总数和模版
	 */
	public function save_consignee_info(){
		global_run();
		if(isset($_REQUEST['xpoint'])){
			$consignee_info['xpoint']=strim($_REQUEST['xpoint']);
		}
		if(isset($_REQUEST['ypoint'])){
			$consignee_info['ypoint']=strim($_REQUEST['ypoint']);
		}
		if(isset($_REQUEST['api_address'])){
			$consignee_info['api_address']=strim($_REQUEST['api_address']);
		}
		if(isset($_REQUEST['address'])){
			$consignee_info['address']=strim($_REQUEST['address']);
		}
		$consignee_info['consignee']=strim($_REQUEST['consignee']);
		$consignee_info['mobile']=$_REQUEST['mobile'];
		$consignee_info['is_main']=1;
		$location_id=intval($_REQUEST['location_id']);
		$consignee_info['user_id']=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
			
		$consignee_id=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."dc_consignee where user_id=".$consignee_info['user_id']);
		if($consignee_id > 0){
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info,$mode='UPDATE','user_id='.$consignee_info['user_id'],$querymode = 'SILENT');
	
		}else{
				
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info);
		}
		
		$id=$GLOBALS['db']->insert_id();
		if($id>0){
			$result['id']=$id;
			
		}else{
			$result['id']=$consignee_id;
			
		}	
		$result['status']=$GLOBALS['db']->affected_rows();

		ajax_return($result);
	}
	
	/*
	 * 删除餐桌或者包间的预订购物车记录
	 */
	public function delete_dc_cart_table(){

		global_run();
		$location_id=intval($_REQUEST['location_id']);

		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where user_id=".$GLOBALS['user_info']['id']." and location_id=".$location_id." and session_id='".es_session::id()."'");
		$result['status']=$GLOBALS['db']->affected_rows();
		if($result['status']>0){
			$result['jump']=url('index','dcres');
		}
		ajax_return($result);
	}
	
	/*
	 * 返回当前用户的ID
	 */
	public function get_user_id(){
	
		global_run();
		$user_id=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
		ajax_return($user_id);
	}
	

	
	
	/*
	 * 搜索餐厅和美食
	*
	*/
	public function get_location_search(){
	
		$keyword=strim($_REQUEST['kw']);
	
		/*  搜索餐厅
		 */
		$location_info=$GLOBALS['db']->getAll("select id,name,preview,dc_buy_count from ".DB_PREFIX."supplier_location where name like '%".$keyword."%' and is_dc=1");
		require_once APP_ROOT_PATH."system/model/dc.php";
		foreach($location_info as $k=>$v){
			$location_info[$k]['url']=url('index','dcbuy',array('lid'=>$v['id']));
		}
		$result_arr['location']=$location_info;
		$result_arr['lid_count']=count($location_info);
	
		/*
		 * 搜索美食
		*/
	
		$menu_info=$GLOBALS['db']->getAll("select id,name,price,location_id from ".DB_PREFIX."dc_menu where name like '%".$keyword."%' and is_effect=1");
		foreach($menu_info as $k=>$v){
			$lid_info=$GLOBALS['db']->getRow("select id,name from ".DB_PREFIX."supplier_location where id=".$v['location_id']." and is_dc=1");
				
			if($lid_info){
				$menu_info[$k]['lid_name']=$lid_info['name'];
				$menu_info[$k]['url']=url('index','dcbuy',array('lid'=>$lid_info['id']));
			}else{
				unset($menu_info[$k]);
			}
		}
		$result_arr['menu']=$menu_info;
		$result_arr['menu_count']=count($menu_info);
	
		$GLOBALS['tmpl']->assign("result_arr",$result_arr);
	
		$result['html']=$GLOBALS['tmpl']->fetch('dc/inc/dc_search_location_result.html');
		$result['lid_count']=count($location_info);
		$result['menu_count']=count($menu_info);
		ajax_return($result);
	}
	
	
	public function save_user_consignee(){
			global_run();
			if(isset($_REQUEST['xpoint'])){
				$consignee_info['xpoint']=strim($_REQUEST['xpoint']);
			}
			if(isset($_REQUEST['ypoint'])){
				$consignee_info['ypoint']=strim($_REQUEST['ypoint']);
			}
			if(isset($_REQUEST['api_address'])){
				$consignee_info['api_address']=strim($_REQUEST['api_address']);
			}
			if(isset($_REQUEST['address'])){
				$consignee_info['address']=strim($_REQUEST['address']);
			}		
			$consignee_info['consignee']=strim($_REQUEST['consignee']);
			$consignee_info['mobile']=$_REQUEST['mobile'];
			$id=intval($_REQUEST['id']);
			$consignee_info['user_id']=isset($GLOBALS['user_info']['id'])?intval($GLOBALS['user_info']['id']):0;
			$user_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where user_id=".$consignee_info['user_id']." and id=".$id);
			if($user_count > 0){
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info,$mode='UPDATE','user_id='.$consignee_info['user_id'].' and id='.$id,$querymode = 'SILENT');
				$result['info']=2;
				$result['url']=url('index','dc_consignee',array());
			}else{
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info);
			$result['info']=1;
			$result['url']=url('index','dc_consignee',array());
			}
			
			ajax_return($result);
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
	
	/**
	 * 计算购物车总价
	 */
	public function dc_count_buy_total()
	{
		global_run();
		require_once APP_ROOT_PATH."system/model/dc.php";
		$consignee_id = intval($_REQUEST['consignee_id']); //配送地区
		
		$account_money =  floatval($_REQUEST['account_money']); //余额
		$ecvsn = $_REQUEST['ecvsn']?addslashes(trim($_REQUEST['ecvsn'])):'';
		$ecvpassword = $_REQUEST['ecvpassword']?addslashes(trim($_REQUEST['ecvpassword'])):'';
		$payment = intval($_REQUEST['payment']);
		$location_id = intval($_REQUEST['location_id']);
		$reload = intval($_REQUEST['reload']);
		$dc_type=isset($_REQUEST['dc_type'])?intval($_REQUEST['dc_type']):-1;//预订方式，如果有dc_type参数传递过来，就代表预订，则不享受促销优惠，如果为-1，代表享受促销优惠

		$all_account_money = intval($_REQUEST['all_account_money']);

		$payment_id = intval($_REQUEST['payment_id']);  //$payment_id==0,代表在线支付
		$bank_id = strim(trim($_REQUEST['bank_id']));
	
		$user_id = intval($GLOBALS['user_info']['id']);
		$session_id = es_session::id();
		//外卖验证是否有配送地址
		$dc_consignee=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where id=".$consignee_id);
			$consignee_info_error=0;
		if($dc_consignee==0 && $dc_type==-1 && $reload==0){
			$consignee_info_error=1;
		}
		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
		$GLOBALS['tmpl']->assign('location_dc_table_cart',$location_dc_table_cart);
		$GLOBALS['tmpl']->assign('location_dc_cart',$location_dc_cart);
		
		$result = dc_count_buy_total($location_id,$location_dc_table_cart,$location_dc_cart,$payment_id,$dc_type,$consignee_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$bank_id,0,0,0);
		//验证是否超过该商家配送范围，$is_out_scale=0为不超出，$is_out_scale=1为超出商家配送范围
		$is_out_scale=0;
		if($location_dc_table_cart['total_data']['total_price']==0 && $location_dc_cart['total_data']['total_price']>0){
			if($result['location_delivery_info']['is_free_delivery']==2){
				$is_out_scale=1;
			}
			
		}
		$result['consignee_info_error']=$consignee_info_error;
		$result['is_out_scale']=$is_out_scale;
		$GLOBALS['tmpl']->assign("result",$result);
		$html = $GLOBALS['tmpl']->fetch("dc/inc/dc_order_total.html");
		$data = $result;
		$data['html'] = $html;
		
			$data['expire'] = false;
		if($location_dc_table_cart['total_data']['total_price']==0 && $location_dc_cart['total_data']['total_price']==0){
			
			$data['expire'] = true;
		}
		if($data['expire'])$data['jump'] = url("index","dcbuy",array('lid'=>$location_id));
		ajax_return($data);
	}
	
	
	/**
	 * 计算购物车总价
	 */
	public function dc_count_order_total()
	{
		global_run();
		require_once APP_ROOT_PATH."system/model/dc.php";
		
		
		$consignee_id = intval($_REQUEST['consignee_id']); //配送地区
	
		$account_money =  floatval($_REQUEST['account_money']); //余额
		$ecvsn = $_REQUEST['ecvsn']?addslashes(trim($_REQUEST['ecvsn'])):'';
		$ecvpassword = $_REQUEST['ecvpassword']?addslashes(trim($_REQUEST['ecvpassword'])):'';
		$payment = intval($_REQUEST['payment']);
		$location_id = intval($_REQUEST['location_id']);
		$reload = intval($_REQUEST['reload']);
		$dc_type=isset($_REQUEST['dc_type'])?intval($_REQUEST['dc_type']):-1;//预订方式，如果有dc_type参数传递过来，就代表预订，则不享受促销优惠，如果为-1，代表享受促销优惠
	
		$all_account_money = intval($_REQUEST['all_account_money']);
	
		$payment_id = intval($_REQUEST['payment_id']);  //$payment_id==0,代表在线支付
		$bank_id = strim(trim($_REQUEST['bank_id']));
	
		$user_id = intval($GLOBALS['user_info']['id']);
		$session_id = es_session::id();
		//外卖验证是否有配送地址
		$dc_consignee=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where id=".$consignee_id);
		$consignee_info_error=0;
		if($dc_consignee==0 && $dc_type==-1 && $reload==0){
			$consignee_info_error=1;
		}
		
		$id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$id." and type_del = 0 and pay_status <> 1 and order_status <> 1 and user_id =".intval($GLOBALS['user_info']['id']));
		if(!$order_info)
		{
			app_redirect(url("index","dc"));
		}
		$location_id=$order_info['location_id'];
		$is_location_close=$GLOBALS['db']->getOne("select is_close from ".DB_PREFIX."supplier_location where id=".$location_id);
		if($is_location_close==1){
			app_redirect(url('index','dc'));
		
		}
		
		$order_menu=unserialize($order_info['order_menu']);
		
		$location_dc_table_cart=$order_menu['rs_list'];
		$location_dc_cart=$order_menu['menu_list'];
		
		$GLOBALS['tmpl']->assign('location_dc_table_cart',$location_dc_table_cart);
		$GLOBALS['tmpl']->assign('location_dc_cart',$location_dc_cart);
		$result = dc_count_buy_total($location_id,$location_dc_table_cart,$location_dc_cart,$payment_id,$dc_type,$consignee_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$bank_id,$order_info['account_money'],$order_info['ecv_money'],$order_info['promote_amount']);
		//验证是否超过该商家配送范围，$is_out_scale=0为不超出，$is_out_scale=1为超出商家配送范围
		$is_out_scale=0;
		if($location_dc_table_cart['total_data']['total_price']==0 && $location_dc_cart['total_data']['total_price']>0){
			if($result['location_delivery_info']['is_free_delivery']==2){
				$is_out_scale=1;
			}
				
		}
		$result['consignee_info_error']=$consignee_info_error;
		$result['is_out_scale']=$is_out_scale;
		$GLOBALS['tmpl']->assign("result",$result);
		$html = $GLOBALS['tmpl']->fetch("dc/inc/dc_order_total.html");
		$data = $result;
		$data['html'] = $html;
	
		$data['expire'] = false;
		if($location_dc_table_cart['total_data']['total_price']==0 && $location_dc_cart['total_data']['total_price']==0){
				
			$data['expire'] = true;
		}
		if($data['expire'])$data['jump'] = url("index","dcbuy",array('lid'=>$location_id));
		ajax_return($data);
	}
	
	/**
	 * 验证优惠券
	 */
	public function verify_ecv()
	{
		global_run();
		$ecvsn = strim($_REQUEST['ecvsn']);
		$ecvpassword = strim($_REQUEST['ecvpassword']);
		$user_id = intval($GLOBALS['user_info']['id']);
		$now = NOW_TIME;
		$ecv_sql = "select e.*,et.name from ".DB_PREFIX."ecv as e left join ".
				DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
				$ecvsn."' and e.password = '".
				$ecvpassword."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
				"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
				"and (e.user_id = ".$user_id." or e.user_id = 0)";
	
		$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
		if($ecv_data)
			$data['info'] = "[".$ecv_data['name']."] ".$GLOBALS['lang']['IS_VALID'];
		else
			$data['info'] = $GLOBALS['lang']['IS_INVALID_ECV'];
		ajax_return($data);
	}
	/**
	 * 判断是否付款超时
	 */
	public function pay_is_out_time(){
		global_run();
		if(check_save_login()!=LOGIN_STATUS_LOGINED)
		{	$result['status']=1000;
			ajax_return($result);
		}
		if(isset($_REQUEST['pay_id'])){
			$pay_id = intval($_REQUEST['pay_id']);
			$pay_info=$GLOBALS['db']->getRow("select create_time from ".DB_PREFIX."payment_notice where id=".$pay_id);
			$pay_time=$pay_info['create_time'];
		}
		if(isset($_REQUEST['order_id'])){
			$order_id = intval($_REQUEST['order_id']);
			$order_info=$GLOBALS['db']->getRow("select create_time from ".DB_PREFIX."dc_order where id=".$order_id);
			$pay_time=$order_info['create_time'];
		}
		if(NOW_TIME-$pay_time > 900){
			//下单时间超过15分钟，超时
			$result['status']=0;
			if(isset($_REQUEST['pay_id'])){
				$result['url']=url("index","dc_payment#out_time",array('id'=>intval($_REQUEST['pay_id'])));	
			}
			if(isset($_REQUEST['order_id'])){
				require_once APP_ROOT_PATH."system/model/dc.php";
				
				$result['info']='支付超时，已付金额退回帐户，请重新下单';
				dc_order_close($order_id , 1,$result['info']);
				//$result['url']=url("index","dc_dcorder#out_time",array('id'=>intval($_REQUEST['order_id'])));
			}
			
			
		}else{
			$result['status']=1;
		}
			ajax_return($result);
	}
	 /**
	  * 商家结算，把未结算金额变为可提现金额
	  */
	public function dc_supplier_balance(){
		$supplier_id = intval($_REQUEST['sid']);
		require_once APP_ROOT_PATH."system/model/dc.php";
		$rs=dc_supplier_balance($supplier_id);
		if($rs > 0){
			$result['status']=1;
			$result['info']='结算成功';
		}else{
			$result['status']=0;
			$result['info']='结算失败';
		}
		
		ajax_return($result);	
		
	}
	
}
?>