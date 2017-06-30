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
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".CACHE_SUBDIR."/page_static_cache/");
		$GLOBALS['dynamic_cache'] = $GLOBALS['cache']->get("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".CACHE_SUBDIR."/avatar_cache/");
		$GLOBALS['dynamic_avatar_cache'] = $GLOBALS['cache']->get("AVATAR_DYNAMIC_CACHE"); //头像的动态缓存
		
		
		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);		
		
		
		/* 返回上一页后续再做*/
		if(
				MODULE_NAME=="index"&&ACTION_NAME=="index"||
				MODULE_NAME=="cart"&&ACTION_NAME=="check"||
				MODULE_NAME=="cart"&&ACTION_NAME=="order"||
				MODULE_NAME=="cart"&&ACTION_NAME=="index"||
				MODULE_NAME=="city"&&ACTION_NAME=="index"||
				MODULE_NAME=="deal_detail"&&ACTION_NAME=="index"||
				MODULE_NAME=="deal"&&ACTION_NAME=="index"||
				MODULE_NAME=="dp_list"&&ACTION_NAME=="index"||
				MODULE_NAME=="event"&&ACTION_NAME=="index"||
				MODULE_NAME=="event"&&ACTION_NAME=="detail"||
				MODULE_NAME=="event"&&ACTION_NAME=="load_event_submit"||
				MODULE_NAME=="events"&&ACTION_NAME=="index"||
				MODULE_NAME=="goods"&&ACTION_NAME=="index"||
				MODULE_NAME=="notice"&&ACTION_NAME=="index"||
				MODULE_NAME=="notice"&&ACTION_NAME=="detail"||
				MODULE_NAME=="payment"&&ACTION_NAME=="done"||
				MODULE_NAME=="scores"&&ACTION_NAME=="index"||
				MODULE_NAME=="search"&&ACTION_NAME=="index"||
				MODULE_NAME=="stores"&&ACTION_NAME=="index"||
				MODULE_NAME=="store"&&ACTION_NAME=="index"||
				MODULE_NAME=="tuan"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_address"&&ACTION_NAME=="add"||
				MODULE_NAME=="uc_address"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_collect"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_collect"&&ACTION_NAME=="event_collect"||
				MODULE_NAME=="uc_collect"&&ACTION_NAME=="youhui_collect"||
				MODULE_NAME=="uc_coupon"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_event"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_invite"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_lottery"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_order"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_review"&&ACTION_NAME=="index"||
				MODULE_NAME=="uc_youhui"&&ACTION_NAME=="index"||
				MODULE_NAME=="user_center"&&ACTION_NAME=="index"||
				MODULE_NAME=="youhui"&&ACTION_NAME=="index"||
				MODULE_NAME=="youhui"&&ACTION_NAME=="detail"||
				MODULE_NAME=="youhuis"&&ACTION_NAME=="index"||
		        MODULE_NAME=="ecv"&&ACTION_NAME=="index"||
		        MODULE_NAME=="uc_home"&&ACTION_NAME=="index"
				)
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
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".CACHE_SUBDIR."/page_static_cache/");
			$GLOBALS['cache']->set("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME,$GLOBALS['dynamic_cache']);
			if(count($GLOBALS['dynamic_avatar_cache'])<=500)
			{
				$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".CACHE_SUBDIR."/avatar_cache/");
				$GLOBALS['cache']->set("AVATAR_DYNAMIC_CACHE",$GLOBALS['dynamic_avatar_cache']); //头像的动态缓存
			}
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