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

	
	
/**
 * 提交订单页面     
 */
	public function cart()
	{
	
		global_run();

		$param['lid']=$location_id = intval($_REQUEST['lid']);
		
		require_once APP_ROOT_PATH."wap/Lib/main/dcajaxModule.class.php";
		dcajaxModule::update_dc_cart($location_id);
		
		$param['form']='wap';
		$param['not_check_delivery']= intval($_REQUEST['not_check_delivery']);
		//付款方式 $payment_id=0为在线支付，$payment_id=1为货到付款
		$param['payment_id'] = intval($_REQUEST['payment_id']);
		require_once APP_ROOT_PATH."system/model/dc.php";
		$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
		$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
		$menu_num=array();
		foreach($location_dc_cart['cart_list'] as $k=>$v){
			$menu_num[$v['menu_id']]=$v['num'];
			
		}
		$param['menu_num'] = $menu_num;
		$rs_num=array();
		foreach($location_dc_table_cart['cart_list'] as $k=>$v){
			$rs_info=array();
			$rs_info['id']=$v['table_time_id'];
			$rs_info['num']=$v['num'];
			$rs_info['rs_date']=to_date($v['table_time'],"Y-m-d");
			$rs_num[]=$rs_info;
		}

		$param['ecvsn'] = strim($_REQUEST['ecvsn']);
		//$dc_type大等于0为预订方式，不享受促销优惠，-1代表享受促销优惠
		$param['dc_type'] = isset($_REQUEST['dc_type'])?intval($_REQUEST['dc_type']):-1;
		$param['consignee_id'] =$_REQUEST['consignee_id']?intval($_REQUEST['consignee_id']):'';

		$data = request_api("dcorder","cart",$param);
		//set_gopreview();
		//设置当前页面为前一页面
		$url=wap_url('index','dcorder#cart',array('lid'=>$location_id));
		es_session::set("wap_gopreview",$url);
		if($data['user_login_status']==1)
		{
			if($data['status']==0){

				if($data['is_return']==1){
					showErr($data['info'],0,wap_url('index','dcbuy',array('lid'=>$location_id)));
				}
			}

		//	print_r($data);
			$GLOBALS['tmpl']->assign("data",$data);
			$GLOBALS['tmpl']->assign("location_dc_table_cart",$location_dc_table_cart);
			$GLOBALS['tmpl']->assign("location_dc_cart",$location_dc_cart);
			$GLOBALS['tmpl']->display("dc/dc_cart.html");
		
		}else{
			showErr('未登录，请先登录',0,wap_url('index','user#login'));
			//app_redirect(wap_url('index','user#login'));
		}
		
}	


/**
 * 生成订单接口
 *
 */
public function make_order()
{

	
	global_run();
	
	//$dc_type大等于0为预订方式，不享受促销优惠，-1代表享受促销优惠
	$param['dc_type'] = isset($_REQUEST['dc_type'])?intval($_REQUEST['dc_type']):-1;
	
	$param['lid']=$location_id = intval($_REQUEST['lid']);
	
	
	$param['consignee'] = strim($_REQUEST['consignee']);
	$param['mobile'] = strim($_REQUEST['mobile']);
	$param['ecvsn'] = $_REQUEST['ecvsn']?strim($_REQUEST['ecvsn']):'';	
	$param['dc_comment'] = $_REQUEST['dc_comment']?strim( $_REQUEST['dc_comment']):'';
	$param['invoice'] =  $_REQUEST['invoice']?strim($_REQUEST['invoice']):'';

	//付款方式 $payment_id=0为在线支付，$payment_id=1为货到付款
	$param['payment_id'] = intval($_REQUEST['payment_id']);
	require_once APP_ROOT_PATH."system/model/dc.php";
	$location_dc_table_cart=load_dc_cart_list(true,$location_id,$type=0);
	$location_dc_cart=load_dc_cart_list(true,$location_id,$type=1);
	$menu_num=array();
	foreach($location_dc_cart['cart_list'] as $k=>$v){
		$menu_num[$v['menu_id']]=$v['num'];
			
	}
	$param['menu_num'] = $menu_num;
	$rs_num=array();
	foreach($location_dc_table_cart['cart_list'] as $k=>$v){
		$rs_info=array();
		$rs_info['id']=$v['table_time_id'];
		$rs_info['num']=$v['num'];
		$rs_info['rs_date']=to_date($v['table_time'],"Y-m-d");
		$rs_num[]=$rs_info;
	}
	
	$param['rs_num'] = $rs_num;
	$param['consignee_id'] =$_REQUEST['consignee_id']?intval($_REQUEST['consignee_id']):'';
	$param['order_delivery_time']=  $_REQUEST['order_delivery_time']?strim( $_REQUEST['order_delivery_time']):'';

	/*
	$param['lid']=41;
	$param['dc_type']=1;
	$param['consignee_id']=119;
	$param['order_delivery_time']='18:30';
	$param['dc_comment']='不要吃';
	$param['invoice']='发标';
	*/
	$data = request_api("dcorder","make_order",$param);
	//ajax_return($data);

	if($param['dc_type']==1){
		$url=wap_url('index','dctable',array('lid'=>$location_id));
		es_session::set("wap_gopreview",$url);
	}
	if($data['user_login_status']==1)
	{
		
			$result['status']=$data['status'];
			$result['info']=$data['info'];
			if($result['status']==0){
				$result['jump']=wap_url('index','dcbuy',array('lid'=>$location_id));
			}else{
				$result['jump']=wap_url('index','dcorder#order',array('id'=>$data['order_id']));
			}
			ajax_return($result);
	
	}else{
		//app_redirect(wap_url('index','user#login'));
		$result['status']=-1;
		$result['info']='未登录，请先登录';
		$result['jump']=wap_url('index','user#login');
		ajax_return($result);
		//app_redirect(wap_url('index','user#login'));
	}


}




/**
 * 继续支付的页面
 *
 */

public function order(){

	global_run();

	$param['id'] = intval($_REQUEST['id']);
	$param['payment'] = intval($_REQUEST['payment']);
	$param['all_account_money'] = intval($_REQUEST['all_account_money']);
	$param['from'] = 'wap';
	$data = request_api("dcorder","order",$param);

	if($data['user_login_status']==1)
	{

		if($data['status']==0){
			if($data['is_rs']==1){
				$url=wap_url('index','dc_rsorder#view',array('id'=>$data['id']));
			}else{
				$url=wap_url('index','dc_dcorder#view',array('id'=>$data['id']));
			}
			if($data['is_return']==1){
				showErr($data['info'],0,$url);
			}else{
				showErr($data['info'],1,get_current_url());
			}
		
		}
		
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("dc/dcorder.html");
	
	}else{
		showErr('未登录，请先登录',0,wap_url('index','user#login'));
	}
}


/**
 *  继续支付页面，点击 “确认支付”后的提交地址
 *
 */
public function order_done()
{
	
	$param['id'] = intval($_REQUEST['id']);
	$param['payment'] = intval($_REQUEST['payment']);
	$param['all_account_money'] = intval($_REQUEST['all_account_money']);
	$param['account_money'] = floatval($_REQUEST['account_money']);
	$param['payment_fee'] = floatval($_REQUEST['payment_fee']);
	$param['pay_price'] = floatval($_REQUEST['pay_price']);
	
	$data = request_api("dcorder","order_done",$param);
	if($data['user_login_status']==1)
	{
		if($data['status']==1){
			$order_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."dc_order where id = ".$data['order_id']);
			if($data['pay_status']==1 || $data['pay_status']==5 ){
			$GLOBALS['db']->query("delete from ".DB_PREFIX."dc_cart where user_id=".$order_info['user_id']." and location_id=".$order_info['location_id']." and session_id='".es_session::id()."'");
			}
			$data['jump']=wap_url('index','dc_payment#done',array('pay_status'=>$data['pay_status'],'order_id'=>$data['order_id'],'payment_notice_id'=>$data['payment_notice_id']));
		}else{
			$data['jump']=wap_url('index','dcorder#order',array('id'=>$param['id']));
		}

		ajax_return($data);
	
	}else{
		showErr('未登录，请先登录',0,wap_url('index','user#login'));
	}
}






}

?>