<?php
//商圈缓存
class cache_area_auto_cache extends auto_cache{
	public function load($param)
	{
		static $area_list;
		if($area_list)return $area_list;		
		$param = array("city_id"=>$param['city_id']); //重新定义缓存的有效参数，过滤非法参数		
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$area_list = $GLOBALS['cache']->get($key);				
		if($area_list === false)
		{		
			//验证参数有效性
			$param['city_id'] = intval($GLOBALS['db']->getOne("select id from ".DB_PREFIX."deal_city where id = ".intval($param['city_id'])));			
			$key = $this->build_key(__CLASS__,$param);
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$area_list = $GLOBALS['cache']->get($key);
			if($area_list!==false)return $area_list;
			
			$city_id = $param['city_id'];
			if($city_id>0)
			$area_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id);
			else
			$area_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area");
			foreach($area_list_rs as $k=>$v)
			{
				$area_list[$v['id']] = $v;
			}
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$area_list);
		}
		return $area_list;	
	}
	public function rm($param)
	{
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->rm($key);
	}
	public function clear_all()
	{
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$GLOBALS['cache']->clear();
	}
}
?>