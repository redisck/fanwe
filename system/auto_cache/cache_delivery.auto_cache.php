<?php
//配送方式
class cache_delivery_auto_cache extends auto_cache{
	public function load($param)
	{
		static $delivery_list;
		if($delivery_list)return $delivery_list;	
		$param = array();
		$key = $this->build_key(__CLASS__,$param);
		$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
		$delivery_list = $GLOBALS['cache']->get($key);				
		if($delivery_list === false)
		{		
			$delivery_list_rs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."delivery");
			foreach($delivery_list_rs as $k=>$v)
			{
				$delivery_list[$v['id']] = $v;
			}
			$GLOBALS['cache']->set_dir(APP_ROOT_PATH."public/runtime/data/".__CLASS__."/");
			$GLOBALS['cache']->set($key,$delivery_list);
		}
		return $delivery_list;	
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