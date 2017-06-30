<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2014 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------

class userxypointModule extends MainBaseModule
{
	public function index()
	{
		global_run();		
		if($GLOBALS['geo']['xpoint']==0&&$GLOBALS['geo']['ypoint']==0)
		{
			request_api("userxypoint","index");
		}		
				
		$xpoint = strim($_REQUEST['m_longitude']);
		$ypoint = strim($_REQUEST['m_latitude']);
		if($xpoint&&$ypoint)
		{
			$url = "http://api.map.baidu.com/geocoder/v2/?ak=FANWE_MAP_KEY&location=FANWE_MAP_YPOINT,FANWE_MAP_XPOINT&output=json";
			$url = str_replace("FANWE_MAP_KEY", app_conf("BAIDU_MAP_APPKEY"), $url);
			$url = str_replace("FANWE_MAP_YPOINT", $ypoint, $url);
			$url = str_replace("FANWE_MAP_XPOINT", $xpoint, $url);
				
			require_once APP_ROOT_PATH."system/utils/transport.php";
			$trans = new transport();
			$trans->use_curl = true;
			$request_data = $trans->request($url);
			$data = $request_data['body'];
			$data = json_decode($data,1);
			$data = $data['result']['addressComponent'];
			$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where is_effect = 1 and LOCATE(name,'".$data['district']."')");
			if(empty($current_city))
				$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where is_effect = 1 and LOCATE(name,'".$data['city']."')");
			
			if($current_city&&$current_city['id']!=$GLOBALS['city']['id'])
			{
				ajax_return(array("status"=>0,"city"=>$current_city));
			}
		}
		
		ajax_return(array("status"=>1));
	}
	
	
}
?>