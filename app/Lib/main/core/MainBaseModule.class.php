<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class MainBaseModule{
	public function __construct()
	{
		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']=="ES_FILE")
		{
			//logger::write($GLOBALS['distribution_cfg']['OSS_DOMAIN']."/es_file.php");
			global $syn_image_ci;
			global $curl_param;
			//global $syn_image_idx;
			$syn_image_idx = 0;
			$syn_image_ci  =  curl_init($GLOBALS['distribution_cfg']['OSS_DOMAIN']."/es_file.php");
			curl_setopt($syn_image_ci, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($syn_image_ci, CURLOPT_NOPROGRESS, true);
			curl_setopt($syn_image_ci, CURLOPT_HEADER, false);
			curl_setopt($syn_image_ci, CURLOPT_POST, TRUE);
			curl_setopt($syn_image_ci, CURLOPT_TIMEOUT, 1);
			curl_setopt($syn_image_ci, CURLOPT_TIMECONDITION, 1);
			$curl_param['username'] = $GLOBALS['distribution_cfg']['OSS_ACCESS_ID'];
			$curl_param['password'] = $GLOBALS['distribution_cfg']['OSS_ACCESS_KEY'];
			$curl_param['act'] = 2;
		}
		
		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");
		$GLOBALS['dynamic_cache'] = $GLOBALS['cache']->get("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
		$GLOBALS['dynamic_avatar_cache'] = $GLOBALS['cache']->get("AVATAR_DYNAMIC_CACHE"); //头像的动态缓存
		
		/* 返回上一页后续再做*/
		if(
		MODULE_NAME=="index"&&ACTION_NAME=="index"||
		MODULE_NAME=="acate"&&ACTION_NAME=="index"||
		MODULE_NAME=="article"&&ACTION_NAME=="index"||
		MODULE_NAME=="article"&&ACTION_NAME=="system_article"||
		MODULE_NAME=="cart"&&ACTION_NAME=="index"||
		MODULE_NAME=="cart"&&ACTION_NAME=="check"||
		MODULE_NAME=="cart"&&ACTION_NAME=="order"||
		MODULE_NAME=="cate"&&ACTION_NAME=="index"||
		MODULE_NAME=="daren"&&ACTION_NAME=="index"||
		MODULE_NAME=="daren"&&ACTION_NAME=="submit"||		
		MODULE_NAME=="deal"&&ACTION_NAME=="index"||
		MODULE_NAME=="discover"&&ACTION_NAME=="index"||
		MODULE_NAME=="event"&&ACTION_NAME=="index"||
		MODULE_NAME=="events"&&ACTION_NAME=="index"||
		MODULE_NAME=="group"&&ACTION_NAME=="index"||
		MODULE_NAME=="group"&&ACTION_NAME=="create"||
		MODULE_NAME=="group"&&ACTION_NAME=="forum"||
		MODULE_NAME=="group"&&ACTION_NAME=="user_list"||
		MODULE_NAME=="help"&&ACTION_NAME=="index"||		
		MODULE_NAME=="link"&&ACTION_NAME=="index"||
		MODULE_NAME=="mall"&&ACTION_NAME=="index"||
		MODULE_NAME=="news"&&ACTION_NAME=="index"||
		MODULE_NAME=="notice"&&ACTION_NAME=="index"||
		MODULE_NAME=="review"&&ACTION_NAME=="index"||
		MODULE_NAME=="scores"&&ACTION_NAME=="index"||
		MODULE_NAME=="store"&&ACTION_NAME=="index"||
		MODULE_NAME=="stores"&&ACTION_NAME=="index"||
		MODULE_NAME=="stores"&&ACTION_NAME=="brand"||
		MODULE_NAME=="sys_article"&&ACTION_NAME=="index"||
		MODULE_NAME=="topic"&&ACTION_NAME=="index"||
		MODULE_NAME=="tuan"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_account"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_collect"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_collect"&&ACTION_NAME=="youhui_collect"||
		MODULE_NAME=="uc_collect"&&ACTION_NAME=="event_collect"||
		MODULE_NAME=="uc_consignee"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_consignee"&&ACTION_NAME=="add"||
		MODULE_NAME=="uc_coupon"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_event"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_home"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_home"&&ACTION_NAME=="myfav"||
		MODULE_NAME=="uc_home"&&ACTION_NAME=="uc_follow_list"||
		MODULE_NAME=="uc_home"&&ACTION_NAME=="uc_fans_list"||
		MODULE_NAME=="uc_invite"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_log"&&ACTION_NAME=="money"||
		MODULE_NAME=="uc_log"&&ACTION_NAME=="score"||
		MODULE_NAME=="uc_log"&&ACTION_NAME=="point"||
		MODULE_NAME=="uc_log"&&ACTION_NAME=="exchange"||
		MODULE_NAME=="uc_lottery"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_medal"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_money"&&ACTION_NAME=="withdraw"||
		MODULE_NAME=="uc_money"&&ACTION_NAME=="incharge"||
		MODULE_NAME=="uc_msg"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_myinfo"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_order"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_order"&&ACTION_NAME=="view"||
		MODULE_NAME=="uc_review"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_voucher"&&ACTION_NAME=="index"||
		MODULE_NAME=="uc_voucher"&&ACTION_NAME=="exchange"||
		MODULE_NAME=="uc_youhui"&&ACTION_NAME=="index"||
		MODULE_NAME=="youhui"&&ACTION_NAME=="index"||
		MODULE_NAME=="youhuis"&&ACTION_NAME=="index")
		{
			set_gopreview();
		}
		
		
	}

	public function index()
	{
		showErr("invalid access");
	}
	public function __destruct()
	{
		
		if(isset($GLOBALS['cache']))
		{
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");
			$GLOBALS['cache']->set("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME,$GLOBALS['dynamic_cache']);
			if(count($GLOBALS['dynamic_avatar_cache'])<=500)
			{
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
				$GLOBALS['cache']->set("AVATAR_DYNAMIC_CACHE",$GLOBALS['dynamic_avatar_cache']); //头像的动态缓存
			}
		}
		
		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']=="ES_FILE")
		{
			if(count($GLOBALS['curl_param']['images'])>0)
			{
				$GLOBALS['curl_param']['images'] =  base64_encode(serialize($GLOBALS['curl_param']['images']));
				curl_setopt($GLOBALS['syn_image_ci'], CURLOPT_POSTFIELDS, $GLOBALS['curl_param']);
				$rss = curl_exec($GLOBALS['syn_image_ci']);
			}
			curl_close($GLOBALS['syn_image_ci']);
		}
		if($GLOBALS['refresh_page']&&!IS_DEBUG)
		{
			echo "<script>location.reload();</script>";
			exit;
		}
		unset($this);
	}
}
?>