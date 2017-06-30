<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class BizBaseModule{
	public function __construct()
	{
		if($GLOBALS['distribution_cfg']['OSS_TYPE']&&$GLOBALS['distribution_cfg']['OSS_TYPE']=="ES_FILE")
		{
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
		if(
				MODULE_NAME=="account"&&ACTION_NAME=="index"||
				MODULE_NAME=="balance"&&ACTION_NAME=="index"||
				MODULE_NAME=="bankinfo"&&ACTION_NAME=="index"||
				MODULE_NAME=="deal"&&ACTION_NAME=="index"||
				MODULE_NAME=="dealo"&&ACTION_NAME=="index"||
				MODULE_NAME=="dealr"&&ACTION_NAME=="index"||
				MODULE_NAME=="dealv"&&ACTION_NAME=="index"||
				MODULE_NAME=="delivery"&&ACTION_NAME=="index"||
				MODULE_NAME=="event"&&ACTION_NAME=="index"||
				MODULE_NAME=="evento"&&ACTION_NAME=="index"||
				MODULE_NAME=="eventr"&&ACTION_NAME=="index"||
				MODULE_NAME=="eventv"&&ACTION_NAME=="index"||
				MODULE_NAME=="goods"&&ACTION_NAME=="index"||
				MODULE_NAME=="goodso"&&ACTION_NAME=="index"||
				MODULE_NAME=="index"&&ACTION_NAME=="index"||
				MODULE_NAME=="location"&&ACTION_NAME=="index"||
				MODULE_NAME=="storer"&&ACTION_NAME=="index"||
				MODULE_NAME=="withdrawal"&&ACTION_NAME=="index"||
				MODULE_NAME=="wxconf"&&ACTION_NAME=="index"||
				MODULE_NAME=="youhui"&&ACTION_NAME=="index"||
				MODULE_NAME=="youhuio"&&ACTION_NAME=="index"||
				MODULE_NAME=="youhuir"&&ACTION_NAME=="index"||
				MODULE_NAME=="youhuiv"&&ACTION_NAME=="index"
		)
		set_biz_gopreview();
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
	
	/**
	 * 验证用户权限
	 */
	protected function check_auth()
	{	    
	    $ajax = intval($_REQUEST['ajax']);
		$s_account_info = $GLOBALS['account_info'];
		
		if(intval($s_account_info['id'])==0)
		{
		    showBizErr("没有登录商户账户，请先登录!",$ajax,url("biz","user#login"));
		}
		else
		{
		   //获取权限进行判断
		   if(!check_module_auth(MODULE_NAME)){
		       showBizErr("没有操作模块的权限，请更换有权限的账户登录!",$ajax);
		   }
		}
	}
	
	
}
?>