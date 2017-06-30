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
	
	
	/**
	 * 更新会员的定位
	 * 输入：
	 * 无，由底层的m_longitude与m_latitude获取
	 * 
	 * 输出：
	 * m_latitude: float 纬度y
	 * m_longitude： float 经度x
	 * 
	 */
	public function index()
	{
		$root = array();
		
		//检查用户,用户密码
		$user = $GLOBALS['user_info'];
		$user_id  = intval($user['id']);
		
		$latitude = $GLOBALS['geo']['ypoint'];//ypoint
		$longitude = $GLOBALS['geo']['xpoint'];//xpoint
		
		
		$root['m_latitude'] = $latitude;
		$root['m_longitude'] = $longitude;

		
		if ($user_id > 0 && $latitude > 0 && $longitude > 0){
			$user_x_y_point = array(
					'uid' => $user_id,
					'xpoint' => $longitude,
					'ypoint' => $latitude,
					'locate_time' => NOW_TIME,
			);
			$GLOBALS['db']->autoExecute(DB_PREFIX."user_x_y_point", $user_x_y_point, 'INSERT');
			$sql = "update ".DB_PREFIX."user set xpoint = $longitude, ypoint = $latitude, locate_time = ".NOW_TIME." where id = $user_id";
			$GLOBALS['db']->query($sql);
		}
		
		output($root);
	}
	
}
?>