<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

if (isset($read_api) && $read_api == true)
{
    return false;
}

define("FILE_PATH","/dh"); //文件目录
require_once '../system/system_init.php';
require_once APP_ROOT_PATH.'app/Lib/'.APP_TYPE.'/core/main_init.php';


function emptyTag($string)
{
		if(empty($string))
			return "";
			
		$string = strip_tags(strim($string));
		$string = preg_replace("|&.+?;|",'',$string);
		
		return $string;
}
function convertUrl($url)
{
		$url = str_replace("&","&amp;",$url);
		return $url;
}
?>