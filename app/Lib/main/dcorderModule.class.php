<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class dcorderModule extends MainBaseModule
{
	public function index()
	{		
		global_run();
		init_app_page();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);

		$location_id=intval($_REQUEST['lid']);
		
		$is_location_close=$GLOBALS['db']->getOne("select is_close from ".DB_PREFIX."supplier_location where id=".$location_id);
		if($is_location_close==1){
			app_redirect(url('index','dc'));
			//showErr($GLOBALS['lang']['DC_LOCATION_CLOSE'],1,url('index','dc'));
			
		}
		
		require_once APP_ROOT_PATH."system/model/dc.php";

		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);

		
		//开始身边团购的地理定位
		$ypoint =  $GLOBALS['geo']['ypoint'];  //ypoint
		$xpoint =  $GLOBALS['geo']['xpoint'];  //xpoint
		$address = $GLOBALS['geo']['address'];
		$tname='l';
				if($xpoint>0)/* 排序（$order_type）  default 智能（默认）*/
		{
			$pi = PI;  //圆周率
			$r = EARTH_R;  //地球平均半径(米)
			$field_append = ", (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((".$tname.".ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((".$tname.".ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (".$tname.".xpoint * $pi) / 180 ) ) * $r)/1000 as distance ";

		}
		$location_info=get_location_info($tname,$location_id,$field_append);
		

		if(count($location_dc_table_cart['cart_list'])==0 && count($location_dc_cart['cart_list'])==0 ){
				app_redirect(url('index','dcbuy',array('lid'=>$location_id)));
			
		}else{
			
			$cart_menu_info['total_data']=$GLOBALS['db']->getRow("select sum(total_price) as total_price , sum(num) as total_count from ".DB_PREFIX."dc_cart where session_id ='".es_session::id()."' and user_id=".$user_id." and cart_type=1 and location_id=".$location_id);
			//当有预定餐桌时，商品订单总额必须大等于餐桌定金
			if($location_dc_table_cart['total_data']['total_price'] > 0){
			
				if($cart_menu_info['total_data']['total_price'] > 0 && $location_dc_table_cart['total_data']['total_price'] > $cart_menu_info['total_data']['total_price']){
					app_redirect(url('index','dcbuy',array('lid'=>$location_id)));
					//showErr($GLOBALS['lang']['DC_RS_PRICE_LITTLE'],1,url('index','dcbuy',array('lid'=>$location_id)));
				}			
			}else{
			
					$id_arr=array($location_info['id']=>array('id'=>$location_info['id'],'distance'=>$location_info['distance']));
					$location_delivery_info=get_location_delivery_info($id_arr);

				if($location_delivery_info[$location_id]['is_free_delivery']==2 || ($location_delivery_info[$location_id]['is_free_delivery']==0 && $location_delivery_info[$location_id]['start_price']>$location_dc_cart['total_data']['total_price'])){
					app_redirect(url('index','dcbuy',array('lid'=>$location_id)));
				}
			}

		if(count($location_dc_table_cart['cart_list'])>0){
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);	
		}
		if(count($location_dc_cart['cart_list'])>0){
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
		}
		
		
		$GLOBALS['tmpl']->assign("cart_menu_info",$cart_menu_info);

		//输出支付方式
		$payment_list = load_auto_cache("dc_cache_payment");
		
		$icon_paylist = array(); //用图标展示的支付方式
		$disp_paylist = array(); //特殊的支付方式(Voucher,Account,Otherpay)
		$bank_paylist = array(); //网银直连
		
		foreach($payment_list as $k=>$v)
		{
			if($v['class_name']=="Voucher"||$v['class_name']=="Account"||$v['class_name']=="Otherpay")
			{ 	$class_name='Dc_'.$v['class_name'];
				if($v['class_name']=="Account")
				{
					
					$directory = APP_ROOT_PATH."system/dc_payment/";
					$file = $directory. '/' .$class_name."_payment.php";
					if(file_exists($file))
					{
						require_once($file);
						$payment_class = $class_name."_payment";
						$payment_object = new $payment_class();
						$v['display_code'] = $payment_object->get_display_code();
					}
				}

				//商家是否开户代金 卷支付
				if($v['class_name']=="Voucher"){
					//代金 卷,每日限制1单

					$begin_time=to_timespan(to_date(NOW_TIME,"Y-m-d"));
					$end_time=$begin_time+3600*24-1;
					
					$today_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_order where user_id=".$user_id ." and create_time > ".$begin_time." and create_time < ".$end_time." and pay_status=1 and payment_id=0 and ecv_money > 0");	
					if($location_info['dc_allow_ecv']==1 && $today_order_count ==0){
					$disp_paylist[] = $v;
					}
				}else{	
					$disp_paylist[] = $v;
				}
				
			}
			else
			{
				if($v['is_bank']==1)
					$bank_paylist[] = $v;
				else
					$icon_paylist[] = $v;
			}
		}
	
		/* 去掉原始的ui-textbox效果
		foreach($disp_paylist as $kk=>$vv){		
			$disp_paylist[$kk]['display_code']=preg_replace('/ui-textbox/i','',$vv['display_code']);
		}
		 */
		$GLOBALS['tmpl']->assign("icon_paylist",$icon_paylist);
		$GLOBALS['tmpl']->assign("disp_paylist",$disp_paylist);
		$GLOBALS['tmpl']->assign("bank_paylist",$bank_paylist);
		
		$delivery_time=$this->get_delivery_time($location_id);
		$GLOBALS['tmpl']->assign("delivery_time",$delivery_time);

		$GLOBALS['tmpl']->assign("location_info",$location_info);
		$GLOBALS['tmpl']->assign("order_id",0);
		$consignee_info_new=$this->load_user_consignee();
		$GLOBALS['tmpl']->assign("consignee_info",$consignee_info_new);
		$GLOBALS['tmpl']->assign("consignee_list_count",count($consignee_info_new['list']));
		$GLOBALS['tmpl']->display("dc/dcorder.html");
		
		


		}
		
		
	}
	


	/**
	 *  获取外卖订单页面配送时间段(只适合于订餐的时间)
	 * @param unknown_type $time_span 多少秒一个时间段，默认900秒，15分钟
	 * @param unknown_type $delivery_time 自定义中午和早上配送时间段
	 * @return array 返回配送时间段数组
	 */
	public function get_delivery_time_eat($location_id,$time_span=900,$delivery_time=array('am'=>array("11:00","14:00"),'pm'=>array("16:00","20:00"))){
	
		$delivery_time_span=array();
		foreach($delivery_time as $delivery_time_item){
			$delivery_time_span[]=$this->get_time_span(to_timespan($delivery_time_item[0]),to_timespan($delivery_time_item[1]),$time_span);
		}
	
		$delivery_time_new=array();
		foreach($delivery_time_span as $delivery_time_span_row){
	
			foreach($delivery_time_span_row as $delivery_time_span_one){
	
				$delivery_time_new[]=$delivery_time_span_one;
			}
		}
		$delivery_time_arr=array();
		foreach($delivery_time_new as $delivery_time_row){
			if(NOW_TIME + 3600 <= $delivery_time_row){
				//	$delivery_time_arr[]= to_date($delivery_time_row,"H:i");
				$delivery_time_arr[]= $delivery_time_row;
			}
		}
		$open_time_info=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$location_id);
	
		$open_time_arr=array();
		foreach($open_time_info as $kp=>$vp){
			$open_time_row=array();
			$open_time_row['begin_time']=to_timespan($vp['begin_time_h'].":".$vp['begin_time_m']);
			$open_time_row['end_time']=to_timespan($vp['end_time_h'].":".$vp['end_time_m']);
			$open_time_arr[]=$open_time_row;
	
		}
		$open_time_arr_new=array();
		foreach($delivery_time_arr as $k=>$v){
			foreach($open_time_arr as $kp=>$vp){
				if($v >= $vp['begin_time'] && $v <= $vp['end_time']){
					$open_time_arr_new[]=to_date($v,"H:i");
					break;
						
				}
	
					
			}
		}
	
		return $open_time_arr_new;
	}
	
	
	/**
	 *  获取外卖订单页面配送时间段
	 * @param unknown_type $time_span 多少秒一个时间段，默认900秒，15分钟
	 * @param unknown_type $delivery_time 自定义中午和早上配送时间段
	 * @return array 返回配送时间段数组
	 */
	public function get_delivery_time($location_id,$time_span=900,$delivery_time=array('am'=>array("05:00","23:45"))){
	
		$delivery_time_span=array();
		foreach($delivery_time as $delivery_time_item){
			$delivery_time_span[]=$this->get_time_span(to_timespan($delivery_time_item[0]),to_timespan($delivery_time_item[1]),$time_span);
		}
	
		$delivery_time_new=array();
		foreach($delivery_time_span as $delivery_time_span_row){
	
			foreach($delivery_time_span_row as $delivery_time_span_one){
	
				$delivery_time_new[]=$delivery_time_span_one;
			}
		}
		$delivery_time_arr=array();
		foreach($delivery_time_new as $delivery_time_row){
			if(NOW_TIME + 3600 <= $delivery_time_row){
				//	$delivery_time_arr[]= to_date($delivery_time_row,"H:i");
				$delivery_time_arr[]= $delivery_time_row;
			}
		}
		$open_time_info=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_supplier_location_open_time where location_id=".$location_id);
	
		$open_time_arr=array();
		foreach($open_time_info as $kp=>$vp){
			$open_time_row=array();
			$open_time_row['begin_time']=to_timespan($vp['begin_time_h'].":".$vp['begin_time_m']);
			$open_time_row['end_time']=to_timespan($vp['end_time_h'].":".$vp['end_time_m']);
			$open_time_arr[]=$open_time_row;
	
		}
		$open_time_arr_new=array();
		foreach($delivery_time_arr as $k=>$v){
			foreach($open_time_arr as $kp=>$vp){
				if($v >= $vp['begin_time'] && $v <= $vp['end_time']){
					$open_time_arr_new[]=to_date($v,"H:i");
					break;
	
				}
	
					
			}
		}
	
		return $open_time_arr_new;
	}
	

/**
 * 获取开始时间和结束时间之间，以$time_span为分隔长度的时间数据
 * @param unknown_type $begin_time 开始时间，格式为时间戳
 * @param unknown_type $end_time   结束时间，格式为时间戳
 * @param unknown_type $time_span  时间分隔长度，以秒为单位
 * @return array 返回   开始时间和结束时间之间，以$time_span为分隔长度的时间数组
 */

	public function get_time_span($begin_time,$end_time,$time_span){
		$time_arr=array();
		$num=($end_time-$begin_time)/$time_span+1;
		for($i=0;$i<$num;$i++){
			$time_arr[]=$begin_time+$time_span*$i;
		}
		return $time_arr;
	
	}
	

	
	
	/**
	 * 获取单条送餐地址，可以用于编辑或者新增送餐地址
	 */
	public function get_consignee_row(){
	
		$id=intval($_REQUEST['id']);

		$dc_consignee_row=$this->load_user_consignee($id);
		$GLOBALS['tmpl']->assign("dc_consignee_info",$dc_consignee_row);
	
		$result['html']=$GLOBALS['tmpl']->fetch('dc/inc/dc_inc_consignee.html');
	
		ajax_return($result);
	}
	
	/**
	 * 
	 * 获取用户的送餐地址
	 */
	
	public function load_user_consignee($id){
		global_run();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		
		$consignee_info=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."dc_consignee where user_id=".$user_id." order by is_main desc , id desc");
		
		foreach($consignee_info as $kk=>$vv){
			$consignee_info_new['list'][]=$vv;
			if($vv['is_main']==1){
				$consignee_info_new['is_main']=$vv;
			}
		}
		require_once APP_ROOT_PATH."system/model/dc.php";
		$consignee_info_new['list']=data_format_idkey($consignee_info_new['list'],$key='id');
		if($id){
			return $consignee_info_new['list'][$id];
			
		}else{
			return $consignee_info_new;
		}
		
	}
	
	public function del_consignee_row(){
	
		$id=intval($_REQUEST['id']);
		global_run();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		$dc_consignee_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where user_id=".$user_id);
		if($dc_consignee_count<2){	
			$result['status']=0;
			$result['info']=$GLOBALS['lang']['DC_CON_DONOT_DEL'];
			ajax_return($result);
		}else{	
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_consignee where id=".$id);
			if($GLOBALS['db']->affected_rows()>0){
				$result['status']=1;
				$result['info']=$GLOBALS['lang']['DC_CON_DELETE_SUCCESS'];
				
				$GLOBALS['db']->query("update ".DB_PREFIX."dc_consignee set is_main=1 where user_id=".$user_id." order by id desc limit 1");
				
				
			}else{
				$result['status']=0;
				$result['info']=$GLOBALS['lang']['DC_CON_DELETE_FAIL'];	
			}
			ajax_return($result);
		}
		
	}
	
	public function save_dc_consignee(){
		
		
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
		$consignee_info['user_id']=intval($_REQUEST['user_id']);
			
		$user_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_consignee where id=".$id);
		if($user_count > 0){
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info,$mode='UPDATE','id='.$id,$querymode = 'SILENT');
		
		}else{
			$consignee['is_main']=0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee,$mode='UPDATE','user_id='.$consignee_info['user_id'],$querymode = 'SILENT');
			$consignee_info['is_main']=1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_info);
		}
		$result['status']=$GLOBALS['db']->affected_rows();
		
		ajax_return($result);
	}
	
	
	
	public function order(){
		global_run();
		init_app_page();
		$user_id=isset($GLOBALS['user_info']['id'])?$GLOBALS['user_info']['id']:0;
		$GLOBALS['tmpl']->assign("user_id",$user_id);
		$GLOBALS['tmpl']->assign("city_name",$GLOBALS['city']['name']);
	
		$id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$id." and type_del = 0 and pay_status=0 and order_status=0 and is_cancel=0 and user_id =".intval($GLOBALS['user_info']['id']));
		if(!$order_info)
		{

			showErr($GLOBALS['lang']['INVALID_ORDER_DATA'],0,url('index','dc'));
		}else{
			if(NOW_TIME-$order_info['create_time'] > 900){
			showErr($GLOBALS['lang']['DC_ORDER_OUT_TIME'],0,url('index','dc'));
			}
			
		}
		


		//如果之前已经享受过首单立减，就不能享受了
		$is_ordered=$GLOBALS['db']->getOne("select dc_is_share_first from ".DB_PREFIX."user where id=".$user_id);
		$order_info['promote_str']=unserialize($order_info['promote_str']);
		
		
		foreach($order_info['promote_str'] as $k=>$v){
		
			if($v['class_name']=='FirstOrderDiscount'){
		
				if($is_ordered==1){
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set promote_str='' , promote_amount=0 where id=".$id);
		
				}
			}elseif($v['class_name']=='PayOnlineDiscount'){
				//取出上线支付优惠接口配置
				$promote_obj = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_promote where class_name='PayOnlineDiscount'");
				$promote_cfg = unserialize($promote_obj['config']);
				//如果今天上线支付优惠已经用完，就不能享受了
				$begin_time=to_timespan(to_date(NOW_TIME,"Y-m-d"));
				$end_time=$begin_time+3600*24-1;
		
				$today_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_order where user_id=".$user_id ." and create_time > ".$begin_time." and create_time < ".$end_time." and pay_status=1 and payment_id=0 and promote_amount > 0");
				if($promote_cfg['daily_limit'] <= $today_order_count){
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set promote_str='' , promote_amount=0 where id=".$id);
				}
		
			}
		}
		 $location_id=$order_info['location_id'];
		
		$is_location_close=$GLOBALS['db']->getOne("select is_close from ".DB_PREFIX."supplier_location where id=".$location_id);
		if($is_location_close==1){

			showErr($GLOBALS['lang']['DC_LOCATION_CLOSE'],0,url('index','dc'));
				
		}
	
		require_once APP_ROOT_PATH."system/model/dc.php";

		$order_menu=unserialize($order_info['order_menu']);

		$location_dc_table_cart=$order_menu['rs_list'];
		$location_dc_cart=$order_menu['menu_list'];

		$tname='l';
		$location_info=get_location_info($tname,$location_id,$field_append='');

		if(count($location_dc_table_cart['cart_list'])==0 && count($location_dc_cart['cart_list'])==0 ){
			showErr($GLOBALS['lang']['DC_CART_EMPTY'],0,url('index','dcbuy',array('lid'=>$location_id)));
				
		}else{
				
			$cart_menu_info=$location_dc_cart;
			//当有预定餐桌时，商品订单总额必须大等于餐桌定金
			if($location_dc_table_cart['total_data']['total_price'] > 0){
					
				if($cart_menu_info['total_price'] > 0 && $location_dc_table_cart['total_data']['total_price'] > $cart_menu_info['total_price']){
					app_redirect(url('index','dcbuy',array('lid'=>$location_id)));
				}
			}else{
					
				$id_arr=array($location_info['id']=>array('id'=>$location_info['id'],'distance'=>$location_info['distance']));
				$location_delivery_info=get_location_delivery_info($id_arr);
	
				if($location_delivery_info[$location_id]['is_free_delivery']==2 || ($location_delivery_info[$location_id]['is_free_delivery']==0 && $location_delivery_info[$location_id]['start_price']>$location_dc_cart['total_data']['total_price'])){
					app_redirect(url('index','dcbuy',array('lid'=>$location_id)));
				}
			}
	
			if(count($location_dc_table_cart['cart_list'])>0){
				$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			}
			if(count($location_dc_cart['cart_list'])>0){
				$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			}
	
	
			$GLOBALS['tmpl']->assign("cart_menu_info",$cart_menu_info);
	
			//输出支付方式
			$payment_list = load_auto_cache("cache_payment");
	
	
			$icon_paylist = array(); //用图标展示的支付方式
			$disp_paylist = array(); //特殊的支付方式(Voucher,Account,Otherpay)
			$bank_paylist = array(); //网银直连
			foreach($payment_list as $k=>$v)
			{
				if($v['class_name']=="Voucher"||$v['class_name']=="Account"||$v['class_name']=="Otherpay")
				{	$class_name='Dc_'.$v['class_name'];
					if($v['class_name']=="Account")
					{	
						$directory = APP_ROOT_PATH."system/dc_payment/";
						$file = $directory. '/' .$class_name."_payment.php";
						if(file_exists($file))
						{
							require_once($file);
							$payment_class =$class_name."_payment";
							$payment_object = new $payment_class();
							$v['display_code'] = $payment_object->get_display_code();
						}
					}
	
					//商家是否开户代金 卷支付
					if($v['class_name']=="Voucher"){
						if($location_info['dc_allow_ecv']==1 && $order_info['ecv_money']==0){
							$disp_paylist[] = $v;
						}
					}else{
						$disp_paylist[] = $v;
					}
				}
				else
				{
					if($v['is_bank']==1)
						$bank_paylist[] = $v;
					else
						$icon_paylist[] = $v;
				}
			}
	
			/* 去掉原始的ui-textbox效果 
			foreach($disp_paylist as $kk=>$vv){
				$disp_paylist[$kk]['display_code']=preg_replace('/ui-textbox/i','',$vv['display_code']);
			}
			*/
			$GLOBALS['tmpl']->assign("icon_paylist",$icon_paylist);
			$GLOBALS['tmpl']->assign("disp_paylist",$disp_paylist);
			$GLOBALS['tmpl']->assign("bank_paylist",$bank_paylist);
	
	
			$delivery_time=$this->get_delivery_time($location_id);
			$GLOBALS['tmpl']->assign("delivery_time",$delivery_time);
	
			$GLOBALS['tmpl']->assign("location_info",$location_info);
			$GLOBALS['tmpl']->assign("order_id",$order_info['id']);
			$GLOBALS['tmpl']->assign("order_info",$order_info);
			$consignee_info_new=$this->load_user_consignee();
			$GLOBALS['tmpl']->assign("consignee_info",$consignee_info_new);
			$GLOBALS['tmpl']->assign("consignee_list_count",count($consignee_info_new['list']));
			$GLOBALS['tmpl']->display("dc/dcorder.html");
	
	
	
	
		}
	
	
	}
	
	
	//购物车订单提交
	public function done()
	{ 
		
		require_once APP_ROOT_PATH."system/model/dc.php";
		global_run();
		$ajax = 1;
		$payment = intval($_REQUEST['payment']);
		$account_money = floatval($_REQUEST['account_money']);
		$all_account_money = strim($_REQUEST['all_account_money']);
		
		if($all_account_money=='on'){
			$all_account_money=1;
		}else{
			$all_account_money=0;
		}
		$ecv_pass = intval($_REQUEST['ecv_pass']);   //代金劵是否需要验证，0为不验证，1为要验证
		$ecvsn = $_REQUEST['ecvsn']?strim($_REQUEST['ecvsn']):'';
		$ecvpassword = $_REQUEST['ecvpassword']?strim($_REQUEST['ecvpassword']):'';
		$consignee = $_REQUEST['consignee']?strim($_REQUEST['consignee']):'';
		$mobile = $_REQUEST['mobile']?strim($_REQUEST['mobile']):'';
		$address = isset($_REQUEST['address'])?strim($_REQUEST['address']):'';
		$dc_comment = $_REQUEST['dc_comment']?strim($_REQUEST['dc_comment']):'';
		$invoice = $_REQUEST['invoice']?strim($_REQUEST['invoice']):'';
		$payment_id = $_REQUEST['payment_id']?intval($_REQUEST['payment_id']):0;  //付款方式 ,1为货到付款，0为在线支付
		$dc_type = isset($_REQUEST['dc_type'])?intval($_REQUEST['dc_type']):-1;          //$dc_type大等于0为预订方式，不享受促销优惠，-1代表享受促销优惠
		$bank_id = $_REQUEST['bank_id']?strim($_REQUEST['bank_id']):'';
		$order_delivery_time = $_REQUEST['order_delivery_time']?strim($_REQUEST['order_delivery_time']):'';
		$consignee_id = $_REQUEST['consignee_id']?intval($_REQUEST['consignee_id']):'';
		$user_id = intval($GLOBALS['user_info']['id']);
		$session_id = es_session::id();
		$location_id = $_REQUEST['location_id']?intval($_REQUEST['location_id']):0;
		
		if($address!=''){
			$consignee_data['address']=$address;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_data,'UPDATE','id='.$consignee_id,'SILENT');
		}
		
		//验证用户是否已经登录
		if((check_save_login()!=LOGIN_STATUS_LOGINED && $GLOBALS['user_info']['money']>0)||check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			//showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url("index","user#login"));
			$data['status'] = 1000;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			$data['jump'] = '';
			ajax_return($data);
			
			
		}
		
		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
		//验证购物车是否为空
		if($location_dc_table_cart['total_data']['total_count']==0 && $location_dc_cart['total_data']['total_count']==0)
		{
			showErr($GLOBALS['lang']['CART_EMPTY_TIP'],$ajax,url("index","dcbuy",array('lid'=>$location_id)));
		}
		
		
		
		/* 验证购物车中每条购物是否存在，暂无  */
		$tname='sl';
		$location_info=get_location_info($tname,$location_id,$field_append='');
		
		if($payment_id==0 && $location_info['dc_online_pay']==0){	
			showErr($GLOBALS['lang']['CANNOT_PAY_ONLINE'],$ajax);  //不支持在线支付
		}
		if($payment_id==1 && $location_info['dc_allow_cod']==0){
			showErr($GLOBALS['lang']['CANNOT_ALLOW_COD_PAY'],$ajax);  //不支持货到付款
		}
		
		//结束验证购物车
		//开始验证订单接交信息
		$result = dc_count_buy_total($location_id,$location_dc_table_cart,$location_dc_cart,$payment_id,$dc_type,$consignee_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$bank_id);

		if($result['ecv_pass']==1 && $ecv_pass==1){
					$data['status'] = 2;
					$data['info'] = $GLOBALS['lang']['DC_ECV_PASS'];
					$data['jump'] = '';
					ajax_return($data);	
		}

		$dc_consignee=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_consignee where id=".$consignee_id);
		//外卖时 $dc_type==-1，验证配送地址是否存在
		if($dc_type==-1){	
			if($dc_consignee['xpoint']=='' || $dc_consignee['ypoint']=='' || $dc_consignee['consignee']=='' || $dc_consignee['mobile']=='' || $dc_consignee['api_address']=='' || $dc_consignee['address']=='' ){
			showErr($GLOBALS['lang']['DC_DELIVERY_NO_EXIST'],$ajax,url("index","dcorder",array('lid'=>$location_id)));  
			}
		}else{
			//验证预订位置的库存
			foreach($location_dc_table_cart['cart_list'] as $kk=>$vv){
				$rs_date=to_date($vv['table_time'],"Y-m-d");
				$total_count=$GLOBALS['db']->getOne("select total_count from ".DB_PREFIX."dc_rs_item_time where  id=".$vv['table_time_id']);
				$rs_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_day where time_id=".$vv['table_time_id']." and rs_date='".$rs_date."'");

				if(!$rs_info && $total_count==0){
					showErr($GLOBALS['lang']['DC_TABLE_OUT_STOCK'],$ajax,url("index","dctable",array('lid'=>$location_id)));				
					break;
				}elseif($rs_info && $rs_info['buy_count'] + $vv['num'] > $total_count ){	
					showErr($GLOBALS['lang']['DC_TABLE_OUT_STOCK'],$ajax,url("index","dctable",array('lid'=>$location_id)));
					break;
				}
			}
			
			
		}
		
		//验证是否超过该商家配送范围，$is_out_scale=0为不超出，$is_out_scale=1为超出商家配送范围
		$is_out_scale=0;
		if($location_dc_table_cart['total_data']['total_price']==0 && $location_dc_cart['total_data']['total_price']>0){
			if($result['location_delivery_info']['is_free_delivery']==2){
				$is_out_scale=1;
			}		
		}
		if($is_out_scale==1){
			showErr($GLOBALS['lang']['DC_OUT_DELIVERY_SCALE'],$ajax);  //超出商家配送范围
		}
		
		
		
		//结束验证订单接交信息
		
		//开始生成订单
		
		if(round($result['pay_price'],4)>0 && !$result['payment_info'] && $payment_id==0)
		{
			showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT'],$ajax);
		}

		$consignee_info=$this->load_user_consignee($consignee_id);
		
		if(trim($consignee)==''){
			$consignee=$consignee_info['consignee'];
		}
		if(trim($mobile)==''){
			$mobile=$consignee_info['mobile'];
		}
		
		//保存送餐地址中未保存部分,$dc_type==-1代表外卖
		if($dc_type==-1 && $address!=''){
		$consignee_address['address']=$address;
		$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_address,"UPDATE","id=".$consignee_id);
		}
		//获取代金劵ID
		$ecvid=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."ecv where sn=".$ecvsn." and password=".$ecvpassword." and user_id=".$user_id);
		
		//享受的优惠措失，带换行的字符串
		foreach($result['dc_promote'] as $k=>$v){		
		//	$order['promote_str'].=$v['promote_description']."<br/>";	
			$order['promote_amount'] += $v['discount_amount'];
		}
		$order['promote_str']=serialize($result['dc_promote']);
		//$dc_type大等于0为预订方式，不享受促销优惠，-1代表享受促销优惠
		if($dc_type==-1){
			$order['rs_price'] = 0;   //定金
			$order['is_rs'] = 0;   //是否为预定定单，0:否 1:是，预定定单不用选配送，发团购券	
			
		}else{
			$order['rs_price'] = $location_dc_table_cart['total_data']['total_price'];   //定金
			$order['is_rs'] = 1;   //是否为预定定单，0:否 1:是，预定定单不用选配送，发团购券
			
		}
		
		//	
		$dc_cart_total['rs_list']=$location_dc_table_cart;
		$dc_cart_total['menu_list']=$location_dc_cart;
		$order['order_menu'] = serialize($dc_cart_total); //序列化下来的订单的商品数据，含名称，数量，图片，单价，总价等
		//计算网站提成，和应给商家的钱 ,商家的balance_type=0为  按百分比收取 ，balance_type=1按单收取
		if($location_info['balance_type']==0){
			$order['balance_price']=$result['pay_total_price']*(1-$location_info['balance_amount']);
		}elseif($location_info['balance_type']==1){
			$order['balance_price']=$result['pay_total_price']-$location_info['balance_amount'];
		}
	
		
		$now = NOW_TIME;
		$order['supplier_id'] = $location_info['supplier_id'];
		$order['location_id'] = $location_id;
		$order['create_time'] = $now;
		$order['order_status'] = 0;  //结单状态，新单都为零， 等下面的流程同步订单状态
		$order['confirm_status'] = 0;  //订单商家确认状态，0:未确认（未接单，用户未付款可以取消，已付款可直接退款），1.已确认（商家已接单,客户可与商家联系，申请退款），2.已配送（商家已配送,不可以申请退款）
		$order['pay_status'] = 0;  //支付状态，新单都为零， 等下面的流程同步订单状态
		$order['total_price'] = $result['pay_total_price'];  //应付总额  商品价  + 打包费 + 支付手续费 + 配送费

		$order['bank_id'] = $bank_id;
		$order['menu_price'] = $location_dc_cart['total_data']['total_price'];
		$order['package_price'] = $result['package_fee'];
		$order['delivery_price'] = $result['delivery_fee'];
		$order['payment_fee'] = $result['payment_fee'];
		$order['pay_amount'] = 0; // 已付总额，pay_amount>=total_price时表示支付成功
		$order['pay_time'] = 0;   //支付成功时间
		$order['online_pay'] = 0;  //在线支付的额度
		$order['ecv_id'] = $ecvid;
		$order['ecv_money'] = 0;
		$order['account_money'] = 0;
		$order['payment_id'] = $payment_id;  //支付方式ID，0表示在线支付，1表示货到付款
		$order['user_id'] = $user_id;
		$order['xpoint'] = $consignee_info['xpoint'];
		$order['ypoint'] = $consignee_info['ypoint'];
		$order['address'] = $consignee_info['address'];
		$order['api_address'] = $consignee_info['api_address'];
		$order['consignee'] = $consignee;
		$order['mobile'] = $mobile;
		if($order_delivery_time==''){
		$order['order_delivery_time']==0;
		}elseif($order_delivery_time==1){  //1表示尽快送达
		$order['order_delivery_time'] = $order_delivery_time;
		}else{
			$order['order_delivery_time'] = to_timespan($order_delivery_time);
		}

		$order['dc_comment'] = $dc_comment;  //外卖预订的客户留言
		$order['invoice'] = $invoice;
		$order['location_name'] = $location_info['name'];  //门店名
		$order['type_del'] = 0;   // 删除状态: 0未经过删除处理 ，1为经过删除
		$user_info = es_session::get("user_info");
		$order['user_name'] = $user_info['user_name']; //会员名称
		

		do
		{
			$order['order_sn'] = to_date(NOW_TIME,"Ymdhis").rand(10,99);
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_order",$order,'INSERT','','SILENT');
			$order_id = intval($GLOBALS['db']->insert_id());
		}while($order_id==0);

		if($location_dc_table_cart['total_data']['total_price']>0){
			foreach($location_dc_table_cart['cart_list'] as $k=>$v){
				$goods_item['name'] =$v['name'];
				//$goods_item['icon'] = 0;
				$goods_item['menu_id'] = $v['menu_id'];
				$goods_item['supplier_id'] = $location_info['supplier_id'];
				$goods_item['location_id'] = $location_id;
				$goods_item['order_id'] = $order_id;
				$goods_item['user_id'] = $user_id;
				$goods_item['num'] = $v['num'];
				$goods_item['unit_price'] = $v['unit_price'];
				$goods_item['total_price'] = $v['total_price'];
				$goods_item['order_sn'] = $order['order_sn'];
				$goods_item['table_time_id'] = $v['table_time_id'];
				$goods_item['type'] = 0;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_order_menu",$goods_item,'INSERT','','SILENT');
				

			}
		}
		if($location_dc_cart['total_data']['total_price']>0){
			foreach($location_dc_cart['cart_list'] as $k=>$v){
				$goods_item['name'] =$v['name'];
				//$goods_item['icon'] = 0;
				$goods_item['menu_id'] = $v['menu_id'];
				$goods_item['supplier_id'] = $location_info['supplier_id'];
				$goods_item['location_id'] = $location_id;
				$goods_item['order_id'] = $order_id;
				$goods_item['user_id'] = $user_id;
				$goods_item['num'] = $v['num'];
				$goods_item['unit_price'] = $v['unit_price'];
				$goods_item['total_price'] = $v['total_price'];
				$goods_item['order_sn'] = $order['order_sn'];
				$goods_item['table_time_id']=0;
				$goods_item['type'] = 1;
				$GLOBALS['db']->autoExecute(DB_PREFIX."dc_order_menu",$goods_item,'INSERT','','SILENT');
			}
		}
		
		//删除购物车该订单的数据
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
		$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where user_id=".$order_info['user_id']." and location_id=".$order_info['location_id']." and session_id='".es_session::id()."'");
			
		//如果是在线支付
		if($payment_id==0){
			//生成order_id 后
			//1. 代金券支付
			$ecv_data = $result['ecv_data'];
			if($ecv_data)
			{
				$ecv_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Voucher'");
				if($ecv_data['money']>$order['total_price'])$ecv_data['money'] = $order['total_price'];
				$payment_notice_id = make_dcpayment_notice($result['ecv_money'],$order_id,$ecv_payment_id,"",$ecv_data['id']);
				require_once APP_ROOT_PATH."system/dc_payment/Dc_Voucher_payment.php";
				$voucher_payment = new Dc_Voucher_payment();
				$voucher_payment->direct_pay($ecv_data['sn'],$ecv_data['password'],$payment_notice_id);
			}
			
			//2. 余额支付
			$account_money = $result['account_money'];
			if(floatval($account_money) > 0)
			{
				$account_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
				$payment_notice_id = make_dcpayment_notice($account_money,$order_id,$account_payment_id);
				require_once APP_ROOT_PATH."system/dc_payment/Dc_Account_payment.php";
				$account_payment = new Dc_Account_payment();
				$account_payment->get_payment_code($payment_notice_id);
			}
			
			//3. 相应的支付接口
			$payment_info = $result['payment_info'];
			if($payment_info&&$result['pay_price']>0)
			{
				$payment_notice_id = make_dcpayment_notice($result['pay_price'],$order_id,$payment_info['id']);
				//创建支付接口的付款单
			}
		
			$rs = dcorder_paid($order_id);

			if($rs)
			{
				$data = array();
				$data['info'] = "";
				$data['jump'] = url("index","dc_payment#done",array("id"=>$payment_notice_id,'rs'=>1));
				ajax_return($data); //支付成功
			
			}
			else
			{
	
				
				if($order_info['pay_status'] == 1)
				{
					$data = array();
					$data['info'] = "";
					$data['jump'] = url("index","dc_payment#done",array("id"=>$payment_notice_id,'rs'=>2));
				
				}
				else{
					$data = array();
					$data['info'] = "";
					$data['jump'] = url("index","dc_payment#pay",array("id"=>$payment_notice_id,'rs'=>0));
						
				}
				ajax_return($data);
			}
		}else{
			//货到付款
			$data = array();
			$data['info'] = "";
			$data['jump'] = url("index","dc_payment#done",array("id"=>$order_id,'rs'=>5));
			
			require_once APP_ROOT_PATH."system/model/dc.php";
			dcorder_paid_done($order_id);
			ajax_return($data);
		}
		
	}
	
	
	//已存在的订单的再次支付
	public function order_done()
	{
	
		require_once APP_ROOT_PATH."system/model/dc.php";
		global_run();
		$ajax = 1;
		$payment = intval($_REQUEST['payment']);
		$account_money = floatval($_REQUEST['account_money']);
		$all_account_money = strim($_REQUEST['all_account_money']);
	
		if($all_account_money=='on'){
			$all_account_money=1;
		}else{
			$all_account_money=0;
		}
		$order_id=$id = intval($_REQUEST['id']);
		$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$id." and type_del = 0 and pay_status <> 1 and order_status <> 1 and user_id =".intval($GLOBALS['user_info']['id']));
		//验证用户是否已经登录
		if((check_save_login()!=LOGIN_STATUS_LOGINED && $GLOBALS['user_info']['money']>0)||check_save_login()==LOGIN_STATUS_NOLOGIN)
		{
			//showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax,url("index","user#login"));
			$data['status'] = 1000;
			$data['info'] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
			$data['jump'] = '';
			ajax_return($data);
		}
		
		
		if(!$order_info)
		{
		
			showErr($GLOBALS['lang']['INVALID_ORDER_DATA'],$ajax,url('index','dc'));
		}
		if($order_info['refund_status'] == 1)
		{
			showErr($GLOBALS['lang']['REFUNDING_CANNOT_PAY'],$ajax);
		}
		if($order_info['refund_status'] == 2)
		{
			showErr($GLOBALS['lang']['REFUNDED_CANNOT_PAY'],$ajax);
		}
		$ecvsn = $_REQUEST['ecvsn']?strim($_REQUEST['ecvsn']):'';
		$ecvpassword = $_REQUEST['ecvpassword']?strim($_REQUEST['ecvpassword']):'';
		$consignee = $_REQUEST['consignee']?strim($_REQUEST['consignee']):'';
		$mobile = $_REQUEST['mobile']?strim($_REQUEST['mobile']):'';
		$address = isset($_REQUEST['address'])?strim($_REQUEST['address']):'';
		$dc_comment = $_REQUEST['dc_comment']?strim($_REQUEST['dc_comment']):'';
		$invoice = $_REQUEST['invoice']?strim($_REQUEST['invoice']):'';
		$payment_id = $_REQUEST['payment_id']?intval($_REQUEST['payment_id']):0;  //付款方式 ,1为货到付款，0为在线支付
		$dc_type = isset($_REQUEST['dc_type'])?intval($_REQUEST['dc_type']):-1;          //$dc_type大等于0为预订方式，不享受促销优惠，-1代表享受促销优惠
		$bank_id = $_REQUEST['bank_id']?strim($_REQUEST['bank_id']):'';
		$order_delivery_time = $_REQUEST['order_delivery_time']?strim($_REQUEST['order_delivery_time']):'';
		$consignee_id = $_REQUEST['consignee_id']?intval($_REQUEST['consignee_id']):'';
		$user_id = intval($GLOBALS['user_info']['id']);
		$session_id = es_session::id();
		$location_id = $order_info['location_id'];
	

		$order_menu=unserialize($order_info['order_menu']);
		
		$location_dc_table_cart=$order_menu['rs_list'];
		$location_dc_cart=$order_menu['menu_list'];
		//验证购物车是否为空
		if($location_dc_table_cart['total_data']['total_count']==0 && $location_dc_cart['total_data']['total_count']==0)
		{
			showErr($GLOBALS['lang']['CART_EMPTY_TIP'],$ajax,url("index","dcbuy",array('lid'=>$location_id)));
		}
	
	
	
		/* 验证购物车中每条购物是否存在，暂无  */
		$tname='sl';
		$location_info=get_location_info($tname,$location_id,$field_append='');
	
		if($payment_id==0 && $location_info['dc_online_pay']==0){
			showErr($GLOBALS['lang']['CANNOT_PAY_ONLINE'],$ajax);  //不支持在线支付
		}
		if($payment_id==1 && $location_info['dc_allow_cod']==0){
			showErr($GLOBALS['lang']['CANNOT_ALLOW_COD_PAY'],$ajax);  //不支持货到付款
		}
	
		//结束验证购物车
		//开始验证订单接交信息
		$result = dc_count_buy_total($location_id,$location_dc_table_cart,$location_dc_cart,$payment_id,$dc_type,$consignee_id,$payment,$account_money,$all_account_money,$ecvsn,$ecvpassword,$bank_id,$order_info['account_money'],$order_info['ecv_money'],$order_info['promote_amount']);

		if($result['ecv_pass']==1 && $ecv_pass==1){
			$data['status'] = 2;
			$data['info'] = $GLOBALS['lang']['DC_ECV_PASS'];
			$data['jump'] = '';
			ajax_return($data);
		}
		$dc_consignee=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_consignee where id=".$consignee_id);
		//外卖时 $dc_type==-1，验证配送地址是否存在
		if($dc_type==-1){
			if($dc_consignee['xpoint']=='' || $dc_consignee['ypoint']=='' || $dc_consignee['consignee']=='' || $dc_consignee['mobile']=='' || $dc_consignee['api_address']=='' || $dc_consignee['address']=='' ){
				showErr($GLOBALS['lang']['DC_DELIVERY_NO_EXIST'],$ajax,url("index","dcorder#order",array('id'=>$id)));
			}
		}else{
			//验证预订位置的库存
			foreach($location_dc_table_cart['cart_list'] as $kk=>$vv){
				$rs_date=to_date($vv['table_time'],"Y-m-d");
				$total_count=$GLOBALS['db']->getOne("select total_count from ".DB_PREFIX."dc_rs_item_time where  id=".$vv['table_time_id']);
				$rs_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_rs_item_day where time_id=".$vv['table_time_id']." and rs_date='".$rs_date."'");

				if(!$rs_info && $total_count==0){
					showErr($GLOBALS['lang']['DC_TABLE_OUT_STOCK'],$ajax,url("index","dctable",array('lid'=>$location_id)));				
					break;
				}elseif($rs_info && $rs_info['buy_count'] + $vv['num'] > $total_count ){	
					showErr($GLOBALS['lang']['DC_TABLE_OUT_STOCK'],$ajax,url("index","dctable",array('lid'=>$location_id)));
					break;
				}
			}
			
		}
	
		//验证是否超过该商家配送范围，$is_out_scale=0为不超出，$is_out_scale=1为超出商家配送范围
		$is_out_scale=0;
		if($location_dc_table_cart['total_data']['total_price']==0 && $location_dc_cart['total_data']['total_price']>0){
			if($result['location_delivery_info']['is_free_delivery']==2){
				$is_out_scale=1;
			}
		}
		if($is_out_scale==1){
			showErr($GLOBALS['lang']['DC_OUT_DELIVERY_SCALE'],$ajax);  //超出商家配送范围
		}
		


		//如果之前已经享受过首单立减，就不能享受了
		$is_ordered=$GLOBALS['db']->getOne("select dc_is_share_first from ".DB_PREFIX."user where id=".$user_id);
		$order_info['promote_str']=unserialize($order_info['promote_str']);
		
		
		foreach($order_info['promote_str'] as $k=>$v){
		
			if($v['class_name']=='FirstOrderDiscount'){
		
				if($is_ordered==1){
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set promote_str='' , promote_amount=0 where id=".$id);
		
				}
			}elseif($v['class_name']=='PayOnlineDiscount'){
				//取出上线支付优惠接口配置
				$promote_obj = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_promote where class_name='PayOnlineDiscount'");
				$promote_cfg = unserialize($promote_obj['config']);
				//如果今天上线支付优惠已经用完，就不能享受了
				$begin_time=to_timespan(to_date(NOW_TIME,"Y-m-d"));
				$end_time=$begin_time+3600*24-1;
		
				$today_order_count=$GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."dc_supplier_order where user_id=".$user_id ." and create_time > ".$begin_time." and create_time < ".$end_time." and pay_status=1 and payment_id=0 and promote_amount > 0");
				if($promote_cfg['daily_limit'] <= $today_order_count){
					$GLOBALS['db']->query("update ".DB_PREFIX."dc_order set promote_str='' , promote_amount=0 where id=".$id);
				}
		
			}
		}
	
		//结束验证订单接交信息
	
		//开始生成订单
	
		if(round($result['pay_price'],4)>0 && !$result['payment_info'] && $payment_id==0)
		{
			showErr($GLOBALS['lang']['PLEASE_SELECT_PAYMENT'],$ajax);
		}
	
		$consignee_info=$this->load_user_consignee($consignee_id);
	
		if(trim($consignee)==''){
			$consignee=$consignee_info['consignee'];
		}
		if(trim($mobile)==''){
			$mobile=$consignee_info['mobile'];
		}
		
		//保存送餐地址中未保存部分,$dc_type==-1代表外卖
		if($dc_type==-1 && $addres!=''){
			$consignee_address['address']=$address;
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_consignee",$consignee_address,"UPDATE","id=".$consignee_id);
		}
		//获取代金劵ID
		$ecvid=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."ecv where sn=".$ecvsn." and password=".$ecvpassword." and user_id=".$user_id);
	
		//享受的优惠措失，带换行的字符串
		$order['promote_str']=unserialize($order_info['promote_str']);
		foreach($result['dc_promote'] as $k=>$v){
			//$order['promote_str'].=$v['promote_description']."<br/>";
			$order['promote_amount'] += $v['discount_amount'];
			$order['promote_str'][$k]=$v;
		}
		$order['promote_str']=serialize($order['promote_str']);
			//开始修正订单
			//计算网站提成，和应给商家的钱 ,商家的balance_type=0为  按百分比收取 ，balance_type=1按单收取
			if($location_info['balance_type']==0){
				$order['balance_price']=$result['pay_total_price']*(1-$location_info['balance_amount']);
			}elseif($location_info['balance_type']==1){
				$order['balance_price']=$result['pay_total_price']-$location_info['balance_amount'];
			}
			$now = NOW_TIME;	
			$order['total_price'] = $result['pay_total_price'];  //应付总额  商品价  + 打包费 + 支付手续费 + 配送费
			$order['package_price'] = $result['package_fee'];
			$order['bank_id'] = $bank_id;
			$order['delivery_price'] = $result['delivery_fee'];
			$order['payment_fee'] = $result['payment_fee'];  //手续费只有在线支付成功后，才能加入订单表中
			$order['payment_id'] = $payment_id;  //支付方式ID，0表示在线支付，1表示货到付款
			$order['xpoint'] = $consignee_info['xpoint'];
			$order['ypoint'] = $consignee_info['ypoint'];
			$order['address'] = $consignee_info['address'];
			$order['api_address'] = $consignee_info['api_address'];
			$order['consignee'] = $consignee;
			$order['mobile'] = $mobile;
			$order['ecv_id'] = $ecvid;
			if($order_delivery_time==''){
			$order['order_delivery_time']==0;
			}elseif($order_delivery_time==1){  //1表示尽快送达
			$order['order_delivery_time'] = $order_delivery_time;
			}else{
				$order['order_delivery_time'] = to_timespan($order_delivery_time);
			}
	
			$order['dc_comment'] = $dc_comment;  //外卖预订的客户留言
			$order['invoice'] = $invoice;
			$user_info = es_session::get("user_info");
			$GLOBALS['db']->autoExecute(DB_PREFIX."dc_order",$order,'UPDATE','id='.$id,'SILENT');

			//如果是在线支付
			if($payment_id==0){
				//生成order_id 后
				//1. 代金券支付
				$ecv_data = $result['ecv_data'];
				if($ecv_data)
				{
							$ecv_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Voucher'");
							if($ecv_data['money']>$order['total_price'])$ecv_data['money'] = $order['total_price'];
							$payment_notice_id = make_dcpayment_notice($result['ecv_money'],$order_id,$ecv_payment_id,"",$ecv_data['id']);
							require_once APP_ROOT_PATH."system/dc_payment/Dc_Voucher_payment.php";
							$voucher_payment = new Dc_Voucher_payment();
							$voucher_payment->direct_pay($ecv_data['sn'],$ecv_data['password'],$payment_notice_id);
				}
				
	
				//2. 余额支付
				$account_money = $result['account_money'];
				if(floatval($account_money) > 0)
				{
					$account_payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment where class_name = 'Account'");
					$payment_notice_id = make_dcpayment_notice($account_money,$order_id,$account_payment_id);
					require_once APP_ROOT_PATH."system/dc_payment/Dc_Account_payment.php";
					$account_payment = new Dc_Account_payment();
					$account_payment->get_payment_code($payment_notice_id);
				}
				
				//3. 相应的支付接口
				$payment_info = $result['payment_info'];
				if($payment_info&&$result['pay_price']>0)
				{
					$payment_notice_id = make_dcpayment_notice($result['pay_price'],$order_id,$payment_info['id']);
					//创建支付接口的付款单
				}
				
				$rs = dcorder_paid($order_id);
				//	update_order_cache($order_id);
				if($rs)
				{
					$data = array();
					$data['info'] = "";
					$data['jump'] = url("index","dc_payment#done",array("id"=>$payment_notice_id,'rs'=>1));
					ajax_return($data); //支付成功
				
				}
				else
				{
					$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$order_id);
					if($order_info['pay_status'] == 1)
					{
						$data = array();
						$data['info'] = "";
						$data['jump'] = url("index","dc_payment#done",array("id"=>$payment_notice_id,'rs'=>2));
	
					}
					else{
						$data = array();
						$data['info'] = "";
						$data['jump'] = url("index","dc_payment#pay",array("id"=>$payment_notice_id,'rs'=>0));
						
					}
					ajax_return($data);
				}
			
			}else{
				//货到付款
				$data = array();
				$data['info'] = "";
				$data['jump'] = url("index","dc_payment#done",array("id"=>$order_id,'rs'=>5));
				require_once APP_ROOT_PATH."system/model/dc.php";
				dcorder_paid_done($order_id);
				ajax_return($data);
			}
	
	}
}

?>