<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


class dealvModule extends BizBaseModule
{
	public function __construct()
	{
		parent::__construct();
		global_run();
		$this->check_auth();
	}
	public function index()
	{		
		
		init_app_page();
		$s_account_info = $GLOBALS['account_info'];
		$account_id = intval($s_account_info['id']);

		//获取支持的门店
		$location_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
		$GLOBALS['tmpl']->assign("location_list",$location_list);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['VERIFY_COUPON']);
		$GLOBALS['tmpl']->display("pages/verify/index.html");
	}
	
	
	public function check_coupon()
	{
		require_once  APP_ROOT_PATH."system/model/biz_verify.php";
		$s_account_info = $GLOBALS['account_info'];
		$location_id = intval($_REQUEST['location_id']);
		$pwd = strim($_REQUEST['coupon_pwd']);
		ajax_return(biz_check_coupon($s_account_info,$pwd,$location_id));
	}
	
	
	public function use_coupon()
	{
		require_once  APP_ROOT_PATH."system/model/biz_verify.php";
		$s_account_info = $GLOBALS['account_info'];
		$location_id = intval($_REQUEST['location_id']);
		$pwd = strim($_REQUEST['coupon_pwd']);
		ajax_return(biz_use_coupon($s_account_info,$pwd,$location_id));
	}

	
	//批量验证消费劵
	public function batch(){
		init_app_page();
		$s_account_info = $GLOBALS['account_info'];
		$account_id = intval($s_account_info['id']);
		
		assign_biz_nav_list();
	    
		
		//获取支持的门店
		$location_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
		$GLOBALS['tmpl']->assign("location_list",$location_list);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['VERIFY_COUPON']);
		$GLOBALS['tmpl']->display("pages/verify/verify_batch.html");
	}

	
	public function check_coupon_batch()
	{
		$s_account_info = $GLOBALS['account_info'];
		if(intval($s_account_info['id'])==0)
		{
			$res['status'] = 0;
			ajax_return($res);
		}
		else
		{
			
			$now = NOW_TIME;
			$supplier_id = intval($s_account_info['supplier_id']);
			$location_id = intval($_REQUEST['location_id']);

			require_once  APP_ROOT_PATH."system/model/biz_verify.php";
			$result = biz_check_coupon_batch($s_account_info,$location_id,$_REQUEST['coupon_pwd']);
		}

		ajax_return($result);
	}
	
	
	public function super(){
		init_app_page();
		$s_account_info = $GLOBALS['account_info'];
		$account_id = intval($s_account_info['id']);
		assign_biz_nav_list();
		
		
		//获取支持的门店
		$location_list = $GLOBALS['db']->getAll("select id,name from " . DB_PREFIX . "supplier_location where id in(" . implode(",", $GLOBALS['account_info']['location_ids']) . ")");
		$GLOBALS['tmpl']->assign("location_list",$location_list);
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['VERIFY_COUPON']);
		$GLOBALS['tmpl']->display("pages/verify/super.html");
	}
	
	public function super_check(){

		require_once  APP_ROOT_PATH."system/model/biz_verify.php";
		$s_account_info = $GLOBALS['account_info'];
		$location_id = intval($_REQUEST['location_id']);
		$pwd = strim($_REQUEST['coupon_pwd']);
		$result = biz_super_check_coupon($s_account_info,$pwd,$location_id);
		$result['location_id'] = $location_id;
		$result['coupon_pwd'] = $pwd;
		$GLOBALS['tmpl']->assign("result",$result);

		$result['weebox_html'] = $GLOBALS['tmpl']->fetch("pages/verify/super_weebox.html");
		ajax_return($result);
		
	}
	
	public function super_use_coupon(){
		$now = NOW_TIME;
		$location_id = intval($_REQUEST['location_id']);
		$pwd = strim($_REQUEST['coupon_pwd']);
		$coupon_use_count = intval($_REQUEST['coupon_use_count']); //使用数量
		require_once  APP_ROOT_PATH."system/model/biz_verify.php";
		ajax_return(biz_super_use_coupon($s_account_info,$location_id,$pwd,$coupon_use_count));
	}
}
?>