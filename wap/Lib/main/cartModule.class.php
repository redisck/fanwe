<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class cartModule extends MainBaseModule
{

	public function index()
	{
		global_run();		
		init_app_page();
		
		$data = request_api("cart","index");
		if(empty($data['cart_list']))
		{
			app_redirect(wap_url("index"));
		}
		
		$GLOBALS['tmpl']->assign("sms_lesstime",load_sms_lesstime());
		$GLOBALS['tmpl']->assign("data",$data);		
		
		//生成json数据
		$jsondata = array();
		foreach($data['cart_list'] as $k=>$v)
		{
			$bind_data = array();
			$bind_data['id'] = $v['id'];
			if($data['is_score']==1)
			{
				$bind_data['unit_price'] = abs($v['return_score']);
				$bind_data['total_price'] = abs($v['return_total_score']);		
			}
			else
			{
				$bind_data['unit_price'] = round($v['unit_price'],2);
				$bind_data['total_price'] = round($v['total_price'],2);
			}
			$bind_data['number'] = $v['number'];
			$bind_data['deal_id'] = $v['deal_id'];
			$bind_data['max'] = $v['max'];
			$jsondata[$v['id']] = $bind_data;
		}
		$GLOBALS['tmpl']->assign("jsondata",json_encode($jsondata));
		
		
		$GLOBALS['tmpl']->display("cart.html");
	}
	
	public function addcart(){
	    global_run();
		$is_relate = false;
		$ids = $_REQUEST['id'];
	    if( !empty($ids)&&(is_array($ids)) ){
			$is_relate = true;
			$data = request_api("cart","addcartByRelate",array("ids"=>$ids,"deal_attr"=>$_REQUEST['dealAttrArray']));
		}else{
			$id = intval($ids);
			$deal_attr = array();
			if($_REQUEST['deal_attr'])
			{
				foreach($_REQUEST['deal_attr'] as $k=>$v)
				{
					$deal_attr[$k] = intval($v);
				}
			}
			$data = request_api("cart","addcart",array("id"=>$id,"deal_attr"=>$deal_attr));
		}
		
	    $ajax_data = array();
	    $ajax_data['status'] = $data['status'];
	    if($data['status']==1)
	    {
	    	$ajax_data['jump'] = wap_url("index","cart");
	    }
	    elseif($data['status']==-1)
	    {
	    	$ajax_data['jump'] = wap_url("index","user#login");
	    }
	    else
	    {
			if( $is_relate ){
				//有没有购买成功的商品
//				$ajax_data['info'] = array();
//				foreach($data as $kk=>$info){
//					if( in_array($kk,$ids) ){
//						$ajax_data['info'][$kk] = $info;
//					}
//				}
				$ajax_data['jump'] = wap_url("index","cart");
			}else{
				$ajax_data['info'] = $data['info'];
			}
	    }
	    
	    ajax_return($ajax_data);
	}
	
	public function check_cart()
	{
		global_run();
		
		$num = array();
	    if($_REQUEST['num'])
	    {
	    	foreach($_REQUEST['num'] as $k=>$v)
	    	{
	    		$num[$k] = intval($v);
	    	}
	    }
	    
	    $mobile = strim($_REQUEST['mobile']);
	    $sms_verify = strim($_REQUEST['sms_verify']);
	    
	    $data = request_api("cart","check_cart",array("num"=>$num,"mobile"=>$mobile,"sms_verify"=>$sms_verify));
	    
	    if($data['status'])
	    {
	    	$ajaxdata['jump'] = wap_url("index","cart#check");
	    	$ajaxdata['status'] = 1;
	    	ajax_return($ajaxdata);
	    }
	    else
	    {
	    	$ajaxdata['status'] = 0;
	    	$ajaxdata['info'] = $data['info'];
	    	ajax_return($ajaxdata);
	    }
	}
	
	
	public function del()
	{
		global_run();
		$id = intval($_REQUEST['id']);
		$data = request_api("cart","del",array("id"=>$id));
		
		app_redirect(get_gopreview());
	}
	
	public function check()
	{
		global_run();		
		init_app_page();
		
		$data = request_api("cart","check");
		if(!$GLOBALS['is_weixin'])
		{
			foreach($data['payment_list'] as $k=>$v)
			{
				if($v['code']=="Wwxjspay")
				{
					unset($data['payment_list'][$k]);
				}
			}
		}
		else
		{
			foreach($data['payment_list'] as $k=>$v)
			{
				if($v['code']=="Upacpwap")
				{
					unset($data['payment_list'][$k]);
				}
			}
		}
// 		print_r($data);exit;

		if($data['status']==-1)
		{
			app_redirect(wap_url("index","user#login"));
		}
		
		if(empty($data['cart_list']))
		{
			app_redirect(wap_url("index"));
		}
		
		$account_amount = round($GLOBALS['user_info']['money'],2);
		$GLOBALS['tmpl']->assign("account_amount",$account_amount);
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("cart_check.html");
	}
	
	
	public function done()
	{
		global_run();
		
		$param['delivery_id'] =  intval($_REQUEST['delivery']); //配送方式
		$param['ecvsn'] = $_REQUEST['ecvsn']?addslashes(trim($_REQUEST['ecvsn'])):'';
		$param['ecvpassword'] = $_REQUEST['ecvpassword']?addslashes(trim($_REQUEST['ecvpassword'])):'';
		$param['payment'] = intval($_REQUEST['payment']);
		$param['all_account_money'] = intval($_REQUEST['all_account_money']);
		$param['content'] = strim($_REQUEST['content']);
		
		$data = request_api("cart","done",$param);
		if($data['status']==-1)
		{
			$ajaxobj['status'] = 1;
			$ajaxobj['jump'] = wap_url("index","user#login");
			ajax_return($ajaxobj);
		}
		elseif($data['status']==1)
		{
			$ajaxobj['status'] = 1;
			$ajaxobj['jump'] = wap_url("index","payment#done",array("id"=>$data['order_id']));
			ajax_return($ajaxobj);
		}
		else
		{
			$ajaxobj['status'] = $data['status'];
			$ajaxobj['info'] = $data['info'];
			ajax_return($ajaxobj);
		}
		
	}
	
	public function order()
	{
		global_run();
		init_app_page();
	
		$order_id = intval($_REQUEST['id']);
		$data = request_api("cart","order",array("id"=>$order_id));
		if(!$GLOBALS['is_weixin'])
		{
			foreach($data['payment_list'] as $k=>$v)
			{
				if($v['code']=="Wwxjspay")
				{
					unset($data['payment_list'][$k]);
				}
			}
		}
		else
		{
			foreach($data['payment_list'] as $k=>$v)
			{
				if($v['code']=="Upacpwap")
				{
					unset($data['payment_list'][$k]);
				}
			}
		}
// 				print_r($data);exit;
	
		if($data['status']==-1)
		{
			app_redirect(wap_url("index","user#login"));
		}
		
		if($data['status']==0)
		{
			showErr($data['info']);
		}

	
		$account_amount = round($GLOBALS['user_info']['money'],2);
		$GLOBALS['tmpl']->assign("account_amount",$account_amount);
		$GLOBALS['tmpl']->assign("data",$data);
		$GLOBALS['tmpl']->display("cart_check.html");
	}
	
	public function order_done()
	{
		global_run();
		
		$param['order_id'] = intval($_REQUEST['order_id']);
		$param['delivery_id'] =  intval($_REQUEST['delivery']); //配送方式
		$param['payment'] = intval($_REQUEST['payment']);
		$param['all_account_money'] = intval($_REQUEST['all_account_money']);
		$param['content'] = strim($_REQUEST['content']);
		
		$data = request_api("cart","order_done",$param);
		if($data['status']==-1)
		{
			$ajaxobj['status'] = 1;
			$ajaxobj['jump'] = wap_url("index","user#login");
			ajax_return($ajaxobj);
		}
		elseif($data['status']==1)
		{
			$ajaxobj['status'] = 1;
			$ajaxobj['jump'] = wap_url("index","payment#done",array("id"=>$data['order_id']));
			ajax_return($ajaxobj);
		}
		else
		{
			$ajaxobj['status'] = $data['status'];
			$ajaxobj['info'] = $data['info'];
			ajax_return($ajaxobj);
		}
	}
}
?>