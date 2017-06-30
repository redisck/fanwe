<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

//开始定义IOS/android的客户端版本号
define("IOS_CLIENT_VERSION","3.03.01");
define("ANDROID_CLIENT_VERSION","4.5.2");
define("IS_IOS_UPGRADING",false); //IOS正在审核中，审核结束改为false,true时将会关闭相关审核不允许出现的内容
define("IS_ANDROID_UPGRADING",false);

require_once APP_ROOT_PATH.FOLDER_NAME.'/Lib/core/common.php';
define("CACHE_SUBDIR","mapi");

$IMG_APP_ROOT = APP_ROOT;

$_REQUEST = array_merge($_GET,$_POST);
filter_request($_REQUEST);
convert_req($_REQUEST);

if (isset($_REQUEST['i_type']))
{
	$i_type = intval($_REQUEST['i_type']);
}

if ($i_type == 1)
{
	$request = $_REQUEST;
}
else
{
	if (isset($_REQUEST['requestData']))
	{
		if ($i_type == 2)
		{
			$request = json_decode(trim($_REQUEST['requestData']), 1);			
		}else if($i_type == 4){
			
			require_once APP_ROOT_PATH.'/system/libs/crypt_aes.php';
			$aes = new CryptAES();
			$aes->set_key(FANWE_AES_KEY);
			$aes->require_pkcs5();

			$decString = $aes->decrypt(trim($_REQUEST['requestData']));

			$request = json_decode($decString, 1);
		}
		else
		{
			//$_REQUEST['requestData'] ="eyJ1c2VyX2luZm8iOiJ7XCJpZFwiOjE3Mjk0OTAxNjAsXCJpZHN0clwiOlwiMTcyOTQ5MDE2MFwiLFwic2NyZWVuX25hbWVcIjpcIkNoaWdhb1wiLFwibmFtZVwiOlwiQ2hpZ2FvXCIsXCJwcm92aW5jZVwiOlwiMzVcIixcImNpdHlcIjpcIjFcIixcImxvY2F0aW9uXCI6XCLnpo/lu7og56aP5beeXCIsXCJkZXNjcmlwdGlvblwiOlwiXCIsXCJ1cmxcIjpcImh0dHA6XC9cL3d3dy5teXhpbGkuY29tXCIsXCJwcm9maWxlX2ltYWdlX3VybFwiOlwiaHR0cDpcL1wvdHAxLnNpbmFpbWcuY25cLzE3Mjk0OTAxNjBcLzUwXC8wXC8xXCIsXCJwcm9maWxlX3VybFwiOlwiY2hpZ2FvXCIsXCJkb21haW5cIjpcImNoaWdhb1wiLFwid2VpaGFvXCI6XCJcIixcImdlbmRlclwiOlwibVwiLFwiZm9sbG93ZXJzX2NvdW50XCI6OTgsXCJmcmllbmRzX2NvdW50XCI6NDMwLFwic3RhdHVzZXNfY291bnRcIjo1NjUsXCJmYXZvdXJpdGVzX2NvdW50XCI6MCxcImNyZWF0ZWRfYXRcIjpcIlR1ZSBBcHIgMTMgMTc6MTc6MzMgKzA4MDAgMjAxMFwiLFwiZm9sbG93aW5nXCI6ZmFsc2UsXCJhbGxvd19hbGxfYWN0X21zZ1wiOmZhbHNlLFwiZ2VvX2VuYWJsZWRcIjp0cnVlLFwidmVyaWZpZWRcIjpmYWxzZSxcInZlcmlmaWVkX3R5cGVcIjotMSxcInJlbWFya1wiOlwiXCIsXCJzdGF0dXNcIjp7XCJjcmVhdGVkX2F0XCI6XCJTYXQgTWFyIDIzIDE2OjIwOjA3ICswODAwIDIwMTNcIixcImlkXCI6MzU1OTA0ODc0ODY1Mjk5NCxcIm1pZFwiOlwiMzU1OTA0ODc0ODY1Mjk5NFwiLFwiaWRzdHJcIjpcIjM1NTkwNDg3NDg2NTI5OTRcIixcInRleHRcIjpcIiPoiIzlsJbkuIrnmoTpn6nlm70jIOWFs+mUrueci+aYr+S4jeaYr+e7v+iJsuaXoOaxoeafk++8jOacieayoeaciea3u+WKoOWJgu+8gSDor6bmg4U6aHR0cDpcL1wvdC5jblwvellreXB0eFwiLFwic291cmNlXCI6XCI8YSBocmVmPVxcXCJodHRwOlwvXC9hcHAud2VpYm8uY29tXC90XC9mZWVkXC80QWJBRlZcXFwiIHJlbD1cXFwibm9mb2xsb3dcXFwiPuW+ruivnemimDxcL2E+XCIsXCJmYXZvcml0ZWRcIjpmYWxzZSxcInRydW5jYXRlZFwiOmZhbHNlLFwiaW5fcmVwbHlfdG9fc3RhdHVzX2lkXCI6XCJcIixcImluX3JlcGx5X3RvX3VzZXJfaWRcIjpcIlwiLFwiaW5fcmVwbHlfdG9fc2NyZWVuX25hbWVcIjpcIlwiLFwicGljX3VybHNcIjpbXSxcImdlb1wiOm51bGwsXCJhbm5vdGF0aW9uc1wiOlt7XCJzb3VyY2VcIjp7XCJuYW1lXCI6XCLoiIzlsJbkuIrnmoTpn6nlm71cIixcImFwcGlkXCI6XCI0MzhcIixcInVybFwiOlwiaHR0cDpcL1wvaHVhdGkud2VpYm8uY29tXC8yOTU4MlwifX0se1wiaHVhdGlcIjp7XCJmcm9tXCI6XCJ0b3BpYgopeWJsaXNoXCJ9fV0sXCJyZXBvc3RzX2NvdW50XCI6MCxcImNvbW1lbnRzX2NvdW50XCI6MCxcImF0dGl0dWRlc19jb3VudFwiOjAsXCJtbGV2ZWxcIjowLFwidmlzaWJsZVwiOntcInR5cGVcIjowLFwibGlzdF9pZFwiOjB9fSxcImFsbG93X2FsbF9jb21tZW50XCI6dHJ1ZSxcImF2YXRhcl9sYXJnZVwiOlwiaHR0cDpcL1wvdHAxLnNpbmFpbWcuY25cLzE3Mjk0OTAxNjBcLzE4MFwvMFwvMVwiLFwidmVyaWZpZWRfcmVhc29uXCI6XCJcIixcImZvbGxvd19tZVwiOmZhbHNlLFwib25saW5lX3N0YXR1c1wiOjAsXCJiaV9mb2xsb3dlcnNfY291bnRcIjoxLFwibGFuZ1wiOlwiemgtY25cIixcInN0YXJcIjowLFwibWJ0eXBlXCI6MCxcIm1icmFua1wiOjAsXCJibG9ja193b3JkXCI6MH0iLCJyZWZyZXNoX3RpbWUiOiIxMzcxNDA5MjE2IiwiYWNjZXNzX3NlY3JldCI6IjVkY2RjMTlmMDhjZmU3ZThkM2MxNTM2YzAxZGYwYTk0IiwidHlwZSI6ImFuZHJvaWQiLCJhY3QiOiJzeW5jbG9naW4iLCJzaW5hX2lkIjoiMTcyOTQ5MDE2MCIsImFjY2Vzc190b2tlbiI6IjIuMDBhNWxDdEJpOHV4X0JjNThiYTA2OGRiQ3BKdjlCIiwibG9naW5fdHlwZSI6IlVTU2luYSJ9";
			$request = base64_decode((trim($_REQUEST['requestData'])));
			$request = json_decode($request, 1);
		}
	}else
	{
		$request = $_REQUEST;
	}
}


if($request['from']=="wap"){
	define('APP_INDEX','wap');
}else{
	define('APP_INDEX','app');
}

if(IS_DEBUG)
{
	if($i_type==0)
	$url = SITE_DOMAIN.APP_ROOT."/".FOLDER_NAME."/index.php?requestData=".$_REQUEST['requestData']."&r_type=2";
	else
	{
		$url_request = $request;
		unset($url_request['r_type']);
		$debug_param_str = http_build_query($url_request);
		$url = SITE_DOMAIN.APP_ROOT."/".FOLDER_NAME."/index.php?r_type=2&".$debug_param_str;
	}
	$api_log = array();
	$api_log['api'] = $url;
	$api_log['act'] = $request['ctl']."#".$request['act'];
	$api_log['param_json'] = json_encode($request);
	$api_log['param_array'] = print_r($request,1);
	$GLOBALS['db']->autoExecute(DB_PREFIX."api_log", $api_log, 'INSERT');
}


$m_config = getMConfig();//初始化手机端配置

//定义一些常量
if (intval($m_config['page_size']) == 0){
	define('PAGE_SIZE',20); //分页的常量
}else{
	define('PAGE_SIZE',intval($m_config['page_size'])); //分页的常量
}
define('VERSION',3.05); //接口版本号,float 类型


$image_zoom = 2;
if(intval($GLOBALS['request']['image_zoom'])>0)
{
	$image_zoom = intval($GLOBALS['request']['image_zoom']);
}
define("IMAGE_ZOOM",$image_zoom);


//初始化session
global $sess_id;
global $define_sess_id;
$sess_id = strim($request['sess_id']);
if($sess_id)
{
	$define_sess_id = true;
	es_session::set_sessid($sess_id);
}
else
{
	$define_sess_id = false;
	$sess_id= es_session::id();
}

global_run();


//开始定位模板引擎，用于专题代码的编译
if(!file_exists(APP_ROOT_PATH.'public/runtime/'.CACHE_SUBDIR.'/'))
{
	mkdir(APP_ROOT_PATH.'public/runtime/'.CACHE_SUBDIR.'/',0777);
}
if(!file_exists(APP_ROOT_PATH.'public/runtime/'.CACHE_SUBDIR.'/tpl_caches/'))
	mkdir(APP_ROOT_PATH.'public/runtime/'.CACHE_SUBDIR.'/tpl_caches/',0777);
if(!file_exists(APP_ROOT_PATH.'public/runtime/'.CACHE_SUBDIR.'/tpl_compiled/'))
	mkdir(APP_ROOT_PATH.'public/runtime/'.CACHE_SUBDIR.'/tpl_compiled/',0777);
$GLOBALS['tmpl']->cache_dir      = APP_ROOT_PATH . 'public/runtime/'.CACHE_SUBDIR.'/tpl_caches';
$GLOBALS['tmpl']->compile_dir    = APP_ROOT_PATH . 'public/runtime/'.CACHE_SUBDIR.'/tpl_compiled';
$GLOBALS['tmpl']->template_dir   = APP_ROOT_PATH .FOLDER_NAME.'/mobile_zt';

//定义模板路径
$tmpl_path = SITE_DOMAIN.APP_ROOT."/".FOLDER_NAME."/mobile_zt/";


$GLOBALS['tmpl']->assign("TMPL",$tmpl_path);
$GLOBALS['tmpl']->assign("TMPL_REAL",APP_ROOT_PATH.FOLDER_NAME."/mobile_zt");
$GLOBALS['tmpl']->assign("APP_INDEX",APP_INDEX);

$GLOBALS['tmpl']->assign("APP_ROOT",APP_ROOT);
//end tmpl

?>