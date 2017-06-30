<?php
//城市以及地理定位的管理类
class City{
	
	/**
	 * 定位经续度
	 * @param unknown_type $x 经度
	 * @param unknown_type $y 纬度
	 */
	public static function locate_geo($xpoint=0,$ypoint=0)
	{
		$xpoint = floatval($xpoint);
		$ypoint = floatval($ypoint);
		$current_geo = es_session::get("current_geo");
				
		if($xpoint!=0)
		{
			$current_geo['xpoint'] = $xpoint;
			$current_geo['address'] = "";
		}
		elseif(empty($current_geo['xpoint']))
			$current_geo['xpoint'] = 0;
		
		if($ypoint!=0)
		{
			$current_geo['ypoint'] = $ypoint;
			$current_geo['address'] = "";
		}		
		elseif(empty($current_geo['ypoint']))
			$current_geo['ypoint'] = 0;
		

		
		if($current_geo['xpoint']>0||$current_geo['ypoint']>0)
		{
			if($current_geo['address']=="")
			{
				$url = "http://api.map.baidu.com/geocoder/v2/?ak=FANWE_MAP_KEY&location=FANWE_MAP_YPOINT,FANWE_MAP_XPOINT&output=json";
				$url = str_replace("FANWE_MAP_KEY", app_conf("BAIDU_MAP_APPKEY"), $url);
				$url = str_replace("FANWE_MAP_YPOINT", $current_geo['ypoint'], $url);
				$url = str_replace("FANWE_MAP_XPOINT", $current_geo['xpoint'], $url);
				
				require_once APP_ROOT_PATH."system/utils/transport.php";
				$trans = new transport();
				$trans->use_curl = true;
				$request_data = $trans->request($url);
				$data = $request_data['body'];
				$data = json_decode($data,1);
				$address = $data['result']['sematic_description'];
				
				if($address)
					$current_geo['address'] = $address;
				else
				{
					es_session::delete("current_geo");
					return null;
				}
			}
		}		
		
		es_session::set("current_geo",$current_geo);
		return $current_geo;
		
	}
	
	/**
	 * 清除当前地理定位
	 */
	public static function clear_geo()
	{
		es_session::delete("current_geo");
	}
	
	//定位城市 $city_py:拼音或ID
	public static function locate_city($city_py="")
	{		
		if(!$city_py)
		$city_py = strim($_GET['city']);		
		if($city_py)
		{
			$current_city = es_session::get("current_city");
			
			//强行定位
			if($current_city['uname']!=$city_py&&$current_city['id']!=$city_py)
			$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where (uname = '".$city_py."' or id = '".$city_py."') and is_effect = 1");
		}
		
		if(empty($current_city))
		{
			//无城市，由session中获取
			$current_city = es_session::get("current_city");
		}
		
		if(empty($current_city))
		{	
			$city_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_city where is_effect = 1");
			//自动定位
			require_once APP_ROOT_PATH."system/extend/ip.php";
			$ip =  CLIENT_IP;
			$iplocation = new iplocate();
			$address=$iplocation->getaddress($ip);
			foreach ($city_list as $city)
			{
				if(strpos($address['area1'],$city['name']))
				{
					$current_city = $city;
					break;
				}
			}
		}
		
		if(empty($current_city))
		$current_city = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_city where is_default = 1 and is_effect = 1");

		es_session::set("current_city", $current_city);
		return $current_city;
		
	}
	

}
?>